<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WorkSchedule extends Model
{
    use HasFactory;

    protected $table = 'work_schedules';

    protected $fillable = [
        'day_of_week',
        'check_in_start',
        'check_in_end',
        'check_out_start',
        'check_out_end',
        'is_working_day',
    ];

    protected $casts = [
        'is_working_day' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static $days = [
        'monday' => 'Senin',
        'tuesday' => 'Selasa',
        'wednesday' => 'Rabu',
        'thursday' => 'Kamis',
        'friday' => 'Jumat',
        'saturday' => 'Sabtu',
        'sunday' => 'Minggu'
    ];

    public function getDayLabelAttribute()
    {
        return self::$days[$this->day_of_week] ?? ucfirst($this->day_of_week);
    }

    public function getCheckInWindowAttribute()
    {
        if (!$this->is_working_day) {
            return 'Libur';
        }

        $start = $this->check_in_start ? date('H:i', strtotime($this->check_in_start)) : '00:00';
        $end = $this->check_in_end ? date('H:i', strtotime($this->check_in_end)) : '00:00';
        return $start . ' - ' . $end;
    }

    public function getCheckOutWindowAttribute()
    {
        if (!$this->is_working_day) {
            return 'Libur';
        }

        $start = $this->check_out_start ? date('H:i', strtotime($this->check_out_start)) : '00:00';
        $end = $this->check_out_end ? date('H:i', strtotime($this->check_out_end)) : '00:00';
        return $start . ' - ' . $end;
    }

    public function getWorkingHoursAttribute()
    {
        if (!$this->is_working_day) {
            return 'Libur';
        }

        if (!$this->check_in_start || !$this->check_out_end) {
            return '-';
        }

        $start = Carbon::parse($this->check_in_start);
        $end = Carbon::parse($this->check_out_end);
        $diff = $start->diff($end);
        return $diff->format('%h jam %i menit');
    }

    public static function getTodaySchedule()
    {
        $today = strtolower(now()->format('l'));
        $schedule = self::where('day_of_week', $today)->first();

        // Jika tidak ada schedule, buat default
        if (!$schedule) {
            $schedule = new self();
            $schedule->is_working_day = true;
            $schedule->check_in_start = '08:00:00';
            $schedule->check_in_end = '16:00:00';
            $schedule->check_out_start = '17:00:00';
            $schedule->check_out_end = '18:00:00';
        }

        return $schedule;
    }

    public static function getScheduleByDate($date)
    {
        $day = strtolower(Carbon::parse($date)->format('l'));
        return self::where('day_of_week', $day)->first();
    }
}
