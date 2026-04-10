<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $query = Leave::where('user_id', Auth::id());

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('leave_type', $request->type);
        }

        $leaves = $query->orderBy('created_at', 'desc')->paginate(10);

        // Statistics
        $stats = [
            'total' => Leave::where('user_id', Auth::id())->count(),
            'pending' => Leave::where('user_id', Auth::id())->where('status', 'pending')->count(),
            'approved' => Leave::where('user_id', Auth::id())->where('status', 'approved')->count(),
            'rejected' => Leave::where('user_id', Auth::id())->where('status', 'rejected')->count(),
            'total_days' => Leave::where('user_id', Auth::id())->where('status', 'approved')->sum('total_days'),
        ];

        return view('employee.leaves.index', compact('leaves', 'stats'));
    }

    public function create()
    {
        return view('employee.leaves.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'leave_type' => 'required|in:annual,sick,personal,emergency,maternity,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Calculate total days
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Handle attachment upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave-attachments/' . date('Y/m'), 'public');
        }

        $leave = Leave::create([
            'user_id' => Auth::id(),
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create_leave',
            'description' => "Mengajukan izin/cuti: {$leave->leave_type_label} ({$leave->date_range})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('employee.leaves.index')
            ->with('success', 'Pengajuan izin berhasil dikirim! Menunggu persetujuan operator.');
    }

    public function show(Leave $leave)
    {
        // Check if leave belongs to current user
        if ($leave->user_id != Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        // Calculate work duration if needed
        $duration = '';
        if ($leave->start_date && $leave->end_date) {
            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);
            $diff = $start->diff($end);
            $duration = $diff->days + 1;
        }

        return view('employee.leaves.show', compact('leave', 'duration'));
    }

    public function edit(Leave $leave)
    {
        // Only pending leaves can be edited
        if ($leave->user_id != Auth::id() || $leave->status != 'pending') {
            abort(403);
        }

        return view('employee.leaves.edit', compact('leave'));
    }
    public function update(Request $request, Leave $leave)
    {
        // Only pending leaves can be updated
        if ($leave->user_id != Auth::id() || $leave->status != 'pending') {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'leave_type' => 'required|in:annual,sick,personal,emergency,maternity,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Calculate total days
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Handle attachment upload
        if ($request->hasFile('attachment')) {
            // Delete old attachment
            if ($leave->attachment) {
                Storage::disk('public')->delete($leave->attachment);
            }
            $attachmentPath = $request->file('attachment')->store('leave-attachments/' . date('Y/m'), 'public');
            $leave->attachment = $attachmentPath;
        }

        $leave->update([
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update_leave',
            'description' => "Mengupdate pengajuan izin/cuti: {$leave->leave_type_label}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('employee.leaves.index')
            ->with('success', 'Pengajuan izin berhasil diupdate!');
    }

    public function destroy(Leave $leave)
    {
        // Only pending leaves can be deleted
        if ($leave->user_id != Auth::id() || $leave->status != 'pending') {
            abort(403);
        }

        // Delete attachment if exists
        if ($leave->attachment) {
            Storage::disk('public')->delete($leave->attachment);
        }

        $leave->delete();

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'cancel_leave',
            'description' => "Membatalkan pengajuan izin/cuti: {$leave->leave_type_label}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('employee.leaves.index')
            ->with('success', 'Pengajuan izin berhasil dibatalkan!');
    }

    public function cancel(Leave $leave)
    {
        // Only pending leaves can be cancelled
        if ($leave->user_id != Auth::id() || $leave->status != 'pending') {
            abort(403);
        }

        $leave->update(['status' => 'cancelled']);

        return redirect()->route('employee.leaves.index')
            ->with('success', 'Pengajuan izin berhasil dibatalkan!');
    }
}
