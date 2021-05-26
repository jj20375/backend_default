<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_members', function (Blueprint $table) {
            $table->increments('member_id')->comment("primary key");
            $table->unsignedInteger('operator_id')->nullable()->comment("店面id");
            $table->string('account')->unique()->comment('帳號');
            $table->string('name')->comment('姓名');
            $table->string('nickname')->nullable()->comment('暱稱');
            $table->integer('status')->default(5)->comment('管理者狀態 0=停用 1=暫停 5=啟用 9=刪除');
            $table->string('phone')->nullable()->comment('手機號碼');
            $table->date('birthday')->nullable()->comment("生日");
            $table->integer('limit')->nullable()->comment('多少業績可抽成');
            $table->string('note')->nullable()->comment('註記內容');
            $table->softDeletes()->comment('軟刪除欄位');
            $table->timestamps();
        });
        Schema::table('user_members', function (Blueprint $table) {
            $table->foreign('operator_id')->references('operator_id')->on('user_operators')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_members');
    }
}
