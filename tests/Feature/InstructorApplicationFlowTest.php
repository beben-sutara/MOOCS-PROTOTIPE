<?php

namespace Tests\Feature;

use App\Models\InstructorApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorApplicationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_instructor_application_page()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get(route('instructor.apply'));

        $response->assertOk();
        $response->assertSee('Ajukan diri Anda sebagai instructor.');
    }

    public function test_user_can_submit_instructor_application()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->post(route('instructor.apply.store'), [
            'expertise' => 'Web Development',
            'motivation' => 'Saya ingin berbagi pengalaman membangun aplikasi Laravel untuk pemula.',
            'experience' => 'Sudah mengajar workshop internal dan membuat beberapa course singkat.',
        ]);

        $response->assertRedirect(route('instructor.apply'));
        $response->assertSessionHas('success', 'Pengajuan instructor berhasil dikirim dan sedang menunggu review admin.');

        $this->assertDatabaseHas('instructor_applications', [
            'user_id' => $user->id,
            'expertise' => 'Web Development',
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_submit_duplicate_pending_instructor_application()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        InstructorApplication::create([
            'user_id' => $user->id,
            'expertise' => 'Frontend Development',
            'motivation' => 'Saya sudah mengajar dan sedang menunggu review.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->post(route('instructor.apply.store'), [
            'expertise' => 'Web Development',
            'motivation' => 'Saya ingin mengajukan ulang walau masih ada yang pending.',
        ]);

        $response->assertRedirect(route('instructor.apply'));
        $response->assertSessionHas('error', 'Anda masih memiliki pengajuan instructor yang sedang direview admin.');
        $this->assertSame(1, InstructorApplication::count());
    }

    public function test_user_can_reapply_after_previous_application_was_rejected()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        InstructorApplication::create([
            'user_id' => $user->id,
            'expertise' => 'Frontend Development',
            'motivation' => 'Pengajuan pertama saya.',
            'status' => 'rejected',
            'admin_notes' => 'Tambahkan detail pengalaman Anda.',
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('instructor.apply.store'), [
            'expertise' => 'Fullstack Development',
            'motivation' => 'Saya memperbarui pengajuan dengan pengalaman yang lebih lengkap.',
            'experience' => 'Sudah membangun LMS internal dan membimbing tim junior.',
        ]);

        $response->assertRedirect(route('instructor.apply'));
        $this->assertSame(2, InstructorApplication::count());
        $this->assertDatabaseHas('instructor_applications', [
            'user_id' => $user->id,
            'expertise' => 'Fullstack Development',
            'status' => 'pending',
        ]);
    }

    public function test_instructor_cannot_access_application_form()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $response = $this->actingAs($instructor)->get(route('instructor.apply'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error', 'Pengajuan instructor hanya tersedia untuk pengguna biasa.');
    }

    public function test_admin_can_view_instructor_application_management_page()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $applicant = User::factory()->create([
            'name' => 'Calon Instructor',
            'role' => 'user',
        ]);

        InstructorApplication::create([
            'user_id' => $applicant->id,
            'expertise' => 'Backend Engineering',
            'motivation' => 'Ingin membantu learner memahami backend Laravel.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.instructor-applications.index'));

        $response->assertOk();
        $response->assertSee('Pengajuan Instructor');
        $response->assertSee('Calon Instructor');
    }

    public function test_non_admin_cannot_view_instructor_application_management_page()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get(route('admin.instructor-applications.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error', 'Halaman ini hanya untuk admin.');
    }

    public function test_admin_can_approve_instructor_application_and_promote_user()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $application = InstructorApplication::create([
            'user_id' => $user->id,
            'expertise' => 'Backend Engineering',
            'motivation' => 'Saya ingin mengajar topik arsitektur backend.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.instructor-applications.update', $application), [
            'status' => 'approved',
            'admin_notes' => 'Profil Anda cocok untuk mulai mengajar.',
        ]);

        $response->assertRedirect(route('admin.instructor-applications.index'));
        $response->assertSessionHas('success', 'Pengajuan instructor berhasil disetujui.');

        $application->refresh();
        $user->refresh();

        $this->assertSame('approved', $application->status);
        $this->assertSame($admin->id, $application->reviewed_by);
        $this->assertNotNull($application->reviewed_at);
        $this->assertSame('instructor', $user->role);
    }

    public function test_admin_can_reject_instructor_application_without_promoting_user()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $application = InstructorApplication::create([
            'user_id' => $user->id,
            'expertise' => 'UI Design',
            'motivation' => 'Saya ingin mengajar desain antarmuka.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.instructor-applications.update', $application), [
            'status' => 'rejected',
            'admin_notes' => 'Mohon lengkapi pengalaman mengajar atau portofolio terlebih dahulu.',
        ]);

        $response->assertRedirect(route('admin.instructor-applications.index'));
        $response->assertSessionHas('success', 'Pengajuan instructor berhasil ditolak.');

        $application->refresh();
        $user->refresh();

        $this->assertSame('rejected', $application->status);
        $this->assertSame('user', $user->role);
        $this->assertSame('Mohon lengkapi pengalaman mengajar atau portofolio terlebih dahulu.', $application->admin_notes);
    }

    public function test_rejecting_application_requires_admin_notes()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $application = InstructorApplication::create([
            'user_id' => $user->id,
            'expertise' => 'DevOps',
            'motivation' => 'Saya ingin membagikan praktik deployment dan observability.',
            'status' => 'pending',
        ]);

        $response = $this->from(route('admin.instructor-applications.index'))
            ->actingAs($admin)
            ->put(route('admin.instructor-applications.update', $application), [
                'status' => 'rejected',
                'admin_notes' => '',
            ]);

        $response->assertRedirect(route('admin.instructor-applications.index'));
        $response->assertSessionHasErrors(['admin_notes']);

        $application->refresh();
        $this->assertSame('pending', $application->status);
    }
}
