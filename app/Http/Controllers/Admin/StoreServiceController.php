<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 導入 storeService 服務
use App\Services\StoreServiceService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;

class StoreServiceController extends Controller
{
    // storeServcieService Service 指定變數
    protected $storeServiceService;
    // 導入共用方法 指定變數
    protected $funcHelper;
     
    public function __construct(StoreServiceService $storeServiceService, FuncHelper $funcHelper)
    {
        $this->storeServiceService = $storeServiceService;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 取得服務列表
     */
    public function lists(Request $request)
    {
        // 驗證規則
        $rules = [
            'ableType' => "required",
            'ableId' => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            'ableType.required' => "請輸入關聯表model路徑",
            'ableId.integer' => "請輸入關聯表id",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $storeServiceLists = $this->storeServiceService->getLists($request->ableType, $request->ableId);
        return $this->funcHelper->successBack($storeServiceLists);
    }
    /**
     * 取得服務Able資料
     */
    public function getAbleData(Request $request)
    {
        // 驗證規則
        $rules = [
            'serviceId' => "required",
            "ableType" => "required| string",
        ];
        // 驗證錯誤訊息
        $messages = [
            'serviceId.required' => "請輸入服務表id",
            "ableType.required" => "請輸入關聯表類型",
            "ableType.string" => "關聯表類型為字串",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $ableType = $request->ableType === "designer" ? "App\\Models\\User\\UserDesigner" : "App\\Models\\User\\UserOperator";
        $storeService = $this->storeServiceService->getAbleDataById($request->serviceId, $ableType);
        return $this->funcHelper->successBack($storeService);

    }
    /**
     * 取得服務Able資料
     */
    public function getData(Request $request)
    {
        // 驗證規則
        $rules = [
            'serviceId' => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            'serviceId.required' => "請輸入服務表id",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $storeService = $this->storeServiceService->getDataById($request->serviceId);
        return $this->funcHelper->successBack($storeService);

    }
}
