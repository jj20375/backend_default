<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// 導入 userAssistant 服務
use App\Services\User\UserAssistantService;
// 導入 Group 服務
use App\Services\GroupService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;

class UserAssistantController extends Controller
{
    // userAssistantService Service 指定變數
    protected $userAssistantService;
    // GroupService Service 指定變數
    protected $groupService;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    public function __construct(UserAssistantService $userAssistantService, GroupService $groupService, FuncHelper $funcHelper)
    {
        $this->userAssistantService = $userAssistantService;
        $this->groupService = $groupService;
        $this->funcHelper = $funcHelper;
    }

    // 新增助理
    public function create(Request $request)
    {
        // 驗證規則
        $rules = [
            'operator_id' => "required|integer",
            "group_id" => "required|integer",
            'password' => 'required|min:6',
            'account' => "required|min:4",
            'name' => "required|string|max:50",
            'status' => "required|integer",
            "imgFile" => "image|max:1540",
        ];
        // 驗證錯誤訊息
        $messages = [
            'operator_id.required' => "請輸入管理者id",
            'operator_id.integer' => "管理者id為數字",
            'group_id.required' => "請輸入群組id",
            'group_id.integer' => "群組id為數字",
            'password.min' => '密碼最少六個字',
            'password.required' => '請輸入密碼',
            'account.required' => '請輸入帳號',
            'account.min' => '帳號最少4位數',
            'name.required' => '請輸入名稱',
            'name.max' => '名稱過長',
            'status.required' => '請輸入狀態值',
            'status.integer' => '狀態值為數字',
            "imgFile.image" => "請傳入正確的圖片格式",
            "imgFile.max" => "圖片檔案過大",
        ];
        // 判斷是否有傳入logo 且為圖檔格式
        if ($request->hasFile('imgFile')) {
            $imgFile = $request->file("imgFile");
        } else {
            $imgFile = null;
        }
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 群組資料
        $group = $this->groupService->getData($request->group_id);
        // 判斷選擇群組是否符合該使用者身份
        if ($group->group_code !== "ASSISTANT") {
            return $this->funcHelper->errorBack("群組身份不符", 500);
        }
        // 新增助理
        $userAssistant = $this->userAssistantService->create($request->input(), $imgFile);
        return $this->funcHelper->successBack($userAssistant);
    }
    // 更新助理
    public function update(Request $request)
    {
        // 驗證規則
        $rules = [
            'assistant_id' => "required",
            'user_id' => "required",
            'operator_id' => "required",
            'password' => 'sometimes|min:6',
            'name' => "sometimes|string|max:50",
            'status' => "sometimes|integer",
            "imgFile" => "image|max:1540",
        ];
        // 驗證錯誤訊息
        $messages = [
            'assistant_id.required' => "請輸入助理id",
            'user_id.required' => "請輸入使用者表id",
            'operator_id.required' => "請輸入管理者id",
            'password.min' => '密碼最少六個字',
            'name.max' => '名稱過長',
            'status.integer' => '狀態值為數字',
            "imgFile.image" => "請傳入正確的圖片格式",
            "imgFile.max" => "圖片檔案過大",
        ];
        // 判斷是否有傳入logo 且為圖檔格式
        if ($request->hasFile('imgFile')) {
            $imgFile = $request->file("imgFile");
        } else {
            $imgFile = null;
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
            if ($group->group_code !== "ASSISTANT") {
                return $this->funcHelper->errorBack("群組身份不符", 500);
            }
        }
        // 更新助理
        $userAssistant = $this->userAssistantService->update($request->input(), $imgFile);
        return $this->funcHelper->successBack($userAssistant);
    }
    /**
     * 刪除助理圖片
     */
    public function deleteImage(Request $request)
    {
        // 驗證規則
        $rules = [
            'assistantId' => "required|integer",
            'imgPath' => "required|string",
        ];
        // 驗證錯誤訊息
        $messages = [
            'assistantId.required' => "請輸入助理id",
            'assistantId.integer' => "助理id為數字",
            "imgPath.required" => "請輸入圖片路徑",
            "imgPath.string" => "圖片路徑為字串",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $userAssistantImage = $this->userAssistantService->deleteImage($request->imgPath, $request->assistantId);
        return $this->funcHelper->successBack($userAssistantImage);
    }

    // 取得助理列表資料
    public function lists(Request $request)
    {
        $userAssistantLists = $this->userAssistantService->getLists($request->input());
        return $this->funcHelper->successBack($userAssistantLists);
    }
    /**
     * 取的指定id底下的助理列表資料
     */
    public function listsById(Request $request)
    {
        // 驗證規則
        $rules = [
            'operatorId' => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            'operatorId.required' => '請輸入使用者id',
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $userAssistantLists = $this->userAssistantService->getListsById($request->operatorId, $request->input());
        return $this->funcHelper->successBack($userAssistantLists);
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
        $userAssistantLists = $this->userAssistantService->getSelectLists($request->operatorId);
        return $this->funcHelper->successBack($userAssistantLists);
    }
    // 取得助理單一資料
    public function getData(Request $request)
    {
        // 驗證規則
        $rules = [
            'account' => "required|string",
        ];
        // 驗證錯誤訊息
        $messages = [
            'account.required' => "請輸入助理帳號",
            'account.string' => "助理帳號為字串",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $userAssistant = $this->userAssistantService->getDataByAccount($request->account);
        return $this->funcHelper->successBack($userAssistant);
    }
}
