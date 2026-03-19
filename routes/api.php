<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\TrainingController;


Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    // Get the current user
    Route::get('/user', function (Request $request) {
        return $request->user()->only([
            'id',
            'name',
            'email',
            'role'
        ]);
    });

    // The Onboarding Endpoint
    Route::post('/onboarding', [OnboardingController::class, 'store']);

    // Dashboard Endpoints
    Route::prefix('dashboard')->group(function () {
        Route::get('/overview', [\App\Http\Controllers\DashboardController::class, 'overview']);
        Route::get('/calendar', [\App\Http\Controllers\DashboardController::class, 'calendar']);
        Route::get('/recent-history', [\App\Http\Controllers\DashboardController::class, 'recentHistory']);
    });

    // Workout Sessions Endpoints
    Route::middleware('throttle:workouts')->group(function () {
        Route::post('/workouts/start', [WorkoutController::class, 'start']);
        Route::post('/workouts/{session}/finish', [WorkoutController::class, 'finish']);
    });

    // Settings Endpoints
    Route::patch('/settings/biometrics', [SettingsController::class, 'updateBiometrics']);
    Route::delete('/settings/account', [SettingsController::class, 'destroyAccount']);

    // Analytics Endpoints
    Route::prefix('analytics')->group(function () {
        Route::get('/summary', [\App\Http\Controllers\AnalyticsController::class, 'summary']);
        Route::get('/charts/volume', [\App\Http\Controllers\AnalyticsController::class, 'volumeTrend']);
        Route::get('/charts/muscle-distribution', [\App\Http\Controllers\AnalyticsController::class, 'muscleDistribution']);
    });

    // Trainings Endpoint
    Route::get('/trainings', [TrainingController::class, 'index']);
    Route::get('/trainings/{training}', [TrainingController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

    // Dashboard & Announcements
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);
    Route::post('/announcements', [\App\Http\Controllers\Admin\AnnouncementController::class, 'store']);

    // User Management
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index']);
    Route::patch('/users/{id}/role', [\App\Http\Controllers\Admin\UserController::class, 'updateRole']);

    // Recycle Bin
    Route::get('/users/deleted', [\App\Http\Controllers\Admin\UserController::class, 'trashed']);
    Route::post('/users/{id}/restore', [\App\Http\Controllers\Admin\UserController::class, 'restore']);
    Route::delete('/users/{id}/force', [\App\Http\Controllers\Admin\UserController::class, 'forceDelete']);

    // Training Management
    Route::get('/trainings', [\App\Http\Controllers\Admin\TrainingController::class, 'index']);
    Route::post('/trainings', [\App\Http\Controllers\Admin\TrainingController::class, 'store']);
    Route::delete('/trainings/{id}', [\App\Http\Controllers\Admin\TrainingController::class, 'destroy']);

    // Exercise Management
    Route::get('/exercises', [\App\Http\Controllers\Admin\ExerciseController::class, 'index']);
    Route::post('/exercises', [\App\Http\Controllers\Admin\ExerciseController::class, 'store']);
    Route::delete('/exercises/{id}', [\App\Http\Controllers\Admin\ExerciseController::class, 'destroy']);

    // Audit Logs
    Route::get('/logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index']);
});
