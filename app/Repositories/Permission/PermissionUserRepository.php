<?php
namespace App\Repositories\Permission;

// 個人權限資料 model
use App\Models\Permission\PermissionUser;
// 權限群組資料 model
use App\Models\Permission\PermissionGroup;
// 預設權限資料 model
use App\Models\Permission\PermissionDefault;
// Users表資料 model
use App\Models\User\User;
// 管理者資料 model
use App\Models\User\UserOperator;

class PermissionUserRepository
{

    // PermissionUser Model 指定變數
    protected $permissionUser;
    // PermissionGroup Model 指定變數
    protected $permissionGroup;
    // PermissionDefault Model 指定變數
    protected $permissionDefault;
    // User Model 指定變數
    protected $user;
    // UserOperator Model 指定變數
    protected $userOperator;

    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(PermissionUser $permissionUser, PermissionGroup $permissionGroup, PermissionDefault $permissionDefault, User $user, UserOperator $userOperator)
    {
        $this->permissionUser = $permissionUser;
        $this->permissionDefault = $permissionDefault;
        $this->permissionGroup = $permissionGroup;
        $this->user = $user;
        $this->userOperator = $userOperator;
    }

    /**
     * 新增個人權限
     * @param object $data 新個人權限資料
     */
    public function create($data)
    {
        $permissionUser = new $this->permissionUser;
        $permissionUser->fill($data);
        $permissionUser->save();
        return $permissionUser->fresh();
    }

    /**
     * 更新群組權限資料
     * @param object $data 更新群組權限資料
     * @param string $userId 需更新的使用者 id
     * @param object $ormData 關聯表資料
     */
    public function update($data, $userId, $ormData)
    {
        // 取得需更新的個人權限資料
        $permissionUser = $this->permissionUser->where("user_id", $data["user_id"])->where("key", $data["key"])->first();
        // 判斷如果沒有找到群組權限則執行新增方法
        if ($permissionUser === null) {
            $permissionUser = $this->permissionUser->create($data);
            return $permissionUser;
        }
        // 因為 多對多多態 需要使用 find 方法找出需更新的嘅人權限單筆資料 因此多做這一步查詢
        $permissionUser = $this->permissionUser->find($userId);
        $permissionUpdate = $this->permissionUser
            ->where("user_id", $userId)
            ->where("key", $data["key"])
            ->update($data);
        // 判斷是否更新成功
        if ($permissionUpdate === 1) {
            $permissionUser = $data;
            return $permissionUser;
        }
        return false;
    }

    /**
     * 判斷指定欄位是否有重複資料
     * @param string $column 欄位名稱
     * @param string $data 欄位資料
     */
    public function checkDuplicate($column, $data)
    {
        // 判斷是否有職
        $permissionUser = $this->permissionUser->where($column, $data)->first();
        // 如果有資料代表
        if (isset($permissionUser)) {
            return true;
        }
        return false;
    }

    /**
     * 判斷指定id中欄位是否有重複資料
     * @param string $column 欄位名稱
     * @param string $data 欄位資料
     * @param string $column2 判斷第二個欄位名稱
     */
    public function checkDuplicateById($column, $data, $column2)
    {
        // 判斷指定欄位是否有重複資料
        $isDuplicate = $this->permissionUser
        ->where($column, $data[$column])
        ->where($column2, $data[$column2])
        ->first();
        // 如果有資料代表
        if (isset($isDuplicate)) {
            return true;
        }
        return false;
    }

    /**
    * 取得可用選單路由
    */
    public function getMenu()
    {
        // 取得登入者身份
        $user = auth()->user();
        // 取得登入者身份的群組代碼
        $userGroupCode = $user->group->group_code;
        /**
         * 判斷登入者使用群組為 OPERATOR 以及 parent_id 不等於 null 時 需取得上層管理者 群組權限資料 (parent_id = null 時，此登入身份為最上層管者)
         * 再將上層管理者可用群組權限 可用的 permission_id 帶入搜尋條件 取出 目前登入者可用的 預設權限
         * (此判斷用意在於 預防 下層者可以 獲得比上層者更高的權限問題發生)
         */
        if ($userGroupCode === "OPERATOR" && $user->userable->parent_id !== null) {
            // 取得數位權限中可用得預設權限 id
            $permissionDefaultIds = collect($this->permissionDefault->select("id")->where('permission_rule', '&', config('app.groupCode.'.$userGroupCode))->get())
                    ->pluck("id")->all();
            // 取得上層群組可用權限 id (permission_id)
            $bossPermissionIds = $this->getBossPermission($user->user_id);
            // 取得最上層與上層群組權限 crud 過濾後結果 (防止有上層開啟權限 但最上層沒開啟權限 導致超越最上層權限問題發生)
            $bossPermissionGroup = $this->getBossPermissionCrud($user->user_id, $permissionDefaultIds);
            /**
             * 取出本身可用個人權限資料 再將上層可用的群組權限資料 當條件 整理出 登入者使用的群組目前可用的權限資料
             * 並回傳 permission_id 值 (type array)
             */
            $permissionIds = collect($this->permissionUser->select("permission_id")
                    ->where("user_id", $user->user_id)
                    ->where("per_read", 1)
                    ->whereIn("permission_id", $bossPermissionIds)->get())
                    ->pluck("permission_id")->all();
        } else {
            // 取出本身可用個人權限資料 並回傳 permission_id 值 (type array)
            $permissionIds = collect($this->permissionUser->select("permission_id")
                        ->where("user_id", $user->user_id)
                        ->where("per_read", 1)->get())
                        ->pluck("permission_id")->all();
            // 取得最上層與上層群組權限 crud 過濾後結果 因登入使用者沒有上層 因此回傳空陣列
            $bossPermissionGroup = [];
        }
        // 透過 permission_id 值 取出對應的 選單路由權限資料
        $permissionMenu = $this->permissionDefault->with(["hasManyPermissionUser"  => function ($query) use ($user) {
            $query->where("user_id", $user->user_id);
        }])
        ->where('permission_rule', '&', config('app.groupCode.'.$userGroupCode))
        ->whereIn("id", $permissionIds)->get();
        // 儲存子功能
        $options = [];
        // 將登入者群組可用 crud 權限 與上層跟最上層比對完 crud 可用權限 做比對
        foreach ($permissionMenu as $item) {
            // 判斷是否有子項功能
            if ($item->hasManyPermissionGroup[0]["options"] !== null) {
                $options[$item["key"]] = $item->hasManyPermissionGroup[0]["options"];
            }
            // 如果沒有上層管理者 或者是 系統 就直接回傳本身群組權限可用 crud 設定值
            if (empty($bossPermissionGroup)) {
                $crudKeys[$item["key"]] = [
                    "id" => $item->hasManyPermissionUser[0]["id"],
                    "key" => $item["key"],
                    "user_id" => $item->hasManyPermissionUser[0]["user_id"],
                    "permission_id" => $item->hasManyPermissionUser[0]["permission_id"],
                    "per_create" => $item->hasManyPermissionUser[0]["per_create"],
                    "per_read" => $item->hasManyPermissionUser[0]["per_read"],
                    "per_update" => $item->hasManyPermissionUser[0]["per_update"],
                    "per_delete" => $item->hasManyPermissionUser[0]["per_delete"],
                ];
            }
            /**
             * 如果有上層或最上層管理者時 需比對 登入者群組 與上層跟最上層群組 crud 值 判斷是否上層有關閉 但是下層還是開啟狀態
             * 如果是此情況以上層跟最上層權限為主
             * */
            foreach ($bossPermissionGroup as $bossItem) {
                if ($item->hasManyPermissionUser[0]["key"] === $bossItem["key"]) {
                    $crudKeys[$item["key"]] = [
                        "id" => $item->hasManyPermissionUser[0]["id"],
                        "key" => $item["key"],
                        "user_id" => $item->hasManyPermissionUser[0]["user_id"],
                        "permission_id" => $item->hasManyPermissionUser[0]["permission_id"],
                        "per_create" => $bossItem["per_create"] === 1 ? $item->hasManyPermissionUser[0]["per_create"] : $bossItem["per_create"],
                        "per_read" => $bossItem["per_read"] === 1 ? $item->hasManyPermissionUser[0]["per_read"] : $bossItem["per_read"],
                        "per_update" => $bossItem["per_update"] === 1 ? $item->hasManyPermissionUser[0]["per_update"] : $bossItem["per_update"],
                        "per_delete" => $bossItem["per_delete"] === 1 ? $item->hasManyPermissionUser[0]["per_delete"] : $bossItem["per_delete"],
                    ];
                }
            }
        }
        return ["permissionMenu" => $permissionMenu, "crudKeys" => $crudKeys, "options" => $options];
        // // 如果需要臨時回傳多餘的指定欄位時 可以用此 makeVisible 方法添加
        // $permissionGroup->makeVisible(["per_create", "per_read"]);
        // // 如果需要隱藏回傳多餘的指定欄位時 可以用此 makeVisible 方法隱藏
        // $permissionGroup->makeHidden(["key", "per_read"]);
    }

    /**
     * 客製化樹狀結果回傳值
     * @param array $datas 樹狀資料
     * @param object $crudKey 對應預設權限的crud設定值
     */
    public function customToTree($datas, $crudKey)
    {
        $responseData = [];
        foreach ($datas as $item) {
            $data = $item;
            // 刪除不必要的 key
            unset($data["hasManyPermissionUser"]);
            // 回傳 curd 給前端做判斷顯示用
            $data["crud"] = $crudKey[$item["key"]];
            // 判斷是否有子項功能 如果沒有回傳空陣列
            $data["options"] = $options[$item["key"]] ?? [];
            // 判斷是否有子項權限
            if ($item['children']) {
                $data['children'] = $this->customToTree($item['children'], $crudKey);
            }
            $responseData[] = $data;
        }
        return $responseData;
    }

    /**
    * 取得可選擇的預設權限
    * @param string $groupCode 群組代碼
    */
    public function showPermission($groupCode)
    {

        // 取得登入使用者資料
        $user = auth()->user();
        // 取得登入者的 group_code 代碼
        $userGroupCode = $this->user->find($user->user_id)->group->group_code;
        /**
         * 判斷是否為使用者群組
         * --------------------------
         * 此判斷原則
         * 1. 只需將 permission_defaults 表中的 permission_rule 數位權限做判斷
         * 取出目前登入者使用群組可用得群組權限 (使用表 permission_defaults)
         */
        if ($userGroupCode === "SYSTEM") {
            // 數位權限判斷 判斷登入使用者 使用群組 可以查看哪些權限 "&" 符號就是在做 十進制 換算 如果結果是 1 代表有權限
            $permissionDefault = $this->permissionDefault->where('permission_rule', '&', config('app.groupCode.'.$groupCode))->get();
            return $permissionDefault;
        }
        /**
         * 判斷是否為管理者群組
         * --------------------------
         * 此判斷原則
         * 1. 需先抓取出上層管理者資料 (使用表 user_operators)
         * 2. 接著再抓取出上層管理者 群組可用權限 (使用表 permission_groups)
         * 3. 再抓取取出登入者使用的群組可用的權限 (使用表 permission_groups)
         * 4. 並且將登入者群組與上層管理者群組權限做比對 如果有大於上層管理者可用權限時 將會被移除 (使用表 permission_groups)
         * 5. 再將過濾完的群組權限資料中的 permission_id 當搜尋條件 (使用表 permission_groups)
         * 6. 最後將 permission_defaults 表中 的 permission_rule 欄位中 數位權限設定值 取出登入群組身份可用權限
         * 再將剛剛過濾出群組權限可用的 permission_id 當搜尋條件 搜尋出 permission_defaults 表 中可用權限資料 (使用表 permission_defaults)
         */
        if ($userGroupCode === "OPERATOR" && $user->userable->userOperator->parent_id !== null) {
            // 取得上層群組可用權限 id (permission_id)
            $bossPermissionIds = $this->getBossPermission($user->user_id);
            // 取出本身可用群組權限資料 再將上層可用的群組權限資料 當條件 整理出 登入者使用的群組目前可用的權限資料
            $permissionIds = collect($this->permissionGroup->select("permission_id")
                    ->where("group_id", $user->userable->userOperator->user->group_id)
                    ->where("per_read", 1)
                    ->whereIn("permission_id", $bossPermissionIds)->get())
                    ->pluck("permission_id")->all();
            // 數位權限判斷 判斷登入使用者 使用群組 可以查看哪些權限 "&" 符號就是在做 十進制 換算 如果結果是 1 代表有權限
            $permissionDefault = $this->permissionDefault
                ->where('permission_rule', '&', config('app.groupCode.'.$groupCode))
                ->whereIn("id", $permissionIds)->get();
            return $permissionDefault;
        }

        if ($userGroupCode === "OPERATOR") {
            // 取出本身可用群組權限資料 再將上層可用的群組權限資料 當條件 整理出 登入者使用的群組目前可用的權限資料
            $permissionIds = collect($this->permissionGroup->select("permission_id")
                    ->where("group_id", $user->userable->userOperator->user->group_id)
                    ->where("per_read", 1)->get())
                    ->pluck("permission_id")->all();
            $permissionDefault = $this->permissionDefault->where('permission_rule', '&', config('app.groupCode.'.$groupCode))->whereIn("id", $permissionIds)->get();
        }
        /**
         * 數位權限判斷 (不隸屬於 以上兩個使用者群組身份時 且此登入身份為最上層管者時 目前此回傳結果跟 SYSTEM 群組回傳結果一樣)
         * --------------------------
         * 判斷原則
         * 1. 判斷登入使用者 使用群組 可以查看哪些權限 "&" 符號就是在做 十進制 換算 如果結果是 1 代表有權限
         */
        return $permissionDefault;
    }

    /**
     * 取得個人權限 crud 值
     * @param $groupCode 群組代碼
     * @param $userId 需更改的使用者Id
     */
    public function showPermissionCrud($groupCode, $userId)
    {
        // 登入使用者資料
        $user = auth()->user();
        // 登入使用者群組代碼
        $userGroupCode = $user->group->group_code;
        // 取得數位權限中可用得預設權限 id
        $permissionDefaultIds = collect($this->permissionDefault->select("id")->where('permission_rule', '&', config('app.groupCode.'.$groupCode))->get())
        ->pluck("id")->all();
        
        // 用來儲存上層管理者群組權限 crud 資料用
        $bossPermissionGroup = [];
        // 用來儲存回傳資料用
        $responseData = [];

        // 判斷是否為系統使用者
        if ($userGroupCode === "SYSTEM") {
            // 取得個人權限 crud 資料 並過濾數位權限中，屬於該群組可用權限
            $permissionUser = $this->permissionUser->where("user_id", $userId)->whereIn("permission_id", $permissionDefaultIds)->get();
        }
        // 判斷是否為 管理者 且此管理者是否有上層管理者
        if ($userGroupCode === "OPERATOR" && $user->userable->parent_id !== null) {
            // 取得上層群組可用權限 id (permission_id)
            $bossPermissionIds = $this->getBossPermission($user->user_id);
            // 取得個人權限 crud 資料且 per_read 需 = 1，並過濾上層群組可用權限資料(如果是兩層以上的上層會連帶過濾最上層權限資料)，最後在過濾數位權限中，屬於該群組可用權限
            $permissionUser = $this->permissionUser->where("user_id", $userId)->where("per_read", 1)->whereIn("permission_id", $bossPermissionIds)->whereIn("permission_id", $permissionDefaultIds)->get();
            // 取得最上層與上層群組權限 crud 過濾後結果 (防止有上層開啟權限 但最上層沒開啟權限 導致超越最上層權限問題發生)
            $bossPermissionGroup = $this->getBossPermissionCrud($user->user_id, $permissionDefaultIds);
        }
        // 判斷是否為最上層管理者
        if ($userGroupCode === "OPERATOR" && ($user->userable->parent_id === null || $user->group->is_sub === 1)) {
            // 取得登入者群組可用權限 id (permission_id) 預防列出權限超出登入者群組可使用權限(個人權限永遠不能大於群組權限除了系統使用者可以給予超過群組權限的設定值)
            $bossPermissionIds = collect($this->permissionGroup->select("permission_id")->where("group_id", $user->group->group_id)->where("per_read", 1)->whereIn("permission_id", $permissionDefaultIds)->get())->pluck("permission_id")->all();
            // 取得個人權限 crud 資料且 per_read 需 = 1，並過濾上層群組可用權限資料(如果是兩層以上的上層會連帶過濾最上層權限資料)，最後在過濾數位權限中，屬於該群組可用權限
            $permissionUser = $this->permissionUser->where("user_id", $userId)->where("per_read", 1)->whereIn("permission_id", $bossPermissionIds)->whereIn("permission_id", $permissionDefaultIds)->get();
        }

        // 迴圈整理回傳資料 因為需加上 disabled 判斷
        foreach ($permissionUser as $item) {
            // 判斷是否有上層群組權限資料 如果為空 則不執行底下回圈
            if (empty($bossPermissionGroup)) {
                $responseData[] = [
                    "key" => $item["key"],
                    "permission_id" => $item["permission_id"],
                    "options" => $item["options"] !== null ? json_decode($item["options"]) : null,
                    "per_create" => [
                        "disabled" => 0,
                        "value" => $item["per_create"],
                    ],
                    "per_read" => [
                        "disabled" => 0,
                        "value" => $item["per_read"],
                    ],
                    "per_update" => [
                        "disabled" => 0,
                        "value" => $item["per_update"],
                    ],
                    "per_delete" => [
                        "disabled" => 0,
                        "value" => $item["per_delete"],
                    ],
                ];
            } else {
                foreach ($bossPermissionGroup as $bossItem) {
                    if ($item["permission_id"] === $bossItem["permission_id"]) {
                        $responseData[] = [
                            "key" => $item["key"],
                            "group_id" =>  $item["group_id"],
                            "permission_id" => $item["permission_id"],
                            "options" => $item["options"] !== null ? json_decode($item["options"]) : null,
                            "per_create" => [
                                "disabled" => $bossItem["per_create"] === 1 ? 0 : 1,
                                "value" => $item["per_create"],
                            ],
                            "per_read" => [
                                "disabled" => $bossItem["per_read"] === 1 ? 0 : 1,
                                "value" => $item["per_read"],
                            ],
                            "per_update" => [
                                "disabled" => $bossItem["per_update"] === 1 ? 0 : 1,
                                "value" => $item["per_update"],
                            ],
                            "per_delete" => [
                                "disabled" => $bossItem["per_delete"] === 1 ? 0 : 1,
                                "value" => $item["per_delete"],
                            ],
                        ];
                    }
                }
            }
        }
        return ["permissionDefaultIds" => $permissionDefaultIds, "permissionUser" => $permissionUser, "bossPermissionGroup" => $bossPermissionGroup, "responseData" => $responseData];
    }

    /**
     * 比對最上層群組 與上層群組中 crud 的設定值
     * @param string $userId 需查詢的 usrrId
     * @param object $permissionDefaultIds 可使用的預設權限 id
     */
    public function getBossPermissionCrud($userId, $permissionDefaultIds)
    {
        $user = $this->user->find($userId);
        // 抓取登入者上層資料
        $getBoss = $this->userOperator->find($this->user->find($user->user_id)->userable->userOperator->parent_id)->user;
        // 取得上層群組可用權限 id (permission_id)
        $bossPermissionIds = $this->getBossPermission($userId);
        // 取得上層群組權限 crud 資料且per_read = 1，最後在過濾數位權限中，屬於該群組可用權限
        $bossPermissionGroup = $this->permissionGroup->where("group_id", $getBoss->group->group_id)->where("per_read", 1)->whereIn("permission_id", $bossPermissionIds)->whereIn("permission_id", $permissionDefaultIds)->get();
        // 抓取上層管理者 (ancestorsOf 此函數 用來抓取上層 ｜ descendantsOf 此函數用來抓取下層)
        $getTopBoss = $this->userOperator->with("user")->ancestorsOf($this->user->find($user->user_id)->userable->userOperator->operator_id);
        if ($getTopBoss->count() > 1) {
            // 取得最上層使用者資料
            $getRootBoss =$getTopBoss->where("parent_id", null);
            // 取的最上層群組權限 crud 資料且per_read = 1，最後在過濾數位權限中，屬於該群組可用權限
            $rooBossPermissionGroup = $this->permissionGroup->where("group_id", $getRootBoss[0]->user->group_id)->where("per_read", 1)->whereIn("permission_id", $permissionDefaultIds)->get();
        } else {
            $rooBossPermissionGroup = [];
        }
        foreach ($bossPermissionGroup as $item) {
            // 判斷是否有最上層使用者群組設定值，如果沒有，回傳上層群組使用者，群組設定值
            if (empty($rooBossPermissionGroup)) {
                $responseData[] = $item;
            } else {
                foreach ($rooBossPermissionGroup as $rootBossItem) {
                    // 比對最上層與上層群組設定值
                    if ($item["permission_id"] === $rootBossItem["permission_id"]) {
                        // 將 per_create | per_read | per_update | per_delete 值替換成 最上層使用者，群組權限設定值
                        $responseData[] = [
                            "id" => $item["id"],
                            "group_id" => $item["group_id"],
                            "permission_id" => $item["permission_id"],
                            "key" => $item["key"],
                            "per_create" => $rootBossItem["per_create"] === 1 ? $item["per_create"] : $rootBossItem["per_create"],
                            "per_read" => $rootBossItem["per_read"] === 1 ? $item["per_read"] : $rootBossItem["per_read"],
                            "per_update" => $rootBossItem["per_update"] === 1 ? $item["per_update"] : $rootBossItem["per_update"],
                            "per_delete" => $rootBossItem["per_delete"] === 1 ? $item["per_delete"] : $rootBossItem["per_delete"],
                            "created_at" => $item["created_at"],
                            "updated_at" => $item["updated_at"],
                        ];
                    }
                }
            }
        }
        // dd($responseData);
        return $responseData;
    }
    
    /**
     * 取得上層群組可用群組權限資料
     * @param string $userId 需查詢的使用者id
     */
    public function getBossPermission($userId)
    {
        $user = $this->user->find($userId);
        // 抓取登入者上層資料
        $getBoss = $this->userOperator->find($this->user->find($user->user_id)->userable->userOperator->parent_id)->user;
        // 抓取上層管理者 (ancestorsOf 此函數 用來抓取上層 ｜ descendantsOf 此函數用來抓取下層)
        $getTopBoss = $this->userOperator->with("user")->ancestorsOf($this->user->find($user->user_id)->userable->userOperator->operator_id);
        /**
         * 判斷是否有最上層管理者
         * --------------------------
         * 此判斷目的
         * 主要是預防下層管理者 超出最上層管理者可用權限
         */
        if ($getTopBoss->count() > 1) {
            // 取得最上層使用者資料
            $getRootBoss =$getTopBoss->where("parent_id", null);
            /**
             * 取出最上層管理者可用權限 透過 group_id 與 per_read 是否為 1 做判斷 回傳出可用 permission_id (對應 permission_defaults)
             * 並透過 collect 方法 去除 permssion_key 取得最上層使用者群組可用權限資料 中 permission_id 值
             */
            $rootBossPermissionIds = collect($this->permissionGroup->select("permission_id")
            ->where("group_id", $getRootBoss[0]->user->group_id)
            ->where("per_read", 1)->get())
            ->pluck("permission_id")->all();
            /**
             * 取出登入管理者上層可用權限 透過 group_id 與 per_read 是否為 1 做判斷 回傳出可用 permission_id (對應 permission_defaults)
             * 並透過 collect 方法 去除 permssion_key 取得最上層使用者群組可用權限資料 中 permission_id 值
             * 再將此值 當作過濾條件 過濾出 上層管理者中 是否有超出 最上層管理者可用權限 如果有將會被過濾掉
             * 並回傳 剩餘可用的 permission_id 值
             */
            $bossPermissionIds = collect($this->permissionGroup->select("permission_id")
            ->where("group_id", $getBoss->group_id)
            ->where("per_read", 1)
            ->whereIn("permission_id", $rootBossPermissionIds)->get())
            ->pluck("permission_id")->all();
        } else {
            // 取出上層管理者可用權限 透過 group_id 與 per_read 是否為 1 做判斷 回傳出可用 permission_id (對應 permission_defaults)
            $bossPermissionIds = collect($this->permissionGroup->select("permission_id")
                ->where("group_id", $getBoss->group_id)
                ->where("per_read", 1)->get())
                ->pluck("permission_id")->all();
        }
        return $bossPermissionIds;
    }
}
