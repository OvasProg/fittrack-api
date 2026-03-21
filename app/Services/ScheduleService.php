<?php

namespace App\Services;

use App\Enums\WorkoutStatus;
use App\Models\ScheduledWorkout;
use App\Models\User;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ScheduleService
{
    public function generateCalendar(User $user): array
    {
        $today = Carbon::today();
        $nextWeek = Carbon::today()->addDays(6);

        // We fetch the next 7 days of workouts and key them by date.
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
                'status' => $scheduledWorkout ? $scheduledWorkout->status : WorkoutStatus::REST_DAY,
                'training' => $scheduledWorkout ? [
                    'id' => $scheduledWorkout->training->id,
                    'name' => $scheduledWorkout->training->name,
                    'difficulty' => $scheduledWorkout->training->difficulty_level,
                ] : null,
            ];
        }

        return $calendar;
    }

    public function startWorkout(User $user, array $data): WorkoutSession
    {
        $session = WorkoutSession::create([
            'user_id' => $user->id,
            'training_id' => $data['training_id'],
            'started_at' => Carbon::now(),
        ]);

        if (! empty($data['scheduled_workout_id'])) {
            ScheduledWorkout::where('id', $data['scheduled_workout_id'])
                ->where('user_id', $user->id)
                ->update(['status' => WorkoutStatus::IN_PROGRESS]);
        }

        return $session;
    }

    public function finishWorkout(WorkoutSession $session, array $data): void
    {
        DB::beginTransaction();

        try {
            foreach ($data['sets'] as $set) {
                $session->workoutSets()->create([
                    'exercise_id' => $set['exercise_id'],
                    'set_number' => $set['set_number'],
                    'weight_used' => $set['weight_used'],
                    'reps_completed' => $set['reps_completed'],
                ]);
            }

            $session->update([
                'completed_at' => Carbon::now(),
            ]);

            if (! empty($data['scheduled_workout_id'])) {
                ScheduledWorkout::where('id', $data['scheduled_workout_id'])
                    ->update(['status' => WorkoutStatus::COMPLETED]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
