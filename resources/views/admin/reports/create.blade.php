@extends('layouts.app')

@section('title', 'Tambah Absensi')
@section('page-title', 'Tambah Absensi Manual')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="mx-auto col-md-8">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-plus-circle text-primary"></i> Tambah Absensi Baru
                </h5>

                <div class="mb-4 alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Informasi:</strong> Form ini digunakan untuk menambah absensi pegawai secara manual.
                </div>

                <form method="POST" action="{{ route('admin.reports.store') }}">
                    @csrf

                    <div class="row">
                        <div class="mb-3 col-md-12">
                            <label class="form-label">Pilih Pegawai <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Pegawai --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} - {{ $user->employee_id ?? 'ID: ' . $user->id }} ({{ $user->department ?? 'No Dept' }})
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
                                   value="{{ old('attendance_date', date('Y-m-d')) }}" required>
                            @error('attendance_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="present" {{ old('status') == 'present' ? 'selected' : '' }}>Hadir (Tepat Waktu)</option>
                                <option value="late" {{ old('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                                <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                                <option value="half_day" {{ old('status') == 'half_day' ? 'selected' : '' }}>Setengah Hari</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">Waktu Check In</label>
                            <input type="time" name="check_in_time" class="form-control @error('check_in_time') is-invalid @enderror"
                                   value="{{ old('check_in_time', '08:00') }}">
                            @error('check_in_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">Waktu Check Out</label>
                            <input type="time" name="check_out_time" class="form-control @error('check_out_time') is-invalid @enderror"
                                   value="{{ old('check_out_time', '17:00') }}">
                            @error('check_out_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">Keterlambatan (menit)</label>
                            <input type="number" name="late_minutes" class="form-control @error('late_minutes') is-invalid @enderror"
                                   value="{{ old('late_minutes', 0) }}" min="0">
                            @error('late_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">Latitude Check In</label>
                            <input type="number" step="any" name="check_in_latitude" class="form-control"
                                   value="{{ old('check_in_latitude', $workLocation->latitude ?? '-6.200000') }}">
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">Longitude Check In</label>
                            <input type="number" step="any" name="check_in_longitude" class="form-control"
                                   value="{{ old('check_in_longitude', $workLocation->longitude ?? '106.816666') }}">
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">Latitude Check Out</label>
                            <input type="number" step="any" name="check_out_latitude" class="form-control"
                                   value="{{ old('check_out_latitude', $workLocation->latitude ?? '-6.200000') }}">
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">Longitude Check Out</label>
                            <input type="number" step="any" name="check_out_longitude" class="form-control"
                                   value="{{ old('check_out_longitude', $workLocation->longitude ?? '106.816666') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                  rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Absensi yang ditambahkan akan langsung tervalidasi dan tercatat di log aktivitas.
                    </div>

                    <div class="text-end">
                        <a href="{{ route('admin.reports.attendance') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
