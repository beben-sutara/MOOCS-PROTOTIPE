<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::where('role', 'user')->get();
        $courses = Course::all();

        // Enroll students di courses
        // Each student enrolls in 2-3 random courses
        foreach ($students as $student) {
            $randomCourses = $courses->random(rand(2, 3));
            
            foreach ($randomCourses as $course) {
                Enrollment::create([
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'status' => 'active',
                ]);
            }
        }

        echo "\n✅ EnrollmentSeeder: Students enrolled in courses\n";
    }
}
