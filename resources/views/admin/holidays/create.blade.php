@extends('layouts.app')

@section('title', 'Tambah Hari Libur')
@section('page-title', 'Tambah Hari Libur Baru')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="stat-card">
                <form method="POST" action="{{ route('admin.holidays.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Nama Libur <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required placeholder="Contoh: Tahun Baru Masehi">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="holiday_date" class="form-control @error('holiday_date') is-invalid @enderror"
                               value="{{ old('holiday_date') }}" required>
                        @error('holiday_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Pilih tanggal libur</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_national_holiday" class="form-check-input"
                                   value="1" {{ old('is_national_holiday') ? 'checked' : '' }}>
                            <label class="form-check-label">Libur Nasional</label>
                        </div>
                        <small class="text-muted">Centang jika ini adalah hari libur nasional</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi (Opsional)</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="3" placeholder="Keterangan tambahan tentang libur ini">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="text-end">
                        <a href="{{ route('admin.holidays.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-info-circle"></i> Informasi
                </h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-calendar-day text-primary"></i>
                        Hari libur akan mempengaruhi perhitungan absensi
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-flag-checkered text-danger"></i>
                        Libur nasional ditandai dengan warna merah di kalender
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-bell text-warning"></i>
                        Pegawai akan mendapat notifikasi tentang libur mendatang
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
