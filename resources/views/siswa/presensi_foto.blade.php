@extends('layouts.presensi')

@section('title', 'Presensi Siswa')


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
                $isLate = $todayPresensi->isTerlambat();
            @endphp

            <div class="statusBanner {{ $isLate ? 'running' : 'done' }} mb-4">
                <div class="statusIcon {{ $isLate ? 'warn' : 'green' }}">
                    <ion-icon name="{{ $isLate ? 'alert-circle' : 'checkmark-circle' }}"></ion-icon>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="statusTitle {{ $isLate ? 'text-warning' : 'text-success' }}">
                        {{ $isLate ? 'Presensi Masuk Tercatat Terlambat' : 'Presensi Masuk Hari Ini Berhasil' }}
                    </div>
                    <div class="statusSub">
                        Kehadiran Anda berhasil diverifikasi pada pukul <strong>{{ $jamMasuk }} WIB</strong>
                        @if($isLate)
                            <span class="text-warning font-semibold">(Terlambat {{ $todayPresensi->menit_keterlambatan }} menit dari jadwal KBM)</span>
                        @else
                            <span class="text-success font-semibold">(Tepat Waktu)</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Kartu Bukti Kehadiran Siswa --}}
            <div class="data-mobile-card mb-4">
                <div class="dmc-header">
                    <div class="d-flex items-center gap-2">
                        <div class="avatar-sm rounded-full bg-primary-light text-primary d-flex align-items-center justify-content-center font-bold" style="width: 32px; height: 32px; font-size: 0.9rem;">
                            <ion-icon name="shield-checkmark-outline"></ion-icon>
                        </div>
                        <div>
                            <h4 class="dmc-title">Bukti Presensi Mandiri</h4>
                            <div class="dmc-subtitle">{{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}</div>
                        </div>
                    </div>
                    <div>
                        @if($isLate)
                            <span class="app-badge badge-layanan-dl">Terlambat (+{{ $todayPresensi->menit_keterlambatan }} mnt)</span>
                        @elseif($todayPresensi->status_kehadiran === 'lebih_awal')
                            <span class="app-badge badge-layanan-komunitas">Lebih Awal</span>
                        @else
                            <span class="app-badge badge-status-aktif">Hadir (Tepat Waktu)</span>
                        @endif
                    </div>
                </div>

                {{-- User Profile & Selfie Snapshot Card --}}
                <div class="d-flex items-center gap-3 p-3 rounded-xl mb-3" style="background: var(--card-alt, #f8fafc); border: 1px solid var(--border, #f1f5f9);">
                    <div class="pos-relative flex-shrink-0">
                        @if ($todayPresensi->foto_masuk_url)
                            <img src="{{ $todayPresensi->foto_masuk_url }}" 
                                 onclick="openBuktiPhotoModal('{{ $todayPresensi->foto_masuk_url }}', 'Foto Presensi: {{ $displayName }}')"
                                 alt="Foto Presensi Masuk" 
                                 class="rounded-xl object-cover cursor-pointer shadow-sm" 
                                 style="width: 72px; height: 72px; object-fit: cover; border: 2px solid var(--card, #fff);" 
                                 title="Klik untuk memperbesar">
                            <span class="pos-absolute bottom-0 right-0 bg-success text-white rounded-full p-0.5 d-flex align-items-center justify-content-center shadow" style="width: 20px; height: 20px; font-size: 11px; transform: translate(25%, 25%);">
                                <ion-icon name="checkmark-outline"></ion-icon>
                            </span>
                        @else
                            <div class="avatar-lg bg-success-light text-success font-extrabold rounded-xl d-flex align-items-center justify-content-center shadow-sm" style="width: 72px; height: 72px; font-size: 1.8rem;">
                                ✓
                            </div>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-extrabold text-base text-dark mb-0.5 truncate">{{ $displayName }}</h4>
                        <div class="text-xs text-muted font-semibold mb-1">
                            {{ $siswa?->kelas?->nama_kelas ?? 'Kelas Siswa' }} &bull; No. Absen: <strong>{{ $siswa?->no_absen ?? '—' }}</strong>
                        </div>
                        <div class="text-xs text-muted">
                            NISN/NIK: <span class="font-mono font-semibold text-dark">{{ $siswa?->nisn ?? ($user->nik ?? '—') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Detail Grid Informasi Presensi --}}
                <div class="dmc-grid">
                    <div class="dmc-field">
                        <div class="dmc-label">Jam Kedatangan</div>
                        <div class="dmc-value text-primary font-extrabold">
                            {{ $jamMasuk }} WIB
                        </div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Status Kehadiran</div>
                        <div class="dmc-value">
                            @if($isLate)
                                <span class="text-warning font-bold">Terlambat (+{{ $todayPresensi->menit_keterlambatan }} mnt)</span>
                            @elseif($todayPresensi->status_kehadiran === 'lebih_awal')
                                <span class="text-info font-bold">Lebih Awal</span>
                            @else
                                <span class="text-success font-bold">Tepat Waktu</span>
                            @endif
                        </div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Titik Lokasi Belajar</div>
                        <div class="dmc-value" title="{{ $lokasiNama }}">
                            {{ \Illuminate\Support\Str::limit($lokasiNama, 25) }}
                        </div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Integritas Lokasi GPS</div>
                        <div class="dmc-value text-muted font-semibold text-xs">
                            @if($todayPresensi->lokasi_akurasi)
                                &plusmn;{{ round($todayPresensi->lokasi_akurasi) }}m (Sinyal Valid)
                            @else
                                Valid (Radius Sekolah)
                            @endif
                        </div>
                    </div>

                    @if($todaySesi)
                        <div class="dmc-field full pt-2 mt-1 border-t-base">
                            <div class="dmc-label">Sesi KBM Terkait Hari Ini</div>
                            <div class="d-flex items-center justify-between flex-wrap gap-1 mt-0.5">
                                <span class="font-bold text-dark text-xs">
                                    {{ $todaySesi->kategoriTutorial->nama_kategori ?? 'Tutorial KBM' }}
                                    ({{ $todaySesi->jam_masuk_formatted }} - {{ $todaySesi->jam_pulang_formatted }} WIB)
                                </span>
                                <span class="text-xs text-muted font-semibold">
                                    Tutor: <strong>{{ $todaySesi->tutor->nama_lengkap ?? 'Tutor Pembimbing' }}</strong>
                                </span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="dmc-footer">
                    <div class="dmc-actions flex-wrap gap-2">
                        <a href="{{ route('siswa.dashboard') }}" class="profileBtnPrimary text-xs py-2 px-3 flex-1 justify-center">
                            <ion-icon name="home-outline"></ion-icon> Dashboard
                        </a>
                        <a href="{{ route('siswa.jadwal') }}" class="profileBtnSecondary text-xs py-2 px-3 flex-1 justify-center">
                            <ion-icon name="calendar-outline"></ion-icon> Jadwal
                        </a>
                        <a href="{{ route('siswa.riwayat') }}" class="profileBtnSecondary text-xs py-2 px-3 flex-1 justify-center">
                            <ion-icon name="time-outline"></ion-icon> Riwayat
                        </a>
                    </div>
                </div>
            </div>

        {{-- ── TIME-GATED: TIDAK BISA ABSEN (BELUM WAKTUNYA ATAU TIDAK ADA JADWAL) ── --}}
        @elseif (! $canCheckIn)
            @if ($gatingReason === 'no_schedule')
                <div class="statusBanner mb-4" style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.22);">
                    <div class="statusIcon warn" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">
                        <ion-icon name="calendar-outline"></ion-icon>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="statusTitle" style="color: #b91c1c;">Tidak Ada Jadwal Belajar Hari Ini</div>
                        <div class="statusSub" style="color: #64748b;">Presensi mandiri hanya dapat dilakukan saat jadwal KBM aktif.</div>
                    </div>
                </div>

                <div class="data-mobile-card mb-4 text-center p-4">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="avatar-lg bg-danger-light text-danger rounded-2xl d-flex align-items-center justify-content-center" style="width: 72px; height: 72px; font-size: 2.2rem;">
                            <ion-icon name="calendar-outline"></ion-icon>
                        </div>
                    </div>
                    <h3 class="font-extrabold text-base text-dark mb-1">Hari Ini Tidak Ada KBM</h3>
                    <p class="text-xs text-muted mb-4 max-w-sm mx-auto" style="line-height: 1.5;">
                        Anda tidak memiliki jadwal sesi belajar yang aktif untuk hari ini (<strong>{{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}</strong>). Silakan periksa kalender belajar untuk melihat agenda mingguan Anda.
                    </p>

                    <div class="dmc-footer pt-3 border-t-base">
                        <div class="dmc-actions flex-wrap gap-2">
                            <a href="{{ route('siswa.jadwal') }}" class="profileBtnPrimary text-xs py-2 px-3 flex-1 justify-center">
                                <ion-icon name="calendar-outline"></ion-icon> Lihat Jadwal Mingguan
                            </a>
                            <a href="{{ route('siswa.dashboard') }}" class="profileBtnSecondary text-xs py-2 px-3 flex-1 justify-center">
                                <ion-icon name="home-outline"></ion-icon> Dashboard
                            </a>
                        </div>
                    </div>
                </div>

            @elseif ($gatingReason === 'too_early')
                <div class="statusBanner running mb-4">
                    <div class="statusIcon warn">
                        <ion-icon name="lock-closed-outline"></ion-icon>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="statusTitle text-warning">Presensi Belum Dibuka (Terkunci)</div>
                        <div class="statusSub">Absensi mandiri dibuka mulai 30 menit sebelum sesi KBM dimulai.</div>
                    </div>
                </div>

                <div class="data-mobile-card mb-4 text-center p-4">
                    <div class="d-flex justify-content-center mb-2">
                        <div class="avatar-lg bg-warning-light text-warning rounded-2xl d-flex align-items-center justify-content-center" style="width: 68px; height: 68px; font-size: 2rem;">
                            <ion-icon name="time-outline"></ion-icon>
                        </div>
                    </div>
                    <h3 class="font-extrabold text-base text-dark mb-1">Jadwal Sesi KBM Hari Ini</h3>
                    <div class="mb-3">
                        <span class="app-badge badge-layanan-dl font-bold text-xs py-1 px-3">
                            Terkunci Hingga Pukul {{ $waktuBukaStr }} WIB
                        </span>
                    </div>

                    <div class="dmc-grid text-left mb-3">
                        <div class="dmc-field">
                            <div class="dmc-label">Tutor Pengampu</div>
                            <div class="dmc-value font-bold text-dark">
                                {{ $todaySesi->tutor->nama_lengkap ?? 'Tutor Pembimbing' }}
                            </div>
                        </div>
                        <div class="dmc-field">
                            <div class="dmc-label">Mata Pelajaran</div>
                            <div class="dmc-value font-bold text-primary">
                                {{ $todaySesi->kategoriTutorial->nama_kategori ?? 'Tutorial KBM' }}
                            </div>
                        </div>
                        <div class="dmc-field">
                            <div class="dmc-label">Jam Sesi KBM</div>
                            <div class="dmc-value text-dark font-extrabold">
                                {{ $todaySesi->jam_masuk_formatted }} - {{ $todaySesi->jam_pulang_formatted }} WIB
                            </div>
                        </div>
                        <div class="dmc-field">
                            <div class="dmc-label">Waktu Buka Presensi</div>
                            <div class="dmc-value text-success font-extrabold">
                                {{ $waktuBukaStr }} WIB (H-30 Mnt)
                            </div>
                        </div>
                    </div>

                    {{-- Countdown Timer Box --}}
                    <div class="countdownCard mb-3 p-3">
                        <div class="countdownLabel">Waktu Menuju Buka Presensi</div>
                        <div class="countdownTime" id="countdownClock" style="font-size: 2rem; color: #0284c7;">
                            -- : -- : --
                        </div>
                        <div class="countdownSub mt-1 text-xs">Halaman akan otomatis memuat ulang saat jam absen tiba.</div>
                    </div>

                    <div class="dmc-footer pt-3 border-t-base">
                        <div class="dmc-actions flex-wrap gap-2">
                            <a href="{{ route('siswa.dashboard') }}" class="profileBtnSecondary text-xs py-2 px-3 flex-1 justify-center">
                                <ion-icon name="home-outline"></ion-icon> Dashboard
                            </a>
                            <a href="{{ route('siswa.jadwal') }}" class="profileBtnSecondary text-xs py-2 px-3 flex-1 justify-center">
                                <ion-icon name="calendar-outline"></ion-icon> Lihat Jadwal
                            </a>
                        </div>
                    </div>
                </div>

                <script>
                    (function() {
                        const openTime = new Date("{{ $today }}T{{ $waktuBukaStr }}:00").getTime();
                        const clockEl = document.getElementById('countdownClock');

                        function tick() {
                            const now = new Date().getTime();
                            const diff = openTime - now;

                            if (diff <= 0) {
                                if (clockEl) clockEl.textContent = "00:00:00 (Terbuka!)";
                                setTimeout(() => window.location.reload(), 1500);
                                return;
                            }

                            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                            if (clockEl) {
                                clockEl.textContent = 
                                    String(hours).padStart(2, '0') + " : " + 
                                    String(minutes).padStart(2, '0') + " : " + 
                                    String(seconds).padStart(2, '0');
                            }
                        }

                        tick();
                        setInterval(tick, 1000);
                    })();
                </script>
            @endif

        {{-- ── WAKTU ABSEN TERBUKA (NORMAL FORM) ── --}}
        @else
            @if(isset($sesiEval))
                <div class="card mb-4 p-4 border-base bg-card-alt rounded-xl">
                    <div class="d-flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <div class="text-xs font-bold text-primary text-uppercase tracking-wider">
                                Jadwal KBM: {{ $todaySesi->kategoriTutorial->nama_kategori ?? 'Tutorial KBM' }} ({{ $sesiEval['jam_masuk_target'] }} - {{ $sesiEval['jam_pulang_target'] }} WIB)
                            </div>
                            <div class="text-xs text-muted mt-1">
                                Target Masuk: <b>{{ $sesiEval['jam_masuk_target'] }} WIB</b> &bull; Batas Toleransi: <b>{{ $sesiEval['batas_toleransi'] }} WIB</b> ({{ $sesiEval['tolerance_minutes'] }} mnt)
                            </div>
                        </div>
                        <div>
                            @if($sesiEval['status_kehadiran'] === 'tepat_waktu')
                                <span class="badge bg-success-light text-success font-bold text-xs py-1 px-3 rounded-full">Tepat Waktu</span>
                            @elseif($sesiEval['status_kehadiran'] === 'lebih_awal')
                                <span class="badge bg-primary-light text-primary font-bold text-xs py-1 px-3 rounded-full">Lebih Awal</span>
                            @else
                                <span class="badge bg-warning-light text-warning font-bold text-xs py-1 px-3 rounded-full">Terlambat (+{{ $sesiEval['menit_keterlambatan'] }} mnt)</span>
                            @endif
                        </div>
                    </div>
                    @if($sesiEval['is_terlambat'])
                        <div class="mt-2 text-xs text-warning font-semibold">
                            <ion-icon name="alert-circle-outline" style="vertical-align: -2px;"></ion-icon>
                            Anda melewati batas waktu toleransi ({{ $sesiEval['batas_toleransi'] }} WIB). Presensi tetap dapat dilakukan dan tercatat terlambat.
                        </div>
                    @endif
                </div>
            @endif

            <div class="statusBanner {{ isset($sesiEval) && $sesiEval['is_terlambat'] ? 'mb-4' : 'ready mb-4' }}" style="{{ isset($sesiEval) && $sesiEval['is_terlambat'] ? 'background: #fffbeb; border-left: 4px solid #f59e0b;' : '' }}">
                <div>
                    <div class="statusTitle" style="{{ isset($sesiEval) && $sesiEval['is_terlambat'] ? 'color: #b45309;' : '' }}">
                        {{ isset($sesiEval) && $sesiEval['is_terlambat'] ? 'Presensi Masuk (Melebihi Toleransi)' : 'Presensi KBM Masuk Dibuka' }}
                    </div>
                    <div class="statusSub" style="{{ isset($sesiEval) && $sesiEval['is_terlambat'] ? 'color: #78350f;' : '' }}">
                        {{ isset($sesiEval) && $sesiEval['is_terlambat'] ? 'Anda tercatat terlambat ' . $sesiEval['menit_keterlambatan'] . ' menit. Ambil foto selfie untuk mencatat kehadiran.' : 'Silakan ambil foto kehadiran Anda di lokasi sekolah' }}
                    </div>
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

    {{-- ── Modal Bukti Foto Presensi ── --}}
    <div class="modal-overlay" id="buktiPhotoModal" onclick="if(event.target===this)closeBuktiPhotoModal()">
        <div class="modal-box">
            <div class="modal-header">
                <span id="buktiPhotoModalTitle" class="font-extrabold text-dark">Foto Presensi</span>
                <button type="button" class="modal-close" onclick="closeBuktiPhotoModal()" aria-label="Tutup">&times;</button>
            </div>
            <div class="modal-body p-0 text-center" style="background: #000;">
                <img id="buktiPhotoModalImg" src="" alt="Foto Presensi" style="width: 100%; max-height: 75vh; object-fit: contain; display: block; margin: 0 auto;">
            </div>
        </div>
    </div>

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

        function initPresensiMapInstance() {
            var mapEl = document.getElementById('leafletMap');
            if (!mapEl || presensiMap || typeof window.createPresensiMap === 'undefined') return;

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
                        } else {
                            badge.style.background = 'rgba(220, 38, 38, 0.12)';
                            badge.style.border = '1px solid rgba(220, 38, 38, 0.35)';
                            badge.style.color = '#b91c1c';
                            badge.innerHTML = '<span class="d-inline-flex items-center gap-1"><b>Di Luar Radius ' + state.targetNama + '</b> (' + Math.round(state.distance) + ' m, maks ' + state.targetRadius + ' m)</span>';
                        }
                    }

                    if (radarCard) {
                        radarCard.classList.remove('d-none');
                        if (state.isWithin) {
                            radarCard.className = 'proximityRadarCard radar-in-zone';
                            if (radarDot) radarDot.className = 'radarStatusDot dot-green';
                            if (radarTitle) radarTitle.textContent = 'Posisi Terverifikasi di Area ' + state.targetNama;
                            if (radarSub) radarSub.textContent = 'Jarak: ' + Math.round(state.distance) + ' meter dari titik pusat (Radius: ' + state.targetRadius + 'm).';
                            if (btnMaps) btnMaps.classList.add('d-none');
                        } else {
                            radarCard.className = 'proximityRadarCard radar-out-zone';
                            if (radarDot) radarDot.className = 'radarStatusDot dot-red';
                            var selisih = Math.round(state.distance - state.targetRadius);
                            if (radarTitle) radarTitle.textContent = 'Di Luar Batas Presensi (' + Math.round(state.distance) + ' m)';
                            if (radarSub) radarSub.textContent = 'Mendekatlah sekitar ' + selisih + ' meter lagi ke area ' + state.targetNama + '.';
                            if (btnMaps) {
                                btnMaps.href = 'https://www.google.com/maps/dir/?api=1&origin=' + state.userLat + ',' + state.userLng + '&destination=' + state.targetLat + ',' + state.targetLng;
                                btnMaps.classList.remove('d-none');
                            }
                        }
                    }

                    if (state.zoneChanged && window.showAppToast) {
                        window.showAppToast({
                            type: state.isWithin ? 'success' : 'warning',
                            title: state.isWithin ? 'Memasuki Area ' + state.targetNama : 'Keluar Dari Area ' + state.targetNama,
                            message: state.isWithin ? 'Anda berada dalam jangkauan presensi (' + Math.round(state.distance) + 'm).' : 'Anda berada ' + Math.round(state.distance) + 'm dari titik pusat.',
                            duration: 3500
                        });
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

            if (lokasiEl) {
                lokasiEl.value = lat.toFixed(6) + ',' + lng.toFixed(6);
            }

            initPresensiMapInstance();

            if (ph) ph.classList.add('hidden');

            if (presensiMap) {
                presensiMap.updateUserLocation(lat, lng, accuracy);
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

                setMapFromLatLng(lat, lng, accuracy);

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
                setMapFromLatLng(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
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

        function openBuktiPhotoModal(src, title) {
            const modal = document.getElementById('buktiPhotoModal');
            const img = document.getElementById('buktiPhotoModalImg');
            const titleEl = document.getElementById('buktiPhotoModalTitle');
            if (img) img.src = src;
            if (titleEl && title) titleEl.textContent = title;
            if (modal) modal.classList.add('active');
        }

        function closeBuktiPhotoModal() {
            const modal = document.getElementById('buktiPhotoModal');
            if (modal) modal.classList.remove('active');
        }
    </script>
@endsection
