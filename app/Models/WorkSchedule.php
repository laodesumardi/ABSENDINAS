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

        if (!$this->check_in_start) {
            return 'Belum diatur';
        }

        return 'Mulai jam ' . date('H:i', strtotime($this->check_in_start));
    }

    public function getCheckOutWindowAttribute()
    {
        if (!$this->is_working_day) {
            return 'Libur';
        }

        if (!$this->check_out_start) {
            return 'Belum diatur';
        }

        return 'Mulai jam ' . date('H:i', strtotime($this->check_out_start));
    }

    public function getLateThresholdAttribute()
    {
        if (!$this->is_working_day || !$this->check_in_end) {
            return null;
        }
        return date('H:i', strtotime($this->check_in_end));
    }

    public function getWorkingHoursAttribute()
    {
        if (!$this->is_working_day) {
            return 'Libur';
        }

        if (!$this->check_in_start || !$this->check_out_start) {
            return '-';
        }

        $start = Carbon::parse($this->check_in_start);
        $end = Carbon::parse($this->check_out_start);
        $diff = $start->diff($end);
        return $diff->format('%h jam %i menit');
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_working_day ? 'success' : 'secondary';
    }

    public function getStatusTextAttribute()
    {
        return $this->is_working_day ? 'Hari Kerja' : 'Libur';
    }

    public static function getTodaySchedule()
    {
        $today = strtolower(now()->format('l'));
        $schedule = self::where('day_of_week', $today)->first();

        return $schedule;
    }

    public static function getScheduleByDate($date)
    {
        $day = strtolower(Carbon::parse($date)->format('l'));
        return self::where('day_of_week', $day)->first();
    }
}
