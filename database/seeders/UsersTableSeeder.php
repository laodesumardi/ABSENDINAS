<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin System',
                'email' => 'admin@absensi.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'employee_id' => 'ADM001',
                'position' => 'System Administrator',
                'department' => 'IT',
                'phone' => '081234567890',
                'is_active' => true,
            ],
            [
                'name' => 'Operator HRD',
                'email' => 'operator@absensi.com',
                'password' => Hash::make('password123'),
                'role' => 'operator',
                'employee_id' => 'OPR001',
                'position' => 'HRD Operator',
                'department' => 'Human Resources',
                'phone' => '081234567891',
                'is_active' => true,
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@absensi.com',
                'password' => Hash::make('password123'),
                'role' => 'employee',
                'employee_id' => 'EMP001',
                'position' => 'Staff Marketing',
                'department' => 'Marketing',
                'phone' => '081234567892',
                'is_active' => true,
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti@absensi.com',
                'password' => Hash::make('password123'),
                'role' => 'employee',
                'employee_id' => 'EMP002',
                'position' => 'Staff Finance',
                'department' => 'Finance',
                'phone' => '081234567893',
                'is_active' => true,
            ],
            [
                'name' => 'Joko Widodo',
                'email' => 'joko@absensi.com',
                'password' => Hash::make('password123'),
                'role' => 'employee',
                'employee_id' => 'EMP003',
                'position' => 'Staff IT',
                'department' => 'IT',
                'phone' => '081234567894',
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->insert(array_merge($user, [
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
