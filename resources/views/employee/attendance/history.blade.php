@extends('layouts.app')

@section('title', 'Riwayat Absensi')
@section('page-title', 'Riwayat Absensi')

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Kehadiran</h6>
                        <h3 class="mb-0">{{ $attendances->total() }}</h3>
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
                        <h3 class="mb-0 text-success">{{ $attendances->where('status', 'present')->count() }}</h3>
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
                        <h3 class="mb-0 text-warning">{{ $attendances->where('status', 'late')->count() }}</h3>
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
                        <h3 class="mb-0 text-danger">{{ $attendances->where('status', 'absent')->count() }}</h3>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-user-slash"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Statistics Chart -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-chart-line text-primary"></i> Statistik Per Bulan
                </h5>
                <canvas id="monthlyChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-filter text-primary"></i> Filter Riwayat
                </h5>
                <form method="GET" action="{{ route('employee.attendance.history') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tahun</label>
                        <select name="year" class="form-select">
                            <option value="">Semua Tahun</option>
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bulan</label>
                        <select name="month" class="form-select">
                            <option value="">Semua Bulan</option>
                            <option value="01" {{ request('month') == '01' ? 'selected' : '' }}>Januari</option>
                            <option value="02" {{ request('month') == '02' ? 'selected' : '' }}>Februari</option>
                            <option value="03" {{ request('month') == '03' ? 'selected' : '' }}>Maret</option>
                            <option value="04" {{ request('month') == '04' ? 'selected' : '' }}>April</option>
                            <option value="05" {{ request('month') == '05' ? 'selected' : '' }}>Mei</option>
                            <option value="06" {{ request('month') == '06' ? 'selected' : '' }}>Juni</option>
                            <option value="07" {{ request('month') == '07' ? 'selected' : '' }}>Juli</option>
                            <option value="08" {{ request('month') == '08' ? 'selected' : '' }}>Agustus</option>
                            <option value="09" {{ request('month') == '09' ? 'selected' : '' }}>September</option>
                            <option value="10" {{ request('month') == '10' ? 'selected' : '' }}>Oktober</option>
                            <option value="11" {{ request('month') == '11' ? 'selected' : '' }}>November</option>
                            <option value="12" {{ request('month') == '12' ? 'selected' : '' }}>Desember</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('employee.attendance.history') }}" class="btn btn-secondary ms-2 w-100">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Attendance History Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Hari</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Durasi</th>
                                <th>Status</th>
                                <th>Keterlambatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $index => $attendance)
                            <tr class="{{ $attendance->status == 'late' ? 'table-warning' : ($attendance->status == 'absent' ? 'table-danger' : '') }}">
                                <td>{{ $attendances->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $attendance->attendance_date->format('d/m/Y') }}</strong>
                                 </td>
                                <td>
                                    {{ $attendance->attendance_date->format('l') }}
                                 </td>
                                <td>
                                    @if($attendance->check_in_time)
                                        <span class="text-success">
                                            <i class="fas fa-sign-in-alt"></i>
                                            {{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i:s') }}
                                        </span>
                                        @if($attendance->check_in_latitude)
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt"></i>
                                                {{ number_format($attendance->check_in_latitude, 6) }},
                                                {{ number_format($attendance->check_in_longitude, 6) }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                 </td>
                                <td>
                                    @if($attendance->check_out_time)
                                        <span class="text-info">
                                            <i class="fas fa-sign-out-alt"></i>
                                            {{ \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i:s') }}
                                        </span>
                                        @if($attendance->check_out_latitude)
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt"></i>
                                                {{ number_format($attendance->check_out_latitude, 6) }},
                                                {{ number_format($attendance->check_out_longitude, 6) }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                 </td>
                                <td>
                                    @php
                                        $duration = '';
                                        if($attendance->check_in_time && $attendance->check_out_time) {
                                            $checkIn = \Carbon\Carbon::parse($attendance->check_in_time);
                                            $checkOut = \Carbon\Carbon::parse($attendance->check_out_time);
                                            $diff = $checkIn->diff($checkOut);
                                            $duration = $diff->format('%h jam %i menit');
                                        }
                                    @endphp
                                    {{ $duration ?: '-' }}
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
                                    <div class="btn-group" role="group">
                                        <button type="button"
                                                class="btn btn-sm btn-info text-white"
                                                onclick="showDetail({{ $attendance->id }})"
                                                title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($attendance->check_in_photo)
                                            <button type="button"
                                                    class="btn btn-sm btn-primary"
                                                    onclick="showPhoto('{{ Storage::url($attendance->check_in_photo) }}', 'Foto Check In - {{ $attendance->attendance_date->format('d/m/Y') }}')"
                                                    title="Foto Check In">
                                                <i class="fas fa-camera"></i>
                                            </button>
                                        @endif
                                        @if($attendance->check_out_photo)
                                            <button type="button"
                                                    class="btn btn-sm btn-secondary"
                                                    onclick="showPhoto('{{ Storage::url($attendance->check_out_photo) }}', 'Foto Check Out - {{ $attendance->attendance_date->format('d/m/Y') }}')"
                                                    title="Foto Check Out">
                                                <i class="fas fa-camera-retro"></i>
                                            </button>
                                        @endif
                                    </div>
                                 </td>
                             </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-2x text-muted mb-2 d-block"></i>
                                    Belum ada data riwayat absensi
                                    @if(request('year') || request('month'))
                                        <div class="mt-2">
                                            <a href="{{ route('employee.attendance.history') }}" class="btn btn-sm btn-primary-custom">
                                                <i class="fas fa-undo"></i> Reset Filter
                                            </a>
                                        </div>
                                    @endif
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

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #3a0ca3, #2c0a7a); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detail Absensi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Photo Modal -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalTitle">Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                                <img id="photoImage" src="" alt="Foto" style="max-width: 100%; border-radius: 10px;">
                            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Monthly Statistics Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyStats = @json($monthlyStats);

    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: monthlyStats.map(item => {
                const [year, month] = item.month.split('-');
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                return `${monthNames[parseInt(month)-1]} ${year}`;
            }),
            datasets: [
                {
                    label: 'Tepat Waktu',
                    data: monthlyStats.map(item => item.present),
                    backgroundColor: '#06d6a0',
                    borderColor: '#06d6a0',
                    borderWidth: 1
                },
                {
                    label: 'Terlambat',
                    data: monthlyStats.map(item => item.late),
                    backgroundColor: '#ffd166',
                    borderColor: '#ffd166',
                    borderWidth: 1
                },
                {
                    label: 'Tidak Hadir',
                    data: monthlyStats.map(item => item.absent),
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
                            return `${context.dataset.label}: ${context.raw} hari`;
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
                            return value + ' hari';
                        }
                    }
                }
            }
        }
    });

    function showDetail(id) {
        $('#detailModal').modal('show');
        $('#detailContent').html('<div class="text-center"><div class="spinner-border text-primary"></div></div>');

        // You can create an API endpoint or just show static detail
        // For now, we'll show a message
        setTimeout(() => {
            $('#detailContent').html(`
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Detail lengkap akan ditampilkan di sini. Anda dapat menambahkan API endpoint untuk mengambil data detail.
                </div>
                <p>Untuk implementasi lengkap, Anda dapat membuat endpoint API di controller.</p>
            `);
        }, 500);
    }

    function showPhoto(url, title) {
        document.getElementById('photoModalTitle').textContent = title;
        document.getElementById('photoImage').src = url;
        new bootstrap.Modal(document.getElementById('photoModal')).show();
    }
</script>
@endpush

@push('styles')
<style>
    .table-custom tbody tr:hover {
        background-color: rgba(58, 12, 163, 0.05);
        cursor: pointer;
    }
</style>
@endpush
