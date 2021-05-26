<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserOperatorInfo extends Model
{
    use HasFactory;

    protected $table = "user_operator_infos"; // 指定資料表名稱
    protected $primaryKey = 'id';   //設定主鍵值欄位

    /**
     * 可儲存或更新資料庫欄位
     * @var array
     */
    protected $fillable = ["operator_id", "template_id", "http_type", "domain", "port", "web_name", "logo"];
    /**
     * 輸出時隱藏欄位
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * user_operator_infos 1對1關聯
     */
    public function userOperator()
    {
        return $this->belongsTo("App\Models\User\UserOperator", "operator_id", "operator_id");
    }
}
