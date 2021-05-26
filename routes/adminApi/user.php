<?php
Route::post("/login", "Admin\User\UserController@login");
Route::group(['prefix'=>'user', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function () {
    // 更新使用者
    Route::put("/update", "Admin\User\UserController@update");
    // 取得登入使用者資料
    Route::get("/getData", "Admin\User\UserController@getData");
    // 重新取的 token
    Route::get("/refreshToken", "Admin\User\UserController@refreshToken");
    // 取得伺服器時間
    Route::get("/serverTime", "Admin\User\UserController@getServerTime");
    // 取得伺服器時間
    Route::get("/menu", "Admin\User\UserController@menu");
});
