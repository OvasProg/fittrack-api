<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\ScheduledWorkoutResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\WorkoutSessionResource;
use App\Models\Announcement;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Enums\UserRole;

/**
 * Handles the data needed for the user's main home screen.
 * * This controller gathers biometrics, today's schedule, active
 * announcements, and recent activity to give the user a complete
 * snapshot of their fitness journey as soon as they log in.
 */
class DashboardController extends Controller
{
    public function __construct(private ScheduleService $scheduleService) {}

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
                'tier' => $user->subscribed('pro') ? UserRole::PRO : $user->role,
                'biometrics' => (new UserResource($user))->toArray($request)['biometrics'],
            ],
            'announcement' => $globalAnnouncement ? new AnnouncementResource($globalAnnouncement) : null,
            'todays_workout' => $todaysWorkout ? new ScheduledWorkoutResource($todaysWorkout) : null,
        ], 200);
    }

    public function calendar(Request $request): JsonResponse
    {
        $user = $request->user();
        $calendar = $this->scheduleService->generateCalendar($user);

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
            ->get();

        return response()->json(WorkoutSessionResource::collection($recentHistory), 200);
    }
}
