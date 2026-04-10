@extends('layouts.app')

@section('title', 'Edit Absensi')
@section('page-title', 'Edit Absensi')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-edit text-primary"></i> Edit Absensi
                </h5>

                <div class="alert alert-warning mb-4">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Perhatian:</strong> Mengedit absensi yang sudah divalidasi akan mengubah status validasi.
                </div>

                <form method="POST" action="{{ route('operator.attendance.update', $attendance) }}">
                    @csrf
                    @method('PUT')

                    <div class="alert alert-info">
                        <strong>Pegawai:</strong> {{ $attendance->user->name }}<br>
                        <strong>Tanggal:</strong> {{ $attendance->attendance_date->format('d F Y') }}
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Check In</label>
                            <input type="time" name="check_in_time" class="form-control"
                                   value="{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '' }}">
                            <small class="text-muted">Kosongkan jika tidak ada</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Check Out</label>
                            <input type="time" name="check_out_time" class="form-control"
                                   value="{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '' }}">
                            <small class="text-muted">Kosongkan jika tidak ada</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="present" {{ $attendance->status == 'present' ? 'selected' : '' }}>Hadir</option>
                                <option value="late" {{ $attendance->status == 'late' ? 'selected' : '' }}>Terlambat</option>
                                <option value="absent" {{ $attendance->status == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                                <option value="half_day" {{ $attendance->status == 'half_day' ? 'selected' : '' }}>Setengah Hari</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Keterlambatan (menit)</label>
                            <input type="number" name="late_minutes" class="form-control" value="{{ $attendance->late_minutes }}" min="0">
                        </div>
                    </div>

                    <!-- Koordinat Section -->
                    <div class="card mb-3">
                        <div class="card-header" style="background: #f8f9fc;">
                            <h6 class="mb-0">
                                <i class="fas fa-map-marker-alt text-primary"></i> Koordinat Lokasi
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="use_office_location" id="use_office_location"
                                               class="form-check-input" value="1">
                                        <label class="form-check-label" for="use_office_location">
                                            Gunakan lokasi kantor ({{ $workLocation->name ?? 'Kantor' }})
                                        </label>
                                        <small class="text-muted d-block">Centang untuk menggunakan koordinat lokasi kantor</small>
                                    </div>
                                </div>
                            </div>

                            <div id="manualCoordinates">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Latitude Check In</label>
                                        <input type="number" step="any" name="check_in_latitude" id="check_in_latitude"
                                               class="form-control" value="{{ $attendance->check_in_latitude ?? ($workLocation->latitude ?? '') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Longitude Check In</label>
                                        <input type="number" step="any" name="check_in_longitude" id="check_in_longitude"
                                               class="form-control" value="{{ $attendance->check_in_longitude ?? ($workLocation->longitude ?? '') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Latitude Check Out</label>
                                        <input type="number" step="any" name="check_out_latitude" id="check_out_latitude"
                                               class="form-control" value="{{ $attendance->check_out_latitude ?? ($workLocation->latitude ?? '') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Longitude Check Out</label>
                                        <input type="number" step="any" name="check_out_longitude" id="check_out_longitude"
                                               class="form-control" value="{{ $attendance->check_out_longitude ?? ($workLocation->longitude ?? '') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="3">{{ $attendance->notes }}</textarea>
                    </div>

                    <div class="text-end">
                        <a href="{{ route('operator.attendance.show', $attendance) }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#use_office_location').on('change', function() {
            if ($(this).is(':checked')) {
                $('#manualCoordinates').hide();
                $('#check_in_latitude').val('{{ $workLocation->latitude ?? "" }}');
                $('#check_in_longitude').val('{{ $workLocation->longitude ?? "" }}');
                $('#check_out_latitude').val('{{ $workLocation->latitude ?? "" }}');
                $('#check_out_longitude').val('{{ $workLocation->longitude ?? "" }}');
            } else {
                $('#manualCoordinates').show();
            }
        });
    });
</script>
@endpush
