<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        Announcement::where('is_active', true)->update(['is_active' => false]);

        $announcement = Announcement::create([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'is_active' => true,
        ]);

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
