<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'old_data',
        'new_data',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Accessors
    public function getActionLabelAttribute()
    {
        $labels = [
            'create_user' => 'Membuat User',
            'update_user' => 'Mengupdate User',
            'delete_user' => 'Menghapus User',
            'approve_leave' => 'Menyetujui Izin',
            'reject_leave' => 'Menolak Izin',
            'check_in' => 'Check In',
            'check_out' => 'Check Out',
            'update_profile' => 'Update Profile',
            'create_location' => 'Membuat Lokasi',
            'update_location' => 'Update Lokasi',
            'delete_location' => 'Hapus Lokasi',
            'update_schedule' => 'Update Jadwal',
            'reset_schedule' => 'Reset Jadwal',
            'create_holiday' => 'Membuat Hari Libur',
            'update_holiday' => 'Update Hari Libur',
            'delete_holiday' => 'Hapus Hari Libur',
            'bulk_delete_holidays' => 'Hapus Massal Hari Libur',
            'import_holidays' => 'Import Hari Libur',
            'login' => 'Login',
            'logout' => 'Logout',
        ];

        return $labels[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }

    public function getActionIconAttribute()
    {
        $icons = [
            'create_user' => 'fas fa-user-plus text-success',
            'update_user' => 'fas fa-user-edit text-warning',
            'delete_user' => 'fas fa-user-minus text-danger',
            'approve_leave' => 'fas fa-check-circle text-success',
            'reject_leave' => 'fas fa-times-circle text-danger',
            'check_in' => 'fas fa-sign-in-alt text-primary',
            'check_out' => 'fas fa-sign-out-alt text-info',
            'update_profile' => 'fas fa-user-edit text-warning',
            'create_location' => 'fas fa-map-marker-alt text-success',
            'update_location' => 'fas fa-map-marked-alt text-warning',
            'delete_location' => 'fas fa-map-marker-alt text-danger',
            'update_schedule' => 'fas fa-calendar-alt text-warning',
            'reset_schedule' => 'fas fa-undo-alt text-info',
            'create_holiday' => 'fas fa-gift text-success',
            'update_holiday' => 'fas fa-gift text-warning',
            'delete_holiday' => 'fas fa-gift text-danger',
            'login' => 'fas fa-sign-in-alt text-success',
            'logout' => 'fas fa-sign-out-alt text-secondary',
        ];

        return $icons[$this->action] ?? 'fas fa-history text-secondary';
    }

    public function getHumanReadableTimeAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('d F Y H:i:s');
    }

    public function getDeviceInfoAttribute()
    {
        $browser = $this->getBrowser();
        $os = $this->getOperatingSystem();
        return "{$browser} - {$os}";
    }

    // Method untuk mendapatkan browser
    public function getBrowser()
    {
        $userAgent = $this->user_agent;
        if (empty($userAgent)) return 'Unknown';

        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        if (strpos($userAgent, 'Edge') !== false) return 'Edge';
        if (strpos($userAgent, 'MSIE') !== false) return 'Internet Explorer';
        if (strpos($userAgent, 'Opera') !== false) return 'Opera';

        return 'Unknown';
    }

    // Method untuk mendapatkan operating system
    public function getOperatingSystem()
    {
        $userAgent = $this->user_agent;
        if (empty($userAgent)) return 'Unknown';

        if (strpos($userAgent, 'Windows') !== false) return 'Windows';
        if (strpos($userAgent, 'Mac') !== false) return 'MacOS';
        if (strpos($userAgent, 'Linux') !== false) return 'Linux';
        if (strpos($userAgent, 'Android') !== false) return 'Android';
        if (strpos($userAgent, 'iOS') !== false) return 'iOS';
        if (strpos($userAgent, 'iPhone') !== false) return 'iOS';
        if (strpos($userAgent, 'iPad') !== false) return 'iOS';

        return 'Unknown';
    }

    // Helper untuk mendapatkan icon browser
    public function getBrowserIcon()
    {
        $browser = $this->getBrowser();
        $icons = [
            'Chrome' => 'fab fa-chrome',
            'Firefox' => 'fab fa-firefox',
            'Safari' => 'fab fa-safari',
            'Edge' => 'fab fa-edge',
            'Opera' => 'fab fa-opera',
            'Internet Explorer' => 'fab fa-internet-explorer',
        ];

        return $icons[$browser] ?? 'fas fa-globe';
    }
}
