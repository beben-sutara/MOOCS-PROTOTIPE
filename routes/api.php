<?php

use App\Http\Controllers\ModuleController;
use App\Http\Controllers\UserXpController;
use App\Http\Controllers\LeaderboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public Routes
Route::get('/leaderboard/xp', [LeaderboardController::class, 'topByXp']);
Route::get('/leaderboard/level', [LeaderboardController::class, 'topByLevel']);
Route::get('/leaderboard/stats', [LeaderboardController::class, 'stats']);
Route::get('/leaderboard/weekly', [LeaderboardController::class, 'weekly']);
Route::get('/leaderboard/course/{courseId}', [LeaderboardController::class, 'byCourse']);
Route::get('/leaderboard/level/{level}', [LeaderboardController::class, 'filterByLevel']);
Route::get('/users/{user}/xp', [UserXpController::class, 'getUserXp']);

// Protected Routes - Require Authentication
Route::middleware('auth:sanctum')->group(function () {
    // User endpoints
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // XP Routes
    Route::prefix('user')->group(function () {
        Route::get('/xp-summary', [UserXpController::class, 'getSummary']);
        Route::get('/xp-logs', [UserXpController::class, 'getHistory']);
        Route::get('/xp-analytics', [UserXpController::class, 'getAnalytics']);
        Route::get('/rank', [UserXpController::class, 'getRank']);
    });

    // Award XP (instructor/admin only)
    Route::post('/users/{user}/award-xp', [UserXpController::class, 'awardXp']);

    // Module Routes
    Route::prefix('courses/{course}')->group(function () {
        Route::get('/modules', [ModuleController::class, 'index']);
        Route::get('/modules/{module}', [ModuleController::class, 'show']);
        Route::post('/modules/{module}/complete', [ModuleController::class, 'complete']);
    });
});

