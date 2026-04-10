<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get statistics
        $totalLogs = ActivityLog::count();
        $todayLogs = ActivityLog::whereDate('created_at', today())->count();
        $uniqueUsers = ActivityLog::distinct('user_id')->count('user_id');

        // Get actions for filter
        $actions = ActivityLog::distinct('action')->pluck('action');

        // Get users for filter
        $users = User::orderBy('name')->get();

        // Get activity by hour for chart
        $activityByHour = ActivityLog::select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('COUNT(*) as total')
        )
            ->whereDate('created_at', today())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Get activity by action for chart
        $activityByAction = ActivityLog::select(
            'action',
            DB::raw('COUNT(*) as total')
        )
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('action')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Get recent activities for dashboard
        $recentActivities = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.activity.index', compact(
            'logs',
            'totalLogs',
            'todayLogs',
            'uniqueUsers',
            'actions',
            'users',
            'activityByHour',
            'activityByAction',
            'recentActivities'
        ));
    }

    public function show(ActivityLog $log)
    {
        return view('admin.activity.show', compact('log'));
    }

    public function destroy(ActivityLog $log)
    {
        $log->delete();

        return redirect()->route('admin.activity.index')
            ->with('success', 'Log aktivitas berhasil dihapus!');
    }

    public function clearOld()
    {
        $deleted = ActivityLog::where('created_at', '<', now()->subDays(30))->delete();

        return redirect()->route('admin.activity.index')
            ->with('success', "Berhasil menghapus {$deleted} log aktivitas lama!");
    }

    public function clearAll()
    {
        ActivityLog::truncate();

        return redirect()->route('admin.activity.index')
            ->with('success', 'Semua log aktivitas berhasil dihapus!');
    }

    public function export(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $filename = "activity_logs_" . now()->format('Y-m-d_H-i-s') . ".csv";

        $handle = fopen('php://temp', 'w');

        // Add UTF-8 BOM
        fwrite($handle, "\xEF\xBB\xBF");

        // Headers
        fputcsv($handle, [
            'No',
            'Waktu',
            'User',
            'Aksi',
            'Deskripsi',
            'IP Address',
            'Device',
            'Browser',
            'OS'
        ]);

        // Data
        $no = 1;
        foreach ($logs as $log) {
            fputcsv($handle, [
                $no++,
                $log->formatted_date,
                $log->user->name ?? 'System',
                $log->action_label,
                $log->description,
                $log->ip_address ?? '-',
                $log->user_agent ?? '-',
                $log->getBrowser(),
                $log->getOperatingSystem()
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
