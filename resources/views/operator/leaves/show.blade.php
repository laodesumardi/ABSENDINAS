@extends('layouts.app')

@section('title', 'Detail Pengajuan Izin')
@section('page-title', 'Detail Pengajuan Izin')

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
                    <span class="badge bg-{{ $leave->status_badge }} fs-6">
                        {{ $leave->status_text }}
                    </span>
                </div>

                <!-- Employee Info -->
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-6">
                            <strong><i class="fas fa-user"></i> Nama Pegawai:</strong><br>
                            {{ $leave->user->name }}
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-id-card"></i> ID Pegawai:</strong><br>
                            {{ $leave->user->employee_id ?? '-' }}
                        </div>
                        <div class="col-md-6 mt-2">
                            <strong><i class="fas fa-briefcase"></i> Posisi:</strong><br>
                            {{ $leave->user->position ?? '-' }}
                        </div>
                        <div class="col-md-6 mt-2">
                            <strong><i class="fas fa-building"></i> Departemen:</strong><br>
                            {{ $leave->user->department ?? '-' }}
                        </div>
                    </div>
                </div>

                <!-- Leave Details -->
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Tanggal Pengajuan</th>
                        <td>: {{ $leave->created_at->format('d F Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Periode Izin</th>
                        <td>: {{ $leave->start_date->format('d F Y') }} - {{ $leave->end_date->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <th>Durasi</th>
                        <td>: {{ $leave->total_days }} hari\n
                        <small class="text-muted">Termasuk tanggal mulai dan akhir</small>
                        </td>
                    </tr>
                    <tr>
                        <th>Alasan</th>
                        <td>: {{ $leave->reason }}</td>
                    </tr>
                    @if($leave->attachment)
                    <tr>
                        <th>Lampiran</th>
                        <td>:
                            <a href="{{ Storage::url($leave->attachment) }}" target="_blank" class="btn btn-sm btn-info">
                                <i class="fas fa-download"></i> Download Lampiran
                            </a>
                        </td>
                    </tr>
                    @endif
                    @if($leave->approved_at)
                    <tr>
                        <th>Tanggal Diproses</th>
                        <td>: {{ $leave->approved_at->format('d F Y H:i:s') }}</td>
                    </tr>
                    @endif
                    @if($leave->approver)
                    <tr>
                        <th>Diproses Oleh</th>
                        <td>: {{ $leave->approver->name }} ({{ ucfirst($leave->approver->role) }})</td>
                    </tr>
                    @endif
                    @if($leave->rejection_reason)
                    <tr>
                        <th>Alasan Penolakan</th>
                        <td>:
                            <div class="alert alert-danger mb-0">
                                <i class="fas fa-exclamation-triangle"></i> {{ $leave->rejection_reason }}
                            </div>
                        </td>
                    </tr>
                    @endif
                </table>

                <div class="text-end mt-4">
                    <a href="{{ route('operator.leaves.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    @if($leave->status == 'pending')
                        <button type="button" class="btn btn-success" onclick="approveLeave({{ $leave->id }})">
                            <i class="fas fa-check"></i> Setujui
                        </button>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times"></i> Tolak
                        </button>
                    @endif
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
            <form id="rejectForm" method="POST" action="{{ route('operator.leaves.reject', $leave) }}">
                @csrf
                <div class="modal-body">
                    <p>Tolak pengajuan izin dari <strong>{{ $leave->user->name }}</strong>?</p>
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
@endsection

@push('scripts')
<script>
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
</script>
@endpush
