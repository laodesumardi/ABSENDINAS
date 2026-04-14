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
    /**
     * Display attendance dashboard
     */
    public function index()
    {
        $today = now()->format('Y-m-d');
        $todayAttendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', $today)
            ->first();

        $isHoliday = Holiday::isHoliday($today);
        $holidayInfo = $isHoliday ? Holiday::getHolidayByDate($today) : null;

        $schedule = WorkSchedule::getTodaySchedule();

        // Jika schedule tidak ada, buat default
        if (!$schedule || !$schedule->check_in_start || $schedule->check_in_start == '00:00:00') {
            $schedule = new \stdClass();
            $schedule->is_working_day = true;
            $schedule->check_in_start = '08:00:00';
            $schedule->check_in_end = '17:00:00';
            $schedule->check_out_start = '17:00:00';
            $schedule->check_out_end = '23:00:00';
        }

        // Cek apakah bisa check in dan check out
        $canCheckIn = $this->canCheckIn($schedule, $todayAttendance);
        $canCheckOut = $this->canCheckOut($schedule, $todayAttendance);

        // Format waktu untuk ditampilkan
        $checkInWindow = date('H:i', strtotime($schedule->check_in_start)) . ' - ' . date('H:i', strtotime($schedule->check_in_end));
        $checkOutWindow = date('H:i', strtotime($schedule->check_out_start)) . ' - ' . date('H:i', strtotime($schedule->check_out_end));

        $start = Carbon::parse($schedule->check_in_start);
        $end = Carbon::parse($schedule->check_out_end);
        $diff = $start->diff($end);
        $workingHours = $diff->format('%h jam %i menit');

        $workLocation = WorkLocation::where('is_active', true)->first();

        // Statistik bulanan
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

        // Riwayat absensi terbaru
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

    /**
     * Check if user can check in
     */
    private function canCheckIn($schedule, $todayAttendance)
    {
        // Jika sudah check in hari ini
        if ($todayAttendance) {
            return false;
        }

        // Jika hari libur
        if (!$schedule || !$schedule->is_working_day) {
            return false;
        }

        // TOMBOL SELALU MUNCUL untuk testing (HAPUS setelah testing selesai)
        return true;
    }

    /**
     * Check if user can check out
     */
    private function canCheckOut($schedule, $todayAttendance)
    {
        // Jika belum check in atau sudah check out
        if (!$todayAttendance || $todayAttendance->check_out_time) {
            return false;
        }

        // Jika hari libur
        if (!$schedule || !$schedule->is_working_day) {
            return true;
        }

        // Cek apakah sudah bisa check out (setelah jam 17:00)
        $now = now();
        $checkOutStart = Carbon::parse($schedule->check_out_start);

        // Jika belum jam 17:00, belum bisa check out
        if ($now < $checkOutStart) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can check out
     */


    /**
     * Process check in
     */
    public function checkIn(Request $request)
    {
        // Log request untuk debugging
        Log::info('=== CHECK IN START ===');
        Log::info('Request Data:', $request->all());

        // Cek apakah sudah check in hari ini
        $existingAttendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', today())
            ->first();

        if ($existingAttendance) {
            return redirect()->back()->with('error', 'Anda sudah melakukan check in hari ini.');
        }

        // Cek apakah hari libur
        if (Holiday::isHoliday(today())) {
            return redirect()->back()->with('error', 'Hari ini adalah hari libur, tidak perlu check in.');
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Validasi lokasi kantor
        $workLocation = WorkLocation::where('is_active', true)->first();
        if (!$workLocation) {
            return redirect()->back()->with('error', 'Lokasi kerja belum dikonfigurasi. Silakan hubungi admin.');
        }

        // Cek apakah lokasi berada dalam radius kantor
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

        // Cek keterlambatan
        $schedule = WorkSchedule::getTodaySchedule();
        $checkInTime = now();
        $isLate = false;
        $lateMinutes = 0;

        // DEBUG: Log schedule
        Log::info('Schedule Data:', [
            'schedule_exists' => $schedule ? 'Yes' : 'No',
            'is_working_day' => $schedule ? $schedule->is_working_day : 'No',
            'check_in_end' => $schedule ? $schedule->check_in_end : 'NULL',
            'current_time' => $checkInTime->format('H:i:s')
        ]);

        if ($schedule && $schedule->is_working_day && $schedule->check_in_end) {
            $lateThreshold = Carbon::parse($schedule->check_in_end);

            Log::info('Comparison:', [
                'current_time' => $checkInTime->format('H:i:s'),
                'late_threshold' => $lateThreshold->format('H:i:s'),
                'is_late' => $checkInTime->gt($lateThreshold) ? 'Yes' : 'No'
            ]);

            // HANYA dianggap terlambat jika check_in_time MELEBIHI late_threshold
            if ($checkInTime->gt($lateThreshold)) {
                $isLate = true;
                $lateMinutes = $checkInTime->diffInMinutes($lateThreshold);
            } else {
                $isLate = false;
                $lateMinutes = 0; // Pastikan 0 jika tidak terlambat
            }
        }

        Log::info('Result:', [
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes
        ]);

        // Handle upload foto
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendance-photos/' . date('Y/m'), 'public');
        }

        // Simpan data absensi
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

        Log::info('Saved Attendance:', [
            'id' => $attendance->id,
            'status' => $attendance->status,
            'late_minutes' => $attendance->late_minutes
        ]);

        // Log aktivitas
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'check_in',
            'description' => "Check in pada " . $checkInTime->format('H:i:s') . ($isLate ? " (Terlambat {$lateMinutes} menit)" : " (Tepat waktu)"),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $message = $isLate ? "Check in berhasil! (Terlambat {$lateMinutes} menit)" : "Check in berhasil! Selamat bekerja.";

        Log::info('=== CHECK IN END ===');

        return redirect()->route('employee.attendance.index')->with('success', $message);
    }
    /**
     * Process check out
     */
    public function checkOut(Request $request)
    {
        // Log request untuk debugging
        Log::info('Check Out Request:', $request->all());

        // Cek apakah sudah check in
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', today())
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', 'Anda belum check in hari ini.');
        }

        if ($attendance->check_out_time) {
            return redirect()->back()->with('error', 'Anda sudah melakukan check out hari ini.');
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Validasi lokasi untuk check out
        $workLocation = WorkLocation::where('is_active', true)->first();
        if ($workLocation) {
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
                return redirect()->back()->with('error', "Anda harus berada di dalam radius kantor untuk check out! Jarak: " . round($distance) . " meter");
            }
        }

        $checkOutTime = now();

        // Cek apakah sudah bisa check out (setelah jam 17:00)
        $schedule = WorkSchedule::getTodaySchedule();
        $canCheckOut = true;

        if ($schedule && $schedule->is_working_day && $schedule->check_out_start) {
            $checkOutStart = Carbon::parse($schedule->check_out_start); // Jam 17:00
            if ($checkOutTime < $checkOutStart) {
                return redirect()->back()->with('error', "Belum waktunya check out! Check out dapat dilakukan mulai jam " . $checkOutStart->format('H:i'));
            }
        }

        // Handle upload foto
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendance-photos/' . date('Y/m'), 'public');
        }

        // Update data absensi
        $attendance->update([
            'check_out_time' => $checkOutTime->format('H:i:s'),
            'check_out_latitude' => $request->latitude,
            'check_out_longitude' => $request->longitude,
            'check_out_photo' => $photoPath,
        ]);

        // Hitung durasi kerja
        $checkIn = Carbon::parse($attendance->check_in_time);
        $checkOut = Carbon::parse($attendance->check_out_time);
        $workDuration = $checkIn->diff($checkOut);

        // Log aktivitas
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'check_out',
            'description' => "Check out pada " . $checkOutTime->format('H:i:s'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_data' => json_encode([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'time' => $checkOutTime->format('H:i:s'),
            ]),
        ]);

        Log::info('Check Out Success:', $attendance->toArray());

        $durationText = $workDuration->format('%h jam %i menit');
        $message = "Check out berhasil! Durasi kerja: {$durationText}";

        return redirect()->route('employee.attendance.index')->with('success', $message);
    }

    /**
     * Display attendance history
     */
    public function history(Request $request)
    {
        $query = Attendance::where('user_id', Auth::id());

        // Filter by month
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

        // Statistik per bulan
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

        // Tahun yang tersedia untuk filter
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

    /**
     * Get current location validation
     */
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
            $distance = $workLocation->calculateDistance(
                $latitude,
                $longitude,
                $workLocation->latitude,
                $workLocation->longitude
            );

            return response()->json([
                'success' => true,
                'is_valid' => $isValid,
                'distance' => round($distance),
                'max_distance' => $workLocation->radius,
                'location_name' => $workLocation->name,
                'message' => $isValid ? 'Lokasi valid' : 'Lokasi tidak valid'
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

    /**
     * Get attendance detail for modal
     */
    public function getDetail($id)
    {
        $attendance = Attendance::with('user')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $duration = '';
        if ($attendance->check_in_time && $attendance->check_out_time) {
            $checkIn = Carbon::parse($attendance->check_in_time);
            $checkOut = Carbon::parse($attendance->check_out_time);
            $diff = $checkIn->diff($checkOut);
            $duration = $diff->format('%h jam %i menit');
        }

        $html = '
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Tanggal</th>
                        <td>: ' . $attendance->attendance_date->format('d F Y') . ' (' . $attendance->attendance_date->format('l') . ')</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>: <span class="badge bg-' . ($attendance->status == 'present' ? 'success' : ($attendance->status == 'late' ? 'warning' : 'danger')) . '">' . ucfirst($attendance->status) . '</span></td>
                    </tr>
                    <tr>
                        <th>Check In</th>
                        <td>: ' . ($attendance->check_in_time ?? '-') . '</td>
                    </tr>
                    <tr>
                        <th>Lokasi Check In</th>
                        <td>: ' . ($attendance->check_in_latitude ? number_format($attendance->check_in_latitude, 6) . ', ' . number_format($attendance->check_in_longitude, 6) : '-') . '</td>
                    </tr>
                    <tr>
                        <th>Keterlambatan</th>
                        <td>: ' . ($attendance->late_minutes > 0 ? $attendance->late_minutes . ' menit' : '-') . '</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Durasi Kerja</th>
                        <td>: ' . $duration . '</td>
                    </tr>
                    <tr>
                        <th>Check Out</th>
                        <td>: ' . ($attendance->check_out_time ?? '-') . '</td>
                    </tr>
                    <tr>
                        <th>Lokasi Check Out</th>
                        <td>: ' . ($attendance->check_out_latitude ? number_format($attendance->check_out_latitude, 6) . ', ' . number_format($attendance->check_out_longitude, 6) : '-') . '</td>
                    </tr>
                    <tr>
                        <th>Pulang Awal</th>
                        <td>: ' . ($attendance->early_checkout_minutes > 0 ? $attendance->early_checkout_minutes . ' menit' : '-') . '</td>
                    </tr>
                    <tr>
                        <th>Catatan</th>
                        <td>: ' . ($attendance->notes ?? '-') . '</td>
                    </tr>
                </table>
            </div>
        </div>';

        if ($attendance->check_in_photo) {
            $html .= '<div class="mt-3 row"><div class="col-12"><hr><h6><i class="fas fa-camera"></i> Foto Check In</h6><img src="' . Storage::url($attendance->check_in_photo) . '" class="rounded img-fluid" style="max-height: 200px;"></div></div>';
        }

        if ($attendance->check_out_photo) {
            $html .= '<div class="mt-3 row"><div class="col-12"><hr><h6><i class="fas fa-camera"></i> Foto Check Out</h6><img src="' . Storage::url($attendance->check_out_photo) . '" class="rounded img-fluid" style="max-height: 200px;"></div></div>';
        }

        return response()->json(['html' => $html]);
    }
}
