<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{
    use HasFactory;
    protected $table = "sms"; // 指定資料表名稱
    protected $primaryKey = "sms_id"; //設定主鍵值欄位

    /**
     * 可儲存或更新資料庫欄位
     *
     * @var array
     */
    protected $fillable = ["operator_id", "operator_ids", "key", "name", "httpType", "url", "status", "key_data"];
    /**
     * 輸出時隱藏欄位
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];
    
    /**
     * user_operators 1對多關聯
     */
    public function userOperator() {
        return $this->belongsTo("App\Models\User\UserOperator", "operator_id", "operator_id");
    }

}
