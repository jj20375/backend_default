<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// UserOperator表 model
use App\Models\User\UserOperator;
// 導入 共用方法
use App\Helpers\FuncHelper;


/**
 * 此判斷用來判斷非系統管者時 登入者可查詢的管理者id 為哪些
 */
class CheckOperatorIdCanUse
{
    // UserOperator Model 指定變數
    protected $userOperator;
    // 導入共用方法 指定變數
    protected $funcHelper;

    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(UserOperator $userOperator, FuncHelper $funcHelper)
    {
        $this->userOperator = $userOperator;
        $this->funcHelper = $funcHelper;
    }

    
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
        // 登入者身份的管理者id
        $loginUserOperatorId = auth()->user()->userable->userOperator->operator_id;
        // 請求的管理者id
        $reqOperatorId = isset($request->operator_id) ? $request->operator_id : $request->operatorId;
        // 取得包含登入者欲下層管理者的id
        $canUserOperatorIds = $this->userOperator->find($loginUserOperatorId)->descendantsAndSelf($loginUserOperatorId)->pluck("operator_id");
        // 當傳入的管理者id 與登入者不符實 需判斷此管理者 是否為登入者下層管理者id 如果不是 則無法執行
        if($request->operator_id != $loginUserOperatorId && $request->operatorId != $loginUserOperatorId) {
            // 判斷請求管理者id 是否為此登入者可查看
            if(in_array($reqOperatorId, $canUserOperatorIds->toArray())) {
                return $next($request);
            } else {
                return $this->funcHelper->errorBack("權限不足", 500);
            }
        } else {
            return $next($request);
        }
    }
}
