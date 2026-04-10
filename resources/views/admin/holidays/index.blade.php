@extends('layouts.app')

@section('title', 'Manajemen Hari Libur')
@section('page-title', 'Manajemen Hari Libur')

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Libur {{ $year }}</h6>
                        <h3 class="mb-0">{{ $totalHolidays }}</h3>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Libur Nasional</h6>
                        <h3 class="mb-0">{{ $nationalHolidays }}</h3>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Akan Datang</h6>
                        <h3 class="mb-0 text-warning">{{ $upcomingHolidays }}</h3>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Sudah Lewat</h6>
                        <h3 class="mb-0 text-muted">{{ $pastHolidays }}</h3>
                    </div>
                    <div class="stat-icon secondary">
                        <i class="fas fa-history"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <form method="GET" action="{{ route('admin.holidays.index') }}" class="row g-2">
                            <div class="col-md-6">
                                <select name="year" class="form-select">
                                    @foreach($years as $y)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                            Tahun {{ $y }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Akan Datang</option>
                                    <option value="past" {{ request('status') == 'past' ? 'selected' : '' }}>Sudah Lewat</option>
                                </select>
                            </div>
                            <div class="col-md-12 mt-2">
                                <button type="submit" class="btn btn-primary-custom w-100">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-8 text-md-end mt-3 mt-md-0">
                        <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary-custom">
                            <i class="fas fa-plus"></i> Tambah Libur
                        </a>
                        <a href="{{ route('admin.holidays.calendar') }}" class="btn btn-info">
                            <i class="fas fa-calendar-alt"></i> Kalender
                        </a>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-download"></i> Import Libur Nasional
                        </button>
                        <button type="button" class="btn btn-danger" id="bulkDeleteBtn" style="display: none;">
                            <i class="fas fa-trash"></i> Hapus Terpilih
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Holidays Alert -->
    @if($upcomingList->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-bell"></i>
                <strong>Libur Mendatang:</strong>
                @foreach($upcomingList->take(3) as $holiday)
                    {{ $holiday->name }} ({{ $holiday->formatted_date }})
                    @if(!$loop->last) , @endif
                @endforeach
                @if($upcomingList->count() > 3)
                    dan {{ $upcomingList->count() - 3 }} libur lainnya
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Holidays Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th width="50">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>No</th>
                                <th>Nama Libur</th>
                                <th>Tanggal</th>
                                <th>Hari</th>
                                <th>Tipe</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($holidays as $index => $holiday)
                            <tr class="{{ $holiday->is_today ? 'table-primary' : '' }}">
                                <td>
                                    <input type="checkbox" class="holiday-checkbox" value="{{ $holiday->id }}">
                                </td>
                                <td>{{ $holidays->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $holiday->name }}</strong>
                                    @if($holiday->description)
                                        <br>
                                        <small class="text-muted">{{ Str::limit($holiday->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <i class="fas fa-calendar-day"></i>
                                    {{ $holiday->formatted_date }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $holiday->day_name_indonesian }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $holiday->type_badge }}">
                                        {{ $holiday->type_text }}
                                    </span>
                                </td>
                                <td>
                                    @if($holiday->is_today)
                                        <span class="badge bg-danger">Hari Ini</span>
                                    @elseif($holiday->is_upcoming)
                                        <span class="badge bg-warning">
                                            {{ $holiday->days_left }} hari lagi
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Sudah Lewat</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.holidays.show', $holiday) }}"
                                           class="btn btn-sm btn-info text-white"
                                           title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.holidays.edit', $holiday) }}"
                                           class="btn btn-sm btn-warning text-white"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-danger"
                                                onclick="deleteHoliday({{ $holiday->id }}, '{{ $holiday->name }}')"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-2x text-muted mb-2 d-block"></i>
                                    Tidak ada data hari libur untuk tahun {{ $year }}
                                    <div class="mt-2">
                                        <a href="{{ route('admin.holidays.create') }}" class="btn btn-sm btn-primary-custom">
                                            <i class="fas fa-plus"></i> Tambah Libur
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $holidays->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545, #c82333); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus hari libur <strong id="deleteHolidayName"></strong>?</p>
                <p class="text-danger mb-0">Tindakan ini tidak dapat dibatalkan!</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #28a745, #218838); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-download"></i> Import Hari Libur Nasional
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.holidays.import') }}">
                @csrf
                <div class="modal-body">
                    <p>Import hari libur nasional untuk tahun tertentu.</p>
                    <div class="mb-3">
                        <label class="form-label">Pilih Tahun</label>
                        <select name="year" class="form-select" required>
                            @for($i = date('Y')-1; $i <= date('Y')+2; $i++)
                                <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>
                                    Tahun {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Data yang diimport adalah hari libur nasional tetap dan hari besar keagamaan (data sederhana).
                        Anda dapat mengedit setelah import.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Delete Form -->
<form id="bulkDeleteForm" method="POST" action="{{ route('admin.holidays.bulk-delete') }}">
    @csrf
    @method('DELETE')
    <input type="hidden" name="ids" id="bulkDeleteIds">
</form>
@endsection

@push('scripts')
<script>
function deleteHoliday(id, name) {
    document.getElementById('deleteHolidayName').textContent = name;
    const form = document.getElementById('deleteForm');
    form.action = "{{ url('admin/holidays') }}/" + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Select all checkboxes
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.holiday-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    toggleBulkDeleteButton();
});

document.querySelectorAll('.holiday-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', toggleBulkDeleteButton);
});

function toggleBulkDeleteButton() {
    const checked = document.querySelectorAll('.holiday-checkbox:checked').length;
    const bulkBtn = document.getElementById('bulkDeleteBtn');
    if (bulkBtn) {
        bulkBtn.style.display = checked > 0 ? 'inline-block' : 'none';
    }
}

document.getElementById('bulkDeleteBtn')?.addEventListener('click', function() {
    const checked = document.querySelectorAll('.holiday-checkbox:checked');
    const ids = Array.from(checked).map(cb => cb.value);

    if (ids.length === 0) return;

    if (confirm(`Apakah Anda yakin ingin menghapus ${ids.length} hari libur?`)) {
        document.getElementById('bulkDeleteIds').value = JSON.stringify(ids);
        document.getElementById('bulkDeleteForm').submit();
    }
});
</script>
@endpush
