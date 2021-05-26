<?php

namespace App\Models\User;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// 軟刪除套件
use Illuminate\Database\Eloquent\SoftDeletes;
// jwt 驗證套件
use Tymon\JWTAuth\Contracts\JWTSubject;
// 導入 共用方法
use App\Helpers\FuncHelper;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = "users"; // 指定資料表名稱
    protected $primaryKey = 'user_id';   //設定主鍵值欄位
    protected $dates = ["deleted_at"]; //建立一個指定變數 指定到該表中 軟刪除欄位

    /**
     * 可儲存或更新資料庫欄位
     *
     * @var array
     */
    protected $fillable = [
        "group_id",
        'account',
        'password',
        'remarks',
        "login_time",
        "last_ip",
        "create_ip",
        "token",
        "token_time",
        "lang",
        "open_user_permission",
    ];

    /**
     * 輸出時隱藏欄位
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', "token", "userable_type", "userable_id"];

    /**
     * 自動轉換儲存格式
     *
     * @var array
     */
    protected $casts = [
        // 'login_time' => "datetime",
    ];

    /**
     * 確認使用者身份
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Jwt 回傳對應使用者資料
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        $guard = FuncHelper::getGuard();
        return ["role" => $guard];
    }

    /**
     * 對應關聯的資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function userable()
    {
        return $this->morphTo();
    }
    
    /**
     * groups表 1對多 關聯查詢
     */

    public function group()
    {
        return $this->belongsTo("App\Models\Group", "group_id");
    }

    /**
     * permission_users 表 1對多關聯
     */
    public function permissionUser()
    {
        return $this->hasMany("App\Models\Permission\PermissionUser", "user_id", "user_id");
    }
}
