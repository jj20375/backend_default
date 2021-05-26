<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 導入 userOperator 服務
use App\Services\User\UserSubService;
// 導入 Group 服務
use App\Services\GroupService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;

class UserSubController extends Controller
{
    // userSubService Service 指定變數
    protected $userSubService;
    // GroupService Service 指定變數
    protected $groupService;
    // 導入共用方法
    protected $funcHelper;

    
    public function __construct(UserSubService $userSubService, GroupService $groupService, FuncHelper $funcHelper)
    {
        $this->userSubService = $userSubService;
        $this->groupService = $groupService;
        $this->funcHelper = $funcHelper;
    }
    
    // 新增子帳號
    public function create(Request $request)
    {
        // 驗證規則
        $rules = [
            "user_id" => "required",
            "group_id" => "required",
            'password' => 'required|min:6',
            'account' => "required|min:4",
            'name' => "required|string|max:50",
            'status' => "required|integer",
        ];
        // 驗證錯誤訊息
        $messages = [
            'user_id.required' => '請輸入欲新增子帳號的使用者id',
            'group_id.required' => '請選擇群組',
            'password.min' => '密碼最少六個字',
            'password.required' => '請輸入密碼',
            'account.required' => '請輸入帳號',
            'account.min' => '帳號最少4位數',
            'name.required' => '請輸入名稱',
            'name.max' => '名稱過長',
            'status.required' => '請輸入狀態值',
            'status.integer' => '狀態值為數字',
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 群組資料
        $group = $this->groupService->getData($request->group_id);
        // 判斷選擇群組是否符合該使用者身份
        if ($group->group_code !== "OPERATOR" && $group->is_sub !== 1) {
            return $this->funcHelper->errorBack("群組身份不符", 500);
        }
        // 指定哪個使用者專屬的子帳號
        $userId = $request->user_id;
        // 新增子帳號
        $userSub = $this->userSubService->create($request->input(), $userId);
        return $this->funcHelper->successBack($userSub);
    }

    // 更新子帳號
    public function update(Request $request)
    {
        // 驗證規則
        $rules = [
            'sub_id' => "required",
            'user_id' => "required",
            'password' => 'sometimes|min:6',
            'name' => "sometimes|string|max:50",
            'status' => "sometimes|integer",
        ];
        // 驗證錯誤訊息
        $messages = [
            'sub_id.required' => '請輸入子帳號id',
            'user_id.required' => '請輸入子帳號users表中id',
            'password.min' => '密碼最少六個字',
            'name.max' => '名稱過長',
            'status.integer' => '狀態值為數字',
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 判斷是否有傳入 group_id
        if (isset($request->group_id)) {
            // 群組資料
            $group = $this->groupService->getData($request->group_id);
            // 判斷選擇群組是否符合該使用者身份
            if ($group->group_code !== "OPERATOR" && $group->is_sub !== 1) {
                return $this->funcHelper->errorBack("群組身份不符", 500);
            }
        }
        // 更新子帳號
        $userSub = $this->userSubService->update($request->input());
        return $this->funcHelper->successBack($userSub);
    }

    /**
     * 取的子帳號列表資料
     */
    public function lists(Request $request)
    {
        $userSubLists = $this->userSubService->getLists($request->input());
        return $this->funcHelper->successBack($userSubLists);
    }
    /**
     * 取的指定id底下的子帳號列表資料
     */
    public function listsById(Request $request)
    {
        // 驗證規則
        $rules = [
            'userId' => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            'userId.required' => '請輸入使用者id',
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $userSubLists = $this->userSubService->getListsById($request->userId, $request->input());
        return $this->funcHelper->successBack($userSubLists);
    }
    /**
     * 取得可選擇列表
     */
    public function selectLists(Request $request)
    {
        // 驗證規則
        $rules = [
            "operatorId" => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            "operatorId.required" => "請輸入管理者id",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $userSubLists = $this->userSubService->getSelectLists($request->operatorId);
        return $this->funcHelper->successBack($userSubLists);
    }
    /**
     * 取的單一子帳號資料
     */
    public function getData(Request $request)
    {
        $userSub = $this->userSubService->getDataByAccount($request->account);
        return $this->funcHelper->successBack($userSub);
    }
}
