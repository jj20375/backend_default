<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * 此判斷用來判斷非系統管者時 且傳入管理者id 與登入者不符合時 則無法執行
 */
class CheckOperatorUserId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 判斷是否為系統身份 如果為系統身份可以幫其他人管理者新增或更新
        if (auth()->user()->group->group_code === "SYSTEM") {
            return $next($request);
        }
        // 如果非管理者身份群組 則沒有權限
        // if (auth()->user()->group->group_code !== "OPERATOR") {
        //     return response()->json(['message' => '權限不足'], 500);
        // }
        $operatorId = auth()->user()->userable->userOperator->operator_id;
        // 登入者管理者id 與傳入的管理者id 不符合 則無法進行操作
        if ($request->operator_id != $operatorId && $request->operatorId != $operatorId) {
            return response()->json(['message' => '權限不足'], 500);
        }  else {
            return $next($request);
        }
        
    }
}
