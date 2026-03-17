<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('xp')->default(0)->comment('Experience points');
            $table->integer('level')->default(1)->comment('User level');
            $table->integer('next_level_xp')->default(100)->comment('XP needed for next level');
            $table->timestamp('last_xp_earned_at')->nullable()->comment('Last time user earned XP');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['xp', 'level', 'next_level_xp', 'last_xp_earned_at']);
        });
    }
};
