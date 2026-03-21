<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Transforms raw workout data into meaningful fitness insights.
 *
 * This controller calculates lifting volume, tracks consistency,
 * and provides the data needed for the frontend charts. It also
 * enforces tier-based access to advanced analytics.
 */
class AnalyticsController extends Controller
{
    public function __construct(private AnalyticsService $analyticsService) {}

    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $summary = $this->analyticsService->getSummary($user);

        return response()->json([
            'tier' => $user->role,
            'total_workouts_all_time' => $summary['total_workouts_all_time'],
            'monthly_volume_kg' => $summary['monthly_volume_kg'],
        ], 200);
    }

    public function volumeTrend(Request $request): JsonResponse
    {
        $user = $request->user();
        $trend = $this->analyticsService->getVolumeTrend($user);

        return response()->json($trend, 200);
    }

    public function muscleDistribution(Request $request): JsonResponse
    {
        Gate::authorize('viewProStats');

        $distribution = $this->analyticsService->getMuscleDistribution($request->user());

        return response()->json($distribution, 200);
    }
}
