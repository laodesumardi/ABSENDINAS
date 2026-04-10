<div class="sidebar" id="sidebar">
    <div class="brand">
        <h3><i class="fas fa-fingerprint"></i> Absensi</h3>
        <p>Employee Panel</p>
    </div>

    <nav class="nav flex-column">
        <a class="nav-link" href="{{ route('employee.attendance.index') }}">
            <i class="fas fa-clock"></i> Absensi Hari Ini
        </a>
        <a class="nav-link" href="{{ route('employee.attendance.history') }}">
            <i class="fas fa-history"></i> Riwayat Absensi
        </a>
        <a class="nav-link" href="{{ route('employee.leaves.index') }}">
    <i class="fas fa-calendar-plus"></i> Pengajuan Izin
</a>
       <a class="nav-link" href="{{ route('employee.statistics.index') }}">
    <i class="fas fa-chart-line"></i> Statistik Saya
</a>
        <a class="nav-link" href="{{ route('profile.edit') }}">
            <i class="fas fa-user-edit"></i> Profil Saya
        </a>
    </nav>

    <div class="position-absolute bottom-0 w-100 p-3">
        <div class="text-center text-white-50 small">
            <i class="fas fa-user-check"></i> Selamat Bekerja
        </div>
    </div>
</div>
