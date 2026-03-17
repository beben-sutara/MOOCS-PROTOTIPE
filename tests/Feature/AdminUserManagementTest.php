<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_management_page()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $managedUser = User::factory()->create([
            'name' => 'Managed User',
            'role' => 'user',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Kelola Pengguna');
        $response->assertSee('Managed User');
    }

    public function test_non_admin_cannot_view_user_management_page()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $response = $this->actingAs($instructor)->get(route('admin.users.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error', 'Halaman ini hanya untuk admin.');
    }

    public function test_admin_can_filter_users_by_role()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Student One',
            'role' => 'user',
        ]);

        User::factory()->create([
            'name' => 'Instructor One',
            'role' => 'instructor',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.index', ['role' => 'instructor']));

        $response->assertOk();
        $response->assertSee('Instructor One');
        $response->assertDontSee('Student One');
    }

    public function test_admin_can_update_user_profile_and_role()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $managedUser = User::factory()->create([
            'name' => 'Before Update',
            'email' => 'before@example.com',
            'phone' => null,
            'role' => 'user',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $managedUser), [
            'name' => 'After Update',
            'email' => 'after@example.com',
            'phone' => '+628123456789',
            'role' => 'instructor',
        ]);

        $response->assertRedirect(route('admin.users.edit', $managedUser));
        $response->assertSessionHas('success', 'Data user berhasil diperbarui.');

        $managedUser->refresh();

        $this->assertSame('After Update', $managedUser->name);
        $this->assertSame('after@example.com', $managedUser->email);
        $this->assertSame('+628123456789', $managedUser->phone);
        $this->assertSame('instructor', $managedUser->role);
    }

    public function test_admin_cannot_remove_admin_role_from_own_account()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->from(route('admin.users.edit', $admin))
            ->actingAs($admin)
            ->put(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'phone' => $admin->phone,
                'role' => 'user',
            ]);

        $response->assertRedirect(route('admin.users.edit', $admin));
        $response->assertSessionHasErrors(['role']);

        $admin->refresh();
        $this->assertSame('admin', $admin->role);
    }

    public function test_admin_can_view_instructor_courses_on_edit_page()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $instructor = User::factory()->create([
            'name' => 'Instructor Owner',
            'role' => 'instructor',
        ]);

        $otherInstructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $ownedCourse = Course::create([
            'title' => 'Laravel Dasar',
            'description' => 'Course milik instructor yang sedang dilihat.',
            'instructor_id' => $instructor->id,
            'status' => 'published',
        ]);

        Course::create([
            'title' => 'Course Orang Lain',
            'description' => 'Tidak boleh muncul di halaman ini.',
            'instructor_id' => $otherInstructor->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.edit', $instructor));

        $response->assertOk();
        $response->assertSee('Daftar kursus instruktur');
        $response->assertSee('Laravel Dasar');
        $response->assertSee(route('courses.show', $ownedCourse));
        $response->assertDontSee('Course Orang Lain');
    }
}
