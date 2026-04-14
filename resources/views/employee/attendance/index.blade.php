@extends('layouts.app')

@section('title', 'Absensi Hari Ini')
@section('page-title', 'Absensi Hari Ini')

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Welcome Card -->
    <div class="row">
        <div class="col-12">
            <div class="stat-card" style="background: linear-gradient(135deg, #3a0ca3, #2c0a7a); color: white;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4>Selamat {{ \Carbon\Carbon::now()->format('H') < 12 ? 'Pagi' : (\Carbon\Carbon::now()->format('H') < 18 ? 'Siang' : 'Malam') }}, {{ auth()->user()->name }}!</h4>
                        <p class="mb-0">
                            <i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::now()->format('l, d F Y') }}
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="user-avatar d-inline-block" style="width: 60px; height: 60px; font-size: 24px; background: rgba(255,255,255,0.2);">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Holiday Alert -->
    @if(isset($isHoliday) && $isHoliday && isset($holidayInfo))
    <div class="mt-3 row">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="fas fa-gift"></i>
                <strong>Hari Libur!</strong> {{ $holidayInfo->name }}
                <p class="mt-1 mb-0">Anda tidak perlu melakukan absensi hari ini.</p>
            </div>
        </div>
    </div>
    @endif

  <!-- Schedule Info -->
<div class="mt-3 row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-clock"></i>
            <strong>Jadwal Hari Ini:</strong>
            @if(isset($schedule) && $schedule && $schedule->is_working_day)
                Check In: Mulai jam {{ date('H:i', strtotime($schedule->check_in_start)) }}
                @if($schedule->check_in_end)
                    (Terlambat setelah {{ date('H:i', strtotime($schedule->check_in_end)) }})
                @endif
                | Check Out: Mulai jam {{ date('H:i', strtotime($schedule->check_out_start)) }}
            @else
                Hari Libur / Tidak ada jadwal kerja
            @endif
        </div>
    </div>
</div>

 <!-- Check In Card -->
@if($todayAttendance && $todayAttendance->check_in_time)
    <div class="mb-3">
        <div class="mx-auto mb-3 stat-icon" style="width: 80px; height: 80px; font-size: 2.5rem; background: {{ $todayAttendance->status == 'late' ? 'linear-gradient(135deg, #ffc107, #e6b800)' : 'linear-gradient(135deg, #06d6a0, #05a07a)' }};">
            <i class="fas {{ $todayAttendance->status == 'late' ? 'fa-clock' : 'fa-check-circle' }}"></i>
        </div>
        <h5>Check In</h5>
        <h2 class="{{ $todayAttendance->status == 'late' ? 'text-warning' : 'text-success' }}">
            {{ \Carbon\Carbon::parse($todayAttendance->check_in_time)->format('H:i:s') }}
        </h2>
        @if($todayAttendance->status == 'late' && $todayAttendance->late_minutes > 0)
            <span class="badge bg-warning">Terlambat {{ $todayAttendance->late_minutes }} menit</span>
        @elseif($todayAttendance->status == 'late')
            <span class="badge bg-warning">Terlambat</span>
        @else
            <span class="badge bg-success">Tepat Waktu</span>
        @endif

        <!-- Tampilan Foto Check In -->
        @if($todayAttendance->check_in_photo)
            <div class="mt-3">
                <img src="{{ $todayAttendance->check_in_photo_url }}"
                     alt="Foto Check In"
                     class="rounded img-fluid"
                     style="max-width: 150px; cursor: pointer;"
                     onclick="showPhoto('{{ $todayAttendance->check_in_photo_url }}', 'Foto Check In - {{ $todayAttendance->attendance_date->format('d/m/Y') }}')">
                <br>
                <button type="button" class="mt-2 btn btn-sm btn-info" onclick="showPhoto('{{ $todayAttendance->check_in_photo_url }}', 'Foto Check In')">
                    <i class="fas fa-camera"></i> Lihat Foto
                </button>
            </div>
        @endif
    </div>
@endif

<!-- Check Out Card -->
@if($todayAttendance && $todayAttendance->check_out_time)
    <div class="mb-3">
        <div class="mx-auto mb-3 stat-icon" style="width: 80px; height: 80px; font-size: 2.5rem; background: linear-gradient(135deg, #118ab2, #0e6d8a);">
            <i class="fas fa-check-double"></i>
        </div>
        <h5>Check Out</h5>
        <h2 class="text-info">
            {{ \Carbon\Carbon::parse($todayAttendance->check_out_time)->format('H:i:s') }}
        </h2>
        @if($todayAttendance->early_checkout_minutes > 0)
            <span class="badge bg-warning">Pulang awal {{ $todayAttendance->early_checkout_minutes }} menit</span>
        @endif

        <!-- Tampilan Foto Check Out -->
        @if($todayAttendance->check_out_photo)
            <div class="mt-3">
                <img src="{{ $todayAttendance->check_out_photo_url }}"
                     alt="Foto Check Out"
                     class="rounded img-fluid"
                     style="max-width: 150px; cursor: pointer;"
                     onclick="showPhoto('{{ $todayAttendance->check_out_photo_url }}', 'Foto Check Out - {{ $todayAttendance->attendance_date->format('d/m/Y') }}')">
                <br>
                <button type="button" class="mt-2 btn btn-sm btn-info" onclick="showPhoto('{{ $todayAttendance->check_out_photo_url }}', 'Foto Check Out')">
                    <i class="fas fa-camera"></i> Lihat Foto
                </button>
            </div>
        @endif
    </div>
@endif
                </div>
            @else
                <div class="mb-3">
                    <div class="mx-auto mb-3 stat-icon primary" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <h5>Check In</h5>
                    @if(isset($canCheckIn) && $canCheckIn && (!isset($isHoliday) || !$isHoliday))
                        <p class="text-muted">Silakan lakukan check in</p>
                        <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#checkInModal">
                            <i class="fas fa-sign-in-alt"></i> Check In Sekarang
                        </button>
                    @elseif(isset($isHoliday) && $isHoliday)
                        <p class="text-muted">Hari libur, tidak perlu check in</p>
                    @else
                        <p class="text-muted">Belum waktunya check in atau sudah melewati batas waktu</p>
                        <small class="text-muted">Waktu check in: {{ $checkInWindow ?? 'Belum diatur' }}</small>
                    @endif
                </div>
            @endif
        </div>
    </div>


    <!-- Check Out Card -->
    <div class="col-md-6">
        <div class="text-center stat-card">
            @if(isset($todayAttendance) && $todayAttendance && $todayAttendance->check_out_time)
                <div class="mb-3">
                    <div class="mx-auto mb-3 stat-icon" style="width: 80px; height: 80px; font-size: 2.5rem; background: linear-gradient(135deg, #118ab2, #0e6d8a);">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <h5>Check Out</h5>
                    <h2 class="text-info">
                        {{ \Carbon\Carbon::parse($todayAttendance->check_out_time)->format('H:i:s') }}
                    </h2>
                    @if($todayAttendance->early_checkout_minutes > 0)
                        <span class="badge bg-warning">Pulang awal {{ $todayAttendance->early_checkout_minutes }} menit</span>
                    @endif
                    @if($todayAttendance->check_out_photo)
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-info" onclick="showPhoto('{{ Storage::url($todayAttendance->check_out_photo) }}', 'Foto Check Out')">
                                <i class="fas fa-camera"></i> Lihat Foto
                            </button>
                        </div>
                    @endif
                </div>
            @elseif(isset($todayAttendance) && $todayAttendance && $todayAttendance->check_in_time && !$todayAttendance->check_out_time)
                <div class="mb-3">
                    <div class="mx-auto mb-3 stat-icon warning" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <h5>Check Out</h5>
                    @if(isset($canCheckOut) && $canCheckOut && (!isset($isHoliday) || !$isHoliday))
                        <p class="text-muted">Silakan lakukan check out</p>
                        <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#checkOutModal">
                            <i class="fas fa-sign-out-alt"></i> Check Out Sekarang
                        </button>
                    @else
                        <p class="text-muted">Belum waktunya check out</p>
                        <small class="text-muted">Waktu check out: {{ $checkOutWindow ?? 'Belum diatur' }}</small>
                    @endif
                </div>
            @else
                <div class="mb-3">
                    <div class="mx-auto mb-3 stat-icon secondary" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h5>Check Out</h5>
                    <p class="text-muted">Silakan check in terlebih dahulu</p>
                </div>
            @endif
        </div>
    </div>
</div>

    <!-- Monthly Statistics -->
    <div class="mt-4 row">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-chart-line text-primary"></i> Statistik Bulan {{ \Carbon\Carbon::now()->format('F Y') }}
                </h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="p-3 text-center border rounded">
                            <h3 class="text-primary">{{ isset($monthStats) ? $monthStats->total ?? 0 : 0 }}</h3>
                            <p class="mb-0 text-muted">Total Kehadiran</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 text-center border rounded">
                            <h3 class="text-success">{{ isset($monthStats) ? $monthStats->present ?? 0 : 0 }}</h3>
                            <p class="mb-0 text-muted">Tepat Waktu</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 text-center border rounded">
                            <h3 class="text-warning">{{ isset($monthStats) ? $monthStats->late ?? 0 : 0 }}</h3>
                            <p class="mb-0 text-muted">Terlambat</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 text-center border rounded">
                            <h3 class="text-danger">{{ isset($monthStats) ? $monthStats->absent ?? 0 : 0 }}</h3>
                            <p class="mb-0 text-muted">Tidak Hadir</p>
                        </div>
                    </div>
                </div>
                @if(isset($monthStats) && $monthStats->total_late_minutes > 0)
                    <div class="mt-3 text-center">
                        <small class="text-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Total keterlambatan: {{ floor($monthStats->total_late_minutes / 60) }} jam {{ $monthStats->total_late_minutes % 60 }} menit
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Attendances -->
    <div class="mt-4 row">
        <div class="col-12">
            <div class="stat-card">
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history text-primary"></i> Riwayat Absensi Terbaru
                    </h5>
                    <a href="{{ route('employee.attendance.history') }}" class="btn btn-sm btn-primary-custom">
                        Lihat Semua <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Keterlambatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($recentAttendances) && $recentAttendances->count() > 0)
                                @foreach($recentAttendances as $attendance)
                                <tr>
                                    <td>{{ $attendance->attendance_date->format('d/m/Y') }}</td>
                                    <td>{{ $attendance->check_in_time }}</td>
                                    <td>{{ $attendance->check_out_time ?? '-' }}</td>
                                    <td>
                                        @php
                                            $badgeColor = [
                                                'present' => 'success',
                                                'late' => 'warning',
                                                'absent' => 'danger',
                                                'half_day' => 'info'
                                            ][$attendance->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $badgeColor }}">
                                            {{ ucfirst($attendance->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($attendance->late_minutes > 0)
                                            <span class="text-warning">{{ $attendance->late_minutes }} menit</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada riwayat absensi</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Check In Modal -->
<div class="modal fade" id="checkInModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #3a0ca3, #2c0a7a); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-sign-in-alt"></i> Check In
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('employee.attendance.check-in') }}" method="POST" enctype="multipart/form-data" id="checkInForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary w-100" id="getLocationBtn" onclick="getCurrentLocation()">
                            <i class="fas fa-map-marker-alt"></i> Ambil Lokasi Saat Ini
                        </button>
                    </div>

                    <div id="locationStatus" class="alert alert-secondary" style="display: none;">
                        <i class="fas fa-info-circle"></i> <span id="locationText">Klik tombol di atas untuk mengambil lokasi</span>
                    </div>

                    <input type="hidden" name="latitude" id="checkInLatitude" required>
                    <input type="hidden" name="longitude" id="checkInLongitude" required>

                    <div class="mt-3 mb-3">
                        <label class="form-label">Foto Selfie (Opsional)</label>
                        <input type="file" name="photo" class="form-control" accept="image/*" capture="environment">
                        <small class="text-muted">Ambil foto selfie untuk verifikasi</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan jika ada..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom" id="submitCheckIn" disabled>
                        <i class="fas fa-check"></i> Check In
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Check Out Modal -->
<div class="modal fade" id="checkOutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #3a0ca3, #2c0a7a); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-sign-out-alt"></i> Check Out
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('employee.attendance.check-out') }}" method="POST" enctype="multipart/form-data" id="checkOutForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary w-100" id="getLocationOutBtn" onclick="getCurrentLocationForCheckOut()">
                            <i class="fas fa-map-marker-alt"></i> Ambil Lokasi Saat Ini
                        </button>
                    </div>

                    <div id="locationStatusOut" class="alert alert-secondary" style="display: none;">
                        <i class="fas fa-info-circle"></i> <span id="locationTextOut">Klik tombol di atas untuk mengambil lokasi</span>
                    </div>

                    <input type="hidden" name="latitude" id="checkOutLatitude" required>
                    <input type="hidden" name="longitude" id="checkOutLongitude" required>

                    <div class="mt-3 mb-3">
                        <label class="form-label">Foto Selfie (Opsional)</label>
                        <input type="file" name="photo" class="form-control" accept="image/*" capture="environment">
                        <small class="text-muted">Ambil foto selfie untuk verifikasi</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom" id="submitCheckOut" disabled>
                        <i class="fas fa-check"></i> Check Out
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Photo Modal -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalTitle">Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="text-center modal-body">
                <img id="photoImage" src="" alt="Foto" style="max-width: 100%; border-radius: 10px;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Fungsi untuk mengambil lokasi check in
function getCurrentLocation() {
    const locationStatus = document.getElementById('locationStatus');
    const locationText = document.getElementById('locationText');
    const submitBtn = document.getElementById('submitCheckIn');
    const getLocationBtn = document.getElementById('getLocationBtn');

    if (!navigator.geolocation) {
        locationStatus.style.display = 'block';
        locationStatus.className = 'alert alert-danger';
        locationText.innerHTML = 'Browser tidak mendukung GPS. Silakan gunakan Chrome atau Firefox.';
        submitBtn.disabled = true;
        return;
    }

    getLocationBtn.disabled = true;
    getLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mendapatkan lokasi...';
    locationStatus.style.display = 'block';
    locationStatus.className = 'alert alert-info';
    locationText.innerHTML = 'Mendapatkan lokasi...';

    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const accuracy = position.coords.accuracy;

            document.getElementById('checkInLatitude').value = lat;
            document.getElementById('checkInLongitude').value = lng;

            $.get('{{ route("employee.attendance.current-location") }}', {
                latitude: lat,
                longitude: lng
            }, function(response) {
                if (response.success) {
                    if (response.is_valid) {
                        locationStatus.className = 'alert alert-success';
                        locationText.innerHTML = '<i class="fas fa-check-circle"></i> Lokasi valid! Anda berada di dalam radius kantor.<br><small>Jarak: ' + response.distance + ' meter</small>';
                        submitBtn.disabled = false;
                    } else {
                        locationStatus.className = 'alert alert-danger';
                        locationText.innerHTML = '<i class="fas fa-times-circle"></i> Anda berada di luar radius kantor!<br><small>Jarak: ' + response.distance + ' meter (Maksimal: ' + response.max_distance + ' meter)</small>';
                        submitBtn.disabled = true;
                    }
                } else {
                    locationStatus.className = 'alert alert-danger';
                    locationText.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Gagal validasi lokasi.';
                    submitBtn.disabled = true;
                }
                getLocationBtn.disabled = false;
                getLocationBtn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Ambil Lokasi Lagi';
            }).fail(function() {
                locationStatus.className = 'alert alert-danger';
                locationText.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Gagal koneksi ke server.';
                submitBtn.disabled = true;
                getLocationBtn.disabled = false;
                getLocationBtn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Ambil Lokasi Lagi';
            });
        },
        function(error) {
            let errorMessage = '';
            switch(error.code) {
                case 1: errorMessage = 'Izin lokasi ditolak. Silakan izinkan akses lokasi.'; break;
                case 2: errorMessage = 'Tidak dapat mendeteksi lokasi. Pastikan GPS aktif.'; break;
                case 3: errorMessage = 'Timeout. Coba lagi.'; break;
                default: errorMessage = error.message || 'Gagal mendapatkan lokasi.';
            }
            locationStatus.style.display = 'block';
            locationStatus.className = 'alert alert-danger';
            locationText.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + errorMessage;
            submitBtn.disabled = true;
            getLocationBtn.disabled = false;
            getLocationBtn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Ambil Lokasi Lagi';
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

// Fungsi untuk mengambil lokasi check out
function getCurrentLocationForCheckOut() {
    const locationStatus = document.getElementById('locationStatusOut');
    const locationText = document.getElementById('locationTextOut');
    const submitBtn = document.getElementById('submitCheckOut');
    const getLocationBtn = document.getElementById('getLocationOutBtn');

    if (!navigator.geolocation) {
        locationStatus.style.display = 'block';
        locationStatus.className = 'alert alert-danger';
        locationText.innerHTML = 'Browser tidak mendukung GPS.';
        submitBtn.disabled = true;
        return;
    }

    getLocationBtn.disabled = true;
    getLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mendapatkan lokasi...';
    locationStatus.style.display = 'block';
    locationStatus.className = 'alert alert-info';
    locationText.innerHTML = 'Mendapatkan lokasi...';

    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            document.getElementById('checkOutLatitude').value = lat;
            document.getElementById('checkOutLongitude').value = lng;

            $.get('{{ route("employee.attendance.current-location") }}', {
                latitude: lat,
                longitude: lng
            }, function(response) {
                if (response.success) {
                    if (response.is_valid) {
                        locationStatus.className = 'alert alert-success';
                        locationText.innerHTML = '<i class="fas fa-check-circle"></i> Lokasi valid! Jarak: ' + response.distance + ' meter';
                        submitBtn.disabled = false;
                    } else {
                        locationStatus.className = 'alert alert-danger';
                        locationText.innerHTML = '<i class="fas fa-times-circle"></i> Di luar radius kantor! Jarak: ' + response.distance + ' meter';
                        submitBtn.disabled = true;
                    }
                } else {
                    locationStatus.className = 'alert alert-danger';
                    locationText.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Gagal validasi lokasi.';
                    submitBtn.disabled = true;
                }
                getLocationBtn.disabled = false;
                getLocationBtn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Ambil Lokasi Lagi';
            }).fail(function() {
                locationStatus.className = 'alert alert-danger';
                locationText.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Gagal koneksi.';
                submitBtn.disabled = true;
                getLocationBtn.disabled = false;
                getLocationBtn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Ambil Lokasi Lagi';
            });
        },
        function(error) {
            locationStatus.style.display = 'block';
            locationStatus.className = 'alert alert-danger';
            locationText.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Gagal mendapatkan lokasi.';
            submitBtn.disabled = true;
            getLocationBtn.disabled = false;
            getLocationBtn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Ambil Lokasi Lagi';
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

// Reset modal saat ditutup
$('#checkInModal').on('hidden.bs.modal', function() {
    document.getElementById('locationStatus').style.display = 'none';
    document.getElementById('checkInLatitude').value = '';
    document.getElementById('checkInLongitude').value = '';
    document.getElementById('submitCheckIn').disabled = true;
    const btn = document.getElementById('getLocationBtn');
    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Ambil Lokasi Saat Ini'; }
});

$('#checkOutModal').on('hidden.bs.modal', function() {
    document.getElementById('locationStatusOut').style.display = 'none';
    document.getElementById('checkOutLatitude').value = '';
    document.getElementById('checkOutLongitude').value = '';
    document.getElementById('submitCheckOut').disabled = true;
    const btn = document.getElementById('getLocationOutBtn');
    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Ambil Lokasi Saat Ini'; }
});

function showPhoto(url, title) {
    document.getElementById('photoModalTitle').textContent = title;
    document.getElementById('photoImage').src = url;
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}
</script>
@endpush
