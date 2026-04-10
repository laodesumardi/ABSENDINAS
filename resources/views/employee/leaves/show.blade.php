@extends('layouts.app')

@section('title', 'Detail Pengajuan Izin')
@section('page-title', 'Detail Pengajuan Izin / Cuti')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="stat-card">
                <!-- Status Header -->
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="{{ $leave->leave_type_icon }} fa-3x text-primary"></i>
                    </div>
                    <h4>{{ $leave->leave_type_label }}</h4>
                    <span class="badge bg-{{ $leave->status_badge }} fs-6 px-3 py-2">
                        <i class="fas {{ $leave->status == 'approved' ? 'fa-check-circle' : ($leave->status == 'pending' ? 'fa-clock' : ($leave->status == 'rejected' ? 'fa-times-circle' : 'fa-ban')) }} me-1"></i>
                        {{ $leave->status_text }}
                    </span>

                    @if($leave->status == 'pending')
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-clock me-2"></i>
                            <strong>Menunggu Persetujuan</strong>
                            <p class="mb-0 mt-1">Pengajuan Anda sedang diproses oleh operator HRD. Anda akan mendapatkan notifikasi setelah diproses.</p>
                        </div>
                    @elseif($leave->status == 'approved')
                        <div class="alert alert-success mt-3">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Pengajuan Disetujui</strong>
                            <p class="mb-0 mt-1">Selamat! Pengajuan izin/cuti Anda telah disetujui.</p>
                            @if($leave->approved_by)
                                <small class="d-block mt-2">
                                    <i class="fas fa-user-check me-1"></i> Disetujui oleh: {{ $leave->approver->name ?? 'Operator' }}
                                    <br>
                                    <i class="fas fa-calendar-check me-1"></i> Tanggal: {{ $leave->approved_at ? $leave->approved_at->format('d F Y H:i') : '-' }}
                                </small>
                            @endif
                        </div>
                    @elseif($leave->status == 'rejected')
                        <div class="alert alert-danger mt-3">
                            <i class="fas fa-times-circle me-2"></i>
                            <strong>Pengajuan Ditolak</strong>
                            <p class="mb-0 mt-1">Mohon maaf, pengajuan izin/cuti Anda tidak dapat disetujui.</p>
                            @if($leave->rejection_reason)
                                <div class="mt-2 p-2 bg-light rounded">
                                    <strong><i class="fas fa-comment-dots me-1"></i> Alasan Penolakan:</strong>
                                    <p class="mb-0 mt-1">{{ $leave->rejection_reason }}</p>
                                </div>
                            @endif
                        </div>
                    @elseif($leave->status == 'cancelled')
                        <div class="alert alert-secondary mt-3">
                            <i class="fas fa-ban me-2"></i>
                            <strong>Pengajuan Dibatalkan</strong>
                            <p class="mb-0 mt-1">Pengajuan ini telah dibatalkan oleh Anda.</p>
                        </div>
                    @endif
                </div>

                <!-- Leave Details -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-card p-3 mb-3">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-info-circle me-2"></i> Informasi Pengajuan
                            </h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="40%">No. Pengajuan</th>
                                    <td>: <code>#{{ str_pad($leave->id, 6, '0', STR_PAD_LEFT) }}</code>
                                </tr>
                                <tr>
                                    <th>Tanggal Pengajuan</th>
                                    <td>: {{ $leave->created_at->format('d F Y H:i:s') }}
                                </tr>
                                <tr>
                                    <th>Jenis Izin</th>
                                    <td>: {{ $leave->leave_type_label }}
                                </tr>
                                <tr>
                                    <th>Durasi</th>
                                    <td>: {{ $leave->total_days }} hari kerja
                                </tr>
                                <tr>
                                    <th>Periode</th>
                                    <td>: {{ $leave->start_date->format('d F Y') }} - {{ $leave->end_date->format('d F Y') }}
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-card p-3 mb-3">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-user me-2"></i> Informasi Pemohon
                            </h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="40%">Nama</th>
                                    <td>: {{ $leave->user->name }}
                                </tr>
                                <tr>
                                    <th>ID Pegawai</th>
                                    <td>: {{ $leave->user->employee_id ?? '-' }}
                                </tr>
                                <tr>
                                    <th>Posisi</th>
                                    <td>: {{ $leave->user->position ?? '-' }}
                                </tr>
                                <tr>
                                    <th>Departemen</th>
                                    <td>: {{ $leave->user->department ?? '-' }}
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>: {{ $leave->user->email }}
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Reason -->
                <div class="info-card p-3 mb-3">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-comment-dots me-2"></i> Alasan Pengajuan
                    </h6>
                    <div class="p-3 bg-light rounded">
                        {{ $leave->reason }}
                    </div>
                </div>

                <!-- Attachment -->
                @if($leave->attachment)
                <div class="info-card p-3 mb-3">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-paperclip me-2"></i> Lampiran
                    </h6>
                    <div class="d-flex align-items-center p-3 bg-light rounded">
                        <i class="fas fa-file-alt fa-2x text-primary me-3"></i>
                        <div>
                            <strong>Dokumen Pendukung</strong>
                            <br>
                            <small class="text-muted">
                                @php
                                    $ext = pathinfo($leave->attachment, PATHINFO_EXTENSION);
                                @endphp
                                File {{ strtoupper($ext) }} -
                                {{ round(Storage::disk('public')->size($leave->attachment) / 1024) }} KB
                            </small>
                        </div>
                        <a href="{{ Storage::url($leave->attachment) }}" target="_blank" class="btn btn-sm btn-primary-custom ms-auto">
                            <i class="fas fa-download me-1"></i> Download
                        </a>
                    </div>
                </div>
                @endif

                <!-- Status Timeline -->
                <div class="info-card p-3 mb-3">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-chart-line me-2"></i> Timeline Pengajuan
                    </h6>
                    <div class="timeline-simple">
                        <div class="timeline-step {{ $leave->created_at ? 'completed' : '' }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Pengajuan Dikirim</h6>
                                <p class="text-muted small mb-0">{{ $leave->created_at->format('d F Y H:i:s') }}</p>
                            </div>
                        </div>

                        <div class="timeline-step {{ $leave->status == 'approved' || $leave->status == 'rejected' ? 'completed' : ($leave->status == 'pending' ? 'active' : '') }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Diproses oleh Operator</h6>
                                @if($leave->approved_at)
                                    <p class="text-muted small mb-0">{{ $leave->approved_at->format('d F Y H:i:s') }}</p>
                                @else
                                    <p class="text-muted small mb-0">Menunggu proses</p>
                                @endif
                            </div>
                        </div>

                        @if($leave->status == 'approved')
                        <div class="timeline-step completed">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Pengajuan Disetujui</h6>
                                <p class="text-success small mb-0">Selamat! Pengajuan Anda telah disetujui.</p>
                            </div>
                        </div>
                        @elseif($leave->status == 'rejected')
                        <div class="timeline-step rejected">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Pengajuan Ditolak</h6>
                                <p class="text-danger small mb-0">Pengajuan tidak dapat disetujui.</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-end mt-4">
                    <a href="{{ route('employee.leaves.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>

                    @if($leave->status == 'pending')
                        <a href="{{ route('employee.leaves.edit', $leave) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <button type="button" class="btn btn-danger" onclick="cancelLeave({{ $leave->id }})">
                            <i class="fas fa-times me-1"></i> Batalkan
                        </button>
                    @endif
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
                <p>Apakah Anda yakin ingin membatalkan pengajuan izin/cuti ini?</p>
                <p><strong>Jenis:</strong> {{ $leave->leave_type_label }}</p>
                <p><strong>Periode:</strong> {{ $leave->date_range }}</p>
                <p class="text-danger mb-0">Tindakan ini tidak dapat dibatalkan!</p>
            </div>
            <div class="modal-footer">
                <form id="cancelForm" method="POST" action="{{ route('employee.leaves.destroy', $leave) }}">
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

@push('styles')
<style>
.info-card {
    background: #f8f9fc;
    border-radius: 12px;
    border: 1px solid #e9ecef;
}

.info-card table th {
    font-weight: 600;
    color: #4a5568;
}

/* Timeline Styles */
.timeline-simple {
    position: relative;
    padding-left: 30px;
}

.timeline-step {
    position: relative;
    padding-bottom: 25px;
}

.timeline-step:last-child {
    padding-bottom: 0;
}

.timeline-step .timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #cbd5e1;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #cbd5e1;
    z-index: 1;
}

.timeline-step.completed .timeline-marker {
    background: #06d6a0;
    box-shadow: 0 0 0 2px #06d6a0;
}

.timeline-step.active .timeline-marker {
    background: #ffd166;
    box-shadow: 0 0 0 2px #ffd166;
    animation: pulse 1.5s infinite;
}

.timeline-step.rejected .timeline-marker {
    background: #ef476f;
    box-shadow: 0 0 0 2px #ef476f;
}

.timeline-step:not(:last-child):before {
    content: '';
    position: absolute;
    left: -23px;
    top: 16px;
    bottom: -9px;
    width: 2px;
    background: #e2e8f0;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(255, 209, 102, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(255, 209, 102, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(255, 209, 102, 0);
    }
}
</style>
@endpush

@push('scripts')
<script>
function cancelLeave(id) {
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}
</script>
@endpush
