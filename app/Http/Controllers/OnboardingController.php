<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOnboardingRequest;
use App\Http\Resources\UserResource;
use App\Services\OnboardingService;
use Illuminate\Http\JsonResponse;

/**
 * Handles the initial setup for new FitTrack users.
 *
 * This controller captures the user's physical profile and
 * automatically generates their first week of training
 * based on their experience level and preferred days.
 */
class OnboardingController extends Controller
{
    public function __construct(private OnboardingService $onboardingService) {}

    public function store(StoreOnboardingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $this->onboardingService->completeOnboarding($user, $validated);

        return response()->json([
            'message' => 'Onboarding complete. Schedule generated successfully.',
            'user' => new UserResource($user->fresh()),
        ], 200);
    }
}
