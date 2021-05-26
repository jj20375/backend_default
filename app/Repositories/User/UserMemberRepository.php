<?php
namespace App\Repositories\User;

// user_members表 model
use App\Models\User\UserMember;
// user_operators表 model
use App\Models\User\UserOperator;
// 導入 共用方法
use App\Helpers\FuncHelper;

class UserMemberRepository
{

    // UserMember Model 指定變數
    protected $userMember;
    // UserOperator Model 指定變數
    protected $userOperator;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(UserMember $userMember, UserOperator $userOperator, FuncHelper $funcHelper)
    {
        $this->userMember= $userMember;
        $this->userOperator= $userOperator;
        $this->funcHelper= $funcHelper;
    }

    /**
     * 新增會員
     * @param object $data 新增會員資料
     */
    public function create($data)
    {
        $userMember = new $this->userMember;
        $userMember->fill($data);
        $userMember->save();
        return $userMember->fresh();
    }
    /**
     * 更新會員
     * @param object $data 新增會員資料
     */
    public function update($data)
    {
        $userMember = $this->userMember->find($data["member_id"]);
        $userMember->fill($data);
        $userMember->save();
        return $userMember->fresh();
    }
    /**
     * 取得列表
     * @param object $data 分頁搜尋過濾條件
     */
    public function getLists($data = null)
    {
        // 取得登入者資料
        $user = auth()->user();
        // 預設如果沒有傳送一頁呈現幾筆資料的話 會出現15筆
        $perPage = $data["perPage"] ?? 15;
        // 刪除perPage key 此 key 只是用來判斷一頁要呈現幾筆資料而已
        unset($data["perPage"]);
        // 判斷是否為系統使用者
        if ($user->group->group_code === "SYSTEM") {
            $userMemberLists = $this->userMember->with(["userOperator", "user", "userMemberInfo"]);
        } else {
            // 回傳管理者者自行建立的會員列表 userMember_id 值
            $userMemberIds = $user->userable->userOperator->userMember->pluck("member_id");
            // 取出會員並關聯相關的表資料
            $userMemberLists = $this->userMember->with(["userOperator", "user", "userMemberInfo"])
            // 取出管理者創建的會員
            ->whereIn("member_id", $userMemberIds);
        }
        // 將會員列表傳入過濾參數
        $responseData = $this->funcHelper->toWhereSort($userMemberLists, $data)->paginate($perPage);
        // 將會員列表啟用分頁
        return $responseData;
    }

    /**
     * 取得指定id列表資料
     * @param string $operatorId user_operators表 id
     * @param object $data 分頁搜尋過濾資料
     */
    public function getListsById($operatorId, $data = null)
    {
        $userGroupCode = auth()->user()->group->group_code;
        // 預設如果沒有傳送一頁呈現幾筆資料的話 會出現15筆
        $perPage = $data["perPage"] ?? 15;
        // 刪除不必要的 key
        unset($data["operatorId"]);
        // 判斷是否有傳入 perPage key
        if (!empty($data["perPage"])) {
            // 刪除perPage key 此 key 只是用來判斷一頁要呈現幾筆資料而已
            unset($data["perPage"]);
        }
        /**
         * 此判斷最主要是防止 系統已為使用者 透過id 方式 獲取到不屬於他底細的子帳號資料
         */
        if ($userGroupCode === "SYSTEM") {
            // 系統身份直接撈取管理者id對應的會員列表
            $userMemberLists = $this->userOperator->find($operatorId)->userMember()->with(["userOperator", "user", "userMemberInfo"]);
        } else {
            // 回傳管理者者自行建立的會員列表 member_id 值
            $userMemberIds = auth()->user()->userable->userOperator->userMember->pluck("member_id");
            $userMemberLists = $this->userOperator->find($operatorId)->userMember()->whereIn("member_id", $userMemberIds)->with(["userOperator", "user", "userMemberInfo"]);
        }
        // 將會員列表傳入過濾參數
        $responseData = $this->funcHelper->toWhereSort($userMemberLists, $data)->paginate($perPage);
        // 將會員列表啟用分頁
        return $responseData;
    }
    /**
     * 取得可使用的列表
     * @param string $operatorId 管理者id
     */
    public function getSelectLists($operatorId)
    {
        // 取得登入者群組代碼
        $userGroupCode = auth()->user()->group->group_code;
        // 判斷是否為系統登入者
        if ($userGroupCode === "SYSTEM") {
            // 取出屬於指定管理者id自己創建的會員id
            $userMemberIds = $this->userOperator->find($operatorId)->userMember->pluck("member_id");
        } else {
            // 取出屬於該管理者自己創建的會員id
            $userMemberIds = auth()->user()->userable->userOperator->userMember->pluck("member_id");
        }
        /**
         * 此判斷式主要用來取出
         * 1.屬於登入的管理者創建的會員
         * 2.屬於系統創建的會員 且有指定管理者可以使用
         * 3.屬於共用會員也就是值為 [](空陣列) 或 null
         */
        $userMemberLists = $this->userMember
        ->with(["userMemberInfo"])
        // 過濾會員 id
        ->whereIn("member_id", $userMemberIds)
        // 判斷是否有會員
        ->where("status", 5)
        ->get();
        return $userMemberLists;
    }
    /**
     * 取得資料
     * @param string $column 資料表欄位
     * @param string $data 欄位資料
     */
    public function getData($column, $data)
    {
        // 取得登入者群取身份
        $userGroupCode = auth()->user()->group->group_code;
        // 判斷是否為系統身份
        if ($userGroupCode === "SYSTEM") {
            // 如果為系統身份時 無需過慮該會員是否為 管理者自行創建的
            $userMember = $this->userMember->with(["userOperator", "user", "userMemberInfo"])->where($column, $data)->first();
            return $userMember;
        } else {
            // 如果非系統身份時 只能查詢管理者自己創建的會員資料
            $userMemberIds = auth()->user()->userable->userOperator->userMember->pluck("member_id");
            $userMember = $this->userMember->with(["userOperator", "user", "userMemberInfo"])->where($column, $data)->whereIn("member_id", $userMemberIds)->first();
            return $userMember;
        }
    }
    /**
    * 取得多筆會員資料
    * @param data { type Object(物件) } 搜尋過濾資料
    * @param operatorId { type String or Number(字串或數字) } 管理者id
    * @param onleyOneOperator { type Boolean(布林值) } 判斷是否需要抓取下層管理者資料
    */
    public function getMoreData($data, $operatorId, $onleyOneOperator = false)
    {
        // 如果傳入0時取身份為系統時 不過過濾管理者id
        if (auth()->user()->group->group_code === "SYSTEM" && $operatorId == 0) {
            // 會員資料
            $members = $this->userMember->with(["userOperator"]);
        } else {
            // 判斷是否需要下抓取下層管理者id
            if ($onleyOneOperator) {
                // 只抓取指定管理者本身的id
                $userOperatorIds = $this->userOperator->where("operator_id", $operatorId)->pluck("operator_id");
            } else {
                // 取得對應管理者資料
                $userOperator = $this->userOperator->find($operatorId);
                // 取出包含自身管理者以及下層管理者id
                $userOperatorIds = $userOperator->descendantsAndSelf($operatorId)->pluck("operator_id");
            }
            // 會員資料
            $members = $this->userMember->with(["userOperator"])->whereIn("operator_id", $userOperatorIds);
        }
        // 搜尋結果時 無需過濾 管理者 id
        unset($data["operator_id"]);
        // 回傳搜尋結果
        $responseData = $this->funcHelper->toWhereSort($members, $data)->get();
        return $responseData;
    }

    /**
     * 取得簡訊發送名單
     * @param params { type Object(物件) } 過濾參數
     */
    public function getSmsSendLists($params)
    {
        $lists = $this->userMember->where("phone", "!=", null)->where("status", 5)->where("operator_id", $params["operator_id"]);
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'month':
                        $lists->whereMonth("birthday", $value);
                    break;
                case 'day':
                        $lists->whereDay("birthday", $value);
                    break;
                case 'sendSmsActive':
                        $lists->where("sendSmsActive", $value);
                    break;
            }
        }
        return $lists->get();
    }
    /**
     * 模糊比對搜尋使用
     * @param params { type Object(物件) }搜尋參數
     */
    public function remoteLists($params) {
        
        $lists = $this->userMember->with("userMemberInfo");
        foreach($params as $key => $value) {
            switch ($key) {
                case 'custom_id':
                    $lists->whereHas("userMemberInfo", function($query) use($key, $value) {
                        $query->where($key, "like", "%".$value);
                    });
                    break;
                case 'account':
                    $lists->where($key, $value);
                    break;
                case 'name':
                    $lists->where($key, $value);
                    break;
                case 'nickname':
                    $lists->where($key, $value);
                    break;
                case 'phone':
                    $lists->where($key, $value);
                    break;
                case 'phone2':
                    $lists->where($key, $value);
                    break;
            }
        }
        return $lists->where("operator_id", $params["operator_id"])->take(10)->get();
    }
}
