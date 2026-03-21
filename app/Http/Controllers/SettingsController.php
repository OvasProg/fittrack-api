<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBiometricsRequest;
use App\Http\Resources\UserResource;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Manages user profile updates and account lifecycle.
 *
 * This controller allows users to tweak their physical data or
 * training preferences. It also handles the complex logic of
 * shifting their future schedule if they change their fitness
 * level or available days.
 */
class SettingsController extends Controller
{
    public function __construct(private SettingsService $settingsService) {}

    public function updateBiometrics(UpdateBiometricsRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $result = $this->settingsService->updateBiometrics($user, $validated);

        return response()->json([
            'message' => ($result['levelChanged'] || $result['daysChanged'])
                ? 'Profile updated and schedule recalculated.'
                : 'Biometric data updated successfully.',
            'user' => new UserResource($user),
        ], 200);
    }

    public function destroyAccount(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->settingsService->destroyAccount($user);

        return response()->json([
            'message' => 'Account deactivated and scheduled for deletion.',
        ], 200);
    }
}
