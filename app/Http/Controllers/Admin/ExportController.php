<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\ActivityLog;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExportController extends Controller
{
    protected $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    public function exportUsers(Request $request)
    {
        $role = $request->filled('role') ? $request->role : null;
        $status = $request->filled('status') ? $request->status : null;

        $users = User::when($role, function ($q) use ($role) {
            return $q->where('role', $role);
        })
            ->when($status, function ($q) use ($status) {
                return $q->where('is_active', $status == 'active');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                $user->employee_id ?? '-',
                $user->name,
                $user->email,
                $user->role,
                $user->position ?? '-',
                $user->department ?? '-',
                $user->phone ?? '-',
                $user->is_active ? 'Aktif' : 'Nonaktif',
                $user->created_at->format('d/m/Y'),
                $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : '-',
            ];
        }

        $headers = ['ID Pegawai', 'Nama', 'Email', 'Role', 'Posisi', 'Departemen', 'Telepon', 'Status', 'Bergabung', 'Terakhir Login'];
        $filename = "data_user_" . Carbon::now()->format('d-m-Y_H-i-s');

        return $this->exportService->exportToCSV($data, $headers, $filename);
    }

    public function exportActivityLog(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->start_date : Carbon::now()->subDays(30)->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : Carbon::now()->format('Y-m-d');

        $logs = ActivityLog::with('user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [];
        foreach ($logs as $log) {
            $data[] = [
                $log->created_at->format('d/m/Y H:i:s'),
                $log->user->name ?? 'System',
                $log->user->role ?? '-',
                $log->action_label,
                $log->description,
                $log->ip_address ?? '-',
                $log->user_agent ?? '-',
            ];
        }

        $headers = ['Waktu', 'User', 'Role', 'Aksi', 'Deskripsi', 'IP Address', 'User Agent'];
        $filename = "log_aktivitas_" . Carbon::parse($startDate)->format('d-m-Y') . "_sd_" . Carbon::parse($endDate)->format('d-m-Y');

        return $this->exportService->exportToCSV($data, $headers, $filename);
    }
}
