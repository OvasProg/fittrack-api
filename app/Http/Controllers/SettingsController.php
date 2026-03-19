<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Training;
use Carbon\Carbon;

class SettingsController extends Controller
{
    public function updateBiometrics(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'age' => 'sometimes|integer|min:10|max:100',
            'weight' => 'sometimes|numeric|min:30|max:300',
            'height' => 'sometimes|numeric|min:100|max:250',
            'experience_level' => 'sometimes|string|in:Beginner,Intermediate,Advanced',
            'training_days' => 'sometimes|array|min:1|max:7',
            'training_days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ]);

        $levelChanged = isset($validated['experience_level']) && $validated['experience_level'] !== $user->experience_level;

        $daysChanged = isset($validated['training_days']) && json_encode($validated['training_days']) !== json_encode($user->training_days);

        $user->update($validated);

        if ($levelChanged || $daysChanged) {
            $this->reschedulePendingWorkouts($user);
        }

        return response()->json([
            'message' => ($levelChanged || $daysChanged) ? 'Profile updated and schedule recalculated.' : 'Biometric data updated successfully.',
            'user' => [
                'name' => $user->name,
                'age' => $user->age,
                'weight' => $user->weight,
                'height' => $user->height,
                'experience_level' => $user->experience_level,
                'training_days' => $user->training_days,
            ]
        ], 200);
    }

    public function destroyAccount(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();

        $user->delete();

        return response()->json([
            'message' => 'Account has been scheduled for deletion and successfully deactivated.'
        ], 200);
    }

    private function reschedulePendingWorkouts($user)
    {
        $newTrainings = Training::where('difficulty_level', $user->experience_level)->get();
        $preferredDays = $user->training_days;

        if ($newTrainings->isEmpty() || empty($preferredDays)) return;

        $pendingWorkouts = $user->scheduledWorkouts()
            ->where('status', 'pending')
            ->where('date', '>=', Carbon::today()->toDateString())
            ->orderBy('date', 'asc')
            ->get();

        $trainingIndex = 0;
        $datePointer = Carbon::today();

        foreach ($pendingWorkouts as $workout) {

            while (!in_array($datePointer->format('l'), $preferredDays)) {
                $datePointer->addDay();
            }

            $training = $newTrainings[$trainingIndex % $newTrainings->count()];

            $workout->update([
                'training_id' => $training->id,
                'date' => $datePointer->toDateString()
            ]);

            $datePointer->addDay();
            $trainingIndex++;
        }
    }
}
