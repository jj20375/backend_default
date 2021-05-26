<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;
// 導入 log 服務
use App\Services\LogService;
// 導入 共用方法
use App\Helpers\FuncHelper;


class LogController extends Controller
{
    // LogService Service 指定變數
    protected $logService;
    // 導入共用方法 指定變數
    protected $funcHelper;

    public function __construct(LogService $logService, FuncHelper $funcHelper)
    {
        $this->logService = $logService;
        $this->funcHelper = $funcHelper;
    }

    public function getPermissionGroupLogLists(Request $request) {
        $sendData = [
            "type_key" => "permissionGroup_update",
        ];
        $logLists = $this->logService->getPermissionGroupLog($sendData);
        return $this->funcHelper->successBack($logLists);
    }
}
