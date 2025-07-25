<?php

namespace App\Http\Controllers\System\AuditLog;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use OwenIt\Auditing\Models\Audit;

class AuditLogController extends Controller
{
    /**
     * Display audit log page
     */
    public function index(): Response
    {
        return Inertia::render('AuditLog');
    }

    /**
     * Get audit logs via API
     */
    public function getAudits(Request $request): JsonResponse
    {
        $request->validate([
            'auditable_type' => 'nullable|string',
            'event' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'user_id' => 'nullable|integer|exists:users,id',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:5|max:100',
        ]);

        $query = Audit::with('user')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $perPage = $request->get('per_page', 20);
        $audits = $query->paginate($perPage);

        // Transform the data
        $audits->getCollection()->transform(function ($audit) {
            return [
                'id' => $audit->id,
                'event' => $audit->event,
                'auditable_type' => class_basename($audit->auditable_type),
                'auditable_id' => $audit->auditable_id,
                'user' => $audit->user ? [
                    'id' => $audit->user->id,
                    'name' => $audit->user->name,
                    'email' => $audit->user->email,
                ] : null,
                'old_values' => $audit->old_values,
                'new_values' => $audit->new_values,
                'url' => $audit->url,
                'ip_address' => $audit->ip_address,
                'user_agent' => $audit->user_agent,
                'created_at' => $audit->created_at->format('Y-m-d H:i:s'),
                'created_at_human' => $audit->created_at->diffForHumans(),
            ];
        });

        return response()->json($audits);
    }

    /**
     * Get audit statistics
     */
    public function getStats(): JsonResponse
    {
        $stats = [
            'total_audits' => Audit::count(),
            'today_audits' => Audit::whereDate('created_at', today())->count(),
            'this_week_audits' => Audit::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])->count(),
            'this_month_audits' => Audit::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'events_breakdown' => Audit::select('event')
                ->selectRaw('count(*) as count')
                ->groupBy('event')
                ->orderBy('count', 'desc')
                ->get()
                ->keyBy('event')
                ->map->count,
            'models_breakdown' => Audit::select('auditable_type')
                ->selectRaw('count(*) as count')
                ->groupBy('auditable_type')
                ->orderBy('count', 'desc')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [class_basename($item->auditable_type) => $item->count];
                }),
            'recent_activities' => Audit::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($audit) {
                    return [
                        'event' => $audit->event,
                        'model' => class_basename($audit->auditable_type),
                        'user' => $audit->user?->name ?? 'System',
                        'created_at' => $audit->created_at->diffForHumans(),
                    ];
                }),
        ];

        return response()->json($stats);
    }

    /**
     * Export audit logs
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'format' => 'required|in:csv,json,excel',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'filters' => 'nullable|array',
        ]);

        // This would typically generate and return a download link
        // Implementation depends on your export requirements

        return response()->json([
            'message' => 'Export functionality not yet implemented',
            'download_url' => null,
        ]);
    }
}
