<?php
namespace App\Repositories;

// categories表 model
use App\Models\Category;
// UserOperator表 model
use App\Models\User\UserOperator;
// 導入 共用方法
use App\Helpers\FuncHelper;

class CategoryRepository
{
    // Category Model 指定變數
    protected $category;
    // UserOperator Model 指定變數
    protected $userOperator;
    // 導入共用方法 指定變數
    protected $funcHelper;

    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(Category $category, UserOperator $userOperator, FuncHelper $funcHelper)
    {
        $this->category = $category;
        $this->userOperator = $userOperator;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增分類
     * @param object $data 新增分類資料
     * @param object $categoryableOrm 多態關聯表資料
     */
    public function create($data, $categoryableOrm)
    {
        $category = new $this->category;
        $category->categoryable()->associate($categoryableOrm);
        $category->fill($data);
        $category->save();
        return $category->fresh();
    }
    /**
     * 更新分類
     * @param object $data 更新分類資料
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
            // 取得管理者自行創建分類資料 如果為空陣列 代表此分類非此管理者創建的
            $checkCategoryCreateUser = auth()->user()->userable->userOperator->category()->where("category_id", $data["category_id"])->get();
            // 判斷是否為管理者自行創建的分類 如果不是管理者自行創建的分類 則無法更新
            if (!empty($checkCategoryCreateUser->toArray())) {
                $canUpdate = true;
            }
        }
        // 判斷是否能更新
        if ($canUpdate) {
            $category = $this->category->find($data["category_id"]);
            $category->fill($data);
            $category->save();
            return $category->fresh();
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
            $categoryLists = $this->category->with(["categoryable"]);
        } else {
            // 回傳登入者自行建立的分類列表 category_id 值
            $categoryIds = $user->userable->userOperator->category->pluck("category_id");
            // 取出登入者可用分類列表以及系統建立的分類列表 且系統建立的群列表中如果有包含系統分類時，需過濾系統分類
            $categoryLists = $this->category->with(["categoryable"])
            // 取出登入者創建的分類
            ->whereIn("category_id", $categoryIds)
            // 取出系統串間的分類
            ->orWhereHasMorph("categoryable", ["App\Models\User\UserSystem"], function ($query) use ($user) {
                // 取出系統創建指定登入管理者可用分類
                $query->whereJsonContains("operator_ids", $user->userable->userOperator->operator_id)
                // 或者取出 operator_ids 值為 null 的分類 (如果為null 代表為共用分類) 以及對應的分類代碼 並且非子帳號專用分類
                ->orWhere("operator_ids", null)
                // 或者取出 operator_ids 值為 [](空陣列) 的分類 (如果為 [](空陣列) 代表為共用分類) 以及對應的分類代碼 並且非子帳號專用分類
                ->orWhereJsonLength("operator_ids", 0);
            });
        }
        // 將分類列表傳入過濾參數
        $responseData = $this->funcHelper->toWhereSort($categoryLists, $data)->paginate($perPage);
        // 將分類列表啟用分頁
        return $responseData;
    }
    /**
     * 取得可使用的列表
     * @param string $operatorId 管理者id
     * @param string $categoryType 分類類型
     */
    public function getSelectLists($categoryType, $operatorId)
    {
        // 登入者群組代碼
        $userGroupCode = auth()->user()->group->group_code;
        // 如果是新增系統使用者時 需傳入 0
        if ($operatorId != 0) {
            // 取出屬於該管理者自己創建的分類id
            $categoryIds = $this->userOperator->find($operatorId)->category->pluck("category_id");
        } else {
            $categoryIds = [];
        }
        /**
         * 新增系統使用者時使用
         * 判斷管理者id 是否傳入 0
         * 判斷系統分類代碼 是否為 SYSTEM
         * 判斷登入者是否為系統身份
         */
        if ($operatorId == 0 && $userGroupCode === "SYSTEM") {
            // 過濾 數位權限
            $categoryLists = $this->category->where('permission_rule', '&', config('app.categoryType.'.$categoryType))->get();
            return $categoryLists;
        }
        /**
         * 此判斷式主要用來取出
         * 1.屬於登入的管理者創建的分類
         * 2.屬於系統創建的分類 且有指定管理者可以使用
         * 3.屬於共用分類也就是值為 [](空陣列) 或 null
         */
        $categoryLists = $this->category
        // 分類類別
        ->where('permission_rule', '&', config('app.categoryType.'.$categoryType))
        // 過濾分類 id
        ->whereIn("category_id", $categoryIds)
        // 判斷是否有啟用分類
        ->where("active", 1)
        // 取出系統創建的分類
        ->orWhereHasMorph("categoryable", ["App\Models\User\UserSystem"], function ($query) use ($operatorId, $categoryType) {
            // 取出登入者可用分類 以及 判斷是否為對應分類代碼可用的分類
            $query->whereJsonContains("operator_ids", (int)$operatorId)->where('permission_rule', '&', config('app.categoryType.'.$categoryType))
            // 或者取出 operator_ids 值為 null 的分類 (如果為null 代表為共用分類) 以及對應的分類數位權限 判斷此分類只可給哪些種類使用
            ->orWhere("operator_ids", null)->where('permission_rule', '&', config('app.categoryType.'.$categoryType))
            // 或者取出 operator_ids 值為 [](空陣列) 的分類 (如果為 [](空陣列) 代表為共用分類) 以及對應的分類數位權限 判斷此分類只可給哪些種類使用
            ->orWhereJsonLength("operator_ids", 0)->where('permission_rule', '&', config('app.categoryType.'.$categoryType));
        })->where("active", 1)->get();
        return $categoryLists;
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
            // 如果為系統身份時 無需過慮該分類是否為 管理者自行創建的
            $category = $this->category->where($column, $data)->first();
            return $category;
        } else {
            // 如果非系統身份時 只能查詢管理者自己創建的分類資料
            $categoryIds = auth()->user()->userable->userOperator->category->pluck("category_id");
            $category = $this->category->where($column, $data)->whereIn("category_id", $categoryIds)->first();
            return $category;
        }
    }
    /**
    * 判斷指定欄位是否有重複資料
    * @param string $column 欄位名稱
    * @param string $data 欄位資料
    */
    public function checkDuplicate($column, $data)
    {
        // 判斷是否有重複值
        $category = $this->category->where($column, $data)->first();
        // 如果有資料代表
        if (isset($category)) {
            return true;
        }
        return false;
    }
}
