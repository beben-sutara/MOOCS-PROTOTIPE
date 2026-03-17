<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminCourseController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminInstructorApplicationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstructorApplicationController;
use App\Http\Controllers\LeaderboardWebController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/leaderboard', [LeaderboardWebController::class, 'index'])->name('leaderboard');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/courses', [AdminCourseController::class, 'index'])->name('courses.index');
        Route::put('/courses/{course}/status', [AdminCourseController::class, 'updateStatus'])->whereNumber('course')->name('courses.status.update');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->whereNumber('user')->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->whereNumber('user')->name('users.update');
        Route::get('/instructor-applications', [AdminInstructorApplicationController::class, 'index'])->name('instructor-applications.index');
        Route::put('/instructor-applications/{instructorApplication}', [AdminInstructorApplicationController::class, 'update'])->whereNumber('instructorApplication')->name('instructor-applications.update');
    });

    Route::get('/instructor/apply', [InstructorApplicationController::class, 'create'])->name('instructor.apply');
    Route::post('/instructor/apply', [InstructorApplicationController::class, 'store'])->name('instructor.apply.store');
    
    // Courses
    Route::get('/courses', [CoursesController::class, 'index'])->name('courses.index');
    Route::get('/courses/manage', [CoursesController::class, 'manage'])->name('courses.manage');
    Route::get('/courses/create', [CoursesController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CoursesController::class, 'store'])->name('courses.store');
    Route::get('/courses/{course}/edit', [CoursesController::class, 'edit'])->whereNumber('course')->name('courses.edit');
    Route::put('/courses/{course}', [CoursesController::class, 'update'])->whereNumber('course')->name('courses.update');
    Route::delete('/courses/{course}', [CoursesController::class, 'destroy'])->whereNumber('course')->name('courses.destroy');
    Route::get('/courses/{course}', [CoursesController::class, 'show'])->whereNumber('course')->name('courses.show');
    Route::post('/courses/{course}/enroll', [CoursesController::class, 'enroll'])->whereNumber('course')->name('courses.enroll');
    Route::post('/api/courses/{course}/enroll', [CoursesController::class, 'enroll'])->whereNumber('course')->name('courses.enroll.api');
    
    // Modules
    Route::get('/courses/{course}/modules/create', [ModuleController::class, 'createForCourse'])->whereNumber('course')->name('modules.create');
    Route::post('/courses/{course}/modules', [ModuleController::class, 'storeForCourse'])->whereNumber('course')->name('modules.store');
    Route::get('/courses/{course}/modules/{module}/edit', [ModuleController::class, 'editForCourse'])->whereNumber('course')->whereNumber('module')->name('modules.edit');
    Route::put('/courses/{course}/modules/{module}', [ModuleController::class, 'updateForCourse'])->whereNumber('course')->whereNumber('module')->name('modules.update');
    Route::delete('/courses/{course}/modules/{module}', [ModuleController::class, 'destroyForCourse'])->whereNumber('course')->whereNumber('module')->name('modules.destroy');
    Route::get('/courses/{course}/modules/{module}', [ModuleController::class, 'show'])->whereNumber('course')->whereNumber('module')->name('courses.modules.show');
    Route::post('/courses/{course}/modules/{module}/complete', [ModuleController::class, 'complete'])->whereNumber('course')->whereNumber('module')->name('modules.complete');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
});
