<?php
namespace App\Services\Permission;

// 導入 users 資料庫操作方法
use App\Repositories\Permission\PermissionDefaultRepository;
// 導入 共用方法
use App\Helpers\FuncHelper;

class PermissionDefaultService
{

    // PermissionDefaultRepository Repository 指定變數
    protected $permissionDefaultRepository;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    
    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(PermissionDefaultRepository $permissionDefaultRepository, FuncHelper $funcHelper)
    {
        $this->permissionDefaultRepository = $permissionDefaultRepository;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增權限
     * @param object $data 新增權限資料
     */

    public function create($data)
    {
        // 判斷帳號是否重複
        if ($this->permissionDefaultRepository->checkDuplicate("key", $data["key"])) {
            return $this->funcHelper->errorBack("key重複", 500);
        }
        // 將新增資料 傳入 PermissionDefaultRepository  執行新增權限方法
        $permissionDefault = $this->permissionDefaultRepository->create($data);
        return $permissionDefault;
    }
    /**
     * 更新權限
     * @param object $data 更新權限資料
     */

    public function update($data)
    {
        $permissionDefault = $this->permissionDefaultRepository->update($data);
        return $permissionDefault;
    }
    /**
     * 刪除權限
     */
    public function delete($permissionId)
    {
        $permissionDefault = $this->permissionDefaultRepository->delete($permissionId);
        return $permissionDefault;
    }
    /**
     * 取得列表
     */
    public function getTreeLists()
    {
        $permissionDefault = $this->permissionDefaultRepository->getLists()->toTree();
        $permissionRule = config("app.groupCode");
        return ["permissionDefault" => $permissionDefault, "permissionRule" => $permissionRule];
    }

    /**
     * 取得預設權限資料
     */
    public function getDataById($permissionId)
    {
        $permissionDefault = $this->permissionDefaultRepository->getData("id", $permissionId);
        // 取的可選擇的群組身份
        $permissionRule = config("app.groupCode");
        // 選中的群組身份
        $selectPermissionRule = collect([]);
        // 將可選擇的群組身份 執行回圈匹配
        foreach ($permissionRule as $key => $value) {
            // 判斷方式採用 數位權限 二進制判斷 如果匹配失敗時 值會等於 0
            if (($permissionDefault->permission_rule & $value) !== 0) {
                $selectPermissionRule->push($value);
            }
        }
        return ["permissionDefault" => $permissionDefault, "selectPermissionRule" => $selectPermissionRule];
    }
}
