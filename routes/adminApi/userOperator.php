<?php
Route::group(['prefix'=>'userOperator', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function () {
    // 新增管理者
    Route::put("/create", "Admin\User\UserOperatorController@create")->middleware(["checkUserPermission:accountControl_operator,per_create"]);
    // 更新管理者詳細資料
    Route::post("/updateInfo", "Admin\User\UserOperatorController@updateInfo")->middleware(["checkUserPermission:accountControl_operator,per_update"]);
    // 取得管理者資料
    Route::post("/getData", "Admin\User\UserOperatorController@getData")->middleware(["checkUserPermission:accountControl_operator,per_read"]);
    // 取得樹狀列表資料
    Route::post("/treeLists", "Admin\User\UserOperatorController@treeLists")->middleware(["checkOperatorUserId"]);
    // 取得列表資料
    Route::post("/lists", "Admin\User\UserOperatorController@lists")->middleware(["checkUserPermission:accountControl_operator,per_read"]);
    // 可選擇管理者列表
    Route::put("/selectLists", "Admin\User\UserOperatorController@selectLists")->middleware(["checkOperatorUserId"]);
    // 更新管理者狀態值
    Route::put("/updateStatus", "Admin\User\UserOperatorController@updateStatus")->middleware(["checkUserPermission:accountControl_operator,per_update"]);
    // 新增管理者服務
    Route::put("/createService", "Admin\User\UserOperatorController@createService")->middleware(["checkUserPermission:webControl_storeService,per_create"]);
    // 更新管理者服務
    Route::put("/updateService", "Admin\User\UserOperatorController@updateService")->middleware(["checkUserPermission:webControl_storeService,per_update"]);
    // 服務項目
    Route::put("/serviceLists", "Admin\User\UserOperatorController@getServiceLists")->middleware(["checkUserPermission:webControl_storeService,per_read"]);
    // 可選擇服務項目
    Route::put("/serviceSelectLists", "Admin\User\UserOperatorController@getServiceSelectLists");
    // 模糊比對搜尋列表
    Route::put("/remoteLists", "Admin\User\UserOperatorController@remoteLists");
});
