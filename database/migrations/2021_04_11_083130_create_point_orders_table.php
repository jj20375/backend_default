<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePointOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_orders', function (Blueprint $table) {
            $table->increments('point_order_id')->comment("primary key");
            $table->string('order_number')->unique()->comment("訂單號");
            $table->unsignedInteger('member_id')->nullable()->comment("會員id");
            $table->unsignedInteger('operator_id')->nullable()->comment("管理者id");
            $table->unsignedInteger('user_id')->nullable()->comment("操作員users表id");
            $table->integer('before_point')->default(0)->comment("新增前點數");
            $table->integer('after_point')->comment("新增後點數");
            $table->integer('point')->comment("點數");
            $table->string('remarks')->comment("備註");
            $table->softDeletes()->comment('軟刪除欄位');
            $table->timestamps();
        });
        Schema::table('point_orders', function (Blueprint $table) {
            $table->foreign('member_id')->references('member_id')->on('user_members')->onDelete('set null');
            $table->foreign('operator_id')->references('operator_id')->on('user_operators')->onDelete('set null');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('point_orders');
    }
}
