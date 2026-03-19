<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// App Controllers
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\TrainingController;

// Admin Controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TrainingController as AdminTrainingController;
use App\Http\Controllers\Admin\ExerciseController;
use App\Http\Controllers\Admin\AuditLogController;

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
    Route::prefix('dashboard')->controller(DashboardController::class)->group(function () {
        Route::get('/overview', 'overview');
        Route::get('/calendar', 'calendar');
        Route::get('/recent-history', 'recentHistory');
    });

    // Workout Sessions Endpoints
    Route::middleware('throttle:workouts')->prefix('workouts')->controller(WorkoutController::class)->group(function () {
        Route::post('/start', 'start');
        Route::post('/{session}/finish', 'finish');
    });

    // Settings Endpoints
    Route::prefix('settings')->controller(SettingsController::class)->group(function () {
        Route::patch('/biometrics', 'updateBiometrics');
        Route::delete('/account', 'destroyAccount');
    });

    // Analytics Endpoints
    Route::prefix('analytics')->controller(AnalyticsController::class)->group(function () {
        Route::get('/summary', 'summary');
        Route::get('/charts/volume', 'volumeTrend');
        Route::get('/charts/muscle-distribution', 'muscleDistribution');
    });

    // Trainings Endpoint
    Route::prefix('trainings')->controller(TrainingController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{training}', 'show');
    });
});

// Admin Endpoints
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);
    //Announcements
    Route::post('/announcements', [AnnouncementController::class, 'store']);

    // User Management
    Route::prefix('users')->controller(UserController::class)->group(function () {
        Route::get('/', 'index');
        Route::patch('/{id}/role', 'updateRole');
        Route::get('/deleted', 'trashed');
        Route::post('/{id}/restore', 'restore');
        Route::delete('/{id}/force', 'forceDelete');
    });

    // Training Management
    Route::prefix('trainings')->controller(AdminTrainingController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::delete('/{id}', 'destroy');
    });

    // Exercise Management
    Route::prefix('exercises')->controller(ExerciseController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::delete('/{id}', 'destroy');
    });

    // Audit Logs
    Route::get('/logs', [AuditLogController::class, 'index']);
});
