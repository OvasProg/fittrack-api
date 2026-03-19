<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = Carbon::today();
        $nextWeek = Carbon::today()->addDays(6);

        $schedules = $user->scheduledWorkouts()
            ->whereBetween('date', [$today->toDateString(), $nextWeek->toDateString()])
            ->with(['training'])
            ->get()
            ->keyBy(function ($item) {
                return \Carbon\Carbon::parse($item->date)->toDateString();
            });

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

        $calendar = [];
        $todaysWorkout = null;

        for ($i = 0; $i < 7; $i++) {
            $currentDate = $today->copy()->addDays($i)->toDateString();
            $dayName = $today->copy()->addDays($i)->format('l');

            $scheduledWorkout = $schedules->get($currentDate);

            $calendarData = [
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

            $calendar[] = $calendarData;

            if ($i === 0 && $scheduledWorkout) {
                $todaysWorkout = $scheduledWorkout;
            }
        }

        $chartSessions = $user->workoutSessions()
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', Carbon::now()->subDays(7))
            ->with('workoutSets')
            ->get();

        $volumeTrend = [];

        for ($i = 6; $i >= 0; $i--) {
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

        $filteredVolumeTrend = array_filter($volumeTrend, function ($volume) {
            return $volume > 0;
        });

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
                ],
            ],
            'announcement' => $globalAnnouncement,
            'todays_workout' => $todaysWorkout,
            'weekly_calendar' => $calendar,
            'recent_history' => $recentHistory,
            'charts' => [
                'volume_trend_labels' => array_keys($filteredVolumeTrend),
                'volume_trend_data' => array_values($filteredVolumeTrend),
            ],
        ]);
    }
}
