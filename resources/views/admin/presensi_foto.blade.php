@extends('layouts.presensi')

@php
    $roleTitle = 'Presensi Admin';
@endphp

@section('title', $roleTitle)

@push('head')
<style>
    /* ── Responsive Layout & Spacing Presensi Pulang ── */
    .presensi-pulang-grid {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    @media (min-width: 992px) {
        .pagePad {
            max-width: 960px !important;
            padding: 24px 24px calc(120px + env(safe-area-inset-bottom, 0px)) !important;
        }
        .presensi-pulang-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: stretch;
        }
        .presensi-pulang-grid > .card {
            margin-bottom: 0 !important;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
    }

    /* ── Active Session Executive Summary Card ── */
    .active-session-summary-card {
        background: var(--card, #ffffff);
        border: 1px solid var(--border, #e2e8f0);
        border-radius: 18px;
        padding: 18px 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        margin-bottom: 16px;
        position: relative;
        overflow: hidden;
    }

    .active-session-summary-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: linear-gradient(180deg, #10b981 0%, #059669 100%);
        border-radius: 4px 0 0 4px;
    }

    .session-stat-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        background: var(--card-alt, #f8fafc);
        border: 1px solid var(--border, #e2e8f0);
        border-radius: 14px;
        padding: 12px 16px;
        margin: 12px 0;
    }

    @media (max-width: 640px) {
        .pagePad {
            padding-left: 14px !important;
            padding-right: 14px !important;
        }
        .active-session-summary-card {
            padding: 14px 14px;
            border-radius: 16px;
            margin-bottom: 14px;
        }
        .session-stat-grid {
            grid-template-columns: 1fr;
            gap: 6px;
            padding: 8px 12px;
            margin: 10px 0;
            border-radius: 12px;
        }
        .session-stat-item {
            flex-direction: row !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 5px 0;
            border-bottom: 1px dashed var(--border, #e2e8f0);
        }
        .session-stat-item:last-child {
            border-bottom: none;
        }
        .session-stat-val {
            font-size: 13px;
            max-width: 58%;
            text-align: right;
        }
    }

    .session-stat-item {
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 0;
    }

    .session-stat-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--muted, #64748b);
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .session-stat-val {
        font-size: 14px;
        font-weight: 800;
        color: var(--dark, #0f172a);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pulse-dot-green {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulseGreen 1.8s infinite;
        display: inline-block;
        flex-shrink: 0;
    }

    @keyframes pulseGreen {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .session-info-lead {
        font-size: 13px;
        color: var(--muted, #64748b);
        line-height: 1.5;
        margin: 4px 0 10px;
    }

    .session-notice-box {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 14px;
        background: rgba(99, 102, 241, 0.05);
        border: 1px solid rgba(99, 102, 241, 0.18);
        border-radius: 12px;
        margin-top: 10px;
    }

    .session-notice-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: rgba(99, 102, 241, 0.12);
        color: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .session-notice-content {
        flex: 1;
        min-width: 0;
    }

    .session-notice-title {
        font-size: 12px;
        font-weight: 700;
        color: var(--dark, #0f172a);
        line-height: 1.3;
        margin-bottom: 3px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }

    .session-notice-title .notice-tag {
        font-size: 10px;
        font-weight: 700;
        padding: 1px 7px;
        background: #e0e7ff;
        color: #4338ca;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        line-height: 1.4;
    }

    .session-notice-text {
        font-size: 11.5px;
        color: var(--muted, #64748b);
        line-height: 1.5;
        margin: 0;
        word-break: break-word;
    }

    /* ── Mobile Map & Camera Optimizations ── */
    @media (max-width: 768px) {
        .mapBox.pulang-mapbox {
            min-height: 220px;
            max-height: 250px;
        }
        .pulang-card-header {
            padding-bottom: 12px;
        }
    }

    @media (max-width: 576px) {
        .session-info-lead {
            font-size: 12px;
        }
        .session-notice-box {
            padding: 10px 12px;
            gap: 10px;
        }
        .session-notice-icon {
            width: 26px;
            height: 26px;
            font-size: 15px;
        }
        .session-notice-text {
            font-size: 11px;
        }
    }
</style>
@endpush

@section('content')
    @php
        $displayName = (string) ($user->nama_lengkap ?? ($user->name ?? 'Admin'));
        $initial = strtoupper(substr($displayName, 0, 1));
        $isBypassRadius = $isBypassRadius ?? true;
        $isBypassWaktuTunggu = $isBypassWaktuTunggu ?? true;

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
        $dashRoute = route('admin.dashboard');
        $storeRoute = route('admin.presensi.store');
        $adminWa = \App\Models\User::getAdminWaContact();
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
        <button type="button" onclick="checkPermissions()" class="permWarnBtn">
            <ion-icon name="refresh-outline"></ion-icon> Aktifkan Izin
        </button>
    </div>

    {{-- ══════════════════ MAIN CONTENT ══════════════════ --}}
    <div class="pagePad" id="mainContent">

        {{-- ── PERINGATAN PRESENSI PULANG LAMPAU TERLEWAT ── --}}
        @if (isset($unclosedPastSession) && $unclosedPastSession)
            @php
                $tglUnclosed = \Carbon\Carbon::parse($unclosedPastSession->tgl_presensi, 'Asia/Jakarta');
                $jamMulaiUnclosed = substr((string) $unclosedPastSession->jam_mulai, 0, 5);
                $adminWaNumber = $adminWa;
                $pesanWa = urlencode("Halo Admin PKBM Pikat, saya {$displayName} terlewat presensi pulang pada hari {$tglUnclosed->translatedFormat('l, d F Y')} (masuk pukul {$jamMulaiUnclosed} WIB). Mohon bantuan rekonsiliasi jam kerja di sistem. Terima kasih.");
            @endphp
            <div id="bannerUnclosedPastSession" class="statusBanner mb-4" style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.3); border-left: 4px solid #f59e0b;">
                <div class="statusIcon warn" style="background: rgba(245, 158, 11, 0.15); color: #d97706;">
                    <ion-icon name="time-outline"></ion-icon>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="statusTitle" style="color: #b45309;">
                        Presensi Pulang Terlewat pada {{ $tglUnclosed->translatedFormat('l, d F Y') }}
                    </div>
                    <div class="statusSub" style="color: #92400e; line-height: 1.5;">
                        Anda tercatat masuk pukul <strong>{{ $jamMulaiUnclosed }} WIB</strong> kemarin namun belum melakukan presensi pulang (*checkout*). Kehadiran hari ini tetap dapat dicatat normal.
                    </div>
                    <div class="d-flex items-center gap-2 mt-2.5 flex-wrap">
                        <a href="https://wa.me/{{ $adminWaNumber }}?text={{ $pesanWa }}" target="_blank" rel="noopener noreferrer"
                           class="profileBtnPrimary text-xs py-1.5 px-3 d-inline-flex items-center gap-1"
                           style="background: #059669; border-color: #059669;">
                            <ion-icon name="logo-whatsapp"></ion-icon> Konfirmasi ke Admin
                        </a>
                        <button type="button" onclick="document.getElementById('bannerUnclosedPastSession').style.display='none'"
                                class="btnOutline text-xs py-1.5 px-3" style="background: transparent;">
                            Abaikan &amp; Lanjut Absen Hari Ini
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── Banner Notifikasi Fleksibilitas Bebas Radius (Admin) ── --}}
        @if ($isBypassRadius && !$activeSesi)
            <div class="statusBanner flexible mb-3">
                <div class="statusIcon blue">
                    <ion-icon name="briefcase-outline"></ion-icon>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="statusTitle">Akses Fleksibel (Bebas Radius) Aktif</div>
                    <div class="statusSub">
                        Sebagai Administrator, presensi Anda tidak dibatasi oleh radius titik lokasi sekolah untuk mendukung fleksibilitas dinas luar atau rapat penting.
                    </div>
                </div>
            </div>
        @endif

        {{-- ── RIWAYAT SESI SELESAI HARI INI ── --}}
        @if ($completedSessions->count() > 0)
            <div class="card mb-4">
                <div class="cardTitle">
                    <div class="cardTitleStart">
                        <ion-icon name="time-outline"></ion-icon>
                        <span>Riwayat Presensi Hari Ini</span>
                    </div>
                    <span class="badge bg-success-light text-success text-xs px-2.5 py-1 rounded-full font-bold">{{ $completedSessions->count() }} Presensi</span>
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
                try {
                    $masukCarbon = \Carbon\Carbon::parse($activeSesi->tgl_presensi . ' ' . $activeSesi->jam_mulai, 'Asia/Jakarta');
                    $nowCarbon = \Carbon\Carbon::now('Asia/Jakarta');
                    $durasiMenitTotal = max(1, (int) $masukCarbon->diffInMinutes($nowCarbon, false));
                    $jamDur = floor($durasiMenitTotal / 60);
                    $menitDur = $durasiMenitTotal % 60;
                    $durasiBerjalanLabel = ($jamDur > 0 ? "{$jamDur} jam " : '') . "{$menitDur} menit";
                } catch (\Throwable) {
                    $durasiBerjalanLabel = '-';
                }
                $lokasiMasukNama = $activeLokasi?->nama_lokasi ?? config('lokasi.sekolah_nama', 'Gedung Pusat PKBM Pikat');
            @endphp

            @if ($bisaPulang)
                {{-- Executive Card: Sesi Siap Presensi Pulang --}}
                <div class="active-session-summary-card">
                    <div class="d-flex items-center justify-between gap-2 flex-wrap mb-2">
                        <div class="d-flex items-center gap-2 flex-wrap">
                            <span class="badge bg-success-light text-success font-bold text-xs px-2.5 py-1 rounded-full d-inline-flex items-center gap-1.5">
                                <span class="pulse-dot-green"></span>
                                Siap Presensi Pulang
                            </span>
                            @if ($isBypassWaktuTunggu)
                                <span class="badge-flexible">
                                    <ion-icon name="flash-outline"></ion-icon>
                                    Akses Fleksibel Kepulangan Aktif
                                </span>
                            @endif
                        </div>
                        <div class="text-xs font-bold text-muted">
                            {{ \Carbon\Carbon::parse($activeSesi->tgl_presensi)->translatedFormat('l, d F Y') }}
                        </div>
                    </div>

                    {{-- Metrik Ringkas Sesi Berjalan (Waktu Masuk, Durasi Kerja, Titik Lokasi Masuk) --}}
                    <div class="session-stat-grid">
                        <div class="session-stat-item">
                            <span class="session-stat-label">
                                <ion-icon name="log-in-outline" class="text-primary"></ion-icon> Jam Masuk
                            </span>
                            <span class="session-stat-val text-primary">{{ $jamMasuk }} WIB</span>
                        </div>
                        <div class="session-stat-item">
                            <span class="session-stat-label">
                                <ion-icon name="time-outline" class="text-success"></ion-icon> Durasi Kerja
                            </span>
                            <span class="session-stat-val text-success">{{ $durasiBerjalanLabel }}</span>
                        </div>
                        <div class="session-stat-item">
                            <span class="session-stat-label">
                                <ion-icon name="location-outline" class="text-danger"></ion-icon> Titik Lokasi
                            </span>
                            <span class="session-stat-val text-dark" title="{{ $lokasiMasukNama }}">{{ $lokasiMasukNama }}</span>
                        </div>
                    </div>

                    <div class="session-info-lead">
                        @if ($isBypassWaktuTunggu)
                            Formulir presensi pulang terbuka dan siap dikirimkan kapan saja setelah agenda rapat atau dinas selesai.
                        @else
                            Telah memenuhi durasi minimal kerja (≥ 1 jam). Silakan verifikasi titik lokasi dan ambil foto untuk presensi pulang.
                        @endif
                    </div>
                    @if ($isBypassRadius)
                        <div class="session-notice-box">
                            <div class="session-notice-icon">
                                <ion-icon name="shield-checkmark-outline"></ion-icon>
                            </div>
                            <div class="session-notice-content">
                                <div class="session-notice-title">
                                    <span>Mode Bebas Radius Aktif</span>
                                    <span class="notice-tag">Dispensasi</span>
                                </div>
                                <p class="session-notice-text">
                                    Mode Bebas Radius aktif untuk mendukung penugasan luar / rapat dinas administrator tanpa kendala batasan jarak radius.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Warning jika sudah lewat 2 jam untuk staf biasa (jika ada) --}}
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

                <div class="presensi-pulang-grid">
                    {{-- ── KARTU 1: LOKASI PRESENSI PULANG ── --}}
                    <div class="card mb-0">
                        <div class="cardTitle pulang-card-header">
                            <div class="cardTitleStart">
                                <ion-icon name="location-outline"></ion-icon>
                                <span>Lokasi Presensi Pulang</span>
                            </div>
                            @if ($isBypassRadius)
                                <span class="badge-flexible">
                                    <ion-icon name="shield-checkmark-outline"></ion-icon>
                                    Bebas Radius
                                </span>
                            @endif
                        </div>

                        <div id="mapBox" class="mapBox pos-relative pulang-mapbox">
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
                                    <ion-icon name="navigate-outline"></ion-icon> <span>Petunjuk Arah</span>
                                </a>
                                <button type="button" id="btnToggleLiveGps" class="btnLiveGpsActive" onclick="toggleLiveTracking()">
                                    <span id="liveGpsLabel">Pantau Lokasi: Aktif</span>
                                </button>
                            </div>
                        </div>

                        <div class="mapToolbar mt-auto pt-3">
                            <button type="button" class="btnRefreshLocation" onclick="refreshLocation(true)">
                                <ion-icon name="locate-outline"></ion-icon> <span>Perbarui Lokasi</span>
                            </button>
                        </div>
                    </div>

                    {{-- ── KARTU 2: FOTO & KIRIM PRESENSI PULANG ── --}}
                    <div class="card mb-0">
                        <div class="cardTitle pulang-card-header">
                            <div class="cardTitleStart">
                                <ion-icon name="camera-outline"></ion-icon>
                                <span>Foto Presensi Pulang</span>
                            </div>
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
                        <div class="camActions mt-3" id="camActions">
                            <button type="button" class="captureBtn" id="btnBukaKamera" onclick="openLiveCamera()">
                                <ion-icon name="camera-outline"></ion-icon> Buka Kamera
                            </button>
                            <div id="camRowStreaming" class="camRow d-none">
                                <button type="button" class="captureBtn" onclick="snapPhoto()">
                                    Ambil Foto
                                </button>
                                <button type="button" class="captureBtn secondary" onclick="cancelCamera()">Batal</button>
                            </div>
                            <button type="button" id="btnUlangi" onclick="retakePhoto()" class="captureBtn secondary d-none">
                                <ion-icon name="refresh-outline"></ion-icon> Ulangi Foto
                            </button>
                        </div>

                        {{-- Tombol SELESAI --}}
                        <button type="submit" id="btnSubmit" @if (!$bisaPulang) disabled @endif class="captureBtn primary-red mt-3.5">
                            <ion-icon name="log-out-outline"></ion-icon> Kirim Presensi Pulang
                        </button>

                        <div class="presensi-aux-actions mt-3">
                            <a href="{{ route('admin.izin.index') }}" class="btn-izin-action">
                                <ion-icon name="calendar-outline"></ion-icon>
                                <span>Data Rekap Izin &amp; Sakit</span>
                            </a>

                            <a href="https://wa.me/{{ $adminWa }}?text={{ urlencode('Halo Admin, saya ' . $displayName . ' mengalami kendala saat presensi pulang hari ini: ') }}" target="_blank" class="wa-support-btn">
                                <ion-icon name="logo-whatsapp"></ion-icon>
                                <span>Kendala Presensi (Hubungi Admin)</span>
                            </a>
                        </div>
                    </div>
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
                            <div class="text-xs font-bold text-primary text-uppercase tracking-wider">Jadwal: {{ $shiftEval['shift_nama'] }}</div>
                            @if(!empty($shiftEval['has_shift']))
                                <div class="text-xs text-muted">Masuk: <b>{{ $shiftEval['jam_masuk_target'] }} WIB</b> &bull; Batas: <b>{{ $shiftEval['batas_toleransi'] }} WIB</b></div>
                            @else
                                <div class="text-xs text-muted">Waktu: <b>{{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i') }} WIB</b> &bull; Jam kerja fleksibel</div>
                            @endif
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
                <div class="statusIcon blue">
                    <ion-icon name="finger-print-outline"></ion-icon>
                </div>
                <div class="flex-1 min-w-0">
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
                        <div class="cardTitleStart">
                            <ion-icon name="location-outline"></ion-icon>
                            <span>Lokasi Presensi Masuk</span>
                        </div>
                    </div>

                    {{-- Dropdown Pemilihan Titik Lokasi Absen --}}
                    <div class="mb-3" id="boxPilihLokasi">
                        <label class="d-flex items-center justify-between text-sm font-semibold text-muted mb-2">
                            <span>Pilih Titik Lokasi Presensi <span class="text-danger">*</span></span>
                            @if ($isBypassRadius)
                                <span class="badge-flexible">
                                    <ion-icon name="shield-checkmark-outline"></ion-icon>
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
                                <ion-icon name="navigate-outline"></ion-icon> <span>Petunjuk Arah</span>
                            </a>
                            <button type="button" id="btnToggleLiveGps" class="btnLiveGpsActive" onclick="toggleLiveTracking()">
                                <span id="liveGpsLabel">Pantau Lokasi: Aktif</span>
                            </button>
                        </div>
                    </div>

                    <div class="mapToolbar">
                        <button type="button" class="btnRefreshLocation" onclick="refreshLocation(true)">
                            <ion-icon name="locate-outline"></ion-icon> <span>Perbarui Lokasi</span>
                        </button>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="cardTitle">
                        <div class="cardTitleStart">
                            <ion-icon name="camera-outline"></ion-icon>
                            <span>Foto Presensi Masuk</span>
                        </div>
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
                        <ion-icon name="checkmark-circle-outline"></ion-icon> Kirim Presensi Masuk
                    </button>

                    <div class="presensi-aux-actions">
                        <a href="{{ route('admin.izin.index') }}" class="btn-izin-action">
                            <ion-icon name="calendar-outline"></ion-icon>
                            <span>Data Rekap Izin &amp; Sakit</span>
                        </a>

                        <a href="https://wa.me/{{ $adminWa }}?text={{ urlencode('Halo Admin, saya ' . $displayName . ' mengalami kendala saat presensi hari ini: ') }}" target="_blank" class="wa-support-btn">
                            <ion-icon name="logo-whatsapp"></ion-icon>
                            <span>Kendala Presensi (Hubungi Admin)</span>
                        </a>
                    </div>
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

        function requestCameraPermission() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                return Promise.reject(new Error('no_media_devices'));
            }
            return navigator.mediaDevices.getUserMedia({
                video: true,
                audio: false
            }).then(function (stream) {
                if (stream) {
                    stream.getTracks().forEach(function (t) {
                        t.stop();
                    });
                }
                return true;
            });
        }

        function requestLocationPermission() {
            return new Promise(function (resolve, reject) {
                if (!navigator.geolocation) {
                    return reject(new Error('no_geolocation'));
                }
                navigator.geolocation.getCurrentPosition(
                    function (pos) {
                        resolve(pos);
                    },
                    function (err) {
                        if (err && (err.code === 2 || err.code === 3)) {
                            navigator.geolocation.getCurrentPosition(
                                function (pos) {
                                    resolve(pos);
                                },
                                function (retryErr) {
                                    reject(retryErr);
                                },
                                {
                                    enableHighAccuracy: false,
                                    timeout: 10000,
                                    maximumAge: 60000
                                }
                            );
                        } else {
                            reject(err);
                        }
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 8000,
                        maximumAge: 30000
                    }
                );
            });
        }

        function checkPermissions() {
            var permW = document.getElementById('permWarning');
            if (permW) permW.style.display = 'none';

            requestCameraPermission()
                .then(function () {
                    return requestLocationPermission();
                })
                .then(function () {
                    showMainContent();
                })
                .catch(function (err) {
                    var name = err && err.name ? err.name : '';
                    var code = err && typeof err.code !== 'undefined' ? err.code : null;

                    if (err && err.message === 'no_media_devices') {
                        showGateError('Peramban tidak mendukung kamera. Gunakan Chrome atau Safari terbaru.');
                    } else if (err && err.message === 'no_geolocation') {
                        showGateError('Peramban tidak mendukung geolokasi GPS.');
                    } else if (name === 'NotAllowedError' || name === 'PermissionDeniedError' || code === 1) {
                        if (code === 1) {
                            showGateError('Izin <strong>lokasi GPS</strong> belum diizinkan.<br>Silakan aktifkan GPS dan izinkan akses lokasi.');
                        } else {
                            showGateError('Izin <strong>kamera</strong> belum diizinkan.<br>Kamera depan wajib untuk foto selfie presensi.');
                        }
                    } else {
                        showGateError('Gagal mendapatkan izin kamera/lokasi GPS. Pastikan GPS aktif dan izin diberikan.');
                    }
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            checkPermissions();

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
            if (!mapEl || presensiMap) return;

            if (typeof window.createPresensiMap === 'undefined') {
                setTimeout(initPresensiMapInstance, 100);
                return;
            }

            mapEl.classList.remove('d-none');
            mapEl.style.display = 'block';

            var ph = document.getElementById('mapPlaceholder');
            if (ph) {
                ph.classList.add('hidden');
                ph.style.display = 'none';
            }

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
                        radarCard.classList.remove('d-none');
                        radarCard.style.display = 'flex';
                        var sisaJarak = Math.max(0, Math.round(state.distance - state.targetRadius));

                        if (state.isWithin) {
                            radarDot.className = 'radarStatusDot pulse-green';
                            radarTitle.textContent = 'Posisi Anda Sesuai di ' + state.targetNama;
                            radarSub.textContent = 'Lokasi telah cocok. Silakan ambil foto dan kirim presensi.';
                            if (btnMaps) {
                                btnMaps.classList.add('d-none');
                                btnMaps.style.display = 'none';
                            }

                            if (state.zoneChanged && navigator.vibrate) {
                                navigator.vibrate([100, 50, 100]);
                            }
                        } else if (IS_BYPASS_RADIUS) {
                            radarDot.className = 'radarStatusDot pulse-blue';
                            radarTitle.textContent = 'Mode Bebas Radius: Jarak ' + Math.round(state.distance) + ' m dari ' + state.targetNama;
                            radarSub.textContent = 'Presensi di luar radius diizinkan khusus Admin untuk dinas luar / rapat.';
                            if (btnMaps) {
                                btnMaps.classList.remove('d-none');
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
                                btnMaps.classList.remove('d-none');
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

            var refreshBtn = document.querySelector('.btnRefreshLocation');
            if (refreshBtn) {
                refreshBtn.classList.remove('loading');
                var span = refreshBtn.querySelector('span');
                if (span) span.textContent = 'Perbarui Lokasi';
            }
        }

        function handleGpsError() {
            var refreshBtn = document.querySelector('.btnRefreshLocation');
            if (refreshBtn) {
                refreshBtn.classList.remove('loading');
                var span = refreshBtn.querySelector('span');
                if (span) span.textContent = 'Perbarui Lokasi';
            }

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
            if (!btn || !label) return;

            if (isLiveTracking) {
                btn.className = 'btnLiveGpsActive';
                label.textContent = 'Pantau Lokasi: Aktif';
                if (dot) dot.className = 'liveDot';
            } else {
                btn.className = 'btnLiveGpsPaused';
                label.textContent = 'Pantau Lokasi: Jeda';
                if (dot) dot.className = 'liveDot paused';
            }
        }

        function refreshLocation(manualClick) {
            var refreshBtn = document.querySelector('.btnRefreshLocation');
            if (refreshBtn && manualClick) {
                refreshBtn.classList.add('loading');
                var span = refreshBtn.querySelector('span');
                if (span) span.textContent = 'Mencari Lokasi...';
            }

            var ph = document.getElementById('mapPlaceholder');
            if (!navigator.geolocation) {
                if (ph) ph.textContent = 'Geolocation tidak didukung.';
                if (refreshBtn) {
                    refreshBtn.classList.remove('loading');
                    var span = refreshBtn.querySelector('span');
                    if (span) span.textContent = 'Perbarui Lokasi';
                }
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

        document.addEventListener('DOMContentLoaded', function () {
            initPresensiMapInstance();
            refreshLocation(false);
            startLiveTracking();
        });

        /* ══════════════════ ADVANCED CAMERA CONTROLS ══════════════════ */
        const currentFacingMode = 'user';
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
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Kamera Depan',
                    text: 'Pengambilan foto presensi dibatasi hanya menggunakan kamera depan.',
                    timer: 2000,
                    showConfirmButton: false
                });
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
                        facingMode: { ideal: 'user' },
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                }).catch(function() {
                    return navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'user' },
                        audio: false
                    });
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

            var targetAspect = 3 / 4;
            var videoWidth = video.videoWidth;
            var videoHeight = video.videoHeight;
            var videoAspect = videoWidth / videoHeight;
            var sx, sy, sWidth, sHeight;

            if (videoAspect > targetAspect) {
                sHeight = videoHeight;
                sWidth = Math.round(videoHeight * targetAspect);
                sx = Math.round((videoWidth - sWidth) / 2);
                sy = 0;
            } else {
                sWidth = videoWidth;
                sHeight = Math.round(videoWidth / targetAspect);
                sx = 0;
                sy = Math.round((videoHeight - sHeight) / 2);
            }

            var outputWidth = Math.min(sWidth, 960);
            var outputHeight = Math.round(outputWidth / targetAspect);

            var canvas = document.createElement('canvas');
            canvas.width = outputWidth;
            canvas.height = outputHeight;
            var ctx = canvas.getContext('2d');

            if (isMirrored) {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }

            ctx.drawImage(video, sx, sy, sWidth, sHeight, 0, 0, canvas.width, canvas.height);

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
