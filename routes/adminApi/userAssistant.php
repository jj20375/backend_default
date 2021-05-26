<?php
Route::group(['prefix'=>'userAssistant', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function () {
    // 新增助理
    Route::post("/create", "Admin\User\UserAssistantController@create")->middleware(["checkOperatorUserId", "checkUserPermission:accountControl_assistant,per_create"]);
    // 更新助理
    Route::post("/update", "Admin\User\UserAssistantController@update")->middleware(["checkOperatorUserId", "checkUserPermission:accountControl_assistant,per_update"]);
    // 助理列表
    Route::post("/lists", "Admin\User\UserAssistantController@lists")->middleware(["checkUserPermission:accountControl_assistant,per_read"]);
    // 指定id底下的助理列表
    Route::post("/listsById", "Admin\User\UserAssistantController@listsById")->middleware(["checkOperatorUserId", "checkUserPermission:accountControl_assistant,per_read"]);
    // 可選擇助理列表
    Route::post("/selectLists", "Admin\User\UserAssistantController@selectLists")->middleware(["checkOperatorUserId"]);
    // 助理單一資料
    Route::post("/getData", "Admin\User\UserAssistantController@getData")->middleware(["checkUserPermission:accountControl_assistant,per_read"]);
    // 刪除助理圖片
    Route::delete("/deleteImage", "Admin\User\UserAssistantController@deleteImage")->middleware(["checkUserPermission:accountControl_assistant,per_delete"]);
});
