<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkSchedule;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
            $schedule->check_in_end = '16:00:00';
            $schedule->check_out_start = '17:00:00';
            $schedule->check_out_end = '18:00:00';
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

        $schedule->is_working_day = $request->has('is_working_day');

        if ($request->has('is_working_day') && $request->is_working_day == 1) {
            $schedule->check_in_start = $request->check_in_start ?: '08:00:00';
            $schedule->check_in_end = $request->check_in_end ?: '20:00:00';
            $schedule->check_out_start = $request->check_out_start ?: '17:00:00';
            $schedule->check_out_end = $request->check_out_end ?: '23:00:00';
        } else {
            $schedule->check_in_start = null;
            $schedule->check_in_end = null;
            $schedule->check_out_start = null;
            $schedule->check_out_end = null;
        }

        $schedule->save();

        // Clear cache setelah update
        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal kerja berhasil diupdate! Silakan cek halaman employee.');
    }

    public function reset()
    {
        $defaultSchedules = [
            ['day_of_week' => 'monday', 'check_in_start' => '08:00:00', 'check_in_end' => '16:00:00', 'check_out_start' => '17:00:00', 'check_out_end' => '18:00:00', 'is_working_day' => true],
            ['day_of_week' => 'tuesday', 'check_in_start' => '08:00:00', 'check_in_end' => '16:00:00', 'check_out_start' => '17:00:00', 'check_out_end' => '18:00:00', 'is_working_day' => true],
            ['day_of_week' => 'wednesday', 'check_in_start' => '08:00:00', 'check_in_end' => '16:00:00', 'check_out_start' => '17:00:00', 'check_out_end' => '18:00:00', 'is_working_day' => true],
            ['day_of_week' => 'thursday', 'check_in_start' => '08:00:00', 'check_in_end' => '16:00:00', 'check_out_start' => '17:00:00', 'check_out_end' => '18:00:00', 'is_working_day' => true],
            ['day_of_week' => 'friday', 'check_in_start' => '08:00:00', 'check_in_end' => '16:00:00', 'check_out_start' => '17:00:00', 'check_out_end' => '18:00:00', 'is_working_day' => true],
            ['day_of_week' => 'saturday', 'check_in_start' => '08:00:00', 'check_in_end' => '12:00:00', 'check_out_start' => '12:00:00', 'check_out_end' => '13:00:00', 'is_working_day' => false],
            ['day_of_week' => 'sunday', 'check_in_start' => null, 'check_in_end' => null, 'check_out_start' => null, 'check_out_end' => null, 'is_working_day' => false],
        ];

        foreach ($defaultSchedules as $schedule) {
            WorkSchedule::updateOrCreate(
                ['day_of_week' => $schedule['day_of_week']],
                $schedule
            );
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal kerja berhasil direset ke default!');
    }


    public function getScheduleApi()
    {
        $schedules = WorkSchedule::orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->get();

        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
    }
}
