<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionGroupsTable extends Migration
{
    /**
     * 群組權限表
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_groups', function (Blueprint $table) {
            $table->increments('id')->comment("群組權限id");
            $table->unsignedInteger('group_id')->nullable()->comment('群組對應id');
            $table->unsignedInteger('permission_id')->nullable()->comment('預設權限對應id');
            $table->string('key',150)->index()->comment('KEY對應');
            $table->boolean('per_create')->comment('建立新增權限');
            $table->boolean('per_read')->comment('讀取列表權限');
            $table->boolean('per_update')->comment('更新編輯權限');
            $table->boolean('per_delete')->comment('刪除權限');
            $table->json('options')->nullable()->comment('子項功能');
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
        Schema::dropIfExists('permission_groups');
    }
}
