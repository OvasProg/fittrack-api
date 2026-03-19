<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isPro = $user->role === 'pro' || $user->role === 'admin';

        $thirtyDaysAgo = Carbon::now()->subDays(30);

        $totalWorkouts = $user->workoutSessions()->whereNotNull('completed_at')->count();

        $recentSessions = $user->workoutSessions()
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $thirtyDaysAgo)
            ->with('workoutSets')
            ->get();

        $monthlyVolume = 0;
        $volumeTrend = [];

        for ($i = 6; $i >= 0; $i--) {
            $volumeTrend[Carbon::now()->subDays($i)->format('M d')] = 0;
        }

        foreach ($recentSessions as $session) {
            $sessionVolume = 0;
            foreach ($session->workoutSets as $set) {
                $sessionVolume += ($set->weight_used * $set->reps_completed);
            }
            $monthlyVolume += $sessionVolume;

            $dateKey = $session->completed_at->format('M d');
            if (array_key_exists($dateKey, $volumeTrend)) {
                $volumeTrend[$dateKey] += $sessionVolume;
            }
        }

        $filteredVolumeTrend = array_filter($volumeTrend, function ($volume) {
            return $volume > 0;
        });

        $proStats = null;
        if ($isPro) {
            $proStats = [
                'muscle_distribution' => $this->calculateMuscleDistribution($user, $thirtyDaysAgo),
            ];
        }

        return response()->json([
            'tier' => $user->role,
            'basic_stats' => [
                'total_workouts_all_time' => $totalWorkouts,
                'monthly_volume_kg' => $monthlyVolume,
            ],
            'charts' => [
                'volume_trend_labels' => array_keys($filteredVolumeTrend),
                'volume_trend_data' => array_values($filteredVolumeTrend),
            ],
            'pro_stats' => $proStats
        ], 200);
    }

    private function calculateMuscleDistribution($user, $since)
    {
        return DB::table('workout_sessions')
            ->join('workout_sets', 'workout_sessions.id', '=', 'workout_sets.workout_session_id')
            ->join('exercises', 'workout_sets.exercise_id', '=', 'exercises.id')
            ->where('workout_sessions.user_id', $user->id)
            ->whereNotNull('workout_sessions.completed_at')
            ->where('workout_sessions.completed_at', '>=', $since)
            ->select('exercises.target_muscle', DB::raw('count(*) as total_sets'))
            ->groupBy('exercises.target_muscle')
            ->pluck('total_sets', 'target_muscle');
    }
}
