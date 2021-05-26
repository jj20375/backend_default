<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMemberInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_member_infos', function (Blueprint $table) {
            $table->increments('id')->comment("會員資訊表id");
            $table->unsignedInteger('member_id')->nullable()->comment("會員id");
            $table->string('custom_id')->nullable()->comment("會員自定義編號");
            $table->integer('point')->default(0)->comment('會員點數');
            $table->timestamps();
        });
        Schema::table('user_member_infos', function (Blueprint $table) {
            $table->foreign('member_id')->references('member_id')->on('user_members')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_member_infos');
    }
}
