<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCourseLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_published_courses_only()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        Course::create([
            'title' => 'Published Course',
            'description' => 'Tampil di homepage.',
            'status' => 'published',
            'instructor_id' => $instructor->id,
        ]);

        Course::create([
            'title' => 'Pending Course',
            'description' => 'Belum tampil.',
            'status' => 'pending_approval',
            'instructor_id' => $instructor->id,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Published Course');
        $response->assertDontSee('Pending Course');
    }

    public function test_guest_can_register_from_selected_course_and_get_auto_enrolled()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $course = Course::create([
            'title' => 'Course Pilihan',
            'description' => 'Dipilih sebelum signup.',
            'status' => 'published',
            'instructor_id' => $instructor->id,
        ]);

        $response = $this->post(route('register'), [
            'name' => 'New Student',
            'email' => 'student@example.com',
            'phone' => '+628123456789',
            'password' => 'password',
            'password_confirmation' => 'password',
            'course' => (string) $course->id,
        ]);

        $response->assertRedirect(route('courses.show', $course));

        $user = User::where('email', 'student@example.com')->first();

        $this->assertNotNull($user);
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);
    }

    public function test_guest_can_login_from_selected_course_and_get_auto_enrolled()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $course = Course::create([
            'title' => 'Course Login',
            'description' => 'Dipilih sebelum login.',
            'status' => 'published',
            'instructor_id' => $instructor->id,
        ]);

        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'existing@example.com',
            'password' => 'password',
            'course' => (string) $course->id,
        ]);

        $response->assertRedirect(route('courses.show', $course));
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);
    }
}
