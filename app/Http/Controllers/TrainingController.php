<?php

namespace App\Http\Controllers;

use App\Http\Resources\TrainingResource;
use App\Models\Training;
use App\Services\PublicTrainingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Manages the public-facing library of workout plans.
 *
 * This controller allows users to browse available training programs,
 * filter them by difficulty level, and view the specific
 * exercises included in each plan.
 */
class TrainingController extends Controller
{
    public function __construct(private PublicTrainingService $trainingService) {}

    public function index(Request $request): JsonResponse
    {
        $level = $request->query('level');
        $trainings = $this->trainingService->getTrainings($level);

        return response()->json([
            'trainings' => TrainingResource::collection($trainings),
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $trainings = Cache::rememberForever('trainings.all', function () {
            return Training::with('exercises')->get();
        });

        // Find the specific training in the cached list
        $training = $trainings->firstWhere('id', $id);
        // We "Eager Load" the exercises here. This ensures that
        // the response includes the full list of movements,
        // preventing extra database queries on the frontend.
        $training->load('exercises');

        return response()->json([
            'training' => new TrainingResource($training),
        ], 200);
    }
}
