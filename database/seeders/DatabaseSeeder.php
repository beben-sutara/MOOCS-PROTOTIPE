<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Run seeders in proper order
        $this->call([
            UserSeeder::class,
            CourseSeeder::class,
            ModuleSeeder::class,
            EnrollmentSeeder::class,
            ModuleProgressSeeder::class,
        ]);
    }
}
