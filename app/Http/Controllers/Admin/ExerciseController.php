<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExerciseRequest;
use App\Models\AuditLog;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin portal for managing the global exercise library.
 *
 * This controller allows admins to define the individual movements
 * (like Squats or Bench Press) that users can track. It serves
 * as the data source for the training plan builder.
 */
class ExerciseController extends Controller
{
    public function index(): JsonResponse
    {
        $exercises = Exercise::all();

        return response()->json($exercises, 200);
    }

    public function store(StoreExerciseRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // If no multiplier is provided, we default to 1.0
        if (! isset($validated['base_multiplier'])) {
            $validated['base_multiplier'] = 1.0;
        }

        $exercise = Exercise::create($validated);

        // Logging
        AuditLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'created_exercise',
            'details' => json_encode([
                'exercise_id' => $exercise->id,
                'name' => $exercise->name,
            ]),
        ]);

        return response()->json([
            'message' => 'Exercise added to library successfully.',
            'exercise' => $exercise,
        ], 201);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $exercise = Exercise::findOrFail($id);
        $exerciseName = $exercise->name;

        $exercise->delete();

        // Logging
        AuditLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'deleted_exercise',
            'details' => json_encode(['exercise_name' => $exerciseName]),
        ]);

        return response()->json([
            'message' => 'Exercise removed from library.',
        ], 200);
    }
}
