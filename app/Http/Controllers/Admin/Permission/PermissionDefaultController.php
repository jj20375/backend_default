<?php

namespace App\Http\Controllers\Admin\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 導入 PermissionDefault 服務
use App\Services\Permission\PermissionDefaultService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;

class PermissionDefaultController extends Controller
{

    // PermissionDefaultService Service 指定變數
    protected $permissionDefaultService;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    public function __construct(PermissionDefaultService $permissionDefaultService, FuncHelper $funcHelper)
    {
        $this->permissionDefaultService = $permissionDefaultService;
        $this->funcHelper = $funcHelper;
    }

    // 新增預設權限路由
    public function create(Request $request)
    {
        // 驗證規則
        $rules = [
            'permission_rule' => 'required|integer',
            'key' => "required|string",
            'str' => "required|string",
            'is_menu' => "required|boolean",
            'is_option' => "required|boolean",
        ];
        // 驗證錯誤訊息
        $messages = [
            'permission_rule.required' => '請輸入可使用群組權限數字',
            'permission_rule.integer' => '群組權限為數字格式格式',
            'key.required' => '請輸入此權限路由key',
            'key.string' => '權限路由key格式為字串',
            'str.required' => '請輸入此權限路由中文說明',
            'str.string' => '權限路由中文說明格式為字串',
            'is_menu.required' => '請選擇此權限路由是否為選單',
            'is_menu.boolean' => '權限路由選單格式為boolean',
            'is_option.required' => '請選擇此權限路由是否為子功能',
            'is_option.boolean' => '路由是否為子功能路由格式為boolean',
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 新增預設權限路由
        $permissionDefault = $this->permissionDefaultService->create($request->input());
        return $this->funcHelper->successBack($permissionDefault);
    }
    // 更新預設權限路由
    public function update(Request $request)
    {
        // 驗證規則
        $rules = [
            "id" => "required",
            'permission_rule' => 'required|integer',
            'key' => "required|string",
            'str' => "required|string",
            'is_menu' => "required|boolean",
            'is_option' => "required|boolean",
        ];
        // 驗證錯誤訊息
        $messages = [
            "id.required" => "請輸入更新id",
            'permission_rule.required' => '請輸入可使用群組權限數字',
            'permission_rule.integer' => '群組權限為數字格式格式',
            'key.required' => '請輸入此權限路由key',
            'key.string' => '權限路由key格式為字串',
            'str.required' => '請輸入此權限路由中文說明',
            'str.string' => '權限路由中文說明格式為字串',
            'is_menu.required' => '請選擇此權限路由是否為選單',
            'is_menu.boolean' => '權限路由選單格式為boolean',
            'is_option.required' => '請選擇此權限路由是否為子功能',
            'is_option.boolean' => '路由是否為子功能路由格式為boolean',
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 新增預設權限路由
        $permissionDefault = $this->permissionDefaultService->update($request->input());
        return $this->funcHelper->successBack($permissionDefault);
    }
    /**
     * 刪除權限
     */
    public function delete(Request $request)
    {
         // 驗證規則
         $rules = [
            "permissionId" => "required",
        ];
        // 驗證錯誤訊息
        $messages = [
            "permissionId.required" => "請輸入權限id",
        ];
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        $permissionDefault = $this->permissionDefaultService->delete($request->permissionId);
        return $this->funcHelper->successBack($permissionDefault);
    }
    /**
     * 取得列表
     */
    public function treeLists()
    {
        $permissionDefault = $this->permissionDefaultService->getTreeLists();
        return $this->funcHelper->successBack($permissionDefault);
    }

    /**
     * 取得預設權限資料
     * 使用id 取得單一預設權限資料
     */
    public function getDataById(Request $request)
    {
        $permissionDefault = $this->permissionDefaultService->getDataById($request->permissionId);
        return $this->funcHelper->successBack($permissionDefault);
    }
}
