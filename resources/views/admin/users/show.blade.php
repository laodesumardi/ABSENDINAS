@extends('layouts.app')

@section('title', 'Detail User')
@section('page-title', 'Detail User')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <!-- User Info -->
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="user-avatar mx-auto mb-3" style="width: 100px; height: 100px; font-size: 40px; line-height: 100px;">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <h4>{{ $user->name }}</h4>
                <p class="text-muted">{{ $user->email }}</p>

                @php
                    $roleBadge = [
                        'admin' => 'danger',
                        'operator' => 'warning',
                        'employee' => 'primary'
                    ][$user->role] ?? 'secondary';
                @endphp
                <span class="badge bg-{{ $roleBadge }} fs-6 mb-3">{{ ucfirst($user->role) }}</span>

                @if($user->is_active)
                    <span class="badge bg-success fs-6 mb-3">Aktif</span>
                @else
                    <span class="badge bg-danger fs-6 mb-3">Nonaktif</span>
                @endif

                <hr>

                <div class="text-start">
                    <p><strong><i class="fas fa-id-card"></i> ID Pegawai:</strong> {{ $user->employee_id ?? '-' }}</p>
                    <p><strong><i class="fas fa-briefcase"></i> Posisi:</strong> {{ $user->position ?? '-' }}</p>
                    <p><strong><i class="fas fa-building"></i> Departemen:</strong> {{ $user->department ?? '-' }}</p>
                    <p><strong><i class="fas fa-phone"></i> Telepon:</strong> {{ $user->phone ?? '-' }}</p>
                    <p><strong><i class="fas fa-map-marker-alt"></i> Alamat:</strong> {{ $user->address ?? '-' }}</p>
                    <p><strong><i class="fas fa-calendar-alt"></i> Bergabung:</strong> {{ $user->created_at->format('d F Y') }}</p>
                </div>

                <div class="mt-3">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="col-md-8">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-chart-line text-primary"></i> Statistik Kehadiran (30 Hari)
                </h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <h3 class="text-primary">{{ $attendanceStats->total ?? 0 }}</h3>
                            <p class="text-muted mb-0">Total Kehadiran</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <h3 class="text-success">{{ $attendanceStats->present ?? 0 }}</h3>
                            <p class="text-muted mb-0">Tepat Waktu</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <h3 class="text-warning">{{ $attendanceStats->late ?? 0 }}</h3>
                            <p class="text-muted mb-0">Terlambat</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Attendances -->
            <div class="stat-card mt-4">
                <h5 class="mb-3">
                    <i class="fas fa-history text-primary"></i> Riwayat Absensi Terbaru
                </h5>
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
                                <td>{{ $attendance->check_in_time }}</td>
                                <td>{{ $attendance->check_out_time ?? '-' }}</td>
                                <td>
                                    @php
                                        $badgeColor = [
                                            'present' => 'success',
                                            'late' => 'warning',
                                            'absent' => 'danger'
                                        ][$attendance->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }}">
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Belum ada data absensi</td>
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
