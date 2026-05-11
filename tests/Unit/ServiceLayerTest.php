<?php

use App\Models\Exercise;
use App\Models\Training;
use App\Models\User;
use App\Services\ExerciseService;
use App\Services\TrainingService;
use App\Enums\UserRole;
use App\Enums\ExperienceLevel;

test('training service creates training and audit log', function () {
    $trainingService = app(TrainingService::class);
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $exercises = Exercise::factory()->count(2)->create();

    $data = [
        'name' => 'Service Training',
        'difficulty_level' => ExperienceLevel::INTERMEDIATE,
        'description' => 'Service description',
        'exercise_ids' => $exercises->pluck('id')->toArray(),
    ];

    $training = $trainingService->createTraining($admin, $data);

    expect($training)->toBeInstanceOf(Training::class);
    $this->assertDatabaseHas('trainings', ['name' => 'Service Training']);
    $this->assertDatabaseHas('audit_logs', [
        'admin_id' => $admin->id,
        'action' => 'created_training',
    ]);
    expect($training->exercises)->toHaveCount(2);
});

test('training service attaches exercise correctly', function () {
    $trainingService = app(TrainingService::class);
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $training = Training::factory()->create();
    $exercise = Exercise::factory()->create();

    $trainingService->attachExercise($admin, $training->id, $exercise->id, [
        'default_sets' => 4,
        'default_reps' => 12
    ]);

    $this->assertDatabaseHas('training_exercises', [
        'training_id' => $training->id,
        'exercise_id' => $exercise->id,
        'default_sets' => 4,
        'default_reps' => 12
    ]);

    // Duplicate handling test (syncWithoutDetaching should handle this)
    $trainingService->attachExercise($admin, $training->id, $exercise->id, [
        'default_sets' => 5
    ]);

    $this->assertDatabaseCount('training_exercises', 1);
    $this->assertDatabaseHas('training_exercises', [
        'default_sets' => 5
    ]);
});

test('exercise service updates exercise correctly', function () {
    $exerciseService = app(ExerciseService::class);
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $exercise = Exercise::factory()->create(['name' => 'Old Name']);

    $updatedData = ['name' => 'New Service Name'];
    $exerciseService->updateExercise($admin, $exercise->id, $updatedData);

    $this->assertDatabaseHas('exercises', [
        'id' => $exercise->id,
        'name' => 'New Service Name'
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'admin_id' => $admin->id,
        'action' => 'updated_exercise'
    ]);
});
