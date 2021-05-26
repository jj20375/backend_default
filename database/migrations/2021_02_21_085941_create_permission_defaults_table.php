<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// 導入 無限層套件
use \Kalnoy\Nestedset\NestedSet;
class CreatePermissionDefaultsTable extends Migration
{
    /**
     * 全部權限表
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_defaults', function (Blueprint $table) {
            $table->increments('id')->comment("權限id");
            $table->integer('permission_rule')->default(1)->comment("數位權限判斷 1=系統 2=管理 4=服務提供者 8=助理 16=會員");
            $table->string('key',50)->unique()->comment('KEY對應');
            $table->string('custom_key',100)->nullable()->comment('客製化指定路由key');
            $table->string('str',150)->comment('路由權限說明');
            $table->boolean('is_menu')->comment('是否為選單使用');
            $table->boolean('is_option')->comment('是否為子功能路由');
            $table->json('have_options')->nullable()->comment('用來指定說有哪些子項功能');
            $table->json('route_set')->nullable()->comment('其它路由設定值');
            $table->timestamps();
            NestedSet::columns($table);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_defaults');
    }
}
