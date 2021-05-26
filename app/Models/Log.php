<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = "logs"; // 指定資料表名稱
    protected $primaryKey = 'log_id'; //設定主鍵值欄位
    use HasFactory;

    /**
     * 可儲存或更新資料庫欄位
     *
     * @var array
     */
    protected $fillable = ["user_id", "type_key", "type_name", "req_data", "res_data"];
    /**
     * 輸出時隱藏欄位
     * @var array
     */
    protected $hidden = [];

    /**
     * 對應關聯的資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function log_able()
    {
        return $this->morphTo();
    }
}
