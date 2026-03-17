<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE courses MODIFY status ENUM('draft', 'pending_approval', 'published', 'archived') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down()
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("UPDATE courses SET status = 'draft' WHERE status = 'pending_approval'");
            DB::statement("ALTER TABLE courses MODIFY status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft'");
        }
    }
};
