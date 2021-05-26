<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  routeKey type String(字串) 傳入 vue router 對應的 路由key
     * @param  permissionType type String(字串) 傳入 permission_groups 或 permission_users 中 crud 欄位key (per_create, per_read, per_update, per_delete) 
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $routeKey, $permissionType)
    {
        // 判斷登入的使用者使用個人權限或群組權限
        $checkUseGroupPermission = auth()->user()->open_user_permission === 1 ? false : true;
        if($checkUseGroupPermission) { 
            // 判斷群組權限是否有對應的 route key 權限
            if(auth()->user()->group->permissionGroup()->where("key", $routeKey)->where($permissionType, 1)->first() !== null) {
                // 如果有對應的 route key 權限時，取出對應的 crud權限
                $checkRole = auth()->user()->group->permissionGroup()->where("key", $routeKey)->where($permissionType, 1)->first()->$permissionType;
            } else {
                return response()->json(['message' => '權限不足'], 500);
            }
        } else {
            // 判斷個人權限是否有對應的 route key 權限
            if(auth()->user()->permissionUser->where("key", $routeKey)->where($permissionType, 1)->first() !== null) {
                // 如果有對應的 route key 權限時，取出對應的 crud權限
                $checkRole = auth()->user()->permissionUser->where("key", $routeKey)->where($permissionType, 1)->first()->$permissionType;
            } else {
                return response()->json(['message' => '權限不足'], 500);
            }
        }
        // 判斷對應的 crud 權限值是否為 1 如果為1 代表有權限
        if($checkRole === 1) {
            return $next($request);
        } else {
            return response()->json(['message' => '權限不足'], 500);
        }
    }
}
