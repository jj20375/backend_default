<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// 導入 userOperator 服務
use App\Services\User\UserOperatorService;
// 導入 userOperatorInfo 服務
use App\Services\User\UserOperatorInfoService;
// 導入 Group 服務
use App\Services\GroupService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;

// user_operators(經營者表) 操作路由方法
class UserOperatorController extends Controller
{
    // UserOperatorInfoService Service 指定變數
    protected $userOperatorInfoService;
    // UserOperatorService Service 指定變數
    protected $userOperatorService;
    // GroupService Service 指定變數
    protected $groupService;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    public function __construct(UserOperatorInfoService $userOperatorInfoService, UserOperatorService $userOperatorService, GroupService $groupService, FuncHelper $funcHelper)
    {
        $this->userOperatorInfoService = $userOperatorInfoService;
        $this->userOperatorService = $userOperatorService;
        $this->groupService = $groupService;
        $this->funcHelper = $funcHelper;
    }
    // 新增管理者
    public function create(Request $request)
    {
        // 驗證規則
        $rules = [
            'password' => 'required|min:6',
            'account' => "required|min:4",
            'name' => "required|string|max:50",
            'status' => "required|integer",
            'group_id' => "required",
            'parent_id' => "required|integer",
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
            "group_id.required" => "請選擇群族",
            'parent_id.required' => "請輸入上層id",
            'parent_id.integer' => "上層id為數字"
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
        if ($group->group_code !== "OPERATOR") {
            return $this->funcHelper->errorBack("群組身份不符", 500);
        }
        // 新增管理者
        $userOperator = $this->userOperatorService->create($request->input());
        return $this->funcHelper->successBack($userOperator);
    }

    // 更新管理者詳細資料
    public function updateInfo(Request $request)
    {
        // 驗證規則
        $rules = [
            "operator_id" => "required",
            "user_id" => "required",
            'password' => 'required|min:6|sometimes',
            'account' => "required|min:4",
            'name' => "required|string|max:50",
            'status' => "required|integer",
            'http_type' => "required|string",
            'domain' => "required|string",
            'web_name' => "required",
            "logo" => "image|sometimes",
            "lists" => "json", // ip白名單
        ];
        // 驗證錯誤訊息
        $messages = [
            "operator_id.required" => "請輸入管理者id",
            "user_id.required" => "請輸入users表id",
            'password.min' => '密碼最少六個字',
            'password.required' => '請輸入密碼',
            'account.required' => '請輸入帳號',
            'name.required' => '請輸入名稱',
            'name.max' => '名稱過長',
            'status.required' => '請輸入狀態值',
            'status.integer' => '狀態值為數字',
            'http_type.required' => "請輸入http或https",
            'http_type.string' => "請輸入http或https字串",
            'domain.string' => "網址需為字串",
            'domain.required' => "請輸入網址",
            'domain.string' => "網址需為字串",
            'web_name.required' => "請輸入網站名稱",
            "logo.image" => "請確認是否為圖片格式",
            "lists.json" => "格式為json",
        ];
        // 判斷是否有傳入 ip 白名單 如果有傳入 則需要再多傳入 ip白名單表 對應 primary key id
        if (isset($request->lists)) {
            $rules["ip_id"] = "required";
            $messages["ip_id.required"] = "請輸入ip白名單id";
        }
        
        // 判斷是否有傳入logo 且為圖檔格式
        if ($request->hasFile('logo')) {
            $logoFile = $request->file("logo");
        } else {
            $logoFile = null;
        }
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
            if ($group->group_code !== "OPERATOR") {
                return $this->funcHelper->errorBack("群組身份不符", 500);
            }
        }
        $infoData = $request->only(["http_type", "domain", "port", "template_id", "web_name", "operator_id", "user_id", "lists", "ip_id"]);
        // 更新管理者
        $userOperatorInfo = $this->userOperatorInfoService->update($request->input(), $infoData, $logoFile);
        return $this->funcHelper->successBack($userOperatorInfo);
    }
    // 更新管理者狀態值
    public function updateStatus(Request $request)
    {
        // 驗證規則
        $rules = [
            "operator_id" => "required",
            "user_id" => "required",
            "status" => "required|integer",
        ];
        // 驗證錯誤訊息
        $messages = [
            "operator_id.required" => "請輸入管理者id",
            "user_id.required" => "請輸入users表id",
            "status.required" => "請輸入狀態值",
            "status.integer" => "狀態值需為數字",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 更新管理者
        $userOperator = $this->userOperatorService->update($request->input());
        return $this->funcHelper->successBack($userOperator);
    }

    /**
     * 取得管理者資料
     */
    public function getData(Request $request)
    {
        $userOperator = $this->userOperatorService->getDataByAccount($request->account);
        return $this->funcHelper->successBack($userOperator);
    }
    /**
     * 取得管理者樹狀列表
     */
    public function treeLists(Request $request)
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
        $userOperatorLists = $this->userOperatorService->getTreeLists($request->operatorId);
        return $this->funcHelper->successBack($userOperatorLists);
    }
    /**
     * 取得管理者列表
     */
    public function lists(Request $request)
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
        $userOperatorLists = $this->userOperatorService->getLists($request->operatorId);
        return $this->funcHelper->successBack($userOperatorLists);
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
        $userOperatorLists = $this->userOperatorService->getSelectLists($request->operatorId);
        return $this->funcHelper->successBack($userOperatorLists);
    }
    // 新增管理者服務
    public function createService(Request $request)
    {
        // 驗證規則
        $rules = [
            'name' => "required|string",
            "category_id" => "required",
            "operator_ids" => "sometimes|json",
            'operatorId' => "required",
            'price' => "required|integer",
            "tagIds" => "array",
        ];
        // 驗證錯誤訊息
        $messages = [
            'name.required' => "請輸入服務名稱",
            'name.string' => "服務名稱為字串",
            'category_id.required' => "請選擇分類",
            'operatorId.required' => "請輸入管理者id",
            'price.required' => "請輸入服務價格",
            'price.integer' => "服務價格為數字",
            "tagIds.array" => "tagIds為陣列格式",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $infoData = $request->only("price", "tagIds");
        $userOperatorService = $this->userOperatorService->createService($request->input(), $infoData, $request->operatorId);
        return $this->funcHelper->successBack($userOperatorService);
    }
    // 更新管理者服務
    public function updateService(Request $request)
    {
        // 驗證規則
        $rules = [
            "service_id" => "required",
            'name' => "sometimes|string",
            'operator_ids' => "sometimes|json",
            'operatorId' => "required",
            'price' => "required|integer",
            "tagIds" => "array",
        ];
        // 驗證錯誤訊息
        $messages = [
            "service_id.required" => "請輸入服務id",
            'name.string' => "服務名稱為字串",
            'operator_ids.json' => "請傳入json格式",
            'operatorId.required' => "請輸入管理者id",
            'price.required' => "請輸入服務價格",
            'price.integer' => "服務價格為數字",
            "tagIds.array" => "tagIds為陣列格式",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $infoData = $request->only("price", "service_id", "tagIds");
        $userOperatorService = $this->userOperatorService->updateService($request->input(), $infoData, $request->operatorId);
        return $this->funcHelper->successBack($userOperatorService);
    }
    /**
     * 取得服務列表
     */
    public function getServiceLists(Request $request)
    {
        // 驗證規則
        $rules = [
            'operatorId' => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            'operatorId.required' => "請輸入管理者id",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 將分頁搜尋資料存進data變數 但是除了 operatorId key
        $data = $request->except(["operatorId"]);
        $serviceLists = $this->userOperatorService->getServiceLists($request->operatorId, $data);
        return $this->funcHelper->successBack($serviceLists);
    }
    /**
     * 取得可選擇服務列表
     */
    public function getServiceSelectLists(Request $request)
    {
        // 驗證規則
        $rules = [
            'operatorId' => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            'operatorId.required' => "請輸入管理者id",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $serviceLists = $this->userOperatorService->getServiceSelectLists($request->operatorId);
        return $this->funcHelper->successBack($serviceLists);
    }
    /**
     * 模糊比對搜尋使用
     */
    public function remoteLists(Request $request)
    {
        $lists = $this->userOperatorService->remoteLists($request->userName, $request->account);
        return $this->funcHelper->successBack($lists);
    }
}
