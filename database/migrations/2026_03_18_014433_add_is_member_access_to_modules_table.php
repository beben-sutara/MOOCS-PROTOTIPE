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
            $table->boolean('is_member_access')->default(true)->after('is_preview');
        });
    }

    public function down()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('is_member_access');
        });
    }
};
