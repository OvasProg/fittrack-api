<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Manages the public-facing library of workout plans.
 *
 * This controller allows users to browse available training programs,
 * filter them by difficulty level, and view the specific
 * exercises included in each plan.
 */
class TrainingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Training::query();

        // We allow users to filter the list. If they select a
        // level like 'Beginner', we narrow the results; otherwise,
        // we show the full catalog.
        if ($request->has('level')) {
            $level = $request->query('level');

            if (in_array($level, ['Beginner', 'Intermediate', 'Advanced'])) {
                $query->where('difficulty_level', $level);
            }
        }

        $trainings = $query->get();

        return response()->json([
            'trainings' => $trainings,
        ], 200);
    }

    public function show(Training $training): JsonResponse
    {
        // We "Eager Load" the exercises here. This ensures that
        // the response includes the full list of movements,
        // preventing extra database queries on the frontend.
        $training->load('exercises');

        return response()->json([
            'training' => $training,
        ], 200);
    }
}
