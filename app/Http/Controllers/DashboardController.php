<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

/**
 * Handles the data needed for the user's main home screen.
 * * This controller gathers biometrics, today's schedule, active 
 * announcements, and recent activity to give the user a complete 
 * snapshot of their fitness journey as soon as they log in.
 */
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

        // We grab only the latest active announcement.
        $globalAnnouncement = Announcement::where('is_active', true)
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
                'scheduled_workout_id' => $todaysWorkout->id,
                'training_id' => $todaysWorkout->training->id,
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

        // We fetch the next 7 days of workouts and key them by date. 
        // This makes it much faster to look them up inside the loop below.
        $schedules = $user->scheduledWorkouts()
            ->whereBetween('date', [$today->toDateString(), $nextWeek->toDateString()])
            ->with(['training'])
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->toDateString();
            });

        $calendar = [];

        // We manually loop through 7 days to ensure that "Rest Days" are
        // also included in the response for the frontend UI to display.
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

        // We only show sessions that were actually finished. 
        $recentHistory = $user->workoutSessions()
            ->with('training')
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->take(5)
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'training_name' => $session->training ?
                        $session->training->name : 'Custom Workout',
                    'date' => $session->completed_at->format('M d, Y'),
                    'duration_minutes' => $session->started_at
                        ->diffInMinutes($session->completed_at),
                ];
            });

        return response()->json($recentHistory, 200);
    }
}
