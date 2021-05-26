<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_infos', function (Blueprint $table) {
            $table->increments('id')->commit("primary key 服務詳細資料表id");
            $table->unsignedInteger('service_id')->nullable()->comment('服務表id');
            $table->integer('price')->default(5)->comment('服務價位');
            $table->timestamps();
        });
        Schema::table('service_infos', function (Blueprint $table) {
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
        Schema::dropIfExists('service_infos');
    }
}
