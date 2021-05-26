<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 導入 commodities 服務
use App\Services\User\UserService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;
use App\Events\Channel;


class ChatController extends Controller
{
    // UserService Service 指定變數
    protected $userService;
    // 導入共用方法 指定變數
    protected $funcHelper;
      
    public function __construct(UserService $userService, FuncHelper $funcHelper)
    {
        $this->userService = $userService;
        $this->funcHelper = $funcHelper;
    }

    // 發送訊息
    public function sendMessage(Request $request)
    {
        // 驗證規則
        $rules = [
            "message" => "required",
            "account" => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            "message.reuired" => "請輸入訊息",
            "account.reuired" => "請輸入帳號",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 取得使用者資料
        $user = $this->userService->getData();
        $sendData = ["message" => $request->message, "user" => $user["userable"]["name"]];
        broadcast((new Channel($sendData)));
        // return $this->funcHelper->successBack($sendData);
    }
}
