@extends('layouts.app')

@section('title', 'Jadwal Kerja')
@section('page-title', 'Jadwal Kerja')

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Today Schedule Card -->
    <div class="row">
        <div class="col-12">
            <div class="stat-card" style="background: linear-gradient(135deg, #3a0ca3, #2c0a7a); color: white;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-2">
                            <i class="fas fa-calendar-day"></i> Jadwal Hari Ini - {{ now()->format('l, d F Y') }}
                        </h5>
                        @if(isset($todaySchedule) && $todaySchedule && $todaySchedule->is_working_day)
                            <h3 class="mb-2">Hari Kerja</h3>
                            <p class="mb-0">
                                <i class="fas fa-clock"></i>
                                Check In: {{ $todaySchedule->check_in_window ?? 'Belum diatur' }} |
                                Check Out: {{ $todaySchedule->check_out_window ?? 'Belum diatur' }}
                            </p>
                        @else
                            <h3 class="mb-2">Hari Libur</h3>
                            <p class="mb-0">Tidak ada jadwal kerja untuk hari ini</p>
                        @endif
                    </div>
                    <div class="text-center">
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-4 row">
        <div class="col-12">
            <div class="stat-card">
                <div class="flex-wrap d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-week"></i> Daftar Jadwal Kerja
                    </h5>
                    <!-- TOMBOL RESET DIHAPUS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Schedules Table -->
    <div class="mt-4 row">
        <div class="col-12">
            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Hari</th>
                                <th>Status</th>
                                <th>Waktu Check In</th>
                                <th>Waktu Check Out</th>
                                <th>Durasi Kerja</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedules as $index => $schedule)
                            <tr class="{{ $schedule->day_of_week == strtolower(now()->format('l')) ? 'table-active' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $schedule->day_label }}</strong>
                                    @if($schedule->day_of_week == strtolower(now()->format('l')))
                                        <span class="badge bg-primary ms-2">Hari Ini</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $schedule->status_badge }}">
                                        {{ $schedule->status_text }}
                                    </span>
                                </td>
                                <td>
                                    @if($schedule->is_working_day && $schedule->check_in_start && $schedule->check_in_end)
                                        <i class="fas fa-sign-in-alt text-success"></i>
                                        {{ date('H:i', strtotime($schedule->check_in_start)) }} - {{ date('H:i', strtotime($schedule->check_in_end)) }}
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Terlambat setelah {{ date('H:i', strtotime($schedule->check_in_end)) }}
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($schedule->is_working_day && $schedule->check_out_start && $schedule->check_out_end)
                                        <i class="fas fa-sign-out-alt text-danger"></i>
                                        {{ date('H:i', strtotime($schedule->check_out_start)) }} - {{ date('H:i', strtotime($schedule->check_out_end)) }}
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Minimal check out {{ date('H:i', strtotime($schedule->check_out_start)) }}
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($schedule->is_working_day && $schedule->check_in_start && $schedule->check_out_end)
                                        @php
                                            $start = \Carbon\Carbon::parse($schedule->check_in_start);
                                            $end = \Carbon\Carbon::parse($schedule->check_out_end);
                                            $diff = $start->diff($end);
                                            $workingHours = $diff->format('%h jam %i menit');
                                        @endphp
                                        <span class="badge bg-info">
                                            {{ $workingHours }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.schedules.edit', $schedule->day_of_week) }}"
                                       class="btn btn-sm btn-primary-custom">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
