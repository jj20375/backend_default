<?php
Route::group(['prefix'=>'userMember', 'middleware'=>['jwt', 'jwt.role:admin', 'jwt.auth']], function () {
    // 新增會員
    Route::put("/create", "Admin\User\UserMemberController@create")->middleware(["checkOperatorUserId", "checkUserPermission:accountControl_member,per_create"]);
    // 更新會員
    Route::put("/update", "Admin\User\UserMemberController@update")->middleware(["checkOperatorUserId", "checkUserPermission:accountControl_member,per_update"]);
    // 取得會員列表
    Route::post("/lists", "Admin\User\UserMemberController@lists")->middleware(["checkUserPermission:accountControl_member,per_read"]);
    // 指定id底下的會員列表
    Route::post("/listsById", "Admin\User\UserMemberController@listsById")->middleware(["checkOperatorUserId", "checkUserPermission:accountControl_member,per_read"]);
    // 可選擇會員列表
    Route::post("/selectLists", "Admin\User\UserMemberController@selectLists")->middleware(["checkOperatorUserId"]);
    // 取的會員資料
    Route::post("/getData", "Admin\User\UserMemberController@getData")->middleware(["checkUserPermission:accountControl_member,per_read"]);
    // 新增會員點數
    Route::put("/addPoint", "Admin\User\UserMemberController@addPoint")->middleware(["checkOperatorUserId", "checkUserPermission:orderControl_point_order_list,per_create"]);
    // 簡訊名單
    Route::put("/smsSendLists", "Admin\User\UserMemberController@getSmsSendLists")->middleware(["checkUserPermission:otherControl_smsSend,per_read"]);
    // 模糊比對搜尋列表
    Route::put("/remoteLists", "Admin\User\UserMemberController@remoteLists")->middleware(["checkUserPermission:accountControl_member,per_read"]);
});
