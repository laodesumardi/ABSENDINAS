@extends('layouts.app')

@section('title', 'Detail Pegawai - ' . $employee->name)
@section('page-title', 'Detail Pegawai')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <!-- Profile Card -->
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="profile-photo-container mb-3">
                    @if($employee->profile_photo)
                        <img src="{{ Storage::url($employee->profile_photo) }}"
                             alt="{{ $employee->name }}"
                             class="profile-photo">
                    @else
                        <div class="default-avatar">
                            {{ substr($employee->name, 0, 1) }}
                        </div>
                    @endif
                </div>

                <h4>{{ $employee->name }}</h4>
                <p class="text-muted">
                    <i class="fas fa-id-card"></i> {{ $employee->employee_id ?? '-' }}
                </p>

                @if($employee->is_active)
                    <span class="badge bg-success fs-6 mb-3">Aktif</span>
                @else
                    <span class="badge bg-danger fs-6 mb-3">Nonaktif</span>
                @endif

                <hr>

                <div class="text-start">
                    <p><strong><i class="fas fa-envelope"></i> Email:</strong> {{ $employee->email }}</p>
                    <p><strong><i class="fas fa-briefcase"></i> Posisi:</strong> {{ $employee->position ?? '-' }}</p>
                    <p><strong><i class="fas fa-building"></i> Departemen:</strong> {{ $employee->department ?? '-' }}</p>
                    <p><strong><i class="fas fa-phone"></i> Telepon:</strong> {{ $employee->phone ?? '-' }}</p>
                    <p><strong><i class="fas fa-map-marker-alt"></i> Alamat:</strong> {{ $employee->address ?? '-' }}</p>
                    <p><strong><i class="fas fa-calendar-alt"></i> Bergabung:</strong> {{ $employee->created_at->format('d F Y') }}</p>
                    <p><strong><i class="fas fa-clock"></i> Terakhir Login:</strong>
                        {{ $employee->last_login_at ? $employee->last_login_at->format('d F Y H:i') : '-' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="col-md-8">
            <!-- Quick Stats -->
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-card text-center">
                        <h6 class="text-muted">Bulan Ini</h6>
                        <h2 class="text-primary">{{ $currentMonthStats->total ?? 0 }}</h2>
                        <small class="text-muted">Total Kehadiran</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card text-center">
                        <h6 class="text-muted">Ketepatan Waktu</h6>
                        <h2 class="text-success">
                            @php
                                $total = ($currentMonthStats->total ?? 0);
                                $present = ($currentMonthStats->present ?? 0);
                                $rate = $total > 0 ? round(($present / $total) * 100) : 0;
                            @endphp
                            {{ $rate }}%
                        </h2>
                        <small class="text-muted">Tepat waktu vs terlambat</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card text-center">
                        <h6 class="text-muted">Total Cuti</h6>
                        <h2 class="text-info">{{ $leaveStats['total_days'] }}</h2>
                        <small class="text-muted">Hari (disetujui)</small>
                    </div>
                </div>
            </div>

            <!-- Yearly Statistics -->
            <div class="stat-card mt-4">
                <h5 class="mb-3">
                    <i class="fas fa-chart-line text-primary"></i> Statistik Tahunan {{ date('Y') }}
                </h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center p-2">
                            <small class="text-muted">Total Kehadiran</small>
                            <h4>{{ $yearlyStats->total ?? 0 }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-2">
                            <small class="text-muted">Tepat Waktu</small>
                            <h4 class="text-success">{{ $yearlyStats->present ?? 0 }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-2">
                            <small class="text-muted">Terlambat</small>
                            <h4 class="text-warning">{{ $yearlyStats->late ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                @if(($yearlyStats->total_late_minutes ?? 0) > 0)
                    <div class="text-center mt-2">
                        <small class="text-warning">
                            <i class="fas fa-clock"></i>
                            Total keterlambatan: {{ floor(($yearlyStats->total_late_minutes ?? 0) / 60) }} jam {{ ($yearlyStats->total_late_minutes ?? 0) % 60 }} menit
                        </small>
                    </div>
                @endif
            </div>

            <!-- Monthly Chart -->
            <div class="stat-card mt-4">
                <h5 class="mb-3">
                    <i class="fas fa-chart-bar text-primary"></i> Grafik Kehadiran {{ date('Y') }}
                </h5>
                <canvas id="attendanceChart" height="200"></canvas>
            </div>

            <!-- Recent Attendances -->
            <div class="stat-card mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-history text-primary"></i> Absensi Terbaru
                    </h5>
                    <a href="{{ route('operator.employees.attendance', $employee) }}" class="btn btn-sm btn-primary-custom">
                        Lihat Semua <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAttendances as $attendance)
                            <tr>
                                <td>{{ $attendance->attendance_date->format('d/m/Y') }}</td>
                                <td>{{ $attendance->check_in_time ?? '-' }}</td>
                                <td>{{ $attendance->check_out_time ?? '-' }}</td>
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
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Belum ada riwayat absensi</td>
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

@push('styles')
<style>
.profile-photo-container {
    width: 150px;
    height: 150px;
    margin: 0 auto;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #3a0ca3;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.profile-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.default-avatar {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #3a0ca3, #2c0a7a);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 64px;
    font-weight: bold;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const monthlyData = @json($monthlyData);

    const ctx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthlyData.map(item => item.month),
            datasets: [
                {
                    label: 'Hadir / Tepat Waktu',
                    data: monthlyData.map(item => item.present),
                    backgroundColor: '#06d6a0',
                    borderRadius: 5
                },
                {
                    label: 'Terlambat',
                    data: monthlyData.map(item => item.late),
                    backgroundColor: '#ffd166',
                    borderRadius: 5
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
</script>
@endpush
