<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrainingRequest;
use App\Http\Resources\TrainingResource;
use App\Models\Training;
use App\Services\TrainingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;

/**
 * Admin portal for managing the library of training programs.
 *
 * This controller allows administrators to design new workout
 * plans by grouping exercises together. It ensures every change
 * to the curriculum is logged for quality control.
 */
class TrainingController extends Controller
{
    public function __construct(private TrainingService $trainingService) {}

    public function index(): JsonResponse
    {
        // We include the exercises in the list so admins can
        // quickly see the movements associated with each plan.
        $trainings = Cache::rememberForever('trainings.all', function () {
            return Training::with('exercises')->get();
        });

        return response()->json(TrainingResource::collection($trainings), 200);
    }

    public function store(StoreTrainingRequest $request): JsonResponse
    {
        Gate::authorize('create', Training::class);

        $validated = $request->validated();
        $admin = $request->user();

        $training = $this->trainingService->createTraining($admin, $validated);

        Cache::forget('trainings.all');

        return response()->json([
            'message' => 'Training created successfully.',
            'training' => new TrainingResource($training->load('exercises')),
        ], 201);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $training = Training::findOrFail($id);
        Gate::authorize('delete', $training);

        $admin = $request->user();

        $this->trainingService->deleteTraining($admin, $id);

        Cache::forget('trainings.all');

        return response()->json(['message' => 'Training deleted successfully.'], 200);
    }
}
