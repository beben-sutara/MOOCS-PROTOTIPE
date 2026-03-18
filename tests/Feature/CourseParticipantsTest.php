<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\ModuleProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseParticipantsTest extends TestCase
{
    use RefreshDatabase;

    private function createCourseWithInstructor(): array
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::create([
            'title'         => 'Test Course',
            'description'   => 'A test course.',
            'status'        => 'published',
            'instructor_id' => $instructor->id,
        ]);
        return [$instructor, $course];
    }

    public function test_instructor_can_view_participants_of_own_course(): void
    {
        [$instructor, $course] = $this->createCourseWithInstructor();

        $response = $this->actingAs($instructor)
            ->get(route('courses.participants', $course));

        $response->assertOk();
        $response->assertSee('Daftar Peserta');
        $response->assertSee($course->title);
    }

    public function test_instructor_cannot_view_participants_of_other_course(): void
    {
        [, $course] = $this->createCourseWithInstructor();

        $otherInstructor = User::factory()->create(['role' => 'instructor']);

        $response = $this->actingAs($otherInstructor)
            ->get(route('courses.participants', $course));

        $response->assertRedirect(route('courses.manage'));
        $response->assertSessionHas('error');
    }

    public function test_admin_can_view_participants_of_any_course(): void
    {
        [, $course] = $this->createCourseWithInstructor();
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get(route('courses.participants', $course));

        $response->assertOk();
        $response->assertSee('Daftar Peserta');
    }

    public function test_regular_user_cannot_view_participants(): void
    {
        [, $course] = $this->createCourseWithInstructor();
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)
            ->get(route('courses.participants', $course));

        $response->assertRedirect(route('courses.manage'));
        $response->assertSessionHas('error');
    }

    public function test_guest_cannot_view_participants(): void
    {
        [, $course] = $this->createCourseWithInstructor();

        $response = $this->get(route('courses.participants', $course));

        $response->assertRedirect(route('login'));
    }

    public function test_participants_page_shows_enrolled_users(): void
    {
        [$instructor, $course] = $this->createCourseWithInstructor();

        $student1 = User::factory()->create(['name' => 'Budi Peserta', 'role' => 'user']);
        $student2 = User::factory()->create(['name' => 'Ani Belajar', 'role' => 'user']);

        Enrollment::create([
            'user_id'     => $student1->id,
            'course_id'   => $course->id,
            'status'      => 'active',
            'enrolled_at' => now(),
        ]);
        Enrollment::create([
            'user_id'     => $student2->id,
            'course_id'   => $course->id,
            'status'      => 'completed',
            'enrolled_at' => now()->subDays(7),
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($instructor)
            ->get(route('courses.participants', $course));

        $response->assertOk();
        $response->assertSee('Budi Peserta');
        $response->assertSee('Ani Belajar');
        $response->assertSee('Aktif');
        $response->assertSee('Selesai');
    }

    public function test_participants_page_shows_module_progress(): void
    {
        [$instructor, $course] = $this->createCourseWithInstructor();

        $module = Module::create([
            'course_id'  => $course->id,
            'title'      => 'Modul 1',
            'content'    => 'Konten.',
            'order'      => 1,
            'is_locked'  => false,
        ]);

        $student = User::factory()->create(['role' => 'user']);

        Enrollment::create([
            'user_id'     => $student->id,
            'course_id'   => $course->id,
            'status'      => 'active',
            'enrolled_at' => now(),
        ]);

        ModuleProgress::create([
            'user_id'      => $student->id,
            'module_id'    => $module->id,
            'is_viewed'    => true,
            'is_completed' => true,
            'started_at'   => now(),
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($instructor)
            ->get(route('courses.participants', $course));

        $response->assertOk();
        $response->assertSee('1 / 1');
    }

    public function test_participants_page_supports_search_filter(): void
    {
        [$instructor, $course] = $this->createCourseWithInstructor();

        $student1 = User::factory()->create(['name' => 'Cari Saya', 'role' => 'user']);
        $student2 = User::factory()->create(['name' => 'Tidak Muncul', 'role' => 'user']);

        foreach ([$student1, $student2] as $student) {
            Enrollment::create([
                'user_id'     => $student->id,
                'course_id'   => $course->id,
                'status'      => 'active',
                'enrolled_at' => now(),
            ]);
        }

        $response = $this->actingAs($instructor)
            ->get(route('courses.participants', $course) . '?search=Cari+Saya');

        $response->assertOk();
        $response->assertSee('Cari Saya');
        $response->assertDontSee('Tidak Muncul');
    }

    public function test_participants_stats_are_correct(): void
    {
        [$instructor, $course] = $this->createCourseWithInstructor();

        $students = User::factory()->count(3)->create(['role' => 'user']);

        Enrollment::create(['user_id' => $students[0]->id, 'course_id' => $course->id, 'status' => 'active', 'enrolled_at' => now()]);
        Enrollment::create(['user_id' => $students[1]->id, 'course_id' => $course->id, 'status' => 'completed', 'enrolled_at' => now(), 'completed_at' => now()]);
        Enrollment::create(['user_id' => $students[2]->id, 'course_id' => $course->id, 'status' => 'dropped', 'enrolled_at' => now()]);

        $response = $this->actingAs($instructor)
            ->get(route('courses.participants', $course));

        $response->assertOk();
        $response->assertSee('3'); // total
        $response->assertSee('Sedang Belajar');
        $response->assertSee('Selesai');
        $response->assertSee('Dropped');
    }
}
