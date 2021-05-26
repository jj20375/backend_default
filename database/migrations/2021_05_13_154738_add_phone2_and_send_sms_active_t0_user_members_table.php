<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhone2AndSendSmsActiveT0UserMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("user_members", function (Blueprint $table) {
            $table->string('phone2')->nullable()->after("phone")->comment('手機號碼2');
            $table->boolean('sendSmsActive')->default(1)->after("phone2")->comment('是否發送簡訊給此會員');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_members', function (Blueprint $table) {
            $table->dropColumn(['phone2', 'sendSmsActive']);
        });
    }
}
