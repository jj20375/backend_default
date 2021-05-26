<?php

namespace App\Http\Controllers\Admin\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 導入 PermissionGroup 服務
use App\Services\Permission\PermissionGroupService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;

class PermissionGroupController extends Controller
{
    // PermissionGroupService Service 指定變數
    protected $permissionGroupService;
    // 導入共用方法 指定變數
    protected $funcHelper;

    public function __construct(PermissionGroupService $permissionGroupService, FuncHelper $funcHelper)
    {
        $this->permissionGroupService = $permissionGroupService;
        $this->funcHelper = $funcHelper;
    }

    // 新增權限群組
    public function create(Request $request)
    {
        $datas = $request->input();
        foreach ($datas as $item) {
            // 驗證規則
            $rules = [
                'group_id' => 'required|integer',
                'permission_id' => 'required|integer',
                'key' => "required|string",
                'per_create' => "required|boolean",
                'per_read' => "required|boolean",
                'per_update' => "required|boolean",
                'per_delete' => "required|boolean",
            ];
            // 驗證錯誤訊息
            $messages = [
                'group_id.required' => '請輸入群組id',
                'group_id.integer' => '群組id為數字格式格式',
                'permission_id.required' => '請輸入預設權限表id',
                'permission_id.integer' => '預設權限表id為數字格式',
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
        
        // 新增權限群組
        $permissionGroup = $this->permissionGroupService->create($request->input());
        return $this->funcHelper->successBack($permissionGroup);
    }

    /**
     * 更新群組權限
     */
    public function update(Request $request) {
        $datas = $request->datas;
        foreach ($datas as $item) {
            // 驗證規則
            $rules = [
                'group_id' => 'required|integer',
                'permission_id' => 'required|integer',
                'key' => "required|string",
                'per_create' => "required|boolean",
                'per_read' => "required|boolean",
                'per_update' => "required|boolean",
                'per_delete' => "required|boolean",
            ];
            // 驗證錯誤訊息
            $messages = [
                'group_id.required' => '請輸入群組id',
                'group_id.integer' => '群組id為數字格式格式',
                'permission_id.required' => '請輸入預設權限表id',
                'permission_id.integer' => '預設權限表id為數字格式',
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
         // 判斷如果是管理者第一個群組被更改時且更改者非我本人時，判斷更改失敗
         if (auth()->user()->account !== "jj20375" && $request->groupId == 1) {
            return $this->funcHelper->errorBack("更新失敗", 500);
        }
        // 更新權限群組
        $permissionGroup = $this->permissionGroupService->update($request->datas, $request->groupId);
        return $this->funcHelper->successBack($permissionGroup);
    }

    /**
     * 取得群組權限中可用選單路由
     */
    public function getMenu()
    {
        $permissionGroup = $this->permissionGroupService->getMenu();
        return $this->funcHelper->successBack($permissionGroup);
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
        $permissionList = $this->permissionGroupService->showPermission($request->groupCode);
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
        $permissionTreeList = $this->permissionGroupService->showPermissionTree($request->groupCode);
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
            'groupId' => 'required',
        ];
        // 驗證錯誤訊息
        $messages = [
            'groupCode.required' => '請輸入群組代碼',
            'groupCode.string' => '群組代碼為數字格式格式',
            'groupId.required' => '請輸入群組id',
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $permissionList = $this->permissionGroupService->showPermissionCrud($request->groupCode, $request->groupId);
        return $this->funcHelper->successBack($permissionList);
    }
}
