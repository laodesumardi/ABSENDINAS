@extends('layouts.app')

@section('title', 'Laporan Absensi')
@section('page-title', 'Laporan Absensi')

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Filter Form -->
    <div class="row">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-filter text-primary"></i> Filter Laporan
                </h5>
                <form method="GET" action="{{ route('admin.reports.attendance') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Pegawai</label>
                        <select name="user_id" class="form-select">
                            <option value="">Semua Pegawai</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->employee_id ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="present" {{ $status == 'present' ? 'selected' : '' }}>Hadir</option>
                            <option value="late" {{ $status == 'late' ? 'selected' : '' }}>Terlambat</option>
                            <option value="absent" {{ $status == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                            <option value="half_day" {{ $status == 'half_day' ? 'selected' : '' }}>Setengah Hari</option>
                        </select>
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

    <!-- Summary Cards -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Kehadiran</h6>
                        <h3 class="mb-0">{{ $summary['total'] }}</h3>
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
                        <h6 class="text-muted mb-2">Tepat Waktu</h6>
                        <h3 class="mb-0 text-success">{{ $summary['present'] }}</h3>
                        <small class="text-success">
                            @if($summary['total'] > 0)
                                {{ round(($summary['present'] / $summary['total']) * 100) }}%
                            @else
                                0%
                            @endif
                        </small>
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
                        <h3 class="mb-0 text-warning">{{ $summary['late'] }}</h3>
                        <small class="text-warning">
                            Total: {{ $summary['total_late_minutes'] }} menit
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
                        <h6 class="text-muted mb-2">Tidak Hadir</h6>
                        <h3 class="mb-0 text-danger">{{ $summary['absent'] }}</h3>
                        <small class="text-muted">
                            Rata-rata check in: {{ $summary['average_check_in'] }}
                        </small>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-user-slash"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mt-4">
        <div class="col-md-7">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-chart-line text-primary"></i> Grafik Kehadiran Harian
                </h5>
                <canvas id="dailyChart" height="300"></canvas>
            </div>
        </div>

        <div class="col-md-5">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-chart-pie text-primary"></i> Komposisi Kehadiran
                </h5>
                <canvas id="statusChart" height="250"></canvas>
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="fas fa-circle text-success"></i> Hadir</span>
                        <span>{{ $summary['present'] }} ({{ $summary['total'] > 0 ? round(($summary['present'] / $summary['total']) * 100) : 0 }}%)</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="fas fa-circle text-warning"></i> Terlambat</span>
                        <span>{{ $summary['late'] }} ({{ $summary['total'] > 0 ? round(($summary['late'] / $summary['total']) * 100) : 0 }}%)</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="fas fa-circle text-danger"></i> Tidak Hadir</span>
                        <span>{{ $summary['absent'] }} ({{ $summary['total'] > 0 ? round(($summary['absent'] / $summary['total']) * 100) : 0 }}%)</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><i class="fas fa-circle text-info"></i> Setengah Hari</span>
                        <span>{{ $summary['half_day'] }} ({{ $summary['total'] > 0 ? round(($summary['half_day'] / $summary['total']) * 100) : 0 }}%)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Buttons -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h5 class="mb-0">
                            <i class="fas fa-download text-primary"></i> Export Data
                        </h5>
                    </div>
                    <div>
                        <a href="{{ route('admin.reports.export', request()->query()) }}" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export Excel/CSV
                        </a>
                        <a href="{{ route('admin.reports.print', request()->query()) }}" target="_blank" class="btn btn-primary-custom">
                            <i class="fas fa-print"></i> Print
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-table text-primary"></i> Data Absensi
                </h5>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama Pegawai</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Terlambat</th>
                                <th>Durasi</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $index => $attendance)
                            <tr>
                                <td>{{ $attendances->firstItem() + $index }}</td>
                                <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d/m/Y') }}</td>
                                <td>
                                    <strong>{{ $attendance->user->name }}</strong><br>
                                    <small class="text-muted">{{ $attendance->user->employee_id ?? '-' }}</small>
                                </td>
                                <td>
                                    {{ $attendance->check_in_time }}
                                    @if($attendance->check_in_latitude)
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt"></i>
                                            {{ substr($attendance->check_in_latitude, 0, 8) }},
                                            {{ substr($attendance->check_in_longitude, 0, 8) }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    {{ $attendance->check_out_time ?? '-' }}
                                    @if($attendance->check_out_latitude)
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt"></i>
                                            {{ substr($attendance->check_out_latitude, 0, 8) }},
                                            {{ substr($attendance->check_out_longitude, 0, 8) }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusBadge = [
                                            'present' => 'success',
                                            'late' => 'warning',
                                            'absent' => 'danger',
                                            'half_day' => 'info'
                                        ][$attendance->status] ?? 'secondary';

                                        $statusText = [
                                            'present' => 'Hadir',
                                            'late' => 'Terlambat',
                                            'absent' => 'Tidak Hadir',
                                            'half_day' => 'Setengah Hari'
                                        ][$attendance->status] ?? $attendance->status;
                                    @endphp
                                    <span class="badge bg-{{ $statusBadge }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td>
                                    @if($attendance->late_minutes > 0)
                                        <span class="text-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            {{ $attendance->late_minutes }} menit
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $duration = '';
                                        if($attendance->check_in_time && $attendance->check_out_time) {
                                            $checkIn = strtotime($attendance->check_in_time);
                                            $checkOut = strtotime($attendance->check_out_time);
                                            $diff = $checkOut - $checkIn;
                                            $duration = gmdate('H:i', $diff);
                                        }
                                    @endphp
                                    {{ $duration ?: '-' }}
                                </td>
                                <td>
                                    @if($attendance->notes)
                                        <span class="text-muted" title="{{ $attendance->notes }}">
                                            {{ Str::limit($attendance->notes, 30) }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-2x text-muted mb-2 d-block"></i>
                                    Tidak ada data absensi pada periode yang dipilih
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Daily Chart
    const dailyCtx = document.getElementById('dailyChart').getContext('2d');
    const dailyStats = @json($dailyStats);

    new Chart(dailyCtx, {
        type: 'bar',
        data: {
            labels: dailyStats.map(item => item.date),
            datasets: [
                {
                    label: 'Hadir',
                    data: dailyStats.map(item => item.present),
                    backgroundColor: '#06d6a0',
                    borderColor: '#06d6a0',
                    borderWidth: 1
                },
                {
                    label: 'Terlambat',
                    data: dailyStats.map(item => item.late),
                    backgroundColor: '#ffd166',
                    borderColor: '#ffd166',
                    borderWidth: 1
                },
                {
                    label: 'Tidak Hadir',
                    data: dailyStats.map(item => item.absent),
                    backgroundColor: '#ef476f',
                    borderColor: '#ef476f',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.raw} orang`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return value + ' orang';
                        }
                    }
                }
            }
        }
    });

    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusCounts = @json($statusCounts);

    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusCounts.labels,
            datasets: [{
                data: statusCounts.data,
                backgroundColor: statusCounts.colors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = statusCounts.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.raw / total) * 100).toFixed(1);
                            return `${context.label}: ${context.raw} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
