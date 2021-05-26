<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    
    protected $table = "groups"; // 指定資料表名稱
    protected $primaryKey = 'group_id';   //設定主鍵值欄位
    protected $dates = ["deleted_at"]; //建立一個指定變數 指定到該表中 軟刪除欄位

    /**
     * 可儲存或更新資料庫欄位
     * @var array
     */
    protected $fillable = ["operator_ids", "group_name", "group_code", "is_sub", "permission_rule"];
    // 隱藏不必要欄位輸出
    protected $hidden = ["groupable_type", "groupable_id"];
    
    /**
     * 對應關聯的資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function groupable()
    {
        return $this->morphTo();
    }

    /**
     * permission_groups 表 1對多關聯
     */
    public function permissionGroup()
    {
        return $this->hasMany("App\Models\Permission\PermissionGroup", "group_id", "group_id");
    }
}
