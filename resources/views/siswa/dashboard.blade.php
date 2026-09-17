@extends('layouts.presensi')

@section('title', 'Dashboard Siswa')

@php
    $user = auth()->user();
    $siswa = $user->siswa;
    $displayName = (string) ($siswa->nama_siswa ?? ($siswa->nama_lengkap ?? ($user->nama_lengkap ?? ($user->name ?? 'Siswa PKBM'))));
    $subTitle = 'Siswa PKBM Pikat';
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
                <div class="laporanHeaderLabel">PORTAL PEMBELAJARAN &amp; PRESENSI SISWA</div>
                <h1 class="laporanHeaderTitle">Dashboard Siswa</h1>
                <div class="laporanHeaderSub">Selamat datang kembali, {{ $displayName }}!</div>
                <p class="laporanHeaderDesc">Lakukan absensi mandiri saat berada di PKBM Pikat dan pantau catatan kehadiran Anda.</p>
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
        {{-- ── KOLOM KIRI: WAKTU, STATUS HARI INI & INFO KELAS ── --}}
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
                    'proses' => 'Sedang Hadir di PKBM (Sudah Masuk)',
                    'selesai' => 'Selesai Hadir Hari Ini (Sudah Pulang)',
                    default => 'Belum Melakukan Absensi Hari Ini',
                };
                $jamMasukToday = $todayPresensi?->jam_masuk ? substr((string) $todayPresensi->jam_masuk, 0, 5) : '—';
                $jamPulangToday = $todayPresensi?->jam_pulang ? substr((string) $todayPresensi->jam_pulang, 0, 5) : '—';
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

            {{-- ── INFORMASI SISWA & KELAS ── --}}
            @if ($siswa)
                <div class="magangInfoCard">
                    <div class="magangInfoHead">
                        <div class="magangInfoIcon">
                            <ion-icon name="school-outline"></ion-icon>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="magangInfoTitle">
                                {{ $siswa->kelas?->nama_kelas ?? 'Kelas Siswa' }}
                            </div>
                            <div class="magangInfoSub">
                                {{ $siswa->masterJenjang?->nama_jenjang ?? $siswa->jenjang_paket_label }} • NISN: {{ $siswa->nisn ?? ($siswa->no_absen ?? $user->nik) }}
                            </div>
                        </div>
                    </div>
                    <div class="magangInfoGrid">
                        <div class="magangInfoItem">
                            <span class="magangInfoItemLbl">Status Siswa</span>
                            <span class="magangInfoItemVal">
                                <span class="badge {{ $siswa->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}" style="font-size: 0.75rem; padding: 2px 8px; border-radius: 9999px;">
                                    {{ ucfirst($siswa->status ?? 'Aktif') }}
                                </span>
                            </span>
                        </div>
                        <div class="magangInfoItem">
                            <span class="magangInfoItemLbl">Jenis Kelamin</span>
                            <span class="magangInfoItemVal">
                                {{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : ($siswa->jenis_kelamin === 'P' ? 'Perempuan' : '—') }}
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
                    $jamMasukDt = \Carbon\Carbon::parse($today . ' ' . $activeSesi->jam_masuk, 'Asia/Jakarta');
                    $nowDt = \Carbon\Carbon::now('Asia/Jakarta');
                    $diffDetik = $jamMasukDt->diffInSeconds($nowDt, false);
                    $bisaPulang = $diffDetik >= 900; // 15 menit minimal
                    $sisaDetik = max(0, 900 - $diffDetik);
                @endphp
                <div class="activeCard">
                    <div class="activePulse"></div>
                    <div class="activeBody">
                        <div class="activeTitle">Sesi Hadir Sedang Berlangsung</div>
                        <div class="activeSub">
                            Masuk pukul <strong>{{ substr((string) $activeSesi->jam_masuk, 0, 5) }} WIB</strong>
                        </div>
                        <div class="activeLoc">
                            <ion-icon name="location-outline"></ion-icon>
                            <span>Lokasi Masuk Terverifikasi</span>
                        </div>
                    </div>
                    <a href="{{ route('siswa.presensi.foto') }}" class="activeActionBtn">
                        <ion-icon name="camera-outline"></ion-icon>
                        <span>{{ $bisaPulang ? 'Absen Pulang Sekarang' : 'Absen Pulang' }}</span>
                    </a>
                </div>
            @elseif ($todayStatus !== 'selesai')
                <div class="magangCtaCard">
                    <div class="magangCtaTitle">
                        Sudah Tiba di Lokasi Belajar?
                    </div>
                    <div class="magangCtaDesc">
                        Ambil foto selfie presensi masuk dan pastikan Anda berada di area PKBM Pikat.
                    </div>
                    <a href="{{ route('siswa.presensi.foto') }}" class="magangCtaBtn">
                        <ion-icon name="camera-outline" class="icon-md"></ion-icon>
                        <span>Absen Masuk Sekarang</span>
                    </a>
                </div>
            @else
                <div class="statusBanner ready mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <ion-icon name="checkmark-done-circle" style="font-size: 1.5rem; color: #10b981;"></ion-icon>
                        <div>
                            <div class="statusTitle" style="color: #065f46; font-weight: 600;">Presensi Hari Ini Selesai</div>
                            <div class="statusSub" style="color: #047857; font-size: 0.85rem;">Terima kasih, Anda telah menyelesaikan absensi masuk dan pulang hari ini.</div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── STATISTIK KEHADIRAN BULAN INI ── --}}
            <div class="cardBox">
                <div class="cardHeadRow">
                    <h2>
                        <ion-icon name="pie-chart-outline" class="text-primary"></ion-icon>
                        <span>Kehadiran ({{ \Carbon\Carbon::parse($today)->translatedFormat('F Y') }})</span>
                    </h2>
                </div>
                <div class="statsGrid">
                    <div class="statCard">
                        <div class="statIcon success"><ion-icon name="finger-print-outline"></ion-icon></div>
                        <div class="statValue">{{ $hadirBulanIni }}</div>
                        <div class="statLabel">Absen Mandiri</div>
                    </div>
                    <div class="statCard">
                        <div class="statIcon primary"><ion-icon name="book-outline"></ion-icon></div>
                        <div class="statValue">{{ $hadirSesiKelas }}</div>
                        <div class="statLabel">Sesi Kelas</div>
                    </div>
                    <div class="statCard">
                        <div class="statIcon warning"><ion-icon name="checkmark-done-circle-outline"></ion-icon></div>
                        <div class="statValue">{{ $totalHadirBulanIni }}</div>
                        <div class="statLabel">Total Hadir</div>
                    </div>
                </div>
            </div>

            {{-- ── RIWAYAT TERAKHIR ── --}}
            <div class="cardBox">
                <div class="cardHeadRow">
                    <h2>
                        <ion-icon name="time-outline" class="text-primary"></ion-icon>
                        <span>Riwayat Absen Mandiri Terbaru</span>
                    </h2>
                    <a href="{{ route('siswa.riwayat') }}" class="mutedLink">Lihat Semua &rsaquo;</a>
                </div>

                <div class="recentWrap">
                    @forelse($recentPresensi as $p)
                        @php
                            $tgl = \Carbon\Carbon::parse($p->tgl_presensi);
                            $hari = $tgl->translatedFormat('l, d M Y');
                            $masuk = $p->jam_masuk ? substr((string)$p->jam_masuk, 0, 5) : '—';
                            $pulang = $p->jam_pulang ? substr((string)$p->jam_pulang, 0, 5) : '—';
                        @endphp
                        <div class="recentItem">
                            <div class="recentLeft">
                                <div class="recentCheck {{ ($p->foto_masuk && !$p->foto_pulang) ? 'pending' : '' }}">
                                    <ion-icon name="{{ $p->foto_pulang ? 'checkmark' : 'time-outline' }}"
                                        class="text-md"></ion-icon>
                                </div>
                                <div class="recentMeta">
                                    <div class="recentDay">{{ $hari }}</div>
                                    <div class="recentTime">
                                        Masuk: {{ $masuk }} • Pulang: {{ $pulang }}
                                    </div>
                                </div>
                            </div>
                            <div>
                                <span class="pillSmall {{ $p->foto_pulang ? 'pillOk' : 'pillPending' }}">
                                    {{ $p->foto_pulang ? 'Selesai' : 'Hadir (Belum Pulang)' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="emptyState m-0 p-4">
                            Belum ada riwayat absensi mandiri bulan ini.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    @push('head')
        <script>
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
