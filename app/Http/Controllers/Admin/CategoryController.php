<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 導入 category 服務
use App\Services\CategoryService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    // CategoryService Service 指定變數
    protected $categoryService;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    public function __construct(CategoryService $categoryService, FuncHelper $funcHelper)
    {
        $this->categoryService = $categoryService;
        $this->funcHelper = $funcHelper;
    }
    // 新增分類
    public function create(Request $request)
    {
        // 驗證規則
        $rules = [
            'name' => 'required|string',
            'key' => "required|string",
            'active' => "required|boolean",
            "permission_rule" => "required|integer",
            "operator_ids" => "sometimes|json"
        ];
        // 驗證錯誤訊息
        $messages = [
            'name.required' => '請輸入分類名稱',
            'name.string' => '分類名稱為字串',
            'key.required' => '請輸入分類代碼',
            'key.string' => '分類代碼為字串',
            'active.required' => '請選擇分類是否啟用',
            'active.boolean' => '請傳入boolean值',
            "permission_rule.required" => "請輸入該分類權限值",
            "permission_rule.integer" => "群組權限值為數字",
            "operator_ids.json" => "管理者id 格式為 json",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 新增分類
        $category = $this->categoryService->create($request->input());
        return $this->funcHelper->successBack($category);
    }
    // 更新分類
    public function update(Request $request)
    {
        // 驗證規則
        $rules = [
            "category_id" => "required",
            'name' => 'sometimes|string',
            'key' => "sometimes|string",
            "permission_rule" => "sometimes|integer",
            "operator_ids" => "sometimes|json"
        ];
        // 驗證錯誤訊息
        $messages = [
            'category_id.required' => '請輸入分類id',
            'name.string' => '分類名稱為字串',
            'key.string' => '分類代碼為字串',
            "permission_rule.integer" => "群組權限值為數字",
            "operator_ids.json" => "管理者id 格式為 json",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 更新分類
        $category = $this->categoryService->update($request->input());
        return $this->funcHelper->successBack($category);
    }
    // 取得列表
    public function lists(Request $request)
    {
        $categoryLists = $this->categoryService->getLists($request->input());
        return $this->funcHelper->successBack($categoryLists);
    }
    /**
     * 取得可選擇列表
     */
    public function selectLists(Request $request)
    {
        // 驗證規則
        $rules = [
            "categoryType" => "required|string",
            "operatorId" => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            "categoryType.required" => "請輸入分類類別",
            "operatorId.required" => "請輸入管理者id",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $categoryLists = $this->categoryService->getSelectLists($request->categoryType, $request->operatorId);
        return $this->funcHelper->successBack($categoryLists);
    }
    /**
     * 取得單一分類資料
     */
    public function getData(Request $request)
    {
        // 驗證規則
        $rules = [
            "categoryId" => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            "categoryId.required" => "請輸入分類id",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $category = $this->categoryService->getData($request->categoryId);
        return $this->funcHelper->successBack($category);
    }
}
