<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AdminUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Admin-only interface for managing the FitTrack user base.
 *
 * This controller allows administrators to manage roles, view
 * deleted accounts, and perform permanent data cleanup. Every
 * destructive or sensitive action is recorded in the Audit Logs.
 */
class UserController extends Controller
{
    public function __construct(private AdminUserService $adminUserService) {}

    public function index(): JsonResponse
    {
        $users = User::all();

        return response()->json(UserResource::collection($users), 200);
    }

    public function trashed(): JsonResponse
    {
        // We retrieve users who have "Soft Deleted" their accounts.
        // This allows admins to investigate or restore them if needed.
        $trashedUsers = User::onlyTrashed()->get();

        return response()->json(UserResource::collection($trashedUsers), 200);
    }

    public function updateRole(UpdateUserRoleRequest $request, $id): JsonResponse
    {
        $validated = $request->validated();
        $admin = $request->user();
        $targetUser = User::findOrFail($id);

        Gate::authorize('updateRole', $targetUser);

        $updatedUser = $this->adminUserService->updateRole($admin, $id, $validated['role']);

        return response()->json([
            'message' => "User role updated to {$validated['role']}.",
            'user' => new UserResource($updatedUser),
        ], 200);
    }

    public function restore(Request $request, $id): JsonResponse
    {
        $admin = $request->user();

        $this->adminUserService->restoreUser($admin, $id);

        return response()->json([
            'message' => 'User account restored successfully.',
        ], 200);
    }

    public function forceDelete(Request $request, $id): JsonResponse
    {
        $admin = $request->user();

        $this->adminUserService->forceDeleteUser($admin, $id);

        return response()->json([
            'message' => 'User and all associated data permanently deleted.',
        ], 200);
    }
}
