<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_see_courses_list(): void
    {
        $response = $this->get('/courses');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_see_courses_list(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/courses');

        $response->assertStatus(200);
    }

    public function test_instructor_can_create_course(): void
    {
        $instructor = User::factory()->instructor()->create();

        $response = $this->actingAs($instructor)->post('/courses', [
            'title'       => 'My New Course',
            'description' => 'A description for my course.',
            'status'      => 'draft',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('courses', [
            'title'         => 'My New Course',
            'instructor_id' => $instructor->id,
        ]);
    }

    public function test_regular_user_can_enroll_course(): void
    {
        $user   = User::factory()->create();
        $course = Course::factory()->published()->create();

        $response = $this->actingAs($user)->post("/courses/{$course->id}/enroll");

        $response->assertRedirect();
        $this->assertDatabaseHas('enrollments', [
            'user_id'   => $user->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_user_cannot_enroll_same_course_twice(): void
    {
        $user   = User::factory()->create();
        $course = Course::factory()->published()->create();

        Enrollment::create([
            'user_id'     => $user->id,
            'course_id'   => $course->id,
            'status'      => 'active',
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($user)->post("/courses/{$course->id}/enroll");

        $response->assertStatus(400);
        $this->assertDatabaseCount('enrollments', 1);
    }
}
