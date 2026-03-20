<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinishWorkoutRequest;
use App\Http\Requests\StartWorkoutRequest;
use App\Models\ScheduledWorkout;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * The engine for recording live workout activity.
 *
 * This controller handles the transition from a "planned" workout
 * to a "live" session. It manages the real-time start/finish
 * logic and ensures all individual sets are saved safely.
 */
class WorkoutController extends Controller
{
    public function start(StartWorkoutRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = $request->user();

        // We create a new session record to start the "clock"
        // for the user's training duration.
        $session = WorkoutSession::create([
            'user_id' => $user->id,
            'training_id' => $validated['training_id'],
            'started_at' => Carbon::now(),
        ]);

        // If the user started this from their calendar, we mark
        // the scheduled item as 'in_progress' so they can't
        // accidentally start it twice.
        if (! empty($validated['scheduled_workout_id'])) {
            ScheduledWorkout::where('id', $validated['scheduled_workout_id'])
                ->where('user_id', $user->id)
                ->update(['status' => 'in_progress']);
        }

        return response()->json([
            'message' => 'Workout started successfully.',
            'session_id' => $session->id,
            'started_at' => $session->started_at,
        ], 201);
    }

    public function finish(FinishWorkoutRequest $request, WorkoutSession $session): JsonResponse
    {
        // Security check: Ensure the user finishing the workout
        // is the same one who started it.
        if ($session->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        // Integrity check: Prevent users from "double-finishing"
        // a session that is already closed.
        if ($session->completed_at !== null) {
            return response()->json(['message' => 'This workout is already completed.'], 400);
        }

        $validated = $request->validated();

        // We use a Database Transaction to ensure that either
        // everything is saved (session and all sets) or nothing is.
        // This prevents "ghost sessions" with no data.
        DB::beginTransaction();

        try {
            foreach ($validated['sets'] as $set) {
                $session->workoutSets()->create([
                    'exercise_id' => $set['exercise_id'],
                    'set_number' => $set['set_number'],
                    'weight_used' => $set['weight_used'],
                    'reps_completed' => $set['reps_completed'],
                ]);
            }

            $session->update([
                'completed_at' => Carbon::now(),
            ]);

            if (! empty($validated['scheduled_workout_id'])) {
                ScheduledWorkout::where('id', $validated['scheduled_workout_id'])
                    ->update(['status' => 'completed']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Workout logged successfully!',
                'duration_minutes' => $session->started_at
                    ->diffInMinutes($session->completed_at),
            ], 200);
        } catch (\Exception $e) {
            // If anything goes wrong (database error, etc.), we
            // undo everything to keep the data clean.
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to save workout data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
