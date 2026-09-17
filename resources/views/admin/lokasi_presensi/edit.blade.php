@extends('layouts.admin')

@section('title', 'Edit Titik Lokasi Presensi — Admin')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">MANAJEMEN GEOFENCE &amp; MULTI-LOKASI</div>
                <h1 class="laporanHeaderTitle">Edit Titik: {{ $lokasiPresensi->nama_lokasi }}</h1>
                <div class="laporanHeaderSub">Perbarui koordinat titik peta atau sesuaikan toleransi radius geofence</div>
            </div>
            <div class="laporanHeaderActions header-actions-group">
                <a href="{{ route('admin.lokasi-presensi.index') }}" class="btnOutline">
                    <ion-icon name="arrow-back-outline"></ion-icon> Kembali
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mb-4 p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
            <strong class="d-block mb-1">Terdapat kesalahan input:</strong>
            <ul class="mb-0 pl-4 list-disc text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="content-box">
        <form method="POST" action="{{ route('admin.lokasi-presensi.update', $lokasiPresensi) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- ── Kolom Kiri: Form Data Lokasi ── --}}
                <div class="lg:col-span-5 flex flex-col gap-4">
                    <div class="form-group">
                        <label class="form-label font-bold text-dark text-sm mb-1 d-block">
                            Nama Titik Lokasi <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama_lokasi" value="{{ old('nama_lokasi', $lokasiPresensi->nama_lokasi) }}" class="form-control w-full" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label font-bold text-dark text-sm mb-1 d-block">
                            Tipe / Kategori Lokasi <span class="text-danger">*</span>
                        </label>
                        <select name="tipe" class="form-control w-full" required>
                            <option value="pusat" {{ old('tipe', $lokasiPresensi->tipe) == 'pusat' ? 'selected' : '' }}>🏢 Gedung Pusat</option>
                            <option value="cabang" {{ old('tipe', $lokasiPresensi->tipe) == 'cabang' ? 'selected' : '' }}>🏫 Cabang / Rombel Belajar</option>
                            <option value="mitra" {{ old('tipe', $lokasiPresensi->tipe) == 'mitra' ? 'selected' : '' }}>🤝 Instansi Mitra PKL / Magang</option>
                            <option value="kegiatan" {{ old('tipe', $lokasiPresensi->tipe) == 'kegiatan' ? 'selected' : '' }}>🎪 Sentra Belajar / Acara Khusus</option>
                            <option value="lainnya" {{ old('tipe', $lokasiPresensi->tipe) == 'lainnya' ? 'selected' : '' }}>📍 Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label font-bold text-dark text-sm mb-1 d-block">Alamat Lengkap (Opsional)</label>
                        <textarea name="alamat" rows="2" class="form-control w-full">{{ old('alamat', $lokasiPresensi->alamat) }}</textarea>
                    </div>

                    {{-- Koordinat GPS --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="form-group">
                            <label class="form-label font-bold text-dark text-sm mb-1 d-block">Latitude <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="latitude" id="inputLat" value="{{ old('latitude', $lokasiPresensi->latitude) }}" class="form-control w-full font-mono text-sm" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label font-bold text-dark text-sm mb-1 d-block">Longitude <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="longitude" id="inputLng" value="{{ old('longitude', $lokasiPresensi->longitude) }}" class="form-control w-full font-mono text-sm" required>
                        </div>
                    </div>

                    {{-- Radius Geofence --}}
                    <div class="form-group">
                        <div class="d-flex justify-between items-center mb-1">
                            <label class="form-label font-bold text-dark text-sm mb-0">Radius Geofence Presensi <span class="text-danger">*</span></label>
                            <span class="badgeDate text-xs px-2 py-0.5 font-bold" id="radiusBadgeVal">{{ old('radius_meter', $lokasiPresensi->radius_meter) }} Meter</span>
                        </div>
                        <input type="range" min="10" max="1000" step="5" id="sliderRadius" value="{{ old('radius_meter', $lokasiPresensi->radius_meter) }}" class="w-full h-2 bg-slate-200 rounded-lg cursor-pointer">
                        <div class="d-flex items-center gap-2 mt-2">
                            <input type="number" min="10" max="5000" name="radius_meter" id="inputRadius" value="{{ old('radius_meter', $lokasiPresensi->radius_meter) }}" class="form-control w-28 text-center font-bold" required>
                            <span class="text-xs text-muted">Meter (Maksimal jarak tutor dari titik pusat ini)</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label font-bold text-dark text-sm mb-1 d-block">Keterangan / Catatan (Opsional)</label>
                        <input type="text" name="keterangan" value="{{ old('keterangan', $lokasiPresensi->keterangan) }}" class="form-control w-full">
                    </div>

                    <div class="form-check mt-2">
                        <label class="d-flex items-center gap-2 cursor-pointer font-semibold text-dark text-sm">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $lokasiPresensi->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                            Titik lokasi aktif (Dapat dipilih untuk presensi)
                        </label>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 d-flex gap-2">
                        <button type="submit" class="profileBtnPrimary flex-1 justify-center py-2.5">
                            <ion-icon name="save-outline"></ion-icon> Perbarui Titik Lokasi
                        </button>
                        <a href="{{ route('admin.lokasi-presensi.index') }}" class="btnOutline justify-center py-2.5">
                            Batal
                        </a>
                    </div>
                </div>

                {{-- ── Kolom Kanan: Interactive Leaflet Map Picker ── --}}
                <div class="lg:col-span-7 flex flex-col gap-2">
                    <div class="d-flex justify-between items-center">
                        <span class="font-bold text-dark text-sm d-flex items-center gap-1.5">
                            <ion-icon name="navigate-circle-outline" class="text-primary text-base"></ion-icon> Peta Penentu Titik &amp; Radius
                        </span>
                        <button type="button" id="btnDetectAdminLocation" class="btnOutline text-xs py-1 px-2.5 d-inline-flex items-center gap-1">
                            <ion-icon name="locate-outline"></ion-icon> Lokasi Saya Saat Ini
                        </button>
                    </div>

                    <div class="pos-relative">
                        <div id="pickerMap" style="height: 440px; width: 100%; border-radius: 14px; border: 1px solid var(--border); z-index: 1;"></div>
                        <div class="p-2.5 bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm rounded-lg border border-slate-200 dark:border-slate-800 text-xs text-muted shadow-sm mt-2">
                            💡 <b>Petunjuk:</b> Klik di mana saja pada peta atau geser (drag) pin merah untuk menentukan titik lokasi. Lingkaran biru memvisualisasikan toleransi radius geofence.
                        </div>
                    </div>
                </div>
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

        let currentLat = parseFloat(inputLat.value) || {{ $lokasiPresensi->latitude }};
        let currentLng = parseFloat(inputLng.value) || {{ $lokasiPresensi->longitude }};
        let currentRadius = parseInt(inputRadius.value) || {{ $lokasiPresensi->radius_meter }};

        const map = L.map('pickerMap').setView([currentLat, currentLng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        // Marker Titik yang dapat di-drag
        const marker = L.marker([currentLat, currentLng], {
            draggable: true
        }).addTo(map);
        marker.bindPopup('<b>{{ addslashes($lokasiPresensi->nama_lokasi) }}</b><br>Geser untuk mengubah koordinat').openPopup();

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
    });
</script>
@endsection
