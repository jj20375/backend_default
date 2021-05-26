<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserOperatorInfosTable extends Migration
{
    /**
     * 店面表 附加功能
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_operator_infos', function (Blueprint $table) {
            $table->increments('id')->comment("店面資訊表id");
            $table->unsignedInteger('operator_id')->nullable()->comment("店面id");
            $table->string('http_type', 10)->default('http')->comment('儲存http或https');
            $table->string('domain', 150)->comment('域名');
            $table->string('port', 5)->nullable()->comment('指定PORT');
            $table->string('logo', 190)->nullable()->comment('網站logo');
            $table->string('web_name', 150)->comment('網站名稱');
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
        Schema::dropIfExists('user_operator_infos');
    }
}
