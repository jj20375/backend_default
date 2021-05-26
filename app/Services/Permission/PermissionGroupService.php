<?php
namespace App\Services\Permission;

// 導入 permission_groups 資料庫操作方法
use App\Repositories\Permission\PermissionGroupRepository;
// 導入 permission_defaults 資料庫操作方法
use App\Repositories\Permission\PermissionDefaultRepository;
// 導入 logs 資料庫操作方法
use App\Repositories\LogRepository;
// 導入 group 資料操作方法
use App\Repositories\GroupRepository;
// 導入 共用方法
use App\Helpers\FuncHelper;

class PermissionGroupService
{

    // PermissionGroupRepository Repository 指定變數
    protected $permissionGroupRepository;
    // PermissionDefaultRepository Repository 指定變數
    protected $permissionDefaultRepository;
    // LogRepository Repository 指定變數
    protected $logRepository;
    // GroupRepository Repository 指定變數
    protected $groupRepository;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(PermissionGroupRepository $permissionGroupRepository, PermissionDefaultRepository $permissionDefaultRepository, LogRepository $logRepository, GroupRepository $groupRepository, FuncHelper $funcHelper)
    {
        $this->permissionGroupRepository = $permissionGroupRepository;
        $this->permissionDefaultRepository = $permissionDefaultRepository;
        $this->groupRepository = $groupRepository;
        $this->logRepository = $logRepository;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增權限群組
     * @param array $datas 新增權限群組資料
     */

    public function create($datas)
    {
        // 儲存權限群組 新增後回傳資料
        $permissionGroup = [];
        // 將權限群組 透過迴圈新增 並判斷是否有重複key 有重複 key 就中斷
        foreach ($datas as $index => $item) {
            // 判斷key是否重複
            if ($this->permissionGroupRepository->checkDuplicateById("group_id", $item, "key")) {
                return $this->funcHelper->errorBack("key重複", 500);
            }
            // 取得key對應的預設權限資料
            $permissionDefault = $this->permissionDefaultRepository->getData("key", $item["key"]);
            // 將新增資料 傳入 PermissionGroupRepository  執行新增權限方法
            $permissionGroup[$index] = $this->permissionGroupRepository->create($item, $permissionDefault);
        }
        // 記錄參數
        $logData = [
            "user_id" => auth()->user()->user_id,
            "type_key" => "permissionGroup_create",
            "type_name" => "新增群組權限",
            "req_data" => json_encode($datas),
            "res_data" => json_encode($permissionGroup),
        ];
        // 新增記錄
        $this->logRepository->create($logData,$this->groupRepository->getData($datas[0]["group_id"]));
        return $permissionGroup;
    }

    /**
     * 更新群組權限資料
     * @param array $datas 需更新的群組權限資料
     * @param string $groupId 需更新的群組 id
     */
    public function update($datas, $groupId)
    {
       
        // 儲存權限群組 更新後回傳資料
        $permissionGroup = [];
        foreach ($datas as $index => $item) {
            // 取得key對應的預設權限資料
            $permissionDefault = $this->permissionDefaultRepository->getData("key", $item["key"]);
            // 更新群組權限
            $permissionGroup[$index] = $this->permissionGroupRepository->update($item, $groupId, $permissionDefault);
            // 判斷是否有權限更新 沒權限則回傳更新失敗
            if ($permissionGroup[$index] === false) {
                return $this->funcHelper->errorBack("更新失敗", 500);
            }
        }
        // 記錄參數
        $logData = [
            "user_id" => auth()->user()->user_id,
            "type_key" => "permissionGroup_update",
            "type_name" => "更新群組權限",
            "req_data" => json_encode($datas),
            "res_data" => json_encode($permissionGroup),
        ];
        // 新增記錄
        $this->logRepository->create($logData,$this->groupRepository->getData($groupId));
        return $permissionGroup;
    }

    /**
     * 取得選單可用路由
     */
    public function getMenu()
    {
        // 預設權限子項功能
        $options = $this->permissionGroupRepository->getMenu()["options"];
        // crud 對應 預設權限 設定值
        $crudKeys = $this->permissionGroupRepository->getMenu()["crudKeys"];
        // 登入者可使用權限選單
        $permissionGroup = $this->permissionGroupRepository->getMenu()["permissionMenu"]->toTree();
        foreach ($permissionGroup as $item) {
            $data = $item;
            // 刪除不必要的 key
            unset($data["hasManyPermissionGroup"]);
            // 對應預設權限 crud 設定值
            $data["crud"] = $crudKeys[$item["key"]];
            // 判斷是否有子項功能如果沒有則回傳空陣列
            $data["options"] = $options[$item["key"]] ?? [];
            // 判斷是否有子項權限
            if ($item["children"]) {
                // 客製化樹狀結果
                $data["children"] = $this->permissionGroupRepository->customToTree($item["children"], $crudKeys, $options);
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
        $permssionList = $this->permissionGroupRepository->showPermission($groupCode);
        return $permssionList;
    }
    /**
     * 取得可選擇的預設權限樹狀資料
     * @param string $groupCode 群組代碼
     */
    public function showPermissionTree($groupCode)
    {
        $permssionTreeList = $this->permissionGroupRepository->showPermission($groupCode)->toTree();
        return $permssionTreeList;
    }
    /**
     * 取得可選擇的預設權限Crud值
     * @param string $groupCode 群組代碼
     * @param string $groupId 需更改的群組Id
     */
    public function showPermissionCrud($groupCode, $groupId)
    {
        $permssionCrudList = $this->permissionGroupRepository->showPermissionCrud($groupCode, $groupId);
        return $permssionCrudList;
    }
}
