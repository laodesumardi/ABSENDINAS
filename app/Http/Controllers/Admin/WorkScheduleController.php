<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkSchedule;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class WorkScheduleController extends Controller
{
    public function index()
    {
        $schedules = WorkSchedule::orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->get();

        $todaySchedule = WorkSchedule::getTodaySchedule();

        return view('admin.schedules.index', compact('schedules', 'todaySchedule'));
    }

    public function edit($day)
    {
        $schedule = WorkSchedule::where('day_of_week', $day)->first();

        // Jika tidak ada, buat baru
        if (!$schedule) {
            $schedule = new WorkSchedule();
            $schedule->day_of_week = $day;
            $schedule->is_working_day = true;
            $schedule->check_in_start = '08:00:00';
            $schedule->check_in_end = '20:00:00';
            $schedule->check_out_start = '17:00:00';
            $schedule->check_out_end = '23:00:00';
        }

        $dayLabel = WorkSchedule::$days[$day] ?? ucfirst($day);

        return view('admin.schedules.edit', compact('schedule', 'dayLabel'));
    }

    public function update(Request $request, $day)
    {
        $schedule = WorkSchedule::where('day_of_week', $day)->first();

        if (!$schedule) {
            $schedule = new WorkSchedule();
            $schedule->day_of_week = $day;
        }

        $validator = Validator::make($request->all(), [
            'is_working_day' => 'boolean',
            'check_in_start' => 'nullable|date_format:H:i',
            'check_in_end' => 'nullable|date_format:H:i',
            'check_out_start' => 'nullable|date_format:H:i',
            'check_out_end' => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $oldData = $schedule->toArray();

        $schedule->is_working_day = $request->has('is_working_day');

        if ($request->has('is_working_day') && $request->is_working_day == 1) {
            // Set default values jika kosong
            $schedule->check_in_start = $request->check_in_start ?: '08:00:00';
            $schedule->check_in_end = $request->check_in_end ?: '20:00:00';
            $schedule->check_out_start = $request->check_out_start ?: '17:00:00';
            $schedule->check_out_end = $request->check_out_end ?: '23:00:00';
        } else {
            // Untuk hari libur, set ke null
            $schedule->check_in_start = null;
            $schedule->check_in_end = null;
            $schedule->check_out_start = null;
            $schedule->check_out_end = null;
        }

        $schedule->save();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update_schedule',
            'description' => "Mengupdate jadwal kerja untuk hari " . ($schedule->day_label ?? $day),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($schedule->toArray()),
        ]);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal kerja berhasil diupdate!');
    }

    public function reset()
    {
        try {
            // Hapus semua data
            WorkSchedule::truncate();

            // Insert default schedules
            $defaultSchedules = [
                ['day_of_week' => 'monday', 'check_in_start' => '08:00:00', 'check_in_end' => '20:00:00', 'check_out_start' => '17:00:00', 'check_out_end' => '23:00:00', 'is_working_day' => true],
                ['day_of_week' => 'tuesday', 'check_in_start' => '08:00:00', 'check_in_end' => '20:00:00', 'check_out_start' => '17:00:00', 'check_out_end' => '23:00:00', 'is_working_day' => true],
                ['day_of_week' => 'wednesday', 'check_in_start' => '08:00:00', 'check_in_end' => '20:00:00', 'check_out_start' => '17:00:00', 'check_out_end' => '23:00:00', 'is_working_day' => true],
                ['day_of_week' => 'thursday', 'check_in_start' => '08:00:00', 'check_in_end' => '20:00:00', 'check_out_start' => '17:00:00', 'check_out_end' => '23:00:00', 'is_working_day' => true],
                ['day_of_week' => 'friday', 'check_in_start' => '08:00:00', 'check_in_end' => '20:00:00', 'check_out_start' => '17:00:00', 'check_out_end' => '23:00:00', 'is_working_day' => true],
                ['day_of_week' => 'saturday', 'check_in_start' => '08:00:00', 'check_in_end' => '12:00:00', 'check_out_start' => '12:00:00', 'check_out_end' => '13:00:00', 'is_working_day' => false],
                ['day_of_week' => 'sunday', 'check_in_start' => null, 'check_in_end' => null, 'check_out_start' => null, 'check_out_end' => null, 'is_working_day' => false],
            ];

            foreach ($defaultSchedules as $schedule) {
                WorkSchedule::create($schedule);
            }

            return redirect()->route('admin.schedules.index')
                ->with('success', 'Jadwal kerja berhasil direset ke default!');
        } catch (\Exception $e) {
            return redirect()->route('admin.schedules.index')
                ->with('error', 'Gagal reset jadwal: ' . $e->getMessage());
        }
    }
}
