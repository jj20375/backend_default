<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// 無限層級套件
use Kalnoy\Nestedset\NodeTrait;
// 軟刪除套件
use Illuminate\Database\Eloquent\SoftDeletes;

class UserOperator extends Model
{
    use HasFactory, NodeTrait, SoftDeletes;

    protected $table = "user_operators"; // 指定資料表名稱
    protected $primaryKey = 'operator_id';   //設定主鍵值欄位
    protected $dates = ["deleted_at"]; //建立一個指定變數 指定到該表中 軟刪除欄位

    /**
     * 可儲存或更新資料庫欄位
     * @var array
     */
    protected $fillable = ["name", "account", "status", "note"];
    /**
     * 隱藏不必要欄位輸出
     * @var array
     */
    protected $hidden = ["_lft", "_rgt", "deleted_at"];

    /**
     * users表 1對1 多態關聯方法
     */
    public function user()
    {
        return $this->morphOne("App\Models\User\User", "userable");
    }
    /**
     * user_subs表 1對1 多態關聯方法
     */
    public function userSub()
    {
        return $this->morphMany("App\Models\User\UserSub", "subable");
    }
    /**
     * user_assistants表 1對多關聯
     */
    public function userAssistant()
    {
        return $this->hasMany("App\Models\User\UserAssistant", "operator_id", "operator_id");
    }
    /**
     * user_designers表 1對多關聯
     */
    public function userDesigner()
    {
        return $this->hasMany("App\Models\User\UserDesigner", "operator_id", "operator_id");
    }
    /**
     * user_operators 1對多關聯
    */
    public function userOperator()
    {
        return $this->belongsTo("App\Models\User\UserOperator", "operator_id", "operator_id");
    }
    /**
     * user_operator_infos 1對1 關聯方法
     */
    public function userOperatorInfo()
    {
        return $this->hasOne('App\Models\User\UserOperatorInfo', 'operator_id', 'operator_id');
    }
    /**
     * user_members表 1對多關聯
     */
    public function userMember()
    {
        return $this->hasMany("App\Models\User\UserMember", "operator_id", "operator_id");
    }
    /**
     * user_operators 1對1遠程關聯 user_member_infos表 中間表為 user_members
     */
    public function userMemberInfo()
    {
        return $this->hasOneThrough('App\Models\User\UserMemberInfo', "App\Models\User\UserMember", "operator_id", "member_id");
    }
    /**
     * user_operators 1對多遠程關聯 point_orders表 中間表為 user_members
     */
    public function pointOrder()
    {
        return $this->hasManyThrough('App\Models\Order\PointOrder', "App\Models\User\UserMember", "operator_id", "member_id");
    }
    /**
     * groups 1對多 多態關聯方法
     */
    public function group()
    {
        return $this->morphMany("App\Models\Group", "groupable");
    }
    /**
     * tags 1對多 多態關聯方法
     */
    public function tag()
    {
        return $this->morphMany("App\Models\Tag", "createuser_able");
    }
    /**
     * categories 1對多 多態關聯方法
     */
    public function category()
    {
        return $this->morphMany("App\Models\Category", "categoryable");
    }
    /**
     * sms表 1對多關聯
     */
    public function sms()
    {
        return $this->hasMany("App\Models\Sms", "operator_id", "operator_id");
    }
    /**
     * services 多對多多態關聯
     * 獲取 對應的 服務資料
     */
    public function morphToManyServices()
    {
        return $this->morphToMany('App\Models\StoreService', 'serviceable', "service_ables", "serviceable_id", "service_id")->withPivot("name");
    }
    /**
     * white_lists 1對1 多態關聯方法
     */
    public function whiteLists()
    {
        return $this->morphOne("App\Models\WhiteList", "whitelist_able");
    }
}
