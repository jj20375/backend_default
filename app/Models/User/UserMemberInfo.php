<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMemberInfo extends Model
{
    use HasFactory;

    protected $table = "user_member_infos"; // 指定資料表名稱
    protected $primaryKey = 'id'; //設定主鍵值欄位

    /**
     * 可儲存或更新資料庫欄位
     *
     * @var array
     */
    protected $fillable = ["member_id", "custom_id", "point"];
    /**
     * 輸出時隱藏欄位
     * @var array
     */
    protected $hidden = ["deleted_at"];

    /**
     * user_member_infos 1對1關聯
     */
    public function userMember()
    {
        return $this->belongsTo("App\Models\User\UserMember", "member_id", "member_id");
    }
}
