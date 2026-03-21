<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    public function getMetrics(): array
    {
        $totalUsers = User::count();
        $proUsers = User::where('role', UserRole::PRO)->count();
        $freeUsers = User::where('role', UserRole::FREE)->count();

        $weeklyWorkouts = WorkoutSession::whereNotNull('completed_at')
            ->where('completed_at', '>=', Carbon::now()->subDays(7))
            ->count();

        $popularTraining = DB::table('workout_sessions')
            ->join('trainings', 'workout_sessions.training_id', '=', 'trainings.id')
            ->select('trainings.name', DB::raw('count(*) as total_completions'))
            ->whereNotNull('workout_sessions.completed_at')
            ->groupBy('trainings.name')
            ->orderByDesc('total_completions')
            ->first();

        return [
            'total_users' => $totalUsers,
            'pro_users' => $proUsers,
            'free_users' => $freeUsers,
            'weekly_workouts_number' => $weeklyWorkouts,
            'most_popular_training' => $popularTraining ? $popularTraining->name : 'N/A',
            'most_popular_training_completions' => $popularTraining ? $popularTraining->total_completions : 0,
        ];
    }
}
