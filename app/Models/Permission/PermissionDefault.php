<?php

namespace App\Models\Permission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// 無限層級套件
use Kalnoy\Nestedset\NodeTrait;

class PermissionDefault extends Model
{
    use HasFactory, NodeTrait;

    protected $table = "permission_defaults"; // 指定資料表名稱
    protected $primaryKey = 'id';   //設定主鍵值欄位

    /**
     * 可儲存或更新資料庫欄位
     * @var array
     */
    protected $fillable = ["permission_rule", "key", "custom_key", "str", "is_menu", "is_option", "have_options", "route_set"];
    /**
     * 隱藏不必要欄位輸出
     * @var array
     */
    protected $hidden = ["_lft", "_rgt"];

    /**
     * permission_defaults 表 1對多關聯
     */
    public function hasManyPermissionGroup()
    {
        // 第一個參數為用來關聯的 key 
        return $this->hasMany("App\Models\Permission\PermissionGroup", "permission_id");
    }
    /**
     * permission_defaults 表 1對多關聯
     */
    public function hasManyPermissionUser()
    {
        // 第一個參數為用來關聯的 key 
        return $this->hasMany("App\Models\Permission\PermissionUser", "permission_id");
    }
}
