<?php

namespace App\Models\Permission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionGroup extends Model
{
    use HasFactory;

    protected $table = "permission_groups"; // 指定資料表名稱
    protected $primaryKey = 'id';   //設定主鍵值欄位
    /**
     * 可用來限制 api 回傳時 指回傳哪些指定 欄位
     * @var array
     */
    // protected $visible = ['key'];
    /**
     * 可儲存或更新資料庫欄位
     * @var array
     */
    protected $fillable = ["group_id", "permission_id", "key", "per_create", "per_read", "per_update", "per_delete", "options"];
    /**
     * 隱藏不必要欄位輸出
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * groups 表 1對多關聯
     */
    public function group() {
        return $this->belongsTo("App\Models\Group", "group_id", "group_id");
    }
    /**
     * permission_defaults 表 1對多關聯
     */
    public function belongsToPermissionDefault() {
        // 第一個參數為用來關聯的 key 
        return $this->belongsTo("App\Models\Permission\PermissionDefault", "id");
    }
}
