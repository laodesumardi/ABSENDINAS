<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEmployees = User::where('role', 'employee')->count();
        $todayAttendance = Attendance::whereDate('attendance_date', today())->count();
        $pendingLeaves = Leave::where('status', 'pending')->count();
        $recentAttendances = Attendance::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('operator.dashboard', compact(
            'totalEmployees',
            'todayAttendance',
            'pendingLeaves',
            'recentAttendances'
        ));
    }
}
