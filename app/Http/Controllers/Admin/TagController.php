<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 導入 tag 服務
use App\Services\TagService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    // TagService Service 指定變數
    protected $tagService;
    // 導入共用方法 指定變數
    protected $funcHelper;
        
    public function __construct(TagService $tagService, FuncHelper $funcHelper)
    {
        $this->tagService = $tagService;
        $this->funcHelper = $funcHelper;
    }

    // 新增標籤
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
            'name.required' => '請輸入標籤名稱',
            'name.string' => '標籤名稱為字串',
            'key.required' => '請輸入標籤代碼',
            'key.string' => '標籤代碼為字串',
            'active.required' => '請選擇標籤是否啟用',
            'active.boolean' => '請傳入boolean值',
            "permission_rule.required" => "請輸入該標籤權限值",
            "permission_rule.integer" => "群組權限值為數字",
            "operator_ids.json" => "管理者id 格式為 json",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 新增標籤
        $tag = $this->tagService->create($request->input());
        return $this->funcHelper->successBack($tag);
    }
    // 更新標籤
    public function update(Request $request)
    {
        // 驗證規則
        $rules = [
            "tag_id" => "required",
            'name' => 'sometimes|string',
            'key' => "sometimes|string",
            "permission_rule" => "sometimes|integer",
        ];
        // 驗證錯誤訊息
        $messages = [
            'tag_id.required' => '請輸入標籤id',
            'name.string' => '標籤名稱為字串',
            'key.string' => '標籤代碼為字串',
            "permission_rule.integer" => "群組權限值為數字",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 更新標籤
        $tag = $this->tagService->update($request->input());
        return $this->funcHelper->successBack($tag);
    }
    // 取得列表
    public function lists(Request $request)
    {
        $tagLists = $this->tagService->getLists($request->input());
        return $this->funcHelper->successBack($tagLists);
    }
    /**
     * 取得可選擇列表
     */
    public function selectLists(Request $request)
    {
        // 驗證規則
        $rules = [
            "tagType" => "required|string",
            "operatorId" => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            "tagType.required" => "請輸入標籤類別",
            "operatorId.required" => "請輸入管理者id",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $tagLists = $this->tagService->getSelectLists($request->tagType, $request->operatorId);
        return $this->funcHelper->successBack($tagLists);
    }
    /**
     * 取得單一分類資料
     */
    public function getData(Request $request)
    {
        // 驗證規則
        $rules = [
            "tagId" => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            "tagId.required" => "請輸入分類id",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $tag = $this->tagService->getData($request->tagId);
        return $this->funcHelper->successBack($tag);
    }
}
