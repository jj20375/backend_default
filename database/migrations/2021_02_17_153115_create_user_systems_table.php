<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSystemsTable extends Migration
{
    /**
     * 系統管理使用者表
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_systems', function (Blueprint $table) {
            $table->increments('system_id');
            $table->string('account')->unique()->comment('名稱');
            $table->string('name')->comment('系統使用者名稱');
            $table->integer('status')->default(5)->comment('系統使用者狀態 0=停用 1=暫停 5=啟用 9=刪除');
            $table->string('note')->nullable()->comment('註記內容');
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
        Schema::dropIfExists('user_systems');
    }
}
