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
        $schedule = WorkSchedule::where('day_of_week', $day)->firstOrFail();
        $dayLabel = WorkSchedule::$days[$day] ?? ucfirst($day);

        return view('admin.schedules.edit', compact('schedule', 'dayLabel'));
    }

    public function update(Request $request, $day)
    {
        $schedule = WorkSchedule::where('day_of_week', $day)->firstOrFail();

        $rules = [
            'is_working_day' => 'boolean',
        ];

        if ($request->has('is_working_day') && $request->is_working_day == 1) {
            $rules['check_in_start'] = 'required|date_format:H:i';
            $rules['check_in_end'] = 'required|date_format:H:i';
            $rules['check_out_start'] = 'required|date_format:H:i';
            $rules['check_out_end'] = 'required|date_format:H:i';

            // Custom validation messages
            $messages = [
                'check_in_end.after' => 'Waktu akhir check-in harus setelah waktu mulai check-in',
                'check_out_start.after' => 'Waktu mulai check-out harus setelah waktu akhir check-in',
                'check_out_end.after' => 'Waktu akhir check-out harus setelah waktu mulai check-out',
            ];

            // Validasi berurutan
            $validator = Validator::make($request->all(), $rules, $messages);

            $validator->after(function ($validator) use ($request) {
                $checkInStart = $request->check_in_start;
                $checkInEnd = $request->check_in_end;
                $checkOutStart = $request->check_out_start;
                $checkOutEnd = $request->check_out_end;

                // Check in start harus sebelum check in end
                if ($checkInStart >= $checkInEnd) {
                    $validator->errors()->add('check_in_end', 'Waktu akhir check-in harus setelah waktu mulai check-in');
                }

                // Check in end harus sebelum check out start
                if ($checkInEnd >= $checkOutStart) {
                    $validator->errors()->add('check_out_start', 'Waktu mulai check-out harus setelah waktu akhir check-in');
                }

                // Check out start harus sebelum check out end
                if ($checkOutStart >= $checkOutEnd) {
                    $validator->errors()->add('check_out_end', 'Waktu akhir check-out harus setelah waktu mulai check-out');
                }
            });

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        } else {
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }

        $oldData = $schedule->toArray();

        $schedule->update([
            'is_working_day' => $request->has('is_working_day'),
            'check_in_start' => $request->is_working_day ? $request->check_in_start : null,
            'check_in_end' => $request->is_working_day ? $request->check_in_end : null,
            'check_out_start' => $request->is_working_day ? $request->check_out_start : null,
            'check_out_end' => $request->is_working_day ? $request->check_out_end : null,
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update_schedule',
            'description' => "Mengupdate jadwal kerja untuk hari " . $schedule->day_label,
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
        // Reset to default schedules
        $defaultSchedules = [
            ['day_of_week' => 'monday', 'check_in_start' => '08:00:00', 'check_in_end' => '08:30:00', 'check_out_start' => '17:00:00', 'check_out_end' => '18:00:00', 'is_working_day' => true],
            ['day_of_week' => 'tuesday', 'check_in_start' => '08:00:00', 'check_in_end' => '08:30:00', 'check_out_start' => '17:00:00', 'check_out_end' => '18:00:00', 'is_working_day' => true],
            ['day_of_week' => 'wednesday', 'check_in_start' => '08:00:00', 'check_in_end' => '08:30:00', 'check_out_start' => '17:00:00', 'check_out_end' => '18:00:00', 'is_working_day' => true],
            ['day_of_week' => 'thursday', 'check_in_start' => '08:00:00', 'check_in_end' => '08:30:00', 'check_out_start' => '17:00:00', 'check_out_end' => '18:00:00', 'is_working_day' => true],
            ['day_of_week' => 'friday', 'check_in_start' => '08:00:00', 'check_in_end' => '08:30:00', 'check_out_start' => '17:00:00', 'check_out_end' => '18:00:00', 'is_working_day' => true],
            ['day_of_week' => 'saturday', 'check_in_start' => '08:00:00', 'check_in_end' => '08:30:00', 'check_out_start' => '12:00:00', 'check_out_end' => '13:00:00', 'is_working_day' => false],
            ['day_of_week' => 'sunday', 'check_in_start' => '00:00:00', 'check_in_end' => '00:00:00', 'check_out_start' => '00:00:00', 'check_out_end' => '00:00:00', 'is_working_day' => false],
        ];

        foreach ($defaultSchedules as $schedule) {
            WorkSchedule::updateOrCreate(
                ['day_of_week' => $schedule['day_of_week']],
                $schedule
            );
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'reset_schedule',
            'description' => "Merest jadwal kerja ke default",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

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
