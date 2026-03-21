<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AdminUserService
{
    public function updateRole(User $admin, int $targetUserId, string $role): User
    {
        $targetUser = User::findOrFail($targetUserId);

        $oldRole = $targetUser->role;
        $targetUser->update(['role' => $role]);

        AuditLog::create([
            'admin_id' => $admin->id,
            'action' => 'changed_user_role',
            'details' => json_encode([
                'target_user_id' => $targetUser->id,
                'from' => $oldRole,
                'to' => $role,
            ]),
        ]);

        return $targetUser;
    }

    public function restoreUser(User $admin, int $targetUserId): void
    {
        $targetUser = User::onlyTrashed()->findOrFail($targetUserId);
        $targetUser->restore();

        AuditLog::create([
            'admin_id' => $admin->id,
            'action' => 'restored_user',
            'details' => json_encode(['target_user_id' => $targetUser->id]),
        ]);
    }

    public function forceDeleteUser(User $admin, int $targetUserId): void
    {
        $targetUser = User::onlyTrashed()->findOrFail($targetUserId);
        $targetUser->forceDelete();

        AuditLog::create([
            'admin_id' => $admin->id,
            'action' => 'permanently_deleted_user',
            'details' => json_encode(['target_user_id' => $targetUserId]),
        ]);
    }
}
