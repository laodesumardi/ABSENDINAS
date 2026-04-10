<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\WorkLocation;
use App\Models\ActivityLog;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AttendanceValidationController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('user');

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('attendance_date', $request->date);
        } else {
            $query->whereDate('attendance_date', today());
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by validation status (approved or not)
        if ($request->filled('validated')) {
            if ($request->validated == 'yes') {
                $query->whereNotNull('approved_by');
            } else {
                $query->whereNull('approved_by');
            }
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->paginate(20);

        // Statistics
        $stats = [
            'total' => Attendance::whereDate('attendance_date', $request->date ?? today())->count(),
            'present' => Attendance::whereDate('attendance_date', $request->date ?? today())->where('status', 'present')->count(),
            'late' => Attendance::whereDate('attendance_date', $request->date ?? today())->where('status', 'late')->count(),
            'absent' => Attendance::whereDate('attendance_date', $request->date ?? today())->where('status', 'absent')->count(),
            'validated' => Attendance::whereDate('attendance_date', $request->date ?? today())->whereNotNull('approved_by')->count(),
            'not_validated' => Attendance::whereDate('attendance_date', $request->date ?? today())->whereNull('approved_by')->count(),
        ];

        // Get users for filter
        $users = User::where('role', 'employee')->orderBy('name')->get();

        return view('operator.attendance.index', compact('attendances', 'stats', 'users'));
    }

    public function show(Attendance $attendance)
    {
        $workLocation = WorkLocation::where('is_active', true)->first();

        // Calculate distance from office if location exists
        $distance = null;
        if ($workLocation && $attendance->check_in_latitude) {
            $distance = $workLocation->calculateDistance(
                $attendance->check_in_latitude,
                $attendance->check_in_longitude,
                $workLocation->latitude,
                $workLocation->longitude
            );
        }

        $distanceOut = null;
        if ($workLocation && $attendance->check_out_latitude) {
            $distanceOut = $workLocation->calculateDistance(
                $attendance->check_out_latitude,
                $attendance->check_out_longitude,
                $workLocation->latitude,
                $workLocation->longitude
            );
        }

        return view('operator.attendance.show', compact('attendance', 'distance', 'distanceOut'));
    }

    public function validate(Request $request, Attendance $attendance)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:present,late,absent,half_day',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $oldData = $attendance->toArray();

        $attendance->update([
            'status' => $request->status,
            'notes' => $request->notes ? ($attendance->notes ? $attendance->notes . "\n[VALIDASI] " . $request->notes : "[VALIDASI] " . $request->notes) : $attendance->notes,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // If status changed to absent, set late_minutes to 0
        if ($request->status == 'absent') {
            $attendance->update(['late_minutes' => 0]);
        }

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'validate_attendance',
            'description' => "Memvalidasi absensi {$attendance->user->name} tanggal {$attendance->attendance_date->format('d/m/Y')} menjadi " . $this->getStatusText($request->status),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($attendance->toArray()),
        ]);

        return redirect()->route('operator.attendance.index', ['date' => $attendance->attendance_date->format('Y-m-d')])
            ->with('success', 'Absensi berhasil divalidasi!');
    }




    public function bulkValidate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:attendances,id',
            'status' => 'required|in:present,late,absent,half_day',
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            $attendance = Attendance::find($id);
            if ($attendance && !$attendance->approved_by) {
                $attendance->update([
                    'status' => $request->status,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);
                $count++;
            }
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_validate_attendance',
            'description' => "Memvalidasi {$count} absensi secara massal menjadi " . $this->getStatusText($request->status),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('operator.attendance.index')
            ->with('success', "{$count} absensi berhasil divalidasi!");
    }

    public function export(Request $request)
    {
        $query = Attendance::with('user');

        if ($request->filled('start_date')) {
            $query->whereDate('attendance_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('attendance_date', '<=', $request->end_date);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->get();

        $filename = "absensi_" . now()->format('Y-m-d_H-i-s') . ".csv";

        $handle = fopen('php://temp', 'w');
        fwrite($handle, "\xEF\xBB\xBF");

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
            'Lokasi Check In',
            'Lokasi Check Out',
            'Catatan',
            'Status Validasi'
        ]);

        $no = 1;
        foreach ($attendances as $attendance) {
            $duration = '';
            if ($attendance->check_in_time && $attendance->check_out_time) {
                $checkIn = Carbon::parse($attendance->check_in_time);
                $checkOut = Carbon::parse($attendance->check_out_time);
                $diff = $checkIn->diff($checkOut);
                $duration = $diff->format('%h jam %i menit');
            }

            fputcsv($handle, [
                $no++,
                $attendance->attendance_date->format('d/m/Y'),
                $attendance->user->name,
                $attendance->user->employee_id ?? '-',
                $attendance->user->department ?? '-',
                $attendance->check_in_time ?? '-',
                $attendance->check_out_time ?? '-',
                $this->getStatusText($attendance->status),
                $attendance->late_minutes,
                $duration,
                $attendance->check_in_latitude ? number_format($attendance->check_in_latitude, 6) . ', ' . number_format($attendance->check_in_longitude, 6) : '-',
                $attendance->check_out_latitude ? number_format($attendance->check_out_latitude, 6) . ', ' . number_format($attendance->check_out_longitude, 6) : '-',
                $attendance->notes ?? '-',
                $attendance->approved_by ? 'Tervalidasi' : 'Belum Validasi'
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



    public function create()
    {
        $users = User::where('role', 'employee')->orderBy('name')->get();
        $workLocation = WorkLocation::where('is_active', true)->first();
        return view('operator.attendance.create', compact('users', 'workLocation'));
    }


    public function createManual(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
            'status' => 'required|in:present,late,absent,half_day',
            'late_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:500',
            'check_in_latitude' => 'nullable|numeric|between:-90,90',
            'check_in_longitude' => 'nullable|numeric|between:-180,180',
            'check_out_latitude' => 'nullable|numeric|between:-90,90',
            'check_out_longitude' => 'nullable|numeric|between:-180,180',
            'use_office_location' => 'nullable|boolean',
        ], [
            'check_out_time.after' => 'Waktu check out harus setelah waktu check in',
            'check_in_latitude.between' => 'Latitude tidak valid',
            'check_in_longitude.between' => 'Longitude tidak valid',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check if attendance already exists
        $existing = Attendance::where('user_id', $request->user_id)
            ->whereDate('attendance_date', $request->attendance_date)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Absensi untuk tanggal tersebut sudah ada! Silakan edit jika perlu.');
        }

        // Get work location for coordinates
        $workLocation = WorkLocation::where('is_active', true)->first();

        // Set coordinates based on input
        $checkInLat = null;
        $checkInLng = null;
        $checkOutLat = null;
        $checkOutLng = null;

        // If use office location is checked
        if ($request->has('use_office_location') && $workLocation) {
            if ($request->check_in_time) {
                $checkInLat = $workLocation->latitude;
                $checkInLng = $workLocation->longitude;
            }
            if ($request->check_out_time) {
                $checkOutLat = $workLocation->latitude;
                $checkOutLng = $workLocation->longitude;
            }
        } else {
            // Use manual coordinates
            if ($request->check_in_time) {
                $checkInLat = $request->check_in_latitude ?? ($workLocation ? $workLocation->latitude : 0);
                $checkInLng = $request->check_in_longitude ?? ($workLocation ? $workLocation->longitude : 0);
            }
            if ($request->check_out_time) {
                $checkOutLat = $request->check_out_latitude ?? ($workLocation ? $workLocation->latitude : 0);
                $checkOutLng = $request->check_out_longitude ?? ($workLocation ? $workLocation->longitude : 0);
            }
        }

        // Calculate late minutes if status is late but late_minutes not provided
        $lateMinutes = $request->late_minutes ?? 0;
        if ($request->status == 'late' && $request->check_in_time && $lateMinutes == 0) {
            $schedule = WorkSchedule::getScheduleByDate($request->attendance_date);
            if ($schedule && $schedule->is_working_day) {
                $checkInEnd = Carbon::parse($schedule->check_in_end);
                $checkInTime = Carbon::parse($request->check_in_time);
                if ($checkInTime > $checkInEnd) {
                    $lateMinutes = $checkInTime->diffInMinutes($checkInEnd);
                }
            }
        }

        $notes = $request->notes ?? '';
        $coordNote = '';
        if ($checkInLat && $checkInLng) {
            $coordNote .= "\n[Koordinat Check In: {$checkInLat}, {$checkInLng}]";
        }
        if ($checkOutLat && $checkOutLng) {
            $coordNote .= "\n[Koordinat Check Out: {$checkOutLat}, {$checkOutLng}]";
        }

        $attendance = Attendance::create([
            'user_id' => $request->user_id,
            'attendance_date' => $request->attendance_date,
            'check_in_time' => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'check_in_latitude' => $checkInLat,
            'check_in_longitude' => $checkInLng,
            'check_out_latitude' => $checkOutLat,
            'check_out_longitude' => $checkOutLng,
            'status' => $request->status,
            'late_minutes' => $lateMinutes,
            'notes' => "[INPUT MANUAL] " . $notes . $coordNote,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create_manual_attendance',
            'description' => "Membuat absensi manual untuk {$attendance->user->name} tanggal " . Carbon::parse($request->attendance_date)->format('d/m/Y'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_data' => json_encode($attendance->toArray()),
        ]);

        return redirect()->route('operator.attendance.index', ['date' => $request->attendance_date])
            ->with('success', 'Absensi manual berhasil ditambahkan!');
    }


    public function edit(Attendance $attendance)
    {
        $workLocation = WorkLocation::where('is_active', true)->first();
        return view('operator.attendance.edit', compact('attendance', 'workLocation'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validator = Validator::make($request->all(), [
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
            'status' => 'required|in:present,late,absent,half_day',
            'late_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:500',
            'check_in_latitude' => 'nullable|numeric|between:-90,90',
            'check_in_longitude' => 'nullable|numeric|between:-180,180',
            'check_out_latitude' => 'nullable|numeric|between:-90,90',
            'check_out_longitude' => 'nullable|numeric|between:-180,180',
            'use_office_location' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $oldData = $attendance->toArray();

        // Get work location
        $workLocation = WorkLocation::where('is_active', true)->first();

        // Set coordinates
        $checkInLat = $attendance->check_in_latitude;
        $checkInLng = $attendance->check_in_longitude;
        $checkOutLat = $attendance->check_out_latitude;
        $checkOutLng = $attendance->check_out_longitude;

        if ($request->has('use_office_location') && $workLocation) {
            if ($request->check_in_time && !$attendance->check_in_time) {
                $checkInLat = $workLocation->latitude;
                $checkInLng = $workLocation->longitude;
            }
            if ($request->check_out_time && !$attendance->check_out_time) {
                $checkOutLat = $workLocation->latitude;
                $checkOutLng = $workLocation->longitude;
            }
        } else {
            if ($request->check_in_latitude && $request->check_in_longitude) {
                $checkInLat = $request->check_in_latitude;
                $checkInLng = $request->check_in_longitude;
            }
            if ($request->check_out_latitude && $request->check_out_longitude) {
                $checkOutLat = $request->check_out_latitude;
                $checkOutLng = $request->check_out_longitude;
            }
        }

        $notes = $request->notes ?? '';
        $coordNote = '';
        if ($checkInLat && $checkInLng && $checkInLat != ($oldData['check_in_latitude'] ?? null)) {
            $coordNote .= "\n[Koordinat Check In diubah: {$checkInLat}, {$checkInLng}]";
        }
        if ($checkOutLat && $checkOutLng && $checkOutLat != ($oldData['check_out_latitude'] ?? null)) {
            $coordNote .= "\n[Koordinat Check Out diubah: {$checkOutLat}, {$checkOutLng}]";
        }

        $attendance->update([
            'check_in_time' => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'check_in_latitude' => $checkInLat,
            'check_in_longitude' => $checkInLng,
            'check_out_latitude' => $checkOutLat,
            'check_out_longitude' => $checkOutLng,
            'status' => $request->status,
            'late_minutes' => $request->late_minutes ?? 0,
            'notes' => $request->notes ? ($attendance->notes ? $attendance->notes . "\n[EDIT] " . $notes . $coordNote : "[EDIT] " . $notes . $coordNote) : $attendance->notes . $coordNote,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'edit_attendance',
            'description' => "Mengedit absensi {$attendance->user->name} tanggal {$attendance->attendance_date->format('d/m/Y')}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($attendance->toArray()),
        ]);

        return redirect()->route('operator.attendance.show', $attendance)
            ->with('success', 'Absensi berhasil diupdate!');
    }



    public function getSchedule(Request $request)
    {
        $date = $request->input('date');
        $schedule = WorkSchedule::getScheduleByDate($date);

        if ($schedule) {
            return response()->json([
                'success' => true,
                'schedule' => [
                    'is_working_day' => $schedule->is_working_day,
                    'check_in_window' => $schedule->check_in_window,
                    'check_out_window' => $schedule->check_out_window,
                    'working_hours' => $schedule->working_hours,
                ]
            ]);
        }

        return response()->json(['success' => false, 'schedule' => null]);
    }

    public function calculateLate(Request $request)
    {
        $date = $request->input('date');
        $checkInTime = $request->input('check_in_time');

        if (!$date || !$checkInTime) {
            return response()->json(['late_minutes' => 0]);
        }

        $schedule = WorkSchedule::getScheduleByDate($date);
        if (!$schedule || !$schedule->is_working_day) {
            return response()->json(['late_minutes' => 0]);
        }

        $checkInEnd = Carbon::parse($schedule->check_in_end);
        $checkIn = Carbon::parse($checkInTime);

        if ($checkIn > $checkInEnd) {
            $lateMinutes = $checkIn->diffInMinutes($checkInEnd);
            return response()->json(['late_minutes' => $lateMinutes]);
        }

        return response()->json(['late_minutes' => 0]);
    }
}
