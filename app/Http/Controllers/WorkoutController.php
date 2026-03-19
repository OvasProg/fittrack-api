<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkoutSession;
use App\Models\ScheduledWorkout;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkoutController extends Controller
{
    public function start(Request $request)
    {
        $validated = $request->validate([
            'training_id' => 'required|exists:trainings,id',
            'scheduled_workout_id' => 'nullable|exists:scheduled_workouts,id',
        ]);

        $user = $request->user();

        $session = WorkoutSession::create([
            'user_id' => $user->id,
            'training_id' => $validated['training_id'],
            'started_at' => Carbon::now(),
        ]);

        if (!empty($validated['scheduled_workout_id'])) {
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

    public function finish(Request $request, WorkoutSession $session)
    {
        if ($session->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        if ($session->completed_at !== null) {
            return response()->json(['message' => 'This workout is already completed.'], 400);
        }

        $validated = $request->validate([
            'scheduled_workout_id' => 'nullable|exists:scheduled_workouts,id',
            'sets' => 'required|array|min:0',
            'sets.*.exercise_id' => 'required|exists:exercises,id',
            'sets.*.set_number' => 'required|integer|min:1',
            'sets.*.weight_used' => 'required|numeric|min:0',
            'sets.*.reps_completed' => 'required|integer|min:0',
        ]);

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

            if (!empty($validated['scheduled_workout_id'])) {
                ScheduledWorkout::where('id', $validated['scheduled_workout_id'])
                    ->update(['status' => 'completed']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Workout logged successfully!',
                'duration_minutes' => $session->started_at->diffInMinutes($session->completed_at)
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to save workout data.', 'error' => $e->getMessage()], 500);
        }
    }
}
