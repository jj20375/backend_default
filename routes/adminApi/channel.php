<?php

use App\Events\Channel;



Route::group(['prefix'=>'channel', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function () {
    // // 新增商品
    // Route::get("/test", function() {
    //     broadcast(new Channel());
    // });

    Route::post("/message", "Admin\ChatController@sendMessage");
});
