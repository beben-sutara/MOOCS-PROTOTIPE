<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use App\Services\ModuleGatingService;
use App\Services\XpRewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ModuleController extends Controller
{
    protected ModuleGatingService $gatingService;
    protected XpRewardService $xpRewardService;

    public function __construct(ModuleGatingService $gatingService, XpRewardService $xpRewardService)
    {
        $this->gatingService = $gatingService;
        $this->xpRewardService = $xpRewardService;
        $this->middleware('auth');
    }

    protected function canManageCourse(Course $course): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return Auth::user()->role === 'admin' || $course->instructor_id === Auth::id();
    }

    protected function ensureModuleBelongsToCourse(Course $course, Module $module): void
    {
        if ($module->course_id !== $course->id) {
            abort(404);
        }
    }

    protected function moduleValidationRules(Course $course, ?Module $module = null): array
    {
        $availablePrerequisites = $course->modules()
            ->when($module, fn ($query) => $query->where('id', '!=', $module->id))
            ->pluck('id')
            ->all();

        return [
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'order' => 'required|integer|min:0',
            'is_locked' => 'nullable|boolean',
            'prerequisite_module_id' => [
                'nullable',
                'integer',
                'in:' . implode(',', $availablePrerequisites ?: [0]),
            ],
        ];
    }

    protected function hasCircularPrerequisite(Module $module, ?int $prerequisiteModuleId): bool
    {
        if (!$prerequisiteModuleId) {
            return false;
        }

        $visited = [];
        $currentPrerequisiteId = $prerequisiteModuleId;

        while ($currentPrerequisiteId) {
            if ($currentPrerequisiteId === $module->id) {
                return true;
            }

            if (in_array($currentPrerequisiteId, $visited, true)) {
                return true;
            }

            $visited[] = $currentPrerequisiteId;
            $currentPrerequisiteId = Module::whereKey($currentPrerequisiteId)->value('prerequisite_module_id');
        }

        return false;
    }

    /**
     * Display all modules for a course.
     *
     * @param  Course  $course
     * @return \Illuminate\Http\Response
     */
    public function index(Course $course)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Check enrollment
        $enrollment = $user->enrollments()
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            abort(403, 'Anda belum terdaftar dalam kursus ini');
        }

        // Get accessible modules with gating logic
        $modules = $this->gatingService->getAccessibleModules($user, $course->id);
        
        // Get course progress
        $progress = $this->gatingService->getCourseProgress($user, $course->id);

        return response()->json([
            'course' => $course,
            'modules' => $modules,
            'progress' => $progress
        ]);
    }

    /**
     * Display the specified module if user has access.
     *
     * @param  Course  $course
     * @param  Module  $module
     * @return \Illuminate\Http\Response
     */
    public function show(Course $course, Module $module)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $this->ensureModuleBelongsToCourse($course, $module);

        if ($this->canManageCourse($course)) {
            return view('modules.show', [
                'course' => $course->load(['modules' => fn ($query) => $query->orderBy('order')]),
                'module' => $module->load('prerequisite'),
                'canManageCourse' => true,
            ]);
        }

        // Check enrollment
        $enrollment = $user->enrollments()
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            abort(403, 'Anda belum terdaftar dalam kursus ini');
        }

        // Authorization check (this is also done by middleware)
        $this->authorize('view', $module);

        // Check if module can be accessed (prerequisite)
        if (!$this->gatingService->checkModuleAccess($user, $module)) {
            abort(403, 'Anda belum menyelesaikan modul prasyarat');
        }

        // Get the current module from the request (set by middleware)
        $currentModule = request()->get('current_module', $module);

        // Check if this is an API request or web request
        if (request()->expectsJson()) {
            return response()->json([
                'module' => $currentModule,
                'content' => $currentModule->content,
                'prerequisites' => $currentModule->prerequisite,
                'next_module' => $this->getNextModule($currentModule),
                'previous_module' => $this->getPreviousModule($currentModule),
                'user_progress' => $user->moduleProgress()
                    ->where('module_id', $currentModule->id)
                    ->first()
            ]);
        }

        // Mark module as viewed
        $user->moduleProgress()
            ->updateOrCreate(
                ['module_id' => $currentModule->id],
                ['is_viewed' => true]
            );

        return view('modules.show', [
            'course' => $course,
            'module' => $currentModule,
            'canManageCourse' => false,
        ]);
    }

    /**
     * Mark module as completed.
     *
     * @param  Course  $course
     * @param  Module  $module
     * @return \Illuminate\Http\Response
     */
    public function complete(Request $request, Course $course, Module $module)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $this->ensureModuleBelongsToCourse($course, $module);

        // Authorization check
        $this->authorize('complete', $module);

        $enrollment = $user->enrollments()
            ->where('course_id', $course->id)
            ->firstOrFail();

        $wasAlreadyCompleted = $user->moduleProgress()
            ->where('module_id', $module->id)
            ->where('is_completed', true)
            ->exists();

        [$progress, $courseProgress, $xpAwards, $courseCompleted] = DB::transaction(function () use (
            $user,
            $module,
            $course,
            $enrollment,
            $wasAlreadyCompleted
        ) {
            $progress = $this->gatingService->completeModule($user, $module);
            $xpAwards = [];
            $courseCompleted = false;

            if (!$wasAlreadyCompleted) {
                $xpAwards['module_completion'] = $this->xpRewardService->awardModuleCompletion($user, $module->id, [
                    'course_id' => $course->id,
                ]);
            }

            $courseProgress = $this->gatingService->getCourseProgress($user, $course->id);

            if (
                $courseProgress['total'] > 0
                && $courseProgress['completed'] === $courseProgress['total']
                && $enrollment->status !== 'completed'
            ) {
                $enrollment->forceFill([
                    'status' => 'completed',
                    'completed_at' => now(),
                ])->save();

                $xpAwards['course_completion'] = $this->xpRewardService->awardCourseCompletion($user, $course->id, [
                    'completed_modules' => $courseProgress['completed'],
                ]);
                $courseCompleted = true;
            }

            return [$progress, $courseProgress, $xpAwards, $courseCompleted];
        });

        $response = [
            'success' => true,
            'message' => 'Modul telah diselesaikan',
            'progress' => $progress,
            'course_progress' => $courseProgress,
            'course_completed' => $courseCompleted,
            'xp_awards' => $xpAwards,
            'user_summary' => $user->fresh()->getXpSummary(),
        ];

        if ($request->expectsJson()) {
            return response()->json($response);
        }

        return response()->json($response);
    }

    /**
     * Get the next module in sequence.
     *
     * @param  Module  $current
     * @return Module|null
     */
    private function getNextModule(Module $current): ?Module
    {
        return Module::where('course_id', $current->course_id)
            ->where('order', '>', $current->order)
            ->orderBy('order')
            ->first();
    }

    /**
     * Get the previous module in sequence.
     *
     * @param  Module  $current
     * @return Module|null
     */
    private function getPreviousModule(Module $current): ?Module
    {
        return Module::where('course_id', $current->course_id)
            ->where('order', '<', $current->order)
            ->orderBy('order', 'desc')
            ->first();
    }

    public function createForCourse(Course $course)
    {
        if (!$this->canManageCourse($course)) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Anda tidak memiliki izin untuk mengelola modul course ini.');
        }

        return view('modules.create', [
            'course' => $course,
            'module' => new Module([
                'order' => (int) $course->modules()->max('order') + 1,
                'is_locked' => false,
            ]),
            'prerequisiteOptions' => $course->modules()->orderBy('order')->get(),
        ]);
    }

    public function storeForCourse(Request $request, Course $course)
    {
        if (!$this->canManageCourse($course)) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Anda tidak memiliki izin untuk mengelola modul course ini.');
        }

        $validated = $request->validate($this->moduleValidationRules($course));

        $module = $course->modules()->create([
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'order' => $validated['order'],
            'is_locked' => $request->boolean('is_locked'),
            'prerequisite_module_id' => $validated['prerequisite_module_id'] ?? null,
        ]);

        return redirect()->route('courses.show', $course)
            ->with('success', "Modul {$module->title} berhasil ditambahkan.");
    }

    public function editForCourse(Course $course, Module $module)
    {
        $this->ensureModuleBelongsToCourse($course, $module);

        if (!$this->canManageCourse($course)) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Anda tidak memiliki izin untuk mengelola modul course ini.');
        }

        return view('modules.edit', [
            'course' => $course,
            'module' => $module,
            'prerequisiteOptions' => $course->modules()
                ->where('id', '!=', $module->id)
                ->orderBy('order')
                ->get(),
        ]);
    }

    public function updateForCourse(Request $request, Course $course, Module $module)
    {
        $this->ensureModuleBelongsToCourse($course, $module);

        if (!$this->canManageCourse($course)) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Anda tidak memiliki izin untuk mengelola modul course ini.');
        }

        $validated = $request->validate($this->moduleValidationRules($course, $module));

        $selectedPrerequisiteId = isset($validated['prerequisite_module_id'])
            ? (int) $validated['prerequisite_module_id']
            : null;

        if ($this->hasCircularPrerequisite($module, $selectedPrerequisiteId)) {
            return back()
                ->withErrors([
                    'prerequisite_module_id' => 'Prasyarat modul menyebabkan siklus yang tidak valid.',
                ])
                ->withInput();
        }

        $module->update([
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'order' => $validated['order'],
            'is_locked' => $request->boolean('is_locked'),
            'prerequisite_module_id' => $selectedPrerequisiteId,
        ]);

        return redirect()->route('courses.show', $course)
            ->with('success', "Modul {$module->title} berhasil diperbarui.");
    }

    public function destroyForCourse(Course $course, Module $module)
    {
        $this->ensureModuleBelongsToCourse($course, $module);

        if (!$this->canManageCourse($course)) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Anda tidak memiliki izin untuk mengelola modul course ini.');
        }

        $moduleTitle = $module->title;
        $module->delete();

        return redirect()->route('courses.show', $course)
            ->with('success', "Modul {$moduleTitle} berhasil dihapus.");
    }
}
