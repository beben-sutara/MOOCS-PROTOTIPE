<?php

/*
|--------------------------------------------------------------------------
| SETUP INSTRUCTIONS FOR GATING LOGIC MIDDLEWARE
|--------------------------------------------------------------------------
|
| Copy and paste the code snippets below into their respective files
| to complete the setup of the CheckModuleAccess middleware.
|
*/

// FILE 1: app/Http/Kernel.php
// ============================================================================
// Add this line to the $routeMiddleware array:

/*
protected $routeMiddleware = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
    'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
    'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
    'can' => \Illuminate\Auth\Middleware\Authorize::class,
    'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
    'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
    'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
    'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    
    // ADD THIS LINE:
    'check.module.access' => \App\Http\Middleware\CheckModuleAccess::class,
];
*/

// FILE 2: app/Providers/AuthServiceProvider.php
// ============================================================================
// Update the boot method:

/*
public function boot()
{
    $this->registerPolicies();
    
    // ADD THIS:
    Gate::policy(\App\Models\Module::class, \App\Policies\ModulePolicy::class);
}
*/

// Or add to the $policies property:
/*
protected $policies = [
    \App\Models\Module::class => \App\Policies\ModulePolicy::class,
];
*/

// FILE 3: routes/web.php or routes/api.php
// ============================================================================
// Add these module routes:

/*
Route::middleware(['auth'])->group(function () {
    // Courses & Modules
    Route::prefix('/courses/{course:id}')->group(function () {
        // List all modules in a course
        Route::get('/modules', [\App\Http\Controllers\ModuleController::class, 'index'])
            ->name('courses.modules.index');
        
        // View a specific module (protected by gating middleware)
        Route::get('/modules/{module:id}', [\App\Http\Controllers\ModuleController::class, 'show'])
            ->middleware('check.module.access')
            ->name('courses.modules.show');
        
        // Mark module as completed (protected by gating middleware)
        Route::post('/modules/{module:id}/complete', [\App\Http\Controllers\ModuleController::class, 'complete'])
            ->middleware('check.module.access')
            ->name('courses.modules.complete');
    });
});
*/

?>
