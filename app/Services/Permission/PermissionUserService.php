<?php
namespace App\Services\Permission;

// 導入 users 資料庫操作方法
use App\Repositories\Permission\PermissionUserRepository;
// 導入 permission_defaults 資料庫操作方法
use App\Repositories\Permission\PermissionDefaultRepository;
// 導入 users 資料庫操作方法
use App\Repositories\User\UserRepository;
// 導入 logs 資料庫操作方法
use App\Repositories\LogRepository;
// 導入 共用方法
use App\Helpers\FuncHelper;

class PermissionUserService
{

    // PermissionUserRepository Repository 指定變數
    protected $permissionUserRepository;
    // PermissionDefaultRepository Repository 指定變數
    protected $permissionDefaultRepository;
    // UserRepository Repository 指定變數
    protected $userRepository;
    // LogRepository Repository 指定變數
    protected $logRepository;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(PermissionUserRepository $permissionUserRepository, PermissionDefaultRepository $permissionDefaultRepository, UserRepository $userRepository, LogRepository $logRepository, FuncHelper $funcHelper)
    {
        $this->permissionUserRepository = $permissionUserRepository;
        $this->permissionDefaultRepository = $permissionDefaultRepository;
        $this->userRepository = $userRepository;
        $this->logRepository = $logRepository;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增個人權限
     * @param array $datas 新增個人權限資料
     */

    public function create($datas)
    {
        // 儲存權限群組 新增後回傳資料
        $permissionUser = [];
        // 將權限群組 透過迴圈新增 並判斷是否有重複key 有重複 key 就中斷
        foreach ($datas as $index => $item) {
            // 判斷key是否重複
            if ($this->permissionUserRepository->checkDuplicateById("user_id", $item, "key")) {
                return $this->funcHelper->errorBack("key重複", 500);
            }
            // 將新增資料 傳入 permissionUserRepository  執行新增權限方法
            $permissionUser[$index] = $this->permissionUserRepository->create($item);
        }
        // 記錄參數
        $logData = [
            "user_id" => auth()->user()->user_id,
            "type_key" => "permissionUser_create",
            "type_name" => "新增個人權限",
            "req_data" => json_encode($datas),
            "res_data" => json_encode($permissionUser),
        ];
        // 新增記錄
        $this->logRepository->create($logData, $this->userRepository->getData("user_id", $datas[0]["user_id"]));
        return $permissionUser;
    }

    /**
    * 取得選單可用路由
    */
    public function getMenu()
    {
        // 預設權限子項功能
        $options = $this->permissionUserRepository->getMenu()["options"];
        // crud 對應 預設權限 設定值
        $crudKeys = $this->permissionUserRepository->getMenu()["crudKeys"];
        // 登入者可使用權限選單
        $permissionGroup = $this->permissionUserRepository->getMenu()["permissionMenu"]->toTree();
        foreach ($permissionGroup as $item) {
            $data = $item;
            // 刪除不必要的 key
            unset($data["hasManyPermissionUser"]);
            // 對應預設權限 crud 設定值
            $data["crud"] = $crudKeys[$item["key"]];
            // 判斷是否有子項功能如果沒有則回傳空陣列
            $data["options"] = $options[$item["key"]] ?? [];
            // 判斷是否有子項權限
            if ($item["children"]) {
                // 客製化樹狀結果
                $data["children"] = $this->permissionUserRepository->customToTree($item["children"], $crudKeys);
            }
            $responseData[] = $data;
        }
        return $responseData;
    }
    /**
     * 更新個人權限資料
     * @param array $datas 需更新的個人權限資料
     * @param string $userId 需更新的使用者 id
     */
    public function update($datas, $userId)
    {
        // 儲存權限群組 更新後回傳資料
        $permissionUser = [];
        foreach ($datas as $index => $item) {
            // 取得key對應的預設權限資料
            $permissionDefault = $this->permissionDefaultRepository->getData("key", $item["key"]);
            // 更新群組權限
            $permissionUser[$index] = $this->permissionUserRepository->update($item, $userId, $permissionDefault);
        }
        // 記錄參數
        $logData = [
            "user_id" => auth()->user()->user_id,
            "type_key" => "permissionUser_update",
            "type_name" => "更新個人權限",
            "req_data" => json_encode($datas),
            "res_data" => json_encode($permissionUser),
        ];
        // 新增記錄
        $this->logRepository->create($logData, $this->userRepository->getData("user_id", $userId));
        return $permissionUser;
    }

    /**
     * 取得可選擇的預設權限
     * @param string $groupCode 群組代碼
     */
    public function showPermission($groupCode)
    {
        $permssionList = $this->permissionUserRepository->showPermission($groupCode);
        return $permssionList;
    }
    /**
     * 取得可選擇的預設權限樹狀資料
     * @param string $groupCode 群組代碼
     */
    public function showPermissionTree($groupCode)
    {
        $permssionTreeList = $this->permissionUserRepository->showPermission($groupCode)->toTree();
        return $permssionTreeList;
    }
    /**
     * 取得可選擇的預設權限Crud值
     * @param string $groupCode 群組代碼
     * @param string $userId 需更改的使用者Id
     */
    public function showPermissionCrud($groupCode, $userId)
    {
        $permssionCrudList = $this->permissionUserRepository->showPermissionCrud($groupCode, $userId);
        return $permssionCrudList;
    }
}
