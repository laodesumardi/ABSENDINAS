<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class FixDatabaseSeeder extends Seeder
{
    public function run()
    {
        echo "========================================\n";
        echo "MEMPERBAIKI DATABASE ABSENSI\n";
        echo "========================================\n\n";

        // 1. Perbaiki attendance dengan late_minutes negatif
        echo "1. Memperbaiki data attendance dengan late_minutes negatif...\n";
        $fixedNegatif = DB::table('attendances')
            ->where('late_minutes', '<', 0)
            ->update([
                'late_minutes' => 0,
                'status' => 'present',
                'updated_at' => now()
            ]);
        echo "   - $fixedNegatif data attendance diperbaiki\n\n";

        // 2. Perbaiki attendance dengan late_minutes terlalu besar (> 600 menit / 10 jam)
        echo "2. Memperbaiki data attendance dengan late_minutes terlalu besar...\n";
        $fixedTerlaluBesar = DB::table('attendances')
            ->where('late_minutes', '>', 600)
            ->update([
                'late_minutes' => 0,
                'status' => 'present',
                'updated_at' => now()
            ]);
        echo "   - $fixedTerlaluBesar data attendance diperbaiki\n\n";

        // 3. Set status null menjadi present
        echo "3. Memperbaiki data attendance dengan status null...\n";
        $fixedNullStatus = DB::table('attendances')
            ->whereNull('status')
            ->update([
                'status' => 'present',
                'updated_at' => now()
            ]);
        echo "   - $fixedNullStatus data attendance diperbaiki\n\n";

        // 4. Perbaiki jadwal kerja yang tidak valid
        echo "4. Memperbaiki jadwal kerja...\n";

        // Reset semua jadwal ke default
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
            DB::table('work_schedules')->insert(array_merge($schedule, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
        echo "   - Jadwal kerja telah direset ke default\n\n";

        // 5. Pastikan ada lokasi kerja aktif
        echo "5. Memastikan ada lokasi kerja aktif...\n";
        $workLocation = DB::table('work_locations')->where('is_active', 1)->first();

        if (!$workLocation) {
            DB::table('work_locations')->insert([
                'name' => 'Kantor Pusat',
                'address' => 'Jl. Contoh No. 123, Jakarta',
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius' => 100,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "   - Lokasi kerja aktif telah ditambahkan\n\n";
        } else {
            echo "   - Lokasi kerja aktif sudah ada: {$workLocation->name}\n\n";
        }

        // 6. Update user yang tidak memiliki employee_id
        echo "6. Memperbaiki user yang tidak memiliki employee_id...\n";
        $users = DB::table('users')->where('role', 'employee')->whereNull('employee_id')->get();
        $fixedUsers = 0;

        foreach ($users as $user) {
            $newEmployeeId = 'EMP' . str_pad($user->id, 3, '0', STR_PAD_LEFT);
            DB::table('users')->where('id', $user->id)->update([
                'employee_id' => $newEmployeeId,
                'updated_at' => now()
            ]);
            $fixedUsers++;
        }
        echo "   - $fixedUsers user diperbaiki\n\n";

        // 7. Hapus cache
        echo "7. Membersihkan cache...\n";
        \Illuminate\Support\Facades\Cache::flush();
        echo "   - Cache telah dibersihkan\n\n";

        echo "========================================\n";
        echo "DATABASE TELAH DIPERBAIKI!\n";
        echo "========================================\n";
    }
}
