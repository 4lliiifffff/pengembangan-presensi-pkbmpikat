@extends('layouts.admin')

@section('title', 'Tambah Titik Lokasi Presensi — Admin')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">MANAJEMEN GEOFENCE &amp; MULTI-LOKASI</div>
                <h1 class="laporanHeaderTitle">Tambah Titik Lokasi Presensi</h1>
                <div class="laporanHeaderSub">Tentukan koordinat GPS dan batas radius toleransi absensi menggunakan peta interaktif</div>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.lokasi-presensi.index') }}" class="btnOutline">
                    Kembali
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="error-list-container">
            <div class="error-title">Periksa input berikut:</div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-card-container form-card-wide">
        <form method="POST" action="{{ route('admin.lokasi-presensi.store') }}">
            @csrf

            <div class="form-map-split">
                {{-- ── Kolom Kiri: Form Data Lokasi ── --}}
                <div class="d-flex flex-col gap-3">
                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Nama Titik Lokasi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lokasi" value="{{ old('nama_lokasi') }}" placeholder="Contoh: Gedung Pusat PKBM Pikat, Cabang Bantul..." class="profileInput" required>
                        <span class="field-help-text">Nama lokasi ini akan muncul pada pilihan absensi tutor dan mahasiswa magang.</span>
                    </div>

                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Tipe / Kategori Lokasi <span class="text-danger">*</span></label>
                        <select name="tipe" class="filterSelect" required>
                            <option value="pusat" {{ old('tipe', 'pusat') === 'pusat' ? 'selected' : '' }}>Gedung Pusat</option>
                            <option value="cabang" {{ old('tipe', 'cabang') === 'cabang' ? 'selected' : '' }}>Cabang / Rombel Belajar</option>
                            <option value="mitra" {{ old('tipe', 'mitra') === 'mitra' ? 'selected' : '' }}>Instansi Mitra PKL / Magang</option>
                            <option value="kegiatan" {{ old('tipe', 'kegiatan') === 'kegiatan' ? 'selected' : '' }}>Sentra Belajar / Acara Khusus</option>
                            <option value="lainnya" {{ old('tipe', 'lainnya') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>

                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Alamat Lengkap (Opsional)</label>
                        <textarea name="alamat" rows="2" placeholder="Jl. Raya No..., Kelurahan, Kecamatan..." class="profileInput">{{ old('alamat') }}</textarea>
                    </div>

                    {{-- Koordinat GPS --}}
                    <div class="form-grid-responsive">
                        <div class="form-field-wrapper">
                            <label class="filterFieldLabel">Latitude <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="latitude" id="inputLat" value="{{ old('latitude', $defaultLat) }}" class="profileInput font-mono text-sm" required>
                        </div>
                        <div class="form-field-wrapper">
                            <label class="filterFieldLabel">Longitude <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="longitude" id="inputLng" value="{{ old('longitude', $defaultLng) }}" class="profileInput font-mono text-sm" required>
                        </div>
                    </div>

                    {{-- Radius Geofence --}}
                    <div class="form-field-wrapper">
                        <div class="d-flex justify-between items-center mb-1">
                            <label class="filterFieldLabel mb-0">Radius Geofence Presensi <span class="text-danger">*</span></label>
                            <span class="badgeCount font-bold" id="radiusBadgeVal">{{ old('radius_meter', $defaultRadius) }} Meter</span>
                        </div>
                        <div class="radius-slider-group">
                            <input type="range" min="10" max="1000" step="5" id="sliderRadius" value="{{ old('radius_meter', $defaultRadius) }}" class="radius-slider-input">
                            <div class="d-flex items-center gap-2">
                                <input type="number" min="10" max="5000" name="radius_meter" id="inputRadius" value="{{ old('radius_meter', $defaultRadius) }}" class="profileInput text-center font-bold" style="max-width: 120px;" required>
                                <span class="field-help-text m-0">Meter (Batas toleransi jarak presensi dari titik pusat)</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Keterangan / Catatan (Opsional)</label>
                        <input type="text" name="keterangan" value="{{ old('keterangan') }}" placeholder="Catatan tambahan lokasi..." class="profileInput">
                    </div>

                    <div class="mt-1">
                        <label class="checkbox-toggle-card">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                            <span>Aktifkan titik lokasi ini untuk presensi</span>
                        </label>
                    </div>
                </div>

                {{-- ── Kolom Kanan: Interactive Leaflet Map Picker ── --}}
                <div class="picker-map-column">
                    <div class="d-flex justify-between items-center">
                        <span class="filterFieldLabel mb-0 d-flex items-center gap-1.5 font-bold">
                            <ion-icon name="navigate-circle-outline" class="text-primary text-base"></ion-icon> Peta Penentu Titik &amp; Radius
                        </span>
                        <button type="button" id="btnDetectAdminLocation" class="btnOutline text-xs py-1 px-2.5 d-inline-flex items-center gap-1">
                            <ion-icon name="locate-outline"></ion-icon> Lokasi Saya Saat Ini
                        </button>
                    </div>

                    <div class="pos-relative">
                        <div id="pickerMap" class="pickerMapWrapper"></div>
                        <div class="info-callout-box mt-3">
                            <div class="info-callout-title">Petunjuk Penggunaan Peta:</div>
                            <div class="info-callout-desc">
                                Klik di area mana saja pada peta atau geser pin penanda biru untuk menentukan titik koordinat presensi. Lingkaran biru memvisualisasikan batas radius toleransi geofence.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-action-footer">
                <a href="{{ route('admin.lokasi-presensi.index') }}" class="btnOutline">
                    Batal
                </a>
                <button type="submit" class="profileBtnPrimary">
                    Simpan Titik Lokasi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputLat = document.getElementById('inputLat');
        const inputLng = document.getElementById('inputLng');
        const inputRadius = document.getElementById('inputRadius');
        const sliderRadius = document.getElementById('sliderRadius');
        const radiusBadgeVal = document.getElementById('radiusBadgeVal');
        const btnDetect = document.getElementById('btnDetectAdminLocation');

        let currentLat = parseFloat(inputLat.value) || {{ $defaultLat }};
        let currentLng = parseFloat(inputLng.value) || {{ $defaultLng }};
        let currentRadius = parseInt(inputRadius.value) || {{ $defaultRadius }};

        const map = L.map('pickerMap').setView([currentLat, currentLng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        // Marker Titik yang dapat di-drag
        const marker = L.marker([currentLat, currentLng], {
            draggable: true
        }).addTo(map);
        marker.bindPopup('<b>Titik Lokasi Presensi</b><br>Geser untuk mengubah koordinat').openPopup();

        // Lingkaran Radius Geofence
        const circle = L.circle([currentLat, currentLng], {
            color: '#0284c7',
            fillColor: '#38bdf8',
            fillOpacity: 0.25,
            radius: currentRadius
        }).addTo(map);

        function updatePosition(lat, lng) {
            currentLat = lat;
            currentLng = lng;
            inputLat.value = lat.toFixed(7);
            inputLng.value = lng.toFixed(7);
            marker.setLatLng([lat, lng]);
            circle.setLatLng([lat, lng]);
        }

        function updateRadius(rad) {
            currentRadius = rad;
            inputRadius.value = rad;
            sliderRadius.value = Math.min(rad, 1000);
            radiusBadgeVal.textContent = rad + ' Meter';
            circle.setRadius(rad);
        }

        // Event drag marker
        marker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            updatePosition(pos.lat, pos.lng);
        });

        // Event klik pada peta
        map.on('click', function(e) {
            updatePosition(e.latlng.lat, e.latlng.lng);
            map.panTo(e.latlng);
        });

        // Event perubahan input manual lat & lng
        inputLat.addEventListener('change', function() {
            const lat = parseFloat(this.value);
            if (!isNaN(lat)) {
                updatePosition(lat, currentLng);
                map.panTo([lat, currentLng]);
            }
        });

        inputLng.addEventListener('change', function() {
            const lng = parseFloat(this.value);
            if (!isNaN(lng)) {
                updatePosition(currentLat, lng);
                map.panTo([currentLat, lng]);
            }
        });

        // Event perubahan slider & input radius
        sliderRadius.addEventListener('input', function() {
            updateRadius(parseInt(this.value));
        });

        inputRadius.addEventListener('input', function() {
            const val = parseInt(this.value) || 10;
            updateRadius(val);
        });

        // Deteksi lokasi browser admin
        if (btnDetect && navigator.geolocation) {
            btnDetect.addEventListener('click', function() {
                btnDetect.disabled = true;
                btnDetect.innerHTML = '<ion-icon name="sync-outline" class="animate-spin"></ion-icon> Mendeteksi...';

                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        updatePosition(lat, lng);
                        map.setView([lat, lng], 17);
                        btnDetect.disabled = false;
                        btnDetect.innerHTML = '<ion-icon name="locate-outline"></ion-icon> Lokasi Saya Saat Ini';
                    },
                    function(err) {
                        alert('Gagal mendeteksi lokasi GPS: ' + err.message);
                        btnDetect.disabled = false;
                        btnDetect.innerHTML = '<ion-icon name="locate-outline"></ion-icon> Lokasi Saya Saat Ini';
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            });
        }

        setTimeout(function() {
            map.invalidateSize();
        }, 250);
    });
</script>
@endsection
