<?php
Route::group(['prefix'=>'sms', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function () {
    // 新增簡訊商
    Route::put("/create", "Admin\SmsController@create")->middleware(["checkAdminUser", "checkUserPermission:system_sms_list,per_create"]);
    // 更新簡訊商
    Route::put("/update", "Admin\SmsController@update")->middleware(["checkAdminUser", "checkUserPermission:system_sms_list,per_update"]);
    // 發送簡訊商
    Route::put("/sendMessage", "Admin\SmsController@sendMessage")->middleware(["checkUserPermission:otherControl_smsSend,per_create"]);
    // 簡訊商回傳
    // Route::get("/callback", "Admin\SmsController@callback");
    // 簡訊商需輸入資料
    Route::put("/keyData", "Admin\SmsController@keyData")->middleware(["checkAdminUser", "checkUserPermission:system_sms_list,per_read"]);
    // 所有簡訊商列表
    Route::get("/getProviders", "Admin\SmsController@getProviders")->middleware(["checkAdminUser", "checkUserPermission:system_sms_list,per_read"]);
    // 可選擇列表
    Route::post("/selectLists", "Admin\SmsController@selectLists")->middleware(["checkUserPermission:otherControl_smsSend,per_read"]);
    // 取得列表
    Route::post("/lists", "Admin\SmsController@lists")->middleware(["checkAdminUser", "checkUserPermission:system_sms_list,per_read"]);
    // 取的資料
    Route::post("/getData", "Admin\SmsController@getData")->middleware(["checkAdminUser", "checkUserPermission:system_sms_list,per_read"]);
    // 刪除
    Route::delete("/delete", "Admin\SmsController@delete")->middleware(["checkAdminUser", "checkUserPermission:system_sms_list,per_delete"]);
});
