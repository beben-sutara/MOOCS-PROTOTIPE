<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $budi = User::where('email', 'budi@mooc.local')->first();
        $siti = User::where('email', 'siti@mooc.local')->first();

        $courses = [
            [
                'title' => 'Laravel Fundamentals',
                'description' => 'Pelajari dasar-dasar framework Laravel untuk web development modern',
                'instructor_id' => $budi->id,
                'status' => 'published',
            ],
            [
                'title' => 'PHP Advanced Concepts',
                'description' => 'Mendalami konsep advanced di PHP untuk menjadi developer profesional',
                'instructor_id' => $budi->id,
                'status' => 'published',
            ],
            [
                'title' => 'Database Design & SQL',
                'description' => 'Disain database yang efisien dan SQL query optimization',
                'instructor_id' => $siti->id,
                'status' => 'published',
            ],
            [
                'title' => 'REST API Development',
                'description' => 'Membuat REST API yang scalable dan secure',
                'instructor_id' => $siti->id,
                'status' => 'published',
            ],
            [
                'title' => 'Web Security Best Practices',
                'description' => 'Melindungi aplikasi web dari common security vulnerabilities',
                'instructor_id' => $budi->id,
                'status' => 'published',
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }

        echo "\n✅ CourseSeeder: 5 courses created\n";
    }
}
