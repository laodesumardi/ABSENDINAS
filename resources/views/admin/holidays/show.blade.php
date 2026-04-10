@extends('layouts.app')

@section('title', 'Detail Hari Libur')
@section('page-title', 'Detail Hari Libur')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-info-circle text-primary"></i> Informasi Libur
                </h5>
                <table class="table table-borderless">
                    <tr>
                        <th width="150">Nama Libur</th>
                        <td>: <strong>{{ $holiday->name }}</strong></td>
                    </tr>
                    <tr>
                        <th>Tanggal</th>
                        <td>: {{ $holiday->formatted_date }} ({{ $holiday->day_name_indonesian }})</td>
                    </tr>
                    <tr>
                        <th>Tipe</th>
                        <td>:
                            <span class="badge bg-{{ $holiday->type_badge }}">
                                {{ $holiday->type_text }}
                            </span>
                        </td>
                    </tr>
                    @if($holiday->description)
                    <tr>
                        <th>Deskripsi</th>
                        <td>: {{ $holiday->description }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Status</th>
                        <td>:
                            @if($holiday->is_today)
                                <span class="badge bg-danger">Hari Ini</span>
                            @elseif($holiday->is_upcoming)
                                <span class="badge bg-warning">{{ $holiday->days_left }} hari lagi</span>
                            @else
                                <span class="badge bg-secondary">Sudah Lewat</span>
                            @endif
                         </td>
                    </tr>
                    <tr>
                        <th>Dibuat</th>
                        <td>: {{ $holiday->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Terakhir Update</th>
                        <td>: {{ $holiday->updated_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                </table>

                <div class="mt-3">
                    <a href="{{ route('admin.holidays.edit', $holiday) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('admin.holidays.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-calendar-alt text-primary"></i> Informasi Tambahan
                </h5>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Dampak Hari Libur:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Pegawai tidak diwajibkan absen pada hari libur</li>
                        <li>Absensi otomatis dianggap libur</li>
                        <li>Tidak ada penalti keterlambatan atau ketidakhadiran</li>
                    </ul>
                </div>

                @if($holiday->is_upcoming && !$holiday->is_today)
                <div class="alert alert-warning">
                    <i class="fas fa-bell"></i>
                    <strong>Pengumuman:</strong>
                    <p class="mb-0 mt-2">Libur akan datang dalam {{ $holiday->days_left }} hari. Sistem akan otomatis menandai sebagai hari libur.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
