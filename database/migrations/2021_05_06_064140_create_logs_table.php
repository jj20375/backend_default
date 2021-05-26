<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->increments('log_id')->comment("primary key");
            $table->unsignedInteger("user_id")->nullable()->comment("操作者id");
            $table->string("type_key")->comment("log分類");
            $table->string("type_name")->comment("log分類");
            $table->json("req_data")->nullable()->comment("請求資料");
            $table->json("res_data")->nullable()->comment("回應資料");
            $table->morphs('log_able');
            $table->timestamps();
        });
        Schema::table('logs', function (Blueprint $table) {
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
}
