<?php

namespace App\Services;

use App\Enums\WorkoutStatus;
use App\Models\Training;
use App\Models\User;
use Carbon\Carbon;

class SettingsService
{
    public function updateBiometrics(User $user, array $data): array
    {
        $levelChanged = isset($data['experience_level']) &&
            $data['experience_level'] !== $user->experience_level;

        $daysChanged = isset($data['training_days']) &&
            json_encode($data['training_days']) !== json_encode($user->training_days);

        $user->update($data);

        if ($levelChanged || $daysChanged) {
            $this->reschedulePendingWorkouts($user);
        }

        return [
            'levelChanged' => $levelChanged,
            'daysChanged' => $daysChanged,
        ];
    }

    public function destroyAccount(User $user): void
    {
        $user->tokens()->delete();
        $user->delete();
    }

    private function reschedulePendingWorkouts(User $user): void
    {
        $newTrainings = Training::where('difficulty_level', $user->experience_level)->get();
        $preferredDays = $user->training_days;

        if ($newTrainings->isEmpty() || empty($preferredDays)) {
            return;
        }

        $pendingWorkouts = $user->scheduledWorkouts()
            ->where('status', WorkoutStatus::PENDING)
            ->where('date', '>=', Carbon::today()->toDateString())
            ->orderBy('date', 'asc')
            ->get();

        $trainingIndex = 0;
        $datePointer = Carbon::today();

        foreach ($pendingWorkouts as $workout) {
            while (! in_array($datePointer->format('l'), $preferredDays)) {
                $datePointer->addDay();
            }

            $training = $newTrainings[$trainingIndex % $newTrainings->count()];

            $workout->update([
                'training_id' => $training->id,
                'date' => $datePointer->toDateString(),
            ]);

            $datePointer->addDay();
            $trainingIndex++;
        }
    }
}
