<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Total employees
        $totalEmployees = User::where('role', 'employee')->count();

        // Today's attendance
        $totalPresentToday = Attendance::whereDate('attendance_date', today())->count();

        // Pending leaves
        $totalPendingLeaves = Leave::where('status', 'pending')->count();

        // Late today
        $totalLateToday = Attendance::whereDate('attendance_date', today())
            ->where('late_minutes', '>', 0)
            ->count();

        // Attendance stats for last 7 days
        $attendanceStats = Attendance::select(
            DB::raw('DATE(attendance_date) as date'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present'),
            DB::raw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late')
        )
            ->where('attendance_date', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Recent attendances
        $recentAttendances = Attendance::with('user')
            ->orderBy('attendance_date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalEmployees',
            'totalPresentToday',
            'totalPendingLeaves',
            'totalLateToday',
            'attendanceStats',
            'recentAttendances'
        ));
    }
}
