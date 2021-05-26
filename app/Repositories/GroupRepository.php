<?php
namespace App\Repositories;

// 群組表 model
use App\Models\Group;
// UserOperator表 model
use App\Models\User\UserOperator;
// 導入 共用方法
use App\Helpers\FuncHelper;

class GroupRepository
{
    // Group Model 指定變數
    protected $group;
    // UserOperator Model 指定變數
    protected $userOperator;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(Group $group, UserOperator $userOperator, FuncHelper $funcHelper)
    {
        $this->group = $group;
        $this->funcHelper = $funcHelper;
        $this->userOperator = $userOperator;
    }

    /**
     * 新增群組
     * @param $data 群組內容
     * @param object $groupableOrm 多態關聯表資料
     */
    public function create($data, $groupableOrm)
    {
        $group = new $this->group;
        $group->groupable()->associate($groupableOrm);
        $group->fill($data);
        $group->save();
        return $group->fresh();
    }
    /**
     * 更新群組
     * @param $data 群組內容
     */
    public function update($data)
    {
        $user = auth()->user();
        // 用來判斷是否能更新
        $canUpdate = false;
        // 如果是系統使用者 可以更新
        if ($user->group->group_code === "SYSTEM") {
            $canUpdate = true;
        } else {
            // 非系統人員不可更新此欄位值
            unset($data["group_code"]);
            // 非系統人員不可更新此欄位值
            unset($data["permission_rule"]);
            // 取得管理者自行創建群組資料 如果為空陣列 代表此群組非此管理者創建的
            $checkGroupCreateUser = auth()->user()->userable->userOperator->group()->where("group_id", $data["group_id"])->get();
            // 判斷是否為管理者自行創建的群組 如果不是管理者自行創建的群組 則無法更新
            if (!empty($checkGroupCreateUser->toArray())) {
                $canUpdate = true;
            }
        }
        // 判斷是否能更新
        if ($canUpdate) {
            $group = $this->group->find($data["group_id"]);
            $groupableOrm = $group->groupable;
            $group->groupable()->associate($groupableOrm);
            $group->fill($data);
            $group->save();
            return $group->fresh();
        }
        return false;
    }
    /**
     * 取得列表
     * @param object $data 分頁傳送過濾資料
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
            $groupLists = $this->group->with(["groupable"]);
        } else {
            // 回傳登入者自行建立的群組列表 group_id 值
            $groupIds = $user->userable->userOperator->group->pluck("group_id");
            // 取出登入者可用群組列表以及系統建立的群組列表 且系統建立的群列表中如果有包含系統群組時，需過濾系統群組
            $groupLists = $this->group
            ->with(["groupable"])
            // 取出登入者創建的群組
            ->whereIn("group_id", $groupIds)
            // 取出系統串間的群組
            ->orWhereHasMorph("groupable", ["App\Models\User\UserSystem"], function ($query) use ($user) {
                // 取出系統創建指定登入管理者可用群組
                $query->whereJsonContains("operator_ids", $user->userable->userOperator->operator_id)
                // 過濾掉系統專用群組
                ->where("group_code", "!=", "SYSTEM");
            });
        }
        // 將群組列表傳入過濾參數
        $responseData = $this->funcHelper->toWhereSort($groupLists, $data)->paginate($perPage);
        // 將群組列表啟用分頁
        return $responseData;
    }

    /**
     * 取得可使用的列表
     * @param string $groupCode 群組代碼
     */
    public function getSelectLists($groupCode, $operatorId, $isSub = false)
    {
        // 登入者群組代碼
        $userGroupCode = auth()->user()->group->group_code;
        $checkIsSub = $isSub ? 1 : 0;
        // 如果是新增系統使用者時 需傳入 0
        if ($operatorId != 0) {
            // 取出屬於該管理者自己創建的群組id
            $groupIds = collect($this->userOperator->find($operatorId)->group)->pluck("group_id");
        } else {
            $groupIds = [];
        }
        /**
         * 新增系統使用者時使用
         * 判斷管理者id 是否傳入 0
         * 判斷系統群組代碼 是否為 SYSTEM
         * 判斷登入者是否為系統身份
         */
        if ($operatorId == 0 && $groupCode === "SYSTEM" && $userGroupCode === "SYSTEM") {
            // 過濾群組代碼
            $groupLists = $this->group->where("group_code", $groupCode)->where("is_sub", $checkIsSub)->get();
            return $groupLists;
        }
        /**
         * 此判斷式主要用來取出
         * 1.屬於登入的管理者創建的群組
         * 2.屬於系統創建的群組 且有指定管理者可以使用
         * 3.屬於共用群組也就是值為 [](空陣列) 或 null
         * 4.以及過濾是否為子帳號的群組
         */
        $groupLists = $this->group
        // 過濾群組代碼
        ->where("group_code", $groupCode)
        // 過濾子帳號群組
        ->where("is_sub", $checkIsSub)
        // 過濾群組 id
        ->whereIn("group_id", $groupIds)
        // 取出系統創建的群組
        ->orWhereHasMorph("groupable", ["App\Models\User\UserSystem"], function ($query) use ($operatorId, $groupCode, $checkIsSub) {
            // 取出登入者可用群組 以及 判斷是否為對應群組代碼可用的群組
            $query->whereJsonContains("operator_ids", (int)$operatorId)->where("is_sub", $checkIsSub)->where("group_code", $groupCode)
            // 或者取出 operator_ids 值為 null 的群組 (如果為null 代表為共用群組) 以及對應的群組代碼 並且非子帳號專用群組
            ->orWhere("operator_ids", null)->where("is_sub", $checkIsSub)->where("group_code", $groupCode)
            // 或者取出 operator_ids 值為 [](空陣列) 的群組 (如果為 [](空陣列) 代表為共用群組) 以及對應的群組代碼 並且非子帳號專用群組
            ->orWhereJsonLength("operator_ids", 0)->where("is_sub", $checkIsSub)->where("group_code", $groupCode);
        })->get();
        return $groupLists;
    }

    /**
     * 取得資料
     * @param string $groupId 群組id
     */
    public function getData($groupId)
    {
        // 取得登入者群取身份
        $userGroupCode = auth()->user()->group->group_code;
        // 判斷是否為系統身份
        if ($userGroupCode === "SYSTEM") {
            // 如果為系統身份時 無需過慮該群組是否為 管理者自行創建的
            $group = $this->group->find($groupId);
            return $group;
        } else {
            // 如果非系統身份時 只能查詢管理者自己創建的群組資料
            $groupIds = auth()->user()->userable->userOperator->group->pluck("group_id");
            $group = $this->group
            ->where("group_id", $groupId)
            ->whereIn("group_id", $groupIds)
            ->orWhereHasMorph("groupable", ["App\Models\User\UserSystem"], function ($query) use ($groupId) {
                $query->where("group_id", $groupId);
            })
            ->first();
            return $group;
        }
    }
}
