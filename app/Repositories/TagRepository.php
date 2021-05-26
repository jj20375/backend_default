<?php
namespace App\Repositories;

// tags表 model
use App\Models\Tag;
// tag_ables表 model
use App\Models\TagAble;
// UserOperator表 model
use App\Models\User\UserOperator;
// 導入 共用方法
use App\Helpers\FuncHelper;

class TagRepository
{

    // Tag Model 指定變數
    protected $tag;
    // TagAble Model 指定變數
    protected $tagAble;
    // UserOperator Model 指定變數
    protected $userOperator;
    // 導入共用方法 指定變數
    protected $funcHelper;

    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(Tag $tag, TagAble $tagAble, UserOperator $userOperator, FuncHelper $funcHelper)
    {
        $this->tag = $tag;
        $this->tagAble = $tagAble;
        $this->userOperator = $userOperator;
        $this->funcHelper = $funcHelper;
    }
    /**
     * 新增標籤
     * @param object 新增標籤資料
     * @param object 多態關聯表資料
     */
    public function create($data, $ableOrm)
    {
        $tag = new $this->tag;
        $tag->createuser_able()->associate($ableOrm);
        $tag->fill($data);
        $tag->save();
        return $tag;
    }
    /**
     * 更新標籤
     * @param object 新增標籤資料
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
            // 取得管理者自行創建標籤資料 如果為空陣列 代表此標籤非此管理者創建的
            $checkTagCreateUser = $user->userable->userOperator->tag()->where("tag_id", $data["tag_id"])->get();
            // 判斷是否為管理者自行創建的標籤 如果不是管理者自行創建的標籤 則無法更新
            if (!empty($checkTagCreateUser->toArray())) {
                $canUpdate = true;
            }
        }
        // 判斷是否能更新
        if ($canUpdate) {
            $tag = $this->tag->find($data["tag_id"]);
            $tag->fill($data);
            $tag->save();
            return $tag->fresh();
        }
        return false;
    }

    /**
     * 取得列表
     * @param object $data 分頁搜尋過濾條件
     */
    public function getLists($data = null)
    {
        // 取得登入者資料
        $user = auth()->user();
        // 預設如果沒有傳送一頁呈現幾筆資料的話 會出現15筆
        $perPage = $data["perPage"] ?? 15;
        // 刪除perPage key 此 key 只是用來判斷一頁要呈現幾筆資料而已
        unset($data["perPage"]);
        // 判斷是否為系統使用者
        if ($user->group->group_code === "SYSTEM") {
            $tagLists = $this->tag->with(["createuser_able"]);
        } else {
            // 回傳登入者自行建立的標籤列表 group_id 值
            $tagIds = $user->userable->userOperator->tag->pluck("tag_id");
            // 取出登入者可用標籤列表以及系統建立的標籤列表 且系統建立的群列表中如果有包含系統標籤時，需過濾系統標籤
            $tagLists = $this->tag->with(["createuser_able"])
            // 取出登入者創建的標籤
            ->whereIn("tag_id", $tagIds)
            // 取出系統串間的標籤
            ->orWhereHasMorph("createuser_able", ["App\Models\User\UserSystem"], function ($query) use ($user) {
                // 取出系統創建指定登入管理者可用標籤
                $query->whereJsonContains("operator_ids", $user->userable->userOperator->operator_id)
                // 或者取出 operator_ids 值為 null 的標籤 (如果為null 代表為共用標籤) 以及對應的標籤代碼 並且非子帳號專用標籤
                ->orWhere("operator_ids", null)
                // 或者取出 operator_ids 值為 [](空陣列) 的標籤 (如果為 [](空陣列) 代表為共用標籤) 以及對應的標籤代碼 並且非子帳號專用標籤
                ->orWhereJsonLength("operator_ids", 0);
            });
        }
        // 將標籤列表傳入過濾參數
        $responseData = $this->funcHelper->toWhereSort($tagLists, $data)->paginate($perPage);
        // 將標籤列表啟用分頁
        return $responseData;
    }
    /**
     * 取得可使用的列表
     * @param string $operatorId 管理者id
     * @param string $tagType 標籤類型
     */
    public function getSelectLists($tagType, $operatorId)
    {
        // 登入者標籤代碼
        $userGroupCode = auth()->user()->group->group_code;
        // 如果是新增系統使用者時 需傳入 0
        if ($operatorId != 0) {
            // 取出屬於該管理者自己創建的標籤id
            $tagIds = $this->userOperator->find($operatorId)->tag->pluck("tag_id");
        } else {
            $tagIds = [];
        }
        /**
         * 新增系統使用者時使用
         * 判斷管理者id 是否傳入 0
         * 判斷系統標籤代碼 是否為 SYSTEM
         * 判斷登入者是否為系統身份
         */
        if ($operatorId == 0 && $userGroupCode === "SYSTEM") {
            // 過濾 數位權限
            $tagLists = $this->tag->where('permission_rule', '&', config('app.tagType.'.$tagType))->get();
            return $tagLists;
        }
        /**
         * 此判斷式主要用來取出
         * 1.屬於登入的管理者創建的標籤
         * 2.屬於系統創建的標籤 且有指定管理者可以使用
         * 3.屬於共用標籤也就是值為 [](空陣列) 或 null
         */
        $tagLists = $this->tag
        // 標籤類別
        ->where('permission_rule', '&', config('app.tagType.'.$tagType))
        // 過濾標籤 id
        ->whereIn("tag_id", $tagIds)
        // 取出系統創建的標籤
        ->orWhereHasMorph("createuser_able", ["App\Models\User\UserSystem"], function ($query) use ($operatorId, $tagType) {
            // 取出登入者可用標籤 以及 判斷是否為對應標籤代碼可用的標籤
            $query->whereJsonContains("operator_ids", (int)$operatorId)->where('permission_rule', '&', config('app.tagType.'.$tagType))
            // 或者取出 operator_ids 值為 null 的標籤 (如果為null 代表為共用標籤) 以及對應的標籤代碼 並且非子帳號專用標籤
            ->orWhere("operator_ids", null)->where('permission_rule', '&', config('app.tagType.'.$tagType))
            // 或者取出 operator_ids 值為 [](空陣列) 的標籤 (如果為 [](空陣列) 代表為共用標籤) 以及對應的標籤代碼 並且非子帳號專用標籤
            ->orWhereJsonLength("operator_ids", 0)->where('permission_rule', '&', config('app.tagType.'.$tagType));
        })->get();
        return $tagLists;
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
            // 如果為系統身份時 無需過慮該標籤是否為 管理者自行創建的
            $tag = $this->tag->where($column, $data)->first();
            return $tag;
        } else {
            // 如果非系統身份時 只能查詢管理者自己創建的標籤資料
            $tagIds = auth()->user()->userable->userOperator->tag->pluck("tag_id");
            $tag = $this->tag->where($column, $data)->whereIn("tag_id", $tagIds)->first();
            return $tag;
        }
    }
    /**
     * 用id 取的資料
     * @param string $tagId 標籤id
     */
    public function getDataById($tagId)
    {
        $tag = $this->tag->find($tagId);
        return $tag;
    }
    /**
    * 判斷指定欄位是否有重複資料
    * @param string $column 欄位名稱
    * @param string $data 欄位資料
    */
    public function checkDuplicate($column, $data)
    {
        // 判斷是否有重複值
        $tag = $this->tag->where($column, $data)->first();
        // 如果有資料代表
        if (isset($tag)) {
            return true;
        }
        return false;
    }
    /**
     * 更新 tag_ables 多對多多態表
     */
    public function updateAble($tagId, $ableType, $ableId)
    {
        // dd($ableId, $ableType, $tagId);
        $tag = $this->tagAble->updateOrCreate(["tagable_id" => $ableId, "tag_id" => $tagId], ["tag_id" => $tagId, "tagable_type" => $ableType, "tagable_id" => $ableId]);
        return $tag;
    }
}
