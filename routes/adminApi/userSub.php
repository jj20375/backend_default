<?php
Route::group(['prefix'=>'userSub', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function () {
    // 新增子帳號
    Route::put("/create", "Admin\User\UserSubController@create")->middleware(["checkUserPermission:accountControl_sub,per_create"]);
    // 更新子帳號
    Route::put("/update", "Admin\User\UserSubController@update")->middleware(["checkUserPermission:accountControl_sub,per_update"]);
    // 取得子帳號列表
    Route::post("/lists", "Admin\User\UserSubController@lists")->middleware(["checkUserPermission:accountControl_sub,per_read"]);
    // 取得指定id子帳號列表
    Route::post("/listsById", "Admin\User\UserSubController@listsById")->middleware(["checkUserPermission:accountControl_sub,per_read"]);
    // 取得單一子帳號資料
    Route::post("/getData", "Admin\User\UserSubController@getData")->middleware(["checkUserPermission:accountControl_sub,per_read"]);
    // 可選擇子帳號列表
    Route::post("/selectLists", "Admin\User\UserSubController@selectLists")->middleware(["checkOperatorUserId"]);
});
