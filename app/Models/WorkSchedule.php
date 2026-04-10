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
        'check_in_start' => 'datetime:H:i:s',
        'check_in_end' => 'datetime:H:i:s',
        'check_out_start' => 'datetime:H:i:s',
        'check_out_end' => 'datetime:H:i:s',
        'is_working_day' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Day mapping
    public static $days = [
        'monday' => 'Senin',
        'tuesday' => 'Selasa',
        'wednesday' => 'Rabu',
        'thursday' => 'Kamis',
        'friday' => 'Jumat',
        'saturday' => 'Sabtu',
        'sunday' => 'Minggu'
    ];

    // Scopes
    public function scopeWorkingDay($query)
    {
        return $query->where('is_working_day', true);
    }

    public function scopeByDay($query, $day)
    {
        return $query->where('day_of_week', strtolower($day));
    }

    // Accessors
    public function getDayLabelAttribute()
    {
        return self::$days[$this->day_of_week] ?? ucfirst($this->day_of_week);
    }

    public function getCheckInWindowAttribute()
    {
        if (!$this->is_working_day) {
            return 'Libur';
        }
        return $this->check_in_start->format('H:i') . ' - ' . $this->check_in_end->format('H:i');
    }

    public function getCheckOutWindowAttribute()
    {
        if (!$this->is_working_day) {
            return 'Libur';
        }
        return $this->check_out_start->format('H:i') . ' - ' . $this->check_out_end->format('H:i');
    }

    public function getWorkingHoursAttribute()
    {
        if (!$this->is_working_day) {
            return 'Libur';
        }

        $start = Carbon::parse($this->check_in_start);
        $end = Carbon::parse($this->check_out_end);
        $diff = $start->diff($end);

        return $diff->format('%H jam %i menit');
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_working_day ? 'success' : 'secondary';
    }

    public function getStatusTextAttribute()
    {
        return $this->is_working_day ? 'Hari Kerja' : 'Libur';
    }

    // Check if current time is within check-in window
    public function isCheckInTime($time = null)
    {
        if (!$this->is_working_day) {
            return false;
        }

        $time = $time ? Carbon::parse($time) : Carbon::now();
        $checkInStart = Carbon::parse($this->check_in_start);
        $checkInEnd = Carbon::parse($this->check_in_end);

        return $time->between($checkInStart, $checkInEnd);
    }

    // Check if current time is within check-out window
    public function isCheckOutTime($time = null)
    {
        if (!$this->is_working_day) {
            return false;
        }

        $time = $time ? Carbon::parse($time) : Carbon::now();
        $checkOutStart = Carbon::parse($this->check_out_start);
        $checkOutEnd = Carbon::parse($this->check_out_end);

        return $time->between($checkOutStart, $checkOutEnd);
    }

    // Get late minutes
    public function getLateMinutes($checkInTime)
    {
        if (!$this->is_working_day) {
            return 0;
        }

        $checkIn = Carbon::parse($checkInTime);
        $checkInEnd = Carbon::parse($this->check_in_end);

        if ($checkIn > $checkInEnd) {
            return $checkIn->diffInMinutes($checkInEnd);
        }

        return 0;
    }

    // Get early checkout minutes
    public function getEarlyCheckoutMinutes($checkOutTime)
    {
        if (!$this->is_working_day) {
            return 0;
        }

        $checkOut = Carbon::parse($checkOutTime);
        $checkOutStart = Carbon::parse($this->check_out_start);

        if ($checkOut < $checkOutStart) {
            return $checkOutStart->diffInMinutes($checkOut);
        }

        return 0;
    }

    // Get schedule for today
    public static function getTodaySchedule()
    {
        $today = strtolower(now()->format('l'));
        return self::where('day_of_week', $today)->first();
    }

    // Get schedule for specific date
    public static function getScheduleByDate($date)
    {
        $day = strtolower(Carbon::parse($date)->format('l'));
        return self::where('day_of_week', $day)->first();
    }
}
