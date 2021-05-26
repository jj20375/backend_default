<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 導入 userOperatorInfo 服務
use App\Services\GroupService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    // GroupService Service 指定變數
    protected $groupService;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    public function __construct(GroupService $groupService, FuncHelper $funcHelper)
    {
        $this->groupService = $groupService;
        $this->funcHelper = $funcHelper;
    }

    // 新增群組
    public function create(Request $request)
    {
        // 驗證規則
        $rules = [
            'group_name' => 'required',
            'group_code' => "required",
            'is_sub' => "required",
            "permission_rule" => "required|integer",
        ];
        // 驗證錯誤訊息
        $messages = [
            'group_name.required' => '請輸入群組名稱',
            'group_code.required' => '請輸入群組代碼',
            'is_sub.required' => '請選擇是否子帳號',
            "permission_rule.required" => "請輸入該群組權限值",
            "permission_rule.integer" => "群組權限值為數字",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 新增群組
        $group = $this->groupService->create($request->input());
        return $this->funcHelper->successBack($group);
    }
    // 更新群組
    public function update(Request $request)
    {
        // 驗證規則
        $rules = [
            "group_id" => "required",
            'group_name' => 'required',
            'is_sub' => "required",
            "permission_rule" => "required|integer",
            "operator_ids" => "sometimes|json",
        ];
        // 驗證錯誤訊息
        $messages = [
            "group_id" => "請輸入群組id",
            'group_name.required' => '請輸入群組名稱',
            'is_sub.required' => '請選擇是否子帳號',
            "permission_rule.required" => "請輸入該群組權限值",
            "permission_rule.integer" => "群組權限值為數字",
            "operator_ids.json" => "請傳送json格式",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 更新群組
        $group = $this->groupService->update($request->input());
        return $this->funcHelper->successBack($group);
    }

    /**
     * 取得列表
     */
    public function lists(Request $request)
    {
        $groupLists = $this->groupService->getLists($request->input());
        return $this->funcHelper->successBack($groupLists);
    }

    /**
     * 取得可選擇列表
     */
    public function selectLists(Request $request)
    {
        // 驗證規則
        $rules = [
            "groupCode" => "required",
            "operatorId" => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            "groupCode.required" => "請輸入群組id",
            "operatorId.required" => "請輸入管理者id",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $groupLists = $this->groupService->getSelectLists($request->groupCode, $request->operatorId, $request->isSub);
        return $this->funcHelper->successBack($groupLists);
    }

    /**
     * 取得群組資料
     */
    public function getData(Request $request)
    {

        // 驗證規則
        $rules = [
            "groupId" => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            "groupId.required" => "請輸入群組id",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $group = $this->groupService->getData($request->groupId);
        return $this->funcHelper->successBack($group);
    }
}
