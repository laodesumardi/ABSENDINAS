<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'employee');

        // Search by name or employee_id
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status == 'active');
        }

        $employees = $query->orderBy('name')->paginate(15);

        // Get unique departments for filter
        $departments = User::where('role', 'employee')
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department');

        // Statistics
        $stats = [
            'total' => User::where('role', 'employee')->count(),
            'active' => User::where('role', 'employee')->where('is_active', true)->count(),
            'inactive' => User::where('role', 'employee')->where('is_active', false)->count(),
            'total_departments' => User::where('role', 'employee')->whereNotNull('department')->distinct()->count('department'),
        ];

        return view('operator.employees.index', compact('employees', 'stats', 'departments'));
    }

    public function show(User $employee)
    {
        // Ensure only employee role can be viewed
        if ($employee->role !== 'employee') {
            abort(404);
        }

        // Get attendance statistics for current month
        $currentMonthStats = Attendance::where('user_id', $employee->id)
            ->whereYear('attendance_date', now()->year)
            ->whereMonth('attendance_date', now()->month)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent,
                SUM(late_minutes) as total_late_minutes
            ')
            ->first();

        // Get attendance statistics for current year
        $yearlyStats = Attendance::where('user_id', $employee->id)
            ->whereYear('attendance_date', now()->year)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late,
                SUM(late_minutes) as total_late_minutes
            ')
            ->first();

        // Get leave statistics
        $leaveStats = [
            'total_days' => Leave::where('user_id', $employee->id)
                ->where('status', 'approved')
                ->sum('total_days'),
            'pending' => Leave::where('user_id', $employee->id)
                ->where('status', 'pending')
                ->count(),
            'approved' => Leave::where('user_id', $employee->id)
                ->where('status', 'approved')
                ->count(),
            'rejected' => Leave::where('user_id', $employee->id)
                ->where('status', 'rejected')
                ->count(),
        ];

        // Get recent attendances
        $recentAttendances = Attendance::where('user_id', $employee->id)
            ->orderBy('attendance_date', 'desc')
            ->limit(10)
            ->get();

        // Get monthly attendance chart data
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $stats = Attendance::where('user_id', $employee->id)
                ->whereYear('attendance_date', now()->year)
                ->whereMonth('attendance_date', $i)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late
                ')
                ->first();

            $monthlyData[] = [
                'month' => Carbon::create(now()->year, $i, 1)->format('M'),
                'present' => $stats->present ?? 0,
                'late' => $stats->late ?? 0,
            ];
        }

        return view('operator.employees.show', compact(
            'employee',
            'currentMonthStats',
            'yearlyStats',
            'leaveStats',
            'recentAttendances',
            'monthlyData'
        ));
    }

    public function attendance(User $employee, Request $request)
    {
        // Ensure only employee role
        if ($employee->role !== 'employee') {
            abort(404);
        }

        $query = Attendance::where('user_id', $employee->id);

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('attendance_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('attendance_date', '<=', $request->end_date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->paginate(20);

        // Summary statistics for filtered period
        $summary = Attendance::where('user_id', $employee->id)
            ->when($request->filled('start_date'), function ($q) use ($request) {
                return $q->whereDate('attendance_date', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function ($q) use ($request) {
                return $q->whereDate('attendance_date', '<=', $request->end_date);
            })
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent,
                SUM(late_minutes) as total_late_minutes
            ')
            ->first();

        return view('operator.employees.attendance', compact('employee', 'attendances', 'summary'));
    }

    public function leaves(User $employee, Request $request)
    {
        // Ensure only employee role
        if ($employee->role !== 'employee') {
            abort(404);
        }

        $query = Leave::where('user_id', $employee->id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        $leaves = $query->orderBy('created_at', 'desc')->paginate(15);

        // Statistics
        $stats = [
            'total_days' => Leave::where('user_id', $employee->id)->where('status', 'approved')->sum('total_days'),
            'pending' => Leave::where('user_id', $employee->id)->where('status', 'pending')->count(),
            'approved' => Leave::where('user_id', $employee->id)->where('status', 'approved')->count(),
            'rejected' => Leave::where('user_id', $employee->id)->where('status', 'rejected')->count(),
        ];

        return view('operator.employees.leaves', compact('employee', 'leaves', 'stats'));
    }

    public function export(Request $request)
    {
        $query = User::where('role', 'employee');

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status == 'active');
        }

        $employees = $query->orderBy('name')->get();

        $filename = "data_pegawai_" . now()->format('Y-m-d_H-i-s') . ".csv";

        $handle = fopen('php://temp', 'w');
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'No',
            'ID Pegawai',
            'Nama Lengkap',
            'Email',
            'Posisi',
            'Departemen',
            'Telepon',
            'Alamat',
            'Status',
            'Tanggal Bergabung',
            'Terakhir Login'
        ]);

        $no = 1;
        foreach ($employees as $employee) {
            fputcsv($handle, [
                $no++,
                $employee->employee_id ?? '-',
                $employee->name,
                $employee->email,
                $employee->position ?? '-',
                $employee->department ?? '-',
                $employee->phone ?? '-',
                $employee->address ?? '-',
                $employee->is_active ? 'Aktif' : 'Nonaktif',
                $employee->created_at->format('d/m/Y'),
                $employee->last_login_at ? $employee->last_login_at->format('d/m/Y H:i') : '-'
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
