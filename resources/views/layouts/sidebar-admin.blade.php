<div class="sidebar" id="sidebar">
    <div class="brand">
        <h3><i class="fas fa-fingerprint"></i> Absensi</h3>
        <p>Administrator Panel</p>
    </div>

    <nav class="nav flex-column">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a class="nav-link" href="{{ route('admin.users.index') }}">
            <i class="fas fa-users"></i> Manajemen User
        </a>
        <a class="nav-link" href="{{ route('admin.reports.attendance') }}">
            <i class="fas fa-chart-line"></i> Laporan Absensi
        </a>
        <a class="nav-link" href="{{ route('admin.locations.index') }}">
    <i class="fas fa-building"></i> Lokasi Kerja
</a>
        <a class="nav-link" href="{{ route('admin.schedules.index') }}">
    <i class="fas fa-calendar-alt"></i> Jadwal Kerja
</a>
        <a class="nav-link" href="{{ route('admin.holidays.index') }}">
    <i class="fas fa-gift"></i> Hari Libur
</a>
        <a class="nav-link" href="{{ route('admin.activity.index') }}">
    <i class="fas fa-history"></i> Log Aktivitas
</a>
    </nav>

    <div class="position-absolute bottom-0 w-100 p-3">
        <div class="text-center text-white-50 small">
            <i class="fas fa-shield-alt"></i> Sistem Absensi v1.0
        </div>
    </div>
</div>
