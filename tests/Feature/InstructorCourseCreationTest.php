<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InstructorCourseCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_can_view_create_course_page()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $response = $this->actingAs($instructor)->get(route('courses.create'));

        $response->assertOk();
        $response->assertSee('Tambah Course Baru');
    }

    public function test_instructor_can_view_manage_courses_page()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        Course::create([
            'title' => 'Course Instructor',
            'description' => 'Milik instructor.',
            'status' => 'draft',
            'instructor_id' => $instructor->id,
        ]);

        $response = $this->actingAs($instructor)->get(route('courses.manage'));

        $response->assertOk();
        $response->assertSee('Kelola Kursus');
        $response->assertSee('Course Instructor');
    }

    public function test_regular_user_cannot_view_manage_courses_page()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get(route('courses.manage'));

        $response->assertRedirect(route('courses.index'));
        $response->assertSessionHas('error', 'Halaman ini hanya untuk instructor atau admin.');
    }

    public function test_instructor_manage_page_only_shows_owned_courses()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $otherInstructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        Course::create([
            'title' => 'Course Saya',
            'description' => 'Course milik saya.',
            'status' => 'published',
            'instructor_id' => $instructor->id,
        ]);

        Course::create([
            'title' => 'Course Orang Lain',
            'description' => 'Bukan milik saya.',
            'status' => 'published',
            'instructor_id' => $otherInstructor->id,
        ]);

        $response = $this->actingAs($instructor)->get(route('courses.manage'));

        $response->assertOk();
        $response->assertSee('Course Saya');
        $response->assertDontSee('Course Orang Lain');
    }

    public function test_admin_manage_page_can_view_all_courses()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $instructorA = User::factory()->create([
            'role' => 'instructor',
        ]);

        $instructorB = User::factory()->create([
            'role' => 'instructor',
        ]);

        Course::create([
            'title' => 'Course A',
            'description' => 'Course A.',
            'status' => 'draft',
            'instructor_id' => $instructorA->id,
        ]);

        Course::create([
            'title' => 'Course B',
            'description' => 'Course B.',
            'status' => 'published',
            'instructor_id' => $instructorB->id,
        ]);

        $response = $this->actingAs($admin)->get(route('courses.manage'));

        $response->assertOk();
        $response->assertSee('Course A');
        $response->assertSee('Course B');
    }

    public function test_regular_user_cannot_view_create_course_page()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get(route('courses.create'));

        $response->assertRedirect(route('courses.index'));
        $response->assertSessionHas('error');
    }

    public function test_instructor_can_create_a_new_course()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $response = $this->actingAs($instructor)->post(route('courses.store'), [
            'title' => 'Laravel untuk Pemula',
            'description' => 'Belajar dasar Laravel dari nol.',
            'status' => 'draft',
        ]);

        $course = Course::first();

        $response->assertRedirect(route('courses.show', $course));
        $response->assertSessionHas('success', 'Course berhasil disimpan sebagai draft.');

        $this->assertNotNull($course);
        $this->assertEquals('Laravel untuk Pemula', $course->title);
        $this->assertEquals('Belajar dasar Laravel dari nol.', $course->description);
        $this->assertEquals('draft', $course->status);
        $this->assertEquals($instructor->id, $course->instructor_id);
    }

    public function test_instructor_can_submit_course_for_admin_approval()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $response = $this->actingAs($instructor)->post(route('courses.store'), [
            'title' => 'Course Menunggu Review',
            'description' => 'Siap direview admin.',
            'status' => 'pending_approval',
        ]);

        $course = Course::first();

        $response->assertRedirect(route('courses.show', $course));
        $response->assertSessionHas('success', 'Course berhasil dibuat dan dikirim untuk review admin.');
        $this->assertNotNull($course);
        $this->assertSame('pending_approval', $course->status);
    }

    public function test_instructor_can_create_course_with_thumbnail()
    {
        Storage::fake('public');

        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $thumbnail = UploadedFile::fake()->image('thumbnail.jpg');

        $response = $this->actingAs($instructor)->post(route('courses.store'), [
            'title' => 'Course Dengan Thumbnail',
            'description' => 'Punya gambar.',
            'status' => 'draft',
            'thumbnail' => $thumbnail,
        ]);

        $course = Course::first();

        $response->assertRedirect(route('courses.show', $course));
        $this->assertNotNull($course->thumbnail_path);
        Storage::disk('public')->assertExists($course->thumbnail_path);
    }

    public function test_instructor_can_replace_course_thumbnail()
    {
        Storage::fake('public');

        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $oldThumbnail = UploadedFile::fake()->image('old.jpg');
        $newThumbnail = UploadedFile::fake()->image('new.jpg');

        $course = Course::create([
            'title' => 'Course Thumbnail',
            'description' => 'Akan diganti gambarnya.',
            'thumbnail_path' => $oldThumbnail->store('course-thumbnails', 'public'),
            'status' => 'draft',
            'instructor_id' => $instructor->id,
        ]);

        $oldPath = $course->thumbnail_path;

        $response = $this->actingAs($instructor)->put(route('courses.update', $course), [
            'title' => 'Course Thumbnail',
            'description' => 'Gambar baru.',
            'status' => 'pending_approval',
            'thumbnail' => $newThumbnail,
        ]);

        $course->refresh();

        $response->assertRedirect(route('courses.show', $course));
        $this->assertNotEquals($oldPath, $course->thumbnail_path);
        $this->assertSame('pending_approval', $course->status);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($course->thumbnail_path);
    }

    public function test_course_thumbnail_must_be_an_image()
    {
        Storage::fake('public');

        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $response = $this->from(route('courses.create'))
            ->actingAs($instructor)
            ->post(route('courses.store'), [
                'title' => 'Course Invalid Thumbnail',
                'description' => 'File salah.',
                'status' => 'draft',
                'thumbnail' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            ]);

        $response->assertRedirect(route('courses.create'));
        $response->assertSessionHasErrors(['thumbnail']);
        $this->assertDatabaseCount('courses', 0);
    }

    public function test_regular_user_cannot_create_a_new_course()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->post(route('courses.store'), [
            'title' => 'Course Rahasia',
            'description' => 'Harusnya tidak bisa dibuat.',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('courses.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('courses', 0);
    }

    public function test_instructor_cannot_view_another_instructors_draft_course()
    {
        $owner = User::factory()->create([
            'role' => 'instructor',
        ]);

        $otherInstructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $course = Course::create([
            'title' => 'Draft Milik Owner',
            'description' => 'Draft private.',
            'status' => 'draft',
            'instructor_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherInstructor)->get(route('courses.show', $course));

        $response->assertRedirect(route('courses.index'));
        $response->assertSessionHas('error', 'Course tidak ditemukan atau belum tersedia.');
    }

    public function test_instructor_cannot_view_another_instructors_pending_approval_course()
    {
        $owner = User::factory()->create([
            'role' => 'instructor',
        ]);

        $otherInstructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $course = Course::create([
            'title' => 'Pending Milik Owner',
            'description' => 'Sedang direview.',
            'status' => 'pending_approval',
            'instructor_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherInstructor)->get(route('courses.show', $course));

        $response->assertRedirect(route('courses.index'));
        $response->assertSessionHas('error', 'Course tidak ditemukan atau belum tersedia.');
    }

    public function test_instructor_cannot_view_other_instructors_published_course_without_enrollment()
    {
        $owner = User::factory()->create([
            'role' => 'instructor',
        ]);

        $otherInstructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $course = Course::create([
            'title' => 'Published Owner',
            'description' => 'Published course.',
            'status' => 'published',
            'instructor_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherInstructor)->get(route('courses.show', $course));

        $response->assertRedirect(route('courses.index'));
        $response->assertSessionHas('error', 'You must enroll in this course first');
    }

    public function test_course_creation_requires_title()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $response = $this->from(route('courses.create'))
            ->actingAs($instructor)
            ->post(route('courses.store'), [
                'title' => '',
                'description' => 'Tanpa judul.',
                'status' => 'draft',
            ]);

        $response->assertRedirect(route('courses.create'));
        $response->assertSessionHasErrors(['title']);
        $this->assertDatabaseCount('courses', 0);
    }

    public function test_course_creation_rejects_title_longer_than_255_characters()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $response = $this->from(route('courses.create'))
            ->actingAs($instructor)
            ->post(route('courses.store'), [
                'title' => str_repeat('A', 256),
                'description' => 'Judul terlalu panjang.',
                'status' => 'draft',
            ]);

        $response->assertRedirect(route('courses.create'));
        $response->assertSessionHasErrors(['title']);
        $this->assertDatabaseCount('courses', 0);
    }

    public function test_course_creation_requires_valid_status()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $response = $this->from(route('courses.create'))
            ->actingAs($instructor)
            ->post(route('courses.store'), [
                'title' => 'Status Tidak Valid',
                'description' => 'Status salah.',
                'status' => 'archived',
            ]);

        $response->assertRedirect(route('courses.create'));
        $response->assertSessionHasErrors(['status']);
        $this->assertDatabaseCount('courses', 0);
    }

    public function test_instructor_cannot_publish_course_directly_on_create()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $response = $this->from(route('courses.create'))
            ->actingAs($instructor)
            ->post(route('courses.store'), [
                'title' => 'Course Publish Langsung',
                'description' => 'Tidak boleh langsung tayang.',
                'status' => 'published',
            ]);

        $response->assertRedirect(route('courses.create'));
        $response->assertSessionHasErrors(['status']);
        $this->assertDatabaseCount('courses', 0);
    }

    public function test_course_creation_requires_status_field()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $response = $this->from(route('courses.create'))
            ->actingAs($instructor)
            ->post(route('courses.store'), [
                'title' => 'Tanpa Status',
                'description' => 'Status belum dipilih.',
            ]);

        $response->assertRedirect(route('courses.create'));
        $response->assertSessionHasErrors(['status']);
        $this->assertDatabaseCount('courses', 0);
    }

    public function test_instructor_can_view_edit_course_page()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $course = Course::create([
            'title' => 'Course Lama',
            'description' => 'Deskripsi lama.',
            'status' => 'draft',
            'instructor_id' => $instructor->id,
        ]);

        $response = $this->actingAs($instructor)->get(route('courses.edit', $course));

        $response->assertOk();
        $response->assertSee('Edit Course');
        $response->assertSee('Course Lama');
    }

    public function test_instructor_can_update_owned_course()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $course = Course::create([
            'title' => 'Course Lama',
            'description' => 'Deskripsi lama.',
            'status' => 'draft',
            'instructor_id' => $instructor->id,
        ]);

        $response = $this->actingAs($instructor)->put(route('courses.update', $course), [
            'title' => 'Course Baru',
            'description' => 'Deskripsi baru.',
            'status' => 'pending_approval',
        ]);

        $response->assertRedirect(route('courses.show', $course));
        $response->assertSessionHas('success', 'Course berhasil diperbarui dan dikirim untuk review admin.');
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'title' => 'Course Baru',
            'description' => 'Deskripsi baru.',
            'status' => 'pending_approval',
        ]);
    }

    public function test_instructor_cannot_publish_owned_course_directly_on_update()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $course = Course::create([
            'title' => 'Course Draft',
            'description' => 'Masih draft.',
            'status' => 'draft',
            'instructor_id' => $instructor->id,
        ]);

        $response = $this->from(route('courses.edit', $course))
            ->actingAs($instructor)
            ->put(route('courses.update', $course), [
                'title' => 'Course Draft',
                'description' => 'Masih draft.',
                'status' => 'published',
            ]);

        $response->assertRedirect(route('courses.edit', $course));
        $response->assertSessionHasErrors(['status']);

        $course->refresh();
        $this->assertSame('draft', $course->status);
    }

    public function test_instructor_cannot_update_other_instructors_course()
    {
        $owner = User::factory()->create([
            'role' => 'instructor',
        ]);

        $otherInstructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $course = Course::create([
            'title' => 'Course Owner',
            'description' => 'Deskripsi.',
            'status' => 'draft',
            'instructor_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherInstructor)->put(route('courses.update', $course), [
            'title' => 'Diubah Orang Lain',
            'description' => 'Tidak boleh.',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('courses.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'title' => 'Course Owner',
        ]);
    }

    public function test_user_cannot_enroll_in_pending_approval_course()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $student = User::factory()->create([
            'role' => 'user',
        ]);

        $course = Course::create([
            'title' => 'Belum Tayang',
            'description' => 'Masih menunggu review.',
            'status' => 'pending_approval',
            'instructor_id' => $instructor->id,
        ]);

        $response = $this->actingAs($student)->post(route('courses.enroll', $course));

        $response->assertRedirect(route('courses.index'));
        $response->assertSessionHas('error', 'Course belum tersedia untuk enrollment.');
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_instructor_can_delete_owned_course()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $course = Course::create([
            'title' => 'Course Hapus',
            'description' => 'Akan dihapus.',
            'status' => 'draft',
            'instructor_id' => $instructor->id,
        ]);

        $response = $this->actingAs($instructor)->delete(route('courses.destroy', $course));

        $response->assertRedirect(route('courses.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('courses', [
            'id' => $course->id,
        ]);
    }

    public function test_instructor_can_create_module_for_owned_course()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $course = Course::create([
            'title' => 'Course Modul',
            'description' => 'Course untuk modul.',
            'status' => 'draft',
            'instructor_id' => $instructor->id,
        ]);

        $response = $this->actingAs($instructor)->post(route('modules.store', $course), [
            'title' => 'Modul 1',
            'content' => 'Isi modul 1',
            'order' => 1,
            'is_locked' => '1',
        ]);

        $response->assertRedirect(route('courses.show', $course));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('modules', [
            'course_id' => $course->id,
            'title' => 'Modul 1',
            'order' => 1,
            'is_locked' => 1,
        ]);
    }

    public function test_instructor_can_update_module_for_owned_course()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $course = Course::create([
            'title' => 'Course Modul',
            'description' => 'Course untuk modul.',
            'status' => 'draft',
            'instructor_id' => $instructor->id,
        ]);

        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Modul Lama',
            'content' => 'Isi lama',
            'order' => 1,
            'is_locked' => false,
        ]);

        $response = $this->actingAs($instructor)->put(route('modules.update', ['course' => $course, 'module' => $module]), [
            'title' => 'Modul Baru',
            'content' => 'Isi baru',
            'order' => 2,
            'prerequisite_module_id' => '',
        ]);

        $response->assertRedirect(route('courses.show', $course));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('modules', [
            'id' => $module->id,
            'title' => 'Modul Baru',
            'content' => 'Isi baru',
            'order' => 2,
            'is_locked' => 0,
        ]);
    }

    public function test_instructor_cannot_create_circular_module_prerequisite()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $course = Course::create([
            'title' => 'Course Circular',
            'description' => 'Tes circular prerequisite.',
            'status' => 'draft',
            'instructor_id' => $instructor->id,
        ]);

        $moduleA = Module::create([
            'course_id' => $course->id,
            'title' => 'Modul A',
            'content' => 'A',
            'order' => 1,
            'is_locked' => false,
        ]);

        $moduleB = Module::create([
            'course_id' => $course->id,
            'title' => 'Modul B',
            'content' => 'B',
            'order' => 2,
            'is_locked' => true,
            'prerequisite_module_id' => $moduleA->id,
        ]);

        $moduleA->update([
            'prerequisite_module_id' => $moduleB->id,
        ]);

        $response = $this->from(route('modules.edit', ['course' => $course, 'module' => $moduleB]))
            ->actingAs($instructor)
            ->put(route('modules.update', ['course' => $course, 'module' => $moduleB]), [
                'title' => 'Modul B',
                'content' => 'B',
                'order' => 2,
                'is_locked' => '1',
                'prerequisite_module_id' => $moduleA->id,
            ]);

        $response->assertRedirect(route('modules.edit', ['course' => $course, 'module' => $moduleB]));
        $response->assertSessionHasErrors(['prerequisite_module_id']);
    }

    public function test_instructor_can_delete_module_for_owned_course()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $course = Course::create([
            'title' => 'Course Modul',
            'description' => 'Course untuk modul.',
            'status' => 'draft',
            'instructor_id' => $instructor->id,
        ]);

        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Modul Hapus',
            'content' => 'Akan dihapus',
            'order' => 1,
            'is_locked' => false,
        ]);

        $response = $this->actingAs($instructor)->delete(route('modules.destroy', ['course' => $course, 'module' => $module]));

        $response->assertRedirect(route('courses.show', $course));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('modules', [
            'id' => $module->id,
        ]);
    }

    public function test_regular_user_cannot_manage_modules()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $course = Course::create([
            'title' => 'Course Privat',
            'description' => 'Milik instructor.',
            'status' => 'published',
            'instructor_id' => $instructor->id,
        ]);

        $response = $this->actingAs($user)->post(route('modules.store', $course), [
            'title' => 'Modul Ilegal',
            'content' => 'Tidak boleh dibuat.',
            'order' => 1,
        ]);

        $response->assertRedirect(route('courses.show', $course));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('modules', 0);
    }
}
