@extends('layouts.app')

@section('title', 'Edit Lokasi Kerja')
@section('page-title', 'Edit Lokasi Kerja')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-md-8">
            <div class="stat-card">
                <form method="POST" action="{{ route('admin.locations.update', $location) }}" id="locationForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $location->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                  rows="3">{{ old('address', $location->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="latitude" id="latitude"
                                   class="form-control @error('latitude') is-invalid @enderror"
                                   value="{{ old('latitude', $location->latitude) }}" required>
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="longitude" id="longitude"
                                   class="form-control @error('longitude') is-invalid @enderror"
                                   value="{{ old('longitude', $location->longitude) }}" required>
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Radius (meter) <span class="text-danger">*</span></label>
                        <input type="number" name="radius" id="radius"
                               class="form-control @error('radius') is-invalid @enderror"
                               value="{{ old('radius', $location->radius) }}" min="10" max="1000" required>
                        @error('radius')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Minimal 10 meter, maksimal 1000 meter</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" class="form-check-input"
                                   value="1" {{ old('is_active', $location->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label">Aktifkan lokasi ini</label>
                            <small class="text-muted d-block">Hanya satu lokasi yang dapat aktif dalam satu waktu</small>
                        </div>
                    </div>

                    <div class="text-end">
                        <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <h5 class="mb-3">
                    <i class="fas fa-map"></i> Peta Lokasi
                </h5>
                <div id="map" style="height: 400px; border-radius: 10px;"></div>
                <div class="mt-3">
                    <button type="button" class="btn btn-primary-custom w-100" onclick="getCurrentLocation()">
                        <i class="fas fa-crosshairs"></i> Gunakan Lokasi Saya
                    </button>
                </div>
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
    let map;
    let marker;
    let radiusCircle;

    function initMap(lat, lng) {
        if (map) {
            map.remove();
        }

        map = L.map('map').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        marker = L.marker([lat, lng], { draggable: true }).addTo(map);

        marker.on('dragend', function(e) {
            const pos = marker.getLatLng();
            document.getElementById('latitude').value = pos.lat.toFixed(8);
            document.getElementById('longitude').value = pos.lng.toFixed(8);
            drawRadius(pos.lat, pos.lng);
        });

        drawRadius(lat, lng);
    }

    function drawRadius(lat, lng) {
        const radius = document.getElementById('radius').value;
        if (radiusCircle) {
            map.removeLayer(radiusCircle);
        }
        radiusCircle = L.circle([lat, lng], {
            color: '#3a0ca3',
            fillColor: '#3a0ca3',
            fillOpacity: 0.1,
            radius: parseInt(radius)
        }).addTo(map);
    }

    function getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                document.getElementById('latitude').value = lat.toFixed(8);
                document.getElementById('longitude').value = lng.toFixed(8);
                initMap(lat, lng);
            }, function(error) {
                alert('Gagal mendapatkan lokasi: ' + error.message);
            });
        } else {
            alert('Browser tidak mendukung geolocation');
        }
    }

    document.getElementById('radius').addEventListener('change', function() {
        const lat = document.getElementById('latitude').value;
        const lng = document.getElementById('longitude').value;
        if (lat && lng) {
            drawRadius(parseFloat(lat), parseFloat(lng));
        }
    });

    const defaultLat = parseFloat(document.getElementById('latitude').value);
    const defaultLng = parseFloat(document.getElementById('longitude').value);
    initMap(defaultLat, defaultLng);
</script>
@endpush
