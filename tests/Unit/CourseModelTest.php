<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_has_correct_fillable_fields(): void
    {
        $course = new Course();

        $this->assertContains('title', $course->getFillable());
        $this->assertContains('description', $course->getFillable());
        $this->assertContains('instructor_id', $course->getFillable());
        $this->assertContains('status', $course->getFillable());
    }

    public function test_course_belongs_to_instructor(): void
    {
        $course = Course::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $course->instructor()
        );
    }

    public function test_course_has_modules_relationship(): void
    {
        $course = Course::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $course->modules()
        );
    }
}
