@extends('layouts.app')

@section('title', 'Input Absensi Manual')
@section('page-title', 'Input Absensi Manual')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-plus-circle text-primary"></i> Form Input Absensi Manual
                </h5>

                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle"></i>
                    <strong>Informasi:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Input manual digunakan untuk mengisi absensi pegawai yang lupa check in/out</li>
                        <li>Absensi yang diinput manual akan langsung tervalidasi</li>
                        <li>Pastikan data yang diinput sesuai dengan keadaan sebenarnya</li>
                        <li>Koordinat akan direkam untuk keperluan audit</li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('operator.attendance.store-manual') }}" id="attendanceForm">
                    @csrf

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Pilih Pegawai <span class="text-danger">*</span></label>
                            <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
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

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Absensi <span class="text-danger">*</span></label>
                            <input type="date" name="attendance_date" id="attendance_date"
                                   class="form-control @error('attendance_date') is-invalid @enderror"
                                   value="{{ old('attendance_date', date('Y-m-d')) }}" required>
                            @error('attendance_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status Kehadiran <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="present" {{ old('status') == 'present' ? 'selected' : '' }}>Hadir (Tepat Waktu)</option>
                                <option value="late" {{ old('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                                <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                                <option value="half_day" {{ old('status') == 'half_day' ? 'selected' : '' }}>Setengah Hari</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3" id="checkInContainer">
                            <label class="form-label">Waktu Check In</label>
                            <input type="time" name="check_in_time" id="check_in_time"
                                   class="form-control @error('check_in_time') is-invalid @enderror"
                                   value="{{ old('check_in_time', date('H:i')) }}">
                            <small class="text-muted">Kosongkan jika tidak hadir</small>
                            @error('check_in_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3" id="checkOutContainer">
                            <label class="form-label">Waktu Check Out</label>
                            <input type="time" name="check_out_time" id="check_out_time"
                                   class="form-control @error('check_out_time') is-invalid @enderror"
                                   value="{{ old('check_out_time') }}">
                            <small class="text-muted">Kosongkan jika belum/belum check out</small>
                            @error('check_out_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3" id="lateMinutesContainer">
                            <label class="form-label">Keterlambatan (menit)</label>
                            <input type="number" name="late_minutes" id="late_minutes"
                                   class="form-control @error('late_minutes') is-invalid @enderror"
                                   value="{{ old('late_minutes', 0) }}" min="0">
                            <small class="text-muted">Isi jika status Terlambat</small>
                            @error('late_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Koordinat Section -->
                    <div class="card mb-3" id="coordinatesSection">
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
                                               class="form-check-input" value="1" {{ old('use_office_location') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="use_office_location">
                                            Gunakan lokasi kantor ({{ $workLocation->name ?? 'Kantor' }})
                                        </label>
                                        <small class="text-muted d-block">Centang untuk menggunakan koordinat lokasi kantor</small>
                                    </div>
                                </div>
                            </div>

                            <div id="manualCoordinates" style="{{ old('use_office_location') ? 'display: none;' : '' }}">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Latitude Check In</label>
                                        <input type="number" step="any" name="check_in_latitude" id="check_in_latitude"
                                               class="form-control @error('check_in_latitude') is-invalid @enderror"
                                               value="{{ old('check_in_latitude', $workLocation->latitude ?? '-6.200000') }}">
                                        @error('check_in_latitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Longitude Check In</label>
                                        <input type="number" step="any" name="check_in_longitude" id="check_in_longitude"
                                               class="form-control @error('check_in_longitude') is-invalid @enderror"
                                               value="{{ old('check_in_longitude', $workLocation->longitude ?? '106.816666') }}">
                                        @error('check_in_longitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Latitude Check Out</label>
                                        <input type="number" step="any" name="check_out_latitude" id="check_out_latitude"
                                               class="form-control @error('check_out_latitude') is-invalid @enderror"
                                               value="{{ old('check_out_latitude', $workLocation->latitude ?? '-6.200000') }}">
                                        @error('check_out_latitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Longitude Check Out</label>
                                        <input type="number" step="any" name="check_out_longitude" id="check_out_longitude"
                                               class="form-control @error('check_out_longitude') is-invalid @enderror"
                                               value="{{ old('check_out_longitude', $workLocation->longitude ?? '106.816666') }}">
                                        @error('check_out_longitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="alert alert-secondary">
                                    <i class="fas fa-map"></i>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="getCurrentPosition()">
                                        <i class="fas fa-crosshairs"></i> Gunakan Lokasi Saya
                                    </button>
                                    <span id="locationResult" class="ms-2"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                  rows="3" placeholder="Contoh: Check in lupa, diinput manual oleh operator...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Preview Card -->
                    <div class="alert alert-secondary mt-3" id="previewCard" style="display: none;">
                        <h6><i class="fas fa-eye"></i> Preview Data</h6>
                        <div id="previewContent"></div>
                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('operator.attendance.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-save"></i> Simpan Absensi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info Card -->
        <div class="col-md-4">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-clock text-primary"></i> Jadwal Kerja
                </h5>
                <div id="scheduleInfo">
                    <p class="text-muted">Pilih tanggal terlebih dahulu</p>
                </div>
            </div>

            <div class="stat-card mt-4">
                <h5 class="mb-3">
                    <i class="fas fa-map-marker-alt text-primary"></i> Lokasi Kantor Aktif
                </h5>
                @if($workLocation)
                    <p><strong>{{ $workLocation->name }}</strong></p>
                    <p>Latitude: {{ $workLocation->latitude }}</p>
                    <p>Longitude: {{ $workLocation->longitude }}</p>
                    <p>Radius: {{ $workLocation->radius }} meter</p>
                @else
                    <p class="text-muted">Belum ada lokasi kantor yang dikonfigurasi</p>
                @endif
            </div>

            <div class="stat-card mt-4">
                <h5 class="mb-3">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Perhatian
                </h5>
                <ul class="mb-0">
                    <li>Input manual akan langsung tervalidasi</li>
                    <li>Tidak dapat dihapus, hanya bisa diedit</li>
                    <li>Pastikan data benar sebelum menyimpan</li>
                    <li>Koordinat akan direkam untuk audit</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Show/hide fields based on status
        function toggleFields() {
            const status = $('#status').val();
            const isAbsent = status === 'absent';

            if (isAbsent) {
                $('#checkInContainer').hide();
                $('#checkOutContainer').hide();
                $('#lateMinutesContainer').hide();
                $('#coordinatesSection').hide();
                $('#check_in_time').val('');
                $('#check_out_time').val('');
                $('#late_minutes').val(0);
            } else {
                $('#checkInContainer').show();
                $('#checkOutContainer').show();
                $('#coordinatesSection').show();

                if (status === 'late') {
                    $('#lateMinutesContainer').show();
                } else {
                    $('#lateMinutesContainer').hide();
                    $('#late_minutes').val(0);
                }
            }
        }

        // Toggle manual/office coordinates
        function toggleCoordinates() {
            const useOffice = $('#use_office_location').is(':checked');
            if (useOffice) {
                $('#manualCoordinates').hide();
            } else {
                $('#manualCoordinates').show();
            }
        }

        // Load schedule info
        function loadScheduleInfo() {
            const date = $('#attendance_date').val();
            if (date) {
                $.get('{{ route("operator.attendance.get-schedule") }}', {date: date}, function(response) {
                    if (response.success) {
                        let html = '';
                        if (response.schedule) {
                            if (response.schedule.is_working_day) {
                                html = `
                                    <p><strong><i class="fas fa-calendar-check text-success"></i> Hari Kerja</strong></p>
                                    <p><i class="fas fa-clock"></i> Check In: ${response.schedule.check_in_window}</p>
                                    <p><i class="fas fa-clock"></i> Check Out: ${response.schedule.check_out_window}</p>
                                    <p><i class="fas fa-hourglass-half"></i> Durasi: ${response.schedule.working_hours}</p>
                                `;
                            } else {
                                html = '<p><strong><i class="fas fa-calendar-times text-danger"></i> Hari Libur</strong></p>';
                            }
                        } else {
                            html = '<p class="text-muted">Jadwal belum diatur</p>';
                        }
                        $('#scheduleInfo').html(html);
                    }
                });
            }
        }

        // Preview data
        function updatePreview() {
            const user = $('#user_id option:selected').text();
            const date = $('#attendance_date').val();
            const status = $('#status option:selected').text();
            const checkIn = $('#check_in_time').val() || '-';
            const checkOut = $('#check_out_time').val() || '-';
            const lateMinutes = $('#late_minutes').val() || '0';
            const notes = $('#notes').val() || '-';

            let coordText = '';
            const useOffice = $('#use_office_location').is(':checked');
            if (useOffice) {
                coordText = 'Menggunakan lokasi kantor';
            } else {
                const latIn = $('#check_in_latitude').val();
                const lngIn = $('#check_in_longitude').val();
                if (latIn && lngIn) {
                    coordText = `Check In: ${latIn}, ${lngIn}`;
                }
                const latOut = $('#check_out_latitude').val();
                const lngOut = $('#check_out_longitude').val();
                if (latOut && lngOut) {
                    coordText += ` | Check Out: ${latOut}, ${lngOut}`;
                }
            }

            if (user && date) {
                $('#previewCard').show();
                $('#previewContent').html(`
                    <table class="table table-sm table-borderless">
                        <tr><th width="35%">Pegawai</th><td>${user}</td></tr>
                        <tr><th>Tanggal</th><td>${date}</td></tr>
                        <tr><th>Status</th><td>${status}</td></tr>
                        <tr><th>Check In</th><td>${checkIn}</td></tr>
                        <tr><th>Check Out</th><td>${checkOut}</td></tr>
                        <tr><th>Keterlambatan</th><td>${lateMinutes} menit</td></tr>
                        <tr><th>Koordinat</th><td>${coordText || '-'}</td></tr>
                        <tr><th>Catatan</th><td>${notes.substring(0, 100)}</td></tr>
                    </table>
                `);
            } else {
                $('#previewCard').hide();
            }
        }

        // Get current position
        window.getCurrentPosition = function() {
            if (navigator.geolocation) {
                $('#locationResult').html('<i class="fas fa-spinner fa-spin"></i> Mendapatkan lokasi...');
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    $('#check_in_latitude').val(lat.toFixed(8));
                    $('#check_in_longitude').val(lng.toFixed(8));
                    $('#check_out_latitude').val(lat.toFixed(8));
                    $('#check_out_longitude').val(lng.toFixed(8));
                    $('#locationResult').html('<i class="fas fa-check-circle text-success"></i> Lokasi berhasil didapatkan');
                    updatePreview();
                }, function(error) {
                    let errorMsg = 'Gagal mendapatkan lokasi';
                    if (error.code === 1) errorMsg = 'Izin lokasi ditolak';
                    if (error.code === 2) errorMsg = 'Posisi tidak tersedia';
                    if (error.code === 3) errorMsg = 'Timeout';
                    $('#locationResult').html('<i class="fas fa-exclamation-triangle text-danger"></i> ' + errorMsg);
                });
            } else {
                $('#locationResult').html('<i class="fas fa-exclamation-triangle text-danger"></i> Browser tidak support GPS');
            }
        };

        // Event listeners
        $('#status').on('change', function() {
            toggleFields();
            updatePreview();
        });

        $('#attendance_date').on('change', function() {
            loadScheduleInfo();
            updatePreview();
        });

        $('#use_office_location').on('change', function() {
            toggleCoordinates();
            updatePreview();
        });

        $('#user_id, #check_in_time, #check_out_time, #late_minutes, #notes, #check_in_latitude, #check_in_longitude, #check_out_latitude, #check_out_longitude').on('change keyup', function() {
            updatePreview();
        });

        // Auto calculate late minutes
        function calculateLateMinutes() {
            const checkInTime = $('#check_in_time').val();
            const date = $('#attendance_date').val();
            const status = $('#status').val();

            if (status === 'late' && checkInTime && date) {
                $.get('{{ route("operator.attendance.calculate-late") }}', {
                    date: date,
                    check_in_time: checkInTime
                }, function(response) {
                    if (response.late_minutes > 0) {
                        $('#late_minutes').val(response.late_minutes);
                        updatePreview();
                    }
                });
            }
        }

        $('#check_in_time').on('change', function() {
            if ($('#status').val() === 'late') {
                calculateLateMinutes();
            }
        });

        // Initial calls
        toggleFields();
        toggleCoordinates();
        loadScheduleInfo();
        updatePreview();

        // Form validation before submit
        $('#attendanceForm').on('submit', function(e) {
            const status = $('#status').val();
            const checkInTime = $('#check_in_time').val();

            if (status !== 'absent' && !checkInTime) {
                e.preventDefault();
                alert('Waktu check in harus diisi untuk status Hadir/Terlambat');
                return false;
            }

            if (confirm('Pastikan data yang diinput sudah benar. Lanjutkan?')) {
                return true;
            }
            return false;
        });
    });
</script>
@endpush
