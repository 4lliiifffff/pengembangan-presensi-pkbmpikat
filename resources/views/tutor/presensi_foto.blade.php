@extends('layouts.presensi')

@section('title', 'Presensi Tutor')

@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


    @php
        $user = auth()->user();
        $displayName = (string) ($user->nama_lengkap ?? ($user->name ?? 'Tutor'));
        $initial = strtoupper(substr($displayName, 0, 1));
        $selectedSiswa = (int) old('siswa_id', request('siswa_id', 0));

        // Tentukan state sesi aktif hari ini
        // Sesi AKTIF = sudah absen masuk (foto_mulai ada) tapi BELUM absen pulang (foto_selesai kosong)
        $activeSesi = $globalActiveSesi; // dari controller: foto_mulai ada, foto_selesai null

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
        $adminWa = config('app.admin_wa', '6281234567890');
    @endphp

    {{-- ── Head Bar ── --}}
    <div class="headRow">
        <div class="headLeft">
            <div class="headAvatar">
                @if ($user->foto)
                    <img src="{{ str_starts_with($user->foto, 'uploads/') ? asset($user->foto) : asset('storage/' . $user->foto) }}" alt="Avatar"
                        style="width:100%;height:100%;object-fit:cover;" />
                @else
                    {{ $initial }}
                @endif
            </div>
            <div>
                <div class="headName">{{ $displayName }}</div>
                <div class="headSub">Presensi • {{ \Carbon\Carbon::parse($today)->translatedFormat('d M Y') }}</div>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <button class="theme-btn" type="button" aria-label="Tema" id="themeToggleBtn">
                <ion-icon name="moon-outline" style="font-size:20px;" id="themeToggleIcon"></ion-icon>
            </button>
            <a href="{{ route('tutor.dashboard') }}" class="badge" style="text-decoration:none;">
                <ion-icon name="grid-outline"></ion-icon>
                Dashboard
            </a>
        </div>
    </div>

    {{-- ── Warning Banner Izin ── --}}
    <div id="permWarning" style="display:none; margin:10px 14px 0; padding:12px 14px; border-radius:14px;
        background:rgba(245,158,11,0.12); border:1px solid rgba(245,158,11,0.35);
        display:none; align-items:center; gap:10px; flex-wrap:wrap;">
        <span style="font-size:20px;">⚠️</span>
        <div style="flex:1; min-width:0;">
            <div style="font-size:12px; font-weight:900; color:#92400e;" id="permWarnTitle">Izin belum diberikan</div>
            <div style="font-size:11px; color:#b45309; margin-top:2px;" id="permWarnDesc">Kamera dan lokasi diperlukan untuk absen.</div>
        </div>
        <button onclick="checkPermissions()" style="border:none; background:#f59e0b; color:#fff; border-radius:10px;
            padding:7px 12px; font-size:11px; font-weight:900; cursor:pointer; white-space:nowrap;">
            🔄 Coba Lagi
        </button>
    </div>

    {{-- ══════════════════ MAIN CONTENT ══════════════════ --}}
    <div class="pagePad" id="mainContent">


        {{-- ── RIWAYAT SESI SELESAI HARI INI ── --}}
        @if ($completedSessions->count() > 0)
            <div class="card" style="margin-bottom:14px;">
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
                            $durLabel = floor($durMin / 60) . 'j ' . $durMin % 60 . 'm';
                        } catch (\Throwable) {
                            $durLabel = '-';
                        }
                    @endphp
                    <div style="padding:8px 0; border-bottom:1px solid #f1f5f9;">
                        <div style="font-size:12px; font-weight:900; color:#0f172a;">
                            {{ $sesi->siswa->nama_siswa ?? 'Siswa' }}</div>
                        <div style="font-size:11px; color:#64748b;">{{ $jm }} - {{ $js }}
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
                <div>
                    <div class="statusTitle">Sesi Sedang Berjalan</div>
                    <div class="statusSub">Masuk pukul {{ $jamMasuk }} —
                        {{ $activeSessions->pluck('siswa.nama_siswa')->join(', ') }}</div>
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
                <div class="statusBanner ready" style="margin-bottom:14px;">
                    <div class="statusIcon blue">
                        <ion-icon name="checkmark-circle-outline"></ion-icon>
                    </div>
                    <div>
                        <div class="statusTitle">Siap Absen Pulang</div>
                        <div class="statusSub">Sudah lebih dari 1 jam sejak masuk</div>
                    </div>
                </div>
            @endif

            {{-- Warning: sudah lebih dari 2 jam --}}
            @if ($sudahLewat2Jam)
                <div class="statusBanner" style="background:rgba(220,38,38,0.10);border:1px solid rgba(220,38,38,0.25);margin-bottom:14px;">
                    <div class="statusIcon" style="background:rgba(220,38,38,0.15);color:#dc2626;">⚠️</div>
                    <div>
                        <div class="statusTitle" style="color:#dc2626;">Sudah Lewat 2 Jam!</div>
                        <div class="statusSub">Segera lakukan absen pulang sekarang.</div>
                    </div>
                </div>
            @endif

            {{-- Form absen PULANG: hanya tampil jika sudah bisa pulang --}}
            @if ($bisaPulang)
            <form method="POST" action="{{ route('tutor.presensi.store') }}" enctype="multipart/form-data"
                id="presensiForm">
                @csrf
                <input type="hidden" name="mode" value="selesai">
                @foreach($activeSessions as $sesi)
                <input type="hidden" name="siswa_id[]" value="{{ $sesi->siswa_id }}">
                @endforeach
                <input type="hidden" name="lokasi" id="lokasi" value="">

                <div class="card">
                    <div class="cardTitle">
                        <ion-icon name="location-outline"></ion-icon>Lokasi Presensi Pulang & Radius Geofence
                    </div>
                    <div class="mapBox" id="mapBox" style="position:relative;">
                        <div class="mapPlaceholder" id="mapPlaceholder">Memuat peta & lokasi GPS…</div>
                        <div id="leafletMap" style="width:100%; height:260px; border-radius:14px; display:none; z-index:1;"></div>
                    </div>
                    <div id="geofenceBadge" style="display:none; margin:10px 0 4px; padding:10px 14px; border-radius:12px; font-size:12px; font-weight:800;"></div>
                    <div class="mapCoordHint" id="mapHint">Pastikan GPS aktif. Radius toleransi: {{ config('lokasi.radius_meter', 100) }} meter.</div>
                    <div class="mapToolbar">
                        <button type="button" onclick="refreshLocation()">Perbarui lokasi & peta</button>
                    </div>
                </div>


                <div class="card">
                    <div class="cardTitle">
                        <ion-icon name="camera-outline"></ion-icon>Foto Presensi Pulang
                    </div>
                    <div class="photoFrame">
                        <video id="videoPreview" playsinline muted></video>
                        <img id="previewImg" alt="Preview foto" />
                        <div class="photoPlaceholder" id="placeholder">
                            Kamera langsung.<br>Tap <b>Buka kamera</b>, lalu <b>Selesai</b>.
                        </div>
                    </div>
                    <input type="file" name="foto" id="fotoInput" accept="image/jpeg" style="display:none;" />
                    <div class="camActions" id="camActions">
                        <button type="button" class="captureBtn" id="btnBukaKamera" onclick="openLiveCamera()">
                            <ion-icon name="camera" style="font-size:22px;"></ion-icon> Buka kamera
                        </button>
                        <div class="camRow" id="camRowStreaming" style="display:none;">
                            <button type="button" class="captureBtn" onclick="snapPhoto()">
                                <ion-icon name="radio-button-on" style="font-size:22px;"></ion-icon> Foto
                            </button>
                            <button type="button" class="captureBtn secondary" onclick="cancelCamera()">Batal</button>
                        </div>
                        <button type="button" class="captureBtn secondary" id="btnUlangi" style="display:none;"
                            onclick="retakePhoto()">
                            <ion-icon name="refresh-outline" style="font-size:20px;"></ion-icon> Ulangi foto
                        </button>
                    </div>

                    {{-- Tombol SELESAI: disabled jika belum 1 jam --}}
                    <button type="submit" class="captureBtn primary-red" id="btnSubmit"
                        @if (!$bisaPulang) disabled @endif style="margin-top:14px;">
                        <ion-icon name="log-out-outline" style="font-size:20px;"></ion-icon>
                        Selesai (Absen Pulang)
                    </button>

                    @if (!$bisaPulang)
                        <div class="hint" style="color:#d97706;">
                            ⏳ Tombol aktif setelah {{ number_format($sisaDetik / 60, 0) }} menit lagi
                            (minimal 1 jam setelah masuk).
                        </div>
                    @else
                        <div class="hint">
                            Foto langsung dari kamera + lokasi GPS wajib diisi.
                        </div>
                    @endif
                </div>
            </form>

            @else
                {{-- Belum 1 jam: tampilkan info saja, tanpa form --}}
                <div class="card" style="text-align:center; padding:20px;">
                    <div style="font-size:32px; margin-bottom:8px;">⏳</div>
                    <div style="font-size:13px; font-weight:900; color:#d97706;">Form absen pulang muncul setelah 1 jam</div>
                    <div style="font-size:11px; color:#64748b; margin-top:4px;">Tersisa {{ number_format($sisaDetik / 60, 0) }} menit lagi</div>
                </div>
            @endif

            {{-- ── BELUM ABSEN (belum masuk, atau sesi sudah selesai → tombol Mulai) ── --}}
        @else
            <div class="statusBanner ready" style="margin-bottom:14px;">
                <div class="statusIcon blue">
                    <ion-icon name="log-in-outline"></ion-icon>
                </div>
                <div>
                    <div class="statusTitle">Belum Absen Masuk</div>
                    <div class="statusSub">Silakan absen masuk terlebih dahulu</div>
                </div>
            </div>

            {{-- Form absen MASUK (tombol "Mulai") --}}
            <form method="POST" action="{{ route('tutor.presensi.store') }}" enctype="multipart/form-data"
                id="presensiForm">
                @csrf
                <input type="hidden" name="mode" value="mulai">
                <input type="hidden" name="lokasi" id="lokasi" value="">

                <div style="margin-bottom:12px;">
                    <div style="font-size:12px; font-weight:900; margin-bottom:6px; color:var(--text);">Moda Pembelajaran <span style="color:#ef4444;">*</span></div>
                    <select name="moda_pembelajaran" id="selectModa" class="input" style="width:100%; border-radius:12px; padding:10px; font-size:13px; font-weight:600; background:var(--card); color:var(--text); border:1px solid var(--border);" onchange="toggleModaDaring(this.value)">
                        <option value="sekolah">🏢 Sekolah (Tatap Muka di Gedung PKBM Pikat)</option>
                        <option value="kunjungan_rumah">🏡 Kunjungan Rumah (Home Visit / Les Privat)</option>
                        <option value="online">💻 Pembelajaran Online (Daring via Zoom/GMeet/WA)</option>
                    </select>
                </div>

                <div style="margin-bottom:12px; display:none;" id="boxLinkDaring">
                    <div style="font-size:12px; font-weight:900; margin-bottom:6px; color:#0284c7;">Link Ruang Pertemuan Online (Opsional)</div>
                    <input type="url" name="link_daring" class="input" placeholder="https://meet.google.com/xxx-xxxx-xxx atau Zoom Link" style="width:100%; border-radius:12px; padding:10px; font-size:13px; background:var(--card); color:var(--text); border:1px solid #0284c7;">
                </div>

                <div style="margin-bottom:12px;" id="siswaCheckboxes">
                    <div style="font-size:12px; font-weight:900; margin-bottom:8px; color:var(--text);">Pilih Siswa (Bisa lebih dari satu)</div>
                    <div style="max-height: 180px; overflow-y: auto; background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 4px;">
                        @foreach ($siswas as $siswa)
                            @php
                                $p = collect($presensiToday)->get($siswa->id);
                                $badge = '';
                                if ($p?->foto_mulai && !$p?->foto_selesai) {
                                    $badge = ' <span style="color:#d97706; font-size:10px;">(⏳ Berjalan)</span>';
                                }
                                $isChecked = in_array($siswa->id, (array) old('siswa_id', [])) ? 'checked' : '';
                            @endphp
                            <label style="display:flex; align-items:center; gap:10px; padding:10px 8px; border-bottom:1px solid rgba(226, 232, 240, .5); cursor:pointer;">
                                <input type="checkbox" name="siswa_id[]" class="siswa-checkbox" value="{{ $siswa->id }}" {{ $isChecked }} style="width:16px; height:16px; accent-color:#1f3b8a;">
                                <span style="font-size:13px; font-weight:800; color:var(--text);">{{ $siswa->nama_siswa }} {!! $badge !!}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="card">
                    <div class="cardTitle">
                        <ion-icon name="location-outline"></ion-icon>Lokasi Presensi Masuk & Radius Geofence
                    </div>
                    <div class="mapBox" id="mapBox" style="position:relative;">
                        <div class="mapPlaceholder" id="mapPlaceholder">Memuat peta & lokasi GPS…</div>
                        <div id="leafletMap" style="width:100%; height:260px; border-radius:14px; display:none; z-index:1;"></div>
                    </div>
                    <div id="geofenceBadge" style="display:none; margin:10px 0 4px; padding:10px 14px; border-radius:12px; font-size:12px; font-weight:800;"></div>
                    <div class="mapCoordHint" id="mapHint">Pastikan GPS aktif. Radius toleransi: {{ config('lokasi.radius_meter', 100) }} meter.</div>
                    <div class="mapToolbar">
                        <button type="button" onclick="refreshLocation()">Perbarui lokasi & peta</button>
                    </div>
                </div>


                <div class="card">
                    <div class="cardTitle">
                        <ion-icon name="camera-outline"></ion-icon>Foto Presensi Masuk
                    </div>
                    <div class="photoFrame">
                        <video id="videoPreview" playsinline muted></video>
                        <img id="previewImg" alt="Preview foto" />
                        <div class="photoPlaceholder" id="placeholder">
                            Kamera langsung.<br>Tap <b>Buka kamera</b>, lalu <b>Absen</b>.
                        </div>
                    </div>
                    <input type="file" name="foto" id="fotoInput" accept="image/jpeg" style="display:none;" />
                    <div class="camActions" id="camActions">
                        <button type="button" class="captureBtn" id="btnBukaKamera" onclick="openLiveCamera()">
                            <ion-icon name="camera" style="font-size:22px;"></ion-icon> Buka kamera
                        </button>
                        <div class="camRow" id="camRowStreaming" style="display:none;">
                            <button type="button" class="captureBtn" onclick="snapPhoto()">
                                <ion-icon name="radio-button-on" style="font-size:22px;"></ion-icon> Foto
                            </button>
                            <button type="button" class="captureBtn secondary" onclick="cancelCamera()">Batal</button>
                        </div>
                        <button type="button" class="captureBtn secondary" id="btnUlangi" style="display:none;"
                            onclick="retakePhoto()">
                            <ion-icon name="refresh-outline" style="font-size:20px;"></ion-icon> Ulangi foto
                        </button>
                    </div>

                    {{-- Tombol MULAI --}}
                    <button type="submit" class="captureBtn primary-blue" id="btnSubmit" style="margin-top:14px;">
                        <ion-icon name="log-in-outline" style="font-size:20px;"></ion-icon>
                        Mulai (Absen Masuk)
                    </button>

                    <a href="https://wa.me/{{ $adminWa }}?text={{ urlencode('Halo Admin, saya ' . $displayName . ' ingin izin untuk hari ini...') }}"
                       target="_blank"
                       style="display:flex;align-items:center;gap:8px;padding:12px 14px;border-radius:14px;background:rgba(37,211,102,0.10);border:1px solid rgba(37,211,102,0.3);text-decoration:none;color:#15803d;font-size:13px;font-weight:700;margin-top:8px;">
                        <ion-icon name="logo-whatsapp" style="font-size:20px;color:#25d366;"></ion-icon>
                        Izin (Hubungi Admin)
                    </a>

                    <div class="hint">
                        Foto langsung dari kamera + lokasi GPS wajib diisi.<br>
                        <strong>Pulang</strong> bisa dilakukan min. 1 jam setelah masuk.
                    </div>
                </div>
            </form>

        @endif

    </div>{{-- #mainContent --}}

    <script>
        function showGateError(msg) {
            // Sembunyikan loading
            document.getElementById('permWarning').style.display = 'flex';
            document.getElementById('permWarnDesc').innerHTML = msg;
            
            // Disable tombol submit supaya tidak bisa absen tanpa izin
            var btnSubmit = document.getElementById('btnSubmit');
            if(btnSubmit) btnSubmit.disabled = true;

            // Tampilkan alert JS native. Saat diklik OK, redirect ke dashboard
            alert("Akses Ditolak\n\n" + msg.replace(/<[^>]+>/g, '')); // hapus tag html untuk alert
            window.location.href = '{{ route('tutor.dashboard') }}';
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
                            'Izin <strong>kamera</strong> dan <strong>lokasi</strong> ditolak.<br>Keduanya wajib diaktifkan.'
                        );
                        else if (!r[0]) showGateError(
                            'Izin <strong>kamera</strong> ditolak.<br>Kamera wajib untuk foto presensi.'
                        );
                        else showGateError(
                            'Izin <strong>lokasi</strong> ditolak.<br>Lokasi wajib untuk koordinat presensi.'
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
        const GEOFENCE_LAT = {{ config('lokasi.sekolah_lat', -7.8011945) }};
        const GEOFENCE_LNG = {{ config('lokasi.sekolah_lng', 110.364917) }};
        const GEOFENCE_RADIUS = {{ config('lokasi.radius_meter', 100) }};
        const GEOFENCE_NAMA = @json(config('lokasi.sekolah_nama', 'PKBM Pikat'));

        let leafletMap = null;
        let geofenceCircle = null;
        let sekolahMarker = null;
        let tutorMarker = null;

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

            // Marker Sekolah (Merah)
            var redIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            sekolahMarker = L.marker([GEOFENCE_LAT, GEOFENCE_LNG], { icon: redIcon }).addTo(leafletMap);
            sekolahMarker.bindPopup('<b>🏢 ' + GEOFENCE_NAMA + '</b><br>Titik Pusat Geofence Radius (' + GEOFENCE_RADIUS + ' meter)');

            // Lingkaran Toleransi Radius Geofence
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

                // Marker Tutor (Biru)
                var blueIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });

                if (tutorMarker) {
                    tutorMarker.setLatLng([lat, lng]);
                } else {
                    tutorMarker = L.marker([lat, lng], { icon: blueIcon }).addTo(leafletMap);
                }
                tutorMarker.bindPopup('<b>📍 Lokasi Anda Saat Ini</b><br>Jarak ke ' + GEOFENCE_NAMA + ': ' + distFormatted + ' meter');

                // Zoom fit agar titik sekolah dan posisi tutor terlihat bersamaan
                var bounds = L.latLngBounds([[GEOFENCE_LAT, GEOFENCE_LNG], [lat, lng]]);
                leafletMap.fitBounds(bounds, { padding: [35, 35] });

                // Update status badge & warna lingkaran radius
                var selectModa = document.getElementById('selectModa');
                var isSekolahModa = !selectModa || selectModa.value === 'sekolah';

                if (badge) {
                    badge.style.display = 'block';
                    if (dist <= GEOFENCE_RADIUS) {
                        geofenceCircle.setStyle({ color: '#16a34a', fillColor: '#4ade80', fillOpacity: 0.3 });
                        badge.style.background = 'rgba(22, 163, 74, 0.12)';
                        badge.style.border = '1px solid rgba(22, 163, 74, 0.35)';
                        badge.style.color = '#15803d';
                        badge.innerHTML = '🟢 <b>Di Dalam Radius Sekolah</b> (' + distFormatted + ' m dari ' + GEOFENCE_NAMA + ' — Maks: ' + GEOFENCE_RADIUS + 'm)';
                    } else if (isSekolahModa) {
                        geofenceCircle.setStyle({ color: '#dc2626', fillColor: '#f87171', fillOpacity: 0.3 });
                        badge.style.background = 'rgba(220, 38, 38, 0.12)';
                        badge.style.border = '1px solid rgba(220, 38, 38, 0.35)';
                        badge.style.color = '#dc2626';
                        badge.innerHTML = '🔴 <b>Di Luar Radius Sekolah</b> (' + distFormatted + ' m dari ' + GEOFENCE_NAMA + ' — Maks: ' + GEOFENCE_RADIUS + 'm)';
                    } else {
                        geofenceCircle.setStyle({ color: '#0284c7', fillColor: '#38bdf8', fillOpacity: 0.2 });
                        badge.style.background = 'rgba(2, 132, 199, 0.12)';
                        badge.style.border = '1px solid rgba(2, 132, 199, 0.35)';
                        badge.style.color = '#0369a1';
                        badge.innerHTML = 'ℹ️ <b>Bypass Radius (Moda Non-Sekolah)</b> — Jarak dari sekolah: ' + distFormatted + ' m';
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
                ph.textContent = 'Geolocation tidak didukung.';
                return;
            }
            ph.classList.remove('hidden');
            ph.textContent = 'Mencari lokasi GPS…';
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    // Deteksi heuristik Fake GPS
                    var isFake = false;
                    if (pos.coords.mocked === true) {
                        isFake = true;
                    } else if (pos.coords.altitude === 0 && pos.coords.altitudeAccuracy === 0 && pos.coords.speed === 0 && pos.coords.heading === 0) {
                        isFake = true;
                    }
                    
                    if (isFake) {
                        ph.textContent = 'Terdeteksi penggunaan Fake GPS. Matikan aplikasi Fake GPS Anda!';
                        ph.style.color = '#ef4444';
                        alert('Peringatan: Sistem mendeteksi kemungkinan penggunaan aplikasi Fake GPS atau Mock Location. Harap matikan aplikasi tersebut untuk dapat melanjutkan presensi.');
                        return;
                    }

                    ph.style.color = 'var(--muted)';
                    setMapFromLatLng(pos.coords.latitude, pos.coords.longitude);
                },
                function() {
                    ph.textContent = 'Gagal mengambil lokasi.';
                }, {
                    enableHighAccuracy: true,
                    timeout: 12000,
                    maximumAge: 0
                }
            );
        }


        /* ══════════════════ CAMERA ══════════════════ */
        let mediaStream = null;

        function openLiveCamera() {
            var checkboxes = document.querySelectorAll('.siswa-checkbox');
            if (checkboxes.length > 0) {
                var isChecked = Array.from(checkboxes).some(cb => cb.checked);
                if (!isChecked) {
                    alert('Pilih siswa terlebih dahulu.');
                    return;
                }
            }

            if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
                alert('Peramban tidak mendukung kamera langsung. Gunakan Chrome/Safari terbaru.');
                return;
            }
            var video = document.getElementById('videoPreview');
            var tryCamera = function() {
                return navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: {
                                ideal: 'user'
                            },
                            width: {
                                ideal: 1280
                            },
                            height: {
                                ideal: 720
                            }
                        },
                        audio: false
                    })
                    .catch(function() {
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
                document.getElementById('placeholder').classList.add('hidden');
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
            canvas.getContext('2d').drawImage(video, 0, 0);
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

                // Aktifkan tombol submit pulang jika sudah bisa pulang
                var submitBtn = document.getElementById('btnSubmit');
                if (submitBtn && submitBtn.disabled) {
                    // jangan aktifkan — pulang belum boleh
                }
            }, 'image/jpeg', 0.88);
        }

        function toggleModaDaring(val) {
            var box = document.getElementById('boxLinkDaring');
            if (box) {
                box.style.display = (val === 'online') ? 'block' : 'none';
            }
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
