@extends('layouts.app')

@section('title', 'Manajemen Lokasi Kerja')
@section('page-title', 'Manajemen Lokasi Kerja')

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Active Location Card -->
    @if($activeLocation)
    <div class="row">
        <div class="col-12">
            <div class="stat-card" style="background: linear-gradient(135deg, #3a0ca3, #2c0a7a); color: white;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-2">
                            <i class="fas fa-map-marker-alt"></i> Lokasi Aktif Saat Ini
                        </h5>
                        <h3 class="mb-2">{{ $activeLocation->name }}</h3>
                        <p class="mb-0">
                            <i class="fas fa-map-pin"></i> Radius: {{ $activeLocation->radius }} meter
                        </p>
                    </div>
                    <div class="text-center">
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div class="mt-2">Aktif</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-12">
            <div class="stat-card" style="background: linear-gradient(135deg, #ef476f, #d64161); color: white;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-2">
                            <i class="fas fa-exclamation-triangle"></i> Peringatan
                        </h5>
                        <p class="mb-0">Belum ada lokasi kerja yang aktif. Silakan tambah dan aktifkan lokasi kerja terlebih dahulu.</p>
                    </div>
                    <div>
                        <i class="fas fa-map-marker-alt fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Add Location Button -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-building"></i> Daftar Lokasi Kerja
                    </h5>
                    <a href="{{ route('admin.locations.create') }}" class="btn btn-primary-custom">
                        <i class="fas fa-plus"></i> Tambah Lokasi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Locations Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Lokasi</th>
                                <th>Alamat</th>
                                <th>Koordinat</th>
                                <th>Radius</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($locations as $index => $location)
                            <tr>
                                <td>{{ $locations->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $location->name }}</strong>
                                    @if($location->is_active)
                                        <span class="badge bg-success ms-2">Aktif</span>
                                    @endif
                                </td>
                                <td>{{ Str::limit($location->address, 50) ?? '-' }}</td>
                                <td>
                                    <small>
                                        <i class="fas fa-latitude"></i> {{ number_format($location->latitude, 6) }}<br>
                                        <i class="fas fa-longitude-alt"></i> {{ number_format($location->longitude, 6) }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $location->radius }} meter
                                    </span>
                                    <br>
                                    <small>({{ $location->radius_in_km }} km)</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $location->status_badge }}">
                                        {{ $location->status_text }}
                                    </span>
                                </td>
                                <td>{{ $location->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.locations.show', $location) }}"
                                           class="btn btn-sm btn-info text-white"
                                           title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.locations.edit', $location) }}"
                                           class="btn btn-sm btn-warning text-white"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if(!$location->is_active)
                                            <button type="button"
                                                    class="btn btn-sm btn-success"
                                                    onclick="toggleStatus({{ $location->id }})"
                                                    title="Aktifkan">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        @endif
                                        <button type="button"
                                                class="btn btn-sm btn-danger"
                                                onclick="deleteLocation({{ $location->id }}, '{{ $location->name }}')"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-building-slash fa-2x text-muted mb-2 d-block"></i>
                                    Belum ada data lokasi kerja
                                    <div class="mt-2">
                                        <a href="{{ route('admin.locations.create') }}" class="btn btn-sm btn-primary-custom">
                                            <i class="fas fa-plus"></i> Tambah Lokasi Pertama
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $locations->links() }}
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
                <p>Apakah Anda yakin ingin menghapus lokasi kerja <strong id="deleteLocationName"></strong>?</p>
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

<!-- Toggle Status Form -->
<form id="toggleForm" method="POST" style="display: none;">
    @csrf
    @method('PUT')
</form>
@endsection

@push('scripts')
<script>
function deleteLocation(id, name) {
    document.getElementById('deleteLocationName').textContent = name;
    const form = document.getElementById('deleteForm');
    form.action = "{{ url('admin/locations') }}/" + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function toggleStatus(id) {
    if (confirm('Apakah Anda yakin ingin mengaktifkan lokasi ini? Lokasi lain akan otomatis dinonaktifkan.')) {
        const form = document.getElementById('toggleForm');
        form.action = "{{ url('admin/locations') }}/" + id + "/toggle-status";
        form.submit();
    }
}
</script>
@endpush
