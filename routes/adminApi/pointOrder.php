<?php
Route::group(['prefix'=>'pointOrder', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function () {
    // 取得點數訂單列表
    Route::post("/lists", "Admin\Order\PointOrderController@lists")->middleware(["checkUserPermission:orderControl_point_order_list,per_read"]);
    // 指定id底下的點數訂單列表
    Route::post("/listsById", "Admin\Order\PointOrderController@listsById")->middleware(["checkOperatorUserId", "checkUserPermission:orderControl_point_order_list,per_read"]);
});
