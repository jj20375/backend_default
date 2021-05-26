<?php
Route::group(['prefix'=>'tag', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function () {
    // 新增標籤
    Route::put("/create", "Admin\TagController@create")->middleware(["checkUserPermission:webControl_tag,per_create"]);
    // 更新標籤
    Route::put("/update", "Admin\TagController@update")->middleware(["checkUserPermission:webControl_tag,per_update"]);
    // 取得標籤列表
    Route::post("/lists", "Admin\TagController@lists")->middleware(["checkUserPermission:webControl_tag,per_read"]);
    // 可選擇標籤列表
    Route::post("/selectLists", "Admin\TagController@selectLists")->middleware(["checkOperatorUserId"]);
    // 取的標籤資料
    Route::post("/getData", "Admin\TagController@getData")->middleware(["checkUserPermission:webControl_tag,per_read"]);
});
