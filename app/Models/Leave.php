<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Leave extends Model
{
    use HasFactory;

    protected $table = 'leaves';

    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'attachment',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'integer',
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
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate])
            ->orWhereBetween('end_date', [$startDate, $endDate]);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('leave_type', $type);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'secondary',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getStatusTextAttribute()
    {
        $texts = [
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ];

        return $texts[$this->status] ?? $this->status;
    }

    public function getLeaveTypeLabelAttribute()
    {
        $types = [
            'annual' => 'Cuti Tahunan',
            'sick' => 'Sakit',
            'personal' => 'Keperluan Pribadi',
            'emergency' => 'Darurat',
            'maternity' => 'Cuti Melahirkan',
            'other' => 'Lainnya',
        ];

        return $types[$this->leave_type] ?? $this->leave_type;
    }

    public function getLeaveTypeIconAttribute()
    {
        $icons = [
            'annual' => 'fas fa-umbrella-beach',
            'sick' => 'fas fa-thermometer-half',
            'personal' => 'fas fa-user',
            'emergency' => 'fas fa-ambulance',
            'maternity' => 'fas fa-baby',
            'other' => 'fas fa-file-alt',
        ];

        return $icons[$this->leave_type] ?? 'fas fa-calendar-alt';
    }

    public function getDateRangeAttribute()
    {
        if ($this->start_date == $this->end_date) {
            return $this->start_date->format('d M Y');
        }

        return $this->start_date->format('d M Y') . ' - ' . $this->end_date->format('d M Y');
    }

    public function getFormattedStartDateAttribute()
    {
        return $this->start_date->format('d F Y');
    }

    public function getFormattedEndDateAttribute()
    {
        return $this->end_date->format('d F Y');
    }

    public function getDaysLeftAttribute()
    {
        if ($this->status == 'pending' && $this->start_date->isFuture()) {
            return $this->start_date->diffInDays(today());
        }
        return 0;
    }

    // Mutators
    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = $value;
        $this->calculateTotalDays();
    }

    public function setEndDateAttribute($value)
    {
        $this->attributes['end_date'] = $value;
        $this->calculateTotalDays();
    }

    protected function calculateTotalDays()
    {
        if ($this->start_date && $this->end_date) {
            $start = new \DateTime($this->start_date);
            $end = new \DateTime($this->end_date);
            $interval = $start->diff($end);
            $this->attributes['total_days'] = $interval->days + 1;
        }
    }
}
