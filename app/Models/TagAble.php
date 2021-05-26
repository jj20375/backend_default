<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagAble extends Model
{
    use HasFactory;
    protected $table = "tag_ables"; // 指定資料表名稱
    public $timestamps = false;

    /**
     * 可儲存或更新資料庫欄位
     *
     * @var array
     */
    protected $fillable = ["tag_id", "tagable_type", "tagable_id"];


    /**
     * 對應關聯的資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function tagable()
    {
        return $this->morphTo();
    }

    /**
     * services 1對1關聯
     */
    public function tag()
    {
        return $this->belongsTo("App\Models\Tag", "tag_id", "tag_id");
    }
    /**
     * 關聯services表
     * 1對１關聯
     */
    public function storeService()
    {
        return $this->belongsTo("App\Models\StoreService", "tagable_id", "service_id");
    }
}
