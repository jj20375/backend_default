<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 導入 UeerMemberService 服務
use App\Services\User\UserMemberService;
// 導入 UeerMemberInfoService 服務
use App\Services\User\UserMemberInfoService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;

class UserMemberController extends Controller
{
    // UserMemberService Service 指定變數
    protected $userMemberService;
    // UserMemberInfoService Service 指定變數
    protected $userMemberInfoService;
    // 導入共用方法 指定變數
    protected $funcHelper;
          
    public function __construct(UserMemberService $userMemberService, UserMemberInfoService $userMemberInfoService, FuncHelper $funcHelper)
    {
        $this->userMemberService = $userMemberService;
        $this->userMemberInfoService = $userMemberInfoService;
        $this->funcHelper = $funcHelper;
    }

    // 新增會員
    public function create(Request $request)
    {
        // 驗證規則
        $rules = [
            "operator_id" => "required",
            'name' => 'required|string',
            'account' => 'required|string',
            'password' => 'required|string',
            'nickname' => 'string',
            'status' => "required|integer",
            'birthday' => "sometimes|date",
            'note' => "string",
        ];
        // 驗證錯誤訊息
        $messages = [
            "operator_id.required" => "請輸入管理者id",
            'account.required' => '請輸入帳號',
            'account.string' => '帳號為字串',
            'password.required' => '請輸入密碼',
            'password.string' => '密碼為字串',
            'name.required' => '請輸入會員名稱',
            'name.string' => '會員名稱為字串',
            'nickename.string' => '會員暱稱為字串',
            'status.required' => '請選擇會員狀態',
            'status.integer' => '會員狀態為數字',
            'birthday.date' => '生日必須為日期格式',
            'note.string' => '備註為字串格式',
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 會員詳細資料
        $infoData = $request->only(["custom_id"]);
        // 新增會員
        $userMember = $this->userMemberService->create($request->input(), $infoData);
        // 判斷是否有新增成功
        if(isset($userMember["success"]) && !$userMember["success"]) {
            return $this->funcHelper->errorBack($userMember["message"], 500);
        }
        return $this->funcHelper->successBack($userMember);
    }
    // 更新會員
    public function update(Request $request)
    {
        // 驗證規則
        $rules = [
            "operator_id" => "required",
            "member_info_id" => "required",
            "member_id" => "required",
            "user_id" => "required",
            'name' => 'sometimes|string',
            'password' => 'sometimes|string',
            'nickname' => 'sometimes|nullable|string',
            'status' => "integer",
            'birthday' => "sometimes|date",
            'note' => "sometimes|string",
            "lists" => "json", // ip白名單
        ];
        // 驗證錯誤訊息
        $messages = [
            "operator_id.required" => "請輸入管理者id",
            "member_info_id.required" => "請輸入會員詳細資料id",
            "member_id.required" => "請輸入會員id",
            "user_id.required" => "請輸入users 表id",
            'password.string' => '密碼為字串',
            'name.string' => '會員名稱為字串',
            'nickname.string' => '會員暱稱為字串',
            'status.integer' => '會員狀態為數字',
            'birthday.date' => '生日必須為日期格式',
            'note.string' => '備註為字串格式',
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
        $data = $request->input();
        unset($data["account"]);
        // 更新會員詳細資料
        $infoData = $request->only(["member_info_id", "custom_id", "member_id"]);
        // 更新會員
        $userMember = $this->userMemberService->update($data, $infoData);
        return $this->funcHelper->successBack($userMember);
    }
    // 取得列表
    public function lists(Request $request)
    {
        $userMemberLists = $this->userMemberService->getLists($request->input());
        return $this->funcHelper->successBack($userMemberLists);
    }
    /**
     * 取的指定id底下的會員列表資料
     */
    public function listsById(Request $request)
    {
        // 驗證規則
        $rules = [
            'operatorId' => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            'operatorId.required' => '請輸入管理者id',
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $userMemberLists = $this->userMemberService->getListsById($request->operatorId, $request->input());
        return $this->funcHelper->successBack($userMemberLists);
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
        $userMemberLists = $this->userMemberService->getSelectLists($request->operatorId);
        return $this->funcHelper->successBack($userMemberLists);
    }
    /**
     * 取得單一會員資料
     */
    public function getData(Request $request)
    {
        // 驗證規則
        $rules = [
            "account" => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            "account.required" => "請輸入會員帳號",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $userMember = $this->userMemberService->getDataByAccount($request->account);
        return $this->funcHelper->successBack($userMember);
    }
    /**
     * 新增點數
     */
    public function addPoint(Request $request)
    {
        // 驗證規則
        $rules = [
            "operatorId" => "required",
            "userId" => "required",
            "memberId" => "required",
            "member_info_id" => "required",
            "point" => "required|integer",
            "remarks" => "required|string",
        ];
        // 驗證錯誤訊息
        $messages = [
            "operatorId.required" => "請輸入管理者id",
            "userId.required" => "請輸入操作者users表id",
            "memberId.required" => "請輸入會員id",
            "member_info_id.required" => "請輸入會員詳細資料id",
            "point.required" => "請輸入會員詳細資料id",
            "remarks.required" => "請輸入備註",
            "remarks.string" => "備註為字串格式",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $pointOrder = $this->userMemberInfoService->addPoint($request->input());
        return $this->funcHelper->successBack($pointOrder);
    }
    /**
     * 取得簡訊發送名單
     */
    public function getSmsSendLists(Request $request)
    {
        // 驗證規則
        $rules = [
            "operator_id" => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            "operator_id.required" => "請輸入管理者id",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $lists = $this->userMemberService->getSmsSendLists($request->input());
        return $this->funcHelper->successBack($lists);
    }

    /**
     * 模糊比對搜尋使用
     */
    public function remoteLists(Request $request)
    {
        // 驗證規則
        $rules = [
            "operator_id" => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            "operator_id.required" => "請輸入管理者id",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $lists = $this->userMemberService->remoteLists($request->input());
        return $this->funcHelper->successBack($lists);
    }
    
}
