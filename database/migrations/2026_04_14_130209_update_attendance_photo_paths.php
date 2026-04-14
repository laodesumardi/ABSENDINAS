<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Hapus prefix 'storage/' jika ada
        DB::table('attendances')
            ->where('check_in_photo', 'like', 'storage/%')
            ->update([
                'check_in_photo' => DB::raw("REPLACE(check_in_photo, 'storage/', '')")
            ]);

        DB::table('attendances')
            ->where('check_out_photo', 'like', 'storage/%')
            ->update([
                'check_out_photo' => DB::raw("REPLACE(check_out_photo, 'storage/', '')")
            ]);
    }
};
