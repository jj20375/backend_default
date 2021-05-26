<?php
Route::group(['prefix'=>'userSystem', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function(){
    // 新增系統使用者
    Route::put("/create", "Admin\User\UserSystemController@create")->middleware(["checkAdminUser", "checkUserPermission:accountControl_system,per_create"]);
    // 更新系統使用者
    Route::put("/update", "Admin\User\UserSystemController@update")->middleware(["checkAdminUser", "checkUserPermission:accountControl_system,per_update"]);
    // 取得系統使用者列表
    Route::post("/lists", "Admin\User\UserSystemController@lists")->middleware(["checkAdminUser", "checkUserPermission:accountControl_system,per_read"]);
    // 取得系統使用者資料
    Route::post("/getData", "Admin\User\UserSystemController@getData")->middleware(["checkAdminUser", "checkUserPermission:accountControl_system,per_read"]);
});
