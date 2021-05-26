<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// 軟刪除套件
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSub extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "user_subs"; // 指定資料表名稱
    protected $primaryKey = 'sub_id';   //設定主鍵值欄位
    protected $dates = ["deleted_at"]; //建立一個指定變數 指定到該表中 軟刪除欄位

    /**
     * 可儲存或更新資料庫欄位
     * @var array
     */
    protected $fillable = ["account", "name", "status"];
    /**
     * 輸出時隱藏欄位
     * @var array
     */
    protected $hidden = ["created_at", "updated_at", "subable_type", "subable_id", "deleted_at"];

    /**
     * users表 1對1 多態關聯方法
     */
    public function user()
    {
        return $this->morphOne("App\Models\User\User", "userable");
    }
    /**
     * 對應關聯的資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function subable()
    {
        return $this->morphTo();
    }

    /**
     * user_operators 1對多關聯
     */
    public function userOperator()
    {
        return $this->belongsTo("App\Models\User\UserOperator", "subable_id", "operator_id");
    }
}
