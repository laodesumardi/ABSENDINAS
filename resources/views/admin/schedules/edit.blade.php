@extends('layouts.app')

@section('title', 'Edit Jadwal Kerja - ' . $dayLabel)
@section('page-title', 'Edit Jadwal Kerja - ' . $dayLabel)

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="mx-auto col-md-8">
            <div class="stat-card">
                <div class="mb-4 alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Informasi:</strong>
                    <ul class="mt-2 mb-0">
                        <li>Waktu check in yang diatur akan langsung berlaku untuk absensi pegawai</li>
                        <li>Pegawai hanya bisa check in pada jam yang ditentukan</li>
                        <li>Batas check in yang disarankan: 08:00 - 16:00</li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('admin.schedules.update', $schedule->day_of_week) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_working_day" class="form-check-input"
                                   id="isWorkingDay" value="1" {{ $schedule->is_working_day ? 'checked' : '' }}>
                            <label class="form-check-label fs-5" for="isWorkingDay">
                                Hari Kerja
                            </label>
                        </div>
                        <small class="text-muted">Jika dicentang, pegawai wajib absen. Jika tidak, hari libur.</small>
                    </div>

                    <div id="workingHoursSection" style="{{ $schedule->is_working_day ? '' : 'display: none;' }}">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">Waktu Mulai Check In <span class="text-danger">*</span></label>
                                <input type="time" name="check_in_start"
                                       class="form-control"
                                       value="{{ old('check_in_start', $schedule->check_in_start ? \Carbon\Carbon::parse($schedule->check_in_start)->format('H:i') : '08:00') }}"
                                       step="60">
                                <small class="text-muted">Pegawai dapat mulai check in dari jam ini</small>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">Batas Akhir Check In <span class="text-danger">*</span></label>
                                <input type="time" name="check_in_end"
                                       class="form-control"
                                       value="{{ old('check_in_end', $schedule->check_in_end ? \Carbon\Carbon::parse($schedule->check_in_end)->format('H:i') : '16:00') }}"
                                       step="60">
                                <small class="text-muted">Setelah jam ini akan dianggap terlambat</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">Waktu Mulai Check Out <span class="text-danger">*</span></label>
                                <input type="time" name="check_out_start"
                                       class="form-control"
                                       value="{{ old('check_out_start', $schedule->check_out_start ? \Carbon\Carbon::parse($schedule->check_out_start)->format('H:i') : '17:00') }}"
                                       step="60">
                                <small class="text-muted">Pegawai dapat check out setelah jam ini</small>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">Batas Akhir Check Out <span class="text-danger">*</span></label>
                                <input type="time" name="check_out_end"
                                       class="form-control"
                                       value="{{ old('check_out_end', $schedule->check_out_end ? \Carbon\Carbon::parse($schedule->check_out_end)->format('H:i') : '18:00') }}"
                                       step="60">
                                <small class="text-muted">Batas maksimal check out</small>
                            </div>
                        </div>

                        <div class="mt-3 alert alert-info">
                            <i class="fas fa-clock"></i>
                            <strong>Ringkasan Jadwal:</strong>
                            <ul class="mt-2 mb-0">
                                <li>Periode Check In: <strong>{{ $schedule->check_in_window }}</strong></li>
                                <li>Periode Check Out: <strong>{{ $schedule->check_out_window }}</strong></li>
                                <li>Durasi Kerja: <strong>{{ $schedule->working_hours }}</strong></li>
                            </ul>
                        </div>
                    </div>

                    <div id="holidaySection" style="{{ $schedule->is_working_day ? 'display: none;' : '' }}">
                        <div class="alert alert-secondary">
                            <i class="fas fa-calendar-times"></i>
                            <strong>Hari Libur</strong>
                            <p class="mt-2 mb-0">Tidak ada jadwal kerja untuk hari ini. Pegawai tidak diwajibkan absen.</p>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary">Batal</a>
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
    const isWorkingDayCheckbox = document.getElementById('isWorkingDay');
    const workingHoursSection = document.getElementById('workingHoursSection');
    const holidaySection = document.getElementById('holidaySection');

    isWorkingDayCheckbox.addEventListener('change', function() {
        if (this.checked) {
            workingHoursSection.style.display = 'block';
            holidaySection.style.display = 'none';
        } else {
            workingHoursSection.style.display = 'none';
            holidaySection.style.display = 'block';
        }
    });
</script>
@endpush
