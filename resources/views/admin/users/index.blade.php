@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total User</h6>
                        <h3 class="mb-0">{{ $totalUsers }}</h3>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Administrator</h6>
                        <h3 class="mb-0">{{ $totalAdmins }}</h3>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Operator</h6>
                        <h3 class="mb-0">{{ $totalOperators }}</h3>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-user-cog"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Pegawai Aktif</h6>
                        <h3 class="mb-0">{{ $activeUsers }}</h3>
                    </div>
                    <div class="stat-icon info">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter and Search -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2">
                            <div class="col-md-4">
                                <select name="role" class="form-select">
                                    <option value="">Semua Role</option>
                                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="operator" {{ request('role') == 'operator' ? 'selected' : '' }}>Operator</option>
                                    <option value="employee" {{ request('role') == 'employee' ? 'selected' : '' }}>Employee</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary-custom w-100">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary-custom">
                            <i class="fas fa-plus"></i> Tambah User
                        </a>
                        <a href="{{ route('admin.export.users', request()->query()) }}" class="btn btn-success">
    <i class="fas fa-download"></i> Export User
</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>ID Pegawai</th>
                                <th>Posisi</th>
                                <th>Status</th>
                                <th>Terdaftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                    @if($user->id == auth()->id())
                                        <span class="badge bg-info ms-1">Anda</span>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @php
                                        $roleBadge = [
                                            'admin' => 'danger',
                                            'operator' => 'warning',
                                            'employee' => 'primary'
                                        ][$user->role] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $roleBadge }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td>{{ $user->employee_id ?? '-' }}</td>
                                <td>{{ $user->position ?? '-' }}</td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Nonaktif</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.users.show', $user) }}"
                                           class="btn btn-sm btn-info text-white"
                                           title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                           class="btn btn-sm btn-warning text-white"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm {{ $user->is_active ? 'btn-secondary' : 'btn-success' }}"
                                                onclick="toggleStatus({{ $user->id }})"
                                                title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                        </button>
                                        @if($user->id != auth()->id())
                                        <button type="button"
                                                class="btn btn-sm btn-danger"
                                                onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-users-slash fa-2x text-muted mb-2 d-block"></i>
                                    Tidak ada data user
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $users->withQueryString()->links() }}
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
                <p>Apakah Anda yakin ingin menghapus user <strong id="deleteUserName"></strong>?</p>
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
function deleteUser(id, name) {
    document.getElementById('deleteUserName').textContent = name;
    const form = document.getElementById('deleteForm');
    form.action = "{{ url('admin/users') }}/" + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function toggleStatus(id) {
    if (confirm('Apakah Anda yakin ingin mengubah status user ini?')) {
        const form = document.getElementById('toggleForm');
        form.action = "{{ url('admin/users') }}/" + id + "/toggle-status";
        form.submit();
    }
}
</script>
@endpush
