<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\ModuleProgress;
use App\Models\User;
use App\Models\UserXpLog;
use App\Services\ModuleGatingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleGatingTest extends TestCase
{
    use RefreshDatabase;

    protected ModuleGatingService $gatingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gatingService = app(ModuleGatingService::class);
    }

    // =========================================================================
    // Test Cases for CheckModuleAccess Middleware
    // =========================================================================

    public function test_unauthenticated_user_is_redirected_to_login()
    {
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);

        $response = $this->get(route('courses.modules.show', ['course' => $course, 'module' => $module]));

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_not_enrolled_cannot_access_module()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);

        $response = $this->actingAs($user)->get(
            route('courses.modules.show', ['course' => $course, 'module' => $module])
        );

        $response->assertForbidden();
        // Should contain message about not being enrolled
    }

    public function test_enrolled_user_can_access_unlocked_module()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $module = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => false,
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(
            route('courses.modules.show', ['course' => $course, 'module' => $module])
        );

        $response->assertSuccessful();
    }

    public function test_module_is_marked_as_viewed_when_accessed()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $module = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => false,
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)->get(
            route('courses.modules.show', ['course' => $course, 'module' => $module])
        );

        $this->assertTrue(
            $user->moduleProgress()
                ->where('module_id', $module->id)
                ->where('is_viewed', true)
                ->exists()
        );
    }

    // =========================================================================
    // Test Cases for Prerequisite Validation
    // =========================================================================

    public function test_user_cannot_access_locked_module_without_prerequisite()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $module1 = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => false,
        ]);

        $module2 = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => true,
            'prerequisite_module_id' => $module1->id,
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(
            route('courses.modules.show', ['course' => $course, 'module' => $module2])
        );

        $response->assertForbidden();
    }

    public function test_user_can_access_module_after_completing_prerequisite()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $module1 = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => false,
        ]);

        $module2 = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => true,
            'prerequisite_module_id' => $module1->id,
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        // Mark module1 as completed
        ModuleProgress::create([
            'user_id' => $user->id,
            'module_id' => $module1->id,
            'is_completed' => true,
        ]);

        $response = $this->actingAs($user)->get(
            route('courses.modules.show', ['course' => $course, 'module' => $module2])
        );

        $response->assertSuccessful();
    }

    public function test_prerequisite_chain_validation()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        // Create chain: module1 -> module2 -> module3
        $module1 = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => false,
        ]);

        $module2 = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => true,
            'prerequisite_module_id' => $module1->id,
        ]);

        $module3 = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => true,
            'prerequisite_module_id' => $module2->id,
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        // User should not access module3 without completing module2
        $response = $this->actingAs($user)->get(
            route('courses.modules.show', ['course' => $course, 'module' => $module3])
        );
        $response->assertForbidden();

        // Complete module1 and module2
        ModuleProgress::create([
            'user_id' => $user->id,
            'module_id' => $module1->id,
            'is_completed' => true,
        ]);
        ModuleProgress::create([
            'user_id' => $user->id,
            'module_id' => $module2->id,
            'is_completed' => true,
        ]);

        // Now user should access module3
        $response = $this->actingAs($user)->get(
            route('courses.modules.show', ['course' => $course, 'module' => $module3])
        );
        $response->assertSuccessful();
    }

    // =========================================================================
    // Test Cases for ModuleGatingService
    // =========================================================================

    public function test_check_module_access_for_non_enrolled_user()
    {
        $user = User::factory()->create();
        $module = Module::factory()->create();

        $result = $this->gatingService->checkModuleAccess($user, $module);

        $this->assertFalse($result['can_access']);
        $this->assertEquals('not_enrolled', $result['reason']);
    }

    public function test_check_module_access_for_locked_module()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $module1 = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => false,
        ]);

        $module2 = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => true,
            'prerequisite_module_id' => $module1->id,
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $result = $this->gatingService->checkModuleAccess($user, $module2);

        $this->assertFalse($result['can_access']);
        $this->assertEquals('prerequisite_not_met', $result['reason']);
    }

    public function test_get_accessible_modules()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $module1 = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => false,
            'order' => 1,
        ]);

        $module2 = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => true,
            'prerequisite_module_id' => $module1->id,
            'order' => 2,
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $modules = $this->gatingService->getAccessibleModules($user, $course->id);

        $this->assertCount(2, $modules);
        $this->assertTrue($modules[0]->can_access);
        $this->assertFalse($modules[1]->can_access);
    }

    public function test_complete_module()
    {
        $user = User::factory()->create();
        $module = Module::factory()->create(['is_locked' => false]);

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $module->course_id,
            'status' => 'active',
        ]);

        $this->gatingService->completeModule($user, $module);

        $this->assertTrue(
            $user->moduleProgress()
                ->where('module_id', $module->id)
                ->where('is_completed', true)
                ->exists()
        );
    }

    public function test_module_completion_awards_xp_only_once()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $module = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => false,
            'order' => 1,
        ]);
        Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => false,
            'order' => 2,
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)->postJson(route('modules.complete', ['course' => $course, 'module' => $module]))
            ->assertOk()
            ->assertJsonPath('xp_awards.module_completion.current_xp', 100)
            ->assertJsonPath('course_completed', false);

        $this->actingAs($user)->postJson(route('modules.complete', ['course' => $course, 'module' => $module]))
            ->assertOk()
            ->assertJsonPath('xp_awards', [])
            ->assertJsonPath('user_summary.current_xp', 100)
            ->assertJsonPath('course_completed', false);

        $this->assertEquals(100, $user->fresh()->xp);
        $this->assertEquals(1, UserXpLog::where('user_id', $user->id)->where('source', 'module_completed')->count());
        $this->assertEquals(0, UserXpLog::where('user_id', $user->id)->where('source', 'course_completed')->count());
    }

    public function test_completing_final_module_marks_course_completed_and_awards_course_xp_once()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $module1 = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => false,
            'order' => 1,
        ]);
        $module2 = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => false,
            'order' => 2,
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)->postJson(route('modules.complete', ['course' => $course, 'module' => $module1]))
            ->assertOk();

        $this->actingAs($user)->postJson(route('modules.complete', ['course' => $course, 'module' => $module2]))
            ->assertOk()
            ->assertJsonPath('course_completed', true)
            ->assertJsonPath('xp_awards.course_completion.current_xp', 700)
            ->assertJsonPath('user_summary.current_xp', 700)
            ->assertJsonPath('course_progress.completed', 2)
            ->assertJsonPath('course_progress.total', 2);

        $this->actingAs($user)->postJson(route('modules.complete', ['course' => $course, 'module' => $module2]))
            ->assertForbidden();

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        $this->assertNotNull($enrollment);
        $this->assertEquals('completed', $enrollment->status);
        $this->assertNotNull($enrollment->completed_at);
        $this->assertEquals(2, UserXpLog::where('user_id', $user->id)->where('source', 'module_completed')->count());
        $this->assertEquals(1, UserXpLog::where('user_id', $user->id)->where('source', 'course_completed')->count());
        $this->assertEquals(700, $user->fresh()->xp);
    }

    public function test_get_course_progress()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $module1 = Module::factory()->create(['course_id' => $course->id]);
        $module2 = Module::factory()->create(['course_id' => $course->id]);
        $module3 = Module::factory()->create(['course_id' => $course->id]);

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        // Complete 2 out of 3 modules
        ModuleProgress::create([
            'user_id' => $user->id,
            'module_id' => $module1->id,
            'is_completed' => true,
        ]);
        ModuleProgress::create([
            'user_id' => $user->id,
            'module_id' => $module2->id,
            'is_completed' => true,
        ]);

        $progress = $this->gatingService->getCourseProgress($user, $course->id);

        $this->assertEquals(2, $progress['completed']);
        $this->assertEquals(3, $progress['total']);
        $this->assertEquals(66.67, $progress['percentage']);
    }

    // =========================================================================
    // Test Cases for Module Policies
    // =========================================================================

    public function test_user_can_view_accessible_module()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $module = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => false,
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $this->assertTrue($user->can('view', $module));
    }

    public function test_user_cannot_view_locked_module()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $module1 = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => false,
        ]);
        $module2 = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => true,
            'prerequisite_module_id' => $module1->id,
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $this->assertFalse($user->can('view', $module2));
    }

    public function test_user_can_complete_accessible_module()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $module = Module::factory()->create([
            'course_id' => $course->id,
            'is_locked' => false,
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $this->assertTrue($user->can('complete', $module));
    }
}
