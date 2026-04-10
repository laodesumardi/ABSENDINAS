<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\WorkLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        // Get filter values
        $startDate = $request->filled('start_date') ? $request->start_date : now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : now()->format('Y-m-d');
        $userId = $request->filled('user_id') ? $request->user_id : null;
        $status = $request->filled('status') ? $request->status : null;

        // Build query
        $query = Attendance::with('user')
            ->whereBetween('attendance_date', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->paginate(20)
            ->appends($request->all());

        // Get summary statistics
        $summaryQuery = Attendance::whereBetween('attendance_date', [$startDate, $endDate]);
        if ($userId) {
            $summaryQuery->where('user_id', $userId);
        }

        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'present' => (clone $summaryQuery)->where('status', 'present')->count(),
            'late' => (clone $summaryQuery)->where('status', 'late')->count(),
            'absent' => (clone $summaryQuery)->where('status', 'absent')->count(),
            'half_day' => (clone $summaryQuery)->where('status', 'half_day')->count(),
            'total_late_minutes' => (clone $summaryQuery)->sum('late_minutes'),
            'average_check_in' => (clone $summaryQuery)->avg(DB::raw("TIME_TO_SEC(check_in_time)"))
                ? date('H:i:s', (clone $summaryQuery)->avg(DB::raw("TIME_TO_SEC(check_in_time)")))
                : '00:00:00',
        ];

        // Get daily statistics for chart
        $dailyStats = Attendance::select(
            DB::raw('DATE(attendance_date) as date'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present'),
            DB::raw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late'),
            DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent')
        )
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->when($userId, function ($q) use ($userId) {
                return $q->where('user_id', $userId);
            })
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Get users for filter
        $users = User::where('role', 'employee')
            ->orderBy('name')
            ->get();

        // Get status counts for pie chart
        $statusCounts = [
            'labels' => ['Hadir', 'Terlambat', 'Tidak Hadir', 'Setengah Hari'],
            'data' => [
                $summary['present'],
                $summary['late'],
                $summary['absent'],
                $summary['half_day']
            ],
            'colors' => ['#06d6a0', '#ffd166', '#ef476f', '#118ab2']
        ];

        return view('admin.reports.attendance', compact(
            'attendances',
            'summary',
            'dailyStats',
            'users',
            'startDate',
            'endDate',
            'userId',
            'status',
            'statusCounts'
        ));
    }

    public function export(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->start_date : now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : now()->format('Y-m-d');
        $userId = $request->filled('user_id') ? $request->user_id : null;
        $status = $request->filled('status') ? $request->status : null;

        $query = Attendance::with('user')
            ->whereBetween('attendance_date', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->get();

        $filename = "attendance_report_{$startDate}_to_{$endDate}.csv";

        $handle = fopen('php://temp', 'w');

        // Add UTF-8 BOM for Excel compatibility
        fwrite($handle, "\xEF\xBB\xBF");

        // Headers
        fputcsv($handle, [
            'No',
            'Tanggal',
            'Nama Pegawai',
            'ID Pegawai',
            'Departemen',
            'Check In',
            'Check Out',
            'Status',
            'Terlambat (menit)',
            'Durasi Kerja',
            'Catatan'
        ]);

        // Data
        $no = 1;
        foreach ($attendances as $attendance) {
            $duration = '';
            if ($attendance->check_in_time && $attendance->check_out_time) {
                $checkIn = strtotime($attendance->check_in_time);
                $checkOut = strtotime($attendance->check_out_time);
                $diff = $checkOut - $checkIn;
                $duration = gmdate('H:i', $diff);
            }

            fputcsv($handle, [
                $no++,
                $attendance->attendance_date->format('d/m/Y'),
                $attendance->user->name,
                $attendance->user->employee_id ?? '-',
                $attendance->user->department ?? '-',
                $attendance->check_in_time,
                $attendance->check_out_time ?? '-',
                $this->getStatusText($attendance->status),
                $attendance->late_minutes,
                $duration,
                $attendance->notes ?? '-'
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

    public function exportPdf(Request $request)
    {
        // This would require a PDF package like barryvdh/laravel-dompdf
        // For now, we'll redirect back
        return redirect()->back()->with('info', 'Fitur PDF akan segera tersedia');
    }

    public function print(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->start_date : now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : now()->format('Y-m-d');
        $userId = $request->filled('user_id') ? $request->user_id : null;

        $query = Attendance::with('user')
            ->whereBetween('attendance_date', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->get();

        $selectedUser = $userId ? User::find($userId) : null;

        return view('admin.reports.print', compact('attendances', 'startDate', 'endDate', 'selectedUser'));
    }

    private function getStatusText($status)
    {
        $statuses = [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'half_day' => 'Setengah Hari'
        ];

        return $statuses[$status] ?? $status;
    }
}
