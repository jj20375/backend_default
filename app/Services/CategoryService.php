<?php
namespace App\Services;

// 導入 categories 資料庫操作方法
use App\Repositories\CategoryRepository;
// 導入 共用方法
use App\Helpers\FuncHelper;

class CategoryService
{
    // CategoryRepository 指定變數
    protected $categoryRepository;
    // 導入共用方法 指定變數
    protected $funcHelper;

    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(CategoryRepository $categoryRepository, FuncHelper $funcHelper)
    {
        $this->categoryRepository = $categoryRepository;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增分類
     * @param object $data 新增分類資料
     */
    public function create($data)
    {
        // 判斷是否為系統身份
        if (auth()->user()->group->group_code === "SYSTEM") {
            // 取得系統身份資料
            $ableOrm = auth()->user()->userable;
        } else {
            // 取得管理者身份資料
            $ableOrm = auth()->user()->userable->userOperator;
        }
        $category = $this->categoryRepository->create($data, $ableOrm);
        return $category;
    }
    /**
     * 更新分類
     * @param object $data 更新分類資料
     */
    public function update($data)
    {
        $category = $this->categoryRepository->update($data);
        // 判斷是否更新成功
        if ($category === false) {
            return $this->funcHelper->errorBack("更新失敗", 500);
        }
        return $category;
    }
    /**
     * 取得列表
     * @param object $data 列表搜尋過濾資料
     */
    public function getLists($data = null)
    {
        $categoryLists = $this->categoryRepository->getLists($data);
        return $categoryLists;
    }
    /**
     * 取得可用列表
     * @param string $categoryType 分類代碼
     * @param string $operatorId 管理者id
     */
    public function getSelectLists($categoryType, $operatorId)
    {
        $categoryLists = $this->categoryRepository->getSelectLists($categoryType, $operatorId);
        return $categoryLists;
    }
    /**
     * 取得單一資料
     * @param string $categoryId 分類id
     */
    public function getData($categoryId)
    {
        $category = $this->categoryRepository->getData("category_id", $categoryId);
        $categoryType = config("app.categoryType");
        // 選中的群組身份
        $selectPermissionRule = collect([]);
        // 將可選擇的群組身份 執行回圈匹配
        foreach ($categoryType as $key => $value) {
            // 判斷方式採用 數位權限 二進制判斷 如果匹配失敗時 值會等於 0
            if (($category->permission_rule & $value) !== 0) {
                $selectPermissionRule->push($value);
            }
        }
        return ["data" => $category, "selectPermissionRule" => $selectPermissionRule];
    }
}
