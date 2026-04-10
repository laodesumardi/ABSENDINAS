<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'check_in_photo',
        'check_out_photo',
        'status',
        'late_minutes',
        'early_checkout_minutes',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime:H:i:s',
        'check_out_time' => 'datetime:H:i:s',
        'check_in_latitude' => 'decimal:8',
        'check_in_longitude' => 'decimal:8',
        'check_out_latitude' => 'decimal:8',
        'check_out_longitude' => 'decimal:8',
        'late_minutes' => 'integer',
        'early_checkout_minutes' => 'integer',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('attendance_date', today());
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('attendance_date', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeLate($query)
    {
        return $query->where('late_minutes', '>', 0);
    }

    // Accessors
    public function getIsCheckedInAttribute()
    {
        return !is_null($this->check_in_time);
    }

    public function getIsCheckedOutAttribute()
    {
        return !is_null($this->check_out_time);
    }

    public function getWorkDurationAttribute()
    {
        if ($this->check_in_time && $this->check_out_time) {
            $checkIn = strtotime($this->check_in_time);
            $checkOut = strtotime($this->check_out_time);
            $diff = $checkOut - $checkIn;
            return gmdate('H:i', $diff);
        }
        return '00:00';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'present' => 'success',
            'late' => 'warning',
            'absent' => 'danger',
            'half_day' => 'info',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    // Mutators
    public function setCheckInTimeAttribute($value)
    {
        $this->attributes['check_in_time'] = $value;

        // Auto-calculate late minutes if check_in_end is defined
        if ($this->attendance_date) {
            $schedule = WorkSchedule::where('day_of_week', strtolower($this->attendance_date->format('l')))->first();
            if ($schedule && $value > $schedule->check_in_end) {
                $lateMinutes = now()->setTimeFromTimeString($value)
                    ->diffInMinutes(now()->setTimeFromTimeString($schedule->check_in_end));
                $this->attributes['late_minutes'] = $lateMinutes;
                $this->attributes['status'] = $lateMinutes > 0 ? 'late' : 'present';
            }
        }
    }
}
