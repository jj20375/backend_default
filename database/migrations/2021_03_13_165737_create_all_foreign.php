<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllForeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_operator_infos', function (Blueprint $table) {
            $table->foreign('operator_id')->references('operator_id')->on('user_operators')->onDelete('set null');
        });
        Schema::table('user_assistants', function (Blueprint $table) {
            $table->foreign('operator_id')->references('operator_id')->on('user_operators')->onDelete('set null');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('group_id')->references('group_id')->on('groups')->onDelete('set null');
        });
        Schema::table('permission_groups', function (Blueprint $table) {
            $table->foreign('group_id')->references('group_id')->on('groups')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permission_defaults')->onDelete('cascade');
        });
        Schema::table('permission_users', function (Blueprint $table) {
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permission_defaults')->onDelete('cascade');
        });
        Schema::table('services', function (Blueprint $table) {
            $table->foreign('category_id')->references('category_id')->on('categories')->onDelete('set null');
        });
        Schema::table('user_designers', function (Blueprint $table) {
            $table->foreign('operator_id')->references('operator_id')->on('user_operators')->onDelete('set null');
        });

        // Schema::table('user_systems', function (Blueprint $table) {
        //     $table->foreign('account')->references('account')->on('users')->onDelete('cascade');
        // });
        // Schema::table('user_operators', function (Blueprint $table) {
        //     $table->foreign('account')->references('account')->on('users')->onDelete('cascade');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_operator_infos', function(Blueprint $table) {
            $table->dropForeign(['operator_id']);
        });
        Schema::table('user_assistants', function(Blueprint $table) {
            $table->dropForeign(['operator_id']);
        });
        Schema::table('users', function(Blueprint $table) {
            $table->dropForeign(['group_id']);
        });
        Schema::table('permission_groups', function(Blueprint $table) {
            $table->dropForeign(['group_id']);
        });
        Schema::table('permission_users', function(Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('services', function(Blueprint $table) {
            $table->dropForeign(['category_id']);
        });
        Schema::table('user_designers', function(Blueprint $table) {
            $table->dropForeign(['operator_id']);
        });
        // Schema::table('user_systems', function(Blueprint $table) {
        //     $table->dropForeign(['account']);
        // });
        // Schema::table('user_operators', function(Blueprint $table) {
        //     $table->dropForeign(['account']);
        // });
    }
}
