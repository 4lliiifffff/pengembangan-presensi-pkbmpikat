@extends('layouts.presensi')

@section('title', 'Dashboard Siswa')

@php
    $user = auth()->user();
    $siswa = $user->siswa;
    $displayName = (string) ($siswa->nama_siswa ?? ($siswa->nama_lengkap ?? ($user->nama_lengkap ?? ($user->name ?? 'Siswa PKBM'))));
    $subTitle = 'Portal Peserta Didik • PKBM Pikat';
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
                <p class="laporanHeaderDesc">Catat kehadiran mandiri Anda saat tiba di PKBM Pikat dan pantau jadwal pembelajaran serta agenda kegiatan.</p>
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
        {{-- ── KOLOM KIRI: WAKTU, STATUS PRESENSI HARI INI & INFORMASI SISWA ── --}}
        <div class="dashboardCol">
            {{-- ── JAM REALTIME ── --}}
            <div class="clockCard">
                <div class="clockLabel">WAKTU SAAT INI</div>
                <div class="clockTime" id="clockTime">--:--:--</div>
                <div class="clockTz">Waktu Indonesia Barat (WIB)</div>
            </div>

            {{-- ── STATUS PRESENSI HARI INI (SINGLE CHECK-IN) ── --}}
            @php
                $isHadir = ($todayStatus === 'selesai' && $todayPresensi);
                $statusClass = $isHadir ? 'selesai' : 'belum';
                $statusIcon = $isHadir ? 'checkmark-circle-outline' : 'radio-button-off-outline';
                $statusText = $isHadir ? 'Sudah Hadir di PKBM Hari Ini' : 'Belum Melakukan Absensi Hari Ini';
                $jamMasukToday = $todayPresensi?->jam_masuk ? substr((string) $todayPresensi->jam_masuk, 0, 5) . ' WIB' : 'Belum Absen';
                $lokasiMasukToday = $todayPresensi?->lokasiPresensi?->nama_lokasi ?? ($isHadir ? 'Gedung Utama PKBM Pikat' : '—');
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
                            <span class="todayTimeLbl">Jam Kedatangan</span>
                        </div>
                        <div class="todayTimeChip">
                            <span class="todayTimeVal" title="{{ $lokasiMasukToday }}">{{ \Illuminate\Support\Str::limit($lokasiMasukToday, 20) }}</span>
                            <span class="todayTimeLbl">Lokasi Belajar</span>
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
                                {{ $siswa->masterJenjang?->nama_jenjang ?? $siswa->jenjang_paket_label }} • No. Absen: {{ $siswa->no_absen ?? '—' }}
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
                            <span class="magangInfoItemLbl">NISN / NIK</span>
                            <span class="magangInfoItemVal">
                                {{ $siswa->nisn ?? ($user->nik ?? '—') }}
                            </span>
                        </div>
                        <div class="magangInfoItem">
                            <span class="magangInfoItemLbl">Nama Wali</span>
                            <span class="magangInfoItemVal">
                                {{ $siswa->nama_wali ?? '—' }}
                            </span>
                        </div>
                        <div class="magangInfoItem">
                            <span class="magangInfoItemLbl">Kebutuhan Khusus</span>
                            <span class="magangInfoItemVal">
                                <span class="badge {{ $siswa->is_abk ? 'bg-warning' : 'bg-light text-dark' }}" style="font-size: 0.75rem; padding: 2px 8px; border-radius: 9999px;">
                                    {{ $siswa->is_abk ? 'ABK (Inklusi)' : 'Reguler' }}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── AGENDA KEGIATAN PKBM MENDATANG ── --}}
            @if(isset($agendaMendatang) && $agendaMendatang->isNotEmpty())
                <div class="cardBox mt-4">
                    <div class="cardHeadRow">
                        <h2>
                            <ion-icon name="calendar-clear-outline" class="text-primary"></ion-icon>
                            <span>Agenda &amp; Jadwal PKBM</span>
                        </h2>
                        <a href="{{ route('siswa.jadwal') }}" class="text-xs font-bold text-primary text-decoration-none">
                            Lihat Kalender &rsaquo;
                        </a>
                    </div>
                    <div class="recentWrap">
                        @foreach($agendaMendatang as $agenda)
                            @php
                                $tglAgenda = \Carbon\Carbon::parse($agenda->tanggal);
                                $isHariIni = $tglAgenda->isToday();
                            @endphp
                            <div class="recentItem">
                                <div class="recentLeft">
                                    <div class="recentCheck {{ $isHariIni ? '' : 'pending' }}">
                                        <ion-icon name="{{ $isHariIni ? 'flag' : 'calendar-outline' }}" class="text-md"></ion-icon>
                                    </div>
                                    <div class="recentMeta">
                                        <div class="recentDay font-bold text-dark">{{ $agenda->judul }}</div>
                                        <div class="recentTime text-xs">
                                            {{ $tglAgenda->translatedFormat('d M Y') }} • {{ $agenda->lokasi ?: 'PKBM Pikat' }}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <span class="pillSmall {{ $isHariIni ? 'pillOk' : 'pillPending' }}">
                                        {{ $isHariIni ? 'Hari Ini' : $tglAgenda->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- ── KOLOM KANAN: CALL TO ACTION PRESENSI, JADWAL SESI & STATISTIK ── --}}
        <div class="dashboardCol">
            {{-- ── HERO CARD STATUS PRESENSI HARI INI ── --}}
            @if ($isHadir)
                <div class="statusBanner ready mb-4 bg-success-light" style="padding: 18px 20px; border-radius: 16px; border: 1px solid #10b98133;">
                    <div class="d-flex align-items-center justify-content-between w-full flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            @if ($todayPresensi->foto_masuk_url)
                                <img src="{{ $todayPresensi->foto_masuk_url }}" alt="Selfie Masuk" class="rounded-xl border shadow-sm" style="width: 52px; height: 52px; object-fit: cover;">
                            @else
                                <div class="avatar-md bg-success text-white rounded-xl d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; font-size: 1.4rem;">
                                    <ion-icon name="checkmark-done"></ion-icon>
                                </div>
                            @endif
                            <div>
                                <div class="statusTitle font-bold text-dark" style="font-size: 1.05rem; color: #065f46;">Kehadiran Hari Ini Selesai</div>
                                <div class="statusSub text-xs text-muted mt-1">
                                    Tercatat pukul <strong>{{ substr((string)$todayPresensi->jam_masuk, 0, 5) }} WIB</strong> di {{ $lokasiMasukToday }}
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('siswa.presensi.foto') }}" class="btn btn-sm btn-light px-3 py-2 rounded-lg font-bold text-xs">
                            Lihat Kartu Presensi &rsaquo;
                        </a>
                    </div>
                </div>
            @else
                <div class="magangCtaCard mb-4">
                    <div class="magangCtaTitle">
                        Sudah Tiba di Lokasi Belajar?
                    </div>
                    <div class="magangCtaDesc">
                        Ambil foto selfie kehadiran mandiri untuk mencatat kehadiran Anda hari ini di PKBM Pikat.
                    </div>
                    <a href="{{ route('siswa.presensi.foto') }}" class="magangCtaBtn">
                        <ion-icon name="camera-outline" class="icon-md"></ion-icon>
                        <span>Absen Masuk Sekarang</span>
                    </a>
                </div>
            @endif

            {{-- ── JADWAL SESI BELAJAR BERSAMA TUTOR HARI INI ── --}}
            @if(isset($jadwalSesiHariIni) && $jadwalSesiHariIni->isNotEmpty())
                <div class="cardBox mb-4">
                    <div class="cardHeadRow">
                        <h2>
                            <ion-icon name="book-outline" class="text-primary"></ion-icon>
                            <span>Jadwal Sesi Belajar Hari Ini</span>
                        </h2>
                        <a href="{{ route('siswa.jadwal') }}" class="text-xs font-bold text-primary text-decoration-none">
                            Lihat Semua &rsaquo;
                        </a>
                    </div>
                    <div class="recentWrap">
                        @foreach($jadwalSesiHariIni as $sesi)
                            @php
                                $jamMulai = substr((string)$sesi->jam_masuk_rencana, 0, 5);
                                $jamSelesai = substr((string)$sesi->jam_pulang_rencana, 0, 5);
                                $tutorNama = $sesi->tutor?->nama_lengkap ?? 'Tutor Pengajar';
                                $katNama = $sesi->kategoriTutorial?->nama_kategori ?? 'Tutorial KBM';
                                $isHadirSesi = ($sesi->status_kehadiran_siswa === 'hadir');
                            @endphp
                            <div class="recentItem">
                                <div class="recentLeft">
                                    <div class="recentCheck {{ $isHadirSesi ? '' : 'pending' }}">
                                        <ion-icon name="{{ $isHadirSesi ? 'checkmark-circle' : 'time-outline' }}" class="text-md"></ion-icon>
                                    </div>
                                    <div class="recentMeta">
                                        <div class="recentDay font-bold text-dark">{{ $katNama }} • {{ $jamMulai }} - {{ $jamSelesai }} WIB</div>
                                        <div class="recentTime text-xs">
                                            Pengajar: <strong>{{ $tutorNama }}</strong> • Sesi {{ ucfirst($sesi->jenis_sesi) }}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <span class="pillSmall {{ $isHadirSesi ? 'pillOk' : 'pillPending' }}">
                                        {{ $isHadirSesi ? 'Hadir' : 'Terjadwal' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ── STATISTIK KEHADIRAN BULAN INI ── --}}
            <div class="cardBox mb-4">
                <div class="cardHeadRow">
                    <h2>
                        <ion-icon name="pie-chart-outline" class="text-primary"></ion-icon>
                        <span>Kehadiran Bulan Ini ({{ \Carbon\Carbon::parse($today)->translatedFormat('F Y') }})</span>
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
                        <span>Riwayat Kehadiran Terbaru</span>
                    </h2>
                    <a href="{{ route('siswa.riwayat') }}" class="mutedLink">Lihat Semua &rsaquo;</a>
                </div>

                <div class="recentWrap">
                    @forelse($recentPresensi as $p)
                        @php
                            $tgl = \Carbon\Carbon::parse($p->tgl_presensi);
                            $hari = $tgl->translatedFormat('l, d M Y');
                            $masuk = $p->jam_masuk ? substr((string)$p->jam_masuk, 0, 5) : '—';
                            $lokasi = $p->lokasiPresensi?->nama_lokasi ?? 'PKBM Pikat';
                        @endphp
                        <div class="recentItem">
                            <div class="recentLeft">
                                <div class="recentCheck">
                                    <ion-icon name="checkmark" class="text-md"></ion-icon>
                                </div>
                                <div class="recentMeta">
                                    <div class="recentDay font-semibold text-dark">{{ $hari }}</div>
                                    <div class="recentTime text-xs text-muted">
                                        Masuk: <strong>{{ $masuk }} WIB</strong> • {{ $lokasi }}
                                    </div>
                                </div>
                            </div>
                            <div>
                                <span class="pillSmall pillOk">
                                    Hadir
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="emptyState m-0 p-4 text-center text-muted text-sm">
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
