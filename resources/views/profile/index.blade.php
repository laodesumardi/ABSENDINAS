@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <!-- Profile Card -->
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="profile-photo-container mb-3">
                    @if($user->profile_photo)
                        <img src="{{ Storage::url($user->profile_photo) }}"
                             alt="{{ $user->name }}"
                             class="profile-photo">
                    @else
                        <div class="default-avatar">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                </div>

                <h4>{{ $user->name }}</h4>
                <p class="text-muted">
                    <i class="fas fa-envelope"></i> {{ $user->email }}
                </p>

                @php
                    $roleBadge = [
                        'admin' => 'danger',
                        'operator' => 'warning',
                        'employee' => 'primary'
                    ][$user->role] ?? 'secondary';
                @endphp
                <span class="badge bg-{{ $roleBadge }} fs-6 mb-3">
                    {{ ucfirst($user->role) }}
                </span>

                <hr>

                <div class="text-start">
                    <p><strong><i class="fas fa-id-card"></i> ID Pegawai:</strong> {{ $user->employee_id ?? '-' }}</p>
                    <p><strong><i class="fas fa-briefcase"></i> Posisi:</strong> {{ $user->position ?? '-' }}</p>
                    <p><strong><i class="fas fa-building"></i> Departemen:</strong> {{ $user->department ?? '-' }}</p>
                    <p><strong><i class="fas fa-phone"></i> Telepon:</strong> {{ $user->phone ?? '-' }}</p>
                    <p><strong><i class="fas fa-map-marker-alt"></i> Alamat:</strong> {{ $user->address ?? '-' }}</p>
                    <p><strong><i class="fas fa-calendar-alt"></i> Bergabung:</strong> {{ $user->created_at->format('d F Y') }}</p>
                    <p><strong><i class="fas fa-clock"></i> Terakhir Login:</strong>
                        {{ $user->last_login_at ? $user->last_login_at->format('d F Y H:i:s') : '-' }}
                    </p>
                </div>

                <div class="mt-3">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary-custom">
                        <i class="fas fa-edit"></i> Edit Profil
                    </a>
                </div>
            </div>
        </div>

        <!-- Activity Log Card -->
        <div class="col-md-8">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-history text-primary"></i> Aktivitas Terbaru
                </h5>
                <div class="timeline">
                    @forelse($user->activityLogs()->latest()->limit(10)->get() as $activity)
                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <i class="{{ $activity->action_icon }}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $activity->action_label }}</strong>
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
                        Belum ada aktivitas
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.profile-photo-container {
    width: 150px;
    height: 150px;
    margin: 0 auto;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #3a0ca3;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.profile-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.default-avatar {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #3a0ca3, #2c0a7a);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 64px;
    font-weight: bold;
}

.timeline {
    position: relative;
    padding-left: 40px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
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
    bottom: -20px;
    width: 2px;
    background: #e0e0e0;
}
</style>
@endpush
