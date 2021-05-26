<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// 軟刪除套件
use Illuminate\Database\Eloquent\SoftDeletes;

class PointOrder extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "point_orders"; // 指定資料表名稱
    protected $primaryKey = 'point_order_id';   //設定主鍵值欄位
    protected $dates = ["deleted_at"]; //建立一個指定變數 指定到該表中 軟刪除欄位

    /**
     * 可儲存或更新資料庫欄位
     *
     * @var array
     */
    protected $fillable = ["order_number", "member_id", "operator_id", "user_id", "before_point", "after_point", "point", "remarks"];
    /**
     * 輸出時隱藏欄位
     * @var array
     */
    protected $hidden = ["deleted_at"];
    
    /**
     * user_members 1對多 對應關聯的資料
     */
    public function userMember()
    {
        return $this->belongsTo("App\Models\User\UserMember", "member_id", "member_id");
    }
    /**
     * user_members 1對多 對應關聯的資料
     */
    public function userOperator()
    {
        return $this->belongsTo("App\Models\User\UserOperator", "operator_id", "operator_id");
    }
    /**
     * users 1對多 對應關聯的資料
     */
    public function user()
    {
        return $this->belongsTo("App\Models\User\User", "user_id", "user_id");
    }
}
