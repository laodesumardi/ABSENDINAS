@extends('layouts.app')

@section('title', 'Data Pegawai')
@section('page-title', 'Data Pegawai')

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Pegawai</h6>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
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
                        <h6 class="text-muted mb-2">Pegawai Aktif</h6>
                        <h3 class="mb-0 text-success">{{ $stats['active'] }}</h3>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Pegawai Nonaktif</h6>
                        <h3 class="mb-0 text-danger">{{ $stats['inactive'] }}</h3>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-user-slash"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Departemen</h6>
                        <h3 class="mb-0 text-info">{{ $stats['total_departments'] }}</h3>
                    </div>
                    <div class="stat-icon info">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <form method="GET" action="{{ route('operator.employees.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Cari Pegawai</label>
                        <input type="text" name="search" class="form-control" placeholder="Nama / ID / Email / Telepon"
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Departemen</label>
                        <select name="department" class="form-select">
                            <option value="">Semua Departemen</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                    {{ $dept }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('operator.employees.index') }}" class="btn btn-secondary ms-2 w-100">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Export Button -->
   <!-- Export Button -->
<div class="row mt-3">
    <div class="col-12">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-download text-primary"></i> Export Data
                </h5>
                <div>
                    <a href="{{ route('operator.reports.export', array_merge(request()->query(), ['type' => 'employee'])) }}" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export Data Pegawai
                    </a>
                    <a href="{{ route('operator.reports.export', array_merge(request()->query(), ['type' => 'leave'])) }}" class="btn btn-info">
                        <i class="fas fa-calendar-alt"></i> Export Data Izin
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Employees Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Pegawai</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Posisi</th>
                                <th>Departemen</th>
                                <th>Telepon</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $index => $employee)
                            <tr>
                                <td>{{ $employees->firstItem() + $index }}</td>
                                <td>{{ $employee->employee_id ?? '-' }}</td>
                                <td>
                                    <strong>{{ $employee->name }}</strong>
                                    <br>
                                    <small class="text-muted">Bergabung: {{ $employee->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td>{{ $employee->email }}</td>
                                <td>{{ $employee->position ?? '-' }}</td>
                                <td>{{ $employee->department ?? '-' }}</td>
                                <td>{{ $employee->phone ?? '-' }}</td>
                                <td>
                                    @if($employee->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('operator.employees.show', $employee) }}"
                                           class="btn btn-sm btn-info text-white"
                                           title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('operator.employees.attendance', $employee) }}"
                                           class="btn btn-sm btn-primary"
                                           title="Riwayat Absensi">
                                            <i class="fas fa-calendar-check"></i>
                                        </a>
                                        <a href="{{ route('operator.employees.leaves', $employee) }}"
                                           class="btn btn-sm btn-warning text-white"
                                           title="Riwayat Izin">
                                            <i class="fas fa-file-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-users-slash fa-2x text-muted mb-2 d-block"></i>
                                    Tidak ada data pegawai
                                 </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $employees->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
