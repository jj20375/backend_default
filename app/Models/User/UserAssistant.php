<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAssistant extends Model
{
    use HasFactory;

    protected $table = "user_assistants"; // 指定資料表名稱
    protected $primaryKey = 'assistant_id';   //設定主鍵值欄位
    
    /**
     * 可儲存或更新資料庫欄位
     * @var array
     */
    protected $fillable = ["operator_id", "account", "name", "status", "limit", "note"];
    /**
     * 輸出時隱藏欄位
     * @var array
     */
    protected $hidden = [];

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
     * images表 1對1 多態關聯方法
     */
    public function image()
    {
        return $this->morphOne("App\Models\Image", "imageable");
    }

}
