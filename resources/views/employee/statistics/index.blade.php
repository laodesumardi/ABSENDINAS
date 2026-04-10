@extends('layouts.app')

@section('title', 'Statistik Kehadiran Saya')
@section('page-title', 'Statistik Kehadiran')

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Year Filter -->
    <div class="row">
        <div class="col-12">
            <div class="stat-card">
                <form method="GET" action="{{ route('employee.statistics.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Tahun</label>
                        <select name="year" class="form-select">
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bulan</label>
                        <select name="month" class="form-select">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create($year, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="fas fa-chart-line"></i> Tampilkan Statistik
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('employee.statistics.export', ['year' => $year]) }}" class="btn btn-success w-100">
                            <i class="fas fa-download"></i> Export CSV
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <h6 class="text-muted">Tingkat Kehadiran</h6>
                <h2 class="text-primary">{{ $summary['attendance_rate'] }}%</h2>
                <div class="progress mt-2" style="height: 8px;">
                    <div class="progress-bar bg-primary" style="width: {{ $summary['attendance_rate'] }}%"></div>
                </div>
                <small class="text-muted">Target: 100%</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <h6 class="text-muted">Ketepatan Waktu</h6>
                <h2 class="text-success">{{ $summary['punctuality_rate'] }}%</h2>
                <div class="progress mt-2" style="height: 8px;">
                    <div class="progress-bar bg-success" style="width: {{ $summary['punctuality_rate'] }}%"></div>
                </div>
                <small class="text-muted">Tepat waktu vs terlambat</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <h6 class="text-muted">Total Kehadiran</h6>
                <h2 class="text-info">{{ $summary['total_attendance'] }}</h2>
                <small class="text-muted">dari {{ $summary['total_working_days'] }} hari kerja</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <h6 class="text-muted">Total Keterlambatan</h6>
                <h2 class="text-warning">{{ floor($summary['total_late_minutes'] / 60) }} jam</h2>
                <small class="text-muted">{{ $summary['total_late_minutes'] % 60 }} menit</small>
            </div>
        </div>
    </div>

    <!-- Comparison with Previous Month -->
    @if($comparison)
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-chart-line text-primary"></i> Perbandingan dengan Bulan Sebelumnya
                </h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <small class="text-muted">Tingkat Kehadiran</small>
                            <h4>{{ $comparison['attendance_rate']['current'] }}%</h4>
                            @if($comparison['attendance_rate']['change'] > 0)
                                <span class="text-success">
                                    <i class="fas fa-arrow-up"></i> +{{ $comparison['attendance_rate']['change'] }}%
                                </span>
                            @elseif($comparison['attendance_rate']['change'] < 0)
                                <span class="text-danger">
                                    <i class="fas fa-arrow-down"></i> {{ $comparison['attendance_rate']['change'] }}%
                                </span>
                            @else
                                <span class="text-muted">Tidak berubah</span>
                            @endif
                            <br>
                            <small class="text-muted">vs {{ $comparison['attendance_rate']['previous'] }}%</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <small class="text-muted">Ketepatan Waktu</small>
                            <h4>{{ $comparison['punctuality_rate']['current'] }}%</h4>
                            @if($comparison['punctuality_rate']['change'] > 0)
                                <span class="text-success">
                                    <i class="fas fa-arrow-up"></i> +{{ $comparison['punctuality_rate']['change'] }}%
                                </span>
                            @elseif($comparison['punctuality_rate']['change'] < 0)
                                <span class="text-danger">
                                    <i class="fas fa-arrow-down"></i> {{ $comparison['punctuality_rate']['change'] }}%
                                </span>
                            @else
                                <span class="text-muted">Tidak berubah</span>
                            @endif
                            <br>
                            <small class="text-muted">vs {{ $comparison['punctuality_rate']['previous'] }}%</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <small class="text-muted">Total Keterlambatan</small>
                            <h4>{{ floor($comparison['total_late_minutes']['current'] / 60) }} jam</h4>
                            @if($comparison['total_late_minutes']['change'] < 0)
                                <span class="text-success">
                                    <i class="fas fa-arrow-down"></i> {{ floor(abs($comparison['total_late_minutes']['change']) / 60) }} jam
                                </span>
                            @elseif($comparison['total_late_minutes']['change'] > 0)
                                <span class="text-danger">
                                    <i class="fas fa-arrow-up"></i> +{{ floor($comparison['total_late_minutes']['change'] / 60) }} jam
                                </span>
                            @else
                                <span class="text-muted">Sama</span>
                            @endif
                            <br>
                            <small class="text-muted">vs {{ floor($comparison['total_late_minutes']['previous'] / 60) }} jam</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Monthly Statistics Chart -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-chart-bar text-primary"></i> Statistik Bulanan {{ $year }}
                </h5>
                <canvas id="monthlyChart" height="300"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-chart-pie text-primary"></i> Komposisi Kehadiran {{ $year }}
                </h5>
                <canvas id="compositionChart" height="250"></canvas>
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="fas fa-circle text-success"></i> Tepat Waktu</span>
                        <span>{{ $summary['total_present'] }} ({{ $summary['punctuality_rate'] }}%)</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="fas fa-circle text-warning"></i> Terlambat</span>
                        <span>{{ $summary['total_late'] }} ({{ 100 - $summary['punctuality_rate'] }}%)</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><i class="fas fa-circle text-danger"></i> Tidak Hadir</span>
                        <span>{{ $summary['total_absent'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Trends -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-chart-line text-primary"></i> Tren 6 Bulan Terakhir
                </h5>
                <canvas id="trendsChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Best & Worst Months -->
    <div class="row mt-4">
        @if($bestMonth)
        <div class="col-md-6">
            <div class="stat-card text-center" style="background: linear-gradient(135deg, #28a745, #20c997); color: white;">
                <i class="fas fa-trophy fa-2x mb-2"></i>
                <h5>Bulan Terbaik</h5>
                <h3>{{ $bestMonth['month_name'] }}</h3>
                <p class="mb-0">Tingkat Kehadiran: {{ $bestMonth['attendance_rate'] }}%</p>
                <small>Hadir {{ $bestMonth['present'] }} dari {{ $bestMonth['total'] }} hari</small>
            </div>
        </div>
        @endif

        @if($worstMonth)
        <div class="col-md-6">
            <div class="stat-card text-center" style="background: linear-gradient(135deg, #ffc107, #fd7e14); color: white;">
                <i class="fas fa-chart-line fa-2x mb-2"></i>
                <h5>Perlu Improvement</h5>
                <h3>{{ $worstMonth['month_name'] }}</h3>
                <p class="mb-0">Tingkat Kehadiran: {{ $worstMonth['attendance_rate'] }}%</p>
                <small>Hadir {{ $worstMonth['present'] }} dari {{ $worstMonth['total'] }} hari</small>
            </div>
        </div>
        @endif
    </div>

    <!-- Leave Statistics -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-umbrella-beach text-primary"></i> Statistik Cuti {{ $year }}
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="text-center p-3 border rounded mb-2">
                            <h3>{{ $leaveStats['total_days'] }}</h3>
                            <p class="text-muted mb-0">Total Hari Cuti</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center p-3 border rounded mb-2">
                            <h3 class="text-warning">{{ $leaveStats['pending'] }}</h3>
                            <p class="text-muted mb-0">Pengajuan Pending</p>
                        </div>
                    </div>
                </div>
                <canvas id="leaveChart" height="200"></canvas>
            </div>
        </div>

        <!-- Punch Time Analysis -->
        @if($punchTimeAnalysis)
        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-clock text-primary"></i> Analisis Waktu {{ Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="text-center p-3 border rounded mb-2">
                            <small class="text-muted">Rata-rata Check In</small>
                            <h4 class="text-primary">
                                {{ floor($punchTimeAnalysis['avg_check_in']) }}:{{ str_pad(round(($punchTimeAnalysis['avg_check_in'] - floor($punchTimeAnalysis['avg_check_in'])) * 60), 2, '0', STR_PAD_LEFT) }}
                            </h4>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center p-3 border rounded mb-2">
                            <small class="text-muted">Rata-rata Check Out</small>
                            <h4 class="text-info">
                                {{ floor($punchTimeAnalysis['avg_check_out']) }}:{{ str_pad(round(($punchTimeAnalysis['avg_check_out'] - floor($punchTimeAnalysis['avg_check_out'])) * 60), 2, '0', STR_PAD_LEFT) }}
                            </h4>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center p-3 border rounded">
                            <small class="text-muted">Check In Terawal</small>
                            <h5 class="text-success">
                                {{ floor($punchTimeAnalysis['earliest_check_in']) }}:{{ str_pad(round(($punchTimeAnalysis['earliest_check_in'] - floor($punchTimeAnalysis['earliest_check_in'])) * 60), 2, '0', STR_PAD_LEFT) }}
                            </h5>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center p-3 border rounded">
                            <small class="text-muted">Check Out Terakhir</small>
                            <h5 class="text-warning">
                                {{ floor($punchTimeAnalysis['latest_check_out']) }}:{{ str_pad(round(($punchTimeAnalysis['latest_check_out'] - floor($punchTimeAnalysis['latest_check_out'])) * 60), 2, '0', STR_PAD_LEFT) }}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Daily Calendar View -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-calendar-alt text-primary"></i> Kalender Kehadiran {{ Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                </h5>
                <div class="calendar-grid">
                    <div class="row">
                        @foreach($dailyStats as $day)
                            @php
                                $statusClass = '';
                                $statusIcon = '';
                                if($day['is_holiday']) {
                                    $statusClass = 'bg-secondary text-white';
                                    $statusIcon = '🎉';
                                } elseif($day['status'] == 'present') {
                                    $statusClass = 'bg-success text-white';
                                    $statusIcon = '✅';
                                } elseif($day['status'] == 'late') {
                                    $statusClass = 'bg-warning';
                                    $statusIcon = '⚠️';
                                } elseif($day['status'] == 'absent' && $day['is_working_day']) {
                                    $statusClass = 'bg-danger text-white';
                                    $statusIcon = '❌';
                                } else {
                                    $statusClass = 'bg-light';
                                    $statusIcon = '⚪';
                                }
                            @endphp
                            <div class="col-md-2 col-lg-1 mb-2">
                                <div class="card text-center {{ $statusClass }} p-2">
                                    <small>{{ $day['day_name'] }}</small>
                                    <strong>{{ $day['day'] }}</strong>
                                    <span>{{ $statusIcon }}</span>
                                    @if($day['late_minutes'] > 0)
                                        <small class="small">{{ $day['late_minutes'] }}m</small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-center gap-3">
                        <span><i class="fas fa-check-circle text-success"></i> Hadir</span>
                        <span><i class="fas fa-clock text-warning"></i> Terlambat</span>
                        <span><i class="fas fa-times-circle text-danger"></i> Tidak Hadir</span>
                        <span><i class="fas fa-gift text-secondary"></i> Libur</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Monthly Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyStats = @json($monthlyStats);
    const months = Object.values(monthlyStats).map(s => s.month_name.substring(0, 3));
    const attendanceRates = Object.values(monthlyStats).map(s => s.attendance_rate);
    const punctualityRates = Object.values(monthlyStats).map(s => s.punctuality_rate);

    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Tingkat Kehadiran (%)',
                    data: attendanceRates,
                    borderColor: '#3a0ca3',
                    backgroundColor: 'rgba(58, 12, 163, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Ketepatan Waktu (%)',
                    data: punctualityRates,
                    borderColor: '#06d6a0',
                    backgroundColor: 'rgba(6, 214, 160, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });

    // Composition Chart
    const compositionCtx = document.getElementById('compositionChart').getContext('2d');
    new Chart(compositionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Tepat Waktu', 'Terlambat'],
            datasets: [{
                data: [{{ $summary['total_present'] }}, {{ $summary['total_late'] }}],
                backgroundColor: ['#06d6a0', '#ffd166'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Trends Chart
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    const trends = @json($trends);
    new Chart(trendsCtx, {
        type: 'bar',
        data: {
            labels: trends.map(t => t.month),
            datasets: [
                {
                    label: 'Tingkat Kehadiran (%)',
                    data: trends.map(t => t.attendance_rate),
                    backgroundColor: '#3a0ca3'
                },
                {
                    label: 'Ketepatan Waktu (%)',
                    data: trends.map(t => t.punctuality_rate),
                    backgroundColor: '#06d6a0'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });

    // Leave Chart
    const leaveCtx = document.getElementById('leaveChart').getContext('2d');
    const leaveData = @json($leaveStats['by_type']);
    new Chart(leaveCtx, {
        type: 'pie',
        data: {
            labels: ['Cuti Tahunan', 'Sakit', 'Keperluan Pribadi', 'Darurat', 'Cuti Melahirkan', 'Lainnya'],
            datasets: [{
                data: [
                    leaveData.annual,
                    leaveData.sick,
                    leaveData.personal,
                    leaveData.emergency,
                    leaveData.maternity,
                    leaveData.other
                ],
                backgroundColor: ['#3a0ca3', '#4361ee', '#06d6a0', '#ffd166', '#ef476f', '#118ab2']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        font: { size: 10 }
                    }
                }
            }
        }
    });
</script>
@endpush

@push('styles')
<style>
.calendar-grid .card {
    font-size: 12px;
    transition: transform 0.2s;
}
.calendar-grid .card:hover {
    transform: scale(1.05);
    z-index: 1;
}
</style>
@endpush
