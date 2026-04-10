<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Holiday extends Model
{
    use HasFactory;

    protected $table = 'holidays';

    protected $fillable = [
        'name',
        'holiday_date',
        'is_national_holiday',
        'description',
    ];

    protected $casts = [
        'holiday_date' => 'date',
        'is_national_holiday' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('holiday_date', '>=', today());
    }

    public function scopePast($query)
    {
        return $query->where('holiday_date', '<', today());
    }

    public function scopeByYear($query, $year)
    {
        return $query->whereYear('holiday_date', $year);
    }

    public function scopeByMonth($query, $month)
    {
        return $query->whereMonth('holiday_date', $month);
    }

    public function scopeNational($query)
    {
        return $query->where('is_national_holiday', true);
    }

    // Accessors with null checks
    public function getFormattedDateAttribute()
    {
        if (!$this->holiday_date) {
            return '-';
        }
        return $this->holiday_date->format('d F Y');
    }

    public function getDayNameAttribute()
    {
        if (!$this->holiday_date) {
            return '-';
        }
        return $this->holiday_date->format('l');
    }

    public function getDayNameIndonesianAttribute()
    {
        if (!$this->holiday_date) {
            return '-';
        }

        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];

        $dayName = $this->holiday_date->format('l');
        return $days[$dayName] ?? $dayName;
    }

    public function getMonthNameAttribute()
    {
        if (!$this->holiday_date) {
            return '-';
        }
        return $this->holiday_date->format('F');
    }

    public function getYearAttribute()
    {
        if (!$this->holiday_date) {
            return date('Y');
        }
        return $this->holiday_date->format('Y');
    }

    public function getIsUpcomingAttribute()
    {
        if (!$this->holiday_date) {
            return false;
        }
        return $this->holiday_date->isFuture();
    }

    public function getIsTodayAttribute()
    {
        if (!$this->holiday_date) {
            return false;
        }
        return $this->holiday_date->isToday();
    }

    public function getDaysLeftAttribute()
    {
        if (!$this->holiday_date || !$this->holiday_date->isFuture()) {
            return 0;
        }
        return $this->holiday_date->diffInDays(today());
    }

    public function getTypeBadgeAttribute()
    {
        return $this->is_national_holiday ? 'danger' : 'warning';
    }

    public function getTypeTextAttribute()
    {
        return $this->is_national_holiday ? 'Hari Libur Nasional' : 'Cuti Bersama';
    }

    // Check if a date is a holiday
    public static function isHoliday($date)
    {
        return self::where('holiday_date', $date)->exists();
    }

    public static function getHolidayByDate($date)
    {
        return self::where('holiday_date', $date)->first();
    }

    public static function getUpcomingHolidays($limit = 5)
    {
        return self::upcoming()->orderBy('holiday_date', 'asc')->limit($limit)->get();
    }

    public static function getHolidaysByMonth($year, $month)
    {
        return self::whereYear('holiday_date', $year)
            ->whereMonth('holiday_date', $month)
            ->orderBy('holiday_date', 'asc')
            ->get();
    }
}
