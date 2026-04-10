@extends('layouts.app')

@section('title', 'Edit Absensi')
@section('page-title', 'Edit Absensi Pegawai')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="mx-auto col-md-8">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-edit text-primary"></i> Edit Absensi
                </h5>

                <div class="mb-4 alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Perhatian!</strong> Anda sedang mengedit absensi. Perubahan akan tercatat di log aktivitas.
                </div>

                <form method="POST" action="{{ route('admin.reports.update', $attendance->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Pegawai <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                <option value="">Pilih Pegawai</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id', $attendance->user_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->employee_id ?? '-' }}) - {{ $user->department ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">Tanggal Absensi <span class="text-danger">*</span></label>
                            <input type="date" name="attendance_date" class="form-control @error('attendance_date') is-invalid @enderror"
                                   value="{{ old('attendance_date', $attendance->attendance_date->format('Y-m-d')) }}" required>
                            @error('attendance_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">Waktu Check In</label>
                            <input type="time" name="check_in_time" class="form-control @error('check_in_time') is-invalid @enderror"
                                   value="{{ old('check_in_time', $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '') }}">
                            <small class="text-muted">Format: HH:MM (24 jam)</small>
                            @error('check_in_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">Waktu Check Out</label>
                            <input type="time" name="check_out_time" class="form-control @error('check_out_time') is-invalid @enderror"
                                   value="{{ old('check_out_time', $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '') }}">
                            <small class="text-muted">Harus setelah waktu check in</small>
                            @error('check_out_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="present" {{ old('status', $attendance->status) == 'present' ? 'selected' : '' }}>Hadir (Tepat Waktu)</option>
                                <option value="late" {{ old('status', $attendance->status) == 'late' ? 'selected' : '' }}>Terlambat</option>
                                <option value="absent" {{ old('status', $attendance->status) == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                                <option value="half_day" {{ old('status', $attendance->status) == 'half_day' ? 'selected' : '' }}>Setengah Hari</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">Keterlambatan (menit)</label>
                            <input type="number" name="late_minutes" class="form-control @error('late_minutes') is-invalid @enderror"
                                   value="{{ old('late_minutes', $attendance->late_minutes) }}" min="0">
                            <small class="text-muted">Isi jika status Terlambat</small>
                            @error('late_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Koordinat Section -->
                    <div class="mb-3 card">
                        <div class="card-header" style="background: #f8f9fc;">
                            <h6 class="mb-0">
                                <i class="fas fa-map-marker-alt text-primary"></i> Koordinat Lokasi
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Latitude Check In</label>
                                    <input type="number" step="any" name="check_in_latitude" class="form-control"
                                           value="{{ old('check_in_latitude', $attendance->check_in_latitude ?? ($workLocation->latitude ?? '')) }}">
                                    <small class="text-muted">Contoh: -6.200000</small>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Longitude Check In</label>
                                    <input type="number" step="any" name="check_in_longitude" class="form-control"
                                           value="{{ old('check_in_longitude', $attendance->check_in_longitude ?? ($workLocation->longitude ?? '')) }}">
                                    <small class="text-muted">Contoh: 106.816666</small>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Latitude Check Out</label>
                                    <input type="number" step="any" name="check_out_latitude" class="form-control"
                                           value="{{ old('check_out_latitude', $attendance->check_out_latitude ?? ($workLocation->latitude ?? '')) }}">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Longitude Check Out</label>
                                    <input type="number" step="any" name="check_out_longitude" class="form-control"
                                           value="{{ old('check_out_longitude', $attendance->check_out_longitude ?? ($workLocation->longitude ?? '')) }}">
                                </div>
                            </div>

                            @if($workLocation)
                            <div class="alert alert-info">
                                <i class="fas fa-building"></i>
                                <strong>Lokasi Kantor Aktif:</strong> {{ $workLocation->name }}
                                <br>
                                <small>Latitude: {{ $workLocation->latitude }}, Longitude: {{ $workLocation->longitude }}</small>
                                <br>
                                <small>Radius: {{ $workLocation->radius }} meter</small>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                  rows="3">{{ old('notes', $attendance->notes) }}</textarea>
                        <small class="text-muted">Catatan admin akan tercatat di log</small>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-secondary">
                        <i class="fas fa-info-circle"></i>
                        <strong>Informasi:</strong>
                        <ul class="mt-2 mb-0">
                            <li>Perubahan akan tercatat di Log Aktivitas</li>
                            <li>Absensi yang diedit akan otomatis tervalidasi</li>
                            <li>Data lama akan tersimpan di log untuk audit</li>
                        </ul>
                    </div>

                    <div class="mt-4 text-end">
                        <a href="{{ route('admin.reports.attendance') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Batal
                        </a>
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
    // Validate checkout time after checkin time
    const checkInTime = document.querySelector('input[name="check_in_time"]');
    const checkOutTime = document.querySelector('input[name="check_out_time"]');

    function validateCheckOut() {
        if (checkInTime.value && checkOutTime.value && checkOutTime.value <= checkInTime.value) {
            checkOutTime.setCustomValidity('Waktu check out harus setelah waktu check in');
        } else {
            checkOutTime.setCustomValidity('');
        }
    }

    checkInTime.addEventListener('change', validateCheckOut);
    checkOutTime.addEventListener('change', validateCheckOut);
</script>
@endpush
