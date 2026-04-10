@extends('layouts.app')

@section('title', 'Log Aktivitas')
@section('page-title', 'Log Aktivitas Sistem')

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Aktivitas</h6>
                        <h3 class="mb-0">{{ number_format($totalLogs) }}</h3>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-history"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Aktivitas Hari Ini</h6>
                        <h3 class="mb-0">{{ number_format($todayLogs) }}</h3>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">User Aktif</h6>
                        <h3 class="mb-0">{{ number_format($uniqueUsers) }}</h3>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mt-4">
        <div class="col-md-7">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-chart-line text-primary"></i> Aktivitas per Jam (Hari Ini)
                </h5>
                <canvas id="hourlyChart" height="250"></canvas>
            </div>
        </div>

        <div class="col-md-5">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-chart-pie text-primary"></i> Top 10 Aktivitas (30 Hari)
                </h5>
                <canvas id="actionChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-filter text-primary"></i> Filter Log Aktivitas
                </h5>
                <form method="GET" action="{{ route('admin.activity.index') }}" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">User</label>
                        <select name="user_id" class="form-select">
                            <option value="">Semua User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->role }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Aksi</label>
                        <select name="action" class="form-select">
                            <option value="">Semua Aksi</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                    {{ str_replace('_', ' ', ucfirst($action)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Pencarian</label>
                        <input type="text" name="search" class="form-control" placeholder="Cari..." value="{{ request('search') }}">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.activity.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                        <a href="{{ route('admin.activity.export', request()->query()) }}" class="btn btn-success">
                            <i class="fas fa-download"></i> Export CSV
                        </a>
                        <div class="float-end">
                            <button type="button" class="btn btn-warning" onclick="clearOld()">
                                <i class="fas fa-trash-alt"></i> Hapus Log Lama (>30 hari)
                            </button>
                            <button type="button" class="btn btn-danger" onclick="clearAll()">
                                <i class="fas fa-trash"></i> Hapus Semua
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-clock text-primary"></i> Aktivitas Terbaru
                </h5>
                <div class="timeline">
                    @forelse($recentActivities as $activity)
                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <i class="{{ $activity->action_icon }}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $activity->user->name ?? 'System' }}</strong>
                                <small class="text-muted">{{ $activity->human_readable_time }}</small>
                            </div>
                            <p class="mb-1">{{ $activity->description }}</p>
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt"></i> {{ $activity->ip_address ?? '-' }} |
                                <i class="fas fa-desktop"></i> {{ $activity->device_info }}
                            </small>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-2x text-muted mb-2 d-block"></i>
                        Belum ada aktivitas terbaru
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Waktu</th>
                                <th>User</th>
                                <th>Aksi</th>
                                <th>Deskripsi</th>
                                <th>IP Address</th>
                                <th>Device Info</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $index => $log)
                            <tr>
                                <td>{{ $logs->firstItem() + $index }}</td>
                                <td>
                                    <span title="{{ $log->formatted_date }}">
                                        {{ $log->human_readable_time }}
                                    </span>
                                    <br>
                                    <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $log->user->name ?? 'System' }}</strong>
                                    @if($log->user)
                                        <br>
                                        <small class="text-muted">{{ ucfirst($log->user->role) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <i class="{{ $log->action_icon }}"></i>
                                    {{ $log->action_label }}
                                </td>
                                <td>
                                    {{ Str::limit($log->description, 60) }}
                                    @if(strlen($log->description) > 60)
                                        <button class="btn btn-link btn-sm p-0" onclick="showDescription('{{ addslashes($log->description) }}')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endif
                                </td>
                                <td>
                                    <code>{{ $log->ip_address ?? '-' }}</code>
                                </td>
                                <td>
                                    <small>
                                        <i class="{{ $log->getBrowserIcon() }}"></i>
                                        {{ $log->getBrowser() }}<br>
                                        <i class="fas fa-desktop"></i>
                                        {{ $log->getOperatingSystem() }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.activity.show', $log) }}" class="btn btn-sm btn-info text-white" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteLog({{ $log->id }})" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-history fa-2x text-muted mb-2 d-block"></i>
                                    Tidak ada log aktivitas
                                    @if(request()->anyFilled(['start_date', 'end_date', 'user_id', 'action', 'search']))
                                        <div class="mt-2">
                                            <a href="{{ route('admin.activity.index') }}" class="btn btn-sm btn-primary-custom">
                                                <i class="fas fa-undo"></i> Reset Filter
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Custom Pagination -->
                <div class="mt-4">
                    @if ($logs->hasPages())
                        <nav aria-label="Page navigation">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                                {{-- Info --}}
                                <div class="text-muted small">
                                    <i class="fas fa-database me-1"></i>
                                    Menampilkan <strong>{{ $logs->firstItem() ?? 0 }}</strong>
                                    sampai <strong>{{ $logs->lastItem() ?? 0 }}</strong>
                                    dari <strong>{{ $logs->total() }}</strong> data
                                </div>

                                {{-- Pagination Links --}}
                                <ul class="pagination mb-0">
                                    {{-- Previous Page Link --}}
                                    @if ($logs->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link">
                                                <i class="fas fa-chevron-left me-1"></i> Sebelumnya
                                            </span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $logs->previousPageUrl() }}" rel="prev">
                                                <i class="fas fa-chevron-left me-1"></i> Sebelumnya
                                            </a>
                                        </li>
                                    @endif

                                    {{-- First Page --}}
                                    @if ($logs->currentPage() > 3)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $logs->url(1) }}">1</a>
                                        </li>
                                        @if ($logs->currentPage() > 4)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @foreach (range(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page)
                                        @if ($page == $logs->currentPage())
                                            <li class="page-item active" aria-current="page">
                                                <span class="page-link">{{ $page }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $logs->url($page) }}">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endforeach

                                    {{-- Last Page --}}
                                    @if ($logs->currentPage() < $logs->lastPage() - 2)
                                        @if ($logs->currentPage() < $logs->lastPage() - 3)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $logs->url($logs->lastPage()) }}">{{ $logs->lastPage() }}</a>
                                        </li>
                                    @endif

                                    {{-- Next Page Link --}}
                                    @if ($logs->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $logs->nextPageUrl() }}" rel="next">
                                                Selanjutnya <i class="fas fa-chevron-right ms-1"></i>
                                            </a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link">
                                                Selanjutnya <i class="fas fa-chevron-right ms-1"></i>
                                            </span>
                                        </li>
                                    @endif
                                </ul>

                                {{-- Go to Page --}}
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted small">
                                        <i class="fas fa-arrow-right me-1"></i>Lompat ke:
                                    </span>
                                    <select class="form-select form-select-sm w-auto page-selector" style="cursor: pointer; width: 110px;">
                                        @for ($i = 1; $i <= $logs->lastPage(); $i++)
                                            <option value="{{ $logs->url($i) }}" {{ $i == $logs->currentPage() ? 'selected' : '' }}>
                                                Halaman {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </nav>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-info-circle me-1"></i> Menampilkan semua data ({{ $logs->total() }} total)
                        </div>
                    @endif
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
                <p>Apakah Anda yakin ingin menghapus log aktivitas ini?</p>
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

<!-- Description Modal -->
<div class="modal fade" id="descriptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #3a0ca3, #2c0a7a); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detail Deskripsi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="descriptionText"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 40px;
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
}

.timeline-icon {
    position: absolute;
    left: -40px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: white;
    border: 2px solid #3a0ca3;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
}

.timeline-content {
    background: #f8f9fc;
    padding: 12px;
    border-radius: 8px;
    border-left: 3px solid #3a0ca3;
}

.timeline-item:not(:last-child):before {
    content: '';
    position: absolute;
    left: -26px;
    top: 30px;
    bottom: -25px;
    width: 2px;
    background: #e0e0e0;
}

/* Pagination Styles */
.pagination {
    gap: 6px;
    margin-bottom: 0;
    flex-wrap: wrap;
}

.page-item .page-link {
    color: #3a0ca3;
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.page-item .page-link:hover {
    background: linear-gradient(135deg, #3a0ca3 0%, #2c0a7a 100%);
    color: #ffffff;
    border-color: #3a0ca3;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(58, 12, 163, 0.3);
}

.page-item.active .page-link {
    background: linear-gradient(135deg, #3a0ca3 0%, #2c0a7a 100%);
    border-color: #3a0ca3;
    color: #ffffff;
}

.page-item.disabled .page-link {
    color: #94a3b8;
    background-color: #f1f5f9;
    border-color: #e2e8f0;
    cursor: not-allowed;
}

.page-selector {
    cursor: pointer;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    padding: 6px 30px 6px 12px;
    font-size: 13px;
}

.page-selector:hover {
    border-color: #3a0ca3;
}

.page-selector:focus {
    border-color: #3a0ca3;
    box-shadow: 0 0 0 0.2rem rgba(58, 12, 163, 0.25);
    outline: none;
}

/* Responsive */
@media (max-width: 768px) {
    .page-item .page-link {
        padding: 6px 10px;
        font-size: 12px;
    }

    .page-item:not(.active):not(:first-child):not(:last-child):not(:nth-child(2)):not(:nth-last-child(2)) {
        display: none;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Hourly Chart
    const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
    const hourlyData = @json($activityByHour);
    new Chart(hourlyCtx, {
        type: 'bar',
        data: {
            labels: hourlyData.map(item => item.hour + ':00'),
            datasets: [{
                label: 'Jumlah Aktivitas',
                data: hourlyData.map(item => item.total),
                backgroundColor: '#3a0ca3',
                borderColor: '#3a0ca3',
                borderWidth: 1,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        display: true,
                        color: '#e2e8f0'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Action Chart
    const actionCtx = document.getElementById('actionChart').getContext('2d');
    const actionData = @json($activityByAction);
    new Chart(actionCtx, {
        type: 'pie',
        data: {
            labels: actionData.map(item => {
                return item.action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            }),
            datasets: [{
                data: actionData.map(item => item.total),
                backgroundColor: [
                    '#3a0ca3', '#4361ee', '#06d6a0', '#ffd166', '#ef476f',
                    '#118ab2', '#073b4c', '#f4a261', '#e76f51', '#2a9d8f'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        font: { size: 10 },
                        boxWidth: 12
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = actionData.reduce((sum, item) => sum + item.total, 0);
                            const percentage = ((context.raw / total) * 100).toFixed(1);
                            return `${context.label}: ${context.raw} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Delete function
    function deleteLog(id) {
        const form = document.getElementById('deleteForm');
        form.action = "{{ url('admin/activity') }}/" + id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    // Clear old logs
    function clearOld() {
        if (confirm('Apakah Anda yakin ingin menghapus semua log aktivitas yang berusia lebih dari 30 hari?')) {
            window.location.href = "{{ route('admin.activity.clear-old') }}";
        }
    }

    // Clear all logs
    function clearAll() {
        if (confirm('PERINGATAN! Anda akan menghapus SEMUA log aktivitas. Tindakan ini tidak dapat dibatalkan. Lanjutkan?')) {
            window.location.href = "{{ route('admin.activity.clear-all') }}";
        }
    }

    // Show description modal
    function showDescription(description) {
        document.getElementById('descriptionText').textContent = description;
        new bootstrap.Modal(document.getElementById('descriptionModal')).show();
    }

    // Page selector auto redirect
    document.querySelectorAll('.page-selector').forEach(select => {
        select.addEventListener('change', function() {
            window.location.href = this.value;
        });
    });
</script>
@endpush
