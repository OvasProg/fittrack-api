<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();

        $todaysWorkout = $user->scheduledWorkouts()
            ->where('date', $today)
            ->with('training')
            ->first();

        $globalAnnouncement = \App\Models\Announcement::where('is_active', true)
            ->select('title', 'message')
            ->first();

        return response()->json([
            'user' => [
                'name' => $user->name,
                'tier' => $user->role,
                'biometrics' => [
                    'age' => $user->age,
                    'weight' => $user->weight,
                    'height' => $user->height,
                    'experience_level' => $user->experience_level,
                    'training_days' => $user->training_days,
                ],
            ],
            'announcement' => $globalAnnouncement,
            'todays_workout' => $todaysWorkout ? [
                'id' => $todaysWorkout->training->id,
                'name' => $todaysWorkout->training->name,
                'difficulty' => $todaysWorkout->training->difficulty_level,
                'status' => $todaysWorkout->status,
            ] : null,
        ], 200);
    }

    public function calendar(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = Carbon::today();
        $nextWeek = Carbon::today()->addDays(6);

        $schedules = $user->scheduledWorkouts()
            ->whereBetween('date', [$today->toDateString(), $nextWeek->toDateString()])
            ->with(['training'])
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->toDateString();
            });

        $calendar = [];

        for ($i = 0; $i < 7; $i++) {
            $currentDate = $today->copy()->addDays($i)->toDateString();
            $dayName = $today->copy()->addDays($i)->format('l');

            $scheduledWorkout = $schedules->get($currentDate);

            $calendar[] = [
                'date' => $currentDate,
                'day_name' => $dayName,
                'is_today' => $i === 0,
                'status' => $scheduledWorkout ? $scheduledWorkout->status : 'rest_day',
                'training' => $scheduledWorkout ? [
                    'id' => $scheduledWorkout->training->id,
                    'name' => $scheduledWorkout->training->name,
                    'difficulty' => $scheduledWorkout->training->difficulty_level,
                ] : null,
            ];
        }

        return response()->json($calendar, 200);
    }

    public function recentHistory(Request $request): JsonResponse
    {
        $user = $request->user();

        $recentHistory = $user->workoutSessions()
            ->with('training')
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->take(5)
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'training_name' => $session->training ? $session->training->name : 'Custom Workout',
                    'date' => $session->completed_at->format('M d, Y'),
                    'duration_minutes' => $session->started_at->diffInMinutes($session->completed_at),
                ];
            });

        return response()->json($recentHistory, 200);
    }
}
