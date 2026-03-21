<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExerciseRequest;
use App\Http\Resources\ExerciseResource;
use App\Models\Exercise;
use App\Services\ExerciseService;
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
    public function __construct(private ExerciseService $exerciseService) {}

    public function index(): JsonResponse
    {
        $exercises = Exercise::all();

        return response()->json(ExerciseResource::collection($exercises), 200);
    }

    public function store(StoreExerciseRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $admin = $request->user();

        $exercise = $this->exerciseService->createExercise($admin, $validated);

        return response()->json([
            'message' => 'Exercise added to library successfully.',
            'exercise' => new ExerciseResource($exercise),
        ], 201);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $admin = $request->user();

        $this->exerciseService->deleteExercise($admin, $id);

        return response()->json([
            'message' => 'Exercise removed from library.',
        ], 200);
    }
}
