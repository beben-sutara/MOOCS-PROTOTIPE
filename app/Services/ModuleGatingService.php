<?php

namespace App\Services;

use App\Models\Module;
use App\Models\User;
use App\Models\ModuleProgress;

class ModuleGatingService
{
    /**
     * Check if a user can access a module.
     *
     * @param  User  $user
     * @param  Module  $module
     * @return array
     */
    public function checkModuleAccess(User $user, Module $module): array
    {
        // Check enrollment
        $enrollment = $user->enrollments()
            ->where('course_id', $module->course_id)
            ->first();

        if (!$enrollment) {
            return [
                'can_access' => false,
                'reason' => 'not_enrolled',
                'message' => 'Anda belum terdaftar dalam kursus ini'
            ];
        }

        // Check if module is locked
        if ($module->is_locked && !$this->hasCompletedPrerequisite($user, $module)) {
            return [
                'can_access' => false,
                'reason' => 'prerequisite_not_met',
                'message' => 'Modul ini terkunci. Selesaikan modul persyaratan terlebih dahulu',
                'prerequisite' => $module->prerequisite
            ];
        }

        return [
            'can_access' => true,
            'reason' => 'access_granted',
            'message' => 'Anda dapat mengakses modul ini'
        ];
    }

    /**
     * Check if user has completed the prerequisite module.
     *
     * @param  User  $user
     * @param  Module  $module
     * @return bool
     */
    public function hasCompletedPrerequisite(User $user, Module $module): bool
    {
        // If no prerequisite, return true
        if (!$module->prerequisite_module_id) {
            return true;
        }

        return $user->moduleProgress()
            ->where('module_id', $module->prerequisite_module_id)
            ->where('is_completed', true)
            ->exists();
    }

    /**
     * Get all modules available to a user in a course.
     *
     * @param  User  $user
     * @param  int  $courseId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAccessibleModules(User $user, int $courseId)
    {
        return Module::where('course_id', $courseId)
            ->orderBy('order')
            ->get()
            ->map(function ($module) use ($user) {
                $access = $this->checkModuleAccess($user, $module);
                $module->can_access = $access['can_access'];
                $module->access_reason = $access['reason'];
                
                // Get user's progress for this module
                $module->progress = $user->moduleProgress()
                    ->where('module_id', $module->id)
                    ->first();

                return $module;
            });
    }

    /**
     * Mark module as completed for a user.
     *
     * @param  User  $user
     * @param  Module  $module
     * @return ModuleProgress
     */
    public function completeModule(User $user, Module $module): ModuleProgress
    {
        return $user->moduleProgress()->updateOrCreate(
            ['module_id' => $module->id],
            [
                'is_completed' => true,
                'completed_at' => now()
            ]
        );
    }

    /**
     * Get progress statistics for a user in a course.
     *
     * @param  User  $user
     * @param  int  $courseId
     * @return array
     */
    public function getCourseProgress(User $user, int $courseId): array
    {
        $modules = Module::where('course_id', $courseId)->pluck('id');
        
        $completedCount = $user->moduleProgress()
            ->whereIn('module_id', $modules)
            ->where('is_completed', true)
            ->count();

        $totalCount = $modules->count();
        $percentage = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0;

        return [
            'completed' => $completedCount,
            'total' => $totalCount,
            'percentage' => round($percentage, 2)
        ];
    }
}
