<?php
Route::group(['prefix'=>'image', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function () {
    // 客製化圖片上傳方法以及編輯器圖片上傳方法
    Route::post("/customUpload", "Admin\ImageController@customUpload");
});
