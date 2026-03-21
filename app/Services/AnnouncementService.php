<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\User;

class AnnouncementService
{
    public function broadcastAnnouncement(User $admin, array $data): Announcement
    {
        Announcement::where('is_active', true)->update(['is_active' => false]);

        $announcement = Announcement::create([
            'title' => $data['title'],
            'message' => $data['message'],
            'is_active' => true,
        ]);

        AuditLog::create([
            'admin_id' => $admin->id,
            'action' => 'broadcast_announcement',
            'details' => json_encode(['title' => $announcement->title]),
        ]);

        return $announcement;
    }
}
