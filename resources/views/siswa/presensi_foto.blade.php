@extends('layouts.presensi')

@section('title', 'Presensi Siswa')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

@section('content')
    @php
        $user = auth()->user();
        $siswa = $user->siswa;
        $displayName = (string) ($siswa->nama_siswa ?? ($siswa->nama_lengkap ?? ($user->nama_lengkap ?? ($user->name ?? 'Siswa PKBM'))));
        $initial = strtoupper(substr($displayName, 0, 1));

        $dashRoute = route('siswa.dashboard');
        $storeRoute = route('siswa.presensi.store');
    @endphp

    {{-- ── Standard Top Navigation Bar ── --}}
    @include('layouts.components.navigasi_atas', [
        'titleName' => $displayName,
        'subTitle' => 'Presensi Mandiri • ' . \Carbon\Carbon::parse($today)->translatedFormat('d M Y'),
        'dashRoute' => $dashRoute
    ])

    {{-- ── Warning Banner Izin ── --}}
    <div id="permWarning" class="permWarning d-none">
        <div class="flex-1 min-w-0">
            <div class="permWarnTitle" id="permWarnTitle">Izin belum diberikan</div>
            <div class="permWarnDesc" id="permWarnDesc">Kamera dan lokasi diperlukan untuk melakukan absensi mandiri.</div>
        </div>
        <button onclick="checkPermissions()" class="permWarnBtn">
            Coba Lagi
        </button>
    </div>

    {{-- ══════════════════ MAIN CONTENT ══════════════════ --}}
    <div class="pagePad" id="mainContent">

        {{-- ── SUDAH PRESENSI MASUK HARI INI ── --}}
        @if ($alreadyCheckedIn && $todayPresensi)
            @php
                $jamMasuk = substr((string) $todayPresensi->jam_masuk, 0, 5);
                $lokasiNama = $todayPresensi->lokasiPresensi?->nama_lokasi ?? 'PKBM Pikat';
            @endphp

            <div class="statusBanner ready mb-4 bg-success-light">
                <div>
                    <div class="statusTitle text-success">Presensi Hari Ini Sudah Tercatat</div>
                    <div class="statusSub">Kehadiran Anda berhasil dicatat pada pukul {{ $jamMasuk }} WIB</div>
                </div>
            </div>

            <div class="card mb-4 text-center p-4">
                <div class="d-flex justify-content-center mb-3">
                    @if ($todayPresensi->foto_masuk_url)
                        <img src="{{ $todayPresensi->foto_masuk_url }}" alt="Foto Presensi Masuk" class="rounded-2xl border shadow-sm" style="width: 140px; height: 140px; object-fit: cover;">
                    @else
                        <div class="avatar-lg bg-success-light text-success font-extrabold rounded-2xl d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 2rem;">
                            ✓
                        </div>
                    @endif
                </div>
                <h3 class="font-extrabold text-lg text-dark mb-1">{{ $displayName }}</h3>
                <div class="text-sm font-semibold text-success mb-3">
                    <ion-icon name="checkmark-circle" style="vertical-align: -2px; font-size: 1.1rem;"></ion-icon> Status: Hadir
                </div>

                <div class="bg-light p-3 rounded-xl mb-4 text-left d-inline-block w-full" style="max-width: 380px;">
                    <div class="d-flex justify-content-between text-sm py-1 border-b">
                        <span class="text-muted">Tanggal:</span>
                        <span class="font-bold text-dark">{{ \Carbon\Carbon::parse($today)->translatedFormat('d F Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between text-sm py-1 border-b">
                        <span class="text-muted">Jam Masuk:</span>
                        <span class="font-bold text-dark">{{ $jamMasuk }} WIB</span>
                    </div>
                    <div class="d-flex justify-content-between text-sm py-1">
                        <span class="text-muted">Lokasi Belajar:</span>
                        <span class="font-bold text-dark">{{ $lokasiNama }}</span>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="{{ route('siswa.dashboard') }}" class="btn btn-primary px-4 py-2 font-bold rounded-xl">
                        Kembali ke Dashboard
                    </a>
                    <a href="{{ route('siswa.riwayat') }}" class="btn btn-light px-4 py-2 font-bold rounded-xl">
                        Riwayat Presensi
                    </a>
                </div>
            </div>

        {{-- ── BELUM ABSEN MASUK HARI INI ── --}}
        @else
            <div class="statusBanner ready mb-4">
                <div>
                    <div class="statusTitle">Belum Melakukan Absensi Hari Ini</div>
                    <div class="statusSub">Silakan lakukan absensi kehadiran saat tiba di area PKBM Pikat</div>
                </div>
            </div>

            {{-- Form absen MASUK --}}
            <form method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" id="presensiForm">
                @csrf
                <input type="hidden" name="mode" value="mulai">
                <input type="hidden" name="lokasi" id="lokasi" value="">
                <input type="hidden" name="lokasi_akurasi" id="lokasi_akurasi" value="">
                <input type="hidden" name="is_mock_location" id="is_mock_location" value="0">

                <div class="card mb-4">
                    <div class="cardTitle">
                        Lokasi Presensi Masuk
                    </div>

                    {{-- Dropdown Pemilihan Titik Lokasi Absen --}}
                    <div class="mb-3" id="boxPilihLokasi">
                        <label class="d-block text-sm font-semibold text-muted mb-2">
                            Pilih Lokasi PKBM / Mitra <span class="text-danger">*</span>
                        </label>
                        <select name="lokasi_presensi_id" id="selectLokasiPresensi" onchange="handleLokasiPresensiChange()" class="select w-full">
                            @forelse($lokasiPresensis as $lok)
                                <option value="{{ $lok->id }}"
                                    data-lat="{{ $lok->latitude }}"
                                    data-lng="{{ $lok->longitude }}"
                                    data-radius="{{ $lok->radius_meter }}"
                                    data-nama="{{ $lok->nama_lokasi }}"
                                    data-alamat="{{ $lok->alamat ?? '' }}"
                                    {{ (old('lokasi_presensi_id') == $lok->id || ($loop->first && !old('lokasi_presensi_id'))) ? 'selected' : '' }}>
                                    {{ $lok->nama_lokasi }}
                                </option>
                            @empty
                                <option value=""
                                    data-lat="{{ (float) ($kantorLat ?? config('lokasi.sekolah_lat', -7.8011945)) }}"
                                    data-lng="{{ (float) ($kantorLng ?? config('lokasi.sekolah_lng', 110.364917)) }}"
                                    data-radius="{{ (int) ($radius ?? config('lokasi.radius_meter', 100)) }}"
                                    data-nama="{{ config('lokasi.sekolah_nama', 'PKBM Pikat') }}"
                                    data-alamat="Gedung Utama PKBM Pikat">
                                    Gedung Pusat PKBM Pikat
                                </option>
                            @endforelse
                        </select>
                        <div id="lokasiPresensiAlamat" class="text-xs text-muted mt-2 font-medium"></div>
                    </div>

                    <div id="mapBox" class="mapBox pos-relative">
                        <div class="mapPlaceholder" id="mapPlaceholder">Memuat peta lokasi…</div>
                        <div id="leafletMap" class="map-camera-box d-none"></div>
                    </div>
                    <div id="geofenceBadge" class="geofence-feedback-badge d-none"></div>
                    
                    {{-- Proximity Radar & Live Track Card --}}
                    <div id="proximityRadarCard" class="proximityRadarCard d-none">
                        <div class="radarHeader">
                            <div class="radarStatusDot" id="radarStatusDot"></div>
                            <div class="radarInfo">
                                <div class="radarTitle" id="radarTitle">Mencari lokasi Anda...</div>
                                <div class="radarSub" id="radarSub">Menghubungkan ke penunjuk lokasi...</div>
                            </div>
                        </div>
                        <div class="radarActions">
                            <a id="btnPetunjukArah" href="#" target="_blank" class="btnNavMaps d-none">
                                Petunjuk Arah (Peta)
                            </a>
                            <button type="button" id="btnToggleLiveGps" class="btnLiveGpsActive" onclick="toggleLiveTracking()">
                                <span class="liveDot" id="liveGpsDot"></span> <span id="liveGpsLabel">Pantau Lokasi: Aktif</span>
                            </button>
                        </div>
                    </div>

                    <div class="mapToolbar">
                        <button type="button" onclick="refreshLocation(true)">
                            Perbarui Lokasi
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="cardTitle">
                        Foto Presensi Masuk
                    </div>
                    <div class="photoFrame pos-relative overflow-hidden">
                        <video id="videoPreview" playsinline muted></video>
                        <img id="previewImg" alt="Preview foto" />

                        {{-- Grid Overlay --}}
                        <div id="cameraGrid" class="cameraGridOverlay d-none">
                            <div class="gridBox">
                                <div class="gridCell"></div><div class="gridCell"></div><div class="gridCell"></div>
                                <div class="gridCell"></div><div class="gridCell"></div><div class="gridCell"></div>
                                <div class="gridCell"></div><div class="gridCell"></div><div class="gridCell"></div>
                            </div>
                        </div>

                        {{-- Floating Camera Toolbar Overlay --}}
                        <div id="camControlBar" class="camControlBar d-none">
                            <div class="camControlGroup">
                                <button type="button" class="camToolBtn active" id="btnToggleMirror" onclick="toggleCameraMirror()" title="Cermin Kamera">
                                    Cermin
                                </button>
                                <button type="button" class="camToolBtn" id="btnToggleSwitch" onclick="switchCameraFacing()" title="Tukar Kamera">
                                    Tukar Kamera
                                </button>
                            </div>
                            <div class="camControlGroup">
                                <button type="button" class="camToolBtn" id="btnToggleGrid" onclick="toggleCameraGrid()" title="Garis Bantu">
                                    Garis Bantu
                                </button>
                                <button type="button" id="btnToggleTorch" onclick="toggleCameraTorch()" title="Lampu Flash" class="camToolBtn d-none">
                                    Lampu
                                </button>
                            </div>
                        </div>

                        <div class="photoPlaceholder" id="placeholder">
                            Ketuk <b>Buka Kamera</b> untuk mengambil foto presensi masuk.
                        </div>
                    </div>

                    <input type="file" name="foto" id="fotoInput" accept="image/jpeg" class="d-none" />
                    <div class="camActions" id="camActions">
                        <button type="button" class="captureBtn" id="btnBukaKamera" onclick="openLiveCamera()">
                            Buka Kamera
                        </button>
                        <div id="camRowStreaming" class="camRow d-none">
                            <button type="button" class="captureBtn" onclick="snapPhoto()">
                                Ambil Foto
                            </button>
                            <button type="button" class="captureBtn secondary" onclick="cancelCamera()">Batal</button>
                        </div>
                        <button type="button" id="btnUlangi" onclick="retakePhoto()" class="captureBtn secondary d-none">
                            Ulangi Foto
                        </button>
                    </div>

                    {{-- Tombol MULAI --}}
                    <button type="submit" id="btnSubmit" class="captureBtn primary-blue mt-4">
                        Kirim Presensi Masuk
                    </button>
                </div>
            </form>
        @endif

    </div>{{-- #mainContent --}}

    <script>
        function showGateError(msg) {
            const permW = document.getElementById('permWarning');
            if (permW) permW.style.display = 'flex';
            const permD = document.getElementById('permWarnDesc');
            if (permD) permD.innerHTML = msg;
            
            var btnSubmit = document.getElementById('btnSubmit');
            if (btnSubmit) btnSubmit.disabled = true;

            const cleanMsg = msg.replace(/<[^>]+>/g, '');
            if (window.showAppToast) {
                window.showAppToast({
                    type: 'warning',
                    title: 'Izin Diperlukan',
                    message: cleanMsg,
                    duration: 4000
                });
            }
        }

        function showMainContent() {
            var permW = document.getElementById('permWarning');
            if (permW) permW.style.display = 'none';
            if (document.getElementById('mapBox')) refreshLocation();
        }

        function checkPermissions() {
            var permW = document.getElementById('permWarning');
            if (permW) permW.style.display = 'none';

            var camP = navigator.mediaDevices ?
                navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: false
                }) :
                Promise.reject(new Error('no_media_devices'));

            var locP = new Promise(function(resolve, reject) {
                if (!navigator.geolocation) return reject(new Error('no_geolocation'));
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    timeout: 6000,
                    maximumAge: 30000
                });
            });

            Promise.all([camP, locP]).then(function(results) {
                var stream = results[0];
                if (stream) stream.getTracks().forEach(function(t) {
                    t.stop();
                });
                showMainContent();
            }).catch(function(err) {
                var name = err && err.name ? err.name : '';
                if (name === 'NotAllowedError' || name === 'PermissionDeniedError') {
                    showGateError('Izin <strong>kamera</strong> dan <strong>lokasi GPS</strong> wajib diaktifkan.');
                } else if (err && err.message === 'no_media_devices') {
                    showGateError('Peramban tidak mendukung kamera. Gunakan Chrome atau Safari terbaru.');
                } else {
                    showGateError('Gagal mendapatkan izin kamera/lokasi GPS. Pastikan GPS aktif.');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            checkPermissions();
        });

        /* ══════════════════ MAP LEAFLET GEOFENCING VISUALIZER ══════════════════ */
        const DEFAULT_GEOFENCE_LAT = {{ (float) ($kantorLat ?? config('lokasi.sekolah_lat', -7.8011945)) }};
        const DEFAULT_GEOFENCE_LNG = {{ (float) ($kantorLng ?? config('lokasi.sekolah_lng', 110.364917)) }};
        const DEFAULT_GEOFENCE_RADIUS = {{ (int) ($radius ?? config('lokasi.radius_meter', 100)) }};
        const DEFAULT_GEOFENCE_NAMA = @json(config('lokasi.sekolah_nama', 'PKBM Pikat'));

        let currentTargetLat = DEFAULT_GEOFENCE_LAT;
        let currentTargetLng = DEFAULT_GEOFENCE_LNG;
        let currentTargetRadius = DEFAULT_GEOFENCE_RADIUS;
        let currentTargetNama = DEFAULT_GEOFENCE_NAMA;
        let currentTargetAlamat = '';

        let allLokasiPoints = @json($lokasiPresensis ?? []);

        let leafletMap = null;
        let geofenceCircle = null;
        let sekolahMarker = null;
        let userMarker = null;
        let trackPolyline = null;
        let otherMarkersGroup = null;
        let watchPositionId = null;
        let isLiveTracking = true;
        let lastWithinZone = null;
        let lastUserLat = null;
        let lastUserLng = null;

        function updateTargetFromDropdown() {
            var selectEl = document.getElementById('selectLokasiPresensi');
            var alamatEl = document.getElementById('lokasiPresensiAlamat');
            if (selectEl && selectEl.selectedOptions && selectEl.selectedOptions[0]) {
                var opt = selectEl.selectedOptions[0];
                currentTargetLat = parseFloat(opt.dataset.lat) || DEFAULT_GEOFENCE_LAT;
                currentTargetLng = parseFloat(opt.dataset.lng) || DEFAULT_GEOFENCE_LNG;
                currentTargetRadius = parseInt(opt.dataset.radius) || DEFAULT_GEOFENCE_RADIUS;
                currentTargetNama = opt.dataset.nama || DEFAULT_GEOFENCE_NAMA;
                currentTargetAlamat = opt.dataset.alamat || '';

                if (alamatEl) {
                    alamatEl.textContent = currentTargetAlamat ? ('Alamat: ' + currentTargetAlamat) : '';
                }
            }
        }

        function handleLokasiPresensiChange() {
            updateTargetFromDropdown();

            if (leafletMap) {
                if (sekolahMarker) {
                    sekolahMarker.setLatLng([currentTargetLat, currentTargetLng]);
                    sekolahMarker.setPopupContent('<b>' + currentTargetNama + '</b><br>Titik Lokasi Absen (Batas Maksimal: ' + currentTargetRadius + ' meter)');
                }
                if (geofenceCircle) {
                    geofenceCircle.setLatLng([currentTargetLat, currentTargetLng]);
                    geofenceCircle.setRadius(currentTargetRadius);
                }

                renderOtherMarkers();

                if (lastUserLat !== null && lastUserLng !== null) {
                    setMapFromLatLng(lastUserLat, lastUserLng);
                } else {
                    leafletMap.setView([currentTargetLat, currentTargetLng], 17);
                }
            }
        }

        function haversineDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        function renderOtherMarkers() {
            if (!leafletMap || !allLokasiPoints || allLokasiPoints.length === 0) return;

            if (otherMarkersGroup) {
                leafletMap.removeLayer(otherMarkersGroup);
            }
            otherMarkersGroup = L.layerGroup();

            var grayIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-grey.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [20, 32],
                iconAnchor: [10, 32],
                popupAnchor: [1, -28],
                shadowSize: [32, 32]
            });

            allLokasiPoints.forEach(function (lok) {
                var lLat = parseFloat(lok.latitude);
                var lLng = parseFloat(lok.longitude);
                if (Math.abs(lLat - currentTargetLat) > 0.00001 || Math.abs(lLng - currentTargetLng) > 0.00001) {
                    var m = L.marker([lLat, lLng], { icon: grayIcon });
                    m.bindPopup('<b>' + lok.nama_lokasi + '</b><br>Radius: ' + lok.radius_meter + 'm<br><small class="text-muted">Pilih di dropdown jika ingin absen di titik ini</small>');
                    otherMarkersGroup.addLayer(m);
                }
            });

            otherMarkersGroup.addTo(leafletMap);
        }

        function initLeafletMap() {
            var mapEl = document.getElementById('leafletMap');
            if (!mapEl || leafletMap || typeof L === 'undefined') return;

            updateTargetFromDropdown();

            mapEl.style.display = 'block';

            leafletMap = L.map('leafletMap').setView([currentTargetLat, currentTargetLng], 17);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(leafletMap);

            var redIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            sekolahMarker = L.marker([currentTargetLat, currentTargetLng], { icon: redIcon }).addTo(leafletMap);
            sekolahMarker.bindPopup('<b>' + currentTargetNama + '</b><br>Titik Lokasi Absen (Batas Maksimal: ' + currentTargetRadius + ' meter)');

            geofenceCircle = L.circle([currentTargetLat, currentTargetLng], {
                color: '#0284c7',
                fillColor: '#38bdf8',
                fillOpacity: 0.25,
                radius: currentTargetRadius
            }).addTo(leafletMap);

            renderOtherMarkers();
        }

        function setMapFromLatLng(lat, lng) {
            lastUserLat = lat;
            lastUserLng = lng;

            var lokasiEl = document.getElementById('lokasi');
            var ph = document.getElementById('mapPlaceholder');
            var badge = document.getElementById('geofenceBadge');
            var radarCard = document.getElementById('proximityRadarCard');
            var radarDot = document.getElementById('radarStatusDot');
            var radarTitle = document.getElementById('radarTitle');
            var radarSub = document.getElementById('radarSub');
            var btnMaps = document.getElementById('btnPetunjukArah');

            if (lokasiEl) {
                lokasiEl.value = lat.toFixed(6) + ',' + lng.toFixed(6);
            }

            initLeafletMap();

            if (ph) ph.classList.add('hidden');

            if (leafletMap) {
                var dist = haversineDistance(lat, lng, currentTargetLat, currentTargetLng);
                var distFormatted = dist.toFixed(1);
                var isWithin = dist <= currentTargetRadius;

                var blueIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });

                if (userMarker) {
                    userMarker.setLatLng([lat, lng]);
                } else {
                    userMarker = L.marker([lat, lng], { icon: blueIcon }).addTo(leafletMap);
                }
                userMarker.bindPopup('<b>Lokasi Anda</b><br>Jarak ke ' + currentTargetNama + ': ' + distFormatted + ' meter');

                // Track Line Polyline
                if (trackPolyline) {
                    trackPolyline.setLatLngs([[lat, lng], [currentTargetLat, currentTargetLng]]);
                } else {
                    trackPolyline = L.polyline([[lat, lng], [currentTargetLat, currentTargetLng]], {
                        color: isWithin ? '#10b981' : (dist > 200 ? '#ef4444' : '#f59e0b'),
                        weight: 3.5,
                        dashArray: isWithin ? null : '8, 8',
                        opacity: isWithin ? 0.4 : 0.85
                    }).addTo(leafletMap);
                }

                if (isWithin) {
                    trackPolyline.setStyle({ color: '#10b981', dashArray: null, opacity: 0.35 });
                } else {
                    trackPolyline.setStyle({
                        color: dist > 200 ? '#ef4444' : '#f59e0b',
                        dashArray: '8, 8',
                        opacity: 0.85
                    });
                }

                var bounds = L.latLngBounds([[currentTargetLat, currentTargetLng], [lat, lng]]);
                leafletMap.fitBounds(bounds, { padding: [35, 35] });

                if (badge) {
                    badge.style.display = 'block';
                    if (isWithin) {
                        geofenceCircle.setStyle({ color: '#16a34a', fillColor: '#4ade80', fillOpacity: 0.3 });
                        badge.style.background = 'rgba(22, 163, 74, 0.12)';
                        badge.style.border = '1px solid rgba(22, 163, 74, 0.35)';
                        badge.style.color = '#15803d';
                        badge.innerHTML = '<span class="d-inline-flex items-center gap-1"><b>Di Dalam Area ' + currentTargetNama + '</b> (' + Math.round(dist) + ' m)</span>';
                    } else {
                        geofenceCircle.setStyle({ color: '#dc2626', fillColor: '#f87171', fillOpacity: 0.3 });
                        badge.style.background = 'rgba(220, 38, 38, 0.12)';
                        badge.style.border = '1px solid rgba(220, 38, 38, 0.35)';
                        badge.style.color = '#b91c1c';
                        badge.innerHTML = '<span class="d-inline-flex items-center gap-1"><b>Di Luar Radius ' + currentTargetNama + '</b> (' + Math.round(dist) + ' m, maks ' + currentTargetRadius + ' m)</span>';
                    }
                }

                // Proximity Radar Update
                if (radarCard) {
                    radarCard.classList.remove('d-none');
                    if (isWithin) {
                        radarCard.className = 'proximityRadarCard radar-in-zone';
                        if (radarDot) radarDot.className = 'radarStatusDot dot-green';
                        if (radarTitle) radarTitle.textContent = 'Posisi Terverifikasi di Area ' + currentTargetNama;
                        if (radarSub) radarSub.textContent = 'Jarak: ' + Math.round(dist) + ' meter dari titik pusat (Radius: ' + currentTargetRadius + 'm).';
                        if (btnMaps) btnMaps.classList.add('d-none');
                    } else {
                        radarCard.className = 'proximityRadarCard radar-out-zone';
                        if (radarDot) radarDot.className = 'radarStatusDot dot-red';
                        var selisih = Math.round(dist - currentTargetRadius);
                        if (radarTitle) radarTitle.textContent = 'Di Luar Batas Presensi (' + Math.round(dist) + ' m)';
                        if (radarSub) radarSub.textContent = 'Mendekatlah sekitar ' + selisih + ' meter lagi ke area ' + currentTargetNama + '.';
                        if (btnMaps) {
                            btnMaps.href = 'https://www.google.com/maps/dir/?api=1&origin=' + lat + ',' + lng + '&destination=' + currentTargetLat + ',' + currentTargetLng;
                            btnMaps.classList.remove('d-none');
                        }
                    }
                }

                if (lastWithinZone !== null && lastWithinZone !== isWithin) {
                    if (window.showAppToast) {
                        window.showAppToast({
                            type: isWithin ? 'success' : 'warning',
                            title: isWithin ? 'Memasuki Area ' + currentTargetNama : 'Keluar Dari Area ' + currentTargetNama,
                            message: isWithin ? 'Anda berada dalam jangkauan presensi (' + Math.round(dist) + 'm).' : 'Anda berada ' + Math.round(dist) + 'm dari titik pusat.',
                            duration: 3500
                        });
                    }
                }
                lastWithinZone = isWithin;
            }
        }

        function refreshLocation(isManual) {
            if (!navigator.geolocation) return;
            
            var ph = document.getElementById('mapPlaceholder');
            if (ph && isManual) {
                ph.textContent = 'Memperbarui koordinat lokasi...';
                ph.classList.remove('hidden');
            }

            navigator.geolocation.getCurrentPosition(function (pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const accuracy = pos.coords.accuracy;

                const accEl = document.getElementById('lokasi_akurasi');
                if (accEl) accEl.value = accuracy;

                const mockEl = document.getElementById('is_mock_location');
                if (mockEl) {
                    const isMock = Boolean(pos.coords.isMocked || pos.coords.mocked);
                    mockEl.value = isMock ? '1' : '0';
                }

                setMapFromLatLng(lat, lng);

                if (isManual && window.showAppToast) {
                    window.showAppToast({
                        type: 'info',
                        title: 'Lokasi Diperbarui',
                        message: 'Akurasi GPS saat ini: ±' + Math.round(accuracy) + ' meter.',
                        duration: 3000
                    });
                }
            }, function (err) {
                if (isManual && window.showAppToast) {
                    window.showAppToast({
                        type: 'error',
                        title: 'Gagal Membaca GPS',
                        message: 'Pastikan layanan lokasi aktif dan sinyal GPS stabil.',
                        duration: 4000
                    });
                }
            }, {
                enableHighAccuracy: true,
                timeout: 8000,
                maximumAge: 5000
            });
        }

        function toggleLiveTracking() {
            isLiveTracking = !isLiveTracking;
            var dot = document.getElementById('liveGpsDot');
            var lbl = document.getElementById('liveGpsLabel');
            var btn = document.getElementById('btnToggleLiveGps');

            if (isLiveTracking) {
                startLiveTracking();
                if (dot) dot.style.background = '#10b981';
                if (lbl) lbl.textContent = 'Pantau Lokasi: Aktif';
                if (btn) btn.className = 'btnLiveGpsActive';
            } else {
                stopLiveTracking();
                if (dot) dot.style.background = '#94a3b8';
                if (lbl) lbl.textContent = 'Pantau Lokasi: Jeda';
                if (btn) btn.className = 'btnLiveGpsPaused';
            }
        }

        function startLiveTracking() {
            if (!navigator.geolocation || watchPositionId !== null) return;
            watchPositionId = navigator.geolocation.watchPosition(function (pos) {
                if (!isLiveTracking) return;
                setMapFromLatLng(pos.coords.latitude, pos.coords.longitude);
            }, function () {}, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 3000
            });
        }

        function stopLiveTracking() {
            if (watchPositionId !== null && navigator.geolocation) {
                navigator.geolocation.clearWatch(watchPositionId);
                watchPositionId = null;
            }
        }

        startLiveTracking();

        /* ══════════════════ CAMERA STREAM & CAPTURE ══════════════════ */
        let stream = null;
        let isMirror = true;
        let currentFacing = 'user';
        let isTorchOn = false;
        let track = null;

        function openLiveCamera() {
            const video = document.getElementById('videoPreview');
            const placeholder = document.getElementById('placeholder');
            const previewImg = document.getElementById('previewImg');
            const btnBuka = document.getElementById('btnBukaKamera');
            const camRow = document.getElementById('camRowStreaming');
            const camBar = document.getElementById('camControlBar');

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Perangkat Anda tidak mendukung akses kamera langsung.');
                return;
            }

            const constraints = {
                video: {
                    facingMode: currentFacing,
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            };

            navigator.mediaDevices.getUserMedia(constraints)
                .then(function (mediaStream) {
                    stream = mediaStream;
                    track = stream.getVideoTracks()[0];
                    video.srcObject = stream;
                    video.style.display = 'block';
                    previewImg.style.display = 'none';
                    placeholder.style.display = 'none';

                    if (btnBuka) btnBuka.style.display = 'none';
                    if (camRow) camRow.classList.remove('d-none');
                    if (camBar) camBar.classList.remove('d-none');

                    applyMirror();
                    video.play();
                })
                .catch(function (err) {
                    showGateError('Gagal membuka kamera: ' + err.message);
                });
        }

        function applyMirror() {
            const video = document.getElementById('videoPreview');
            if (video) {
                video.style.transform = isMirror ? 'scaleX(-1)' : 'scaleX(1)';
            }
        }

        function toggleCameraMirror() {
            isMirror = !isMirror;
            applyMirror();
            const btn = document.getElementById('btnToggleMirror');
            if (btn) btn.classList.toggle('active', isMirror);
        }

        function switchCameraFacing() {
            currentFacing = (currentFacing === 'user') ? 'environment' : 'user';
            isMirror = (currentFacing === 'user');
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
            }
            openLiveCamera();
        }

        function toggleCameraGrid() {
            const grid = document.getElementById('cameraGrid');
            const btn = document.getElementById('btnToggleGrid');
            if (grid) {
                grid.classList.toggle('d-none');
                if (btn) btn.classList.toggle('active', !grid.classList.contains('d-none'));
            }
        }

        function toggleCameraTorch() {
            if (!track || !track.getCapabilities || !track.getCapabilities().torch) return;
            isTorchOn = !isTorchOn;
            track.applyConstraints({
                advanced: [{ torch: isTorchOn }]
            }).catch(e => console.log('Torch error', e));
        }

        function snapPhoto() {
            const video = document.getElementById('videoPreview');
            const previewImg = document.getElementById('previewImg');
            const fotoInput = document.getElementById('fotoInput');
            const camRow = document.getElementById('camRowStreaming');
            const btnUlangi = document.getElementById('btnUlangi');
            const camBar = document.getElementById('camControlBar');

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');

            if (isMirror) {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(function (blob) {
                const file = new File([blob], 'presensi_siswa_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                const dt = new DataTransfer();
                dt.items.add(file);
                fotoInput.files = dt.files;

                previewImg.src = URL.createObjectURL(blob);
                previewImg.style.display = 'block';
                video.style.display = 'none';

                if (stream) {
                    stream.getTracks().forEach(t => t.stop());
                }

                if (camRow) camRow.classList.add('d-none');
                if (btnUlangi) btnUlangi.classList.remove('d-none');
                if (camBar) camBar.classList.add('d-none');
            }, 'image/jpeg', 0.85);
        }

        function retakePhoto() {
            const btnUlangi = document.getElementById('btnUlangi');
            if (btnUlangi) btnUlangi.classList.add('d-none');
            openLiveCamera();
        }

        function cancelCamera() {
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
            }
            const video = document.getElementById('videoPreview');
            const placeholder = document.getElementById('placeholder');
            const btnBuka = document.getElementById('btnBukaKamera');
            const camRow = document.getElementById('camRowStreaming');
            const camBar = document.getElementById('camControlBar');

            if (video) video.style.display = 'none';
            if (placeholder) placeholder.style.display = 'flex';
            if (btnBuka) btnBuka.style.display = 'block';
            if (camRow) camRow.classList.add('d-none');
            if (camBar) camBar.classList.add('d-none');
        }
    </script>
@endsection
