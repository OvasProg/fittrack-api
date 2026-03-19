<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Training;
use App\Models\ScheduledWorkout;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class OnboardingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'age' => 'required|integer|min:10|max:100',
            'weight' => 'required|numeric|min:30|max:300',
            'height' => 'required|numeric|min:100|max:250',
            'experience_level' => 'required|string|in:Beginner,Intermediate,Advanced',
            'training_days' => 'required|array|min:1|max:7',
            'training_days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ]);

        $user = $request->user();

        $user->update([
            'age' => $validated['age'],
            'weight' => $validated['weight'],
            'height' => $validated['height'],
            'experience_level' => $validated['experience_level'],
            'training_days' => $validated['training_days'],
        ]);

        $user->scheduledWorkouts()->delete();

        $trainings = Training::where('difficulty_level', 'LIKE', $validated['experience_level'])->get();

        if ($trainings->count() === 0) {
            $trainings = Training::take(7)->get();

            if ($trainings->count() === 0) {
                return response()->json(['message' => 'No trainings exist in database. Please run seeders.'], 400);
            }
        }

        $trainingIndex = 0;
        $today = Carbon::today();

        for ($i = 0; $i < 7; $i++) {
            $currentDate = $today->copy()->addDays($i);
            $dayName = $currentDate->format('l');

            if (in_array($dayName, $validated['training_days'])) {
                $training = $trainings->values()->get($trainingIndex % $trainings->count());

                if ($training) {
                    ScheduledWorkout::create([
                        'user_id' => $user->id,
                        'training_id' => $training->id,
                        'date' => $currentDate->toDateString(),
                        'status' => 'pending',
                    ]);
                    $trainingIndex++;
                }
            }
        }

        return response()->json([
            'message' => 'Onboarding complete. Schedule generated successfully.',
            'user' => $user->fresh()
        ], 200);
    }
}
