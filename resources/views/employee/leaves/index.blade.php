@extends('layouts.app')

@section('title', 'Pengajuan Izin / Cuti')
@section('page-title', 'Pengajuan Izin / Cuti')

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

    <!-- Action Buttons -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-primary"></i> Daftar Pengajuan
                    </h5>
                    <div>
                        <a href="{{ route('employee.leaves.create') }}" class="btn btn-primary-custom">
                            <i class="fas fa-plus"></i> Ajukan Izin / Cuti
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <form method="GET" action="{{ route('employee.leaves.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jenis</label>
                        <select name="type" class="form-select">
                            <option value="">Semua Jenis</option>
                            <option value="annual" {{ request('type') == 'annual' ? 'selected' : '' }}>Cuti Tahunan</option>
                            <option value="sick" {{ request('type') == 'sick' ? 'selected' : '' }}>Sakit</option>
                            <option value="personal" {{ request('type') == 'personal' ? 'selected' : '' }}>Keperluan Pribadi</option>
                            <option value="emergency" {{ request('type') == 'emergency' ? 'selected' : '' }}>Darurat</option>
                            <option value="maternity" {{ request('type') == 'maternity' ? 'selected' : '' }}>Cuti Melahirkan</option>
                            <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('employee.leaves.index') }}" class="btn btn-secondary ms-2 w-100">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Leaves Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Jenis</th>
                                <th>Tanggal Izin</th>
                                <th>Durasi</th>
                                <th>Alasan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaves as $index => $leave)
                            <tr>
                                <td>{{ $leaves->firstItem() + $index }}</td>
                                <td>{{ $leave->created_at->format('d/m/Y H:i') }}</td>
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
                                    @if($leave->status == 'pending' && $leave->days_left > 0)
                                        <br>
                                        <small class="text-muted">{{ $leave->days_left }} hari lagi</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('employee.leaves.show', $leave) }}"
                                           class="btn btn-sm btn-info text-white"
                                           title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($leave->status == 'pending')
                                            <a href="{{ route('employee.leaves.edit', $leave) }}"
                                               class="btn btn-sm btn-warning text-white"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="cancelLeave({{ $leave->id }})"
                                                    title="Batalkan">
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
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x text-muted mb-2 d-block"></i>
                                    Belum ada pengajuan izin / cuti
                                    <div class="mt-2">
                                        <a href="{{ route('employee.leaves.create') }}" class="btn btn-sm btn-primary-custom">
                                            <i class="fas fa-plus"></i> Ajukan Sekarang
                                        </a>
                                    </div>
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
</div>

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545, #c82333); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Konfirmasi Pembatalan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin membatalkan pengajuan izin ini?</p>
                <p class="text-danger mb-0">Tindakan ini tidak dapat dibatalkan!</p>
            </div>
            <div class="modal-footer">
                <form id="cancelForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                    <button type="submit" class="btn btn-danger">Ya, Batalkan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function cancelLeave(id) {
    const form = document.getElementById('cancelForm');
    form.action = "{{ url('employee/leaves') }}/" + id;
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}
</script>
@endpush
