<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAssistantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_assistants', function (Blueprint $table) {
            $table->increments('assistant_id');
            $table->unsignedInteger('operator_id')->nullable()->comment('管理者id');
            $table->string('account')->unique()->comment("帳號");
            $table->string('name', 100)->comment("名稱");
            $table->integer('status')->default(5)->comment('助理狀態 0=停用 1=暫停 5=啟用 9=刪除');
            $table->integer('limit')->nullable()->comment('多少業績可抽成');
            $table->string('note')->nullable()->comment('註記內容');
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
        Schema::dropIfExists('user_assistants');
    }
}
