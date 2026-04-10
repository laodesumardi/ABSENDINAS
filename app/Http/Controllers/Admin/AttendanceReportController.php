<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\WorkLocation;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('user');

        // Set default values
        $startDate = $request->filled('start_date') ? $request->start_date : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : Carbon::now()->format('Y-m-d');

        if ($request->filled('start_date')) {
            $query->where('attendance_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('attendance_date', '<=', $request->end_date);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->paginate(20);

        $users = User::where('role', 'employee')->orderBy('name')->get();

        // Statistics
        $totalAttendance = Attendance::count();
        $totalPresent = Attendance::where('status', 'present')->count();
        $totalLate = Attendance::where('status', 'late')->count();
        $totalAbsent = Attendance::where('status', 'absent')->count();

        return view('admin.reports.attendance', compact(
            'attendances',
            'users',
            'startDate',
            'endDate',
            'totalAttendance',
            'totalPresent',
            'totalLate',
            'totalAbsent'
        ));
    }

    public function edit($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);
        $users = User::where('role', 'employee')->orderBy('name')->get();
        $workLocation = WorkLocation::where('is_active', true)->first();

        return view('admin.reports.edit', compact('attendance', 'users', 'workLocation'));
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
            'status' => 'required|in:present,late,absent,half_day',
            'late_minutes' => 'nullable|integer|min:0',
            'check_in_latitude' => 'nullable|numeric|between:-90,90',
            'check_in_longitude' => 'nullable|numeric|between:-180,180',
            'check_out_latitude' => 'nullable|numeric|between:-90,90',
            'check_out_longitude' => 'nullable|numeric|between:-180,180',
            'notes' => 'nullable|string|max:500',
        ], [
            'check_out_time.after' => 'Waktu check out harus setelah waktu check in',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $oldData = $attendance->toArray();

        // Calculate late minutes if needed
        $lateMinutes = $request->late_minutes ?? 0;
        if ($request->status == 'late' && $request->check_in_time && $lateMinutes == 0) {
            $schedule = \App\Models\WorkSchedule::getScheduleByDate($request->attendance_date);
            if ($schedule && $schedule->is_working_day) {
                $checkInEnd = Carbon::parse($schedule->check_in_end);
                $checkInTime = Carbon::parse($request->check_in_time);
                if ($checkInTime > $checkInEnd) {
                    $lateMinutes = $checkInTime->diffInMinutes($checkInEnd);
                }
            }
        }

        $notes = $request->notes;
        if ($oldData['notes'] != $notes && $notes) {
            $notes = "[ADMIN EDIT] " . $notes . "\n\nSebelumnya: " . ($oldData['notes'] ?? '-');
        }

        $attendance->update([
            'user_id' => $request->user_id,
            'attendance_date' => $request->attendance_date,
            'check_in_time' => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'check_in_latitude' => $request->check_in_latitude ?? 0,
            'check_in_longitude' => $request->check_in_longitude ?? 0,
            'check_out_latitude' => $request->check_out_latitude ?? 0,
            'check_out_longitude' => $request->check_out_longitude ?? 0,
            'status' => $request->status,
            'late_minutes' => $lateMinutes,
            'notes' => $notes,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'admin_edit_attendance',
            'description' => "Mengedit absensi milik {$attendance->user->name} tanggal " . Carbon::parse($request->attendance_date)->format('d/m/Y'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($attendance->toArray()),
        ]);

        return redirect()->route('admin.reports.attendance')
            ->with('success', 'Absensi berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendanceName = $attendance->user->name;
        $attendanceDate = $attendance->attendance_date->format('d/m/Y');

        $attendance->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'admin_delete_attendance',
            'description' => "Menghapus absensi milik {$attendanceName} tanggal {$attendanceDate}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.reports.attendance')
            ->with('success', 'Absensi berhasil dihapus!');
    }

    public function create()
    {
        $users = User::where('role', 'employee')->orderBy('name')->get();
        $workLocation = WorkLocation::where('is_active', true)->first();

        return view('admin.reports.create', compact('users', 'workLocation'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
            'status' => 'required|in:present,late,absent,half_day',
            'late_minutes' => 'nullable|integer|min:0',
            'check_in_latitude' => 'nullable|numeric|between:-90,90',
            'check_in_longitude' => 'nullable|numeric|between:-180,180',
            'check_out_latitude' => 'nullable|numeric|between:-90,90',
            'check_out_longitude' => 'nullable|numeric|between:-180,180',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check if attendance already exists
        $existing = Attendance::where('user_id', $request->user_id)
            ->whereDate('attendance_date', $request->attendance_date)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Absensi untuk tanggal tersebut sudah ada!');
        }

        // Calculate late minutes
        $lateMinutes = $request->late_minutes ?? 0;
        if ($request->status == 'late' && $request->check_in_time && $lateMinutes == 0) {
            $schedule = \App\Models\WorkSchedule::getScheduleByDate($request->attendance_date);
            if ($schedule && $schedule->is_working_day) {
                $checkInEnd = Carbon::parse($schedule->check_in_end);
                $checkInTime = Carbon::parse($request->check_in_time);
                if ($checkInTime > $checkInEnd) {
                    $lateMinutes = $checkInTime->diffInMinutes($checkInEnd);
                }
            }
        }

        $attendance = Attendance::create([
            'user_id' => $request->user_id,
            'attendance_date' => $request->attendance_date,
            'check_in_time' => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'check_in_latitude' => $request->check_in_latitude ?? 0,
            'check_in_longitude' => $request->check_in_longitude ?? 0,
            'check_out_latitude' => $request->check_out_latitude ?? 0,
            'check_out_longitude' => $request->check_out_longitude ?? 0,
            'status' => $request->status,
            'late_minutes' => $lateMinutes,
            'notes' => "[ADMIN INPUT] " . ($request->notes ?? ''),
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'admin_create_attendance',
            'description' => "Membuat absensi baru untuk {$attendance->user->name} tanggal " . Carbon::parse($request->attendance_date)->format('d/m/Y'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.reports.attendance')
            ->with('success', 'Absensi berhasil ditambahkan!');
    }

    public function export(Request $request)
    {
        $query = Attendance::with('user');

        if ($request->filled('start_date')) {
            $query->where('attendance_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('attendance_date', '<=', $request->end_date);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
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
            'Terlambat',
            'Koordinat In',
            'Koordinat Out',
            'Catatan',
            'Status Validasi'
        ]);

        $no = 1;
        foreach ($attendances as $attendance) {
            fputcsv($handle, [
                $no++,
                $attendance->attendance_date->format('d/m/Y'),
                $attendance->user->name,
                $attendance->user->employee_id ?? '-',
                $attendance->user->department ?? '-',
                $attendance->check_in_time ?? '-',
                $attendance->check_out_time ?? '-',
                $this->getStatusText($attendance->status),
                $attendance->late_minutes . ' menit',
                $attendance->check_in_latitude ? number_format($attendance->check_in_latitude, 6) . ', ' . number_format($attendance->check_in_longitude, 6) : '-',
                $attendance->check_out_latitude ? number_format($attendance->check_out_latitude, 6) . ', ' . number_format($attendance->check_out_longitude, 6) : '-',
                $attendance->notes ?? '-',
                $attendance->approved_by ? 'Sudah' : 'Belum',
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
}
