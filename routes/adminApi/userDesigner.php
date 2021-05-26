<?php
Route::group(['prefix'=>'userDesigner', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function () {
    // 新增服務提供者
    Route::post("/create", "Admin\User\UserDesignerController@create")->middleware(["checkOperatorUserId", "checkUserPermission:accountControl_designer,per_create"]);
    // 更新服務提供者
    Route::post("/update", "Admin\User\UserDesignerController@update")->middleware(["checkOperatorUserId", "checkUserPermission:accountControl_designer,per_create"]);
    // 服務提供者列表資料
    Route::post("/lists", "Admin\User\UserDesignerController@lists")->middleware(["checkUserPermission:accountControl_designer,per_read"]);
    // 指定id底下的服務提供者列表
    Route::post("/listsById", "Admin\User\UserDesignerController@listsById")->middleware(["checkOperatorUserId", "checkUserPermission:accountControl_designer,per_read"]);
    // 可選擇服務提供者列表
    Route::post("/selectLists", "Admin\User\UserDesignerController@selectLists")->middleware(["checkOperatorUserId"]);
    // 服務提供者單一資料
    Route::post("/getData", "Admin\User\UserDesignerController@getData")->middleware(["checkUserPermission:accountControl_designer,per_read"]);
    // 刪除服務提供者圖片
    Route::delete("/deleteImage", "Admin\User\UserDesignerController@deleteImage")->middleware(["checkUserPermission:accountControl_designer,per_delete"]);
    // 新增服務提供者服務
    Route::put("/createService", "Admin\User\UserDesignerController@createService")->middleware(["checkUserPermission:accountControl_designer_panel,per_create"]);
    // 更新服務提供者服務
    Route::put("/updateService", "Admin\User\UserDesignerController@updateService")->middleware(["checkUserPermission:accountControl_designer_panel,per_update"]);
    // 服務項目
    Route::put("/serviceLists", "Admin\User\UserDesignerController@getServiceLists")->middleware(["checkUserPermission:accountControl_designer_panel,per_read"]);
    // 可選擇服務項目
    Route::put("/serviceSelectLists", "Admin\User\UserDesignerController@getServiceSelectLists");
});
