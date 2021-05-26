<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreService extends Model
{
    use HasFactory;
    protected $table = "services"; // 指定資料表名稱
    protected $primaryKey = 'service_id'; //設定主鍵值欄位

    /**
     * 可儲存或更新資料庫欄位
     *
     * @var array
     */
    protected $fillable = ["category_id", "name", "operator_ids"];
    /**
     * 輸出時隱藏欄位
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * service_infos 1對1 關聯方法
     */
    public function storeServiceInfo()
    {
        return $this->hasOne('App\Models\StoreServiceInfo', 'service_id', 'service_id');
    }
    /**
     * service_ables 多對多多態關聯
     * 獲取 對應的 服務資料
     */
    public function morphByManyUser($ableType)
    {
        return $this->morphedByMany($ableType, 'serviceable', "service_ables", "service_id", "serviceable_id")->withPivot("name");
    }
    /**
     * services 1對1關聯
     */
    public function category()
    {
        return $this->belongsTo("App\Models\Category", "category_id", "category_id");
    }

    /**
     * tag_ables tag多對多多態關聯
     */
    public function morphToManyTag()
    {
        return $this->morphToMany('App\Models\Tag', 'tagable', "tag_ables", "tagable_id", "tag_id");
    }
}
