<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckModuleAccess
{
    /**
     * Handle an incoming request to check module access.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Extract course_id and module_id from route parameters
        $courseId = $request->route('course');
        $moduleId = $request->route('module');

        // If no module_id in route, allow access
        if (!$moduleId) {
            return $next($request);
        }

        $user = $request->user();

        // If user is not authenticated, redirect to login
        if (!$user) {
            return redirect()->route('login');
        }

        // Get the module
        $module = Module::find($moduleId);
        
        if (!$module) {
            abort(404, 'Modul tidak ditemukan');
        }

        // Get course from module or route
        $course = $courseId ? Course::find($courseId) : $module->course;

        if (!$course) {
            abort(404, 'Kursus tidak ditemukan');
        }

        // Check if user is enrolled in the course
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            abort(403, 'Anda belum terdaftar dalam kursus ini');
        }

        // Check if module is locked
        if ($module->is_locked && !$this->canAccessModule($user, $module)) {
            abort(403, 'Modul ini terkunci. Selesaikan modul persyaratan terlebih dahulu');
        }

        // Mark module as viewed
        $user->moduleProgress()->updateOrCreate(
            ['module_id' => $module->id],
            ['is_viewed' => true, 'started_at' => now()]
        );

        // Store current module in request for controller access
        $request->merge(['current_module' => $module]);

        return $next($request);
    }

    /**
     * Check if user can access the module based on prerequisites.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Module  $module
     * @return bool
     */
    private function canAccessModule($user, $module): bool
    {
        // If module has no prerequisites, allow access
        if (!$module->prerequisite_module_id) {
            return true;
        }

        // Check if user has completed the prerequisite module
        $prerequisiteProgress = $user->moduleProgress()
            ->where('module_id', $module->prerequisite_module_id)
            ->where('is_completed', true)
            ->exists();

        return $prerequisiteProgress;
    }
}
