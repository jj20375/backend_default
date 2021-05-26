<?php

namespace App\Models\Permission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionUser extends Model
{
    use HasFactory;
    protected $table = "permission_users"; // 指定資料表名稱
    protected $primaryKey = 'id';   //設定主鍵值欄位

    /**
     * 可儲存或更新資料庫欄位
     * @var array
     */
    protected $fillable = ["user_id", "permission_id", "key", "per_create", "per_read", "per_update", "per_delete"];
    // 隱藏不必要欄位輸出
    protected $hidden = ["created_at", "updated_at"];

    /**
     * users 表 1對多關聯
     */
    public function user() {
        return $this->belongsTo("App\Models\User\User", "user_id", "user_id");
    }
    /**
     * permission_defaults 表 1對多關聯
     */
    public function belongsToPermissionDefault() {
        // 第一個參數為用來關聯的 key 
        return $this->belongsTo("App\Models\Permission\PermissionDefault", "id");
    }
}
