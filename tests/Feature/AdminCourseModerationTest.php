<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCourseModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_course_moderation_page()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $instructor = User::factory()->create([
            'name' => 'Instructor Moderation',
            'role' => 'instructor',
        ]);

        Course::create([
            'title' => 'Moderated Course',
            'description' => 'Course untuk moderasi.',
            'status' => 'pending_approval',
            'instructor_id' => $instructor->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.courses.index'));

        $response->assertOk();
        $response->assertSee('Course moderation');
        $response->assertSee('Moderated Course');
        $response->assertSee('Instructor Moderation');
    }

    public function test_non_admin_cannot_view_course_moderation_page()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $response = $this->actingAs($instructor)->get(route('admin.courses.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error', 'Halaman ini hanya untuk admin.');
    }

    public function test_admin_can_update_course_status_from_moderation_page()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $course = Course::create([
            'title' => 'Course Status',
            'description' => 'Akan diubah statusnya.',
            'status' => 'draft',
            'instructor_id' => $instructor->id,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.courses.status.update', $course), [
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Status course berhasil diperbarui.');

        $course->refresh();
        $this->assertSame('published', $course->status);
    }

    public function test_admin_can_filter_courses_by_status()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        Course::create([
            'title' => 'Draft Course',
            'description' => 'Masih draft.',
            'status' => 'draft',
            'instructor_id' => $instructor->id,
        ]);

        Course::create([
            'title' => 'Published Course',
            'description' => 'Sudah tayang.',
            'status' => 'published',
            'instructor_id' => $instructor->id,
        ]);

        Course::create([
            'title' => 'Pending Course',
            'description' => 'Sedang review.',
            'status' => 'pending_approval',
            'instructor_id' => $instructor->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.courses.index', ['status' => 'pending_approval']));

        $response->assertOk();
        $response->assertSee('Pending Course');
        $response->assertDontSee('Draft Course');
        $response->assertDontSee('Published Course');
    }
}
