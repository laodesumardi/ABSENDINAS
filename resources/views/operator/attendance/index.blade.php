@extends('layouts.app')

@section('title', 'Validasi Absensi')
@section('page-title', 'Validasi Absensi Pegawai')

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Absensi</h6>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
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
                        <h6 class="text-muted mb-2">Hadir / Tepat Waktu</h6>
                        <h3 class="mb-0 text-success">{{ $stats['present'] }}</h3>
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
                        <h6 class="text-muted mb-2">Terlambat</h6>
                        <h3 class="mb-0 text-warning">{{ $stats['late'] }}</h3>
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
                        <h6 class="text-muted mb-2">Belum Validasi</h6>
                        <h3 class="mb-0 text-danger">{{ $stats['not_validated'] }}</h3>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

  <!-- Filter Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="stat-card">
            <form method="GET" action="{{ route('operator.attendance.index') }}" class="row g-3">

                <!-- Tanggal -->
                <div class="col-md-2">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" class="form-control"
                        value="{{ request('date', date('Y-m-d')) }}">
                </div>

                <!-- Pegawai -->
                <div class="col-md-3">
                    <label class="form-label">Pegawai</label>
                    <select name="user_id" class="form-select">
                        <option value="">Semua Pegawai</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->employee_id ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Hadir</option>
                        <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                        <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                        <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Setengah Hari</option>
                    </select>
                </div>

                <!-- Validasi -->
                <div class="col-md-2">
                    <label class="form-label">Status Validasi</label>
                    <select name="validated" class="form-select">
                        <option value="">Semua</option>
                        <option value="yes" {{ request('validated') == 'yes' ? 'selected' : '' }}>Sudah Validasi</option>
                        <option value="no" {{ request('validated') == 'no' ? 'selected' : '' }}>Belum Validasi</option>
                    </select>
                </div>

                <!-- Tombol -->
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary-custom flex-fill">
                        <i class="fas fa-search"></i> Filter
                    </button>

                    <a href="{{ route('operator.attendance.create') }}"
                       class="btn btn-success flex-fill">
                        <i class="fas fa-plus"></i> Input Manual
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>
    <!-- Bulk Actions -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <input type="checkbox" id="selectAll">
                        <label class="ms-2">Pilih Semua</label>
                    </div>
                    <div>
                        <select id="bulkStatus" class="form-select d-inline-block w-auto">
                            <option value="">Ubah Status Menjadi...</option>
                            <option value="present">Hadir</option>
                            <option value="late">Terlambat</option>
                            <option value="absent">Tidak Hadir</option>
                            <option value="half_day">Setengah Hari</option>
                        </select>
                        <button type="button" class="btn btn-primary" id="bulkValidateBtn" style="display: none;">
                            <i class="fas fa-check-double"></i> Validasi Terpilih
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-primary"></i> Daftar Absensi
                    </h5>
                    <a href="{{ route('operator.attendance.export', request()->query()) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-download"></i> Export
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAllCheckbox">
                                </th>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Pegawai</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Validasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $index => $attendance)
                            <tr>
                                <td>
                                    <input type="checkbox" class="attendance-checkbox" value="{{ $attendance->id }}"
                                           data-status="{{ $attendance->status }}">
                                </td>
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
                                        @if($attendance->check_out_photo)
                                            <br>
                                            <a href="{{ Storage::url($attendance->check_out_photo) }}" target="_blank" class="small">
                                                <i class="fas fa-camera"></i> Foto
                                            </a>
                                        @endif
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
                                    @if($attendance->late_minutes > 0)
                                        <br>
                                        <small class="text-warning">{{ $attendance->late_minutes }} menit</small>
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->approved_by)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> Tervalidasi
                                        </span>
                                        <br>
                                        <small>{{ $attendance->approved_at->format('d/m/Y H:i') }}</small>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-hourglass-half"></i> Belum
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('operator.attendance.show', $attendance) }}"
                                           class="btn btn-sm btn-info text-white"
                                           title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(!$attendance->approved_by)
                                            <button type="button"
                                                    class="btn btn-sm btn-success"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#validateModal"
                                                    data-attendance-id="{{ $attendance->id }}"
                                                    data-attendance-name="{{ $attendance->user->name }}"
                                                    data-attendance-date="{{ $attendance->attendance_date->format('d/m/Y') }}"
                                                    title="Validasi">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('operator.attendance.edit', $attendance) }}"
                                           class="btn btn-sm btn-warning text-white"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-2x text-muted mb-2 d-block"></i>
                                    Tidak ada data absensi
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $attendances->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Validate Modal -->
<div class="modal fade" id="validateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #28a745, #20c997); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Validasi Absensi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="validateForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Validasi absensi untuk:</p>
                    <p><strong id="validateEmployeeName"></strong><br>
                    <small id="validateAttendanceDate" class="text-muted"></small></p>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="present">Hadir</option>
                            <option value="late">Terlambat</option>
                            <option value="absent">Tidak Hadir</option>
                            <option value="half_day">Setengah Hari</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Catatan validasi..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Validasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Validate Form -->
<form id="bulkValidateForm" method="POST" action="{{ route('operator.attendance.bulk-validate') }}">
    @csrf
    <input type="hidden" name="ids" id="bulkValidateIds">
    <input type="hidden" name="status" id="bulkValidateStatus">
</form>
@endsection

@push('scripts')
<script>
let selectedAttendanceId = null;

// Validate modal handler
const validateModal = document.getElementById('validateModal');
if (validateModal) {
    validateModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        selectedAttendanceId = button.getAttribute('data-attendance-id');
        const employeeName = button.getAttribute('data-attendance-name');
        const attendanceDate = button.getAttribute('data-attendance-date');

        document.getElementById('validateEmployeeName').textContent = employeeName;
        document.getElementById('validateAttendanceDate').textContent = attendanceDate;

        const form = document.getElementById('validateForm');
        form.action = "{{ url('operator/attendance') }}/" + selectedAttendanceId + "/validate";
    });
}

// Select all functionality
const selectAllCheckbox = document.getElementById('selectAllCheckbox');
const attendanceCheckboxes = document.querySelectorAll('.attendance-checkbox');
const bulkValidateBtn = document.getElementById('bulkValidateBtn');
const bulkStatus = document.getElementById('bulkStatus');

if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
        attendanceCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        toggleBulkValidateBtn();
    });
}

attendanceCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', toggleBulkValidateBtn);
});

function toggleBulkValidateBtn() {
    const checked = document.querySelectorAll('.attendance-checkbox:checked').length;
    if (bulkValidateBtn) {
        bulkValidateBtn.style.display = checked > 0 ? 'inline-block' : 'none';
    }
}

function bulkValidate() {
    const checked = document.querySelectorAll('.attendance-checkbox:checked');
    const status = bulkStatus.value;

    if (checked.length === 0) {
        alert('Pilih absensi yang akan divalidasi');
        return;
    }

    if (!status) {
        alert('Pilih status validasi');
        return;
    }

    const ids = Array.from(checked).map(cb => cb.value);

    if (confirm(`Validasi ${checked.length} absensi menjadi ${status.toUpperCase()}?`)) {
        document.getElementById('bulkValidateIds').value = JSON.stringify(ids);
        document.getElementById('bulkValidateStatus').value = status;
        document.getElementById('bulkValidateForm').submit();
    }
}

if (bulkValidateBtn) {
    bulkValidateBtn.onclick = bulkValidate;
}
</script>
@endpush
