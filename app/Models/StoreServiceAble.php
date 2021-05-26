<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreServiceAble extends Model
{
    use HasFactory;
    protected $table = "service_ables"; // 指定資料表名稱
    protected $primaryKey = 'id'; //設定主鍵值欄位

    /**
     * 可儲存或更新資料庫欄位
     *
     * @var array
     */
    protected $fillable = ["name"];
    /**
     * 輸出時隱藏欄位
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * 對應關聯的資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function serviceable()
    {
        return $this->morphTo();
    }
    /**
     * services 1對1關聯
     */
    public function storeService()
    {
        return $this->belongsTo("App\Models\StoreService", "service_id", "service_id");
    }
    /**
     * 關聯管理者
     * 1對１關聯
     */
    public function operator()
    {
        return $this->belongsTo("App\Models\User\UserOperator", "serviceable_id", "operator_id");
    }
    /**
     * 關聯服務提供者
     * 1對１關聯
     */
    public function designer()
    {
        return $this->belongsTo("App\Models\User\UserDesigner", "serviceable_id", "designer_id");
    }
}
