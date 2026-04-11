<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('work_schedules', function (Blueprint $table) {
            $table->time('check_in_start')->nullable()->change();
            $table->time('check_in_end')->nullable()->change();
            $table->time('check_out_start')->nullable()->change();
            $table->time('check_out_end')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('work_schedules', function (Blueprint $table) {
            $table->time('check_in_start')->nullable(false)->change();
            $table->time('check_in_end')->nullable(false)->change();
            $table->time('check_out_start')->nullable(false)->change();
            $table->time('check_out_end')->nullable(false)->change();
        });
    }
};
