@extends('layouts.presensi')

@section('title', 'Presensi Magang / PKL')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

@section('content')
    @php
        $user = auth()->user();
        $displayName = (string) ($user->nama_lengkap ?? ($user->name ?? 'Mahasiswa Magang'));
        $initial = strtoupper(substr($displayName, 0, 1));

        // Tentukan state sesi aktif hari ini
        // Sesi AKTIF = sudah absen masuk (foto_mulai ada) tapi BELUM absen pulang (foto_selesai kosong)
        $activeSesi = $globalActiveSesi;

        // Hitung sisa waktu jika sesi berjalan (minimal 1 jam)
        $sisaDetik = 0;
        $bisaPulang = false;
        $sudahLewat8Jam = false;
        if ($activeSesi) {
            try {
                $jamMulaiDt = \Carbon\Carbon::parse($today . ' ' . $activeSesi->jam_mulai, 'Asia/Jakarta');
                $nowDt = \Carbon\Carbon::now('Asia/Jakarta');
                $diffDetik = $jamMulaiDt->diffInSeconds($nowDt, false);
                $sisaDetik = max(0, 3600 - $diffDetik);
                $bisaPulang = $diffDetik >= 3600;
                $sudahLewat8Jam = $diffDetik >= 28800;
            } catch (\Throwable) {
            }
        }

        $dashRoute = route('magang.dashboard');
        $storeRoute = route('magang.presensi.store');
    @endphp

    {{-- ── Standard Top Navigation Bar ── --}}
    @include('layouts.components.navigasi_atas', [
        'titleName' => $displayName,
        'subTitle' => 'Presensi Magang • ' . \Carbon\Carbon::parse($today)->translatedFormat('d M Y'),
        'dashRoute' => $dashRoute
    ])

    {{-- ── Warning Banner Izin ── --}}
    <div id="permWarning" class="permWarning" style="display:none;">
        <ion-icon name="warning-outline" style="font-size:20px; color:#d97706; flex-shrink:0;"></ion-icon>
        <div style="flex:1; min-width:0;">
            <div class="permWarnTitle" id="permWarnTitle">Izin belum diberikan</div>
            <div class="permWarnDesc" id="permWarnDesc">Kamera dan lokasi GPS diperlukan untuk absen.</div>
        </div>
        <button onclick="checkPermissions()" class="permWarnBtn">
            <ion-icon name="refresh-outline" style="font-size:14px;"></ion-icon> Coba Lagi
        </button>
    </div>

    {{-- ══════════════════ MAIN CONTENT ══════════════════ --}}
    <div class="pagePad" id="mainContent">

        {{-- ── SESI SEDANG BERJALAN (sudah absen masuk, belum absen pulang) ── --}}
        @if ($activeSesi)
            @php
                $jamMasuk = substr((string) $activeSesi->jam_mulai, 0, 5);
            @endphp

            <div class="statusBanner running">
                <div class="statusIcon warn">
                    <ion-icon name="time-outline"></ion-icon>
                </div>
                <div>
                    <div class="statusTitle">Sesi Magang Sedang Berjalan</div>
                    <div class="statusSub">Absen Masuk tercatat pukul <strong>{{ $jamMasuk }} WIB</strong></div>
                </div>
            </div>

            {{-- Countdown atau siap pulang --}}
            @if (!$bisaPulang)
                @php
                    $menit = floor($sisaDetik / 60);
                    $detik = $sisaDetik % 60;
                    $sisaMenitLabel = sprintf('%02d:%02d', $menit, $detik);
                @endphp
                <div class="countdownCard">
                    <div class="countdownLabel">Bisa Absen Pulang Dalam</div>
                    <div class="countdownTime" id="countdown">{{ $sisaMenitLabel }}</div>
                    <div class="countdownSub">menit lagi (minimal 1 jam durasi magang)</div>
                </div>
            @else
                <div class="statusBanner ready" style="margin-bottom:14px;">
                    <div class="statusIcon blue">
                        <ion-icon name="checkmark-circle-outline"></ion-icon>
                    </div>
                    <div>
                        <div class="statusTitle">Siap Absen Pulang</div>
                        <div class="statusSub">Durasi magang sudah memenuhi syarat minimal (≥ 1 jam)</div>
                    </div>
                </div>
            @endif

            {{-- Form absen PULANG --}}
            @if ($bisaPulang)
                <form method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" id="presensiForm">
                    @csrf
                    <input type="hidden" name="mode" value="selesai">
                    <input type="hidden" name="lokasi" id="lokasi" value="">
                    <input type="hidden" name="lokasi_akurasi" id="lokasi_akurasi" value="">
                    <input type="hidden" name="is_mock_location" id="is_mock_location" value="0">

                    <div class="card" style="margin-bottom:14px;">
                        <div class="cardTitle">
                            <ion-icon name="location-outline"></ion-icon>Lokasi Presensi Pulang & Radius GPS
                        </div>
                        <div class="mapBox" id="mapBox" style="position:relative;">
                            <div class="mapPlaceholder" id="mapPlaceholder">Memuat peta & lokasi GPS…</div>
                            <div id="leafletMap" style="width:100%; height:220px; border-radius:14px; display:none; z-index:1;"></div>
                        </div>
                        <div id="geofenceBadge" style="display:none; margin:10px 0 4px; padding:10px 14px; border-radius:12px; font-size:12px; font-weight:800;"></div>
                        <div class="mapToolbar">
                            <button type="button" onclick="refreshLocation()">
                                <ion-icon name="refresh-outline"></ion-icon> Perbarui Lokasi
                            </button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="cardTitle">
                            <ion-icon name="camera-outline"></ion-icon>Foto Selfie Presensi Pulang
                        </div>
                        <div class="photoFrame" style="position:relative; overflow:hidden;">
                            <video id="videoPreview" playsinline muted></video>
                            <img id="previewImg" alt="Preview foto" />

                            {{-- Grid Overlay --}}
                            <div id="cameraGrid" class="cameraGridOverlay" style="display:none;">
                                <div class="gridBox">
                                    <div class="gridCell"></div><div class="gridCell"></div><div class="gridCell"></div>
                                    <div class="gridCell"></div><div class="gridCell"></div><div class="gridCell"></div>
                                    <div class="gridCell"></div><div class="gridCell"></div><div class="gridCell"></div>
                                </div>
                            </div>

                            {{-- Floating Camera Toolbar Overlay --}}
                            <div id="camControlBar" class="camControlBar" style="display:none;">
                                <div class="camControlGroup">
                                    <button type="button" class="camToolBtn active" id="btnToggleMirror" onclick="toggleCameraMirror()" title="Mirror Kamera">
                                        <ion-icon name="swap-horizontal-outline"></ion-icon> Mirror
                                    </button>
                                    <button type="button" class="camToolBtn" id="btnToggleSwitch" onclick="switchCameraFacing()" title="Tukar Depan/Belakang">
                                        <ion-icon name="camera-reverse-outline"></ion-icon> Switch
                                    </button>
                                </div>
                                <div class="camControlGroup">
                                    <button type="button" class="camToolBtn" id="btnToggleGrid" onclick="toggleCameraGrid()" title="Garis Bantu Komposisi">
                                        <ion-icon name="grid-outline"></ion-icon> Grid
                                    </button>
                                    <button type="button" class="camToolBtn" id="btnToggleTorch" onclick="toggleCameraTorch()" title="Senter / Flash" style="display:none;">
                                        <ion-icon name="flash-outline"></ion-icon> Flash
                                    </button>
                                </div>
                            </div>

                            <div class="photoPlaceholder" id="placeholder">
                                Ketuk <b>Buka Kamera</b> untuk mengambil foto selfie presensi pulang.
                            </div>
                        </div>

                        <input type="file" name="foto" id="fotoInput" accept="image/jpeg" style="display:none;" />
                        <div class="camActions" id="camActions">
                            <button type="button" class="captureBtn" id="btnBukaKamera" onclick="openLiveCamera()">
                                <ion-icon name="camera" style="font-size:20px;"></ion-icon> Buka Kamera
                            </button>
                            <div class="camRow" id="camRowStreaming" style="display:none;">
                                <button type="button" class="captureBtn" onclick="snapPhoto()">
                                    <ion-icon name="radio-button-on" style="font-size:20px;"></ion-icon> Ambil Foto
                                </button>
                                <button type="button" class="captureBtn secondary" onclick="cancelCamera()">Batal</button>
                            </div>
                            <button type="button" class="captureBtn secondary" id="btnUlangi" style="display:none;" onclick="retakePhoto()">
                                <ion-icon name="refresh-outline" style="font-size:18px;"></ion-icon> Ulangi Foto
                            </button>
                        </div>

                        {{-- Tombol SELESAI --}}
                        <button type="submit" class="captureBtn primary-red" id="btnSubmit" style="margin-top:14px;">
                            <ion-icon name="log-out-outline" style="font-size:20px;"></ion-icon>
                            Absen Pulang Sekarang
                        </button>
                    </div>
                </form>
            @else
                <div class="card" style="text-align:center; padding:24px 16px; border-radius:18px;">
                    <div style="font-size:32px; margin-bottom:8px; color:var(--warn);"><ion-icon name="time-outline"></ion-icon></div>
                    <div style="font-size:13.5px; font-weight:800; color:var(--text);">Tombol Absen Pulang Terbuka Otomatis</div>
                    <div style="font-size:11.5px; color:var(--muted); margin-top:4px;">Tersisa {{ number_format($sisaDetik / 60, 0) }} menit lagi (minimal 1 jam durasi magang)</div>
                </div>
            @endif

        {{-- ── BELUM ABSEN MASUK ATAU SUDAH SELESAI HARI INI ── --}}
        @else
            @if ($todayPresensi && $todayPresensi->jam_selesai)
                <div class="statusBanner ready" style="margin-bottom:14px; background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.3);">
                    <div class="statusIcon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                        <ion-icon name="checkmark-done-circle-outline"></ion-icon>
                    </div>
                    <div>
                        <div class="statusTitle" style="color: #065f46;">Presensi Hari Ini Selesai</div>
                        <div class="statusSub">Anda sudah absen masuk ({{ substr((string)$todayPresensi->jam_mulai,0,5) }}) dan pulang ({{ substr((string)$todayPresensi->jam_selesai,0,5) }}).</div>
                    </div>
                </div>
            @else
                <div class="statusBanner ready" style="margin-bottom:14px;">
                    <div class="statusIcon blue">
                        <ion-icon name="log-in-outline"></ion-icon>
                    </div>
                    <div>
                        <div class="statusTitle">Belum Absen Masuk</div>
                        <div class="statusSub">Silakan lakukan presensi masuk untuk memulai magang hari ini</div>
                    </div>
                </div>

                {{-- Form absen MASUK --}}
                <form method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" id="presensiForm">
                    @csrf
                    <input type="hidden" name="mode" value="mulai">
                    <input type="hidden" name="lokasi" id="lokasi" value="">
                    <input type="hidden" name="lokasi_akurasi" id="lokasi_akurasi" value="">
                    <input type="hidden" name="is_mock_location" id="is_mock_location" value="0">

                    <div class="card" style="margin-bottom:14px;">
                        <div class="cardTitle">
                            <ion-icon name="location-outline"></ion-icon>Lokasi Presensi Masuk & Radius GPS
                        </div>
                        <div class="mapBox" id="mapBox" style="position:relative;">
                            <div class="mapPlaceholder" id="mapPlaceholder">Memuat peta & lokasi GPS…</div>
                            <div id="leafletMap" style="width:100%; height:220px; border-radius:14px; display:none; z-index:1;"></div>
                        </div>
                        <div id="geofenceBadge" style="display:none; margin:10px 0 4px; padding:10px 14px; border-radius:12px; font-size:12px; font-weight:800;"></div>
                        <div class="mapToolbar">
                            <button type="button" onclick="refreshLocation()">
                                <ion-icon name="refresh-outline"></ion-icon> Perbarui Lokasi
                            </button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="cardTitle">
                            <ion-icon name="camera-outline"></ion-icon>Foto Selfie Presensi Masuk
                        </div>
                        <div class="photoFrame" style="position:relative; overflow:hidden;">
                            <video id="videoPreview" playsinline muted></video>
                            <img id="previewImg" alt="Preview foto" />

                            {{-- Grid Overlay --}}
                            <div id="cameraGrid" class="cameraGridOverlay" style="display:none;">
                                <div class="gridBox">
                                    <div class="gridCell"></div><div class="gridCell"></div><div class="gridCell"></div>
                                    <div class="gridCell"></div><div class="gridCell"></div><div class="gridCell"></div>
                                    <div class="gridCell"></div><div class="gridCell"></div><div class="gridCell"></div>
                                </div>
                            </div>

                            {{-- Floating Camera Toolbar Overlay --}}
                            <div id="camControlBar" class="camControlBar" style="display:none;">
                                <div class="camControlGroup">
                                    <button type="button" class="camToolBtn active" id="btnToggleMirror" onclick="toggleCameraMirror()" title="Mirror Kamera">
                                        <ion-icon name="swap-horizontal-outline"></ion-icon> Mirror
                                    </button>
                                    <button type="button" class="camToolBtn" id="btnToggleSwitch" onclick="switchCameraFacing()" title="Tukar Depan/Belakang">
                                        <ion-icon name="camera-reverse-outline"></ion-icon> Switch
                                    </button>
                                </div>
                                <div class="camControlGroup">
                                    <button type="button" class="camToolBtn" id="btnToggleGrid" onclick="toggleCameraGrid()" title="Garis Bantu Komposisi">
                                        <ion-icon name="grid-outline"></ion-icon> Grid
                                    </button>
                                    <button type="button" class="camToolBtn" id="btnToggleTorch" onclick="toggleCameraTorch()" title="Senter / Flash" style="display:none;">
                                        <ion-icon name="flash-outline"></ion-icon> Flash
                                    </button>
                                </div>
                            </div>

                            <div class="photoPlaceholder" id="placeholder">
                                Ketuk <b>Buka Kamera</b> untuk mengambil foto selfie presensi masuk.
                            </div>
                        </div>

                        <input type="file" name="foto" id="fotoInput" accept="image/jpeg" style="display:none;" />
                        <div class="camActions" id="camActions">
                            <button type="button" class="captureBtn" id="btnBukaKamera" onclick="openLiveCamera()">
                                <ion-icon name="camera" style="font-size:20px;"></ion-icon> Buka Kamera
                            </button>
                            <div class="camRow" id="camRowStreaming" style="display:none;">
                                <button type="button" class="captureBtn" onclick="snapPhoto()">
                                    <ion-icon name="radio-button-on" style="font-size:20px;"></ion-icon> Ambil Foto
                                </button>
                                <button type="button" class="captureBtn secondary" onclick="cancelCamera()">Batal</button>
                            </div>
                            <button type="button" class="captureBtn secondary" id="btnUlangi" style="display:none;" onclick="retakePhoto()">
                                <ion-icon name="refresh-outline" style="font-size:18px;"></ion-icon> Ulangi Foto
                            </button>
                        </div>

                        {{-- Tombol MULAI --}}
                        <button type="submit" class="captureBtn primary-blue" id="btnSubmit" style="margin-top:14px;">
                            <ion-icon name="log-in-outline" style="font-size:20px;"></ion-icon>
                            Mulai Presensi Masuk
                        </button>
                    </div>
                </form>
            @endif
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
            document.getElementById('permWarning').style.display = 'none';
            if (document.getElementById('mapBox')) refreshLocation();
        }

        function checkPermissions() {
            document.getElementById('permWarning').style.display = 'none';

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

            var cdEl = document.getElementById('countdown');
            @if ($activeSesi && !$bisaPulang)
                var sisaDetik = {{ $sisaDetik }};
                if (cdEl && sisaDetik > 0) {
                    var totalSec = sisaDetik;
                    var cdInterval = setInterval(function() {
                        totalSec--;
                        if (totalSec <= 0) {
                            clearInterval(cdInterval);
                            cdEl.textContent = '00:00';
                            window.location.reload();
                            return;
                        }
                        var m = Math.floor(totalSec / 60);
                        var s = totalSec % 60;
                        cdEl.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                    }, 1000);
                }
            @endif
        });

        /* ══════════════════ MAP LEAFLET GEOFENCING VISUALIZER ══════════════════ */
        const GEOFENCE_LAT = {{ (float) ($kantorLat ?? config('lokasi.sekolah_lat', -7.8011945)) }};
        const GEOFENCE_LNG = {{ (float) ($kantorLng ?? config('lokasi.sekolah_lng', 110.364917)) }};
        const GEOFENCE_RADIUS = {{ (int) ($radius ?? config('lokasi.radius_meter', 100)) }};
        const GEOFENCE_NAMA = @json(config('lokasi.sekolah_nama', 'PKBM Pikat'));

        let leafletMap = null;
        let geofenceCircle = null;
        let sekolahMarker = null;
        let userMarker = null;

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

        function initLeafletMap() {
            var mapEl = document.getElementById('leafletMap');
            if (!mapEl || leafletMap || typeof L === 'undefined') return;

            mapEl.style.display = 'block';

            leafletMap = L.map('leafletMap').setView([GEOFENCE_LAT, GEOFENCE_LNG], 17);

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

            sekolahMarker = L.marker([GEOFENCE_LAT, GEOFENCE_LNG], { icon: redIcon }).addTo(leafletMap);
            sekolahMarker.bindPopup('<b>🏢 ' + GEOFENCE_NAMA + '</b><br>Titik Lokasi Kantor (Batas Maksimal: ' + GEOFENCE_RADIUS + ' meter)');

            geofenceCircle = L.circle([GEOFENCE_LAT, GEOFENCE_LNG], {
                color: '#0284c7',
                fillColor: '#38bdf8',
                fillOpacity: 0.25,
                radius: GEOFENCE_RADIUS
            }).addTo(leafletMap);
        }

        function setMapFromLatLng(lat, lng) {
            var lokasiEl = document.getElementById('lokasi');
            var ph = document.getElementById('mapPlaceholder');
            var hint = document.getElementById('mapHint');
            var badge = document.getElementById('geofenceBadge');

            if (lokasiEl) {
                lokasiEl.value = lat.toFixed(6) + ',' + lng.toFixed(6);
            }

            initLeafletMap();

            if (ph) ph.classList.add('hidden');

            if (leafletMap) {
                var dist = haversineDistance(lat, lng, GEOFENCE_LAT, GEOFENCE_LNG);
                var distFormatted = dist.toFixed(1);

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
                userMarker.bindPopup('<b>📍 Lokasi Anda</b><br>Jarak ke ' + GEOFENCE_NAMA + ': ' + distFormatted + ' meter');

                var bounds = L.latLngBounds([[GEOFENCE_LAT, GEOFENCE_LNG], [lat, lng]]);
                leafletMap.fitBounds(bounds, { padding: [35, 35] });

                if (badge) {
                    badge.style.display = 'block';
                    if (dist <= GEOFENCE_RADIUS) {
                        geofenceCircle.setStyle({ color: '#16a34a', fillColor: '#4ade80', fillOpacity: 0.3 });
                        badge.style.background = 'rgba(22, 163, 74, 0.12)';
                        badge.style.border = '1px solid rgba(22, 163, 74, 0.35)';
                        badge.style.color = '#15803d';
                        badge.innerHTML = '🟢 <b>Di Dalam Radius ' + GEOFENCE_NAMA + '</b> (' + distFormatted + ' m — Maks: ' + GEOFENCE_RADIUS + 'm)';
                    } else {
                        geofenceCircle.setStyle({ color: '#dc2626', fillColor: '#f87171', fillOpacity: 0.3 });
                        badge.style.background = 'rgba(220, 38, 38, 0.12)';
                        badge.style.border = '1px solid rgba(220, 38, 38, 0.35)';
                        badge.style.color = '#dc2626';
                        badge.innerHTML = '🔴 <b>Di Luar Radius ' + GEOFENCE_NAMA + '</b> (' + distFormatted + ' m — Maks: ' + GEOFENCE_RADIUS + 'm)';
                    }
                }

                if (hint) {
                    hint.textContent = lat.toFixed(5) + ', ' + lng.toFixed(5) + ' (Jarak: ' + distFormatted + 'm dari ' + GEOFENCE_NAMA + ')';
                }
            }
        }

        function refreshLocation() {
            var ph = document.getElementById('mapPlaceholder');
            if (!ph) return;
            if (!navigator.geolocation) {
                ph.textContent = 'Geolocation tidak didukung oleh browser Anda.';
                return;
            }
            ph.classList.remove('hidden');
            ph.textContent = 'Mencari lokasi GPS presisi…';
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    var isFake = false;
                    var accuracy = pos.coords.accuracy || 0;

                    var akurasiEls = document.querySelectorAll('input[name="lokasi_akurasi"]');
                    var mockEls = document.querySelectorAll('input[name="is_mock_location"]');

                    akurasiEls.forEach(function(el) { el.value = accuracy; });

                    if (pos.coords.mocked === true || accuracy === 0) {
                        isFake = true;
                    } else if (pos.coords.altitude === 0 && pos.coords.altitudeAccuracy === 0 && pos.coords.speed === 0 && pos.coords.heading === 0) {
                        isFake = true;
                    }

                    mockEls.forEach(function(el) { el.value = isFake ? '1' : '0'; });
                    
                    if (isFake) {
                        ph.textContent = 'Terdeteksi penggunaan Fake GPS / Mock Location. Matikan aplikasi Fake GPS!';
                        ph.style.color = '#ef4444';
                        alert('Peringatan: Sistem mendeteksi kemungkinan Fake GPS atau Mock Location. Harap matikan aplikasi tersebut.');
                        return;
                    }

                    ph.style.color = 'var(--muted)';
                    setMapFromLatLng(pos.coords.latitude, pos.coords.longitude);
                },
                function() {
                    ph.textContent = 'Gagal mengambil lokasi GPS. Pastikan izin lokasi aktif.';
                }, {
                    enableHighAccuracy: true,
                    timeout: 12000,
                    maximumAge: 0
                }
            );
        }

        /* ══════════════════ ADVANCED CAMERA CONTROLS ══════════════════ */
        let mediaStream = null;
        let currentFacingMode = 'user';
        let isMirrored = true;
        let isGridActive = false;
        let isTorchOn = false;

        function updateCameraTransform() {
            var video = document.getElementById('videoPreview');
            if (video) {
                video.style.transform = isMirrored ? 'scaleX(-1)' : 'scaleX(1)';
            }
            var btnMirror = document.getElementById('btnToggleMirror');
            if (btnMirror) {
                if (isMirrored) btnMirror.classList.add('active');
                else btnMirror.classList.remove('active');
            }
        }

        function toggleCameraMirror() {
            isMirrored = !isMirrored;
            updateCameraTransform();
        }

        function toggleCameraGrid() {
            isGridActive = !isGridActive;
            var gridEl = document.getElementById('cameraGrid');
            var btnGrid = document.getElementById('btnToggleGrid');
            if (gridEl) gridEl.style.display = isGridActive ? 'block' : 'none';
            if (btnGrid) {
                if (isGridActive) btnGrid.classList.add('active');
                else btnGrid.classList.remove('active');
            }
        }

        function toggleCameraTorch() {
            if (!mediaStream) return;
            var videoTrack = mediaStream.getVideoTracks()[0];
            if (!videoTrack) return;

            var btnTorch = document.getElementById('btnToggleTorch');
            isTorchOn = !isTorchOn;
            videoTrack.applyConstraints({
                advanced: [{ torch: isTorchOn }]
            }).then(function() {
                if (btnTorch) {
                    if (isTorchOn) btnTorch.classList.add('active');
                    else btnTorch.classList.remove('active');
                }
            }).catch(function(err) {
                isTorchOn = false;
                if (btnTorch) btnTorch.classList.remove('active');
            });
        }

        function checkTorchSupport() {
            var btnTorch = document.getElementById('btnToggleTorch');
            if (!btnTorch) return;
            if (!mediaStream) {
                btnTorch.style.display = 'none';
                return;
            }
            var videoTrack = mediaStream.getVideoTracks()[0];
            if (videoTrack && typeof videoTrack.getCapabilities === 'function') {
                var capabilities = videoTrack.getCapabilities();
                if (capabilities.torch) {
                    btnTorch.style.display = 'inline-flex';
                    return;
                }
            }
            btnTorch.style.display = 'none';
        }

        function switchCameraFacing() {
            currentFacingMode = (currentFacingMode === 'user') ? 'environment' : 'user';
            isMirrored = (currentFacingMode === 'user');

            var btnSwitch = document.getElementById('btnToggleSwitch');
            if (btnSwitch) {
                if (currentFacingMode === 'environment') btnSwitch.classList.add('active');
                else btnSwitch.classList.remove('active');
            }

            if (mediaStream) {
                stopCameraStream();
                openLiveCamera(true);
            }
        }

        function openLiveCamera(isSwitching = false) {
            if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
                alert('Peramban tidak mendukung kamera langsung. Gunakan Chrome/Safari terbaru.');
                return;
            }

            var video = document.getElementById('videoPreview');
            var tryCamera = function() {
                return navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: { ideal: currentFacingMode },
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                }).catch(function() {
                    return navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: false
                    });
                });
            };

            tryCamera().then(function(stream) {
                mediaStream = stream;
                video.srcObject = stream;
                video.classList.add('active');
                
                updateCameraTransform();
                checkTorchSupport();

                document.getElementById('placeholder').classList.add('hidden');
                
                var bar = document.getElementById('camControlBar');
                if (bar) bar.style.display = 'flex';

                var img = document.getElementById('previewImg');
                img.classList.remove('visible');
                img.removeAttribute('src');

                document.getElementById('btnBukaKamera').style.display = 'none';
                document.getElementById('camRowStreaming').style.display = 'flex';
                document.getElementById('btnUlangi').style.display = 'none';

                return video.play();
            }).catch(function(err) {
                alert('Tidak bisa membuka kamera: ' + (err && err.message ? err.message : 'izin ditolak.'));
            });
        }

        function stopCameraStream() {
            if (mediaStream) {
                mediaStream.getTracks().forEach(function(t) {
                    t.stop();
                });
                mediaStream = null;
            }
            var video = document.getElementById('videoPreview');
            if (video) video.srcObject = null;
            var bar = document.getElementById('camControlBar');
            if (bar) bar.style.display = 'none';
        }

        function cancelCamera() {
            stopCameraStream();
            document.getElementById('videoPreview').classList.remove('active');
            document.getElementById('camRowStreaming').style.display = 'none';
            document.getElementById('btnBukaKamera').style.display = 'flex';
            var input = document.getElementById('fotoInput');
            if (!input || !input.files || !input.files.length) {
                document.getElementById('placeholder').classList.remove('hidden');
            }
        }

        function snapPhoto() {
            var video = document.getElementById('videoPreview');
            if (!video || !video.videoWidth) {
                alert('Kamera belum siap, tunggu sebentar.');
                return;
            }

            var canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            var ctx = canvas.getContext('2d');

            if (isMirrored) {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }

            ctx.drawImage(video, 0, 0);

            canvas.toBlob(function(blob) {
                if (!blob) {
                    alert('Gagal membuat gambar foto.');
                    return;
                }
                try {
                    var file = new File([blob], 'presensi-magang-' + Date.now() + '.jpg', {
                        type: 'image/jpeg'
                    });
                    var dt = new DataTransfer();
                    dt.items.add(file);
                    document.getElementById('fotoInput').files = dt.files;
                } catch (e) {
                    alert('Coba Chrome/Safari terbaru.');
                    return;
                }

                var url = URL.createObjectURL(blob);
                var img = document.getElementById('previewImg');
                img.src = url;
                img.classList.add('visible');

                stopCameraStream();

                document.getElementById('videoPreview').classList.remove('active');
                document.getElementById('camRowStreaming').style.display = 'none';
                document.getElementById('btnBukaKamera').style.display = 'none';
                document.getElementById('btnUlangi').style.display = 'flex';
                document.getElementById('placeholder').classList.add('hidden');

            }, 'image/jpeg', 0.88);
        }

        function retakePhoto() {
            document.getElementById('fotoInput').value = '';
            var img = document.getElementById('previewImg');
            img.removeAttribute('src');
            img.classList.remove('visible');
            document.getElementById('btnUlangi').style.display = 'none';
            document.getElementById('btnBukaKamera').style.display = 'flex';
            document.getElementById('placeholder').classList.remove('hidden');
        }

        var presensiForm = document.getElementById('presensiForm');
        if (presensiForm) {
            presensiForm.addEventListener('submit', function(e) {
                var input = document.getElementById('fotoInput');
                if (!input || !input.files || !input.files.length) {
                    e.preventDefault();
                    alert('Ambil foto selfie langsung dengan kamera terlebih dahulu.');
                }
            });
        }
    </script>
@endsection
