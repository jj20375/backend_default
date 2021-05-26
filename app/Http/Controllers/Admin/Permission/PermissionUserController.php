<?php

namespace App\Http\Controllers\Admin\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 導入 PermissionUser 服務
use App\Services\Permission\PermissionUserService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;

class PermissionUserController extends Controller
{
    // PermissionUserService Service 指定變數
    protected $permissionUserService;
    // 導入共用方法 指定變數
    protected $funcHelper;

    public function __construct(PermissionUserService $permissionUserService, FuncHelper $funcHelper)
    {
        $this->permissionUserService = $permissionUserService;
        $this->funcHelper = $funcHelper;
    }

    // 新增個人權限
    public function create(Request $request)
    {
        $datas = $request->input();
        foreach ($datas as $item) {
            // 驗證規則
            $rules = [
                'user_id' => 'required|integer',
                'permission_id' => 'required|integer',
                'key' => "required|string",
                'per_create' => "required|boolean",
                'per_read' => "required|boolean",
                'per_update' => "required|boolean",
                'per_delete' => "required|boolean",
            ];
            // 驗證錯誤訊息
            $messages = [
                'user_id.required' => '請輸入User id',
                'user_id.integer' => '群組id為數字格式格式',
                'permission_id.required' => '請輸入預設權限表id',
                'permission_id.integer' => '預設權限表id為數字格式格式',
                'key.required' => '請輸入此權限路由key',
                'keyc.string' => '權限路由key格式為字串',
                'per_create.required' => '請選擇此權限路由是否有新增權限',
                'per_create.boolean' => '權限路由是否有新增權限格式為boolean',
                'per_read.required' => '請選擇此權限路由是否有讀取權限',
                'per_read.boolean' => '權限路由是否有讀取權限格式為boolean',
                'per_update.required' => '請選擇此權限路由是否有更新權限',
                'per_update.boolean' => '權限路由是否有更新權限格式為boolean',
                'per_delete.required' => '請選擇此權限路由是否有刪除權限',
                'per_delete.boolean' => '權限路由是否有刪除權限格式為boolean',
            ];
            // 表單驗證
            $checkValidate = Validator::make($item, $rules, $messages);
            // 判斷表單驗證是否通過
            if ($checkValidate->fails()) {
                return $this->funcHelper->errorBack($checkValidate->errors(), 500);
            }
        }
        
        // 新增個人權限
        $permissionUser = $this->permissionUserService->create($request->input());
        return $this->funcHelper->successBack($permissionUser);
    }

    /**
     * 更新群組權限
     */
    public function update(Request $request)
    {
        $datas = $request->datas;
        foreach ($datas as $item) {
            // 驗證規則
            $rules = [
                'user_id' => 'required|integer',
                'permission_id' => 'required|integer',
                'key' => "required|string",
                'per_create' => "required|boolean",
                'per_read' => "required|boolean",
                'per_update' => "required|boolean",
                'per_delete' => "required|boolean",
            ];
            // 驗證錯誤訊息
            $messages = [
                'user_id.required' => '請輸入使用者id',
                'user_id.integer' => '使用者id為數字格式格式',
                'permission_id.required' => '請輸入預設權限表id',
                'permission_id.integer' => '預設權限表id為數字格式格式',
                'key.required' => '請輸入此權限路由key',
                'keyc.string' => '權限路由key格式為字串',
                'per_create.required' => '請選擇此權限路由是否有新增權限',
                'per_create.boolean' => '權限路由是否有新增權限格式為boolean',
                'per_read.required' => '請選擇此權限路由是否有讀取權限',
                'per_read.boolean' => '權限路由是否有讀取權限格式為boolean',
                'per_update.required' => '請選擇此權限路由是否有更新權限',
                'per_update.boolean' => '權限路由是否有更新權限格式為boolean',
                'per_delete.required' => '請選擇此權限路由是否有刪除權限',
                'per_delete.boolean' => '權限路由是否有刪除權限格式為boolean',
            ];
            // 表單驗證
            $checkValidate = Validator::make($item, $rules, $messages);
            // 判斷表單驗證是否通過
            if ($checkValidate->fails()) {
                return $this->funcHelper->errorBack($checkValidate->errors(), 500);
            }
        }
        // 更新個人權限
        $permissionUser = $this->permissionUserService->update($request->datas, $request->userId);
        return $this->funcHelper->successBack($permissionUser);
    }

    /**
     * 取得個人權限中可用選單路由
     */
    public function getMenu()
    {
        $permissionUser = $this->permissionUserService->getMenu();
        return $this->funcHelper->successBack($permissionUser);
    }

    /**
    * 取得可選擇的預設權限
    */
    public function showPermission(Request $request)
    {
        // 驗證規則
        $rules = [
            'groupCode' => 'required|string',
        ];
        // 驗證錯誤訊息
        $messages = [
            'groupCode.required' => '請輸入群組代碼',
            'groupCode.string' => '群組代碼為數字格式格式',
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $permissionList = $this->permissionUserService->showPermission($request->groupCode);
        return $this->funcHelper->successBack($permissionList);
    }
    /**
     * 取得可選擇的預設權限樹狀資料
     */
    public function showPermissionTree(Request $request)
    {
        
        // 驗證規則
         $rules = [
            'groupCode' => 'required|string',
        ];
        // 驗證錯誤訊息
        $messages = [
            'groupCode.required' => '請輸入群組代碼',
            'groupCode.string' => '群組代碼為數字格式格式',
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $permissionTreeList = $this->permissionUserService->showPermissionTree($request->groupCode);
        return $this->funcHelper->successBack($permissionTreeList);
    }
    /**
     * 取得可選擇的預設權限Crud值
     */
    public function showPermissionCrud(Request $request)
    {
        // 驗證規則
        $rules = [
            'groupCode' => 'required|string',
            'userId' => 'required|integer',
        ];
        // 驗證錯誤訊息
        $messages = [
            'groupCode.required' => '請輸入群組代碼',
            'groupCode.string' => '群組代碼為數字格式格式',
            'userId.required' => '請輸入使用者id',
            'userId.integer' => '預設使用者id為數字或字串格式',
            // 'userId.string' => '預設使用者id為數字或字串格式2',
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $permissionList = $this->permissionUserService->showPermissionCrud($request->groupCode, $request->userId);
        return $this->funcHelper->successBack($permissionList);
    }
}
