<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 導入 point_orders 服務
use App\Services\Order\PointOrderService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;

class PointOrderController extends Controller
{
    // PointOrderService Service 指定變數
    protected $pointOrderService;
    // 導入共用方法 指定變數
    protected $funcHelper;
      
    public function __construct(PointOrderService $pointOrderService, FuncHelper $funcHelper)
    {
        $this->pointOrderService = $pointOrderService;
        $this->funcHelper = $funcHelper;
    }

    // 取得列表
    public function lists(Request $request)
    {
        $pointOrderLists = $this->pointOrderService->getLists($request->input());
        return $this->funcHelper->successBack($pointOrderLists);
    }
    /**
     * 取的指定id底下的產品列表資料
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
        $pointOrderLists = $this->pointOrderService->getListsById($request->operatorId, $request->input());
        return $this->funcHelper->successBack($pointOrderLists);
    }
}
