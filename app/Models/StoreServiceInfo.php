<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreServiceInfo extends Model
{
    use HasFactory;
    protected $table = "service_infos"; // 指定資料表名稱
    protected $primaryKey = 'id'; //設定主鍵值欄位

    /**
     * 可儲存或更新資料庫欄位
     *
     * @var array
     */
    protected $fillable = ["service_id", "price", "operator_ids"];
    /**
     * 輸出時隱藏欄位
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * service_infos 1對1關聯
     */
    public function storeService()
    {
        return $this->belongsTo("App\Models\StoreService", "service_id", "id");
    }
}
