<?php
Route::group(['prefix'=>'category', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function () {
    // 新增分類
    Route::put("/create", "Admin\CategoryController@create")->middleware(["checkUserPermission:webControl_category,per_create"]);
    // 更新分類
    Route::put("/update", "Admin\CategoryController@update")->middleware(["checkUserPermission:webControl_category,per_update"]);
    // 取得分類列表k
    Route::post("/lists", "Admin\CategoryController@lists")->middleware(["checkUserPermission:webControl_category,per_read"]);
    // 可選擇分類列表
    Route::post("/selectLists", "Admin\CategoryController@selectLists")->middleware(["checkOperatorUserId"]);
    // 取的分類資料
    Route::post("/getData", "Admin\CategoryController@getData")->middleware(["checkUserPermission:webControl_category,per_read"]);
});
