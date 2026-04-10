@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard Administrator')

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
                        <small class="text-success">
                            <i class="fas fa-arrow-up"></i> +12% bulan ini
                        </small>
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
                        <h3 class="mb-0">{{ $totalPresentToday }}</h3>
                        <small class="text-success">
                            <i class="fas fa-check-circle"></i> {{ round(($totalPresentToday / max($totalEmployees, 1)) * 100) }}%
                        </small>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Terlambat</h6>
                        <h3 class="mb-0">{{ $totalLateToday }}</h3>
                        <small class="text-danger">
                            <i class="fas fa-exclamation-triangle"></i> Perlu perhatian
                        </small>
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
                        <h6 class="text-muted mb-2">Izin Pending</h6>
                        <h3 class="mb-0">{{ $totalPendingLeaves }}</h3>
                        <small class="text-info">
                            <i class="fas fa-hourglass-half"></i> Menunggu approval
                        </small>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-chart-line text-primary"></i> Statistik Absensi 7 Hari Terakhir
                </h5>
                <canvas id="attendanceChart" height="300"></canvas>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-chart-pie text-primary"></i> Status Kehadiran
                </h5>
                <canvas id="statusChart" height="250"></canvas>
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="fas fa-circle text-success"></i> Hadir</span>
                        <span>{{ $attendanceStats->sum('present') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="fas fa-circle text-warning"></i> Terlambat</span>
                        <span>{{ $attendanceStats->sum('late') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><i class="fas fa-circle text-danger"></i> Tidak Hadir</span>
                        <span>{{ ($totalEmployees * 7) - ($attendanceStats->sum('present') + $attendanceStats->sum('late')) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Attendances -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-list-alt text-primary"></i> Absensi Terbaru
                    </h5>
                    <a href="{{ route('admin.reports.attendance') }}" class="btn btn-sm btn-primary-custom">
                        Lihat Semua <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Pegawai</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAttendances as $attendance)
                            <tr>
                                <td>{{ $attendance->attendance_date->format('d/m/Y') }}</td>
                                <td>{{ $attendance->user->name }}</td>
                                <td>{{ $attendance->check_in_time }}</td>
                                <td>{{ $attendance->check_out_time ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $attendance->status_badge }}">
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada data absensi</td>
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
    // Attendance Chart
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($attendanceStats->pluck('date')),
            datasets: [{
                label: 'Hadir',
                data: @json($attendanceStats->pluck('present')),
                borderColor: '#06d6a0',
                backgroundColor: 'rgba(6, 214, 160, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Terlambat',
                data: @json($attendanceStats->pluck('late')),
                borderColor: '#ffd166',
                backgroundColor: 'rgba(255, 209, 102, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });

    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Terlambat', 'Tidak Hadir'],
            datasets: [{
                data: [
                    {{ $attendanceStats->sum('present') }},
                    {{ $attendanceStats->sum('late') }},
                    {{ ($totalEmployees * 7) - ($attendanceStats->sum('present') + $attendanceStats->sum('late')) }}
                ],
                backgroundColor: ['#06d6a0', '#ffd166', '#ef476f'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
</script>
@endpush
