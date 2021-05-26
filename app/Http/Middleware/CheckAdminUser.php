<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * 用來判斷是否為系統身份 非系統身份則無法執行
 */
class CheckAdminUser
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
        $userGroupCode = auth()->user()->group->group_code;
        if ($userGroupCode !== "SYSTEM") {
            return response()->json(["message" => "權限不足"], 500);
        }
        return $next($request);
    }
}
