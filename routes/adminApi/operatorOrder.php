<?php
Route::group(['prefix'=>'operatorOrder', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function () {
    // 新增商家訂單
    Route::put("/create", "Admin\Order\OperatorOrderController@create")->middleware(["checkOperatorUserId", "checkUserPermission:orderControl_operator_order_data,per_create"]);
    // 更新商家訂單
    Route::put("/update", "Admin\Order\OperatorOrderController@update")->middleware(["checkOperatorUserId", "checkUserPermission:orderControl_operator_order_data,per_update"]);
    // 商家訂單列表
    Route::post("/lists", "Admin\Order\OperatorOrderController@lists")->middleware(["checkUserPermission:orderControl_operator_order_list,per_read"]);
    // 指定id底下的商家訂單列表
    Route::post("/listsById", "Admin\Order\OperatorOrderController@listsById")->middleware(["checkOperatorUserId", "checkUserPermission:orderControl_operator_order_list,per_read"]);
    // 取得商家訂單資料
    Route::post("/getData", "Admin\Order\OperatorOrderController@getData")->middleware(["checkUserPermission:orderControl_operator_order_data,per_read"]);
    // // 刪除商家訂單資料
    // Route::delete("/deleteInfo", "Admin\Order\OperatorOrderController@deleteInfo")->middleware(["checkUserPermission:orderControl_operator_order_data,per_delete"]);
});
