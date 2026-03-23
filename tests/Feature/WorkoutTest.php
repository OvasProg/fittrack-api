<?php

use App\Models\Exercise;
use App\Models\Training;
use App\Models\User;
use App\Models\WorkoutSession;
use Illuminate\Foundation\Testing\RefreshDatabase;

test('a user can successfully start a workout session', function () {
    /** @var \Tests\TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $training = Training::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/workouts/start', [
        'training_id' => $training->id,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['session_id', 'started_at']);

    $this->assertDatabaseHas('workout_sessions', [
        'user_id' => $user->id,
        'training_id' => $training->id,
        'completed_at' => null,
    ]);
});

test('a user cannot finish a workout session owned by another user', function () {
    /** @var \Tests\TestCase $this */
    /** @var \App\Models\User $user1 */
    $user1 = User::factory()->create();

    /** @var \App\Models\User $user2 */
    $user2 = User::factory()->create();
    $training = Training::factory()->create();
    $exercise = Exercise::factory()->create();

    $session = WorkoutSession::create([
        'user_id' => $user1->id,
        'training_id' => $training->id,
        'started_at' => now(),
    ]);

    $response = $this->actingAs($user2)->postJson("/api/workouts/{$session->id}/finish", [
        'sets' => [
            [
                'exercise_id' => $exercise->id,
                'set_number' => 1,
                'weight_used' => 60,
                'reps_completed' => 10,
            ]
        ]
    ]);

    $response->assertStatus(403);
});

test('a user can successfully finish their own workout session', function () {
    $user = User::factory()->create();
    $training = Training::factory()->create();
    $exercise = Exercise::factory()->create();

    $session = WorkoutSession::create([
        'user_id' => $user->id,
        'training_id' => $training->id,
        'started_at' => now(),
    ]);

    /** @var \Tests\TestCase $this */
    /** @var User $user */
    $response = $this->actingAs($user)->postJson("/api/workouts/{$session->id}/finish", [
        'sets' => [
            [
                'exercise_id' => $exercise->id,
                'set_number' => 1,
                'weight_used' => 80,
                'reps_completed' => 12,
            ]
        ]
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['message', 'duration_minutes']);

    $this->assertDatabaseHas('workout_sessions', [
        'id' => $session->id,
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('workout_sets', [
        'workout_session_id' => $session->id,
        'exercise_id' => $exercise->id,
        'weight_used' => 80,
    ]);
});
