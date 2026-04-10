<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\WorkLocation;
use App\Models\WorkSchedule;
use App\Models\Holiday;
use App\Models\ActivityLog;
use App\Services\GeoLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    protected $geoLocationService;

    public function __construct(GeoLocationService $geoLocationService)
    {
        $this->geoLocationService = $geoLocationService;
    }

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
        $canCheckIn = $this->canCheckIn($schedule, $todayAttendance);
        $canCheckOut = $this->canCheckOut($schedule, $todayAttendance);

        $workLocation = WorkLocation::where('is_active', true)->first();

        // Monthly statistics
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
            'recentAttendances'
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

        // Jika tidak ada jadwal atau hari libur
        if (!$schedule || !$schedule->is_working_day) {
            return false;
        }

        $now = now();
        $checkInStart = Carbon::parse($schedule->check_in_start);
        $checkInEnd = Carbon::parse($schedule->check_in_end);

        // Tambahkan toleransi 2 jam setelah batas check in
        $maxCheckInTime = (clone $checkInEnd)->addHours(2);

        // Cek apakah sekarang dalam waktu check in
        return $now->between($checkInStart, $maxCheckInTime);
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

        // Jika tidak ada jadwal atau hari libur
        if (!$schedule || !$schedule->is_working_day) {
            return true;
        }

        $now = now();
        $checkOutStart = Carbon::parse($schedule->check_out_start);
        $checkOutEnd = Carbon::parse($schedule->check_out_end);

        // Allow checkout dari 1 jam sebelum sampai 12 jam setelah
        $minCheckOutTime = (clone $checkOutStart)->subHour();
        $maxCheckOutTime = (clone $checkOutEnd)->addHours(12);

        return $now->between($minCheckOutTime, $maxCheckOutTime);
    }

    /**
     * Process check in
     */
    public function checkIn(Request $request)
    {
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

        // Validate location
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
            return redirect()->back()->with('error', "Anda berada di luar radius kantor. Jarak: " . round($distance) . " meter (Maksimal: {$workLocation->radius} meter)");
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

        $message = $isLate ? "Check in berhasil! (Terlambat {$lateMinutes} menit)" : "Check in berhasil! Selamat bekerja.";

        return redirect()->route('employee.attendance.index')->with('success', $message);
    }

    /**
     * Process check out
     */
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

        // Check if today is holiday
        if (Holiday::isHoliday(today())) {
            return redirect()->back()->with('error', 'Hari ini adalah hari libur, tidak perlu check out.');
        }

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Validate location for checkout
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
                return redirect()->back()->with('error', "Anda harus berada di dalam radius kantor untuk check out. Jarak: " . round($distance) . " meter");
            }
        }

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendance-photos/' . date('Y/m'), 'public');
        }

        $checkOutTime = now();

        // Check early checkout
        $schedule = WorkSchedule::getTodaySchedule();
        $earlyCheckoutMinutes = 0;
        if ($schedule && $schedule->is_working_day) {
            $checkOutStart = Carbon::parse($schedule->check_out_start);
            if ($checkOutTime < $checkOutStart) {
                $earlyCheckoutMinutes = $checkOutStart->diffInMinutes($checkOutTime);
            }
        }

        $attendance->update([
            'check_out_time' => $checkOutTime->format('H:i:s'),
            'check_out_latitude' => $request->latitude,
            'check_out_longitude' => $request->longitude,
            'check_out_photo' => $photoPath,
            'early_checkout_minutes' => $earlyCheckoutMinutes,
        ]);

        // Calculate work duration
        $checkIn = Carbon::parse($attendance->check_in_time);
        $checkOut = Carbon::parse($attendance->check_out_time);
        $workDuration = $checkIn->diff($checkOut);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'check_out',
            'description' => "Check out pada " . $checkOutTime->format('H:i:s') .
                ($earlyCheckoutMinutes > 0 ? " (Pulang lebih awal {$earlyCheckoutMinutes} menit)" : ""),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $durationText = $workDuration->format('%h jam %i menit');
        $message = "Check out berhasil! Durasi kerja: {$durationText}";
        if ($earlyCheckoutMinutes > 0) {
            $message .= " (Pulang lebih awal {$earlyCheckoutMinutes} menit)";
        }

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

        // Get statistics by month
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

        // Get available years for filter
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

    /**
     * Verify manual location code
     */
    public function verifyLocation(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $validCodes = [
            'KANTOR123' => ['latitude' => -6.200000, 'longitude' => 106.816666],
            'ABSEN123' => ['latitude' => -6.200000, 'longitude' => 106.816666],
        ];

        if (isset($validCodes[$request->code])) {
            return response()->json([
                'success' => true,
                'latitude' => $validCodes[$request->code]['latitude'],
                'longitude' => $validCodes[$request->code]['longitude']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Kode verifikasi tidak valid'
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
            $html .= '<div class="row mt-3">
                <div class="col-12">
                    <hr>
                    <h6><i class="fas fa-camera"></i> Foto Check In</h6>
                    <img src="' . Storage::url($attendance->check_in_photo) . '" class="img-fluid rounded" style="max-height: 200px;">
                </div>
            </div>';
        }

        if ($attendance->check_out_photo) {
            $html .= '<div class="row mt-3">
                <div class="col-12">
                    <hr>
                    <h6><i class="fas fa-camera"></i> Foto Check Out</h6>
                    <img src="' . Storage::url($attendance->check_out_photo) . '" class="img-fluid rounded" style="max-height: 200px;">
                </div>
            </div>';
        }

        return response()->json(['html' => $html]);
    }
}
