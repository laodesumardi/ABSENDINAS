<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        // Set default date range to current month
        $startDate = $request->filled('start_date') ? $request->start_date : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : Carbon::now()->format('Y-m-d');
        $department = $request->filled('department') ? $request->department : null;
        $userId = $request->filled('user_id') ? $request->user_id : null;

        // Get all employees
        $employees = User::where('role', 'employee')
            ->when($department, function ($q) use ($department) {
                return $q->where('department', $department);
            })
            ->orderBy('name')
            ->get();

        // Get unique departments for filter
        $departments = User::where('role', 'employee')
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department');

        // Build attendance report
        $reportData = [];
        $totalStats = [
            'total_days' => 0,
            'total_present' => 0,
            'total_late' => 0,
            'total_absent' => 0,
            'total_half_day' => 0,
            'total_late_minutes' => 0,
        ];

        foreach ($employees as $employee) {
            if ($userId && $userId != $employee->id) {
                continue;
            }

            // Get attendance for date range
            $attendances = Attendance::where('user_id', $employee->id)
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->get();

            // Calculate working days in period (excluding Sundays and holidays)
            $workingDays = $this->countWorkingDays($startDate, $endDate);

            $stats = [
                'user_id' => $employee->id,
                'name' => $employee->name,
                'employee_id' => $employee->employee_id,
                'department' => $employee->department,
                'position' => $employee->position,
                'working_days' => $workingDays,
                'total_attendance' => $attendances->count(),
                'present' => $attendances->where('status', 'present')->count(),
                'late' => $attendances->where('status', 'late')->count(),
                'absent' => $attendances->where('status', 'absent')->count(),
                'half_day' => $attendances->where('status', 'half_day')->count(),
                'late_minutes' => $attendances->sum('late_minutes'),
                'attendance_rate' => $workingDays > 0 ? round(($attendances->count() / $workingDays) * 100, 1) : 0,
                'punctuality_rate' => $attendances->count() > 0 ? round((($attendances->where('status', 'present')->count()) / $attendances->count()) * 100, 1) : 0,
            ];

            $reportData[] = $stats;

            // Update total stats
            $totalStats['total_days'] += $workingDays;
            $totalStats['total_present'] += $stats['present'];
            $totalStats['total_late'] += $stats['late'];
            $totalStats['total_absent'] += $stats['absent'];
            $totalStats['total_half_day'] += $stats['half_day'];
            $totalStats['total_late_minutes'] += $stats['late_minutes'];
        }

        // Summary statistics
        $summary = [
            'total_employees' => $employees->count(),
            'total_working_days' => $this->countWorkingDays($startDate, $endDate),
            'total_attendance' => collect($reportData)->sum('total_attendance'),
            'total_present' => collect($reportData)->sum('present'),
            'total_late' => collect($reportData)->sum('late'),
            'total_absent' => collect($reportData)->sum('absent'),
            'total_half_day' => collect($reportData)->sum('half_day'),
            'total_late_minutes' => collect($reportData)->sum('late_minutes'),
            'overall_attendance_rate' => $totalStats['total_days'] > 0 ? round((collect($reportData)->sum('total_attendance') / $totalStats['total_days']) * 100, 1) : 0,
            'overall_punctuality_rate' => collect($reportData)->sum('total_attendance') > 0 ? round((collect($reportData)->sum('present') / collect($reportData)->sum('total_attendance')) * 100, 1) : 0,
        ];

        // Daily attendance chart
        $dailyChart = $this->getDailyChartData($startDate, $endDate, $department, $userId);

        // Department statistics
        $deptStats = $this->getDepartmentStats($startDate, $endDate);

        // Top performers
        $topPerformers = collect($reportData)->sortByDesc('attendance_rate')->take(5);
        $worstPerformers = collect($reportData)->where('total_attendance', '>', 0)->sortBy('attendance_rate')->take(5);

        return view('operator.reports.attendance', compact(
            'reportData',
            'summary',
            'departments',
            'startDate',
            'endDate',
            'department',
            'userId',
            'dailyChart',
            'deptStats',
            'topPerformers',
            'worstPerformers'
        ));
    }

    public function export(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->start_date : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : Carbon::now()->format('Y-m-d');
        $department = $request->filled('department') ? $request->department : null;

        $employees = User::where('role', 'employee')
            ->when($department, function ($q) use ($department) {
                return $q->where('department', $department);
            })
            ->orderBy('name')
            ->get();

        $filename = "rekap_absensi_" . Carbon::parse($startDate)->format('d-m-Y') . "_sd_" . Carbon::parse($endDate)->format('d-m-Y') . ".csv";

        $handle = fopen('php://temp', 'w');
        fwrite($handle, "\xEF\xBB\xBF");

        // Headers
        fputcsv($handle, [
            'No',
            'ID Pegawai',
            'Nama',
            'Departemen',
            'Posisi',
            'Hari Kerja',
            'Hadir',
            'Terlambat',
            'Setengah Hari',
            'Tidak Hadir',
            'Total Kehadiran',
            'Tingkat Kehadiran (%)',
            'Ketepatan Waktu (%)',
            'Total Keterlambatan (menit)'
        ]);

        $no = 1;
        foreach ($employees as $employee) {
            $attendances = Attendance::where('user_id', $employee->id)
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->get();

            $workingDays = $this->countWorkingDays($startDate, $endDate);
            $totalAttendance = $attendances->count();

            fputcsv($handle, [
                $no++,
                $employee->employee_id ?? '-',
                $employee->name,
                $employee->department ?? '-',
                $employee->position ?? '-',
                $workingDays,
                $attendances->where('status', 'present')->count(),
                $attendances->where('status', 'late')->count(),
                $attendances->where('status', 'half_day')->count(),
                $attendances->where('status', 'absent')->count(),
                $totalAttendance,
                $workingDays > 0 ? round(($totalAttendance / $workingDays) * 100, 1) : 0,
                $totalAttendance > 0 ? round((($attendances->where('status', 'present')->count()) / $totalAttendance) * 100, 1) : 0,
                $attendances->sum('late_minutes')
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

    public function print(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->start_date : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : Carbon::now()->format('Y-m-d');
        $department = $request->filled('department') ? $request->department : null;

        $employees = User::where('role', 'employee')
            ->when($department, function ($q) use ($department) {
                return $q->where('department', $department);
            })
            ->orderBy('name')
            ->get();

        $reportData = [];
        foreach ($employees as $employee) {
            $attendances = Attendance::where('user_id', $employee->id)
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->get();

            $workingDays = $this->countWorkingDays($startDate, $endDate);

            $reportData[] = [
                'name' => $employee->name,
                'employee_id' => $employee->employee_id,
                'department' => $employee->department,
                'working_days' => $workingDays,
                'present' => $attendances->where('status', 'present')->count(),
                'late' => $attendances->where('status', 'late')->count(),
                'absent' => $attendances->where('status', 'absent')->count(),
                'half_day' => $attendances->where('status', 'half_day')->count(),
                'attendance_rate' => $workingDays > 0 ? round(($attendances->count() / $workingDays) * 100, 1) : 0,
            ];
        }

        return view('operator.reports.print', compact('reportData', 'startDate', 'endDate', 'department'));
    }

    private function countWorkingDays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $workingDays = 0;

        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $isHoliday = Holiday::isHoliday($date);
            $schedule = WorkSchedule::getScheduleByDate($date);

            if ($schedule && $schedule->is_working_day && !$isHoliday) {
                $workingDays++;
            }
        }

        return $workingDays;
    }

    private function getDailyChartData($startDate, $endDate, $department = null, $userId = null)
    {
        $query = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->when($userId, function ($q) use ($userId) {
                return $q->where('user_id', $userId);
            })
            ->when($department, function ($q) use ($department) {
                return $q->whereHas('user', function ($sub) use ($department) {
                    $sub->where('department', $department);
                });
            });

        $dailyData = $query->select(
            DB::raw('DATE(attendance_date) as date'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present'),
            DB::raw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late'),
            DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent')
        )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $dailyData;
    }

    private function getDepartmentStats($startDate, $endDate)
    {
        $departments = User::where('role', 'employee')
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department');

        $stats = [];
        foreach ($departments as $dept) {
            $employees = User::where('role', 'employee')->where('department', $dept)->get();
            $totalAttendance = 0;
            $totalPresent = 0;
            $totalLate = 0;
            $totalWorkingDays = 0;

            foreach ($employees as $employee) {
                $attendances = Attendance::where('user_id', $employee->id)
                    ->whereBetween('attendance_date', [$startDate, $endDate])
                    ->get();

                $workingDays = $this->countWorkingDays($startDate, $endDate);
                $totalWorkingDays += $workingDays;
                $totalAttendance += $attendances->count();
                $totalPresent += $attendances->where('status', 'present')->count();
                $totalLate += $attendances->where('status', 'late')->count();
            }

            $stats[] = [
                'department' => $dept,
                'employee_count' => $employees->count(),
                'attendance_rate' => $totalWorkingDays > 0 ? round(($totalAttendance / $totalWorkingDays) * 100, 1) : 0,
                'punctuality_rate' => $totalAttendance > 0 ? round(($totalPresent / $totalAttendance) * 100, 1) : 0,
                'total_late' => $totalLate,
            ];
        }

        return collect($stats)->sortByDesc('attendance_rate');
    }
}
