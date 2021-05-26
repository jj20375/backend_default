<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 導入 台灣簡訊 服務
use App\Services\TwSmsService;
// 導入 sms 服務
use App\Services\SmsService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;

class SmsController extends Controller
{
    // 台灣簡訊 Service 指定變數
    protected $twSmsService;
    // SmsService Service 指定變數
    protected $smsService;
    // 導入共用方法 指定變數
    protected $funcHelper;

       
    public function __construct(TwSmsService $twSmsService, SmsService $smsService, FuncHelper $funcHelper)
    {
        $this->twSmsService = $twSmsService;
        $this->smsService = $smsService;
        $this->funcHelper = $funcHelper;
    }
    
    // 新增簡訊商
    public function create(Request $request)
    {
        // 驗證規則
        $rules = [
            "operator_id" => "required",
            "operator_ids" => "sometimes|json",
            'key' => 'required|string',
            'name' => "required|string",
            'status' => "required|integer",
            'key_data' => "required|json",
        ];
        // 驗證錯誤訊息
        $messages = [
            "operator_id.required" => "請輸入管理者id",
            "operator_ids.json" => "管理者ids 為json格式",
            "key.required" => "請輸入簡訊商key",
            "key.string" => "簡訊商key為字串格式",
            "name.required" => "請輸入簡訊商名稱",
            "name.string" => "簡訊商名稱為字串格式",
            'status.required' => '請輸入狀態值',
            'status.integer' => '狀態值為數字格式',
            'key_data.required' => '請輸入簡訊商資料',
            'key_data.json' => '簡訊商資料為json格式',
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 新增簡訊商
        $sms = $this->smsService->checkKeyMethod($request->key, "create", $request->input());
        // 判斷是否有選擇到簡訊商
        if (!$sms) {
            return $this->funcHelper->errorBack("請選擇正確簡訊商", 500);
        }
        return $this->funcHelper->successBack($sms);
    }
    // 更新簡訊商
    public function update(Request $request)
    {
        // 驗證規則
        $rules = [
            'sms_id' => 'required',
            "operator_ids" => "sometimes|json",
            'key' => 'required|string',
            'name' => "sometimes|string",
            'status' => "sometimes|integer",
            'key_data' => "sometimes|json",
        ];
        // 驗證錯誤訊息
        $messages = [
            "sms_id.required" => "請輸入簡訊商id",
            "operator_ids.json" => "管理者ids 為json格式",
            "key.required" => "請輸入簡訊商key",
            "key.string" => "簡訊商key為字串格式",
            "name.string" => "簡訊商名稱為字串格式",
            'status.integer' => '狀態值為數字格式',
            'key_data.json' => '簡訊商資料為json格式',
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 新增簡訊商
        $sms = $this->smsService->checkKeyMethod($request->key, "update", $request->input());
        // 判斷是否有選擇到簡訊商
        if (!$sms) {
            return $this->funcHelper->errorBack("請選擇正確簡訊商", 500);
        }
        return $this->funcHelper->successBack($sms);
    }

    // 發送簡訊
    public function sendMessage(Request $request)
    {
        // 表單驗證
        $checkValidate = Validator::make(["phones"=>$request->phones], ["phones" => "required|array"], [
            "phones.required" => "請傳入手機號碼陣列",
            "phones.array" => "手機號碼為陣列格式"
        ]);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        foreach ($request->phones as $value) {
            // 驗證規則
            $rules = [
                "sms_id" => "required",
                "key" => "required|string",
                'mobile' => 'required|regex:/^09[0-9]{8}$/',
                'message' => "required|string",
            ];
            // 驗證錯誤訊息
            $messages = [
                "sms_id.required" => "請輸入簡訊商id",
                "key.required" => "請輸入簡訊商key",
                "key.string" => "簡訊商key為字串格式",
                "mobile.required" => "請輸入手機號碼",
                "mobile.regex" => "請輸入手機正規模式",
                'message.required' => '請輸入訊息',
                'message.string' => '訊息為字串格式',
            ];
            $data = $request->input();
            $data["mobile"] = $value;
            // 表單驗證
            $checkValidate = Validator::make($data, $rules, $messages);
            // 判斷表單驗證是否通過
            if ($checkValidate->fails()) {
                return $this->funcHelper->errorBack($checkValidate->errors(), 500);
            }
        }
        // 發送簡訊
        $res = $this->smsService->checkKeyMethod($request->key, "sendMessage", ["phones" => $request->phones, "message" => $request->message], $request->sms_id);
        // 判斷是否有選擇到簡訊商
        if (!$res) {
            return $this->funcHelper->errorBack("請選擇正確簡訊商", 500);
        }
        return $this->funcHelper->successBack($res);
    }
    // 簡訊商回調
    public function callback(Request $request)
    {
        return $request->all();
    }
    // 取得新增簡訊商時需要數入欄位
    public function keyData(Request $request)
    {
        // 驗證規則
        $rules = [
            "key" => "required|string",
        ];
        // 驗證錯誤訊息
        $messages = [
            "key.required" => "請輸入簡訊商key",
            "key.string" => "簡訊商key為字串格式",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 發送簡訊
        $twSmsKey = $this->smsService->checkKeyMethod($request->key, "getKeyData");
        // 判斷是否有選擇到簡訊商
        if (!$twSmsKey) {
            return $this->funcHelper->errorBack("請選擇正確簡訊商", 500);
        }
        return $this->funcHelper->successBack($twSmsKey);
    }
    // 可用簡訊廠商選擇
    public function getProviders()
    {
        $providers = [
            ["key" => "twSms", "name" => "台灣簡訊"],
        ];
        return $this->funcHelper->successBack($providers);
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
        $smsLists = $this->smsService->getSelectLists($request->operatorId);
        return $this->funcHelper->successBack($smsLists);
    }
    // 取得列表
    public function lists(Request $request)
    {
        $smsLists = $this->smsService->getLists($request->input());
        return $this->funcHelper->successBack($smsLists);
    }
    // 取得單一資料
    public function getData(Request $request)
    {
        // 驗證規則
        $rules = [
            "sms_id" => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            "sms_id.required" => "請輸入簡訊商id",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $sms = $this->smsService->getData($request->sms_id);
        return $this->funcHelper->successBack($sms);
    }
    // 刪除
    public function delete(Request $request)
    {
        // 驗證規則
        $rules = [
            "sms_id" => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            "sms_id.required" => "請輸入簡訊商id",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $sms = $this->smsService->delete($request->sms_id);
        return $this->funcHelper->successBack($sms);
    }
}
