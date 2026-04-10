<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Holiday;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->filled('year') ? $request->year : date('Y');
        $month = $request->filled('month') ? $request->month : date('m');

        // Get available years
        $years = Attendance::where('user_id', Auth::id())
            ->selectRaw('DISTINCT YEAR(attendance_date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (empty($years)) {
            $years = [date('Y')];
        }

        // Monthly statistics for current year
        $monthlyStats = $this->getMonthlyStats($year);

        // Daily statistics for selected month
        $dailyStats = $this->getDailyStats($year, $month);

        // Leave statistics
        $leaveStats = $this->getLeaveStats($year);

        // Summary statistics
        $summary = $this->getSummaryStats($year);

        // Comparison with previous month
        $comparison = $this->getComparisonStats($year, $month);

        // Attendance trends
        $trends = $this->getAttendanceTrends();

        // Best and worst months
        $bestMonth = $this->getBestMonth($year);
        $worstMonth = $this->getWorstMonth($year);

        // Punch time analysis
        $punchTimeAnalysis = $this->getPunchTimeAnalysis($year, $month);

        return view('employee.statistics.index', compact(
            'year',
            'month',
            'years',
            'monthlyStats',
            'dailyStats',
            'leaveStats',
            'summary',
            'comparison',
            'trends',
            'bestMonth',
            'worstMonth',
            'punchTimeAnalysis'
        ));
    }

    private function getMonthlyStats($year)
    {
        $stats = [];
        $months = range(1, 12);

        foreach ($months as $month) {
            $attendance = Attendance::where('user_id', Auth::id())
                ->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $month)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late,
                    SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent,
                    SUM(late_minutes) as total_late_minutes,
                    AVG(late_minutes) as avg_late_minutes
                ')
                ->first();

            // Get working days in month (excluding Sundays and holidays)
            $workingDays = $this->countWorkingDays($year, $month);

            $stats[$month] = [
                'month' => $month,
                'month_name' => Carbon::create($year, $month, 1)->format('F'),
                'total' => $attendance->total ?? 0,
                'present' => $attendance->present ?? 0,
                'late' => $attendance->late ?? 0,
                'absent' => $attendance->absent ?? 0,
                'attendance_rate' => $workingDays > 0 ? round((($attendance->total ?? 0) / $workingDays) * 100, 1) : 0,
                'punctuality_rate' => ($attendance->total ?? 0) > 0 ? round((($attendance->present ?? 0) / ($attendance->total ?? 0)) * 100, 1) : 0,
                'total_late_minutes' => $attendance->total_late_minutes ?? 0,
                'avg_late_minutes' => round($attendance->avg_late_minutes ?? 0, 1),
                'working_days' => $workingDays,
            ];
        }

        return $stats;
    }

    private function getDailyStats($year, $month)
    {
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $stats = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $attendance = Attendance::where('user_id', Auth::id())
                ->whereDate('attendance_date', $date)
                ->first();

            $isHoliday = Holiday::isHoliday($date);
            $schedule = WorkSchedule::getScheduleByDate($date);
            $isWorkingDay = $schedule && $schedule->is_working_day && !$isHoliday;

            $stats[$day] = [
                'date' => $date->format('Y-m-d'),
                'day' => $day,
                'day_name' => $date->format('l'),
                'is_holiday' => $isHoliday,
                'is_working_day' => $isWorkingDay,
                'status' => $attendance ? $attendance->status : ($isWorkingDay ? 'absent' : 'holiday'),
                'check_in_time' => $attendance ? $attendance->check_in_time : null,
                'check_out_time' => $attendance ? $attendance->check_out_time : null,
                'late_minutes' => $attendance ? $attendance->late_minutes : 0,
                'early_checkout_minutes' => $attendance ? $attendance->early_checkout_minutes : 0,
            ];
        }

        return $stats;
    }

    private function getLeaveStats($year)
    {
        $leaves = Leave::where('user_id', Auth::id())
            ->whereYear('start_date', $year)
            ->where('status', 'approved')
            ->get();

        $stats = [
            'total_days' => $leaves->sum('total_days'),
            'by_type' => [
                'annual' => $leaves->where('leave_type', 'annual')->sum('total_days'),
                'sick' => $leaves->where('leave_type', 'sick')->sum('total_days'),
                'personal' => $leaves->where('leave_type', 'personal')->sum('total_days'),
                'emergency' => $leaves->where('leave_type', 'emergency')->sum('total_days'),
                'maternity' => $leaves->where('leave_type', 'maternity')->sum('total_days'),
                'other' => $leaves->where('leave_type', 'other')->sum('total_days'),
            ],
            'pending' => Leave::where('user_id', Auth::id())
                ->where('status', 'pending')
                ->count(),
        ];

        return $stats;
    }

    private function getSummaryStats($year)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereYear('attendance_date', $year)
            ->selectRaw('
                COUNT(*) as total_attendance,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as total_present,
                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as total_late,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as total_absent,
                SUM(late_minutes) as total_late_minutes,
                SUM(early_checkout_minutes) as total_early_checkout,
                MIN(check_in_time) as earliest_check_in,
                MAX(check_out_time) as latest_check_out
            ')
            ->first();

        $totalWorkingDays = $this->countTotalWorkingDays($year);

        return [
            'total_attendance' => $attendance->total_attendance ?? 0,
            'total_present' => $attendance->total_present ?? 0,
            'total_late' => $attendance->total_late ?? 0,
            'total_absent' => $attendance->total_absent ?? 0,
            'total_late_minutes' => $attendance->total_late_minutes ?? 0,
            'total_early_checkout' => $attendance->total_early_checkout ?? 0,
            'earliest_check_in' => $attendance->earliest_check_in,
            'latest_check_out' => $attendance->latest_check_out,
            'attendance_rate' => $totalWorkingDays > 0 ? round((($attendance->total_attendance ?? 0) / $totalWorkingDays) * 100, 1) : 0,
            'punctuality_rate' => ($attendance->total_attendance ?? 0) > 0 ? round((($attendance->total_present ?? 0) / ($attendance->total_attendance ?? 0)) * 100, 1) : 0,
            'total_working_days' => $totalWorkingDays,
        ];
    }

    private function getComparisonStats($year, $month)
    {
        $currentMonth = (int)$month;
        $previousMonth = $currentMonth == 1 ? 12 : $currentMonth - 1;
        $previousYear = $currentMonth == 1 ? $year - 1 : $year;

        $currentStats = $this->getMonthlyStats($year)[$currentMonth] ?? null;
        $previousStats = $this->getMonthlyStats($previousYear)[$previousMonth] ?? null;

        if (!$currentStats) {
            return null;
        }

        return [
            'attendance_rate' => [
                'current' => $currentStats['attendance_rate'],
                'previous' => $previousStats ? $previousStats['attendance_rate'] : 0,
                'change' => $previousStats ? round($currentStats['attendance_rate'] - $previousStats['attendance_rate'], 1) : 0,
            ],
            'punctuality_rate' => [
                'current' => $currentStats['punctuality_rate'],
                'previous' => $previousStats ? $previousStats['punctuality_rate'] : 0,
                'change' => $previousStats ? round($currentStats['punctuality_rate'] - $previousStats['punctuality_rate'], 1) : 0,
            ],
            'total_late_minutes' => [
                'current' => $currentStats['total_late_minutes'],
                'previous' => $previousStats ? $previousStats['total_late_minutes'] : 0,
                'change' => $previousStats ? round($currentStats['total_late_minutes'] - $previousStats['total_late_minutes'], 1) : 0,
            ],
        ];
    }

    private function getAttendanceTrends()
    {
        $last6Months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $stats = $this->getMonthlyStats($date->year)[$date->month] ?? null;

            if ($stats) {
                $last6Months[] = [
                    'month' => $date->format('M Y'),
                    'attendance_rate' => $stats['attendance_rate'],
                    'punctuality_rate' => $stats['punctuality_rate'],
                ];
            }
        }

        return $last6Months;
    }

    private function getBestMonth($year)
    {
        $best = null;
        $months = range(1, 12);

        foreach ($months as $month) {
            $stats = $this->getMonthlyStats($year)[$month] ?? null;
            if ($stats && $stats['attendance_rate'] > 0) {
                if (!$best || $stats['attendance_rate'] > $best['attendance_rate']) {
                    $best = [
                        'month_name' => $stats['month_name'],
                        'attendance_rate' => $stats['attendance_rate'],
                        'present' => $stats['present'],
                        'total' => $stats['total'],
                    ];
                }
            }
        }

        return $best;
    }

    private function getWorstMonth($year)
    {
        $worst = null;
        $months = range(1, 12);

        foreach ($months as $month) {
            $stats = $this->getMonthlyStats($year)[$month] ?? null;
            if ($stats && $stats['total'] > 0) {
                if (!$worst || $stats['attendance_rate'] < $worst['attendance_rate']) {
                    $worst = [
                        'month_name' => $stats['month_name'],
                        'attendance_rate' => $stats['attendance_rate'],
                        'present' => $stats['present'],
                        'total' => $stats['total'],
                    ];
                }
            }
        }

        return $worst;
    }

    private function getPunchTimeAnalysis($year, $month)
    {
        $attendances = Attendance::where('user_id', Auth::id())
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->get();

        if ($attendances->isEmpty()) {
            return null;
        }

        $checkInTimes = [];
        $checkOutTimes = [];

        foreach ($attendances as $attendance) {
            if ($attendance->check_in_time) {
                $hour = (int)substr($attendance->check_in_time, 0, 2);
                $minute = (int)substr($attendance->check_in_time, 3, 2);
                $checkInTimes[] = $hour + ($minute / 60);
            }
            if ($attendance->check_out_time) {
                $hour = (int)substr($attendance->check_out_time, 0, 2);
                $minute = (int)substr($attendance->check_out_time, 3, 2);
                $checkOutTimes[] = $hour + ($minute / 60);
            }
        }

        return [
            'avg_check_in' => !empty($checkInTimes) ? round(array_sum($checkInTimes) / count($checkInTimes), 2) : null,
            'avg_check_out' => !empty($checkOutTimes) ? round(array_sum($checkOutTimes) / count($checkOutTimes), 2) : null,
            'earliest_check_in' => !empty($checkInTimes) ? min($checkInTimes) : null,
            'latest_check_out' => !empty($checkOutTimes) ? max($checkOutTimes) : null,
        ];
    }

    private function countWorkingDays($year, $month)
    {
        $date = Carbon::create($year, $month, 1);
        $daysInMonth = $date->daysInMonth;
        $workingDays = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = Carbon::create($year, $month, $day);
            $isHoliday = Holiday::isHoliday($currentDate);
            $schedule = WorkSchedule::getScheduleByDate($currentDate);

            if ($schedule && $schedule->is_working_day && !$isHoliday) {
                $workingDays++;
            }
        }

        return $workingDays;
    }

    private function countTotalWorkingDays($year)
    {
        $total = 0;
        for ($month = 1; $month <= 12; $month++) {
            $total += $this->countWorkingDays($year, $month);
        }
        return $total;
    }

    public function export(Request $request)
    {
        $year = $request->filled('year') ? $request->year : date('Y');

        $monthlyStats = $this->getMonthlyStats($year);
        $summary = $this->getSummaryStats($year);
        $leaveStats = $this->getLeaveStats($year);

        $filename = "statistik_kehadiran_{$year}.csv";

        $handle = fopen('php://temp', 'w');
        fwrite($handle, "\xEF\xBB\xBF");

        // Header
        fputcsv($handle, ['STATISTIK KEHADIRAN TAHUN ' . $year]);
        fputcsv($handle, []);
        fputcsv($handle, ['RINGKASAN TAHUNAN']);
        fputcsv($handle, ['Total Kehadiran', $summary['total_attendance']]);
        fputcsv($handle, ['Tepat Waktu', $summary['total_present']]);
        fputcsv($handle, ['Terlambat', $summary['total_late']]);
        fputcsv($handle, ['Tidak Hadir', $summary['total_absent']]);
        fputcsv($handle, ['Total Keterlambatan', $summary['total_late_minutes'] . ' menit']);
        fputcsv($handle, ['Tingkat Kehadiran', $summary['attendance_rate'] . '%']);
        fputcsv($handle, ['Tingkat Ketepatan Waktu', $summary['punctuality_rate'] . '%']);
        fputcsv($handle, []);

        fputcsv($handle, ['STATISTIK PER BULAN']);
        fputcsv($handle, ['Bulan', 'Hari Kerja', 'Hadir', 'Terlambat', 'Tidak Hadir', 'Tingkat Kehadiran', 'Total Terlambat']);

        foreach ($monthlyStats as $stat) {
            fputcsv($handle, [
                $stat['month_name'],
                $stat['working_days'],
                $stat['present'],
                $stat['late'],
                $stat['absent'],
                $stat['attendance_rate'] . '%',
                $stat['total_late_minutes'] . ' menit'
            ]);
        }

        fputcsv($handle, []);
        fputcsv($handle, ['STATISTIK CUTI']);
        fputcsv($handle, ['Total Cuti Disetujui', $leaveStats['total_days'] . ' hari']);
        fputcsv($handle, ['Cuti Tahunan', $leaveStats['by_type']['annual'] . ' hari']);
        fputcsv($handle, ['Sakit', $leaveStats['by_type']['sick'] . ' hari']);
        fputcsv($handle, ['Keperluan Pribadi', $leaveStats['by_type']['personal'] . ' hari']);

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
