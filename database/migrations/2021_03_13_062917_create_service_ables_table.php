<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceAblesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_ables', function (Blueprint $table) {
            $table->increments('id')->comment("primary key");
            $table->unsignedInteger('service_id')->comment("services表多對多多態 id");
            $table->string('name')->comment("服務名稱");
            $table->morphs('serviceable'); //對應關聯表資料
        });
        Schema::table('service_ables', function(Blueprint $table) {
            $table->foreign('service_id')->references('service_id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_ables');
    }
}
