<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 導入 userOperator 服務
use App\Services\User\UserSystemService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;

class UserSystemController extends Controller
{
    // UserSystemService Service 指定變數
    protected $userSystemService;
    // 導入共用方法
    protected $funcHelper;

    
    public function __construct(UserSystemService $userSystemService, FuncHelper $funcHelper)
    {
        $this->userSystemService = $userSystemService;
        $this->funcHelper = $funcHelper;
    }
    
    // 新增系統使用者
    public function create(Request $request)
    {
        // 驗證規則
        $rules = [
            'password' => 'required|min:6',
            'account' => "required|min:4",
            'name' => "required|string|max:50",
            'status' => "required|integer",
        ];
        // 驗證錯誤訊息
        $messages = [
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
        // 新增系統使用者
        $userSystem = $this->userSystemService->create($request->input());
        return $this->funcHelper->successBack($userSystem);
    }
    // 更新系統使用者
    public function update(Request $request)
    {
        // 驗證規則
        $rules = [
            'user_id' => 'required',
            'system_id' => 'required',
            'password' => 'sometimes|min:6',
            'name' => "sometimes|string|max:50",
            'status' => "sometimes|integer",
            "lists.json" => "格式為json", // ip白名單
        ];
        // 驗證錯誤訊息
        $messages = [
            "user_id.required" => "請輸入user_id",
            "system_id.required" => "請輸入system_id",
            'password.min' => '密碼最少六個字',
            'name.max' => '名稱過長',
            'status.integer' => '狀態值為數字',
            "lists.json" => "格式為json",
        ];
        // 判斷是否有傳入 ip 白名單 如果有傳入 則需要再多傳入 ip白名單表 對應 primary key id
        if (isset($request->lists)) {
            $rules["ip_id"] = "required";
            $messages["ip_id.required"] = "請輸入ip白名單id";
        }
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 更新系統使用者
        $userSystem = $this->userSystemService->update($request->input());
        return $this->funcHelper->successBack($userSystem);
    }
    /**
     * 取得系統使用者列表
     */
    public function lists(Request $request)
    {
        $userSystemLists = $this->userSystemService->getLists($request->input());
        return $this->funcHelper->successBack($userSystemLists);
    }
    /**
     * 取得系統使用者資料
     */
    public function getData(Request $request)
    {
        // 驗證規則
        $rules = [
            'account' => 'required',
        ];
        // 驗證錯誤訊息
        $messages = [
            "account.required" => "請輸入系統使者帳號",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $userSystem = $this->userSystemService->getDataByAccount($request->account);
        return $this->funcHelper->successBack($userSystem);
    }
}
