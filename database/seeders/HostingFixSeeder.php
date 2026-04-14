<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class HostingFixSeeder extends Seeder
{
    public function run()
    {
        // Perbaiki data attendance
        DB::table('attendances')
            ->where('late_minutes', '<', 0)
            ->orWhere('late_minutes', '>', 600)
            ->update([
                'late_minutes' => 0,
                'status' => 'present',
                'updated_at' => now()
            ]);

        // Reset jadwal kerja
        DB::table('work_schedules')->truncate();

        $schedules = [
            ['day_of_week' => 'monday', 'check_in_start' => '08:00:00', 'check_in_end' => '08:30:00', 'check_out_start' => '17:00:00', 'check_out_end' => '18:00:00', 'is_working_day' => 1],
            ['day_of_week' => 'tuesday', 'check_in_start' => '08:00:00', 'check_in_end' => '08:30:00', 'check_out_start' => '17:00:00', 'check_out_end' => '18:00:00', 'is_working_day' => 1],
            ['day_of_week' => 'wednesday', 'check_in_start' => '08:00:00', 'check_in_end' => '08:30:00', 'check_out_start' => '17:00:00', 'check_out_end' => '18:00:00', 'is_working_day' => 1],
            ['day_of_week' => 'thursday', 'check_in_start' => '08:00:00', 'check_in_end' => '08:30:00', 'check_out_start' => '17:00:00', 'check_out_end' => '18:00:00', 'is_working_day' => 1],
            ['day_of_week' => 'friday', 'check_in_start' => '08:00:00', 'check_in_end' => '08:30:00', 'check_out_start' => '17:00:00', 'check_out_end' => '18:00:00', 'is_working_day' => 1],
            ['day_of_week' => 'saturday', 'check_in_start' => '08:00:00', 'check_in_end' => '12:00:00', 'check_out_start' => '12:00:00', 'check_out_end' => '13:00:00', 'is_working_day' => 0],
            ['day_of_week' => 'sunday', 'check_in_start' => '00:00:00', 'check_in_end' => '00:00:00', 'check_out_start' => '00:00:00', 'check_out_end' => '00:00:00', 'is_working_day' => 0],
        ];

        foreach ($schedules as $schedule) {
            DB::table('work_schedules')->updateOrInsert(
                ['day_of_week' => $schedule['day_of_week']],
                array_merge($schedule, ['updated_at' => now()])
            );
        }

        // Clear cache
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');

        echo "Database telah diperbaiki!\n";
    }
}
