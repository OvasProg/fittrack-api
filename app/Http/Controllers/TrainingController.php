<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainingRequest;
use App\Http\Requests\UpdateTrainingRequest;
use App\Http\Resources\ExerciseResource;
use App\Http\Resources\TrainingResource;
use App\Models\Training;
use App\Services\TrainingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Manages the library of workout plans.
 *
 * This controller allows users to browse available training programs,
 * and allows administrators to manage the curriculum.
 */
class TrainingController extends Controller
{
    public function __construct(private TrainingService $trainingService) {}

    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $filters = $request->only(['difficulty', 'title']);
        $trainings = $this->trainingService->getAllTrainings($filters);

        return TrainingResource::collection($trainings);
    }

    public function store(StoreTrainingRequest $request): JsonResponse
    {
        Gate::authorize('create', Training::class);

        $validated = $request->validated();
        $admin = $request->user();

        $training = $this->trainingService->createTraining($admin, $validated);

        return response()->json([
            'message' => 'Training created successfully.',
            'training' => new TrainingResource($training->load('exercises')),
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $training = Training::with('exercises')->findOrFail($id);

        return response()->json([
            'training' => new TrainingResource($training),
        ], 200);
    }

    public function update(UpdateTrainingRequest $request, $id): JsonResponse
    {
        $training = Training::findOrFail($id);
        Gate::authorize('update', $training);

        $validated = $request->validated();
        $admin = $request->user();

        $training = $this->trainingService->updateTraining($admin, $id, $validated);

        return response()->json([
            'message' => 'Training updated successfully.',
            'training' => new TrainingResource($training->load('exercises')),
        ], 200);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $training = Training::findOrFail($id);
        Gate::authorize('delete', $training);

        $admin = $request->user();

        $this->trainingService->deleteTraining($admin, $id);

        return response()->json(null, 204);
    }

    public function exercises($id): JsonResponse
    {
        $exercises = $this->trainingService->getExercises($id);

        return response()->json([
            'exercises' => ExerciseResource::collection($exercises),
        ], 200);
    }

    public function attachExercise(Request $request, $id): JsonResponse
    {
        $training = Training::findOrFail($id);
        Gate::authorize('update', $training);

        $request->validate([
            'exercise_id' => 'required|exists:exercises,id',
            'default_sets' => 'nullable|integer|min:1',
            'default_reps' => 'nullable|integer|min:1',
        ]);

        $admin = $request->user();
        $exerciseId = $request->input('exercise_id');
        $pivotData = $request->only(['default_sets', 'default_reps']);

        $this->trainingService->attachExercise($admin, $id, $exerciseId, $pivotData);

        return response()->json(['message' => 'Exercise attached successfully.'], 200);
    }

    public function detachExercise(Request $request, $id, $exerciseId): JsonResponse
    {
        $training = Training::findOrFail($id);
        Gate::authorize('update', $training);

        // Find the exercise first to ensure a 404 is returned if it doesn't exist
        \App\Models\Exercise::findOrFail($exerciseId);

        $admin = $request->user();

        $this->trainingService->detachExercise($admin, $id, $exerciseId);

        return response()->json(null, 204);
    }
}
