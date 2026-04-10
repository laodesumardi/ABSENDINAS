@extends('layouts.app')

@section('title', 'Employee Dashboard')
@section('page-title', 'Dashboard Karyawan')

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Welcome Card -->
    <div class="row">
        <div class="col-12">
            <div class="stat-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4>Selamat Datang, {{ auth()->user()->name }}!</h4>
                        <p class="text-muted mb-0">
                            <i class="fas fa-calendar-alt"></i> {{ now()->format('l, d F Y') }}
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="user-avatar d-inline-block" style="width: 60px; height: 60px; font-size: 24px;">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Status -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="stat-card text-center">
                @if(!$todayAttendance)
                    <div class="mb-3">
                        <div class="stat-icon primary mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                            <i class="fas fa-fingerprint"></i>
                        </div>
                        <h5>Anda Belum Absen Hari Ini</h5>
                        <p class="text-muted">Silakan lakukan absen sekarang</p>
                        <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#checkInModal">
                            <i class="fas fa-sign-in-alt"></i> Check In
                        </button>
                    </div>
                @else
                    <div>
                        <div class="stat-icon success mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h5>Anda Sudah Absen</h5>
                        <p>Check In: {{ $todayAttendance->check_in_time }}</p>
                        @if($todayAttendance->check_out_time)
                            <p>Check Out: {{ $todayAttendance->check_out_time }}</p>
                            <span class="badge bg-success">Selesai</span>
                        @else
                            <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#checkOutModal">
                                <i class="fas fa-sign-out-alt"></i> Check Out
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-chart-line text-primary"></i> Statistik Bulan Ini
                </h5>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Kehadiran</span>
                        <span><strong>0</strong> hari</span>
                    </div>
                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-success" style="width: 0%"></div>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Terlambat</span>
                        <span><strong>0</strong> kali</span>
                    </div>
                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-warning" style="width: 0%"></div>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Izin</span>
                        <span><strong>0</strong> hari</span>
                    </div>
                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-info" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Menu -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-th-large text-primary"></i> Menu Cepat
                </h5>
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('employee.attendance.history') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-history"></i> Riwayat Absensi
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#leaveModal">
                            <i class="fas fa-calendar-plus"></i> Pengajuan Izin
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-user-edit"></i> Edit Profil
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-outline-primary w-100" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
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
                <h5 class="modal-title">Check In</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('employee.attendance.check-in') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Lokasi Check In</label>
                        <div id="locationStatus" class="alert alert-info">
                            <i class="fas fa-spinner fa-spin"></i> Mendapatkan lokasi...
                        </div>
                        <input type="hidden" name="latitude" id="latitude" required>
                        <input type="hidden" name="longitude" id="longitude" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Foto Selfie</label>
                        <input type="file" name="photo" class="form-control" accept="image/*" capture="environment" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom">Check In</button>
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
                <h5 class="modal-title">Check Out</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('employee.attendance.check-out') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Lokasi Check Out</label>
                        <div id="locationStatusOut" class="alert alert-info">
                            <i class="fas fa-spinner fa-spin"></i> Mendapatkan lokasi...
                        </div>
                        <input type="hidden" name="latitude" id="latitudeOut" required>
                        <input type="hidden" name="longitude" id="longitudeOut" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Foto Selfie</label>
                        <input type="file" name="photo" class="form-control" accept="image/*" capture="environment" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom">Check Out</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Get location for check in
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
        document.getElementById('latitude').value = position.coords.latitude;
        document.getElementById('longitude').value = position.coords.longitude;
        document.getElementById('locationStatus').innerHTML = '<i class="fas fa-check-circle"></i> Lokasi berhasil didapatkan';
        document.getElementById('locationStatus').className = 'alert alert-success';
    }, function(error) {
        document.getElementById('locationStatus').innerHTML = '<i class="fas fa-exclamation-triangle"></i> Gagal mendapatkan lokasi. Pastikan GPS aktif.';
        document.getElementById('locationStatus').className = 'alert alert-danger';
    });
}

// Get location for check out
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
        document.getElementById('latitudeOut').value = position.coords.latitude;
        document.getElementById('longitudeOut').value = position.coords.longitude;
        document.getElementById('locationStatusOut').innerHTML = '<i class="fas fa-check-circle"></i> Lokasi berhasil didapatkan';
        document.getElementById('locationStatusOut').className = 'alert alert-success';
    }, function(error) {
        document.getElementById('locationStatusOut').innerHTML = '<i class="fas fa-exclamation-triangle"></i> Gagal mendapatkan lokasi. Pastikan GPS aktif.';
        document.getElementById('locationStatusOut').className = 'alert alert-danger';
    });
}
</script>
@endpush
