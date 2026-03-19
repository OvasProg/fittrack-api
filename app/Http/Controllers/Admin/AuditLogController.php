<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index()
    {
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
