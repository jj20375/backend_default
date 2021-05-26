<?php
// 預設權限路由方法
Route::group(['prefix'=>'permissionDefault', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function () {
    // 新增預設權限路由資料
    Route::put("/create", "Admin\Permission\PermissionDefaultController@create")->middleware(["checkAdminUser", "checkUserPermission:system_permission_list,per_create"]);
    // 更新預設權限路由資料
    Route::put("/update", "Admin\Permission\PermissionDefaultController@update")->middleware(["checkAdminUser", "checkUserPermission:system_permission_list,per_update"]);
    // 更新預設權限路由資料
    Route::delete("/delete", "Admin\Permission\PermissionDefaultController@delete")->middleware(["checkAdminUser", "checkUserPermission:system_permission_list,per_delete"]);
    // 取得樹狀列表
    Route::get("/treeLists", "Admin\Permission\PermissionDefaultController@treeLists")->middleware(["checkAdminUser", "checkUserPermission:system_permission_list,per_read"]);
    // 取得單一預設權限資料
    Route::post("/getData", "Admin\Permission\PermissionDefaultController@getDataById")->middleware(["checkAdminUser", "checkUserPermission:system_permission_list,per_read"]);
});
// 權限群組路由方法
Route::group(['prefix'=>'permissionGroup', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function () {
    // 新增群組權限
    Route::put("/create", "Admin\Permission\PermissionGroupController@create")->middleware(["checkUserPermission:system_roles,per_create"]);
    // 更新群組權限
    Route::put("/update", "Admin\Permission\PermissionGroupController@update")->middleware(["checkUserPermission:system_roles_update,per_update"]);
    // 取得群組權限專用 選單
    Route::get("/getMenu", "Admin\Permission\PermissionGroupController@getMenu")->middleware(["checkUserPermission:system_roles,per_read"]);
    // 取的新增或更新時 可選擇的預設權限 樹狀列表
    Route::post("/showPermission", "Admin\Permission\PermissionGroupController@showPermission")->middleware(["checkUserPermission:system_roles_update,per_read"]);
    // 取的新增或更新時 可選擇的預設權限 樹狀列表
    Route::post("/showPermissionTree", "Admin\Permission\PermissionGroupController@showPermissionTree")->middleware(["checkUserPermission:system_roles_update,per_read"]);
    // 取的新增或更新時 可選擇的crud 樹狀列表
    Route::post("/showPermissionCrud", "Admin\Permission\PermissionGroupController@showPermissionCrud")->middleware(["checkUserPermission:system_roles_update,per_read"]);
});
// 個人權限路由方法
Route::group(['prefix'=>'permissionUser', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function () {
    // 新增個人權限
    Route::put("/create", "Admin\Permission\PermissionUserController@create")->middleware(["checkUserPermission:accountControl_operator_permissionSet,per_create"]);
    // 更新群組權限
    Route::put("/update", "Admin\Permission\PermissionUserController@update")->middleware(["checkUserPermission:accountControl_operator_permissionSet,per_update"]);
    // 取得個人權限專用 選單
    Route::get("/getMenu", "Admin\Permission\PermissionUserController@getMenu")->middleware(["checkUserPermission:accountControl_operator_permissionSet,per_read"]);
    // 取的新增或更新時 可選擇的預設權限 樹狀列表
    Route::post("/showPermission", "Admin\Permission\PermissionUserController@showPermission")->middleware(["checkUserPermission:accountControl_operator_permissionSet,per_read"]);
    // 取的新增或更新時 可選擇的預設權限 樹狀列表
    Route::post("/showPermissionTree", "Admin\Permission\PermissionUserController@showPermissionTree")->middleware(["checkUserPermission:accountControl_operator_permissionSet,per_read"]);
    // 取的新增或更新時 可選擇的預設權限 樹狀列表
    Route::post("/showPermissionCrud", "Admin\Permission\PermissionUserController@showPermissionCrud")->middleware(["checkUserPermission:accountControl_operator_permissionSet,per_read"]);
});
