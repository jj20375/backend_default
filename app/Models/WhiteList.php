<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhiteList extends Model
{
    use HasFactory;
    protected $table = "white_lists"; // 指定資料表名稱
    protected $primaryKey = 'white_list_id'; //設定主鍵值欄位
     /**
     * 可儲存或更新資料庫欄位
     *
     * @var array
     */
    protected $fillable = ["lists"];
    /**
     * 輸出時隱藏欄位
     * @var array
     */
    protected $hidden = [];

    /**
     * white_lists(ip 白名單) 1對1 多態關聯
     */
    public function whitelist_able()
    {
        return $this->morphTo();
    }
}
