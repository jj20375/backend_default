<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// 導入 userDesigner 服務
use App\Services\User\UserDesignerService;
// 導入 Group 服務
use App\Services\GroupService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;

class UserDesignerController extends Controller
{
    // userDesignerService Service 指定變數
    protected $userDesignerService;
    // GroupService Service 指定變數
    protected $groupService;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    public function __construct(UserDesignerService $userDesignerService, GroupService $groupService, FuncHelper $funcHelper)
    {
        $this->userDesignerService = $userDesignerService;
        $this->groupService = $groupService;
        $this->funcHelper = $funcHelper;
    }

    // 新增服務提供者
    public function create(Request $request)
    {
        // 驗證規則
        $rules = [
            'operator_id' => "required|integer",
            "group_id" => "required|integer",
            'password' => 'required|min:6',
            'account' => "required|min:4",
            'name' => "required|string|max:50",
            'nickname' => "sometimes|string|nullable|max:50",
            'birthday' => "sometimes|date",
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
            'nickname.max' => '暱稱過長',
            'nickname.string' => '暱稱必須為字串',
            'birthday.date' => '生日必須為日期格式',
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
        if ($group->group_code !== "DESIGNER") {
            return $this->funcHelper->errorBack("群組身份不符", 500);
        }
        // 新增服務提供者
        $userDesigner = $this->userDesignerService->create($request->input(), $imgFile);
        return $this->funcHelper->successBack($userDesigner);
    }
    // 更新服務提供者
    public function update(Request $request)
    {
        // 驗證規則
        $rules = [
            'designer_id' => "required",
            'operator_id' => "required",
            'user_id' => "required",
            'password' => 'sometimes|min:6',
            'name' => "sometimes|string|max:50",
            'nickname' => "sometimes|string|nullable|max:50",
            'status' => "sometimes|integer",
            "imgFile" => "image|max:1540",
        ];
        // 驗證錯誤訊息
        $messages = [
            'designer_id.required' => "請輸入服務提供者id",
            'operator_id.required' => "請輸入管理者id",
            'user_id.required' => "請輸入users 表id",
            'password.min' => '密碼最少六個字',
            'password.required' => '請輸入密碼',
            'name.required' => '請輸入名稱',
            'name.max' => '名稱過長',
            'nickname.max' => '暱稱過長',
            'nickname.string' => '暱稱必須為字串',
            'status.integer' => '狀態值為數字',
            "imgFile.image" => "請傳入正確的圖片格式",
            "imgFile.max" => "圖片檔案過大",
        ];
        if (!isset($request->birthday) && $request->birthday !== null) {
            $rules["birthday"] = "sometimes|date";
            $messages["birthday.date"] = "生日必須為日期格式";
        }
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
            if ($group->group_code !== "DESIGNER") {
                return $this->funcHelper->errorBack("群組身份不符", 500);
            }
        }
        // 更新服務提供者
        $userDesigner = $this->userDesignerService->update($request->input(), $imgFile);
        return $this->funcHelper->successBack($userDesigner);
    }
    /**
     * 刪除服務提供者圖片
     */
    public function deleteImage(Request $request)
    {
        // 驗證規則
        $rules = [
            'designerId' => "required|integer",
            'imgPath' => "required|string",
        ];
        // 驗證錯誤訊息
        $messages = [
            'designerId.required' => "請輸入服務提供者id",
            'designerId.integer' => "服務提供者id為數字",
            "imgPath.required" => "請輸入圖片路徑",
            "imgPath.string" => "圖片路徑為字串",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $userDesignerImage = $this->userDesignerService->deleteImage($request->imgPath, $request->designerId);
        return $this->funcHelper->successBack($userDesignerImage);
    }
    // 取得服務提供者列表資料
    public function lists(Request $request)
    {
        $userDesignerLists = $this->userDesignerService->getLists($request->input());
        return $this->funcHelper->successBack($userDesignerLists);
    }
    /**
     * 取的指定id底下的服務提供者列表資料
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
        $userDesignerLists = $this->userDesignerService->getListsById($request->operatorId, $request->input());
        return $this->funcHelper->successBack($userDesignerLists);
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
        $userDesignerLists = $this->userDesignerService->getSelectLists($request->operatorId);
        return $this->funcHelper->successBack($userDesignerLists);
    }
    // 取得服務提供者單一資料
    public function getData(Request $request)
    {
        // 驗證規則
        $rules = [
            'account' => "required|string",
        ];
        // 驗證錯誤訊息
        $messages = [
            'account.required' => "請輸入服務提供者帳號",
            'account.string' => "服務提供者帳號為字串",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $userDesigner = $this->userDesignerService->getDataByAccount($request->account);
        return $this->funcHelper->successBack($userDesigner);
    }
    // 新增服務提供者服務
    public function createService(Request $request)
    {
        // 驗證規則
        $rules = [
            'name' => "required|string",
            "category_id" => "required",
            "operator_ids" => "sometimes|json",
            'designerId' => "required",
            'price' => "required|integer",
            "tagIds" => "array",
        ];
        // 驗證錯誤訊息
        $messages = [
            'name.required' => "請輸入服務名稱",
            'name.string' => "服務名稱為字串",
            'category_id.required' => "請選擇分類",
            'designerId.required' => "請輸入服務提供者id",
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
        $userDesignerService = $this->userDesignerService->createService($request->input(), $infoData, $request->designerId);
        return $this->funcHelper->successBack($userDesignerService);
    }
    // 更新服務提供者服務
    public function updateService(Request $request)
    {
        // 驗證規則
        $rules = [
            "service_id" => "required",
            'name' => "sometimes|string",
            'operator_ids' => "sometimes|json",
            'designerId' => "required",
            'price' => "required|integer",
            "tagIds" => "array",
        ];
        // 驗證錯誤訊息
        $messages = [
            "service_id.required" => "請輸入服務id",
            'name.string' => "服務名稱為字串",
            'operator_ids.json' => "請傳入json格式",
            'designerId.required' => "請輸入服務提供者id",
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
        $userDesignerService = $this->userDesignerService->updateService($request->input(), $infoData, $request->designerId);
        return $this->funcHelper->successBack($userDesignerService);
    }
    /**
     * 取得服務列表
     */
    public function getServiceLists(Request $request)
    {
        // 驗證規則
        $rules = [
            'designerId' => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            'designerId.required' => "請輸入服務提供者id",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 將分頁搜尋資料存進data變數 但是除了 designerId key
        $data = $request->except(["designerId"]);
        $serviceLists = $this->userDesignerService->getServiceLists($request->designerId, $data);
        return $this->funcHelper->successBack($serviceLists);
    }
    /**
     * 取得可選擇服務列表
     */
    public function getServiceSelectLists(Request $request)
    {
        // 驗證規則
        $rules = [
            'designerId' => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            'designerId.required' => "請輸入服務提供者id",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $serviceLists = $this->userDesignerService->getServiceSelectLists($request->designerId);
        return $this->funcHelper->successBack($serviceLists);
    }
}
