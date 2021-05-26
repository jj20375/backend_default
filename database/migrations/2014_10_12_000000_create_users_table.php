<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->unsignedInteger('group_id')->nullable()->comment('群組對應id');
            $table->string('account')->unique()->comment("帳號");
            $table->string('password')->comment("密碼");
            $table->string('remarks')->nullable()->comment('備註');
            $table->integer('login_time')->default(0)->comment("最後登入時間");
            $table->string('last_ip')->nullable()->comment('最後登入ip');
            $table->string('create_ip')->nullable()->comment('創建時ip');
            $table->string('token', 512)->nullable()->comment('token');
            $table->integer('token_time')->default(0)->comment('token時間');
            $table->string('lang', 15)->nullable()->comment('使用語系');
            $table->boolean('open_user_permission')->default(0)->comment('判斷是否啟用個人權限');
            $table->morphs('userable');
            $table->softDeletes()->comment('軟刪除欄位');
            $table->timestamps();
            // $table->string('email')->unique(); 郵箱
            // $table->timestamp('email_verified_at')->nullable(); 郵箱驗證時間
            // $table->rememberToken();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
