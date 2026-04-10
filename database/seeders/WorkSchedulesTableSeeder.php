<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkSchedulesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama
        DB::table('work_schedules')->truncate();

        $schedules = [
            [
                'day_of_week' => 'monday',
                'check_in_start' => '08:00:00',
                'check_in_end' => '08:30:00',
                'check_out_start' => '17:00:00',
                'check_out_end' => '18:00:00',
                'is_working_day' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'day_of_week' => 'tuesday',
                'check_in_start' => '08:00:00',
                'check_in_end' => '08:30:00',
                'check_out_start' => '17:00:00',
                'check_out_end' => '18:00:00',
                'is_working_day' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'day_of_week' => 'wednesday',
                'check_in_start' => '08:00:00',
                'check_in_end' => '08:30:00',
                'check_out_start' => '17:00:00',
                'check_out_end' => '18:00:00',
                'is_working_day' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'day_of_week' => 'thursday',
                'check_in_start' => '08:00:00',
                'check_in_end' => '08:30:00',
                'check_out_start' => '17:00:00',
                'check_out_end' => '18:00:00',
                'is_working_day' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'day_of_week' => 'friday',
                'check_in_start' => '08:00:00',
                'check_in_end' => '08:30:00',
                'check_out_start' => '17:00:00',
                'check_out_end' => '18:00:00',
                'is_working_day' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'day_of_week' => 'saturday',
                'check_in_start' => '08:00:00',
                'check_in_end' => '08:30:00',
                'check_out_start' => '12:00:00',
                'check_out_end' => '13:00:00',
                'is_working_day' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'day_of_week' => 'sunday',
                'check_in_start' => '00:00:00',
                'check_in_end' => '00:00:00',
                'check_out_start' => '00:00:00',
                'check_out_end' => '00:00:00',
                'is_working_day' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($schedules as $schedule) {
            DB::table('work_schedules')->insert($schedule);
        }
    }
}
