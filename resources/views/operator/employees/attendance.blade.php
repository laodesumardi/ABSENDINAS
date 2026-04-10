@extends('layouts.app')

@section('title', 'Riwayat Absensi - ' . $employee->name)
@section('page-title', 'Riwayat Absensi - ' . $employee->name)

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
                <h6 class="text-muted">Total Kehadiran</h6>
                <h2 class="text-primary">{{ $summary->total ?? 0 }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <h6 class="text-muted">Tepat Waktu</h6>
                <h2 class="text-success">{{ $summary->present ?? 0 }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <h6 class="text-muted">Terlambat</h6>
                <h2 class="text-warning">{{ $summary->late ?? 0 }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <h6 class="text-muted">Tidak Hadir</h6>
                <h2 class="text-danger">{{ $summary->absent ?? 0 }}</h2>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <form method="GET" action="{{ route('operator.employees.attendance', $employee) }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua</option>
                            <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Hadir</option>
                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                            <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                            <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Setengah Hari</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('operator.employees.attendance', $employee) }}" class="btn btn-secondary ms-2 w-100">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Durasi</th>
                                <th>Status</th>
                                <th>Keterlambatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $index => $attendance)
                            <tr>
                                <td>{{ $attendances->firstItem() + $index }}</td>
                                <td>{{ $attendance->attendance_date->format('d/m/Y') }}</td>
                                <td>{{ $attendance->check_in_time ?? '-' }}</td>
                                <td>{{ $attendance->check_out_time ?? '-' }}</td>
                                <td>
                                    @php
                                        $duration = '';
                                        if($attendance->check_in_time && $attendance->check_out_time) {
                                            $checkIn = \Carbon\Carbon::parse($attendance->check_in_time);
                                            $checkOut = \Carbon\Carbon::parse($attendance->check_out_time);
                                            $diff = $checkIn->diff($checkOut);
                                            $duration = $diff->format('%h jam %i menit');
                                        }
                                        echo $duration ?: '-';
                                    @endphp
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
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
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
@endsection
