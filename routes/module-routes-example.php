<?php

use App\Http\Controllers\ModuleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes for MOOC Module Gating
|--------------------------------------------------------------------------
|
| These routes demonstrate how to use the CheckModuleAccess middleware
| for module access gating with prerequisite validation.
|
*/

Route::middleware(['auth'])->group(function () {
    // View all modules in a course
    Route::get('/courses/{course:id}/modules', [ModuleController::class, 'index'])
        ->name('courses.modules.index');

    // View a specific module (gating middleware will check access)
    Route::get('/courses/{course:id}/modules/{module:id}', [ModuleController::class, 'show'])
        ->middleware('check.module.access')
        ->name('courses.modules.show');

    // Mark module as completed
    Route::post('/courses/{course:id}/modules/{module:id}/complete', [ModuleController::class, 'complete'])
        ->middleware('check.module.access')
        ->name('courses.modules.complete');
});

/*
|--------------------------------------------------------------------------
| Example Implementation Details
|--------------------------------------------------------------------------
|
| Middleware Behavior:
|
| 1. 'check.module.access' checks:
|    - User is authenticated
|    - User is enrolled in the course
|    - Module prerequisites are completed (if module is locked)
|    - Marks module as viewed
|
| 2. If any check fails, it returns appropriate error:
|    - 401: Not authenticated (redirects to login)
|    - 403: Not enrolled or prerequisite not met
|    - 404: Module or course not found
|
| 3. The middleware stores the validated module in the request
|    for use in your controller methods.
|
|--------------------------------------------------------------------------
| Authorization with Gates & Policies
|--------------------------------------------------------------------------
|
| In controller methods, use these authorizations:
|
|   $this->authorize('view', $module);      // Check if user can view
|   $this->authorize('complete', $module);  // Check if user can complete
|   $this->authorize('viewLocked', $module); // Check if module is locked
|
|--------------------------------------------------------------------------
| Service Usage Example
|--------------------------------------------------------------------------
|
| In your controller, inject the ModuleGatingService:
|
|   use App\Services\ModuleGatingService;
|
|   public function someMethod(ModuleGatingService $gatingService)
|   {
|       $user = auth()->user();
|       $module = Module::find($id);
|
|       // Check access
|       $access = $gatingService->checkModuleAccess($user, $module);
|       if (!$access['can_access']) {
|           return back()->withError($access['message']);
|       }
|
|       // Get accessible modules
|       $modules = $gatingService->getAccessibleModules($user, $courseId);
|
|       // Mark as completed
|       $gatingService->completeModule($user, $module);
|
|       // Get progress
|       $progress = $gatingService->getCourseProgress($user, $courseId);
|   }
|
|--------------------------------------------------------------------------
*/
