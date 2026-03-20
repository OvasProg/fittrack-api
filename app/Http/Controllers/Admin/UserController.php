<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin-only interface for managing the FitTrack user base.
 *
 * This controller allows administrators to manage roles, view
 * deleted accounts, and perform permanent data cleanup. Every
 * destructive or sensitive action is recorded in the Audit Logs.
 */
class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::select('id', 'name', 'email', 'role', 'created_at')->get();

        return response()->json($users, 200);
    }

    public function trashed(): JsonResponse
    {
        // We retrieve users who have "Soft Deleted" their accounts.
        // This allows admins to investigate or restore them if needed.
        $trashedUsers = User::onlyTrashed()
            ->select('id', 'name', 'email', 'role', 'deleted_at')
            ->get();

        return response()->json($trashedUsers, 200);
    }

    public function updateRole(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'role' => 'required|string|in:free,pro,admin',
        ]);

        $targetUser = User::findOrFail($id);

        // Security Check: We prevent an admin from accidentally
        // demoting themselves and losing access to the dashboard.
        if ($targetUser->id === $request->user()->id) {
            return response()->json([
                'message' => 'You cannot change your own role.',
            ], 403);
        }

        $oldRole = $targetUser->role;
        $targetUser->update(['role' => $validated['role']]);

        // Log the change
        AuditLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'changed_user_role',
            'details' => json_encode([
                'target_user_id' => $targetUser->id,
                'from' => $oldRole,
                'to' => $validated['role'],
            ]),
        ]);

        return response()->json([
            'message' => "User role updated to {$validated['role']}.",
            'user' => $targetUser,
        ], 200);
    }

    public function restore(Request $request, $id): JsonResponse
    {
        $targetUser = User::onlyTrashed()->findOrFail($id);
        $targetUser->restore();

        AuditLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'restored_user',
            'details' => json_encode(['target_user_id' => $targetUser->id]),
        ]);

        return response()->json([
            'message' => 'User account restored successfully.',
        ], 200);
    }

    public function forceDelete(Request $request, $id): JsonResponse
    {
        $targetUser = User::onlyTrashed()->findOrFail($id);
        $targetUserId = $targetUser->id;

        // This action is irreversible. It wipes the user
        // and all their workout history from the database.
        $targetUser->forceDelete();

        AuditLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'permanently_deleted_user',
            'details' => json_encode(['target_user_id' => $targetUserId]),
        ]);

        return response()->json([
            'message' => 'User and all associated data permanently deleted.',
        ], 200);
    }
}
