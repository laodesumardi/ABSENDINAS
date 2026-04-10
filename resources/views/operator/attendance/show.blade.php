@extends('layouts.app')

@section('title', 'Detail Absensi')
@section('page-title', 'Detail Absensi')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="stat-card">
                <!-- Header -->
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="fas fa-fingerprint fa-3x text-primary"></i>
                    </div>
                    <h4>Detail Absensi</h4>
                    <p class="text-muted">{{ $attendance->attendance_date->format('l, d F Y') }}</p>
                    <span class="badge bg-{{ $attendance->status == 'present' ? 'success' : ($attendance->status == 'late' ? 'warning' : 'danger') }} fs-6">
                        {{ ucfirst($attendance->status) }}
                    </span>
                    @if($attendance->approved_by)
                        <div class="alert alert-success mt-3 mb-0">
                            <i class="fas fa-check-circle"></i>
                            Sudah divalidasi pada {{ $attendance->approved_at->format('d/m/Y H:i:s') }}
                            @if($attendance->approver)
                                <br><small>Oleh: {{ $attendance->approver->name }}</small>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-hourglass-half"></i>
                            Belum divalidasi
                        </div>
                    @endif
                </div>

                <!-- Employee Info -->
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-6">
                            <strong><i class="fas fa-user"></i> Pegawai:</strong><br>
                            {{ $attendance->user->name }}
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-id-card"></i> ID Pegawai:</strong><br>
                            {{ $attendance->user->employee_id ?? '-' }}
                        </div>
                    </div>
                </div>

                <!-- Attendance Details -->
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-sign-in-alt text-success"></i> Check In</h5>
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Waktu</th>
                                <td>: {{ $attendance->check_in_time ?? '-' }}</td>
                            </tr>
                            @if($attendance->check_in_latitude)
                            <tr>
                                <th>Lokasi</th>
                                <td>: {{ number_format($attendance->check_in_latitude, 6) }}, {{ number_format($attendance->check_in_longitude, 6) }}</td>
                            </tr>
                            <tr>
                                <th>Jarak dari Kantor</th>
                                <td>: {{ $distance ? round($distance) . ' meter' : '-' }}</td>
                            </tr>
                            @endif
                            @if($attendance->check_in_photo)
                            <tr>
                                <th>Foto</th>
                                <td>:
                                    <a href="{{ Storage::url($attendance->check_in_photo) }}" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-camera"></i> Lihat Foto
                                    </a>
                                </td>
                            </tr>
                            @endif
                            @if($attendance->late_minutes > 0)
                            <tr>
                                <th>Keterlambatan</th>
                                <td>: <span class="text-warning">{{ $attendance->late_minutes }} menit</span></td>
                            </tr>
                            @endif
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-sign-out-alt text-danger"></i> Check Out</h5>
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Waktu</th>
                                <td>: {{ $attendance->check_out_time ?? '-' }}</td>
                            </tr>
                            @if($attendance->check_out_latitude)
                            <tr>
                                <th>Lokasi</th>
                                <td>: {{ number_format($attendance->check_out_latitude, 6) }}, {{ number_format($attendance->check_out_longitude, 6) }}</td>
                            </tr>
                            <tr>
                                <th>Jarak dari Kantor</th>
                                <td>: {{ $distanceOut ? round($distanceOut) . ' meter' : '-' }}</td>
                            </tr>
                            @endif
                            @if($attendance->check_out_photo)
                            <tr>
                                <th>Foto</th>
                                <td>:
                                    <a href="{{ Storage::url($attendance->check_out_photo) }}" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-camera"></i> Lihat Foto
                                    </a>
                                </td>
                            </tr>
                            @endif
                            @if($attendance->early_checkout_minutes > 0)
                            <tr>
                                <th>Pulang Awal</th>
                                <td>: <span class="text-warning">{{ $attendance->early_checkout_minutes }} menit</span></td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                @if($attendance->notes)
                <div class="alert alert-secondary mt-3">
                    <strong><i class="fas fa-sticky-note"></i> Catatan:</strong><br>
                    {{ $attendance->notes }}
                </div>
                @endif

                <div class="text-end mt-4">
                    <a href="{{ route('operator.attendance.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    @if(!$attendance->approved_by)
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#validateModal">
                            <i class="fas fa-check"></i> Validasi
                        </button>
                    @endif
                    <a href="{{ route('operator.attendance.edit', $attendance) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Validate Modal -->
<div class="modal fade" id="validateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #28a745, #20c997); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Validasi Absensi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('operator.attendance.validate', $attendance) }}">
                @csrf
                <div class="modal-body">
                    <p>Validasi absensi untuk:</p>
                    <p><strong>{{ $attendance->user->name }}</strong><br>
                    <small class="text-muted">{{ $attendance->attendance_date->format('d/m/Y') }}</small></p>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="present" {{ $attendance->status == 'present' ? 'selected' : '' }}>Hadir</option>
                            <option value="late" {{ $attendance->status == 'late' ? 'selected' : '' }}>Terlambat</option>
                            <option value="absent">Tidak Hadir</option>
                            <option value="half_day">Setengah Hari</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Catatan validasi..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Validasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
