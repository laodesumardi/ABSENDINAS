@extends('layouts.app')

@section('title', 'Ajukan Izin / Cuti')
@section('page-title', 'Ajukan Izin / Cuti')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="stat-card">
                <form method="POST" action="{{ route('employee.leaves.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Jenis Izin / Cuti <span class="text-danger">*</span></label>
                        <select name="leave_type" class="form-select @error('leave_type') is-invalid @enderror" required>
                            <option value="">Pilih Jenis</option>
                            <option value="annual" {{ old('leave_type') == 'annual' ? 'selected' : '' }}>Cuti Tahunan</option>
                            <option value="sick" {{ old('leave_type') == 'sick' ? 'selected' : '' }}>Sakit</option>
                            <option value="personal" {{ old('leave_type') == 'personal' ? 'selected' : '' }}>Keperluan Pribadi</option>
                            <option value="emergency" {{ old('leave_type') == 'emergency' ? 'selected' : '' }}>Darurat</option>
                            <option value="maternity" {{ old('leave_type') == 'maternity' ? 'selected' : '' }}>Cuti Melahirkan</option>
                            <option value="other" {{ old('leave_type') == 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('leave_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date') }}" min="{{ date('Y-m-d') }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Akhir <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date') }}" min="{{ date('Y-m-d') }}" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alasan <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control @error('reason') is-invalid @enderror"
                                  rows="4" required placeholder="Jelaskan alasan pengajuan izin/cuti...">{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lampiran (Opsional)</label>
                        <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror"
                               accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">Format: JPG, PNG, PDF (Max: 2MB)</small>
                        @error('attachment')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Informasi:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Pengajuan akan diproses oleh operator HRD</li>
                            <li>Status pengajuan dapat dilihat di halaman daftar pengajuan</li>
                            <li>Pengajuan dapat diedit atau dibatalkan sebelum disetujui</li>
                        </ul>
                    </div>

                    <div class="text-end">
                        <a href="{{ route('employee.leaves.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-paper-plane"></i> Ajukan
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
    // Auto set end date minimum based on start date
    document.querySelector('input[name="start_date"]').addEventListener('change', function() {
        const endDateInput = document.querySelector('input[name="end_date"]');
        endDateInput.min = this.value;
        if (endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
    });
</script>
@endpush
