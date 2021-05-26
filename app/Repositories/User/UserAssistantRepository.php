<?php
namespace App\Repositories\User;

// 助理表(user_assistants) model
use App\Models\User\UserAssistant;
// 管理者表(user_operators) model
use App\Models\User\UserOperator;
// 導入 共用方法
use App\Helpers\FuncHelper;

class UserAssistantRepository
{

    // UserAssistant Model 指定變數
    protected $userAssistant;
    // UserOperator Model 指定變數
    protected $userOperator;
    // 導入共用方法 指定變數
    protected $funcHelper;

    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(UserAssistant $userAssistant, UserOperator $userOperator, FuncHelper $funcHelper)
    {
        $this->userAssistant = $userAssistant;
        $this->userOperator = $userOperator;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增助理
     * @param object $data 新增助理資料
     */
    public function create($data)
    {
        $userAssistant = new $this->userAssistant;
        $userAssistant->fill($data);
        $userAssistant->save();
        return $userAssistant->fresh();
    }
    /**
     * 更新助理
     * @param object $data 更新助理資料
     */
    public function update($data)
    {
        $userAssistant = $this->userAssistant->find($data["assistant_id"]);
        $userAssistant->fill($data);
        $userAssistant->save();
        return $userAssistant->fresh();
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
            // 系統使用者回傳全部助理列表資料
            $userAssistantLists = $this->userAssistant->with(["userOperator", "user.group"]);
        } else {
            // 非系統使用者回傳屬於管理者底下的助理列表
            $userAssistantLists = auth()->user()->userable->userOperator->userAssistant()->with(["userOperator", "user.group"]);
        }
        // 將群組列表傳入過濾參數
        $responseData = $this->funcHelper->toWhereSort($userAssistantLists, $data)->paginate($perPage);
        // 將群組列表啟用分頁
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
        if(!empty($data["perPage"])) {
            // 刪除perPage key 此 key 只是用來判斷一頁要呈現幾筆資料而已
            unset($data["perPage"]);
        }
        /**
         * 此判斷最主要是防止 系統已為使用者 透過id 方式 獲取到不屬於他底細的子帳號資料
         */
        if ($userGroupCode === "SYSTEM") {
            // 系統身份直接撈取管理者id對應的助理列表
            $userAssistantLists = $this->userOperator->find($operatorId)->userAssistant()->with(["user.group","userOperator"]);
        } else {
            // 回傳管理者者自行建立的助理列表 assistant_id 值
            $assistantIds = auth()->user()->userable->userOperator->userAssistant->pluck("assistant_id");
            $userAssistantLists = $this->userOperator->find($operatorId)->userAssistant()->whereIn("assistant_id", $assistantIds)->with(["user.group","userOperator"]);
        }
        // 將助理列表傳入過濾參數
        $responseData = $this->funcHelper->toWhereSort($userAssistantLists, $data)->paginate($perPage);
        // 將助理列表啟用分頁
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
            // 取出屬於指定管理者id自己創建的助理id
            $userAssistantIds = $this->userOperator->find($operatorId)->userAssistant->pluck("assistant_id");
        } else {
            // 取出屬於該管理者自己創建的助理id
            $userAssistantIds = auth()->user()->userable->userOperator->userAssistant->pluck("assistant_id");
        }
        /**
         * 此判斷式主要用來取出
         * 1.屬於登入的管理者創建的助理
         * 2.屬於系統創建的助理 且有指定管理者可以使用
         * 3.屬於共用助理也就是值為 [](空陣列) 或 null
         */
        $userAssistantLists = $this->userAssistant
        ->with(["user"])
        // 過濾助理 id
        ->whereIn("assistant_id", $userAssistantIds)
        // 判斷是否有助理
        ->where("status", 5)
        ->get();
        return $userAssistantLists;
    }

    /**
     * 取得資料
     * @param string $column 資料表欄位
     * @param string $data 欄位資料
     */
    public function getData($column, $data)
    {
        // 登入者的群組代碼
        $userGroupCode = auth()->user()->group->group_code;
        // 判斷是否為系統使用者
        if ($userGroupCode === "SYSTEM") {
            // 系統使用者回傳搜尋條件對應的助理資料
            $userAssistant = $this->userAssistant->with(["userOperator", "user.group", "image"])->where($column, $data)->first();
        } else {
            // 找出目前登入管理者底下的助理id
            $assistantIds = auth()->user()->userable->userOperator->userAssistant->pluck("assistant_id");
            // 比對搜尋條件並判斷是否屬於該管理者底下的助理(以防有人透過id隨意搜尋助理)
            $userAssistant = $this->userAssistant->with(["userOperator", "user.group", "image"])->where($column, $data)->whereIn("assistant_id", $assistantIds)->first();
        }
        return $userAssistant;
    }
    /**
     * 取得指定 id 陣列中 列表資料
     * @param moreId { type Array(陣列) } 管理者id
     */
    public function getOperatorAssistant($moreId)
    {
        $datas = $this->userAssistant->with(["salary"])->whereIn("operator_id", $moreId)->where("status", 5)->get();
        return $datas;
    }
}
