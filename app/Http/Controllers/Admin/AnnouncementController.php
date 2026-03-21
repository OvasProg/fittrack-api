<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Services\AnnouncementService;
use Illuminate\Http\JsonResponse;

/**
 * Global broadcast system for FitTrack admins.
 * * This controller manages the single active announcement
 * that appears on all user dashboards, useful for
 * maintenance alerts or new feature updates.
 */
class AnnouncementController extends Controller
{
    public function __construct(private AnnouncementService $announcementService) {}

    public function store(StoreAnnouncementRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $admin = $request->user();

        $announcement = $this->announcementService->broadcastAnnouncement($admin, $validated);

        return response()->json([
            'message' => 'Announcement broadcasted successfully!',
            'announcement' => $announcement,
        ], 201);
    }
}
