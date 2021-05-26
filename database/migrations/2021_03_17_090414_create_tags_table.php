<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->increments('tag_id')->commit("primary key");
            $table->string("name")->comment("名稱");
            $table->string("key")->unique()->index()->comment("分類代碼");
            $table->boolean('active')->default(1)->comment('判斷是否開啟分類');
            $table->integer('permission_rule')->default(1)->comment("數位權限判斷 1=服務");
            $table->json("operator_ids")->nullable()->comment("可使用管理者id");
            $table->morphs('createuser_able'); //對應關聯表資料
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
        Schema::dropIfExists('tags');
    }
}
