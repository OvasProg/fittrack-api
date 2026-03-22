<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ExerciseController;
use App\Http\Controllers\Admin\TrainingController as AdminTrainingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/stripe/webhook', [\Laravel\Cashier\Http\Controllers\WebhookController::class, 'handleWebhook']);

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user()->only(['id', 'name', 'email', 'role']);
    });

    Route::post('/onboarding', [OnboardingController::class, 'store']);

    // Dashboard
    Route::prefix('dashboard')->controller(DashboardController::class)->group(function () {
        Route::get('/overview', 'overview');
        Route::get('/calendar', 'calendar');
        Route::get('/recent-history', 'recentHistory');
    });

    // Workouts
    // We apply a stricter throttle here because starting sessions
    // involves heavy database writes and adaptive logic calculations.
    Route::middleware('throttle:workouts')->prefix('workouts')->controller(WorkoutController::class)->group(function () {
        Route::post('/start', 'start');
        Route::post('/{session}/finish', 'finish');
    });

    // Settings
    Route::prefix('settings')->controller(SettingsController::class)->group(function () {
        Route::patch('/biometrics', 'updateBiometrics');
        Route::delete('/account', 'destroyAccount');
    });

    Route::post('/subscribe', [SubscriptionController::class, 'createCheckoutSession']);

    // Analytics
    Route::prefix('analytics')->controller(AnalyticsController::class)->group(function () {
        Route::get('/summary', 'summary');
        Route::get('/charts/volume', 'volumeTrend');
        Route::get('/charts/muscle-distribution', 'muscleDistribution');
    });

    // Trainings
    Route::prefix('trainings')->controller(TrainingController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{training}', 'show');
    });
});

// Admin Area
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

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

    Route::get('/logs', [AuditLogController::class, 'index']);
});
