@extends('layouts.presensi')

@php
    $roleTitle = match ($user?->role) {
        'admin' => 'Presensi Admin',
        'kepala_sekolah' => 'Presensi Kepala Sekolah',
        default => 'Presensi Karyawan',
    };
@endphp

@section('title', $roleTitle)


@section('content')
    @php
        $displayName = (string) ($user->nama_lengkap ?? ($user->name ?? 'Karyawan'));
        $initial = strtoupper(substr($displayName, 0, 1));
        $isBypassRadius = $isBypassRadius ?? in_array($user?->role, ['admin', 'kepala_sekolah'], true);
        $isBypassWaktuTunggu = $isBypassWaktuTunggu ?? in_array($user?->role, ['admin', 'kepala_sekolah'], true);

        // Hitung sisa waktu jika sesi berjalan
        $sisamenit = 0;
        $sisaDetik = 0;
        $bisaPulang = false;
        $sudahLewat2Jam = false;
        if ($activeSesi) {
            try {
                $jamMulaiDt = \Carbon\Carbon::parse($today . ' ' . $activeSesi->jam_mulai, 'Asia/Jakarta');
                $nowDt = \Carbon\Carbon::now('Asia/Jakarta');
                $diffDetik = (int) $jamMulaiDt->diffInSeconds($nowDt, false);
                $sisaDetik = (int) max(0, 3600 - $diffDetik);
                $sisamenit = (int) ceil($sisaDetik / 60);
                $bisaPulang = $diffDetik >= 3600;
                $sudahLewat2Jam = $diffDetik >= 7200;
            } catch (\Throwable) {
            }

            if ($isBypassWaktuTunggu) {
                $bisaPulang = true;
                $sisaDetik = 0;
                $sisamenit = 0;
            }
        }

        $activeLokasi = $activeSesi?->lokasiPresensi;
        $initialTargetLat = (float) ($activeLokasi?->latitude ?? config('lokasi.sekolah_lat', -7.8011945));
        $initialTargetLng = (float) ($activeLokasi?->longitude ?? config('lokasi.sekolah_lng', 110.364917));
        $initialTargetRadius = (int) ($activeLokasi?->radius_meter ?? config('lokasi.radius_meter', 100));
        $initialTargetNama = (string) ($activeLokasi?->nama_lokasi ?? config('lokasi.sekolah_nama', 'PKBM Pikat'));
        $initialTargetAlamat = (string) ($activeLokasi?->alamat ?? '');

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
        $isBypassRadius = $isBypassRadius ?? in_array($user?->role, ['admin', 'kepala_sekolah'], true);
    @endphp

    {{-- ── Standard Top Navigation Bar ── --}}
    @include('layouts.components.navigasi_atas', [
        'titleName' => $displayName,
        'subTitle' => $roleTitle . ' • ' . \Carbon\Carbon::parse($today)->translatedFormat('d M Y'),
        'dashRoute' => $dashRoute
    ])

    {{-- ── Warning Banner Izin ── --}}
    <div id="permWarning" class="permWarning">
        <div class="flex-1 min-w-0">
            <div class="permWarnTitle" id="permWarnTitle">Izin belum diberikan</div>
            <div class="permWarnDesc" id="permWarnDesc">Kamera dan lokasi diperlukan untuk melakukan presensi.</div>
        </div>
        <button onclick="checkPermissions()" class="permWarnBtn">
            Coba Lagi
        </button>
    </div>

    {{-- ══════════════════ MAIN CONTENT ══════════════════ --}}
    <div class="pagePad" id="mainContent">

        {{-- ── Banner Notifikasi Fleksibilitas Bebas Radius (Admin & Kepala Sekolah) ── --}}
        @if ($isBypassRadius && !$activeSesi)
            <div class="card mb-3 p-3 rounded-xl border-base bg-card-alt">
                <div class="d-flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-primary-subtle text-primary d-flex items-center justify-center flex-shrink-0 text-lg">
                        <ion-icon name="briefcase-outline"></ion-icon>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-dark">Akses Fleksibel (Bebas Radius) Aktif</div>
                        <div class="text-xs text-muted mt-0.5 leading-normal">
                            Sebagai {{ $user->role === 'admin' ? 'Administrator' : 'Kepala Sekolah' }}, presensi Anda tidak dibatasi oleh radius titik lokasi sekolah untuk mendukung fleksibilitas dinas luar atau rapat penting.
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── RIWAYAT SESI SELESAI HARI INI ── --}}
        @if ($completedSessions->count() > 0)
            <div class="card mb-4">
                <div class="cardTitle">
                    Riwayat Presensi Hari Ini
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
                    <div class="border-b-base py-2">
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

            @if ($bisaPulang)
                {{-- Sesi Siap Presensi Pulang (Desain Lembut, Kontras Ramah Mata, & Responsif Mobile) --}}
                <div class="card mb-3 p-3.5 border-base bg-card-alt rounded-2xl">
                    <div class="d-flex items-center justify-between gap-2 flex-wrap mb-2">
                        <div class="d-flex items-center gap-2 flex-wrap">
                            <span class="badge bg-success-light text-success font-bold text-xs px-2.5 py-1 rounded-full d-inline-flex items-center gap-1">
                                <ion-icon name="checkmark-circle-outline"></ion-icon>
                                Siap Presensi Pulang
                            </span>
                            @if ($isBypassWaktuTunggu)
                                <span class="badge bg-primary-subtle text-primary font-semibold text-2xs px-2 py-0.5 rounded-full">
                                    Akses Fleksibel Kepulangan Aktif
                                </span>
                            @endif
                        </div>
                        <div class="text-xs font-semibold text-muted">
                            Masuk: <span class="text-dark font-bold">{{ $jamMasuk }} WIB</span>
                        </div>
                    </div>
                    <div class="text-xs text-muted leading-relaxed">
                        @if ($isBypassWaktuTunggu)
                            Formulir presensi pulang terbuka dan siap dikirimkan kapan saja setelah agenda rapat atau dinas selesai.
                        @else
                            Telah memenuhi durasi minimal kerja (≥ 1 jam). Silakan verifikasi titik lokasi dan ambil foto untuk presensi pulang.
                        @endif
                    </div>
                    @if ($isBypassRadius)
                        <div class="d-flex items-center gap-1.5 mt-2.5 pt-2 border-t border-dashed border-base text-2xs text-muted">
                            <ion-icon name="shield-checkmark-outline" class="text-primary text-sm flex-shrink-0"></ion-icon>
                            <span>Mode Bebas Radius aktif untuk mendukung penugasan luar / rapat dinas.</span>
                        </div>
                    @endif
                </div>

                {{-- Warning jika sudah lewat 2 jam untuk staf biasa --}}
                @if (!$isBypassWaktuTunggu && $sudahLewat2Jam)
                    <div class="statusBanner mb-3 alert-danger-box">
                        <div class="statusIcon" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">
                            <ion-icon name="alert-circle-outline"></ion-icon>
                        </div>
                        <div>
                            <div class="statusTitle text-danger">Waktu Kerja Sudah Selesai</div>
                            <div class="statusSub">Segera lakukan presensi pulang sekarang.</div>
                        </div>
                    </div>
                @endif
            @else
                {{-- Masih dalam masa tunggu minimal 1 jam (staf biasa) --}}
                <div class="statusBanner running mb-3">
                    <div class="statusIcon warn">
                        <ion-icon name="time-outline"></ion-icon>
                    </div>
                    <div>
                        <div class="statusTitle">Presensi Sedang Berjalan</div>
                        <div class="statusSub">Masuk pukul {{ $jamMasuk }} WIB</div>
                    </div>
                </div>

                @php
                    $menit = (int) floor($sisaDetik / 60);
                    $detik = (int) ($sisaDetik % 60);
                    $sisaMenitLabel = sprintf('%02d:%02d', $menit, $detik);
                @endphp
                <div class="countdownCard mb-3">
                    <div class="countdownLabel">Bisa presensi pulang dalam</div>
                    <div class="countdownTime" id="countdown">{{ $sisaMenitLabel }}</div>
                    <div class="countdownSub">menit lagi (minimal 1 jam setelah masuk)</div>
                </div>
            @endif

            {{-- Form absen PULANG: hanya tampil jika sudah bisa pulang --}}
            @if ($bisaPulang)
            <form method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" id="presensiForm">
                @csrf
                <input type="hidden" name="mode" value="selesai">
                <input type="hidden" name="lokasi_presensi_id" value="{{ $activeSesi->lokasi_presensi_id ?? '' }}">
                <input type="hidden" name="lokasi" id="lokasi" value="">
                <input type="hidden" name="lokasi_akurasi" id="lokasi_akurasi" value="">
                <input type="hidden" name="is_mock_location" id="is_mock_location" value="0">

                <div class="card mb-4">
                    <div class="cardTitle">
                        Lokasi Presensi Pulang
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
                        Foto Presensi Pulang
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
                            Ketuk <b>Buka Kamera</b> untuk mengambil foto presensi pulang.
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

                    {{-- Tombol SELESAI --}}
                    <button type="submit" id="btnSubmit" @if (!$bisaPulang) disabled @endif class="captureBtn primary-red mt-4">
                        Kirim Presensi Pulang
                    </button>
                </div>
            </form>

            @else
                {{-- Belum waktu pulang: tampilkan info saja, tanpa form --}}
                <div class="card text-center p-4 rounded-xl">
                    <div class="text-md font-extrabold text-dark">Tombol Presensi Pulang Terbuka Otomatis</div>
                    <div class="text-sm text-muted mt-1">Formulir presensi pulang akan terbuka otomatis setelah memenuhi durasi minimal kerja.</div>
                </div>
            @endif

            {{-- ── BELUM ABSEN (belum masuk, atau sesi sudah selesai → tombol Mulai) ── --}}
        @else
            @if(isset($shiftEval))
                <div class="card mb-3 p-3 border-base bg-card-alt rounded-xl">
                    <div class="d-flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <div class="text-xs font-bold text-primary text-uppercase tracking-wider">Jadwal Shift: {{ $shiftEval['shift_nama'] }}</div>
                            <div class="text-xs text-muted">Masuk: <b>{{ $shiftEval['jam_masuk_target'] }} WIB</b> &bull; Batas: <b>{{ $shiftEval['batas_toleransi'] }} WIB</b></div>
                        </div>
                        <div>
                            @if($shiftEval['status_kehadiran'] === 'tepat_waktu')
                                <span class="badge bg-success-light text-success font-bold text-xs py-1 px-2 rounded-full">Tepat Waktu</span>
                            @elseif($shiftEval['status_kehadiran'] === 'lebih_awal')
                                <span class="badge bg-primary-light text-primary font-bold text-xs py-1 px-2 rounded-full">Lebih Awal</span>
                            @else
                                <span class="badge bg-warning-light text-warning font-bold text-xs py-1 px-2 rounded-full">Terlambat (+{{ $shiftEval['menit_keterlambatan'] }}m)</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <div class="statusBanner ready mb-4">
                <div>
                    <div class="statusTitle">Belum Melakukan Presensi</div>
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
                        Lokasi Presensi Masuk
                    </div>

                    {{-- Dropdown Pemilihan Titik Lokasi Absen Karyawan --}}
                    <div class="mb-3" id="boxPilihLokasi">
                        <label class="d-flex items-center justify-between text-sm font-semibold text-muted mb-2">
                            <span>Pilih Lokasi Kerja / Cabang <span class="text-danger">*</span></span>
                            @if ($isBypassRadius)
                                <span class="badge bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 text-xs px-2 py-0.5 rounded-full font-bold">
                                    Bebas Radius
                                </span>
                            @endif
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
                                    data-lat="{{ config('lokasi.sekolah_lat', -7.8011945) }}"
                                    data-lng="{{ config('lokasi.sekolah_lng', 110.364917) }}"
                                    data-radius="{{ config('lokasi.radius_meter', 100) }}"
                                    data-nama="{{ config('lokasi.sekolah_nama', 'PKBM Pikat') }}"
                                    data-alamat="Gedung Pusat PKBM Pikat">
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

                    <a href="https://wa.me/{{ $adminWa }}?text={{ urlencode('Halo Admin, saya ' . $displayName . ' ingin izin untuk hari ini...') }}" target="_blank" class="d-flex items-center justify-center gap-2 p-3 rounded-lg text-no-decor text-success text-md font-extrabold mt-2 wa-support-btn">
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
            if (document.getElementById('mapBox')) {
                refreshLocation(false);
                startLiveTracking();
            }
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

            if (document.getElementById('mapBox')) {
                refreshLocation(false);
                startLiveTracking();
            }

            // ── Countdown timer untuk sesi berjalan yang belum bisa pulang ──
            var cdEl = document.getElementById('countdown');
            @if ($activeSesi && !$bisaPulang)
                var sisaDetik = {{ (int) $sisaDetik }};
                if (cdEl && sisaDetik > 0) {
                    var totalSec = Math.floor(sisaDetik);
                    var cdInterval = setInterval(function() {
                        totalSec--;
                        if (totalSec <= 0) {
                            clearInterval(cdInterval);
                            cdEl.textContent = '00:00';
                            // Refresh halaman agar tombol aktif
                            window.location.reload();
                            return;
                        }
                        var m = Math.floor(totalSec / 60);
                        var s = Math.floor(totalSec % 60);
                        cdEl.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                    }, 1000);
                }
            @endif
        });

        /* ══════════════════ MAP LEAFLET GEOFENCING VISUALIZER ══════════════════ */
        const DEFAULT_GEOFENCE_LAT = {{ config('lokasi.sekolah_lat', -7.8011945) }};
        const DEFAULT_GEOFENCE_LNG = {{ config('lokasi.sekolah_lng', 110.364917) }};
        const DEFAULT_GEOFENCE_RADIUS = {{ config('lokasi.radius_meter', 100) }};
        const DEFAULT_GEOFENCE_NAMA = @json(config('lokasi.sekolah_nama', 'PKBM Pikat'));

        let currentTargetLat = {{ $initialTargetLat }};
        let currentTargetLng = {{ $initialTargetLng }};
        let currentTargetRadius = {{ $initialTargetRadius }};
        let currentTargetNama = @json($initialTargetNama);
        let currentTargetAlamat = @json($initialTargetAlamat);

        let allLokasiPoints = @json($lokasiPresensis ?? []);

        let presensiMap = null;
        let watchPositionId = null;
        let isLiveTracking = true;
        let lastUserLat = null;
        let lastUserLng = null;
        let lastUserAccuracy = null;

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
            } else {
                currentTargetLat = {{ $initialTargetLat }};
                currentTargetLng = {{ $initialTargetLng }};
                currentTargetRadius = {{ $initialTargetRadius }};
                currentTargetNama = @json($initialTargetNama);
                currentTargetAlamat = @json($initialTargetAlamat);
            }
        }

        function handleLokasiPresensiChange() {
            updateTargetFromDropdown();

            if (presensiMap) {
                presensiMap.setTarget(
                    currentTargetLat,
                    currentTargetLng,
                    currentTargetRadius,
                    currentTargetNama,
                    currentTargetAlamat,
                    allLokasiPoints
                );
            }
        }

        const IS_BYPASS_RADIUS = @json($isBypassRadius);

        function initPresensiMapInstance() {
            var mapEl = document.getElementById('leafletMap');
            if (!mapEl || presensiMap || typeof window.createPresensiMap === 'undefined') return;

            mapEl.classList.remove('d-none');
            mapEl.style.display = 'block';

            updateTargetFromDropdown();

            presensiMap = window.createPresensiMap({
                elementId: 'leafletMap',
                targetLat: currentTargetLat,
                targetLng: currentTargetLng,
                targetRadius: currentTargetRadius,
                targetNama: currentTargetNama,
                targetAlamat: currentTargetAlamat,
                lokasiList: allLokasiPoints,
                onDistanceUpdate: function(state) {
                    var hint = document.getElementById('mapHint');
                    var badge = document.getElementById('geofenceBadge');
                    var radarCard = document.getElementById('proximityRadarCard');
                    var radarDot = document.getElementById('radarStatusDot');
                    var radarTitle = document.getElementById('radarTitle');
                    var radarSub = document.getElementById('radarSub');
                    var btnMaps = document.getElementById('btnPetunjukArah');

                    if (badge) {
                        badge.style.display = 'block';
                        if (state.isWithin) {
                            badge.style.background = 'rgba(22, 163, 74, 0.12)';
                            badge.style.border = '1px solid rgba(22, 163, 74, 0.35)';
                            badge.style.color = '#15803d';
                            badge.innerHTML = '<span class="d-inline-flex items-center gap-1"><b>Di Dalam Area ' + state.targetNama + '</b> (' + Math.round(state.distance) + ' m)</span>';
                        } else if (IS_BYPASS_RADIUS) {
                            badge.style.background = 'rgba(37, 99, 235, 0.12)';
                            badge.style.border = '1px solid rgba(37, 99, 235, 0.35)';
                            badge.style.color = '#2563eb';
                            badge.innerHTML = '<span class="d-inline-flex items-center gap-1"><b>Bebas Radius Aktif</b> (' + Math.round(state.distance) + ' m dari ' + state.targetNama + ') &bull; Dinas Luar / Rapat</span>';
                        } else {
                            badge.style.background = 'rgba(220, 38, 38, 0.12)';
                            badge.style.border = '1px solid rgba(220, 38, 38, 0.35)';
                            badge.style.color = '#dc2626';
                            badge.innerHTML = '<span class="d-inline-flex items-center gap-1"><b>Di Luar Area ' + state.targetNama + '</b> (' + Math.round(state.distance) + ' m)</span>';
                        }
                    }

                    if (radarCard && radarDot && radarTitle && radarSub) {
                        radarCard.style.display = 'flex';
                        var sisaJarak = Math.max(0, Math.round(state.distance - state.targetRadius));

                        if (state.isWithin) {
                            radarDot.className = 'radarStatusDot pulse-green';
                            radarTitle.textContent = 'Posisi Anda Sesuai di ' + state.targetNama;
                            radarSub.textContent = 'Lokasi telah cocok. Silakan ambil foto dan kirim presensi.';
                            if (btnMaps) btnMaps.style.display = 'none';

                            if (state.zoneChanged && navigator.vibrate) {
                                navigator.vibrate([100, 50, 100]);
                            }
                        } else if (IS_BYPASS_RADIUS) {
                            radarDot.className = 'radarStatusDot pulse-blue';
                            radarTitle.textContent = 'Mode Bebas Radius: Jarak ' + Math.round(state.distance) + ' m dari ' + state.targetNama;
                            radarSub.textContent = 'Presensi di luar radius diizinkan khusus Admin & Kepala Sekolah untuk dinas luar / rapat.';
                            if (btnMaps) {
                                btnMaps.style.display = 'inline-flex';
                                btnMaps.href = 'https://www.google.com/maps/dir/?api=1&destination=' + state.targetLat + ',' + state.targetLng + '&origin=' + state.userLat + ',' + state.userLng;
                            }
                        } else {
                            if (state.distance > 200) {
                                radarDot.className = 'radarStatusDot pulse-red';
                                radarTitle.textContent = 'Jarak ke Lokasi: ' + Math.round(state.distance) + ' meter';
                                radarSub.textContent = 'Silakan bergerak mendekati ' + state.targetNama + ' (perlu mendekat ' + sisaJarak + ' m).';
                            } else {
                                radarDot.className = 'radarStatusDot pulse-yellow';
                                radarTitle.textContent = 'Mendekati Lokasi (tinggal ' + sisaJarak + ' meter lagi)';
                                radarSub.textContent = 'Sedikit lagi! Bergeraklah mendekat agar dapat melakukan presensi.';
                            }

                            if (btnMaps) {
                                btnMaps.style.display = 'inline-flex';
                                btnMaps.href = 'https://www.google.com/maps/dir/?api=1&destination=' + state.targetLat + ',' + state.targetLng + '&origin=' + state.userLat + ',' + state.userLng;
                            }
                        }
                    }

                    if (state.zoneChanged && window.showAppToast) {
                        if (state.isWithin) {
                            window.showAppToast({
                                type: 'success',
                                title: 'Memasuki Area ' + state.targetNama,
                                message: 'Anda berada dalam jangkauan presensi (' + Math.round(state.distance) + 'm).',
                                duration: 3500
                            });
                        } else if (IS_BYPASS_RADIUS) {
                            window.showAppToast({
                                type: 'info',
                                title: 'Mode Bebas Radius Aktif',
                                message: 'Anda berada di luar radius (' + Math.round(state.distance) + 'm), presensi tetap diizinkan untuk tugas dinas.',
                                duration: 3500
                            });
                        } else {
                            window.showAppToast({
                                type: 'warning',
                                title: 'Keluar Dari Area ' + state.targetNama,
                                message: 'Anda berada ' + Math.round(state.distance) + 'm dari titik pusat.',
                                duration: 3500
                            });
                        }
                    }

                    if (hint) {
                        hint.textContent = state.userLat.toFixed(5) + ', ' + state.userLng.toFixed(5) + ' (Jarak: ' + Math.round(state.distance) + 'm dari ' + state.targetNama + ')';
                    }
                }
            });
        }

        function setMapFromLatLng(lat, lng, accuracy = null) {
            lastUserLat = lat;
            lastUserLng = lng;
            lastUserAccuracy = accuracy;

            var lokasiEl = document.getElementById('lokasi');
            var ph = document.getElementById('mapPlaceholder');
            var mapEl = document.getElementById('leafletMap');

            if (lokasiEl) {
                lokasiEl.value = lat.toFixed(6) + ',' + lng.toFixed(6);
            }

            if (mapEl) {
                mapEl.classList.remove('d-none');
                mapEl.style.display = 'block';
            }

            initPresensiMapInstance();

            if (ph) {
                ph.classList.add('hidden');
                ph.style.display = 'none';
            }

            if (presensiMap) {
                presensiMap.updateUserLocation(lat, lng, accuracy);
                if (typeof presensiMap.invalidateSize === 'function') {
                    presensiMap.invalidateSize();
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
                    ph.textContent = 'Lokasi perangkat tidak valid atau terdeteksi aplikasi pengubah lokasi.';
                    ph.style.color = '#ef4444';
                }
                alert('Peringatan: Lokasi perangkat tidak valid atau terdeteksi aplikasi pengubah lokasi. Harap gunakan lokasi asli Anda.');
                return;
            }

            if (ph) ph.style.color = 'var(--muted)';
            setMapFromLatLng(pos.coords.latitude, pos.coords.longitude, accuracy);
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
                label.textContent = 'Pantau Lokasi: Aktif';
                dot.className = 'liveDot';
            } else {
                label.textContent = 'Pantau Lokasi: Dijeda';
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
                ph.style.display = 'flex';
                ph.textContent = 'Mencari lokasi GPS…';
            }

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    handleGpsSuccess(pos);
                    if (manualClick && !isLiveTracking) {
                        startLiveTracking();
                    }
                },
                handleGpsError,
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
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
