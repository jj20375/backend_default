<?php
namespace App\Repositories\Permission;

// 權限全部資料 model
use App\Models\Permission\PermissionDefault;

class PermissionDefaultRepository
{

    // PermissionDefault Model 指定變數
    protected $permissionDefault;

    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(PermissionDefault $permissionDefault)
    {
        $this->permissionDefault = $permissionDefault;
    }

    /**
     * 新增權限
     * @param object $data 新增權限資料
     */
    public function create($data)
    {
        $permissionDefault = new $this->permissionDefault;
        $permissionDefault->fill($data);
        // 判斷是否為最上層路由權限 因為儲存涵式不同 parent_id = 0 代表最上層
        if ($data['parent_id']==0) {
            $permissionDefault->saveAsRoot();
        } else {
            $permissionDefault->parent_id = $data['parent_id'];
        }
        $permissionDefault->save();
        return $permissionDefault->fresh();
    }
    /**
     * 更新權限
     * @param object $data 更新權限資料
     */
    public function update($data)
    {
        $permissionDefault = $this->permissionDefault->find($data["id"]);
        $permissionDefault->fill($data);
        $permissionDefault->save();
        return $permissionDefault->fresh();
    }
    /**
     * 刪除權限
     */
    public function delete($permissionId)
    {
        $permissionDefault = $this->permissionDefault->destroy($permissionId);
        return $permissionDefault;
    }
    /**
     * 判斷指定欄位是否有重複資料
     * @param string $column 欄位名稱
     * @param string $data 欄位資料
     */
    public function checkDuplicate($column, $data)
    {
        // 取得預設權限資料表 第一筆 比對成功資料
        $permissionDefault = $this->permissionDefault->where($column, $data)->first();
        // 如果有資料代表
        if (isset($permissionDefault)) {
            return true;
        }
        return false;
    }

    /**
     * 取得列表
     */
    public function getLists()
    {
        $permissionDefault = $this->permissionDefault->all();
        return $permissionDefault;
    }

    /**
     * 取得第一筆比對成功資料
     * @param string $column 欄位名稱
     * @param string $data 欄位資料
     */
    public function getData($column, $data)
    {
        // 取得預設權限資料表 第一筆 比對成功資料
        $permissionDefault = $this->permissionDefault->where($column, $data)->first();
        return $permissionDefault;
    }
}
