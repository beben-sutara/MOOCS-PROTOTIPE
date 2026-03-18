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
            $table->timestamp('available_from')->nullable()->after('is_locked');
            $table->timestamp('available_until')->nullable()->after('available_from');
            $table->boolean('is_preview')->default(false)->after('available_until');
        });
    }

    public function down()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['available_from', 'available_until', 'is_preview']);
        });
    }
};
