<?php

use App\Enums\ExperienceLevel;
use App\Enums\UserRole;
use App\Models\Exercise;
use App\Models\Training;
use App\Models\User;

test('index returns paginated trainings', function () {
    Training::factory()->count(15)->create();

    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/trainings');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'links',
            'meta'
        ])
        ->assertJsonCount(10, 'data');
});

test('index can filter by difficulty', function () {
    Training::factory()->create(['difficulty_level' => ExperienceLevel::BEGINNER]);
    Training::factory()->create(['difficulty_level' => ExperienceLevel::ADVANCED]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/trainings?difficulty=Beginner');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.difficulty_level', 'Beginner');
});

test('index can filter by title', function () {
    Training::factory()->create(['name' => 'Full Body Workout']);
    Training::factory()->create(['name' => 'Upper Body']);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/trainings?title=Full');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Full Body Workout');
});

test('admin can create a training', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $exercises = Exercise::factory()->count(3)->create();

    $response = $this->actingAs($admin)->postJson('/api/trainings', [
        'name' => 'New Training',
        'difficulty_level' => 'Intermediate',
        'description' => 'Test description',
        'exercise_ids' => $exercises->pluck('id')->toArray(),
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('training.name', 'New Training');

    $this->assertDatabaseHas('trainings', ['name' => 'New Training']);
    $this->assertDatabaseCount('training_exercises', 3);
});

test('non-admin cannot create a training', function () {
    $user = User::factory()->create(['role' => UserRole::FREE]);
    $exercises = Exercise::factory()->count(3)->create();

    $response = $this->actingAs($user)->postJson('/api/trainings', [
        'name' => 'New Training',
        'difficulty_level' => 'Intermediate',
        'exercise_ids' => $exercises->pluck('id')->toArray(),
    ]);

    $response->assertStatus(403);
});

test('admin can update a training', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $training = Training::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($admin)->patchJson("/api/trainings/{$training->id}", [
        'name' => 'Updated Name',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('training.name', 'Updated Name');

    $this->assertDatabaseHas('trainings', ['id' => $training->id, 'name' => 'Updated Name']);
});

test('admin can delete a training', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $training = Training::factory()->create();

    $response = $this->actingAs($admin)->deleteJson("/api/trainings/{$training->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('trainings', ['id' => $training->id]);
});

test('show returns training with exercises', function () {
    $user = User::factory()->create();
    $training = Training::factory()->create();
    $exercises = Exercise::factory()->count(2)->create();
    $training->exercises()->attach($exercises->pluck('id'));

    $response = $this->actingAs($user)->getJson("/api/trainings/{$training->id}");

    $response->assertStatus(200)
        ->assertJsonStructure(['training' => ['exercises']]);
});

test('training endpoints return 404 for non-existent records', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    $this->actingAs($admin)->getJson('/api/trainings/99999')->assertStatus(404);
    $this->actingAs($admin)->patchJson('/api/trainings/99999', ['name' => 'New Name'])->assertStatus(404);
    $this->actingAs($admin)->deleteJson('/api/trainings/99999')->assertStatus(404);
});

test('training store validates required fields', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    $response = $this->actingAs($admin)->postJson('/api/trainings', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'difficulty_level', 'exercise_ids']);
});

test('training creation fails with invalid difficulty level', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $exercises = Exercise::factory()->count(1)->create();

    $response = $this->actingAs($admin)->postJson('/api/trainings', [
        'name' => 'Invalid Training',
        'difficulty_level' => 'super-hard',
        'exercise_ids' => $exercises->pluck('id')->toArray(),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['difficulty_level']);
});

test('attaching non-existent exercise to training returns error', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $training = Training::factory()->create();

    $response = $this->actingAs($admin)->postJson("/api/trainings/{$training->id}/exercises", [
        'exercise_id' => 99999,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['exercise_id']);
});

test('can list exercises in a training', function () {
    $user = User::factory()->create();
    $training = Training::factory()->create();
    $exercises = Exercise::factory()->count(2)->create();
    $training->exercises()->attach($exercises->pluck('id'));

    $response = $this->actingAs($user)->getJson("/api/trainings/{$training->id}/exercises");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'exercises');
});

test('admin can attach an exercise to a training', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $training = Training::factory()->create();
    $exercise = Exercise::factory()->create();

    $response = $this->actingAs($admin)->postJson("/api/trainings/{$training->id}/exercises", [
        'exercise_id' => $exercise->id,
        'default_sets' => 3,
        'default_reps' => 10,
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('training_exercises', [
        'training_id' => $training->id,
        'exercise_id' => $exercise->id,
        'default_sets' => 3,
        'default_reps' => 10,
    ]);
});

test('admin can detach an exercise from a training', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $training = Training::factory()->create();
    $exercise = Exercise::factory()->create();
    $training->exercises()->attach($exercise->id);

    $response = $this->actingAs($admin)->deleteJson("/api/trainings/{$training->id}/exercises/{$exercise->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('training_exercises', [
        'training_id' => $training->id,
        'exercise_id' => $exercise->id,
    ]);
});

test('non-admin cannot attach or detach exercises', function () {
    $user = User::factory()->create(['role' => UserRole::FREE]);
    $training = Training::factory()->create();
    $exercise = Exercise::factory()->create();

    $this->actingAs($user)->postJson("/api/trainings/{$training->id}/exercises", [
        'exercise_id' => $exercise->id,
    ])->assertStatus(403);

    $training->exercises()->attach($exercise->id);

    $this->actingAs($user)->deleteJson("/api/trainings/{$training->id}/exercises/{$exercise->id}")
        ->assertStatus(403);
});

test('guest cannot access training endpoints', function () {
    $training = Training::factory()->create();

    $this->getJson('/api/trainings')->assertStatus(401);
    $this->getJson("/api/trainings/{$training->id}")->assertStatus(401);
    $this->postJson('/api/trainings')->assertStatus(401);
    $this->patchJson("/api/trainings/{$training->id}")->assertStatus(401);
    $this->deleteJson("/api/trainings/{$training->id}")->assertStatus(401);
});

test('non-admin cannot update or delete training', function () {
    $user = User::factory()->create(['role' => UserRole::FREE]);
    $training = Training::factory()->create();

    $this->actingAs($user)->patchJson("/api/trainings/{$training->id}", ['name' => 'New Name'])->assertStatus(403);
    $this->actingAs($user)->deleteJson("/api/trainings/{$training->id}")->assertStatus(403);
});

test('training update validates data', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $training = Training::factory()->create();

    $response = $this->actingAs($admin)->patchJson("/api/trainings/{$training->id}", [
        'difficulty_level' => 'invalid-level',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['difficulty_level']);
});

test('relational operations return 404 for non-existent training', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $exercise = Exercise::factory()->create();

    $this->actingAs($admin)->postJson('/api/trainings/99999/exercises', ['exercise_id' => $exercise->id])->assertStatus(404);
    $this->actingAs($admin)->getJson('/api/trainings/99999/exercises')->assertStatus(404);
});

test('detaching non-existent exercise returns 404', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $training = Training::factory()->create();

    // The current implementation uses findOrFail($id) for training, but detach doesn't fail if exercise doesn't exist.
    // However, the route has {exercise} so it might depend on implicit binding if we used it, but we use $exerciseId directly.
    // Let's check the controller logic. It finds training but doesn't find exercise.
    // Wait, the controller just does $training->exercises()->detach($exerciseId).
    // If we want it to 404 if exercise doesn't exist, we'd need to find the exercise first.
    // But the prompt asks to assert 404 status.
    
    $this->actingAs($admin)->deleteJson("/api/trainings/{$training->id}/exercises/99999")->assertStatus(404);
});
