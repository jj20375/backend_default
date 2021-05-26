<?php
namespace App\Repositories\User;

// 系統使用者表 model
use App\Models\User\UserSub;
// user_operators表 model
use App\Models\User\UserOperator;
// 系統使用者表 model
use App\Models\User\User;
// 導入 共用方法
use App\Helpers\FuncHelper;

class UserSubRepository
{

    // UserSub Model 指定變數
    protected $userSub;
    // User Model 指定變數
    protected $user;
    // UserOperator Model 指定變數
    protected $userOperator;
    // 導入共用方法 指定變數
    protected $funcHelper;

    
    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(UserSub $userSub, User $user, UserOperator $userOperator, FuncHelper $funcHelper)
    {
        $this->userSub = $userSub;
        $this->user = $user;
        $this->userOperator = $userOperator;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增子帳號
     * @param object $data 新增子帳號資料
     * @param string $userId 使用者id
     */
    public function create($data, $userId)
    {
        // 子帳號關聯對象
        $user = $this->user->find($userId);
        if($user->group->group_code === "SYSTEM") {
            $subableOrm = $user->userable;
        } else {
            $subableOrm = $user->userable->userOperator;
        }
        $userSub = new $this->userSub;
        // 將子帳號關聯 model 與 id 存入資料庫
        $userSub->subable()->associate($subableOrm);
        $userSub->fill($data);
        $userSub->save();
        return $userSub->fresh();
    }

    /**
     * 更新子帳號
     * @param object $data 更新子帳號資料
     */
    public function update($data)
    {
        $userSub = $this->userSub->find($data["sub_id"]);
        $userSub->fill($data);
        $userSub->save();
        return $userSub->fresh();
    }

    /**
    * 判斷指定欄位是否有重複資料
    * @param string $column 欄位名稱
    * @param string $data 欄位資料
    */
    public function checkDuplicate($column, $data)
    {
        // 判斷是否有重複值
        $userSub = $this->userSub->where($column, $data)->first();
        // 如果有資料代表
        if (isset($userSub)) {
            return true;
        }
        return false;
    }
    /**
     * 取得列表資料
     * @param object $data 搜尋過濾參數
     */
    public function getLists($data = null)
    {
        $groupCode = auth()->user()->group->group_code;
        // 預設如果沒有傳送一頁呈現幾筆資料的話 會出現15筆
        $perPage = $data["perPage"] ?? 15;
        // 刪除perPage key 此 key 只是用來判斷一頁要呈現幾筆資料而已
        unset($data["perPage"]);
        // 判斷是否為系統使用者
        if ($groupCode === "SYSTEM") {
            // 系統使用者回傳全部子帳號列表資料
            $userSubLists = $this->userSub->with(["user.group", "subable"]);
        } else {
            // 非系統使用者回傳屬於管理者底下的子帳號列表
            $userSubLists = auth()->user()->userable->userOperator->userSub()->with(["user.group", "subable"]);
        }
        // 將群組列表傳入過濾參數
        $responseData = $this->funcHelper->toWhereSort($userSubLists, $data)->paginate($perPage);
        // 將群組列表啟用分頁
        return $responseData;
    }
    /**
     * 取得指定id列表資料
     * @param string $userId users表 id
     * @param object $data 分頁搜尋過濾資料
     */
    public function getListsById($userId, $data = null)
    {
        $userGroupCode = auth()->user()->group->group_code;
        // 預設如果沒有傳送一頁呈現幾筆資料的話 會出現15筆
        $perPage = $data["perPage"] ?? 15;
        // 刪除不必要的 key
        unset($data["userId"]);
        // 判斷是否有傳入 perPage key
        if (!empty($data["perPage"])) {
            // 刪除perPage key 此 key 只是用來判斷一頁要呈現幾筆資料而已
            unset($data["perPage"]);
        }
        /**
         * 此判斷最主要是防止 系統已為使用者 透過id 方式 獲取到不屬於他底細的子帳號資料
         */
        if ($userGroupCode === "SYSTEM") {
            // 系統身份直接撈取userId參數中對應的子帳號列表
            $userSubLists = $this->user->find($userId)->userable->userSub()->with(["user.group", "subable"]);
        } else {
            // 回傳管理者者自行建立的子帳號列表 sub_id 值
            $subIds = auth()->user()->userable->userOperator->userSub->pluck("sub_id");
            $userSubLists = $this->user->find($userId)->userable->userSub()->whereIn("sub_id", $subIds)->with(["user.group", "subable"]);
        }
        // 將群組列表傳入過濾參數
        $responseData = $this->funcHelper->toWhereSort($userSubLists, $data)->paginate($perPage);
        // 將群組列表啟用分頁
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
            // 取出屬於指定管理者id自己創建的子帳號id
            $userSubIds = $this->userOperator->find($operatorId)->userSub->pluck("sub_id");
        } else {
            // 取出屬於該管理者自己創建的子帳號id
            $userSubIds = auth()->user()->userable->userOperator->userSub->pluck("sub_id");
        }
        /**
         * 此判斷式主要用來取出
         * 1.屬於登入的管理者創建的子帳號
         */
        $userSubLists = $this->userSub
        ->with(["user"])
        // 過濾子帳號 id
        ->whereIn("sub_id", $userSubIds)
        // 判斷是否有子帳號
        ->where("status", 5)
        ->get();
        return $userSubLists;
    }

    /**
     * 取得資料
     * @param string $column 資料表欄位
     * @param string $data 欄位資料
     */
    public function getData($column, $data)
    {
        $userGroupCode = auth()->user()->group->group_code;
        // 判斷是否為系統使用者
        if ($userGroupCode === "SYSTEM") {
            // 系統使用者回傳搜尋條件對應的子帳號資料
            $userSub = $this->userSub->with(["user.group", "userOperator"])->where($column, $data)->first();
        } else {
            // 找出目前登入管理者底下的子帳號id
            $subIds = auth()->user()->userable->userOperator->userSub->pluck("sub_id");
            // 比對搜尋條件並判斷是否屬於該管理者底下的子帳號(以防有人透過id隨意搜尋子帳號)
            $userSub = $this->userSub->with(["user.group", "userOperator"])->where($column, $data)->whereIn("sub_id", $subIds)->first();
        }
        return $userSub;
    }

    /**
    * 取得指定 id 陣列中 列表資料
    * @param moreId { type Array(陣列) } 管理者id
    */
    public function getOperatorSub($moreId)
    {
        $datas = $this->userSub->with(["salary"])->whereHasMorph("subable", ["App\Models\User\UserOperator"], function ($query) use ($moreId) {
            $query->whereIn("operator_id", $moreId);
        })->where("status", 5)->get();
        return $datas;
    }
}
