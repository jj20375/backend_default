<?php
namespace App\Repositories\User;

// user_member_infos表 model
use App\Models\User\UserMemberInfo;
// 導入 共用方法
use App\Helpers\FuncHelper;

class UserMemberInfoRepository
{

    // UserMemberInfo Model 指定變數
    protected $userMemberInfo;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(UserMemberInfo $userMemberInfo, FuncHelper $funcHelper)
    {
        $this->userMemberInfo = $userMemberInfo;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增會員詳細資料
     * @param object $data 新增會員詳細資料
     */
    public function create($data)
    {
        $userMemberInfo = new $this->userMemberInfo;
        $userMemberInfo->fill($data);
        $userMemberInfo->save();
        return $userMemberInfo->fresh();
    }
    /**
     * 更新會員詳細資料
     * @param object $data 新增會員詳細資料
     */
    public function update($data)
    {
        $user = auth()->user();
        // 用來判斷是否能更新
        $canUpdate = false;
        // 如果是系統使用者 可以更新
        if ($user->group->group_code === "SYSTEM") {
            $canUpdate = true;
        } else {
            // 取得管理者自行創建會員資料 如果為空陣列 代表此會員非此管理者創建的
            $checkMemberCreate = auth()->user()->userable->userOperator->userMemberInfo()->where("id", $data["id"])->get();
            // 判斷是否為管理者自行創建的會員 如果不是管理者自行創建的會員 則無法更新
            if (!empty($checkMemberCreate->toArray())) {
                $canUpdate = true;
            }
        }
        // 判斷是否能更新
        if ($canUpdate) {
            $userMemberInfo = $this->userMemberInfo->find($data["id"]);
            $userMemberInfo->fill($data);
            $userMemberInfo->save();
            return $userMemberInfo->fresh();
        }
        return false;
    }
    /**
     * 取得資料
     * @param string $column 資料表欄位
     * @param string $data 欄位資料
     */
    public function getData($column, $data)
    {
        // 取得登入者群取身份
        $userGroupCode = auth()->user()->group->group_code;
        // 判斷是否為系統身份
        if ($userGroupCode === "SYSTEM") {
            // 如果為系統身份時 無需過慮該會員是否為 管理者自行創建的
            $userMemberInfo = $this->userMemberInfo->with(["userMember.user", "userMember.userOperator"])->where($column, $data)->first();
            return $userMemberInfo;
        } else {
            // 如果非系統身份時 只能查詢管理者自己創建的會員資料
            $userMemberIds = auth()->user()->userable->userOperator->userMember->pluck("member_id");
            $userMemberInfo = $this->userMemberInfo->with(["userMember.user", "userMember.userOperator"])->where($column, $data)->whereIn("member_id", $userMemberIds)->first();
            return $userMemberInfo;
        }
    }
}
