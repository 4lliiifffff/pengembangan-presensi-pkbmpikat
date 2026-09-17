@extends('layouts.presensi')

@php
    $roleTitle = match ($user?->role) {
        'admin' => 'Presensi Admin',
        'kepala_sekolah' => 'Presensi Kepala Sekolah',
        default => 'Presensi Karyawan',
    };
@endphp

@section('title', $roleTitle)

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

@section('content')
    @php
        $displayName = (string) ($user->nama_lengkap ?? ($user->name ?? 'Karyawan'));
        $initial = strtoupper(substr($displayName, 0, 1));

        // Hitung sisa waktu jika sesi berjalan
        $sisamenit = 0;
        $sisaDetik = 0;
        $bisaPulang = false;
        $sudahLewat2Jam = false;
        if ($activeSesi) {
            try {
                $jamMulaiDt = \Carbon\Carbon::parse($today . ' ' . $activeSesi->jam_mulai, 'Asia/Jakarta');
                $nowDt = \Carbon\Carbon::now('Asia/Jakarta');
                $diffDetik = $jamMulaiDt->diffInSeconds($nowDt, false);
                $sisaDetik = max(0, 3600 - $diffDetik);
                $sisamenit = $sisaDetik / 60;
                $bisaPulang = $diffDetik >= 3600;
                $sudahLewat2Jam = $diffDetik >= 7200;
            } catch (\Throwable) {
            }
        }

        // Mode otomatis: mulai (belum/sudah selesai) atau selesai (sedang berjalan)
        $autoMode = $activeSesi ? 'selesai' : 'mulai';
        $dashRoute = match ($user?->role) {
            'admin' => route('admin.dashboard'),
            'kepala_sekolah' => route('kepsek.dashboard'),
            default => route('tutor.dashboard'),
        };
        $storeRoute = match ($user?->role) {
            'admin' => route('admin.presensi.store'),
            'kepala_sekolah' => route('kepsek.presensi.store'),
            default => route('tutor.presensi.store'),
        };
        // Nomor WA admin dari env (aman dipakai di @php, bukan langsung di HTML)
        $adminWa = config('app.admin_wa', '6281234567890');
    @endphp

    {{-- ── Standard Top Navigation Bar ── --}}
    @include('layouts.components.navigasi_atas', [
        'titleName' => $displayName,
        'subTitle' => $roleTitle . ' • ' . \Carbon\Carbon::parse($today)->translatedFormat('d M Y'),
        'dashRoute' => $dashRoute
    ])

    {{-- ── Warning Banner Izin ── --}}
    <div id="permWarning" class="permWarning">
        <ion-icon name="warning-outline" class="icon-lg text-warning flex-shrink-0"></ion-icon>
        <div class="flex-1 min-w-0">
            <div class="permWarnTitle" id="permWarnTitle">Izin belum diberikan</div>
            <div class="permWarnDesc" id="permWarnDesc">Kamera dan lokasi diperlukan untuk absen.</div>
        </div>
        <button onclick="checkPermissions()" class="permWarnBtn">
            <ion-icon name="refresh-outline" class="text-md"></ion-icon> Coba Lagi
        </button>
    </div>

    {{-- ══════════════════ MAIN CONTENT ══════════════════ --}}
    <div class="pagePad" id="mainContent">

        {{-- ── RIWAYAT SESI SELESAI HARI INI ── --}}
        @if ($completedSessions->count() > 0)
            <div class="card mb-4">
                <div class="cardTitle">
                    <ion-icon name="time-outline"></ion-icon>Riwayat Sesi Hari Ini
                </div>
                @foreach ($completedSessions as $sesi)
                    @php
                        $jm = substr((string) $sesi->jam_mulai, 0, 5);
                        $js = substr((string) $sesi->jam_selesai, 0, 5);
                        try {
                            $mMulai = \Carbon\Carbon::parse($today . ' ' . $sesi->jam_mulai, 'Asia/Jakarta');
                            $mSelesai = \Carbon\Carbon::parse($today . ' ' . $sesi->jam_selesai, 'Asia/Jakarta');
                            $durMin = $mMulai->diffInMinutes($mSelesai);
                            $durLabel = floor($durMin / 60) . 'j ' . $durMin % 60 . 's';
                        } catch (\Throwable) {
                            $durLabel = '-';
                        }
                    @endphp
                    <div  class="border-b-base py-2">
                        <div class="text-sm font-black text-dark">
                            {{ $roleTitle }}</div>
                        <div class="text-xs text-muted">{{ $jm }} - {{ $js }}
                            ({{ $durLabel }})
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ── SESI SEDANG BERJALAN (sudah absen masuk, belum absen pulang) ── --}}
        @if ($activeSesi)
            @php
                $jamMasuk = substr((string) $activeSesi->jam_mulai, 0, 5);
            @endphp

            <div class="statusBanner running">
                <div class="statusIcon warn">
                    <ion-icon name="time-outline"></ion-icon>
                </div>
                <div >
                    <div class="statusTitle">Sesi Sedang Berjalan</div>
                    <div class="statusSub">Masuk pukul {{ $jamMasuk }}</div>
                </div>
            </div>
            {{-- Countdown atau siap pulang --}}
            @if (!$bisaPulang)
                @php
                    // Hitung total menit dan sisa detik secara manual
                    $menit = floor($sisaDetik / 60);
                    $detik = $sisaDetik % 60;

                    // Format dengan menambahkan '0' di depan jika angka di bawah 10 (misal: 05:03)
                    $sisaMenitLabel = sprintf('%02d:%02d', $menit, $detik);
                @endphp
                <div class="countdownCard">
                    <div class="countdownLabel">Bisa absen pulang dalam</div>
                    <div class="countdownTime" id="countdown">{{ $sisaMenitLabel }}</div>
                    <div class="countdownSub">menit lagi (minimal 1 jam setelah masuk)</div>
                </div>
            @else
                <div class="statusBanner ready mb-4">
                    <div class="statusIcon blue">
                        <ion-icon name="checkmark-circle-outline"></ion-icon>
                    </div>
                    <div >
                        <div class="statusTitle">Siap Absen Pulang</div>
                        <div class="statusSub">Sudah lebih dari 1 jam sejak masuk</div>
                    </div>
                </div>
            @endif

            {{-- Warning: sudah lebih dari 2 jam --}}
            @if ($sudahLewat2Jam)
                <div  class="statusBanner mb-3 alert-danger-box">
                    <div  class="statusIcon text-danger bg-danger-light">
                        <ion-icon name="warning-outline"></ion-icon>
                    </div>
                    <div >
                        <div class="statusTitle text-danger">Sudah Lewat 2 Jam!</div>
                        <div class="statusSub">Segera lakukan absen pulang sekarang.</div>
                    </div>
                </div>
            @endif

            {{-- Form absen PULANG: hanya tampil jika sudah bisa pulang --}}
            @if ($bisaPulang)
            <form method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" id="presensiForm">
                @csrf
                <input type="hidden" name="mode" value="selesai">
                <input type="hidden" name="lokasi" id="lokasi" value="">
                <input type="hidden" name="lokasi_akurasi" id="lokasi_akurasi" value="">
                <input type="hidden" name="is_mock_location" id="is_mock_location" value="0">

                <div class="card mb-4">
                    <div class="cardTitle">
                        <ion-icon name="location-outline"></ion-icon>Lokasi Presensi Pulang & Radius GPS
                    </div>
                    <div id="mapBox" class="mapBox pos-relative">
                        <div class="mapPlaceholder" id="mapPlaceholder">Memuat peta & lokasi GPS…</div>
                        <div id="leafletMap" class="map-camera-box d-none"></div>
                    </div>
                    <div id="geofenceBadge" class="geofence-feedback-badge d-none"></div>
                    
                    {{-- Proximity Radar & Live Track Card --}}
                    <div id="proximityRadarCard" class="proximityRadarCard d-none">
                        <div class="radarHeader">
                            <div class="radarStatusDot" id="radarStatusDot"></div>
                            <div class="radarInfo">
                                <div class="radarTitle" id="radarTitle">Mendeteksi Jarak ke PKBM Pikat...</div>
                                <div class="radarSub" id="radarSub">Pembaruan lokasi live GPS aktif</div>
                            </div>
                        </div>
                        <div class="radarActions">
                            <a id="btnPetunjukArah" href="#" target="_blank" class="btnNavMaps d-none">
                                <ion-icon name="navigate-circle-outline"></ion-icon> Petunjuk Arah (Maps)
                            </a>
                            <button type="button" id="btnToggleLiveGps" class="btnLiveGpsActive" onclick="toggleLiveTracking()">
                                <span class="liveDot" id="liveGpsDot"></span> <span id="liveGpsLabel">Live Track: ON</span>
                            </button>
                        </div>
                    </div>

                    <div class="mapToolbar">
                        <button type="button" onclick="refreshLocation(true)">
                            <ion-icon name="refresh-outline"></ion-icon> Perbarui Lokasi
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="cardTitle">
                        <ion-icon name="camera-outline"></ion-icon>Foto Selfie Presensi Pulang
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
                                <button type="button" id="btnToggleTorch" onclick="toggleCameraTorch()" title="Senter / Flash" class="camToolBtn d-none">
                                    <ion-icon name="flash-outline"></ion-icon> Flash
                                </button>
                            </div>
                        </div>

                        <div class="photoPlaceholder" id="placeholder">
                            Ketuk <b >Buka Kamera</b> untuk mengambil foto selfie presensi pulang.
                        </div>
                    </div>

                    <input type="file" name="foto" id="fotoInput" accept="image/jpeg" class="d-none" />
                    <div class="camActions" id="camActions">
                        <button type="button" class="captureBtn" id="btnBukaKamera" onclick="openLiveCamera()">
                            <ion-icon name="camera" class="icon-lg"></ion-icon> Buka Kamera
                        </button>
                        <div id="camRowStreaming" class="camRow d-none">
                            <button type="button" class="captureBtn" onclick="snapPhoto()">
                                <ion-icon name="radio-button-on" class="icon-lg"></ion-icon> Ambil Foto
                            </button>
                            <button type="button" class="captureBtn secondary" onclick="cancelCamera()">Batal</button>
                        </div>
                        <button type="button" id="btnUlangi" onclick="retakePhoto()" class="captureBtn secondary d-none">
                            <ion-icon name="refresh-outline" class="icon-md"></ion-icon> Ulangi Foto
                        </button>
                    </div>

                    {{-- Tombol SELESAI --}}
                    <button type="submit" id="btnSubmit" @if (!$bisaPulang) disabled @endif class="captureBtn primary-red mt-4">
                        <ion-icon name="log-out-outline" class="icon-lg"></ion-icon>
                        Selesai (Absen Pulang)
                    </button>
                </div>
            </form>

            @else
                {{-- Belum 1 jam: tampilkan info saja, tanpa form --}}
                <div class="card text-center p-4 rounded-xl">
                    <div class="icon-2xl mb-2 text-warning"><ion-icon name="time-outline"></ion-icon></div>
                    <div class="text-md font-extrabold text-dark">Form Absen Pulang Terbuka Otomatis</div>
                    <div class="text-sm text-muted mt-1">Tersisa {{ number_format($sisaDetik / 60, 0) }} menit lagi (minimal 1 jam durasi kerja)</div>
                </div>
            @endif

            {{-- ── BELUM ABSEN (belum masuk, atau sesi sudah selesai → tombol Mulai) ── --}}
        @else
            <div class="statusBanner ready mb-4">
                <div class="statusIcon blue">
                    <ion-icon name="log-in-outline"></ion-icon>
                </div>
                <div >
                    <div class="statusTitle">Belum Absen Masuk</div>
                    <div class="statusSub">Silakan lakukan presensi masuk hari ini</div>
                </div>
            </div>

            {{-- Form absen MASUK (tombol "Mulai") --}}
            <form method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" id="presensiForm">
                @csrf
                <input type="hidden" name="mode" value="mulai">
                <input type="hidden" name="lokasi" id="lokasi" value="">
                <input type="hidden" name="lokasi_akurasi" id="lokasi_akurasi" value="">
                <input type="hidden" name="is_mock_location" id="is_mock_location" value="0">

                <div class="card mb-4">
                    <div class="cardTitle">
                        <ion-icon name="location-outline"></ion-icon>Lokasi Presensi Masuk & Radius GPS
                    </div>

                    {{-- Dropdown Pemilihan Titik Lokasi Absen Karyawan --}}
                    <div class="mb-3" id="boxPilihLokasi">
                        <label class="d-block text-sm font-extrabold text-uppercase text-muted mb-1">
                            Pilih Titik Lokasi Absen <span class="text-danger">*</span>
                        </label>
                        <select name="lokasi_presensi_id" id="selectLokasiPresensi" onchange="handleLokasiPresensiChange()" class="input w-full rounded-lg p-2 text-md font-semibold text-dark border-base bg-card-alt">
                            @forelse($lokasiPresensis as $lok)
                                <option value="{{ $lok->id }}"
                                    data-lat="{{ $lok->latitude }}"
                                    data-lng="{{ $lok->longitude }}"
                                    data-radius="{{ $lok->radius_meter }}"
                                    data-nama="{{ $lok->nama_lokasi }}"
                                    data-alamat="{{ $lok->alamat ?? '' }}"
                                    {{ (old('lokasi_presensi_id') == $lok->id || ($loop->first && !old('lokasi_presensi_id'))) ? 'selected' : '' }}>
                                    📍 {{ $lok->nama_lokasi }} (Radius: {{ $lok->radius_meter }}m)
                                </option>
                            @empty
                                <option value=""
                                    data-lat="{{ config('lokasi.sekolah_lat', -7.8011945) }}"
                                    data-lng="{{ config('lokasi.sekolah_lng', 110.364917) }}"
                                    data-radius="{{ config('lokasi.radius_meter', 100) }}"
                                    data-nama="{{ config('lokasi.sekolah_nama', 'PKBM Pikat') }}"
                                    data-alamat="Gedung Pusat PKBM Pikat">
                                    📍 Gedung Pusat PKBM Pikat (Default)
                                </option>
                            @endforelse
                        </select>
                        <div id="lokasiPresensiAlamat" class="text-xs text-muted mt-1 font-medium"></div>
                    </div>

                    <div id="mapBox" class="mapBox pos-relative">
                        <div class="mapPlaceholder" id="mapPlaceholder">Memuat peta & lokasi GPS…</div>
                        <div id="leafletMap" class="map-camera-box d-none"></div>
                    </div>
                    <div id="geofenceBadge" class="geofence-feedback-badge d-none"></div>
                    
                    {{-- Proximity Radar & Live Track Card --}}
                    <div id="proximityRadarCard" class="proximityRadarCard d-none">
                        <div class="radarHeader">
                            <div class="radarStatusDot" id="radarStatusDot"></div>
                            <div class="radarInfo">
                                <div class="radarTitle" id="radarTitle">Mendeteksi Jarak ke PKBM Pikat...</div>
                                <div class="radarSub" id="radarSub">Pembaruan lokasi live GPS aktif</div>
                            </div>
                        </div>
                        <div class="radarActions">
                            <a id="btnPetunjukArah" href="#" target="_blank" class="btnNavMaps d-none">
                                <ion-icon name="navigate-circle-outline"></ion-icon> Petunjuk Arah (Maps)
                            </a>
                            <button type="button" id="btnToggleLiveGps" class="btnLiveGpsActive" onclick="toggleLiveTracking()">
                                <span class="liveDot" id="liveGpsDot"></span> <span id="liveGpsLabel">Live Track: ON</span>
                            </button>
                        </div>
                    </div>

                    <div class="mapToolbar">
                        <button type="button" onclick="refreshLocation(true)">
                            <ion-icon name="refresh-outline"></ion-icon> Perbarui Lokasi
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="cardTitle">
                        <ion-icon name="camera-outline"></ion-icon>Foto Selfie Presensi Masuk
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
                                <button type="button" id="btnToggleTorch" onclick="toggleCameraTorch()" title="Senter / Flash" class="camToolBtn d-none">
                                    <ion-icon name="flash-outline"></ion-icon> Flash
                                </button>
                            </div>
                        </div>

                        <div class="photoPlaceholder" id="placeholder">
                            Ketuk <b >Buka Kamera</b> untuk mengambil foto selfie presensi masuk.
                        </div>
                    </div>

                    <input type="file" name="foto" id="fotoInput" accept="image/jpeg" class="d-none" />
                    <div class="camActions" id="camActions">
                        <button type="button" class="captureBtn" id="btnBukaKamera" onclick="openLiveCamera()">
                            <ion-icon name="camera" class="icon-lg"></ion-icon> Buka Kamera
                        </button>
                        <div id="camRowStreaming" class="camRow d-none">
                            <button type="button" class="captureBtn" onclick="snapPhoto()">
                                <ion-icon name="radio-button-on" class="icon-lg"></ion-icon> Ambil Foto
                            </button>
                            <button type="button" class="captureBtn secondary" onclick="cancelCamera()">Batal</button>
                        </div>
                        <button type="button" id="btnUlangi" onclick="retakePhoto()" class="captureBtn secondary d-none">
                            <ion-icon name="refresh-outline" class="icon-md"></ion-icon> Ulangi Foto
                        </button>
                    </div>

                    {{-- Tombol MULAI --}}
                    <button type="submit" id="btnSubmit" class="captureBtn primary-blue mt-4">
                        <ion-icon name="log-in-outline" class="icon-lg"></ion-icon>
                        Mulai (Absen Masuk)
                    </button>

                    <a href="https://wa.me/{{ $adminWa }}?text={{ urlencode('Halo Admin, saya ' . $displayName . ' ingin izin untuk hari ini...') }}" target="_blank" class="d-flex items-center justify-center gap-2 p-3 rounded-lg text-no-decor text-success text-md font-extrabold mt-2 wa-support-btn">
                        <ion-icon name="logo-whatsapp" class="icon-md text-success"></ion-icon>
                        Izin / Kendala (Hubungi Admin)
                    </a>
                </div>
            </form>

        @endif

    </div>{{-- #mainContent --}}

    <script >
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
            // Sembunyikan banner saat mengecek
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
                    var camTest = navigator.mediaDevices ?
                        navigator.mediaDevices.getUserMedia({
                            video: true,
                            audio: false
                        })
                        .then(function(s) {
                            s.getTracks().forEach(function(t) {
                                t.stop();
                            });
                            return true;
                        })
                        .catch(function() {
                            return false;
                        }) :
                        Promise.resolve(false);
                    var locTest = new Promise(function(resolve) {
                        if (!navigator.geolocation) return resolve(false);
                        navigator.geolocation.getCurrentPosition(function() {
                            resolve(true);
                        }, function() {
                            resolve(false);
                        }, {
                            enableHighAccuracy: true,
                            timeout: 8000,
                            maximumAge: 0
                        });
                    });
                    Promise.all([camTest, locTest]).then(function(r) {
                        if (!r[0] && !r[1]) showGateError(
                            'Izin <strong >kamera</strong> dan <strong >lokasi</strong> ditolak.<br >Keduanya wajib diaktifkan.'
                        );
                        else if (!r[0]) showGateError(
                            'Izin <strong >kamera</strong> ditolak.<br >Kamera wajib untuk foto presensi.'
                        );
                        else showGateError(
                            'Izin <strong >lokasi</strong> ditolak.<br >Lokasi wajib untuk koordinat presensi.'
                        );
                    });
                } else if (err && err.message === 'no_media_devices') {
                    showGateError('Peramban tidak mendukung kamera. Gunakan Chrome atau Safari terbaru.');
                } else {
                    showGateError(
                        'Gagal mendapatkan izin kamera/lokasi. Pastikan GPS aktif dan izin diberikan.');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            checkPermissions();

            // ── Countdown timer untuk sesi berjalan yang belum bisa pulang ──
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
                            // Refresh halaman agar tombol aktif
                            window.location.reload();
                            return;
                        }
                        cdEl.textContent = (totalSec / 60).toFixed(2);
                    }, 1000);
                }
            @endif
        });

        /* ══════════════════ MAP LEAFLET GEOFENCING VISUALIZER ══════════════════ */
        const DEFAULT_GEOFENCE_LAT = {{ config('lokasi.sekolah_lat', -7.8011945) }};
        const DEFAULT_GEOFENCE_LNG = {{ config('lokasi.sekolah_lng', 110.364917) }};
        const DEFAULT_GEOFENCE_RADIUS = {{ config('lokasi.radius_meter', 100) }};
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
        let karyawanMarker = null;
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
                    sekolahMarker.setPopupContent('<b>🏢 ' + currentTargetNama + '</b><br>Titik Lokasi Absen (Batas Maksimal: ' + currentTargetRadius + ' meter)');
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
                    m.bindPopup('<b>📍 ' + lok.nama_lokasi + '</b><br>Radius: ' + lok.radius_meter + 'm<br><small class="text-muted">Pilih di dropdown jika ingin absen di titik ini</small>');
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

            // Marker Lokasi Terpilih (Merah)
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

            // Lingkaran Toleransi Radius Geofence
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
            var hint = document.getElementById('mapHint');
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

                // Marker Karyawan (Biru)
                var blueIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });

                if (karyawanMarker) {
                    karyawanMarker.setLatLng([lat, lng]);
                } else {
                    karyawanMarker = L.marker([lat, lng], { icon: blueIcon }).addTo(leafletMap);
                }
                karyawanMarker.bindPopup('<b>Lokasi Anda Saat Ini</b><br>Jarak ke ' + currentTargetNama + ': ' + distFormatted + ' meter');

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

                // Zoom fit agar titik sekolah dan posisi terlihat bersamaan
                var bounds = L.latLngBounds([[currentTargetLat, currentTargetLng], [lat, lng]]);
                leafletMap.fitBounds(bounds, { padding: [35, 35] });

                if (badge) {
                    badge.style.display = 'block';
                    if (isWithin) {
                        geofenceCircle.setStyle({ color: '#16a34a', fillColor: '#4ade80', fillOpacity: 0.3 });
                        badge.style.background = 'rgba(22, 163, 74, 0.12)';
                        badge.style.border = '1px solid rgba(22, 163, 74, 0.35)';
                        badge.style.color = '#15803d';
                        badge.innerHTML = '<span class="d-inline-flex items-center gap-1"><ion-icon name="checkmark-circle-outline"></ion-icon> <b>Di Dalam Radius ' + currentTargetNama + '</b> (' + distFormatted + ' m — Maks: ' + currentTargetRadius + 'm)</span>';
                    } else {
                        geofenceCircle.setStyle({ color: '#dc2626', fillColor: '#f87171', fillOpacity: 0.3 });
                        badge.style.background = 'rgba(220, 38, 38, 0.12)';
                        badge.style.border = '1px solid rgba(220, 38, 38, 0.35)';
                        badge.style.color = '#dc2626';
                        badge.innerHTML = '<span class="d-inline-flex items-center gap-1"><ion-icon name="close-circle-outline"></ion-icon> <b>Di Luar Radius ' + currentTargetNama + '</b> (' + distFormatted + ' m — Maks: ' + currentTargetRadius + 'm)</span>';
                    }
                }

                // Proximity Radar Card
                if (radarCard && radarDot && radarTitle && radarSub) {
                    radarCard.style.display = 'flex';
                    var sisaJarak = Math.max(0, Math.round(dist - currentTargetRadius));

                    if (isWithin) {
                        radarDot.className = 'radarStatusDot pulse-green';
                        radarTitle.textContent = '✅ Anda Berada di Dalam Area ' + currentTargetNama + ' (' + distFormatted + ' m)';
                        radarSub.textContent = 'Koordinat GPS valid. Silakan ambil foto selfie untuk melakukan presensi.';
                        if (btnMaps) btnMaps.style.display = 'none';

                        if (lastWithinZone === false && navigator.vibrate) {
                            navigator.vibrate([100, 50, 100]);
                        }
                    } else {
                        if (dist > 200) {
                            radarDot.className = 'radarStatusDot pulse-red';
                            radarTitle.textContent = '📍 Jarak: ' + distFormatted + ' m (Kurang ' + sisaJarak + ' m untuk masuk zona)';
                            radarSub.textContent = 'Ikuti garis panduan merah pada peta menuju gerbang ' + currentTargetNama + '.';
                        } else {
                            radarDot.className = 'radarStatusDot pulse-yellow';
                            radarTitle.textContent = '🚶‍♂️ Mendekati Lokasi (' + distFormatted + ' m — Tinggal ' + sisaJarak + ' m lagi)';
                            radarSub.textContent = 'Sedikit lagi! Bergeraklah mendekati kantor agar tombol presensi aktif.';
                        }

                        if (btnMaps) {
                            btnMaps.style.display = 'inline-flex';
                            btnMaps.href = 'https://www.google.com/maps/dir/?api=1&destination=' + currentTargetLat + ',' + currentTargetLng + '&origin=' + lat + ',' + lng;
                        }
                    }
                }

                lastWithinZone = isWithin;

                if (hint) {
                    hint.textContent = lat.toFixed(5) + ', ' + lng.toFixed(5) + ' (Jarak: ' + distFormatted + 'm dari ' + currentTargetNama + ')';
                }
            }
        }

        function handleGpsSuccess(pos) {
            var isFake = false;
            var accuracy = pos.coords.accuracy || 0;

            var akurasiEls = document.querySelectorAll('input[name="lokasi_akurasi"]');
            var mockEls = document.querySelectorAll('input[name="is_mock_location"]');

            akurasiEls.forEach(function(el) { el.value = accuracy; });

            if (pos.coords.mocked === true) {
                isFake = true;
            } else if (accuracy === 0) {
                isFake = true;
            } else if (pos.coords.altitude === 0 && pos.coords.altitudeAccuracy === 0 && pos.coords.speed === 0 && pos.coords.heading === 0) {
                isFake = true;
            }

            mockEls.forEach(function(el) { el.value = isFake ? '1' : '0'; });

            var ph = document.getElementById('mapPlaceholder');
            if (isFake) {
                if (ph) {
                    ph.textContent = 'Terdeteksi penggunaan Fake GPS / Mock Location. Matikan aplikasi Fake GPS Anda!';
                    ph.style.color = '#ef4444';
                }
                alert('Peringatan: Sistem mendeteksi kemungkinan penggunaan aplikasi Fake GPS atau Mock Location. Harap matikan aplikasi tersebut untuk dapat melanjutkan presensi.');
                return;
            }

            if (ph) ph.style.color = 'var(--muted)';
            setMapFromLatLng(pos.coords.latitude, pos.coords.longitude);
        }

        function handleGpsError() {
            var ph = document.getElementById('mapPlaceholder');
            if (ph) ph.textContent = 'Gagal mengambil lokasi. Pastikan izin GPS aktif.';
        }

        function startLiveTracking() {
            if (!navigator.geolocation || watchPositionId !== null) return;
            isLiveTracking = true;
            updateLiveGpsButton();

            watchPositionId = navigator.geolocation.watchPosition(
                handleGpsSuccess,
                handleGpsError,
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 3000 }
            );
        }

        function stopLiveTracking() {
            if (watchPositionId !== null) {
                navigator.geolocation.clearWatch(watchPositionId);
                watchPositionId = null;
            }
            isLiveTracking = false;
            updateLiveGpsButton();
        }

        function toggleLiveTracking() {
            if (isLiveTracking) {
                stopLiveTracking();
            } else {
                startLiveTracking();
            }
        }

        function updateLiveGpsButton() {
            var btn = document.getElementById('btnToggleLiveGps');
            var label = document.getElementById('liveGpsLabel');
            var dot = document.getElementById('liveGpsDot');
            if (!btn || !label || !dot) return;

            if (isLiveTracking) {
                label.textContent = 'Live Track: ON';
                dot.className = 'liveDot';
            } else {
                label.textContent = 'Live Track: PAUSED';
                dot.className = 'liveDot paused';
            }
        }

        function refreshLocation(manualClick) {
            var ph = document.getElementById('mapPlaceholder');
            if (!navigator.geolocation) {
                if (ph) ph.textContent = 'Geolocation tidak didukung.';
                return;
            }
            if (ph) {
                ph.classList.remove('hidden');
                ph.textContent = 'Mencari lokasi GPS…';
            }

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    handleGpsSuccess(pos);
                    if (manualClick && !isLiveTracking) {
                        startLiveTracking();
                    }
                },
            );
        }

        /* ══════════════════ ADVANCED CAMERA CONTROLS ══════════════════ */
        let mediaStream = null;
        let currentFacingMode = 'user'; // 'user' (depan) atau 'environment' (belakang)
        let isMirrored = true;          // default mirror kamera depan
        let isGridActive = false;
        let isTorchOn = false;

        function updateCameraTransform() {
            var video = document.getElementById('videoPreview');
            if (video) {
                video.style.transform = isMirrored ? 'scaleX(-1)' : 'scaleX(1)';
            }
            var btnMirror = document.getElementById('btnToggleMirror');
            if (btnMirror) {
                if (isMirrored) {
                    btnMirror.classList.add('active');
                } else {
                    btnMirror.classList.remove('active');
                }
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
            if (gridEl) {
                gridEl.style.display = isGridActive ? 'block' : 'none';
            }
            if (btnGrid) {
                if (isGridActive) {
                    btnGrid.classList.add('active');
                } else {
                    btnGrid.classList.remove('active');
                }
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
                    if (isTorchOn) {
                        btnTorch.classList.add('active');
                    } else {
                        btnTorch.classList.remove('active');
                    }
                }
            }).catch(function(err) {
                console.log('Flash/Torch error or not supported:', err);
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
            
            // Kamera belakang secara alami tidak di-mirror
            isMirrored = (currentFacingMode === 'user');

            var btnSwitch = document.getElementById('btnToggleSwitch');
            if (btnSwitch) {
                if (currentFacingMode === 'environment') {
                    btnSwitch.classList.add('active');
                } else {
                    btnSwitch.classList.remove('active');
                }
            }

            if (mediaStream) {
                stopCameraStream();
                openLiveCamera();
            }
        }

        function openLiveCamera() {
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

            // Tangkap gambar dengan memperhatikan status mirror
            if (isMirrored) {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }

            ctx.drawImage(video, 0, 0);

            canvas.toBlob(function(blob) {
                if (!blob) {
                    alert('Gagal membuat gambar.');
                    return;
                }
                try {
                    var file = new File([blob], 'presensi-' + Date.now() + '.jpg', {
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
                    alert('Ambil foto dengan kamera terlebih dahulu (Buka kamera → Absen).');
                }
            });
        }
    </script>
@endsection
