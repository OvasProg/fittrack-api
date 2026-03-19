<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
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

        $formattedLogs = $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'admin' => $log->admin,
                'action' => $log->action,
                'details' => json_decode($log->details),
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                'human_readable_time' => $log->created_at->diffForHumans(),
            ];
        });

        return response()->json($formattedLogs, 200);
    }
}
