<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\InstructorApplication;
use App\Models\Module;
use App\Models\ModuleProgress;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalStudents = User::where('role', 'user')->count();
        $totalInstructors = User::where('role', 'instructor')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        $pendingInstructorApplications = InstructorApplication::where('status', 'pending')->count();

        $totalCourses = Course::count();
        $publishedCourses = Course::where('status', 'published')->count();
        $draftCourses = Course::where('status', 'draft')->count();
        $pendingApprovalCourses = Course::where('status', 'pending_approval')->count();
        $archivedCourses = Course::where('status', 'archived')->count();

        $totalModules = Module::count();
        $completedModules = ModuleProgress::where('is_completed', true)->count();

        $totalEnrollments = Enrollment::count();
        $activeEnrollments = Enrollment::where('status', 'active')->count();
        $completedEnrollments = Enrollment::where('status', 'completed')->count();
        $completionRate = $totalEnrollments > 0
            ? (int) round(($completedEnrollments / $totalEnrollments) * 100)
            : 0;

        $recentUsers = User::latest()->take(5)->get();
        $recentCourses = Course::with('instructor')
            ->latest()
            ->take(5)
            ->get();
        $popularCourses = Course::with('instructor')
            ->withCount(['enrollments', 'modules'])
            ->orderByDesc('enrollments_count')
            ->orderByDesc('modules_count')
            ->take(5)
            ->get();
        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalStudents' => $totalStudents,
            'totalInstructors' => $totalInstructors,
            'totalAdmins' => $totalAdmins,
            'pendingInstructorApplications' => $pendingInstructorApplications,
            'totalCourses' => $totalCourses,
            'publishedCourses' => $publishedCourses,
            'draftCourses' => $draftCourses,
            'pendingApprovalCourses' => $pendingApprovalCourses,
            'archivedCourses' => $archivedCourses,
            'totalModules' => $totalModules,
            'completedModules' => $completedModules,
            'totalEnrollments' => $totalEnrollments,
            'activeEnrollments' => $activeEnrollments,
            'completedEnrollments' => $completedEnrollments,
            'completionRate' => $completionRate,
            'recentUsers' => $recentUsers,
            'recentCourses' => $recentCourses,
            'popularCourses' => $popularCourses,
        ]);
    }
}
