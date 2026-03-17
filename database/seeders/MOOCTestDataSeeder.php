<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\ModuleProgress;
use App\Models\User;
use Illuminate\Database\Seeder;

class MOOCTestDataSeeder extends Seeder
{
    /**
     * Seed the database with test data for MOOC gating logic.
     * 
     * Usage: php artisan db:seed --class=MOOCTestDataSeeder
     */
    public function run(): void
    {
        // Create instructor
        $instructor = User::factory()->create([
            'name' => 'Instructor User',
            'email' => 'instructor@mooc.test',
        ]);

        // Create students
        $student1 = User::factory()->create([
            'name' => 'Student 1',
            'email' => 'student1@mooc.test',
        ]);

        $student2 = User::factory()->create([
            'name' => 'Student 2',
            'email' => 'student2@mooc.test',
        ]);

        // Create a course
        $course = Course::create([
            'title' => 'Advanced Laravel Development',
            'description' => 'Learn advanced Laravel concepts including middleware, services, and policies.',
            'instructor_id' => $instructor->id,
            'status' => 'published',
        ]);

        // Create modules with prerequisite chain
        $module1 = Module::create([
            'course_id' => $course->id,
            'title' => 'Module 1: Introduction to Middleware',
            'content' => 'Learn the basics of Laravel middleware and how to create custom middleware.',
            'order' => 1,
            'is_locked' => false,
            'prerequisite_module_id' => null, // No prerequisite for first module
        ]);

        $module2 = Module::create([
            'course_id' => $course->id,
            'title' => 'Module 2: Advanced Middleware Patterns',
            'content' => 'Explore advanced middleware patterns and best practices.',
            'order' => 2,
            'is_locked' => true,
            'prerequisite_module_id' => $module1->id, // Requires module 1
        ]);

        $module3 = Module::create([
            'course_id' => $course->id,
            'title' => 'Module 3: Service Layer Architecture',
            'content' => 'Design and implement service layers in Laravel applications.',
            'order' => 3,
            'is_locked' => true,
            'prerequisite_module_id' => $module2->id, // Requires module 2
        ]);

        $module4 = Module::create([
            'course_id' => $course->id,
            'title' => 'Module 4: Authorization & Policies',
            'content' => 'Implement authorization using policies and gates.',
            'order' => 4,
            'is_locked' => true,
            'prerequisite_module_id' => $module3->id, // Requires module 3
        ]);

        $module5 = Module::create([
            'course_id' => $course->id,
            'title' => 'Module 5: Final Project',
            'content' => 'Capstone project combining all learned concepts.',
            'order' => 5,
            'is_locked' => true,
            'prerequisite_module_id' => $module4->id, // Requires module 4
        ]);

        // Enroll students in the course
        $enrollment1 = Enrollment::create([
            'user_id' => $student1->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        $enrollment2 = Enrollment::create([
            'user_id' => $student2->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        // Track Student 1's progress (completed modules 1-3, in progress on 4)
        ModuleProgress::create([
            'user_id' => $student1->id,
            'module_id' => $module1->id,
            'is_viewed' => true,
            'is_completed' => true,
            'started_at' => now()->subDays(5),
            'completed_at' => now()->subDays(4),
        ]);

        ModuleProgress::create([
            'user_id' => $student1->id,
            'module_id' => $module2->id,
            'is_viewed' => true,
            'is_completed' => true,
            'started_at' => now()->subDays(3),
            'completed_at' => now()->subDays(2),
        ]);

        ModuleProgress::create([
            'user_id' => $student1->id,
            'module_id' => $module3->id,
            'is_viewed' => true,
            'is_completed' => true,
            'started_at' => now()->subDays(1),
            'completed_at' => now(),
        ]);

        ModuleProgress::create([
            'user_id' => $student1->id,
            'module_id' => $module4->id,
            'is_viewed' => true,
            'is_completed' => false,
            'started_at' => now(),
            'completed_at' => null,
        ]);

        // Module 5 is not accessible yet (prerequisite not met)
        // (No progress record created)

        // Track Student 2's progress (only viewed module 1, not completed)
        ModuleProgress::create([
            'user_id' => $student2->id,
            'module_id' => $module1->id,
            'is_viewed' => true,
            'is_completed' => false,
            'started_at' => now()->subDays(1),
            'completed_at' => null,
        ]);

        // Modules 2-5 are not accessible (prerequisite not met)

        $this->command->info('✓ MOOC test data seeded successfully!');
        $this->command->line('');
        $this->command->info('Test Credentials:');
        $this->command->line('  Instructor: instructor@mooc.test');
        $this->command->line('  Student 1:  student1@mooc.test (3/5 modules completed)');
        $this->command->line('  Student 2:  student2@mooc.test (0/5 modules completed)');
        $this->command->line('');
        $this->command->info('Course Structure:');
        $this->command->line('  Module 1 (No prerequisite) → Module 2 → Module 3 → Module 4 → Module 5');
    }
}
