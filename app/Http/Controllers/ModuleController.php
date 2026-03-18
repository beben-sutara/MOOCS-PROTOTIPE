<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use App\Services\CertificateService;
use App\Services\ModuleGatingService;
use App\Services\XpRewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ModuleController extends Controller
{
    protected ModuleGatingService $gatingService;
    protected XpRewardService $xpRewardService;
    protected CertificateService $certificateService;

    public function __construct(ModuleGatingService $gatingService, XpRewardService $xpRewardService, CertificateService $certificateService)
    {
        $this->gatingService = $gatingService;
        $this->xpRewardService = $xpRewardService;
        $this->certificateService = $certificateService;
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

        $availableSectionIds = $course->sections()->pluck('id')->all();

        $typeList = implode(',', Module::allTypes());

        return [
            'type'    => 'required|string|in:' . $typeList,
            'title'   => 'required|string|max:255',
            'content' => 'nullable|string',
            'order'   => 'required|integer|min:0',
            'is_locked'       => 'nullable|boolean',
            'is_preview'      => 'nullable|boolean',
            'is_member_access'=> 'nullable|boolean',
            'available_from'  => 'nullable|date',
            'available_until' => 'nullable|date|after_or_equal:available_from',
            'quiz_duration'   => 'nullable|integer|min:1|max:600',
            // File & Audio uploads
            'upload_file'  => 'nullable|file|max:102400',  // 100 MB
            'upload_audio' => 'nullable|file|mimes:mp3,ogg,wav,m4a,aac|max:102400',
            'section_id' => [
                'nullable',
                'integer',
                'in:' . implode(',', $availableSectionIds ?: [0]),
            ],
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

        // Public preview — bypass enrollment & gating
        if ($module->is_preview) {
            return view('modules.show', [
                'course'         => $course,
                'module'         => $module->load('prerequisite'),
                'canManageCourse'=> false,
                'isPreview'      => true,
            ]);
        }

        // Check enrollment
        $enrollment = $user->enrollments()
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            abort(403, 'Anda belum terdaftar dalam kursus ini');
        }

        // Check if module can be accessed (prerequisite)
        $access = $this->gatingService->checkModuleAccess($user, $module);
        if (!$access['can_access']) {
            abort(403, $access['message']);
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

                $this->certificateService->issueCertificate($user, $course, $enrollment);
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

        if ($courseCompleted) {
            $certificate = \App\Models\Certificate::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
            if ($certificate) {
                $response['certificate'] = [
                    'number' => $certificate->certificate_number,
                    'url'    => route('certificates.show', $certificate),
                ];
            }
        }

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

    /** Build the JSON content string based on module type and request inputs. */
    protected function resolveContent(Request $request, string $type, ?string $existingContent = null): ?string
    {
        switch ($type) {
            case Module::TYPE_TEXT:
                return $request->input('content') ?: null;

            case Module::TYPE_YOUTUBE:
                $url = trim($request->input('youtube_url', ''));
                return $url ? json_encode(['url' => $url]) : null;

            case Module::TYPE_IFRAME:
                $code = trim($request->input('iframe_code', ''));
                return $code ? json_encode(['code' => $code]) : null;

            case Module::TYPE_VIDEO_DRM:
                return json_encode([
                    'url'      => trim($request->input('drm_url', '')),
                    'token'    => trim($request->input('drm_token', '')),
                    'provider' => trim($request->input('drm_provider', '')),
                ]);

            case Module::TYPE_COACHING:
                return json_encode([
                    'meeting_link' => trim($request->input('coaching_link', '')),
                    'notes'        => trim($request->input('coaching_notes', '')),
                ]);

            case Module::TYPE_FILE:
                if ($request->hasFile('upload_file')) {
                    $file = $request->file('upload_file');
                    $path = $file->store('modules/files', 'public');
                    return json_encode([
                        'path'          => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'size'          => $file->getSize(),
                        'mime'          => $file->getMimeType(),
                    ]);
                }
                return $existingContent; // keep old file on update

            case Module::TYPE_AUDIO:
                if ($request->hasFile('upload_audio')) {
                    $file = $request->file('upload_audio');
                    $path = $file->store('modules/audio', 'public');
                    return json_encode([
                        'path'          => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'size'          => $file->getSize(),
                    ]);
                }
                return $existingContent; // keep old file on update

            case Module::TYPE_QUIZ:
            case Module::TYPE_TAG:
            default:
                return null;
        }
    }

    public function createForCourse(Course $course)
    {
        if (!$this->canManageCourse($course)) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Anda tidak memiliki izin untuk mengelola modul course ini.');
        }

        $type     = in_array(request('type'), Module::allTypes()) ? request('type') : Module::TYPE_TEXT;
        $quickAdd = (bool) request('quick_add', false);

        // Pre-select section if passed via query string
        $presetSectionId = null;
        if ($quickAdd && request('section_id')) {
            $sec = $course->sections()->find((int) request('section_id'));
            if ($sec) {
                $presetSectionId = $sec->id;
            }
        }

        // Auto-calculate default order
        $baseQuery  = $presetSectionId
            ? $course->modules()->where('section_id', $presetSectionId)
            : $course->modules();
        $defaultOrder = (int) $baseQuery->max('order') + 1;

        return view('modules.create', [
            'course' => $course,
            'module' => new Module([
                'type'       => $type,
                'order'      => $defaultOrder,
                'is_locked'  => false,
                'section_id' => $presetSectionId,
            ]),
            'quickAdd'           => $quickAdd,
            'presetSectionId'    => $presetSectionId,
            'prerequisiteOptions' => $course->modules()->orderBy('order')->get(),
        ]);
    }

    public function storeForCourse(Request $request, Course $course)
    {
        if (!$this->canManageCourse($course)) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Anda tidak memiliki izin untuk mengelola modul course ini.');
        }

        $quickAdd = $request->boolean('quick_add');
        $rules    = $this->moduleValidationRules($course);

        // In quick-add mode the order field is not shown; make it optional
        if ($quickAdd) {
            $rules['order'] = 'nullable|integer|min:0';
        }

        $validated = $request->validate($rules);
        $type      = $validated['type'];

        // Auto-calculate order in quick-add mode
        $sectionId = $validated['section_id'] ?? null;
        if ($quickAdd && !isset($validated['order'])) {
            $baseQuery         = $sectionId
                ? $course->modules()->where('section_id', $sectionId)
                : $course->modules();
            $validated['order'] = (int) $baseQuery->max('order') + 1;
        }

        $module = $course->modules()->create([
            'type'                   => $type,
            'title'                  => $validated['title'],
            'content'                => $this->resolveContent($request, $type),
            'order'                  => $validated['order'],
            'is_locked'              => $request->boolean('is_locked'),
            'is_preview'             => $request->boolean('is_preview'),
            'is_member_access'       => $request->boolean('is_member_access'),
            'available_from'         => $validated['available_from'] ?? null,
            'available_until'        => $validated['available_until'] ?? null,
            'section_id'             => $sectionId,
            'prerequisite_module_id' => $validated['prerequisite_module_id'] ?? null,
            'quiz_duration'          => $type === Module::TYPE_QUIZ ? ($validated['quiz_duration'] ?? null) : null,
            'quiz_one_attempt'       => $type === Module::TYPE_QUIZ ? $request->boolean('quiz_one_attempt') : false,
            'quiz_required_for_next' => $type === Module::TYPE_QUIZ ? $request->boolean('quiz_required_for_next') : false,
        ]);

        if ($type === Module::TYPE_QUIZ) {
            return redirect()->route('questions.index', [$course, $module])
                ->with('success', "Kuis \"{$module->title}\" berhasil dibuat. Tambahkan pertanyaan di bawah ini.");
        }

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
        $type = $validated['type'];

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
            'type'                  => $type,
            'title'                 => $validated['title'],
            'content'               => $this->resolveContent($request, $type, $module->content),
            'order'                 => $validated['order'],
            'is_locked'             => $request->boolean('is_locked'),
            'is_preview'            => $request->boolean('is_preview'),
            'is_member_access'      => $request->boolean('is_member_access'),
            'available_from'        => $validated['available_from'] ?? null,
            'available_until'       => $validated['available_until'] ?? null,
            'section_id'            => $validated['section_id'] ?? null,
            'prerequisite_module_id' => $selectedPrerequisiteId,
            'quiz_duration'          => $type === Module::TYPE_QUIZ ? ($validated['quiz_duration'] ?? null) : null,
            'quiz_one_attempt'       => $type === Module::TYPE_QUIZ ? $request->boolean('quiz_one_attempt') : false,
            'quiz_required_for_next' => $type === Module::TYPE_QUIZ ? $request->boolean('quiz_required_for_next') : false,
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
