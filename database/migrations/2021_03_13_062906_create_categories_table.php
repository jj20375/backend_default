<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('category_id')->commit("primary key");
            $table->string("name")->comment("名稱");
            $table->string("key")->comment("分類代碼");
            $table->boolean('active')->default(1)->comment('判斷是否開啟分類');
            $table->integer('permission_rule')->default(1)->comment("數位權限判斷 1=服務種類");
            $table->json("operator_ids")->nullable()->comment("可使用管理者id");
            $table->morphs('categoryable');
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
        Schema::dropIfExists('categories');
    }
}
