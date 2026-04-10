<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LeaveApprovalController extends Controller
{
    public function index(Request $request)
    {
        $query = Leave::with('user');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default show pending first
            $query->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected', 'cancelled')");
        }

        // Filter by leave type
        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        // Search by employee name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $leaves = $query->orderBy('created_at', 'desc')->paginate(15);

        // Statistics
        $stats = [
            'total' => Leave::count(),
            'pending' => Leave::where('status', 'pending')->count(),
            'approved' => Leave::where('status', 'approved')->count(),
            'rejected' => Leave::where('status', 'rejected')->count(),
            'cancelled' => Leave::where('status', 'cancelled')->count(),
            'total_days' => Leave::where('status', 'approved')->sum('total_days'),
        ];

        // Recent activities
        $recentActivities = ActivityLog::where('action', 'like', '%leave%')
            ->orWhere('action', 'approve_leave')
            ->orWhere('action', 'reject_leave')
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('operator.leaves.index', compact('leaves', 'stats', 'recentActivities'));
    }

    public function show(Leave $leave)
    {
        return view('operator.leaves.show', compact('leave'));
    }

    public function approve(Request $request, Leave $leave)
    {
        if ($leave->status !== 'pending') {
            return redirect()->back()->with('error', 'Pengajuan ini sudah diproses!');
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'approve_leave',
            'description' => "Menyetujui pengajuan izin/cuti dari {$leave->user->name} ({$leave->leave_type_label})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_data' => json_encode([
                'leave_id' => $leave->id,
                'user_id' => $leave->user_id,
                'leave_type' => $leave->leave_type,
                'date_range' => $leave->date_range
            ]),
        ]);

        return redirect()->route('operator.leaves.index')
            ->with('success', 'Pengajuan izin berhasil disetujui!');
    }

    public function reject(Request $request, Leave $leave)
    {
        if ($leave->status !== 'pending') {
            return redirect()->back()->with('error', 'Pengajuan ini sudah diproses!');
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|min:10|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $leave->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'reject_leave',
            'description' => "Menolak pengajuan izin/cuti dari {$leave->user->name} ({$leave->leave_type_label})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_data' => json_encode([
                'leave_id' => $leave->id,
                'user_id' => $leave->user_id,
                'leave_type' => $leave->leave_type,
                'date_range' => $leave->date_range,
                'reason' => $request->rejection_reason
            ]),
        ]);

        return redirect()->route('operator.leaves.index')
            ->with('success', 'Pengajuan izin berhasil ditolak!');
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:leaves,id',
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            $leave = Leave::find($id);
            if ($leave && $leave->status === 'pending') {
                $leave->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);
                $count++;
            }
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_approve_leaves',
            'description' => "Menyetujui {$count} pengajuan izin/cuti secara massal",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('operator.leaves.index')
            ->with('success', "{$count} pengajuan izin berhasil disetujui!");
    }

    public function export(Request $request)
    {
        $query = Leave::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        $leaves = $query->orderBy('created_at', 'desc')->get();

        $filename = "pengajuan_izin_" . now()->format('Y-m-d_H-i-s') . ".csv";

        $handle = fopen('php://temp', 'w');
        fwrite($handle, "\xEF\xBB\xBF");

        // Headers
        fputcsv($handle, [
            'No',
            'Tanggal Pengajuan',
            'Nama Pegawai',
            'ID Pegawai',
            'Jenis Izin',
            'Tanggal Mulai',
            'Tanggal Akhir',
            'Durasi',
            'Alasan',
            'Status',
            'Tanggal Diproses',
            'Alasan Penolakan'
        ]);

        $no = 1;
        foreach ($leaves as $leave) {
            fputcsv($handle, [
                $no++,
                $leave->created_at->format('d/m/Y H:i'),
                $leave->user->name,
                $leave->user->employee_id ?? '-',
                $leave->leave_type_label,
                $leave->start_date->format('d/m/Y'),
                $leave->end_date->format('d/m/Y'),
                $leave->total_days . ' hari',
                $leave->reason,
                $leave->status_text,
                $leave->approved_at ? $leave->approved_at->format('d/m/Y H:i') : '-',
                $leave->rejection_reason ?? '-'
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function pendingCount()
    {
        $count = Leave::where('status', 'pending')->count();
        return response()->json(['count' => $count]);
    }
}
