<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function canManageCourse(Course $course): bool
    {
        $user = Auth::user();
        return $user && ($user->role === 'admin' || $course->instructor_id === $user->id);
    }

    public function createForCourse(Course $course)
    {
        if (! $this->canManageCourse($course)) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Anda tidak memiliki izin untuk mengelola bab course ini.');
        }

        $nextOrder = $course->sections()->max('order') + 1;

        return view('sections.create', [
            'course'  => $course,
            'section' => new Section(['order' => $nextOrder]),
        ]);
    }

    public function storeForCourse(Request $request, Course $course)
    {
        if (! $this->canManageCourse($course)) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Anda tidak memiliki izin untuk mengelola bab course ini.');
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'order'       => 'required|integer|min:0',
        ]);

        $section = $course->sections()->create($validated);

        return redirect()->route('courses.show', $course)
            ->with('success', "Bab \"{$section->title}\" berhasil ditambahkan.");
    }

    public function editForCourse(Course $course, Section $section)
    {
        if ($section->course_id !== $course->id) {
            abort(404);
        }

        if (! $this->canManageCourse($course)) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Anda tidak memiliki izin untuk mengelola bab course ini.');
        }

        return view('sections.edit', compact('course', 'section'));
    }

    public function updateForCourse(Request $request, Course $course, Section $section)
    {
        if ($section->course_id !== $course->id) {
            abort(404);
        }

        if (! $this->canManageCourse($course)) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Anda tidak memiliki izin untuk mengelola bab course ini.');
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'order'       => 'required|integer|min:0',
        ]);

        $section->update($validated);

        return redirect()->route('courses.show', $course)
            ->with('success', "Bab \"{$section->title}\" berhasil diperbarui.");
    }

    public function destroyForCourse(Course $course, Section $section)
    {
        if ($section->course_id !== $course->id) {
            abort(404);
        }

        if (! $this->canManageCourse($course)) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Anda tidak memiliki izin untuk mengelola bab course ini.');
        }

        $title = $section->title;
        // Unlink modules from this section (set section_id to null)
        $section->modules()->update(['section_id' => null]);
        $section->delete();

        return redirect()->route('courses.show', $course)
            ->with('success', "Bab \"{$title}\" berhasil dihapus. Modul-modulnya dipindah ke tanpa bab.");
    }
}
