<?php
// 預設服務路由方法
Route::group(['prefix'=>'service', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function () {
    // 服務列表
    Route::post("/lists", "Admin\StoreServiceController@lists")->middleware(["checkUserPermission:webControl_storeService,per_read"]);
    // 取得單一服務able資料
    Route::post("/getAbleData", "Admin\StoreServiceController@getAbleData")->middleware(["checkUserPermission:webControl_storeService,per_read"]);
    // 取得單一服務資料
    Route::post("/getData", "Admin\StoreServiceController@getData")->middleware(["checkUserPermission:webControl_storeService,per_read"]);
});