@extends('layouts.app')

@section('title', 'Operator Dashboard')
@section('page-title', 'Dashboard Operator')

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Pegawai</h6>
                        <h3 class="mb-0">{{ $totalEmployees }}</h3>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Hadir Hari Ini</h6>
                        <h3 class="mb-0">{{ $todayAttendance }}</h3>
                        <small class="text-success">
                            <i class="fas fa-percent"></i> {{ round(($todayAttendance / max($totalEmployees, 1)) * 100) }}%
                        </small>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Izin Pending</h6>
                        <h3 class="mb-0">{{ $pendingLeaves }}</h3>
                        <small class="text-warning">
                            <i class="fas fa-clock"></i> Perlu approval
                        </small>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Tingkat Kehadiran</h6>
                        <h3 class="mb-0">{{ round(($todayAttendance / max($totalEmployees, 1)) * 100) }}%</h3>
                        <small class="text-info">
                            <i class="fas fa-chart-line"></i> Bulan ini
                        </small>
                    </div>
                    <div class="stat-icon info">
                        <i class="fas fa-chart-simple"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="stat-card">
            <h5 class="mb-3">
                <i class="fas fa-bolt text-primary"></i> Aksi Cepat
            </h5>

            <div class="row">
                <!-- Approval Izin -->
                <div class="col-md-3 mb-2">
                    <a href="{{ route('operator.leaves.index') }}"
                       class="btn btn-primary-custom w-100">
                        <i class="fas fa-check-circle"></i> Approval Izin
                    </a>
                </div>

                <!-- Input Manual -->
                <div class="col-md-3 mb-2">
                    <a href="{{ route('operator.attendance.create') }}"
                       class="btn btn-primary-custom w-100">
                        <i class="fas fa-plus"></i> Input Manual
                    </a>
                </div>

                <!-- Export Data -->
                <div class="col-md-3 mb-2">
                    <a href="#"
                       class="btn btn-primary-custom w-100">
                        <i class="fas fa-download"></i> Export Data
                    </a>
                </div>

                <!-- Cetak Laporan -->
                <div class="col-md-3 mb-2">
                    <a href="#"
                       class="btn btn-primary-custom w-100">
                        <i class="fas fa-print"></i> Cetak Laporan
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

    <!-- Recent Attendances -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-clock text-primary"></i> Absensi Terbaru Hari Ini
                </h5>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Nama Pegawai</th>
                                <th>Check In</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAttendances as $attendance)
                            <tr>
                                <td>{{ $attendance->created_at->format('H:i:s') }}</td>
                                <td>{{ $attendance->user->name }}</td>
                                <td>{{ $attendance->check_in_time }}</td>
                                <td>
                                    <span class="badge bg-{{ $attendance->status_badge }}">
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary-custom" onclick="detailAttendance({{ $attendance->id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada absensi hari ini</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function detailAttendance(id) {
        alert('Detail absensi ID: ' + id);
        // Implement detail modal here
    }

    // Auto refresh data setiap 30 detik
    setInterval(function() {
        location.reload();
    }, 30000);
</script>
@endpush
