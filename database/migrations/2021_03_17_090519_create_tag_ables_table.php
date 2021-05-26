<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagAblesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tag_ables', function (Blueprint $table) {
            $table->unsignedInteger('tag_id')->comment("tags表多對多多態 id");
            $table->morphs('tagable'); //對應關聯表資料
        });
        Schema::table('tag_ables', function (Blueprint $table) {
            $table->foreign('tag_id')->references('tag_id')->on('tags')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tag_ables');
    }
}
