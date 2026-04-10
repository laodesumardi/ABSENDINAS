@extends('layouts.app')

@section('title', 'Edit Jadwal Kerja - ' . $dayLabel)
@section('page-title', 'Edit Jadwal Kerja - ' . $dayLabel)

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="stat-card">
                <form method="POST" action="{{ route('admin.schedules.update', $schedule->day_of_week) }}" id="scheduleForm">
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
                        <small class="text-muted">Jika tidak dicentang, maka hari tersebut akan dianggap libur</small>
                    </div>

                    <div id="workingHoursSection" style="{{ $schedule->is_working_day ? '' : 'display: none;' }}">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle"></i>
                            <strong>Urutan Waktu yang Benar:</strong>
                            <ol class="mb-0 mt-2">
                                <li>Waktu Mulai Check In</li>
                                <li>Waktu Akhir Check In (harus setelah Mulai Check In)</li>
                                <li>Waktu Mulai Check Out (harus setelah Akhir Check In)</li>
                                <li>Waktu Akhir Check Out (harus setelah Mulai Check Out)</li>
                            </ol>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu Mulai Check In <span class="text-danger">*</span></label>
                                <input type="time" name="check_in_start" id="check_in_start"
                                       class="form-control @error('check_in_start') is-invalid @enderror"
                                       value="{{ old('check_in_start', $schedule->check_in_start ? \Carbon\Carbon::parse($schedule->check_in_start)->format('H:i') : '08:00') }}"
                                       step="60">
                                @error('check_in_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Pegawai dapat mulai check in dari jam ini</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu Akhir Check In <span class="text-danger">*</span></label>
                                <input type="time" name="check_in_end" id="check_in_end"
                                       class="form-control @error('check_in_end') is-invalid @enderror"
                                       value="{{ old('check_in_end', $schedule->check_in_end ? \Carbon\Carbon::parse($schedule->check_in_end)->format('H:i') : '08:30') }}"
                                       step="60">
                                @error('check_in_end')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Setelah jam ini akan dianggap terlambat</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu Mulai Check Out <span class="text-danger">*</span></label>
                                <input type="time" name="check_out_start" id="check_out_start"
                                       class="form-control @error('check_out_start') is-invalid @enderror"
                                       value="{{ old('check_out_start', $schedule->check_out_start ? \Carbon\Carbon::parse($schedule->check_out_start)->format('H:i') : '17:00') }}"
                                       step="60">
                                @error('check_out_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Pegawai dapat check out setelah jam ini</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu Akhir Check Out <span class="text-danger">*</span></label>
                                <input type="time" name="check_out_end" id="check_out_end"
                                       class="form-control @error('check_out_end') is-invalid @enderror"
                                       value="{{ old('check_out_end', $schedule->check_out_end ? \Carbon\Carbon::parse($schedule->check_out_end)->format('H:i') : '18:00') }}"
                                       step="60">
                                @error('check_out_end')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Batas maksimal check out</small>
                            </div>
                        </div>

                        <div id="timeValidationAlert" class="alert alert-warning" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span id="timeValidationMessage"></span>
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="fas fa-clock"></i>
                            <strong>Ringkasan Jadwal:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Periode Check In: <span id="summaryCheckIn">-</span></li>
                                <li>Periode Check Out: <span id="summaryCheckOut">-</span></li>
                                <li>Durasi Kerja: <span id="summaryDuration">-</span></li>
                            </ul>
                        </div>
                    </div>

                    <div id="holidaySection" style="{{ $schedule->is_working_day ? 'display: none;' : '' }}">
                        <div class="alert alert-secondary">
                            <i class="fas fa-calendar-times"></i>
                            <strong>Hari Libur</strong>
                            <p class="mb-0 mt-2">Tidak ada jadwal kerja untuk hari ini. Pegawai tidak diwajibkan absen.</p>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary-custom" id="submitBtn">
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
    const timeValidationAlert = document.getElementById('timeValidationAlert');
    const timeValidationMessage = document.getElementById('timeValidationMessage');
    const submitBtn = document.getElementById('submitBtn');

    // Get form elements
    const checkInStart = document.getElementById('check_in_start');
    const checkInEnd = document.getElementById('check_in_end');
    const checkOutStart = document.getElementById('check_out_start');
    const checkOutEnd = document.getElementById('check_out_end');

    // Function to validate time sequence
    function validateTimeSequence() {
        if (!isWorkingDayCheckbox.checked) {
            timeValidationAlert.style.display = 'none';
            submitBtn.disabled = false;
            return true;
        }

        const startIn = checkInStart.value;
        const endIn = checkInEnd.value;
        const startOut = checkOutStart.value;
        const endOut = checkOutEnd.value;

        // Check if all fields are filled
        if (!startIn || !endIn || !startOut || !endOut) {
            timeValidationAlert.style.display = 'block';
            timeValidationMessage.innerHTML = 'Semua field waktu harus diisi!';
            submitBtn.disabled = true;
            return false;
        }

        // Validation 1: Check in start must be before check in end
        if (startIn >= endIn) {
            timeValidationAlert.style.display = 'block';
            timeValidationMessage.innerHTML = '❌ Waktu akhir check-in harus setelah waktu mulai check-in!';
            submitBtn.disabled = true;
            return false;
        }

        // Validation 2: Check in end must be before check out start
        if (endIn >= startOut) {
            timeValidationAlert.style.display = 'block';
            timeValidationMessage.innerHTML = '❌ Waktu mulai check-out harus setelah waktu akhir check-in!';
            submitBtn.disabled = true;
            return false;
        }

        // Validation 3: Check out start must be before check out end
        if (startOut >= endOut) {
            timeValidationAlert.style.display = 'block';
            timeValidationMessage.innerHTML = '❌ Waktu akhir check-out harus setelah waktu mulai check-out!';
            submitBtn.disabled = true;
            return false;
        }

        // All validations passed
        timeValidationAlert.style.display = 'none';
        submitBtn.disabled = false;

        // Update summary
        updateSummary();

        return true;
    }

    // Function to update summary
    function updateSummary() {
        if (!isWorkingDayCheckbox.checked) return;

        const startIn = checkInStart.value;
        const endIn = checkInEnd.value;
        const startOut = checkOutStart.value;
        const endOut = checkOutEnd.value;

        document.getElementById('summaryCheckIn').innerHTML = `${startIn} - ${endIn}`;
        document.getElementById('summaryCheckOut').innerHTML = `${startOut} - ${endOut}`;

        // Calculate duration
        if (startIn && endOut) {
            const start = new Date(`2000-01-01T${startIn}:00`);
            const end = new Date(`2000-01-01T${endOut}:00`);
            const diff = (end - start) / (1000 * 60 * 60);
            document.getElementById('summaryDuration').innerHTML = `${diff} jam`;
        }
    }

    // Add event listeners
    checkInStart?.addEventListener('change', validateTimeSequence);
    checkInEnd?.addEventListener('change', validateTimeSequence);
    checkOutStart?.addEventListener('change', validateTimeSequence);
    checkOutEnd?.addEventListener('change', validateTimeSequence);

    // Toggle working hours section
    isWorkingDayCheckbox.addEventListener('change', function() {
        if (this.checked) {
            workingHoursSection.style.display = 'block';
            holidaySection.style.display = 'none';
            validateTimeSequence();
        } else {
            workingHoursSection.style.display = 'none';
            holidaySection.style.display = 'block';
            timeValidationAlert.style.display = 'none';
            submitBtn.disabled = false;
        }
    });

    // Initial validation
    if (isWorkingDayCheckbox.checked) {
        validateTimeSequence();
    }

    // Prevent form submission if validation fails
    document.getElementById('scheduleForm').addEventListener('submit', function(e) {
        if (isWorkingDayCheckbox.checked && !validateTimeSequence()) {
            e.preventDefault();
            alert('Mohon perbaiki urutan waktu yang salah!');
        }
    });
</script>
@endpush
