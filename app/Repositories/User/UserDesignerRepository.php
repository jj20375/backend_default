<?php
namespace App\Repositories\User;

// 助理表(user_assistants) model
use App\Models\User\UserDesigner;
// 助理表(user_operators) model
use App\Models\User\UserOperator;
// 導入 共用方法
use App\Helpers\FuncHelper;

class UserDesignerRepository
{

    // UserDesigner Model 指定變數
    protected $userDesigner;
    // UserOperator Model 指定變數
    protected $userOperator;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    
    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(UserDesigner $userDesigner, UserOperator $userOperator, FuncHelper $funcHelper)
    {
        $this->userDesigner = $userDesigner;
        $this->userOperator = $userOperator;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增服務提供者
     * @param object $data 新增服務提供者資料
     */
    public function create($data)
    {
        $userDesigner = new $this->userDesigner;
        $userDesigner->fill($data);
        $userDesigner->save();
        return $userDesigner->fresh();
    }
    /**
     * 更新服務提供者
     * @param object $data 更新服務提供者資料
     */
    public function update($data)
    {
        $userDesigner = $this->userDesigner->find($data["designer_id"]);
        $userDesigner->fill($data);
        $userDesigner->save();
        return $userDesigner->fresh();
    }
    /**
     * 取得列表資料
     * @param object $data 搜尋過濾參數
     */
    public function getLists($data = null)
    {
        $userGroupCode = auth()->user()->group->group_code;
        // 預設如果沒有傳送一頁呈現幾筆資料的話 會出現15筆
        $perPage = $data["perPage"] ?? 15;
        // 刪除perPage key 此 key 只是用來判斷一頁要呈現幾筆資料而已
        unset($data["perPage"]);
        // 判斷是否為系統使用者
        if ($userGroupCode === "SYSTEM") {
            // 系統使用者回傳全部助理列表資料
            $userDesignerLists = $this->userDesigner->with(["userOperator", "user.group"]);
        } else {
            // 非系統使用者回傳屬於管理者底下的助理列表
            $userDesignerLists = auth()->user()->userable->userOperator->userDesigner()->with(["userOperator", "user.group"]);
        }
        // 將群組列表傳入過濾參數
        $responseData = $this->funcHelper->toWhereSort($userDesignerLists, $data)->paginate($perPage);
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
        if (!empty($data["perPage"])) {
            // 刪除perPage key 此 key 只是用來判斷一頁要呈現幾筆資料而已
            unset($data["perPage"]);
        }
        /**
         * 此判斷最主要是防止 系統已為使用者 透過id 方式 獲取到不屬於他底細的子帳號資料
         */
        if ($userGroupCode === "SYSTEM") {
            // 系統身份直接撈取管理者id對應的服務提供者列表
            $userDesignerLists = $this->userOperator->find($operatorId)->userDesigner()->with(["user.group","userOperator"]);
        } else {
            // 回傳管理者者自行建立的服務提供者列表 designer_id 值
            $designerIds = auth()->user()->userable->userOperator->userDesigner->pluck("designer_id");
            $userDesignerLists = $this->userOperator->find($operatorId)->userDesigner()->whereIn("designer_id", $designerIds)->with(["user.group","userOperator"]);
        }
        // 將群組列表傳入過濾參數
        $responseData = $this->funcHelper->toWhereSort($userDesignerLists, $data)->paginate($perPage);
        // 將群組列表啟用分頁
        return $responseData;
    }
    /**
     * 取得可選擇的列表
     * @param string $operatorId 管理者id
     */
    public function getSelectLists($operatorId)
    {
        // 取得登入者群組代碼
        $userGroupCode = auth()->user()->group->group_code;
        // 判斷是否為系統登入者
        if ($userGroupCode === "SYSTEM") {
            // 取出屬於指定管理者id自己創建的服務提供者id
            $userDesignerIds = $this->userOperator->find($operatorId)->userDesigner->pluck("designer_id");
        } else {
            // 取出屬於該管理者自己創建的服務提供者id
            $userDesignerIds = auth()->user()->userable->userOperator->userDesigner->pluck("designer_id");
        }
        /**
         * 此判斷式主要用來取出
         * 1.屬於登入的管理者創建的服務提供者
         */
        $userDesignerLists = $this->userDesigner
        ->with(["user"])
        // 過濾服務提供者 id
        ->whereIn("designer_id", $userDesignerIds)
        // 判斷是否有啟用服務提供者
        ->where("status", 5)
        ->get();
        return $userDesignerLists;
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
            $userDesigner = $this->userDesigner->with(["userOperator", "user.group", "image"])->where($column, $data)->first();
        } else {
            // 找出目前登入管理者底下的助理id
            $designerIds = auth()->user()->userable->userOperator->userDesigner->pluck("designer_id");
            // 比對搜尋條件並判斷是否屬於該管理者底下的助理(以防有人透過id隨意搜尋助理)
            $userDesigner = $this->userDesigner->with(["userOperator", "user.group", "image"])->where($column, $data)->whereIn("designer_id", $designerIds)->first();
        }
        return $userDesigner;
    }

    /**
     * 取得指定 id 陣列中 列表資料
     * @param moreId { type Array(陣列) } 管理者id
     */
    public function getOperatorDesigner($moreId)
    {
        $datas = $this->userDesigner->whereIn("operator_id", $moreId)->where("status", 5)->get();
        return $datas;
    }
}
