<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCourseController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));

        $coursesQuery = Course::query()
            ->with('instructor')
            ->withCount(['modules', 'enrollments'])
            ->latest();

        if ($search !== '') {
            $coursesQuery->where(function ($query) use ($search) {
                $query
                    ->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('instructor', function ($instructorQuery) use ($search) {
                        $instructorQuery
                            ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        if (in_array($status, ['draft', 'pending_approval', 'published', 'archived'], true)) {
            $coursesQuery->where('status', $status);
        }

        $courses = $coursesQuery->limit(50)->get();

        return view('admin.courses.index', [
            'courses' => $courses,
            'search' => $search,
            'status' => $status,
            'totalCourses' => Course::count(),
            'draftCount' => Course::where('status', 'draft')->count(),
            'pendingApprovalCount' => Course::where('status', 'pending_approval')->count(),
            'publishedCount' => Course::where('status', 'published')->count(),
            'archivedCount' => Course::where('status', 'archived')->count(),
        ]);
    }

    public function updateStatus(Request $request, Course $course)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['draft', 'pending_approval', 'published', 'archived'])],
        ]);

        $course->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Status course berhasil diperbarui.');
    }
}
