@extends('layouts.app')

@section('title', 'Manajemen Pengajuan Izin')
@section('page-title', 'Manajemen Pengajuan Izin / Cuti')

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Pengajuan</h6>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Menunggu</h6>
                        <h3 class="mb-0 text-warning">{{ $stats['pending'] }}</h3>
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
                        <h6 class="text-muted mb-2">Disetujui</h6>
                        <h3 class="mb-0 text-success">{{ $stats['approved'] }}</h3>
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
                        <h6 class="text-muted mb-2">Total Hari Cuti</h6>
                        <h3 class="mb-0 text-info">{{ $stats['total_days'] }}</h3>
                    </div>
                    <div class="stat-icon info">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <form method="GET" action="{{ route('operator.leaves.index') }}" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Jenis</label>
                        <select name="leave_type" class="form-select">
                            <option value="">Semua</option>
                            <option value="annual" {{ request('leave_type') == 'annual' ? 'selected' : '' }}>Cuti Tahunan</option>
                            <option value="sick" {{ request('leave_type') == 'sick' ? 'selected' : '' }}>Sakit</option>
                            <option value="personal" {{ request('leave_type') == 'personal' ? 'selected' : '' }}>Keperluan Pribadi</option>
                            <option value="emergency" {{ request('leave_type') == 'emergency' ? 'selected' : '' }}>Darurat</option>
                            <option value="maternity" {{ request('leave_type') == 'maternity' ? 'selected' : '' }}>Cuti Melahirkan</option>
                            <option value="other" {{ request('leave_type') == 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tgl Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tgl Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cari Pegawai</label>
                        <input type="text" name="search" class="form-control" placeholder="Nama / ID Pegawai" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if(request('status') == 'pending' || !request('status'))
    <div class="row mt-3">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <input type="checkbox" id="selectAll">
                        <label class="ms-2">Pilih Semua</label>
                    </div>
                    <button type="button" class="btn btn-success" id="bulkApproveBtn" style="display: none;">
                        <i class="fas fa-check-double"></i> Setujui Terpilih
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Leaves Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-primary"></i> Daftar Pengajuan Izin / Cuti
                    </h5>
                    <a href="{{ route('operator.leaves.export', request()->query()) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-download"></i> Export
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                @if(request('status') == 'pending' || !request('status'))
                                    <th width="30">
                                        <input type="checkbox" id="selectAllCheckbox">
                                    </th>
                                @endif
                                <th>No</th>
                                <th>Tgl Pengajuan</th>
                                <th>Pegawai</th>
                                <th>Jenis</th>
                                <th>Periode</th>
                                <th>Durasi</th>
                                <th>Alasan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaves as $index => $leave)
                            <tr>
                                @if(request('status') == 'pending' || !request('status'))
                                    <td>
                                        @if($leave->status == 'pending')
                                            <input type="checkbox" class="leave-checkbox" value="{{ $leave->id }}">
                                        @endif
                                    </td>
                                @endif
                                <td>{{ $leaves->firstItem() + $index }}</td>
                                <td>{{ $leave->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <strong>{{ $leave->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $leave->user->employee_id ?? '-' }}</small>
                                </td>
                                <td>
                                    <i class="{{ $leave->leave_type_icon }}"></i>
                                    {{ $leave->leave_type_label }}
                                </td>
                                <td>{{ $leave->date_range }}</td>
                                <td>{{ $leave->total_days }} hari</td>
                                <td>{{ Str::limit($leave->reason, 50) }}</td>
                                <td>
                                    <span class="badge bg-{{ $leave->status_badge }}">
                                        {{ $leave->status_text }}
                                    </span>
                                    @if($leave->status == 'rejected' && $leave->rejection_reason)
                                        <br>
                                        <small class="text-danger" title="{{ $leave->rejection_reason }}">
                                            <i class="fas fa-info-circle"></i> Lihat alasan
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('operator.leaves.show', $leave) }}"
                                           class="btn btn-sm btn-info text-white"
                                           title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($leave->status == 'pending')
                                            <button type="button"
                                                    class="btn btn-sm btn-success"
                                                    onclick="approveLeave({{ $leave->id }})"
                                                    title="Setujui">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectModal"
                                                    data-leave-id="{{ $leave->id }}"
                                                    data-leave-name="{{ $leave->user->name }}"
                                                    title="Tolak">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        @if($leave->attachment)
                                            <a href="{{ Storage::url($leave->attachment) }}"
                                               class="btn btn-sm btn-secondary"
                                               target="_blank"
                                               title="Lampiran">
                                                <i class="fas fa-paperclip"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                @if(request('status') == 'pending' || !request('status'))
                                    <td colspan="10" class="text-center py-4">
                                @else
                                    <td colspan="9" class="text-center py-4">
                                @endif
                                    <i class="fas fa-inbox fa-2x text-muted mb-2 d-block"></i>
                                    Tidak ada data pengajuan izin / cuti
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $leaves->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-history text-primary"></i> Aktivitas Terbaru
                </h5>
                <div class="timeline">
                    @forelse($recentActivities as $activity)
                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <i class="{{ $activity->action_icon }}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $activity->action_label }}</strong>
                                <small class="text-muted">{{ $activity->human_readable_time }}</small>
                            </div>
                            <p class="mb-1">{{ $activity->description }}</p>
                            <small class="text-muted">
                                <i class="fas fa-user"></i> {{ $activity->user->name ?? 'System' }} |
                                <i class="fas fa-map-marker-alt"></i> {{ $activity->ip_address ?? '-' }}
                            </small>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-2x text-muted mb-2 d-block"></i>
                        Belum ada aktivitas
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545, #c82333); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle"></i> Tolak Pengajuan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Tolak pengajuan izin dari <strong id="rejectEmployeeName"></strong>?</p>
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required
                                  placeholder="Berikan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Approve Form -->
<form id="bulkApproveForm" method="POST" action="{{ route('operator.leaves.bulk-approve') }}">
    @csrf
    <input type="hidden" name="ids" id="bulkApproveIds">
</form>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 40px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-icon {
    position: absolute;
    left: -40px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: white;
    border: 2px solid #3a0ca3;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
}

.timeline-content {
    background: #f8f9fc;
    padding: 12px;
    border-radius: 8px;
    border-left: 3px solid #3a0ca3;
}

.timeline-item:not(:last-child):before {
    content: '';
    position: absolute;
    left: -26px;
    top: 30px;
    bottom: -20px;
    width: 2px;
    background: #e0e0e0;
}
</style>
@endpush

@push('scripts')
<script>
let selectedLeaveId = null;
let selectedLeaveName = '';

// Reject modal handler
const rejectModal = document.getElementById('rejectModal');
if (rejectModal) {
    rejectModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        selectedLeaveId = button.getAttribute('data-leave-id');
        selectedLeaveName = button.getAttribute('data-leave-name');

        document.getElementById('rejectEmployeeName').textContent = selectedLeaveName;
        const form = document.getElementById('rejectForm');
        form.action = "{{ url('operator/leaves') }}/" + selectedLeaveId + "/reject";
    });
}

function approveLeave(id) {
    if (confirm('Setujui pengajuan izin ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ url('operator/leaves') }}/" + id + "/approve";
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }
}

// Select all functionality
const selectAllCheckbox = document.getElementById('selectAllCheckbox');
const leaveCheckboxes = document.querySelectorAll('.leave-checkbox');
const bulkApproveBtn = document.getElementById('bulkApproveBtn');

if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
        leaveCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        toggleBulkApproveBtn();
    });
}

leaveCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', toggleBulkApproveBtn);
});

function toggleBulkApproveBtn() {
    const checked = document.querySelectorAll('.leave-checkbox:checked').length;
    if (bulkApproveBtn) {
        bulkApproveBtn.style.display = checked > 0 ? 'inline-block' : 'none';
    }
}

function bulkApprove() {
    const checked = document.querySelectorAll('.leave-checkbox:checked');
    const ids = Array.from(checked).map(cb => cb.value);

    if (ids.length === 0) return;

    if (confirm(`Setujui ${ids.length} pengajuan izin yang dipilih?`)) {
        document.getElementById('bulkApproveIds').value = JSON.stringify(ids);
        document.getElementById('bulkApproveForm').submit();
    }
}

if (bulkApproveBtn) {
    bulkApproveBtn.onclick = bulkApprove;
}
</script>
@endpush
