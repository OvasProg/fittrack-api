<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Http\Resources\AuditLogResource;
use Illuminate\Http\JsonResponse;

/**
 * The transparency layer for all administrative actions.
 *
 * This controller provides a chronological feed of sensitive
 * changes made by admins, such as role updates or content
 * deletions, ensuring full accountability within the team.
 */
class AuditLogController extends Controller
{
    public function index(): JsonResponse
    {
        // We eager load the 'admin' relationship to avoid the
        // "N+1" problem, ensuring we get all names in a single query.
        $logs = AuditLog::with('admin')->latest()->get();

        return response()->json(AuditLogResource::collection($logs), 200);
    }
}
