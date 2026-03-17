<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\ModuleProgress;
use App\Models\User;
use App\Models\UserXpLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_admin_dashboard()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Admin dashboard');
        $response->assertSee('Platform control center');
    }

    public function test_non_admin_cannot_view_admin_dashboard()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $response = $this->actingAs($instructor)->get(route('admin.dashboard'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error', 'Halaman ini hanya untuk admin.');
    }

    public function test_dashboard_redirects_admin_to_admin_dashboard()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_dashboard_shows_platform_statistics()
    {
        $admin = User::factory()->create([
            'name' => 'Platform Admin',
            'role' => 'admin',
        ]);

        $student = User::factory()->create([
            'name' => 'Student Demo',
            'role' => 'user',
        ]);

        $instructor = User::factory()->create([
            'name' => 'Instructor Demo',
            'role' => 'instructor',
        ]);

        $course = Course::create([
            'title' => 'Admin Analytics Course',
            'description' => 'Dipakai untuk admin dashboard.',
            'status' => 'published',
            'instructor_id' => $instructor->id,
        ]);

        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Modul Demo',
            'content' => 'Konten demo.',
            'order' => 1,
            'is_locked' => false,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'enrolled_at' => now(),
            'completed_at' => now(),
        ]);

        ModuleProgress::create([
            'user_id' => $student->id,
            'module_id' => $module->id,
            'is_viewed' => true,
            'is_completed' => true,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        UserXpLog::create([
            'user_id' => $student->id,
            'amount' => 50,
            'source' => 'course_completion',
            'previous_xp' => 0,
            'current_xp' => 50,
            'previous_level' => 1,
            'current_level' => 1,
            'leveled_up' => false,
            'metadata' => ['course_id' => $course->id],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Admin Analytics Course');
        $response->assertSee('Student Demo');
        $response->assertSee('Instructor Demo');
        $response->assertSee('Completion Rate');
    }
}
