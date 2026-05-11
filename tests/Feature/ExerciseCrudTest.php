<?php

use App\Enums\UserRole;
use App\Models\Exercise;
use App\Models\User;

test('index returns paginated exercises', function () {
    Exercise::factory()->count(15)->create();

    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/exercises');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'links',
            'meta'
        ])
        ->assertJsonCount(10, 'data');
});

test('index can filter by muscle group', function () {
    Exercise::factory()->create(['name' => 'Squat', 'target_muscle' => 'Legs']);
    Exercise::factory()->create(['name' => 'Bench Press', 'target_muscle' => 'Chest']);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/exercises?target_muscle=Legs');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Squat');
});

test('index can filter by name', function () {
    Exercise::factory()->create(['name' => 'Pushup']);
    Exercise::factory()->create(['name' => 'Pullup']);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/exercises?name=Push');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Pushup');
});

test('admin can create an exercise', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    $response = $this->actingAs($admin)->postJson('/api/exercises', [
        'name' => 'Deadlift',
        'target_muscle' => 'Back',
        'base_multiplier' => 1.2,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('exercise.name', 'Deadlift');

    $this->assertDatabaseHas('exercises', ['name' => 'Deadlift']);
});

test('non-admin cannot create an exercise', function () {
    $user = User::factory()->create(['role' => UserRole::FREE]);

    $response = $this->actingAs($user)->postJson('/api/exercises', [
        'name' => 'Deadlift',
        'target_muscle' => 'Back',
    ]);

    $response->assertStatus(403);
});

test('admin can update an exercise', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $exercise = Exercise::factory()->create(['name' => 'Old Exercise']);

    $response = $this->actingAs($admin)->patchJson("/api/exercises/{$exercise->id}", [
        'name' => 'Updated Exercise',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('exercise.name', 'Updated Exercise');

    $this->assertDatabaseHas('exercises', ['id' => $exercise->id, 'name' => 'Updated Exercise']);
});

test('admin can delete an exercise', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $exercise = Exercise::factory()->create();

    $response = $this->actingAs($admin)->deleteJson("/api/exercises/{$exercise->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('exercises', ['id' => $exercise->id]);
});

test('show returns exercise details', function () {
    $user = User::factory()->create();
    $exercise = Exercise::factory()->create();

    $response = $this->actingAs($user)->getJson("/api/exercises/{$exercise->id}");

    $response->assertStatus(200)
        ->assertJsonPath('exercise.name', $exercise->name);
});

test('exercise endpoints return 404 for non-existent records', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    $this->actingAs($admin)->getJson('/api/exercises/99999')->assertStatus(404);
    $this->actingAs($admin)->patchJson('/api/exercises/99999', ['name' => 'New Name'])->assertStatus(404);
    $this->actingAs($admin)->deleteJson('/api/exercises/99999')->assertStatus(404);
});

test('exercise store validates required fields', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    $response = $this->actingAs($admin)->postJson('/api/exercises', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'target_muscle']);
});

test('guest cannot access exercise endpoints', function () {
    $exercise = Exercise::factory()->create();

    $this->getJson('/api/exercises')->assertStatus(401);
    $this->getJson("/api/exercises/{$exercise->id}")->assertStatus(401);
    $this->postJson('/api/exercises')->assertStatus(401);
    $this->patchJson("/api/exercises/{$exercise->id}")->assertStatus(401);
    $this->deleteJson("/api/exercises/{$exercise->id}")->assertStatus(401);
});

test('non-admin cannot update or delete exercise', function () {
    $user = User::factory()->create(['role' => UserRole::FREE]);
    $exercise = Exercise::factory()->create();

    $this->actingAs($user)->patchJson("/api/exercises/{$exercise->id}", ['name' => 'New Name'])->assertStatus(403);
    $this->actingAs($user)->deleteJson("/api/exercises/{$exercise->id}")->assertStatus(403);
});

test('exercise update validates data', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $exercise = Exercise::factory()->create();

    $response = $this->actingAs($admin)->patchJson("/api/exercises/{$exercise->id}", [
        'name' => '', // Empty name should fail validation if 'sometimes' still requires content
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['name']);
});
