<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Global broadcast system for FitTrack admins.
 * * This controller manages the single active announcement 
 * that appears on all user dashboards, useful for 
 * maintenance alerts or new feature updates.
 */
class AnnouncementController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // We automatically deactivate all previous announcements
        Announcement::where('is_active', true)->update(['is_active' => false]);

        $announcement = Announcement::create([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'is_active' => true,
        ]);

        // Logging
        AuditLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'broadcast_announcement',
            'details' => json_encode(['title' => $announcement->title])
        ]);

        return response()->json([
            'message' => 'Announcement broadcasted successfully!',
            'announcement' => $announcement
        ], 201);
    }
}
