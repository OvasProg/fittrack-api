<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\WorkoutController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Http\Controllers\WebhookController;

Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook']);

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

    // ------------------------------------------------------------------
    // Curriculum Access (Authenticated Users - Read Only)
    // ------------------------------------------------------------------
    Route::apiResource('trainings', TrainingController::class)->only(['index', 'show']);
    Route::apiResource('exercises', ExerciseController::class)->only(['index', 'show']);
    Route::get('trainings/{training}/exercises', [TrainingController::class, 'exercises']);

    // ------------------------------------------------------------------
    // Curriculum Management (Admins Only - Write Access)
    // ------------------------------------------------------------------
    Route::middleware('admin')->group(function () {
        // Management for Trainings (Create, Update, Delete)
        Route::apiResource('trainings', TrainingController::class)->except(['index', 'show']);

        // Management for Exercises (Create, Update, Delete)
        Route::apiResource('exercises', ExerciseController::class)->except(['index', 'show']);

        // Managing the relationship between Trainings and Exercises
        Route::prefix('trainings/{training}')->controller(TrainingController::class)->group(function () {
            Route::post('/exercises', 'attachExercise');
            Route::delete('/exercises/{exercise}', 'detachExercise');
        });
    });
});

// Admin System Area
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

    Route::get('/logs', [AuditLogController::class, 'index']);
});
