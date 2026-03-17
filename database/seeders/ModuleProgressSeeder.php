<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\Module;
use App\Models\ModuleProgress;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleProgressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $enrollments = Enrollment::all();

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            $modules = $course->modules()->orderBy('order')->get();

            // For each enrolled course, create progress records
            // Simulate: some modules viewed, some completed
            foreach ($modules as $index => $module) {
                $isCompleted = false;
                $isViewed = false;

                // First module: usually viewed
                if ($index == 0) {
                    $isViewed = rand(0, 1) == 1; // 50% chance
                }
                
                // If viewed, might be completed (80% chance)
                if ($isViewed) {
                    $isCompleted = rand(0, 10) <= 8; // 80% chance
                }
                
                // Subsequent modules: only if previous are completed
                if ($index > 0) {
                    $previousModule = $modules[$index - 1];
                    $previousProgress = ModuleProgress::where('user_id', $enrollment->user_id)
                        ->where('module_id', $previousModule->id)
                        ->first();

                    if ($previousProgress && $previousProgress->is_completed) {
                        $isViewed = rand(0, 1) == 1;
                        if ($isViewed) {
                            $isCompleted = rand(0, 10) <= 8;
                        }
                    }
                }

                ModuleProgress::create([
                    'user_id' => $enrollment->user_id,
                    'module_id' => $module->id,
                    'is_viewed' => $isViewed,
                    'is_completed' => $isCompleted,
                ]);
            }
        }

        echo "\n✅ ModuleProgressSeeder: Module progress created\n";
    }
}
