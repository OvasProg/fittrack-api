<?php

namespace App\Http\Controllers;

use App\Enums\WorkoutStatus;
use App\Http\Requests\StoreOnboardingRequest;
use App\Http\Resources\UserResource;
use App\Models\ScheduledWorkout;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

/**
 * Handles the initial setup for new FitTrack users.
 *
 * This controller captures the user's physical profile and
 * automatically generates their first week of training
 * based on their experience level and preferred days.
 */
class OnboardingController extends Controller
{
    public function store(StoreOnboardingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = $request->user();

        $user->update([
            'age' => $validated['age'],
            'weight' => $validated['weight'],
            'height' => $validated['height'],
            'experience_level' => $validated['experience_level'],
            'training_days' => $validated['training_days'],
        ]);

        // We clear any existing schedules before generating new ones.
        $user->scheduledWorkouts()->delete();

        // We try to find plans that match the user's skill level.
        // If no perfect match is found, we provide generic
        // plans so the user isn't left with an empty calendar.
        $trainings = Training::where('difficulty_level', 'LIKE', $validated['experience_level'])
            ->get();

        if ($trainings->count() === 0) {
            $trainings = Training::take(7)->get();

            if ($trainings->count() === 0) {
                return response()->json([
                    'message' => 'No trainings exist in database. Please run seeders.',
                ], 400);
            }
        }

        $trainingIndex = 0;
        $today = Carbon::today();

        // We loop through the next 7 days. If the day name matches
        // one of the user's chosen training days, we assign
        // a workout from our matching pool.
        for ($i = 0; $i < 7; $i++) {
            $currentDate = $today->copy()->addDays($i);
            $dayName = $currentDate->format('l');

            if (in_array($dayName, $validated['training_days'])) {
                // We use the modulo operator (%) to cycle through
                // available trainings if there are fewer plans
                // than there are training days.
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

        return response()->json([
            'message' => 'Onboarding complete. Schedule generated successfully.',
            'user' => new UserResource($user->fresh()),
        ], 200);
    }
}
