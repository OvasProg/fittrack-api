<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

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

        $chartSessions = $user->workoutSessions()
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', Carbon::now()->subDays(7))
            ->with('workoutSets')
            ->get();

        $volumeTrend = [];

        // We pre-fill the last 14 days with zeros. This ensures 
        // the chart doesn't have "holes" if the user took 
        // a few rest days in a row.
        for ($i = 13; $i >= 0; $i--) {
            $volumeTrend[Carbon::now()->subDays($i)->format('M d')] = 0;
        }

        foreach ($chartSessions as $session) {
            $sessionVolume = 0;
            foreach ($session->workoutSets as $set) {
                $sessionVolume += ($set->weight_used * $set->reps_completed);
            }

            $dateKey = $session->completed_at->format('M d');
            if (array_key_exists($dateKey, $volumeTrend)) {
                $volumeTrend[$dateKey] += $sessionVolume;
            }
        }

        // We remove dates with 0 volume for the trend view to keep 
        // the graph focused only on active training days.
        $filteredVolumeTrend = array_filter($volumeTrend, function ($volume) {
            return $volume > 0;
        });

        return response()->json([
            'labels' => array_keys($filteredVolumeTrend),
            'data' => array_values($filteredVolumeTrend),
        ], 200);
    }

    public function muscleDistribution(Request $request): JsonResponse
    {
        $user = $request->user();

        // This is a "Pro" feature
        if ($user->role === 'free') {
            return response()->json([
                'message' => 'Upgrade to Pro to view detailed muscle distribution.'
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
