<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Manages user profile updates and account lifecycle.
 *
 * This controller allows users to tweak their physical data or
 * training preferences. It also handles the complex logic of
 * shifting their future schedule if they change their fitness
 * level or available days.
 */
class SettingsController extends Controller
{
    public function updateBiometrics(Request $request): JsonResponse
    {
        $user = $request->user();

        // We use 'sometimes' so the user can update just one field
        // (like weight) without having to send their entire
        // profile data again.
        $validated = $request->validate([
            'age' => 'sometimes|integer|min:10|max:100',
            'weight' => 'sometimes|numeric|min:30|max:300',
            'height' => 'sometimes|numeric|min:100|max:250',
            'experience_level' => 'sometimes|string|in:Beginner,Intermediate,Advanced',
            'training_days' => 'sometimes|array|min:1|max:7',
            'training_days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ]);

        // We track if the core "plan" variables changed. If they did,
        // we need to trigger a fresh calculation of their calendar.
        $levelChanged = isset($validated['experience_level']) &&
            $validated['experience_level'] !== $user->experience_level;

        $daysChanged = isset($validated['training_days']) &&
            json_encode($validated['training_days']) !== json_encode($user->training_days);

        $user->update($validated);

        if ($levelChanged || $daysChanged) {
            $this->reschedulePendingWorkouts($user);
        }

        return response()->json([
            'message' => ($levelChanged || $daysChanged)
                ? 'Profile updated and schedule recalculated.'
                : 'Biometric data updated successfully.',
            'user' => $user->only([
                'name',
                'age',
                'weight',
                'height',
                'experience_level',
                'training_days',
            ]),
        ], 200);
    }

    public function destroyAccount(Request $request): JsonResponse
    {
        $user = $request->user();

        // We revoke all active login tokens immediately so the user
        // is kicked out of all devices before the account is deleted.
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'Account deactivated and scheduled for deletion.',
        ], 200);
    }

    /**
     * Logic for shifting future workouts to match new user goals.
     */
    private function reschedulePendingWorkouts($user): void
    {
        $newTrainings = Training::where('difficulty_level', $user->experience_level)->get();
        $preferredDays = $user->training_days;

        if ($newTrainings->isEmpty() || empty($preferredDays)) {
            return;
        }

        // We only touch 'pending' workouts. If a user already finished
        // a session, we leave it alone to preserve their history.
        $pendingWorkouts = $user->scheduledWorkouts()
            ->where('status', 'pending')
            ->where('date', '>=', Carbon::today()->toDateString())
            ->orderBy('date', 'asc')
            ->get();

        $trainingIndex = 0;
        $datePointer = Carbon::today();

        foreach ($pendingWorkouts as $workout) {
            // We skip over days that aren't in the user's new
            // preferred schedule
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
