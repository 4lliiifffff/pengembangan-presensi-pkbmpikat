@extends('layouts.presensi')

@section('title', 'Presensi Tutor')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

@section('content')


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
        $adminWa = config('app.admin_wa', '6281234567890');
    @endphp

    {{-- ── Standard Top Navigation Bar ── --}}
    @include('layouts.components.navigasi_atas', [
        'titleName' => $displayName,
        'subTitle' => 'Presensi • ' . \Carbon\Carbon::parse($today)->translatedFormat('d M Y'),
        'dashRoute' => $dashRoute,
        'backRoute' => $dashRoute,
    ])

    {{-- ── Warning Banner Izin ── --}}
    <div id="permWarning" class="permWarning">
        <ion-icon name="warning-outline" style="font-size:20px; color:#d97706; flex-shrink:0;"></ion-icon>
        <div style="flex:1; min-width:0;">
            <div class="permWarnTitle" id="permWarnTitle">Izin belum diberikan</div>
            <div class="permWarnDesc" id="permWarnDesc">Kamera dan lokasi diperlukan untuk absen.</div>
        </div>
        <button onclick="checkPermissions()" class="permWarnBtn">
            <ion-icon name="refresh-outline" style="font-size:14px;"></ion-icon> Coba Lagi
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
                    <div class="statusIcon" style="background:rgba(220,38,38,0.15);color:#dc2626;">
                        <ion-icon name="warning-outline"></ion-icon>
                    </div>
                    <div>
                        <div class="statusTitle" style="color:#dc2626;">Sudah Lewat 2 Jam!</div>
                        <div class="statusSub">Segera lakukan absen pulang sekarang.</div>
                    </div>
                </div>
            @endif

            {{-- Form absen PULANG: hanya tampil jika sudah bisa pulang --}}
            @if ($bisaPulang)
            <form method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" id="presensiForm">
                @csrf
                <input type="hidden" name="mode" value="selesai">
                @foreach($activeSessions as $sesi)
                <input type="hidden" name="siswa_id[]" value="{{ $sesi->siswa_id }}">
                @endforeach
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
                    <button type="submit" class="captureBtn primary-red" id="btnSubmit" @if (!$bisaPulang) disabled @endif style="margin-top:14px;">
                        <ion-icon name="log-out-outline" style="font-size:20px;"></ion-icon>
                        Selesai Sesi (Absen Pulang)
                    </button>
                </div>
            </form>

            @else
                {{-- Belum 1 jam: tampilkan info saja, tanpa form --}}
                <div class="card" style="text-align:center; padding:24px 16px; border-radius:18px;">
                    <div style="font-size:32px; margin-bottom:8px; color:var(--warn);"><ion-icon name="time-outline"></ion-icon></div>
                    <div style="font-size:13.5px; font-weight:800; color:var(--text);">Form Absen Pulang Terbuka Otomatis</div>
                    <div style="font-size:11.5px; color:var(--muted); margin-top:4px;">Tersisa {{ number_format($sisaDetik / 60, 0) }} menit lagi (minimal 1 jam durasi mengajar)</div>
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
            <form method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" id="presensiForm">
                @csrf
                <input type="hidden" name="mode" value="mulai">
                <input type="hidden" name="lokasi" id="lokasi" value="">
                <input type="hidden" name="lokasi_akurasi" id="lokasi_akurasi" value="">
                <input type="hidden" name="is_mock_location" id="is_mock_location" value="0">

                {{-- ── CARD 1: INFORMASI SESI MENGAJAR ── --}}
                <div class="card" style="margin-bottom:14px;">
                    <div class="cardTitle">
                        <ion-icon name="book-outline"></ion-icon>Informasi Sesi Mengajar
                    </div>

                    {{-- 1. Moda Pembelajaran --}}
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:11.5px; font-weight:800; text-transform:uppercase; color:var(--muted); margin-bottom:6px;">
                            Moda Pembelajaran <span style="color:#ef4444;">*</span>
                        </label>
                        <select name="moda_pembelajaran" id="selectModa" class="input" style="width:100%; border-radius:12px; padding:10px 12px; font-size:13px; font-weight:600; background:var(--card-alt); color:var(--text); border:1px solid var(--border);" onchange="handleModaChange(this.value)">
                            <option value="sekolah" {{ old('moda_pembelajaran') == 'sekolah' ? 'selected' : '' }}>Sekolah (Tatap Muka di PKBM)</option>
                            <option value="kunjungan_rumah" {{ old('moda_pembelajaran') == 'kunjungan_rumah' ? 'selected' : '' }}>Kunjungan Rumah (Home Visit)</option>
                            <option value="online" {{ old('moda_pembelajaran') == 'online' ? 'selected' : '' }}>Pembelajaran Daring (Online)</option>
                        </select>
                    </div>

                    {{-- 2. Link Daring (Jika Moda = Online) --}}
                    <div style="margin-bottom:12px; display:none;" id="boxLinkDaring">
                        <label style="display:block; font-size:11.5px; font-weight:800; text-transform:uppercase; color:#0284c7; margin-bottom:6px;">
                            Link Pertemuan Daring (Opsional)
                        </label>
                        <input type="url" name="link_daring" class="input" placeholder="https://meet.google.com/... atau Zoom" value="{{ old('link_daring') }}" style="width:100%; border-radius:12px; padding:10px 12px; font-size:13px; background:var(--card-alt); color:var(--text); border:1px solid #0284c7;">
                    </div>

                    {{-- 3. Durasi Sesi Pertemuan --}}
                    <div style="margin-bottom:12px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                            <label style="font-size:11.5px; font-weight:800; text-transform:uppercase; color:var(--muted);">
                                Durasi Sesi <span style="color:#ef4444;">*</span>
                            </label>
                            <span id="labelKategoriLayanan" style="font-size:10px; font-weight:800; background:rgba(11,94,215,0.12); color:var(--blue2); padding:2px 8px; border-radius:6px;">Tutorial Komunitas</span>
                        </div>
                        <select name="durasi_pilihan" id="selectDurasi" class="input" style="width:100%; border-radius:12px; padding:10px 12px; font-size:13px; font-weight:600; background:var(--card-alt); color:var(--text); border:1px solid var(--border);">
                            <option value="2.0">2 Jam Sesi (Standar)</option>
                            <option value="3.0">3 Jam Sesi (Panjang)</option>
                        </select>
                    </div>

                    {{-- 4. Checkbox Gabungan Komunitas --}}
                    <div style="margin-bottom:12px; background:var(--card-alt); border:1px solid var(--border); border-radius:12px; padding:10px 12px;" id="boxGabungan">
                        <label style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:700; cursor:pointer; color:var(--text); margin:0;">
                            <input type="checkbox" name="is_gabungan" id="inputIsGabungan" value="1" {{ old('is_gabungan') ? 'checked' : '' }} style="width:16px; height:16px; accent-color:#1f3b8a;">
                            <span>Sesi Gabungan Komunitas (Rombel)</span>
                        </label>
                    </div>

                    {{-- 5. Searchable Dropdown Pemilihan Siswa --}}
                    <div style="position:relative;" id="wrapperSiswaDropdown">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                            <label style="font-size:11.5px; font-weight:800; text-transform:uppercase; color:var(--muted); display:flex; align-items:center; gap:5px;">
                                Siswa yang Diajar <span style="color:#ef4444;">*</span>
                            </label>
                            <span style="font-size:10px; font-weight:700; color:#15803d; background:rgba(22,163,74,0.12); padding:2px 8px; border-radius:6px;">Penugasan Admin</span>
                        </div>

                        <div style="background:var(--card-alt); border:1px solid var(--border); border-radius:14px; padding:10px;">
                            {{-- Search Input Bar --}}
                            <div style="position:relative; display:flex; align-items:center; margin-bottom:8px;">
                                <ion-icon name="search-outline" style="position:absolute; left:10px; color:var(--muted); font-size:16px; pointer-events:none;"></ion-icon>
                                <input type="text" id="inputSearchSiswa" placeholder="Cari nama siswa / NIS / ABK..." oninput="filterSiswaList(this.value)" style="width:100%; padding:8px 30px 8px 32px; border-radius:8px; border:1px solid var(--border); font-size:12px; font-weight:600; outline:none; background:var(--card); color:var(--text);" autocomplete="off">
                                <button type="button" onclick="clearSiswaSearch()" id="btnClearSiswaSearch" style="display:none; position:absolute; right:8px; background:none; border:none; color:var(--muted); cursor:pointer; font-size:14px; padding:2px;">✕</button>
                            </div>

                            {{-- Selected Counter & Quick Toggle --}}
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:0 2px 8px; border-bottom:1px solid var(--border); margin-bottom:6px;">
                                <div id="selectedSiswaBadge" style="font-size:11px; font-weight:800; color:var(--blue2);">
                                    <span id="selectedSiswaCount">0</span> Siswa Terpilih
                                </div>
                                <div style="display:flex; gap:8px;">
                                    <button type="button" onclick="selectAllVisibleSiswa(true)" style="font-size:10.5px; font-weight:700; color:var(--blue2); background:none; border:none; cursor:pointer; padding:0;">Pilih Semua</button>
                                    <span style="color:var(--border);">|</span>
                                    <button type="button" onclick="selectAllVisibleSiswa(false)" style="font-size:10.5px; font-weight:700; color:#ef4444; background:none; border:none; cursor:pointer; padding:0;">Reset</button>
                                </div>
                            </div>

                            {{-- Scrollable List of Students --}}
                            <div id="listSiswaItems" style="max-height: 180px; overflow-y: auto; padding-right: 2px;">
                                @forelse ($siswas as $siswa)
                                    @php
                                        $p = collect($presensiToday)->get($siswa->id);
                                        $badge = '';
                                        if ($p?->foto_mulai && !$p?->foto_selesai) {
                                            $badge = ' <span style="color:#d97706; font-size:10px; display:inline-flex; align-items:center; gap:2px; font-weight:700;">(<ion-icon name="time-outline"></ion-icon> Berjalan)</span>';
                                        }
                                        $isChecked = in_array($siswa->id, (array) old('siswa_id', [])) ? 'checked' : '';
                                        $searchKeyword = strtolower($siswa->nama_siswa . ' ' . $siswa->no_absen . ' ' . ($siswa->is_abk ? 'abk berkebutuhan khusus' : 'reguler'));
                                    @endphp
                                    <label class="siswa-item-row" data-search="{{ $searchKeyword }}" style="display:flex; align-items:center; justify-content:space-between; padding:8px 8px; border-radius:8px; margin-bottom:3px; cursor:pointer; transition:background .15s; border:1px solid transparent;">
                                        <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                                            <input type="checkbox" name="siswa_id[]" class="siswa-checkbox" value="{{ $siswa->id }}" {{ $isChecked }} onchange="updateSelectedSiswaCount()" style="width:16px; height:16px; accent-color:var(--blue2); cursor:pointer; flex-shrink:0;">
                                            <div style="min-width:0;">
                                                <div style="font-size:12.5px; font-weight:800; color:var(--text); line-height:1.2;">
                                                    {{ $siswa->nama_siswa }} {!! $badge !!}
                                                </div>
                                                <div style="font-size:10px; color:var(--muted); margin-top:2px;">
                                                    No Absen: {{ $siswa->no_absen }} • Kelas: {{ $siswa->relKelas->nama_kelas ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                        @if($siswa->is_abk)
                                            <span style="display:inline-block; padding:2px 6px; border-radius:6px; font-size:10px; font-weight:800; background:rgba(245,158,11,0.15); color:#b45309; flex-shrink:0;">ABK</span>
                                        @else
                                            <span style="display:inline-block; padding:2px 6px; border-radius:6px; font-size:10px; font-weight:700; background:rgba(22,163,74,0.12); color:#15803d; flex-shrink:0;">Reguler</span>
                                        @endif
                                    </label>
                                @empty
                                    <div style="padding:16px; text-align:center; font-size:12px; color:var(--muted);">
                                        <ion-icon name="person-outline" style="font-size:24px; display:block; margin:0 auto 4px;"></ion-icon>
                                        Belum ada siswa yang ditugaskan ke akun Anda.
                                    </div>
                                @endforelse
                                <div id="noMatchSiswa" style="display:none; padding:16px; text-align:center; font-size:11px; color:var(--muted); font-weight:600;">
                                    <ion-icon name="search-outline" style="font-size:20px; display:block; margin:0 auto 4px;"></ion-icon>
                                    Tidak ditemukan siswa yang sesuai
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── CARD 2: LOKASI PRESENSI ── --}}
                <div class="card" style="margin-bottom:14px;">
                    <div class="cardTitle">
                        <ion-icon name="location-outline"></ion-icon>Lokasi Presensi & Radius GPS
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

                {{-- ── CARD 3: FOTO KAMERA ── --}}
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
                            Ketuk <b>Buka Kamera</b> untuk mengambil foto selfie presensi.
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
                        Mulai Sesi (Absen Masuk)
                    </button>

                    <a href="https://wa.me/{{ $adminWa }}?text={{ urlencode('Halo Admin, saya ' . $displayName . ' ingin izin untuk hari ini...') }}"
                       target="_blank"
                       style="display:flex;align-items:center;justify-content:center;gap:8px;padding:12px 14px;border-radius:14px;background:rgba(37,211,102,0.10);border:1px solid rgba(37,211,102,0.25);text-decoration:none;color:#15803d;font-size:13px;font-weight:800;margin-top:8px;">
                        <ion-icon name="logo-whatsapp" style="font-size:18px;color:#25d366;"></ion-icon>
                        Izin / Kendala (Hubungi Admin)
                    </a>
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
            window.location.href = '{{ $dashRoute }}';
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
            sekolahMarker.bindPopup('<b>🏢 ' + GEOFENCE_NAMA + '</b><br>Titik Lokasi PKBM Pikat (Batas Maksimal: ' + GEOFENCE_RADIUS + ' meter)');

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
                        badge.innerHTML = 'ℹ️ <b>Bebas Batas Jarak (Khusus Kunjungan / Daring)</b> — Jarak dari gedung sekolah: ' + distFormatted + ' m';
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
                    var isFake = false;
                    var accuracy = pos.coords.accuracy || 0;

                    // Simpan data akurasi & mock location ke hidden input form
                    var akurasiEls = document.querySelectorAll('input[name="lokasi_akurasi"]');
                    var mockEls = document.querySelectorAll('input[name="is_mock_location"]');

                    akurasiEls.forEach(function(el) { el.value = accuracy; });

                    // Deteksi heuristik Fake GPS
                    if (pos.coords.mocked === true) {
                        isFake = true;
                    } else if (accuracy === 0) {
                        isFake = true;
                    } else if (pos.coords.altitude === 0 && pos.coords.altitudeAccuracy === 0 && pos.coords.speed === 0 && pos.coords.heading === 0) {
                        isFake = true;
                    }

                    mockEls.forEach(function(el) { el.value = isFake ? '1' : '0'; });
                    
                    if (isFake) {
                        ph.textContent = 'Terdeteksi penggunaan Fake GPS / Mock Location. Matikan aplikasi Fake GPS Anda!';
                        ph.style.color = '#ef4444';
                        alert('Peringatan: Sistem mendeteksi kemungkinan penggunaan aplikasi Fake GPS atau Mock Location. Harap matikan aplikasi tersebut untuk dapat melanjutkan presensi.');
                        return;
                    }

                    ph.style.color = 'var(--muted)';
                    setMapFromLatLng(pos.coords.latitude, pos.coords.longitude);
                },
                function() {
                    ph.textContent = 'Gagal mengambil lokasi. Pastikan izin GPS aktif.';
                }, {
                    enableHighAccuracy: true,
                    timeout: 12000,
                    maximumAge: 0
                }
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
                openLiveCamera(true);
            }
        }

        function openLiveCamera(isSwitching = false) {
            if (!isSwitching) {
                var checkboxes = document.querySelectorAll('.siswa-checkbox');
                if (checkboxes.length > 0) {
                    var isChecked = Array.from(checkboxes).some(cb => cb.checked);
                    if (!isChecked) {
                        alert('Pilih siswa terlebih dahulu.');
                        return;
                    }
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


        function toggleModaDaring(val) {
            var box = document.getElementById('boxLinkDaring');
            if (box) {
                box.style.display = (val === 'online') ? 'block' : 'none';
            }
        }

        // ── Integrasi Metode (Moda) dengan Pilihan Durasi Sesi SK ──
        function handleModaChange(val) {
            var boxLink = document.getElementById('boxLinkDaring');
            var boxGabungan = document.getElementById('boxGabungan');
            var selectDurasi = document.getElementById('selectDurasi');
            var labelLayanan = document.getElementById('labelKategoriLayanan');
            var inputGabungan = document.getElementById('inputIsGabungan');

            if (val === 'online') {
                // Pembelajaran Online -> Distance Learning (DL)
                if (boxLink) boxLink.style.display = 'block';
                if (boxGabungan) {
                    boxGabungan.style.display = 'none';
                    if (inputGabungan) inputGabungan.checked = false;
                }
                if (labelLayanan) {
                    labelLayanan.innerText = 'Distance Learning (DL)';
                    labelLayanan.style.background = '#e0f2fe';
                    labelLayanan.style.color = '#0369a1';
                }
                if (selectDurasi) {
                    selectDurasi.innerHTML = '<option value="1.5" selected>Durasi 1,5 Jam (Standar Distance Learning / DL)</option>';
                }
            } else {
                // Tatap Muka (Sekolah / Kunjungan Rumah) -> Tutorial Komunitas
                if (boxLink) boxLink.style.display = 'none';
                if (boxGabungan) boxGabungan.style.display = 'block';
                if (labelLayanan) {
                    labelLayanan.innerText = 'Tutorial Komunitas';
                    labelLayanan.style.background = '#e0e7ff';
                    labelLayanan.style.color = '#3730a3';
                }
                if (selectDurasi) {
                    var cur = selectDurasi.value;
                    selectDurasi.innerHTML = '<option value="2.0"' + (cur === '3.0' ? '' : ' selected') + '>Durasi 2 Jam (Standar Tutorial Komunitas)</option>' +
                                             '<option value="3.0"' + (cur === '3.0' ? ' selected' : '') + '>Durasi 3 Jam (Tutorial Komunitas Panjang)</option>';
                }
            }
        }

        // ── Searchable Siswa Selection Logic ──
        function filterSiswaList(query) {
            var q = (query || '').toLowerCase().trim();
            var items = document.querySelectorAll('.siswa-item-row');
            var clearBtn = document.getElementById('btnClearSiswaSearch');
            var noMatch = document.getElementById('noMatchSiswa');
            var visibleCount = 0;

            if (clearBtn) {
                clearBtn.style.display = q ? 'block' : 'none';
            }

            items.forEach(function(item) {
                var searchData = item.getAttribute('data-search') || '';
                if (!q || searchData.indexOf(q) !== -1) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (noMatch) {
                noMatch.style.display = (visibleCount === 0) ? 'block' : 'none';
            }
        }

        function clearSiswaSearch() {
            var input = document.getElementById('inputSearchSiswa');
            if (input) {
                input.value = '';
                filterSiswaList('');
                input.focus();
            }
        }

        function updateSelectedSiswaCount() {
            var checked = document.querySelectorAll('.siswa-checkbox:checked');
            var countLabel = document.getElementById('selectedSiswaCount');
            if (countLabel) {
                countLabel.innerText = checked.length;
            }
        }

        function selectAllVisibleSiswa(status) {
            var items = document.querySelectorAll('.siswa-item-row');
            items.forEach(function(item) {
                if (item.style.display !== 'none') {
                    var cb = item.querySelector('.siswa-checkbox');
                    if (cb) cb.checked = status;
                }
            });
            updateSelectedSiswaCount();
        }

        document.addEventListener('DOMContentLoaded', function() {
            var selectModa = document.getElementById('selectModa');
            if (selectModa) {
                handleModaChange(selectModa.value);
            }
            updateSelectedSiswaCount();
        });

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
