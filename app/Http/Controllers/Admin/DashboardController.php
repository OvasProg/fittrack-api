<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Provides high-level business metrics for the admin dashboard.
 *
 * This controller aggregates data from across the entire
 * application to show growth trends, user distribution,
 * and the overall popularity of different training programs.
 */
class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $totalUsers = User::count();
        $proUsers = User::where('role', 'pro')->count();
        $freeUsers = User::where('role', 'free')->count();

        $weeklyWorkouts = WorkoutSession::whereNotNull('completed_at')
            ->where('completed_at', '>=', Carbon::now()->subDays(7))
            ->count();

        // We use a Join and GroupBy here to find out which specific
        // training plan is performing the best
        $popularTraining = DB::table('workout_sessions')
            ->join('trainings', 'workout_sessions.training_id', '=', 'trainings.id')
            ->select('trainings.name', DB::raw('count(*) as total_completions'))
            ->whereNotNull('workout_sessions.completed_at')
            ->groupBy('trainings.name')
            ->orderByDesc('total_completions')
            ->first();

        return response()->json([
            'metrics' => [
                'total_users' => $totalUsers,
                'pro_users' => $proUsers,
                'free_users' => $freeUsers,
                'weekly_workouts_number' => $weeklyWorkouts,
                'most_popular_training' => $popularTraining
                    ? $popularTraining->name : 'N/A',
                'most_popular_training_completions' => $popularTraining
                    ? $popularTraining->total_completions : 0,
            ],
        ], 200);
    }
}
