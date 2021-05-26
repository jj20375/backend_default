<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->increments('group_id')->comment('群組id');
            $table->json('operator_ids')->nullable()->comment('可使用此群組的管理者');
            $table->string('group_name',50)->comment('群組名稱');
            $table->string('group_code',10)->comment('群組主代碼 SYSTEM=系統 OPERATOR=管理 MEMBER=會員');
            $table->boolean('is_sub')->default(0)->comment('判斷是否子帳號專用');
            $table->integer('permission_rule')->default(1)->comment("數位權限判斷 1=系統 2=管理 4=服務提供者 8=助理 16=會員");
            $table->morphs('groupable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groups');
    }
}
