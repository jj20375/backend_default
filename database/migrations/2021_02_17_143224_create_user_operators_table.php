<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// 導入 無限層套件
use \Kalnoy\Nestedset\NestedSet;

class CreateUserOperatorsTable extends Migration
{
    /**
     * 店面表
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_operators', function (Blueprint $table) {
            $table->increments('operator_id')->comment('店面id');
            $table->string('name')->comment('店面名稱');
            $table->string('account')->unique()->comment('帳號');
            $table->integer('status')->default(5)->comment('管理者狀態 0=停用 1=暫停 5=啟用 9=刪除');
            $table->string('note')->nullable()->comment('註記內容');
            $table->softDeletes()->comment('軟刪除欄位');
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
        Schema::dropIfExists('user_operators');
    }
}
