@extends('layouts.app')

@section('title', 'Rekap Absensi')
@section('page-title', 'Rekap Absensi Pegawai')

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Filter Section -->
    <div class="row">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-filter text-primary"></i> Filter Laporan
                </h5>
                <form method="GET" action="{{ route('operator.reports.attendance') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Departemen</label>
                        <select name="department" class="form-select">
                            <option value="">Semua Departemen</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ $department == $dept ? 'selected' : '' }}>
                                    {{ $dept }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="fas fa-chart-line"></i> Tampilkan
                        </button>
                        <a href="{{ route('operator.reports.attendance') }}" class="btn btn-secondary ms-2 w-100">
                            <i class="fas fa-undo"></i> Reset
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
                <h6 class="text-muted">Total Pegawai</h6>
                <h2 class="text-primary">{{ $summary['total_employees'] }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <h6 class="text-muted">Total Kehadiran</h6>
                <h2 class="text-info">{{ $summary['total_attendance'] }}</h2>
                <small>dari {{ $summary['total_working_days'] * $summary['total_employees'] }} hari</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <h6 class="text-muted">Tingkat Kehadiran</h6>
                <h2 class="text-success">{{ $summary['overall_attendance_rate'] }}%</h2>
                <div class="progress mt-2" style="height: 5px;">
                    <div class="progress-bar bg-success" style="width: {{ $summary['overall_attendance_rate'] }}%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <h6 class="text-muted">Ketepatan Waktu</h6>
                <h2 class="text-warning">{{ $summary['overall_punctuality_rate'] }}%</h2>
                <div class="progress mt-2" style="height: 5px;">
                    <div class="progress-bar bg-warning" style="width: {{ $summary['overall_punctuality_rate'] }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-chart-line text-primary"></i> Grafik Kehadiran Harian
                </h5>
                <canvas id="dailyChart" height="250"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-chart-pie text-primary"></i> Komposisi Kehadiran
                </h5>
                <canvas id="compositionChart" height="200"></canvas>
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="fas fa-circle text-success"></i> Hadir</span>
                        <span>{{ $summary['total_present'] }} ({{ $summary['total_attendance'] > 0 ? round(($summary['total_present'] / $summary['total_attendance']) * 100, 1) : 0 }}%)</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="fas fa-circle text-warning"></i> Terlambat</span>
                        <span>{{ $summary['total_late'] }} ({{ $summary['total_attendance'] > 0 ? round(($summary['total_late'] / $summary['total_attendance']) * 100, 1) : 0 }}%)</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="fas fa-circle text-info"></i> Setengah Hari</span>
                        <span>{{ $summary['total_half_day'] }} ({{ $summary['total_attendance'] > 0 ? round(($summary['total_half_day'] / $summary['total_attendance']) * 100, 1) : 0 }}%)</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><i class="fas fa-circle text-danger"></i> Tidak Hadir</span>
                        <span>{{ $summary['total_absent'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Statistics -->
    @if($deptStats->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-building text-primary"></i> Statistik Per Departemen
                </h5>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Departemen</th>
                                <th>Jumlah Pegawai</th>
                                <th>Tingkat Kehadiran</th>
                                <th>Ketepatan Waktu</th>
                                <th>Total Terlambat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deptStats as $dept)
                            <tr>
                                <td><strong>{{ $dept['department'] }}</strong></td>
                                <td>{{ $dept['employee_count'] }} orang</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">{{ $dept['attendance_rate'] }}%</span>
                                        <div class="progress flex-grow-1" style="height: 5px;">
                                            <div class="progress-bar bg-success" style="width: {{ $dept['attendance_rate'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">{{ $dept['punctuality_rate'] }}%</span>
                                        <div class="progress flex-grow-1" style="height: 5px;">
                                            <div class="progress-bar bg-warning" style="width: {{ $dept['punctuality_rate'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $dept['total_late'] }} kali</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Top & Worst Performers -->
    <div class="row mt-4">
        @if($topPerformers->count() > 0)
        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-trophy text-success"></i> 5 Pegawai Terbaik
                </h5>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Departemen</th>
                                <th>Tingkat Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topPerformers as $index => $performer)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $performer['name'] }}</strong></td>
                                <td>{{ $performer['department'] ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-success">{{ $performer['attendance_rate'] }}%</span>
                                 </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @if($worstPerformers->count() > 0)
        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-exclamation-triangle text-danger"></i> Perlu Improvement
                </h5>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Departemen</th>
                                <th>Tingkat Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($worstPerformers as $index => $performer)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $performer['name'] }}</strong></td>
                                <td>{{ $performer['department'] ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-danger">{{ $performer['attendance_rate'] }}%</span>
                                 </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Export Buttons -->
<div class="row mt-4">
    <div class="col-12">
        <div class="stat-card">
            <h5 class="mb-3">
                <i class="fas fa-download text-primary"></i> Export Data
            </h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="btn-group w-100 mb-2">
                        <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-file-excel"></i> Export Rekap Absensi
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('operator.reports.export', array_merge(request()->query(), ['type' => 'rekap', 'format' => 'csv'])) }}">
                                    <i class="fas fa-file-csv"></i> Export ke CSV
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('operator.reports.export', array_merge(request()->query(), ['type' => 'rekap', 'format' => 'excel'])) }}">
                                    <i class="fas fa-file-excel"></i> Export ke Excel
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="btn-group w-100 mb-2">
                        <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-list-alt"></i> Export Detail Absensi
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('operator.reports.export', array_merge(request()->query(), ['type' => 'detail', 'format' => 'csv'])) }}">
                                    <i class="fas fa-file-csv"></i> Export ke CSV
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="btn-group w-100 mb-2">
                        <button type="button" class="btn btn-warning dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-users"></i> Export Data Pegawai
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('operator.reports.export', array_merge(request()->query(), ['type' => 'employee', 'format' => 'csv'])) }}">
                                    <i class="fas fa-file-csv"></i> Export ke CSV
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="btn-group w-100 mb-2">
                        <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-calendar-alt"></i> Export Data Izin
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('operator.reports.export', array_merge(request()->query(), ['type' => 'leave', 'format' => 'csv'])) }}">
                                    <i class="fas fa-file-csv"></i> Export ke CSV
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Main Report Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-table text-primary"></i> Detail Rekap Absensi
                    <small class="text-muted">Periode: {{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</small>
                </h5>
                <div class="table-responsive">
                    <table class="table table-custom" id="reportTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Pegawai</th>
                                <th>Nama</th>
                                <th>Departemen</th>
                                <th>Hari Kerja</th>
                                <th>Hadir</th>
                                <th>Terlambat</th>
                                <th>Setengah Hari</th>
                                <th>Tidak Hadir</th>
                                <th>Total</th>
                                <th>Tingkat Kehadiran</th>
                                <th>Ketepatan Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reportData as $index => $data)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $data['employee_id'] ?? '-' }}</td>
                                <td><strong>{{ $data['name'] }}</strong></td>
                                <td>{{ $data['department'] ?? '-' }}</td>
                                <td>{{ $data['working_days'] }}</td>
                                <td>{{ $data['present'] }}</td>
                                <td>
                                    @if($data['late'] > 0)
                                        <span class="text-warning">{{ $data['late'] }}</span>
                                    @else
                                        {{ $data['late'] }}
                                    @endif
                                 </td>
                                <td>{{ $data['half_day'] }}</td>
                                <td>
                                    @if($data['absent'] > 0)
                                        <span class="text-danger">{{ $data['absent'] }}</span>
                                    @else
                                        {{ $data['absent'] }}
                                    @endif
                                 </td>
                                <td>{{ $data['total_attendance'] }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">{{ $data['attendance_rate'] }}%</span>
                                        <div class="progress flex-grow-1" style="width: 60px; height: 5px;">
                                            <div class="progress-bar bg-success" style="width: {{ $data['attendance_rate'] }}%"></div>
                                        </div>
                                    </div>
                                 </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">{{ $data['punctuality_rate'] }}%</span>
                                        <div class="progress flex-grow-1" style="width: 60px; height: 5px;">
                                            <div class="progress-bar bg-info" style="width: {{ $data['punctuality_rate'] }}%"></div>
                                        </div>
                                    </div>
                                 </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="13" class="text-center py-4">
                                    <i class="fas fa-chart-line fa-2x text-muted mb-2 d-block"></i>
                                    Tidak ada data untuk periode yang dipilih
                                 </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if(count($reportData) > 0)
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="4" class="text-end">TOTAL:</th>
                                <th>{{ $summary['total_working_days'] * $summary['total_employees'] }}</th>
                                <th>{{ $summary['total_present'] }}</th>
                                <th>{{ $summary['total_late'] }}</th>
                                <th>{{ $summary['total_half_day'] }}</th>
                                <th>{{ $summary['total_absent'] }}</th>
                                <th>{{ $summary['total_attendance'] }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
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
    const dailyData = @json($dailyChart);
    const dailyCtx = document.getElementById('dailyChart').getContext('2d');

    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: dailyData.map(item => {
                const date = new Date(item.date);
                return date.getDate() + '/' + (date.getMonth() + 1);
            }),
            datasets: [
                {
                    label: 'Hadir',
                    data: dailyData.map(item => item.present),
                    borderColor: '#06d6a0',
                    backgroundColor: 'rgba(6, 214, 160, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Terlambat',
                    data: dailyData.map(item => item.late),
                    borderColor: '#ffd166',
                    backgroundColor: 'rgba(255, 209, 102, 0.1)',
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
                    ticks: {
                        stepSize: 1
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
            labels: ['Hadir', 'Terlambat', 'Setengah Hari'],
            datasets: [{
                data: [
                    {{ $summary['total_present'] }},
                    {{ $summary['total_late'] }},
                    {{ $summary['total_half_day'] }}
                ],
                backgroundColor: ['#06d6a0', '#ffd166', '#118ab2'],
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
</script>
@endpush
