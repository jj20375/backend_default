<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// 軟刪除套件
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDesigner extends Model
{
    use HasFactory;
    protected $table = "user_designers"; // 指定資料表名稱
    protected $primaryKey = 'designer_id';   //設定主鍵值欄位
    protected $dates = ["deleted_at"]; //建立一個指定變數 指定到該表中 軟刪除欄位

    /**
     * 可儲存或更新資料庫欄位
     * @var array
     */
    protected $fillable = ["operator_id", "name", "account", "nickname", "birthday", "status", "limit", "note"];
    /**
     * 輸出時隱藏欄位
     * @var array
     */
    protected $hidden = [];
    /**
     * user_operators 1對多關聯
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
     * user_desiagners 1對多遠程關聯 user_members表 中間表為 user_operators
     */
    public function userMember()
    {
        return $this->hasManyThrough('App\Models\User\UserMember', "App\Models\User\UserOperator", "operator_id", "member_id");
    }
    /**
     * images表 1對1 多態關聯方法
     */
    public function image()
    {
        return $this->morphOne("App\Models\Image", "imageable");
    }
    /**
     * services 多對多多態關聯
     * 獲取 對應的 服務資料
     */
    public function morphToManyServices()
    {
        return $this->morphToMany('App\Models\StoreService', 'serviceable', "service_ables", "serviceable_id", "service_id")->withPivot("name");
    }
}
