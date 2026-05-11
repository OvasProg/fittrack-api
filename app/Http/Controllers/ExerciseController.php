<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Http\Resources\ExerciseResource;
use App\Models\Exercise;
use App\Services\ExerciseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Manages the global exercise library.
 *
 * This controller allows users to browse available movements
 * and allows administrators to manage the library.
 */
class ExerciseController extends Controller
{
    public function __construct(private ExerciseService $exerciseService) {}

    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $filters = $request->only(['target_muscle', 'name']);
        $exercises = $this->exerciseService->getAllExercises($filters);

        return ExerciseResource::collection($exercises);
    }

    public function store(StoreExerciseRequest $request): JsonResponse
    {
        Gate::authorize('create', Exercise::class);

        $validated = $request->validated();
        $admin = $request->user();

        $exercise = $this->exerciseService->createExercise($admin, $validated);

        return response()->json([
            'message' => 'Exercise added to library successfully.',
            'exercise' => new ExerciseResource($exercise),
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $exercise = Exercise::findOrFail($id);

        return response()->json([
            'exercise' => new ExerciseResource($exercise),
        ], 200);
    }

    public function update(UpdateExerciseRequest $request, $id): JsonResponse
    {
        $exercise = Exercise::findOrFail($id);
        Gate::authorize('update', $exercise);

        $validated = $request->validated();
        $admin = $request->user();

        $exercise = $this->exerciseService->updateExercise($admin, $id, $validated);

        return response()->json([
            'message' => 'Exercise updated successfully.',
            'exercise' => new ExerciseResource($exercise),
        ], 200);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $exercise = Exercise::findOrFail($id);
        Gate::authorize('delete', $exercise);

        $admin = $request->user();

        $this->exerciseService->deleteExercise($admin, $id);

        return response()->json(null, 204);
    }
}
