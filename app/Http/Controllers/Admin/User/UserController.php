<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 導入 user 服務
use App\Services\User\UserService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // UserService Service 指定變數
    protected $userService;
    // 導入共用方法
    protected $funcHelper;

    public function __construct(UserService $userService, FuncHelper $funcHelper)
    {
        $this->userService = $userService;
        $this->funcHelper = $funcHelper;
    }

    public function update(Request $request)
    {
        // 驗證規則
        $rules = [
            "user_id" => "required",
            'password' => 'sometimes|string',
            "name" => "sometimes|string",
        ];
        // 驗證錯誤訊息
        $messages = [
            'user_id.required' => '請輸入user_id',
            'password.string' => '密碼為字串格式',
            'password.name' => '名稱為字串格式',
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 更新子關聯表 需要傳送資料
        $infoData = $request->only(["name"]);
        // 更新主表 需要傳送資料
        $data = $request->only(["password", "user_id"]);
        $user = $this->userService->update($data, $infoData);
        return $this->funcHelper->successBack($user);
    }
    

    /**
     * 登入
     */
    public function login(Request $request)
    {
        // 驗證規則
        $rules = [
            'password' => 'required',
            'account' => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            'password.required' => '請輸入密碼',
            'account.required' => '請輸入帳號',
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 登入使用者
        $userLogin = $this->userService->login($request->account, $request->password);
        // 如果登入失敗時
        if(!$userLogin["success"]) {
            return $this->funcHelper->errorBack($userLogin["msg"], 500);
        }
        return $this->funcHelper->successBack($userLogin);
    }

    /**
     * 取得資料
     */
    public function getData()
    {
        $user = $this->userService->getData();
        return $this->funcHelper->successBack($user);
    }

    /**
     * 取得選單資料
     */
    public function menu()
    {
        $menu = $this->userService->getMenu();
        return $this->funcHelper->successBack($menu);
    }

    /**
     * 重新取得登入token
     */
    public function refreshToken()
    {
        $token = $this->userService->refreshToken();
        return $this->funcHelper->successBack($token);
    }

    /*
    取得伺服器時間
    **/
    public function getServerTime()
    {
        $serverTime =$this->userService->getServerTime();
        return $this->funcHelper->successBack($serverTime);
    }
}
