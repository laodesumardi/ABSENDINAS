@extends('layouts.app')

@section('title', 'Detail Log Aktivitas')
@section('page-title', 'Detail Log Aktivitas')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="stat-card">
                <div class="text-center mb-4">
                    <div class="timeline-icon mx-auto mb-3" style="width: 60px; height: 60px; line-height: 56px; font-size: 24px;">
                        <i class="{{ $log->action_icon }}"></i>
                    </div>
                    <h4>{{ $log->action_label }}</h4>
                    <p class="text-muted">{{ $log->human_readable_time }}</p>
                </div>

                <table class="table table-borderless">
                    <tr>
                        <th width="200">Waktu Kejadian</th>
                        <td>: {{ $log->formatted_date }}</td>
                    </tr>
                    <tr>
                        <th>User</th>
                        <td>:
                            @if($log->user)
                                <strong>{{ $log->user->name }}</strong>
                                <span class="badge bg-secondary">{{ ucfirst($log->user->role) }}</span>
                            @else
                                System
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Deskripsi</th>
                        <td>: {{ $log->description }}</td>
                    </tr>
                    <tr>
                        <th>IP Address</th>
                        <td>: <code>{{ $log->ip_address ?? '-' }}</code></td>
                    </tr>
                    <tr>
                        <th>User Agent</th>
                        <td>: <small>{{ $log->user_agent ?? '-' }}</small></td>
                    </tr>
                    <tr>
                        <th>Browser</th>
                        <td>: {{ $log->getBrowser() }}</td>
                    </tr>
                    <tr>
                        <th>Operating System</th>
                        <td>: {{ $log->getOperatingSystem() }}</td>
                    </tr>
                    <tr>
                        <th>Device</th>
                        <td>: {{ $log->device_info }}</td>
                    </tr>
                    @if($log->old_data)
                    <tr>
                        <th>Data Lama</th>
                        <td>
                            <pre class="bg-light p-2 rounded"><code>{{ json_encode($log->old_data, JSON_PRETTY_PRINT) }}</code></pre>
                        </td>
                    </tr>
                    @endif
                    @if($log->new_data)
                    <tr>
                        <th>Data Baru</th>
                        <td>
                            <pre class="bg-light p-2 rounded"><code>{{ json_encode($log->new_data, JSON_PRETTY_PRINT) }}</code></pre>
                        </td>
                    </tr>
                    @endif
                </table>

                <div class="text-end mt-3">
                    <a href="{{ route('admin.activity.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="button" class="btn btn-danger" onclick="deleteLog({{ $log->id }})">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
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
                <form id="deleteForm" method="POST" action="{{ route('admin.activity.destroy', $log) }}">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    pre {
        max-height: 300px;
        overflow: auto;
    }
</style>
@endpush

@push('scripts')
<script>
    function deleteLog(id) {
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
@endpush
@endsection
