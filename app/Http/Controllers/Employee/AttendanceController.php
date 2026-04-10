<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\WorkLocation;
use App\Models\WorkSchedule;
use App\Models\Holiday;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function index()
    {
        $today = now()->format('Y-m-d');
        $todayAttendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', $today)
            ->first();

        $isHoliday = Holiday::isHoliday($today);
        $holidayInfo = $isHoliday ? Holiday::getHolidayByDate($today) : null;

        $schedule = WorkSchedule::getTodaySchedule();

        // Jika schedule tidak ada, buat default untuk testing
        if (!$schedule || !$schedule->check_in_start || $schedule->check_in_start == '00:00:00') {
            $schedule = new \stdClass();
            $schedule->is_working_day = true;
            $schedule->check_in_start = '08:00:00';
            $schedule->check_in_end = '20:00:00';
            $schedule->check_out_start = '17:00:00';
            $schedule->check_out_end = '23:00:00';
        }

        // Untuk testing: selalu bisa check in dan check out
        $canCheckIn = true;
        $canCheckOut = true;

        // Format waktu untuk ditampilkan
        $checkInWindow = date('H:i', strtotime($schedule->check_in_start)) . ' - ' . date('H:i', strtotime($schedule->check_in_end));
        $checkOutWindow = date('H:i', strtotime($schedule->check_out_start)) . ' - ' . date('H:i', strtotime($schedule->check_out_end));

        $start = Carbon::parse($schedule->check_in_start);
        $end = Carbon::parse($schedule->check_out_end);
        $diff = $start->diff($end);
        $workingHours = $diff->format('%h jam %i menit');

        $workLocation = WorkLocation::where('is_active', true)->first();

        $monthStats = Attendance::where('user_id', Auth::id())
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

        if (!$monthStats) {
            $monthStats = (object) [
                'total' => 0,
                'present' => 0,
                'late' => 0,
                'absent' => 0,
                'total_late_minutes' => 0
            ];
        }

        $recentAttendances = Attendance::where('user_id', Auth::id())
            ->orderBy('attendance_date', 'desc')
            ->limit(5)
            ->get();

        return view('employee.attendance.index', compact(
            'todayAttendance',
            'isHoliday',
            'holidayInfo',
            'schedule',
            'canCheckIn',
            'canCheckOut',
            'workLocation',
            'monthStats',
            'recentAttendances',
            'checkInWindow',
            'checkOutWindow',
            'workingHours'
        ));
    }

    public function checkIn(Request $request)
    {
        // Debug log
        Log::info('Check In Request:', $request->all());

        // Check if already checked in today
        $existingAttendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', today())
            ->first();

        if ($existingAttendance) {
            return redirect()->back()->with('error', 'Anda sudah melakukan check in hari ini.');
        }

        // Check if today is holiday
        if (Holiday::isHoliday(today())) {
            return redirect()->back()->with('error', 'Hari ini adalah hari libur, tidak perlu check in.');
        }

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // VALIDASI LOKASI DI AKTIFKAN KEMBALI
        $workLocation = WorkLocation::where('is_active', true)->first();
        if (!$workLocation) {
            return redirect()->back()->with('error', 'Lokasi kerja belum dikonfigurasi. Silakan hubungi admin.');
        }

        $isValidLocation = $workLocation->isWithinRadius(
            $request->latitude,
            $request->longitude
        );

        if (!$isValidLocation) {
            $distance = $workLocation->calculateDistance(
                $request->latitude,
                $request->longitude,
                $workLocation->latitude,
                $workLocation->longitude
            );
            return redirect()->back()->with('error', "Anda berada di luar radius kantor! Jarak: " . round($distance) . " meter (Maksimal: {$workLocation->radius} meter)");
        }

        // Get schedule and check if late
        $schedule = WorkSchedule::getTodaySchedule();
        $checkInTime = now();
        $isLate = false;
        $lateMinutes = 0;

        if ($schedule && $schedule->is_working_day) {
            $checkInEnd = Carbon::parse($schedule->check_in_end);
            if ($checkInTime > $checkInEnd) {
                $isLate = true;
                $lateMinutes = $checkInTime->diffInMinutes($checkInEnd);
            }
        }

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendance-photos/' . date('Y/m'), 'public');
        }

        $attendance = Attendance::create([
            'user_id' => Auth::id(),
            'attendance_date' => today(),
            'check_in_time' => $checkInTime->format('H:i:s'),
            'check_in_latitude' => $request->latitude,
            'check_in_longitude' => $request->longitude,
            'check_in_photo' => $photoPath,
            'status' => $isLate ? 'late' : 'present',
            'late_minutes' => $lateMinutes,
            'notes' => $request->notes,
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'check_in',
            'description' => "Check in pada " . $checkInTime->format('H:i:s') . ($isLate ? " (Terlambat {$lateMinutes} menit)" : " (Tepat waktu)"),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        Log::info('Check In Success:', $attendance->toArray());

        $message = $isLate ? "Check in berhasil! (Terlambat {$lateMinutes} menit)" : "Check in berhasil! Selamat bekerja.";

        return redirect()->route('employee.attendance.index')->with('success', $message);
    }

    public function checkOut(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', today())
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', 'Anda belum check in hari ini.');
        }

        if ($attendance->check_out_time) {
            return redirect()->back()->with('error', 'Anda sudah melakukan check out hari ini.');
        }

        $validator = Validator::make($request->all(), [
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'photo' => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $checkOutTime = now();
        $latitude = $request->latitude ?: -6.200000;
        $longitude = $request->longitude ?: 106.816666;

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendance-photos/' . date('Y/m'), 'public');
        }

        $attendance->update([
            'check_out_time' => $checkOutTime->format('H:i:s'),
            'check_out_latitude' => $latitude,
            'check_out_longitude' => $longitude,
            'check_out_photo' => $photoPath,
        ]);

        // Calculate work duration
        $checkIn = Carbon::parse($attendance->check_in_time);
        $checkOut = Carbon::parse($attendance->check_out_time);
        $workDuration = $checkIn->diff($checkOut);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'check_out',
            'description' => "Check out pada " . $checkOutTime->format('H:i:s'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $durationText = $workDuration->format('%h jam %i menit');
        $message = "Check out berhasil! Durasi kerja: {$durationText}";

        return redirect()->route('employee.attendance.index')->with('success', $message);
    }

    public function history(Request $request)
    {
        $query = Attendance::where('user_id', Auth::id());

        if ($request->filled('month')) {
            $month = $request->month;
            $year = $request->filled('year') ? $request->year : date('Y');
            $query->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $month);
        } elseif ($request->filled('year')) {
            $query->whereYear('attendance_date', $request->year);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        $monthlyStats = Attendance::where('user_id', Auth::id())
            ->selectRaw('
                DATE_FORMAT(attendance_date, "%Y-%m") as month,
                COUNT(*) as total,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent,
                SUM(late_minutes) as total_late_minutes
            ')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        $years = Attendance::where('user_id', Auth::id())
            ->selectRaw('DISTINCT YEAR(attendance_date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (empty($years)) {
            $years = [date('Y')];
        }

        return view('employee.attendance.history', compact('attendances', 'monthlyStats', 'years'));
    }

    public function getCurrentLocation(Request $request)
    {
        $workLocation = WorkLocation::where('is_active', true)->first();

        if (!$workLocation) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi kerja belum dikonfigurasi'
            ]);
        }

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        if ($latitude && $longitude) {
            $isValid = $workLocation->isWithinRadius($latitude, $longitude);
            $distance = $workLocation->calculateDistance($latitude, $longitude, $workLocation->latitude, $workLocation->longitude);

            return response()->json([
                'success' => true,
                'is_valid' => $isValid,
                'distance' => round($distance),
                'max_distance' => $workLocation->radius,
                'location_name' => $workLocation->name,
                'message' => $isValid ? 'Anda berada dalam radius kantor' : 'Anda berada di luar radius kantor'
            ]);
        }

        return response()->json([
            'success' => true,
            'location' => [
                'latitude' => $workLocation->latitude,
                'longitude' => $workLocation->longitude,
                'name' => $workLocation->name,
                'radius' => $workLocation->radius
            ]
        ]);
    }
}
