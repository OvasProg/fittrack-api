<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Provides high-level business metrics for the admin dashboard.
 *
 * This controller aggregates data from across the entire
 * application to show growth trends, user distribution,
 * and the overall popularity of different training programs.
 */
class DashboardController extends Controller
{
    public function __construct(private AdminDashboardService $dashboardService) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAdminDashboard');

        $metrics = $this->dashboardService->getMetrics();

        return response()->json([
            'metrics' => $metrics,
        ], 200);
    }
}
