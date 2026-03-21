<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinishWorkoutRequest;
use App\Http\Requests\StartWorkoutRequest;
use App\Models\WorkoutSession;
use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * The engine for recording live workout activity.
 *
 * This controller handles the transition from a "planned" workout
 * to a "live" session. It manages the real-time start/finish
 * logic and ensures all individual sets are saved safely.
 */
class WorkoutController extends Controller
{
    public function __construct(private ScheduleService $scheduleService) {}

    public function start(StartWorkoutRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $session = $this->scheduleService->startWorkout($user, $validated);

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
        Gate::authorize('update', $session);

        // Integrity check: Prevent users from "double-finishing"
        // a session that is already closed.
        if ($session->completed_at !== null) {
            abort(400, 'This workout is already completed.');
        }

        $validated = $request->validated();

        try {
            $this->scheduleService->finishWorkout($session, $validated);

            return response()->json([
                'message' => 'Workout logged successfully!',
                'duration_minutes' => $session->started_at
                    ->diffInMinutes($session->completed_at),
            ], 200);
        } catch (\Exception $e) {
            abort(500, 'Failed to save workout data.');
        }
    }
}
