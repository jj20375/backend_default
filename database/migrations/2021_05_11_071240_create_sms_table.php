<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms', function (Blueprint $table) {
            $table->increments('sms_id')->comment("primary key");
            $table->unsignedInteger('operator_id')->nullable()->comment("店家id");
            $table->json('operator_ids')->nullable()->comment('可使用此簡訊的店家');
            $table->string('key')->comment("簡訊商代號");
            $table->string('name')->comment("簡訊商名稱");
            $table->string('httpType')->nullable()->comment("判斷是為https還是http");
            $table->string('url')->nullable()->comment("api發送網址");
            $table->integer('status')->default(5)->comment('狀態 0=停用 1=暫停 5=啟用 9=刪除');
            $table->json('key_data')->nullable()->comment('發送時需要使用參數');
            $table->timestamps();
        });
        Schema::table("sms", function (Blueprint $table) {
            $table->foreign("operator_id")->references("operator_id")->on("user_operators")->onDelete("set null");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms');
    }
}
