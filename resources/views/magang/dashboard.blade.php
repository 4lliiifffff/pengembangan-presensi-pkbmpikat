@extends('layouts.presensi')

@section('title', 'Dashboard Magang / PKL')

@php
    $user = auth()->user();
    $magang = $user->magang;
    $displayName = (string) ($user->nama_lengkap ?? ($user->name ?? 'Mahasiswa Magang'));
    $subTitle = 'Mahasiswa/Siswa Magang';
    $initial = strtoupper(substr($displayName, 0, 1));
@endphp

@push('topbar')
    @include('layouts.components.navigasi_atas', [
        'titleName' => $displayName,
        'subTitle'  => $subTitle,
    ])
@endpush

@section('content')
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">PORTAL PRAKTIK KERJA &amp; MAGANG</div>
                <h1 class="laporanHeaderTitle">Dashboard Magang / PKL</h1>
                <div class="laporanHeaderSub">Selamat datang kembali, {{ $displayName }}!</div>
                <p class="laporanHeaderDesc">Lakukan presensi harian masuk dan pulang, serta pantau riwayat kehadiran praktik kerja di PKBM.</p>
            </div>
            <div class="laporanHeaderActions">
                <div class="badgeDate">
                    <ion-icon name="calendar-outline"></ion-icon>
                    <span>{{ \Carbon\Carbon::parse($today)->translatedFormat('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboardGrid">
        {{-- ── KOLOM KIRI: WAKTU, STATUS HARI INI & INFO INSTANSI ── --}}
        <div class="dashboardCol">
            {{-- ── JAM REALTIME ── --}}
            <div class="clockCard">
                <div class="clockLabel">WAKTU SAAT INI</div>
                <div class="clockTime" id="clockTime">--:--:--</div>
                <div class="clockTz">Waktu Indonesia Barat (WIB)</div>
            </div>

            {{-- ── STATUS PRESENSI HARI INI ── --}}
            @php
                $statusClass = match ($todayStatus) {
                    'proses' => 'proses',
                    'selesai' => 'selesai',
                    default => 'belum',
                };
                $statusIcon = match ($todayStatus) {
                    'proses' => 'time-outline',
                    'selesai' => 'checkmark-circle-outline',
                    default => 'radio-button-off-outline',
                };
                $statusText = match ($todayStatus) {
                    'proses' => 'Sedang Berjalan (Sudah Masuk)',
                    'selesai' => 'Selesai Hari Ini (Sudah Pulang)',
                    default => 'Belum Presensi Hari Ini',
                };
                $jamMasukToday = $todayPresensi?->jam_mulai ? substr((string) $todayPresensi->jam_mulai, 0, 5) : '—';
                $jamPulangToday = $todayPresensi?->jam_selesai ? substr((string) $todayPresensi->jam_selesai, 0, 5) : '—';
            @endphp

            <div class="todayCard {{ $statusClass }}">
                <div class="todayIcon {{ $statusClass }}">
                    <ion-icon name="{{ $statusIcon }}"></ion-icon>
                </div>
                <div class="todayBody">
                    <div class="todayStatusLabel {{ $statusClass }}">{{ $statusText }}</div>
                    <div class="todayTimeRow">
                        <div class="todayTimeChip">
                            <span class="todayTimeVal">{{ $jamMasukToday }}</span>
                            <span class="todayTimeLbl">Jam Masuk</span>
                        </div>
                        <div class="todayTimeChip">
                            <span class="todayTimeVal">{{ $jamPulangToday }}</span>
                            <span class="todayTimeLbl">Jam Pulang</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── INFORMASI MAGANG / INSTANSI ── --}}
            @if ($magang)
                <div class="magangInfoCard">
                    <div class="magangInfoHead">
                        <div class="magangInfoIcon">
                            <ion-icon name="school-outline"></ion-icon>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="magangInfoTitle">
                                {{ $magang->asal_instansi }}
                            </div>
                            <div class="magangInfoSub">
                                {{ $magang->jurusan ?: 'Peserta Magang' }} • {{ $magang->nim_nisn ?: $user->nik }}
                            </div>
                        </div>
                    </div>
                    <div class="magangInfoGrid">
                        <div class="magangInfoItem">
                            <span class="magangInfoItemLbl">Mulai Magang</span>
                            <span class="magangInfoItemVal">
                                {{ $magang->tgl_mulai ? \Carbon\Carbon::parse($magang->tgl_mulai)->translatedFormat('d M Y') : '—' }}
                            </span>
                        </div>
                        <div class="magangInfoItem">
                            <span class="magangInfoItemLbl">Selesai Magang</span>
                            <span class="magangInfoItemVal">
                                {{ $magang->tgl_selesai ? \Carbon\Carbon::parse($magang->tgl_selesai)->translatedFormat('d M Y') : '—' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ── KOLOM KANAN: SESI AKTIF/CTA, STATISTIK & RIWAYAT ── --}}
        <div class="dashboardCol">
            {{-- ── CARD SESI AKTIF ATAU TOMBOL PRESENSI ── --}}
            @if ($activeSesi)
                @php
                    $jamMulaiDt = \Carbon\Carbon::parse($today . ' ' . $activeSesi->jam_mulai, 'Asia/Jakarta');
                    $nowDt = \Carbon\Carbon::now('Asia/Jakarta');
                    $diffDetik = $jamMulaiDt->diffInSeconds($nowDt, false);
                    $bisaPulang = $diffDetik >= 3600;
                    $sisaDetik = max(0, 3600 - $diffDetik);
                @endphp
                <div class="activeCard">
                    <div class="activePulse"></div>
                    <div class="activeBody">
                        <div class="activeTitle">Sesi Magang Sedang Berlangsung</div>
                        <div class="activeSub">
                            Masuk pukul <strong >{{ substr((string) $activeSesi->jam_mulai, 0, 5) }} WIB</strong>
                        </div>
                        <div class="activeLoc">
                            <ion-icon name="location-outline"></ion-icon>
                            <span >Lokasi Masuk Terverifikasi (PKBM Pikat)</span>
                        </div>
                    </div>
                    <a href="{{ route('magang.presensi.foto') }}" class="activeActionBtn">
                        <ion-icon name="camera-outline"></ion-icon>
                        <span >{{ $bisaPulang ? 'Absen Pulang Sekarang' : 'Absen Pulang' }}</span>
                    </a>
                </div>
            @elseif ($todayStatus !== 'selesai')
                <div class="magangCtaCard">
                    <div class="magangCtaTitle">
                        Siap Memulai Aktivitas Magang?
                    </div>
                    <div class="magangCtaDesc">
                        Ambil foto selfie presensi masuk dan pastikan Anda berada di area PKBM Pikat.
                    </div>
                    <a href="{{ route('magang.presensi.foto') }}" class="magangCtaBtn">
                        <ion-icon name="camera-outline" class="icon-md"></ion-icon>
                        <span >Absen Masuk Sekarang</span>
                    </a>
                </div>
            @endif

            {{-- ── STATISTIK KEHADIRAN BULAN INI ── --}}
            <div class="cardBox">
                <div class="cardHeadRow">
                    <h2 >
                        <ion-icon name="pie-chart-outline" class="text-primary"></ion-icon>
                        <span >Statistik Kehadiran ({{ \Carbon\Carbon::parse($today)->translatedFormat('F Y') }})</span>
                    </h2>
                </div>
                <div class="statsGrid">
                    <div class="statCard">
                        <div class="statIcon success"><ion-icon name="checkmark-done-circle-outline"></ion-icon></div>
                        <div class="statValue">{{ $hadirBulanIni }}</div>
                        <div class="statLabel">Hari Hadir</div>
                    </div>
                    <div class="statCard">
                        <div class="statIcon primary"><ion-icon name="calendar-outline"></ion-icon></div>
                        <div class="statValue">{{ $totalHariKerja }}</div>
                        <div class="statLabel">Hari Kerja</div>
                    </div>
                    <div class="statCard">
                        <div class="statIcon warning"><ion-icon name="pie-chart-outline"></ion-icon></div>
                        <div class="statValue">{{ $persentaseKehadiran }}%</div>
                        <div class="statLabel">Kehadiran</div>
                    </div>
                </div>
            </div>

            {{-- ── RIWAYAT TERAKHIR ── --}}
            <div class="cardBox">
                <div class="cardHeadRow">
                    <h2 >
                        <ion-icon name="time-outline" class="text-primary"></ion-icon>
                        <span >Riwayat Presensi Terbaru</span>
                    </h2>
                    @if (Route::has('magang.riwayat'))
                        <a href="{{ route('magang.riwayat') }}" class="mutedLink">Lihat Semua &rsaquo;</a>
                    @endif
                </div>

                <div class="recentWrap">
                    @forelse($recentPresensi as $p)
                        @php
                            $tgl = \Carbon\Carbon::parse($p->tgl_presensi);
                            $hari = $tgl->translatedFormat('l, d M Y');
                            $masuk = $p->jam_mulai ? substr((string)$p->jam_mulai, 0, 5) : '—';
                            $pulang = $p->jam_selesai ? substr((string)$p->jam_selesai, 0, 5) : '—';
                        @endphp
                        <div class="recentItem">
                            <div class="recentLeft">
                                <div class="recentCheck {{ ($p->foto_mulai && !$p->foto_selesai) ? 'pending' : '' }}">
                                    <ion-icon name="{{ $p->foto_selesai ? 'checkmark' : 'time-outline' }}"
                                        class="text-md"></ion-icon>
                                </div>
                                <div class="recentMeta">
                                    <div class="recentDay">{{ $hari }}</div>
                                    <div class="recentTime">
                                        Masuk: {{ $masuk }} • Pulang: {{ $pulang }}
                                    </div>
                                </div>
                            </div>
                            <div >
                                <span class="pillSmall {{ $p->foto_selesai ? 'pillOk' : 'pillPending' }}">
                                    {{ $p->foto_selesai ? 'Selesai' : 'Proses' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="tableEmptyState p-4">
                            <div class="tableEmptyIconWrap indigo" style="width: 44px; height: 44px; font-size: 20px; margin-bottom: 8px;">
                                <ion-icon name="time-outline" class="tableEmptyIcon"></ion-icon>
                            </div>
                            <div class="tableEmptyTitle" style="font-size: 13.5px;">Belum Ada Riwayat Presensi</div>
                            <div class="tableEmptyDesc" style="font-size: 11.5px;">Log presensi magang Anda bulan ini akan tampil di sini.</div>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    @push('head')
        <script >
            function updateLiveClock() {
                const clockEl = document.getElementById('clockTime');
                if (!clockEl) return;
                const now = new Date();
                const pad = (n) => String(n).padStart(2, '0');
                clockEl.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
            }
            setInterval(updateLiveClock, 1000);
            updateLiveClock();
        </script>
    @endpush
@endsection
