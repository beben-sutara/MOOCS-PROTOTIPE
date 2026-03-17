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
        Schema::create('user_xp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('amount');
            $table->string('source')->comment('Source of XP: module_completed, quiz_passed, etc');
            $table->bigInteger('previous_xp')->default(0);
            $table->bigInteger('current_xp');
            $table->integer('previous_level')->default(1);
            $table->integer('current_level');
            $table->boolean('leveled_up')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_xp_logs');
    }
};
