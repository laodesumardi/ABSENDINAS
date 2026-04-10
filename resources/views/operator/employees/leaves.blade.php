@extends('layouts.app')

@section('title', 'Riwayat Izin - ' . $employee->name)
@section('page-title', 'Riwayat Izin / Cuti - ' . $employee->name)

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Back Button -->
    <div class="row">
        <div class="col-12">
            <a href="{{ route('operator.employees.show', $employee) }}" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Kembali ke Detail
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <h6 class="text-muted">Total Hari Cuti</h6>
                <h2 class="text-primary">{{ $stats['total_days'] }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <h6 class="text-muted">Menunggu</h6>
                <h2 class="text-warning">{{ $stats['pending'] }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <h6 class="text-muted">Disetujui</h6>
                <h2 class="text-success">{{ $stats['approved'] }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <h6 class="text-muted">Ditolak</h6>
                <h2 class="text-danger">{{ $stats['rejected'] }}</h2>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <form method="GET" action="{{ route('operator.employees.leaves', $employee) }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-4">
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
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('operator.employees.leaves', $employee) }}" class="btn btn-secondary ms-2 w-100">
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
                                <th>Tgl Pengajuan</th>
                                <th>Jenis</th>
                                <th>Periode</th>
                                <th>Durasi</th>
                                <th>Alasan</th>
                                <th>Status</th>
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
                                    @if($leave->rejection_reason)
                                        <br>
                                        <small class="text-danger" title="{{ $leave->rejection_reason }}">
                                            <i class="fas fa-info-circle"></i> Alasan ditolak
                                        </small>
                                    @endif
                                 </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x text-muted mb-2 d-block"></i>
                                    Tidak ada riwayat izin / cuti
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
@endsection
