<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// 軟刪除套件
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSystem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "user_systems"; // 指定資料表名稱
    protected $primaryKey = 'system_id';   //設定主鍵值欄位
    protected $dates = ["deleted_at"]; //建立一個指定變數 指定到該表中 軟刪除欄位

    /**
     * 可儲存或更新資料庫欄位
     * @var array
     */
    protected $fillable = ["account", "name", "status", "note"];
    /**
     * 隱藏不必要欄位輸出
     * @var array
     */
    protected $hidden = [];
    
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
        return $this->morphOne("App\Models\User\UserSub", "subable");
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
     * white_lists 1對1 多態關聯方法
     */
    public function whiteLists()
    {
        return $this->morphOne("App\Models\WhiteList", "whitelist_able");
    }
}
