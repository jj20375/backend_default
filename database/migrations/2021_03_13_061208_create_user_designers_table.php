<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDesignersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_designers', function (Blueprint $table) {
            $table->increments('designer_id');
            $table->unsignedInteger('operator_id')->nullable()->comment("管理者id");
            $table->string('name', 80)->unique()->comment("名稱");
            $table->string('account')->comment("帳號");
            $table->string('nickname', 80)->nullable()->comment("暱稱");
            $table->date('birthday')->nullable()->comment("生日");
            $table->integer('status')->default(5)->comment('服務提供者狀態 0=停用 1=暫停 5=啟用 9=刪除');
            $table->integer('limit')->nullable()->comment('多少業績可抽成');
            $table->text('note')->nullable()->comment('經歷描述');
            $table->softDeletes()->comment('軟刪除欄位');
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
        Schema::dropIfExists('user_designers');
    }
}
