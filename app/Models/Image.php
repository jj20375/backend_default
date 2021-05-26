<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    protected $table = "images"; // 指定資料表名稱
    protected $primaryKey = 'image_id'; //設定主鍵值欄位

    /**
     * 可儲存或更新資料庫欄位
     *
     * @var array
     */
    protected $fillable = ["img_path"];
    /**
     * 輸出時隱藏欄位
     * @var array
     */
    protected $hidden = ["imageable_type", "imageable_id"];

    /**
     * 對應關聯的資料
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function imageable()
    {
        return $this->morphTo();
    }

}
