<?php
Route::group(['prefix'=>'group', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function () {
    // 新增群組
    Route::put("/create", "Admin\GroupController@create")->middleware(["checkUserPermission:system_roles,per_create"]);
    // 更新群組
    Route::put("/update", "Admin\GroupController@update")->middleware(["checkUserPermission:system_roles,per_update"]);
    // 群組列表
    Route::post("/lists", "Admin\GroupController@lists")->middleware(["checkUserPermission:system_roles,per_read"]);
    // 可選擇群組列表
    Route::post("/selectLists", "Admin\GroupController@selectLists")->middleware(["checkOperatorUserId"]);
    // 取得群組資料
    Route::post("/getData", "Admin\GroupController@getData")->middleware(["checkUserPermission:system_roles,per_read"]);
});
