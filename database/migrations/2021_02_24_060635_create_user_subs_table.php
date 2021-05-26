<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSubsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_subs', function (Blueprint $table) {
            $table->increments('sub_id');
            $table->string('account')->unique()->comment("帳號");
            $table->string('name', 100)->comment('名稱');
            $table->boolean('status')->default(5)->comment('帳號狀態 0=停用 1=暫停 5=啟用 9=刪除');
            $table->morphs('subable');
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
        Schema::dropIfExists('user_subs');
    }
}
