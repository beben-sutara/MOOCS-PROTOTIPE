<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CoursesController extends Controller
{
    protected function isAdmin(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    protected function allowedStatusesForCurrentUser(bool $isUpdate = false): array
    {
        if ($this->isAdmin()) {
            return ['draft', 'pending_approval', 'published', 'archived'];
        }

        return $isUpdate
            ? ['draft', 'pending_approval']
            : ['draft', 'pending_approval'];
    }

    protected function canViewUnpublishedCourse(Course $course): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return $this->isAdmin() || $course->instructor_id === Auth::id();
    }

    protected function courseSavedMessage(Course $course, bool $wasCreated): string
    {
        if ($course->status === 'pending_approval') {
            return $wasCreated
                ? 'Course berhasil dibuat dan dikirim untuk review admin.'
                : 'Course berhasil diperbarui dan dikirim untuk review admin.';
        }

        if ($course->status === 'draft') {
            return $wasCreated
                ? 'Course berhasil disimpan sebagai draft.'
                : 'Course berhasil diperbarui sebagai draft.';
        }

        if ($course->status === 'published') {
            return $wasCreated
                ? 'Course berhasil dipublikasikan.'
                : 'Course berhasil diperbarui dan dipublikasikan.';
        }

        return $wasCreated
            ? 'Course berhasil ditambahkan.'
            : 'Course berhasil diperbarui.';
    }

    protected function canManageCourses(): bool
    {
        return Auth::check() && Auth::user()->role !== 'user';
    }

    protected function canManageCourse(Course $course): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return Auth::user()->role === 'admin' || $course->instructor_id === Auth::id();
    }

    public function index()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $allCourses = Course::where('status', 'published')
            ->with(['modules', 'instructor'])
            ->withCount('enrollments')
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'thumbnail_url' => $course->thumbnail_url,
                    'status' => $course->status,
                    'status_label' => $course->status_label,
                    'status_badge_class' => $course->status_badge_class,
                    'modules' => $course->modules,
                    'instructor' => $course->instructor,
                    'enrollments_count' => $course->enrollments_count,
                ];
            })
            ->toArray();

        $enrolledCourses = [];
        if ($user) {
            $enrolledCourses = $user->enrollments()
                ->where('status', 'active')
                ->with(['course' => function ($query) {
                    $query->with(['modules', 'instructor'])
                        ->withCount('enrollments');
                }])
                ->get()
                ->toArray();
        }

        return view('courses.index', [
            'allCourses' => $allCourses,
            'enrolledCourses' => $enrolledCourses,
        ]);
    }

    /**
     * Show management page for instructor/admin
     */
    public function manage()
    {
        if (!$this->canManageCourses()) {
            return redirect()->route('courses.index')
                ->with('error', 'Halaman ini hanya untuk instructor atau admin.');
        }

        $query = Course::with(['instructor', 'modules'])
            ->withCount('enrollments')
            ->latest();

        if (Auth::user()->role !== 'admin') {
            $query->where('instructor_id', Auth::id());
        }

        $manageableCourses = $query->get();

        return view('courses.manage', [
            'manageableCourses' => $manageableCourses,
            'draftCount' => (clone $query)->where('status', 'draft')->count(),
            'pendingApprovalCount' => (clone $query)->where('status', 'pending_approval')->count(),
            'publishedCount' => (clone $query)->where('status', 'published')->count(),
            'archivedCount' => (clone $query)->where('status', 'archived')->count(),
        ]);
    }

    /**
     * Show create course form
     */
    public function create()
    {
        if (!$this->canManageCourses()) {
            return redirect()->route('courses.index')
                ->with('error', 'Hanya instructor atau admin yang bisa menambahkan course.');
        }

        return view('courses.create', [
            'course' => new Course([
                'status' => 'draft',
            ]),
            'statusOptions' => $this->allowedStatusesForCurrentUser(),
        ]);
    }

    /**
     * Store a new course
     */
    public function store(Request $request)
    {
        if (!$this->canManageCourses()) {
            return redirect()->route('courses.index')
                ->with('error', 'Hanya instructor atau admin yang bisa menambahkan course.');
        }

        $allowedStatuses = $this->allowedStatusesForCurrentUser();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            'status' => 'required|in:' . implode(',', $allowedStatuses),
        ]);

        $thumbnailPath = $request->file('thumbnail')?->store('course-thumbnails', 'public');

        $course = Course::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'thumbnail_path' => $thumbnailPath,
            'status' => $validated['status'],
            'instructor_id' => Auth::id(),
        ]);

        return redirect()->route('courses.show', $course)
            ->with('success', $this->courseSavedMessage($course, true));
    }

    /**
     * Show edit course form
     */
    public function edit(Course $course)
    {
        if (!$this->canManageCourse($course)) {
            return redirect()->route('courses.index')
                ->with('error', 'Anda tidak memiliki izin untuk mengubah course ini.');
        }

        return view('courses.edit', [
            'course' => $course,
            'statusOptions' => $this->allowedStatusesForCurrentUser(true),
        ]);
    }

    /**
     * Update course
     */
    public function update(Request $request, Course $course)
    {
        if (!$this->canManageCourse($course)) {
            return redirect()->route('courses.index')
                ->with('error', 'Anda tidak memiliki izin untuk mengubah course ini.');
        }

        $allowedStatuses = $this->allowedStatusesForCurrentUser(true);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            'status' => 'required|in:' . implode(',', $allowedStatuses),
        ]);

        $thumbnailPath = $course->thumbnail_path;

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail_path) {
                Storage::disk('public')->delete($course->thumbnail_path);
            }

            $thumbnailPath = $request->file('thumbnail')->store('course-thumbnails', 'public');
        }

        $course->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'thumbnail_path' => $thumbnailPath,
            'status' => $validated['status'],
        ]);

        return redirect()->route('courses.show', $course)
            ->with('success', $this->courseSavedMessage($course, false));
    }

    /**
     * Delete course
     */
    public function destroy(Course $course)
    {
        if (!$this->canManageCourse($course)) {
            return redirect()->route('courses.index')
                ->with('error', 'Anda tidak memiliki izin untuk menghapus course ini.');
        }

        $courseTitle = $course->title;

        if ($course->thumbnail_path) {
            Storage::disk('public')->delete($course->thumbnail_path);
        }

        $course->delete();

        return redirect()->route('courses.index')
            ->with('success', "Course {$courseTitle} berhasil dihapus.");
    }

    /**
     * Show course detail with modules
     */
    public function show(Course $course)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (in_array($course->status, ['draft', 'pending_approval'], true) && !$this->canViewUnpublishedCourse($course)) {
            return redirect('/courses')->with('error', 'Course tidak ditemukan atau belum tersedia.');
        }

        $course->load(['modules' => function ($query) {
            $query->orderBy('order');
        }, 'instructor'])
            ->loadCount('enrollments');

        // Check if user is enrolled
        $isEnrolled = $user && $user->enrollments()
            ->where('course_id', $course->id)
            ->exists();

        if (!$isEnrolled && !$this->canManageCourse($course)) {
            return redirect('/courses')->with('error', 'You must enroll in this course first');
        }

        return view('courses.show', [
            'course' => $course,
            'isEnrolled' => $isEnrolled,
            'canManageCourse' => $this->canManageCourse($course),
            'user' => $user,
        ]);
    }

    /**
     * Enroll user in a course
     */
    public function enroll(Request $request, Course $course)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($course->status !== 'published') {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Course belum tersedia untuk enrollment'
                ], 403);
            }

            return redirect()->route('courses.index')
                ->with('error', 'Course belum tersedia untuk enrollment.');
        }

        // Check if already enrolled
        $existing = $user->enrollments()
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'Already enrolled in this course'
            ], 400);
        }

        // Create enrollment
        Enrollment::create([
            'user_id' => Auth::id(),
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully enrolled in course'
            ]);
        }

        return redirect("/courses/{$course->id}")
            ->with('success', 'Successfully enrolled in ' . $course->title);
    }
}
