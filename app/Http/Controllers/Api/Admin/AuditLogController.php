<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditLogController extends BaseController
{
    /**
     * Display a listing of audit logs.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = AuditLog::query()->with(['user', 'auditable']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('event', $request->action);
        }

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action_description', 'like', "%{$search}%")
                    ->orWhere('old_values', 'like', "%{$search}%")
                    ->orWhere('new_values', 'like', "%{$search}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        $logs = $query->paginate($request->input('per_page', 15));

        return AuditLogResource::collection($logs);
    }

    /**
     * Display the specified audit log.
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        return $this->sendResponse(
            new AuditLogResource($auditLog->load(['user', 'auditable'])),
            'Journal d\'audit récupéré avec succès.'
        );
    }

    /**
     * Get audit logs for a specific resource.
     */
    public function forResource(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'auditable_type' => 'required|string',
            'auditable_id' => 'required|integer',
        ]);

        $logs = AuditLog::where('auditable_type', $request->auditable_type)
            ->where('auditable_id', $request->auditable_id)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return AuditLogResource::collection($logs);
    }

    /**
     * Get audit logs for a specific user.
     */
    public function forUser(Request $request, int $userId): AnonymousResourceCollection
    {
        $logs = AuditLog::where('user_id', $userId)
            ->with(['auditable'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return AuditLogResource::collection($logs);
    }

    /**
     * Get summary of audit log actions.
     */
    public function summary(Request $request): JsonResponse
    {
        $period = $request->input('period', 30);
        $startDate = now()->subDays($period);

        $totalLogs = AuditLog::where('created_at', '>=', $startDate)->count();

        $byAction = AuditLog::where('created_at', '>=', $startDate)
            ->selectRaw('event, COUNT(*) as count')
            ->groupBy('event')
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count', 'event');

        $byType = AuditLog::where('created_at', '>=', $startDate)
            ->selectRaw('auditable_type, COUNT(*) as count')
            ->groupBy('auditable_type')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => class_basename($item->auditable_type),
                    'count' => $item->count,
                ];
            });

        $mostActiveUsers = AuditLog::where('created_at', '>=', $startDate)
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->with('user:id,first_name,last_name,email')
            ->get()
            ->map(function ($item) {
                return [
                    'user_id' => $item->user_id,
                    'user_name' => $item->user ? $item->user->first_name.' '.$item->user->last_name : 'Inconnu',
                    'user_email' => $item->user->email ?? null,
                    'actions_count' => $item->count,
                ];
            });

        $recentActivity = AuditLog::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return $this->sendResponse([
            'total_logs' => $totalLogs,
            'period_days' => $period,
            'by_action' => $byAction,
            'by_type' => $byType,
            'most_active_users' => $mostActiveUsers,
            'recent_activity' => $recentActivity,
        ], 'Résumé des journaux d\'audit récupéré avec succès.');
    }

    /**
     * Delete old audit logs (cleanup).
     */
    public function cleanup(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'required|integer|min:90',
        ]);

        $date = now()->subDays($request->days);

        $deleted = AuditLog::where('created_at', '<', $date)->delete();

        return $this->sendResponse([
            'deleted_count' => $deleted,
            'cutoff_date' => $date->toDateString(),
        ], 'Journaux d\'audit nettoyés avec succès.');
    }
}
