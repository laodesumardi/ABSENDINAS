<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkLocationsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('work_locations')->insert([
            [
                'name' => 'Kantor Pusat',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius' => 100,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kantor Cabang Bandung',
                'address' => 'Jl. Merdeka No. 45, Bandung',
                'latitude' => -6.914744,
                'longitude' => 107.609810,
                'radius' => 150,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
