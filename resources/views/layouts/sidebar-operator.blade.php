<div class="sidebar" id="sidebar">
    <div class="brand">
        <h3><i class="fas fa-fingerprint"></i> Absensi</h3>
        <p>Operator Panel</p>
    </div>

    <nav class="nav flex-column">
        <a class="nav-link" href="{{ route('operator.dashboard') }}">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a class="nav-link" href="{{ route('operator.leaves.index') }}">
            <i class="fas fa-envelope-open-text"></i> Pengajuan Izin
            <span class="badge bg-warning float-end mt-1" id="pendingLeavesCount">0</span>
        </a>
        <a class="nav-link" href="{{ route('operator.attendance.index') }}">
    <i class="fas fa-check-circle"></i> Validasi Absensi
</a>
        <a class="nav-link" href="{{ route('operator.employees.index') }}">
    <i class="fas fa-users"></i> Data Pegawai
</a>
        <a class="nav-link" href="{{ route('operator.reports.attendance') }}">
    <i class="fas fa-chart-bar"></i> Rekap Absensi
</a>
        <a class="nav-link" href="#">
            <i class="fas fa-download"></i> Export Data
        </a>
    </nav>

    <div class="position-absolute bottom-0 w-100 p-3">
        <div class="text-center text-white-50 small">
            <i class="fas fa-headset"></i> Support 24/7
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Fetch pending leaves count
    function updatePendingLeaves() {
        $.get('/operator/leaves/pending-count', function(data) {
            $('#pendingLeavesCount').text(data.count);
        });
    }
    setInterval(updatePendingLeaves, 30000);
    updatePendingLeaves();
</script>
@endpush
