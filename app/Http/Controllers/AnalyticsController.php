<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Transforms raw workout data into meaningful fitness insights.
 *
 * This controller calculates lifting volume, tracks consistency,
 * and provides the data needed for the frontend charts. It also
 * enforces tier-based access to advanced analytics.
 */
class AnalyticsController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        $totalWorkouts = $user->workoutSessions()
            ->whereNotNull('completed_at')
            ->count();

        $recentSessions = $user->workoutSessions()
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $thirtyDaysAgo)
            ->with('workoutSets')
            ->get();

        $monthlyVolume = 0;
        foreach ($recentSessions as $session) {
            foreach ($session->workoutSets as $set) {
                $monthlyVolume += ($set->weight_used * $set->reps_completed);
            }
        }

        return response()->json([
            'tier' => $user->role,
            'total_workouts_all_time' => $totalWorkouts,
            'monthly_volume_kg' => $monthlyVolume,
        ], 200);
    }

    public function volumeTrend(Request $request): JsonResponse
    {
        $user = $request->user();

        // We look back 14 days. Can be changed if needed
        $chartSessions = $user->workoutSessions()
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', Carbon::now()->subDays(14))
            ->with('workoutSets')
            ->get();

        $volumeTrend = [];

        foreach ($chartSessions as $session) {
            $dateKey = $session->completed_at->format('M d');

            $sessionVolume = 0;
            foreach ($session->workoutSets as $set) {
                $sessionVolume += ($set->weight_used * $set->reps_completed);
            }

            if (! isset($volumeTrend[$dateKey])) {
                $volumeTrend[$dateKey] = 0;
            }

            $volumeTrend[$dateKey] += $sessionVolume;
        }

        return response()->json([
            'labels' => array_keys($volumeTrend),
            'data' => array_values($volumeTrend),
        ], 200);
    }

    public function muscleDistribution(Request $request): JsonResponse
    {
        $user = $request->user();

        // This is a "Pro" feature
        if ($user->role === 'free') {
            return response()->json([
                'message' => 'Upgrade to Pro to view detailed muscle distribution.',
            ], 403);
        }

        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // We use a Join query here for performance, as we need to
        // look across Sessions, Sets, and Exercises all at once
        // to see which muscle groups are getting the most work.
        $distribution = DB::table('workout_sessions')
            ->join('workout_sets', 'workout_sessions.id', '=', 'workout_sets.workout_session_id')
            ->join('exercises', 'workout_sets.exercise_id', '=', 'exercises.id')
            ->where('workout_sessions.user_id', $user->id)
            ->whereNotNull('workout_sessions.completed_at')
            ->where('workout_sessions.completed_at', '>=', $thirtyDaysAgo)
            ->select('exercises.target_muscle', DB::raw('count(*) as total_sets'))
            ->groupBy('exercises.target_muscle')
            ->pluck('total_sets', 'target_muscle');

        return response()->json($distribution, 200);
    }
}
