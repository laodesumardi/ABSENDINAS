@extends('layouts.app')

@section('title', 'Edit Hari Libur')
@section('page-title', 'Edit Hari Libur')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="stat-card">
                <form method="POST" action="{{ route('admin.holidays.update', $holiday) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nama Libur <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $holiday->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="holiday_date" class="form-control @error('holiday_date') is-invalid @enderror"
                               value="{{ old('holiday_date', $holiday->holiday_date->format('Y-m-d')) }}" required>
                        @error('holiday_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Hari: {{ $holiday->day_name_indonesian }}
                        </small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_national_holiday" class="form-check-input"
                                   value="1" {{ old('is_national_holiday', $holiday->is_national_holiday) ? 'checked' : '' }}>
                            <label class="form-check-label">Libur Nasional</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi (Opsional)</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="3">{{ old('description', $holiday->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="text-end">
                        <a href="{{ route('admin.holidays.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
