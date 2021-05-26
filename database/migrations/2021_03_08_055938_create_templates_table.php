<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->increments('template_id');
            $table->string("name", 100)->comment("樣板名稱");
            $table->json('operator_ids')->nullable()->comment('可使用此樣板管理者id');
            $table->boolean('active')->comment('是否啟用此樣板');
            $table->boolean('public')->comment('是否為共用樣板');
            $table->string('tmp_code', 100)->unique()->comment('樣板代碼');
            $table->string('img_path')->nullable()->comment('樣板預覽圖');
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
        Schema::dropIfExists('templates');
    }
}
