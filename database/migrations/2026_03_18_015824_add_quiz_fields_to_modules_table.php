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
        Schema::table('modules', function (Blueprint $table) {
            $table->unsignedSmallInteger('quiz_duration')->nullable()->after('is_member_access')
                  ->comment('Quiz time limit in minutes; null = no limit');
            $table->boolean('quiz_one_attempt')->default(false)->after('quiz_duration');
            $table->boolean('quiz_required_for_next')->default(false)->after('quiz_one_attempt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['quiz_duration', 'quiz_one_attempt', 'quiz_required_for_next']);
        });
    }
};
