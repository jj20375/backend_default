<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    protected $table = "tags"; // 指定資料表名稱
    protected $primaryKey = 'tag_id'; //設定主鍵值欄位

    /**
     * 可儲存或更新資料庫欄位
     *
     * @var array
     */
    protected $fillable = ["name", "key", "permission_rule", "active", "operator_ids"];
    /**
     * 輸出時隱藏欄位
     * @var array
     */
    protected $hidden = ["created_at", "updated_at", "createuser_able_type", "createuser_able_id"];

    
    /**
     * tags 1對多 多態關聯
     */
    public function createuser_able()
    {
        return $this->morphTo();
    }
}
