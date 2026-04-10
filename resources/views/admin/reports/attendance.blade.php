@extends('layouts.app')

@section('title', 'Laporan Absensi')
@section('page-title', 'Laporan Absensi')

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-2 text-muted">Total Absensi</h6>
                        <h3 class="mb-0">{{ number_format($totalAttendance ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-2 text-muted">Hadir</h6>
                        <h3 class="mb-0 text-success">{{ number_format($totalPresent ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-2 text-muted">Terlambat</h6>
                        <h3 class="mb-0 text-warning">{{ number_format($totalLate ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-2 text-muted">Tidak Hadir</h6>
                        <h3 class="mb-0 text-danger">{{ number_format($totalAbsent ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-user-slash"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="mt-4 row">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-filter text-primary"></i> Filter Laporan
                </h5>
                <form method="GET" action="{{ route('admin.reports.attendance') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date', $startDate ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date', $endDate ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Pegawai</label>
                        <select name="user_id" class="form-select">
                            <option value="">Semua Pegawai</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->employee_id ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Hadir</option>
                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                            <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                            <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Setengah Hari</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.reports.attendance') }}" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                        <a href="{{ route('admin.reports.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Tambah Absensi
                        </a>
                        <a href="{{ route('admin.reports.export', request()->query()) }}" class="btn btn-info">
                            <i class="fas fa-download"></i> Export
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="mt-4 row">
        <div class="col-12">
            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Pegawai</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Keterlambatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $index => $attendance)
                            <tr>
                                <td>{{ $attendances->firstItem() + $index }}</td>
                                <td>{{ $attendance->attendance_date->format('d/m/Y') }}</td>
                                <td>
                                    <strong>{{ $attendance->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $attendance->user->employee_id ?? '-' }}</small>
                                </td>
                                <td>
                                    @if($attendance->check_in_time)
                                        <span class="text-success">{{ $attendance->check_in_time }}</span>
                                        @if($attendance->check_in_photo)
                                            <br>
                                            <a href="{{ Storage::url($attendance->check_in_photo) }}" target="_blank" class="small">
                                                <i class="fas fa-camera"></i> Foto
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->check_out_time)
                                        <span class="text-info">{{ $attendance->check_out_time }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badgeColor = [
                                            'present' => 'success',
                                            'late' => 'warning',
                                            'absent' => 'danger',
                                            'half_day' => 'info'
                                        ][$attendance->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }}">
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($attendance->late_minutes > 0)
                                        <span class="text-warning">{{ $attendance->late_minutes }} menit</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.reports.edit', $attendance->id) }}"
                                           class="text-white btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-danger"
                                                onclick="deleteAttendance({{ $attendance->id }})"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="py-4 text-center">
                                    <i class="mb-2 fas fa-calendar-times fa-2x text-muted d-block"></i>
                                    Tidak ada data absensi
                                    @if(request()->anyFilled(['start_date', 'end_date', 'user_id', 'status']))
                                        <div class="mt-2">
                                            <a href="{{ route('admin.reports.attendance') }}" class="btn btn-sm btn-primary-custom">
                                                <i class="fas fa-undo"></i> Reset Filter
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $attendances->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545, #c82333); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus absensi ini?</p>
                <p class="mb-0 text-danger">Tindakan ini tidak dapat dibatalkan!</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteAttendance(id) {
    const form = document.getElementById('deleteForm');
    form.action = "{{ url('admin/reports/attendance') }}/" + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
