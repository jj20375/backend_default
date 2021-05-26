<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// 軟刪除套件
use Illuminate\Database\Eloquent\SoftDeletes;

class UserMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "user_members"; // 指定資料表名稱
    protected $primaryKey = 'member_id'; //設定主鍵值欄位
    protected $dates = ["deleted_at"]; //建立一個指定變數 指定到該表中 軟刪除欄位

    /**
     * 可儲存或更新資料庫欄位
     *
     * @var array
     */
    protected $fillable = ["operator_id", "account", "name", "nickname", "status", "birthday", "phone", "phone2", "sendSmsActive", "note"];
    /**
     * 輸出時隱藏欄位
     * @var array
     */
    protected $hidden = ["deleted_at"];

    /**
     * user_member_infos 1對1 關聯方法
     */
    public function userMemberInfo()
    {
        return $this->hasOne('App\Models\User\UserMemberInfo', 'member_id', 'member_id');
    }
    /**
     * user_operator 1對多關聯
     */
    public function userOperator()
    {
        return $this->belongsTo("App\Models\User\UserOperator", "operator_id", "operator_id");
    }
    /**
     * users表 1對1 多態關聯方法
     */
    public function user()
    {
        return $this->morphOne("App\Models\User\User", "userable");
    }

    /**
     * point_orders 1對多 關聯方法
     */
    public function pointOrder()
    {
        return $this->hasMany('App\Models\Order\PointOrder', 'member_id', 'member_id');
    }
}
