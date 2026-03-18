<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ModulePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view the module.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Module  $module
     * @return bool
     */
    public function view(User $user, Module $module): bool
    {
        if ($user->role === 'admin' || $module->course?->instructor_id === $user->id) {
            return true;
        }

        // Check if user is enrolled in the course
        $enrollment = $user->enrollments()
            ->where('course_id', $module->course_id)
            ->whereIn('status', ['active', 'completed'])
            ->exists();

        if (!$enrollment) {
            return false;
        }

        // Check if module is locked
        if (!$module->is_locked) {
            return true;
        }

        // Check if user has completed the prerequisite
        if ($module->prerequisite_module_id) {
            return $user->moduleProgress()
                ->where('module_id', $module->prerequisite_module_id)
                ->where('is_completed', true)
                ->exists();
        }

        return true;
    }

    /**
     * Determine if the user can complete the module.
     * Only active enrollments can complete modules (course still in progress).
     */
    public function complete(User $user, Module $module): bool
    {
        if ($user->role === 'admin' || $module->course?->instructor_id === $user->id) {
            return true;
        }

        return $user->enrollments()
            ->where('course_id', $module->course_id)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Determine if the user can see the module is locked.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Module  $module
     * @return bool
     */
    public function viewLocked(User $user, Module $module): bool
    {
        return !$this->view($user, $module) && $module->is_locked;
    }
}
