<?php

namespace App\Services;

use App\Enums\WorkoutStatus;
use App\Models\ScheduledWorkout;
use App\Models\Training;
use App\Models\User;
use Carbon\Carbon;
use Exception;

class OnboardingService
{
    public function completeOnboarding(User $user, array $data): void
    {
        $user->update([
            'age' => $data['age'],
            'weight' => $data['weight'],
            'height' => $data['height'],
            'experience_level' => $data['experience_level'],
            'training_days' => $data['training_days'],
        ]);

        $this->generateInitialSchedule($user, $data['experience_level'], $data['training_days']);
    }

    private function generateInitialSchedule(User $user, string $experienceLevel, array $trainingDays): void
    {
        // We clear any existing schedules before generating new ones.
        $user->scheduledWorkouts()->delete();

        // We try to find plans that match the user's skill level.
        $trainings = Training::where('difficulty_level', 'LIKE', $experienceLevel)->get();

        if ($trainings->count() === 0) {
            $trainings = Training::take(7)->get();

            if ($trainings->count() === 0) {
                throw new Exception('No trainings exist in database. Please run seeders.');
            }
        }

        $trainingIndex = 0;
        $today = Carbon::today();

        for ($i = 0; $i < 7; $i++) {
            $currentDate = $today->copy()->addDays($i);
            $dayName = $currentDate->format('l');

            if (in_array($dayName, $trainingDays)) {
                $training = $trainings->values()->get($trainingIndex % $trainings->count());

                if ($training) {
                    ScheduledWorkout::create([
                        'user_id' => $user->id,
                        'training_id' => $training->id,
                        'date' => $currentDate->toDateString(),
                        'status' => WorkoutStatus::PENDING,
                    ]);
                    $trainingIndex++;
                }
            }
        }
    }
}
