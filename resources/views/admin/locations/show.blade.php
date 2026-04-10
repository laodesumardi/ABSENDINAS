@extends('layouts.app')

@section('title', 'Detail Lokasi Kerja')
@section('page-title', 'Detail Lokasi Kerja')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-info-circle text-primary"></i> Informasi Lokasi
                </h5>
                <table class="table table-borderless">
                    <tr>
                        <th width="150">Nama Lokasi</th>
                        <td>: <strong>{{ $location->name }}</strong></td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td>: {{ $location->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Latitude</th>
                        <td>: {{ number_format($location->latitude, 8) }}</td>
                    </tr>
                    <tr>
                        <th>Longitude</th>
                        <td>: {{ number_format($location->longitude, 8) }}</td>
                    </tr>
                    <tr>
                        <th>Radius</th>
                        <td>: {{ $location->radius }} meter ({{ $location->radius_in_km }} km)</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>:
                            @if($location->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Dibuat</th>
                        <td>: {{ $location->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Terakhir Update</th>
                        <td>: {{ $location->updated_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                </table>

                <div class="mt-3">
                    <a href="{{ route('admin.locations.edit', $location) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-map"></i> Peta Lokasi
                </h5>
                <div id="map" style="height: 400px; border-radius: 10px;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const lat = {{ $location->latitude }};
    const lng = {{ $location->longitude }};
    const radius = {{ $location->radius }};

    const map = L.map('map').setView([lat, lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    L.marker([lat, lng]).addTo(map);

    L.circle([lat, lng], {
        color: '#3a0ca3',
        fillColor: '#3a0ca3',
        fillOpacity: 0.1,
        radius: radius
    }).addTo(map);
</script>
@endpush
