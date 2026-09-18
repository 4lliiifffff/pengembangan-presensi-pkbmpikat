@extends('layouts.presensi')

@section('title', 'Jadwal & Agenda Kegiatan Siswa')

@php
    use Carbon\Carbon;
    $user = auth()->user();
    $displayName = (string) ($siswa->nama_siswa ?? ($siswa->nama_lengkap ?? ($user->nama_lengkap ?? ($user->name ?? 'Siswa PKBM'))));
    $initial = strtoupper(substr($displayName, 0, 1));

    $tz = 'Asia/Jakarta';
    $todayDate = Carbon::today($tz);
    $selectedDate = $selectedDate ?? Carbon::parse(request('tanggal', $todayDate->toDateString()))->startOfDay();

    // Hitung tanggal navigasi bulan sebelumnya dan berikutnya
    $prevMonthDate = $selectedDate->copy()->subMonth()->toDateString();
    $nextMonthDate = $selectedDate->copy()->addMonth()->toDateString();

    // Hitung offset hari awal bulan (1 = Senin, 7 = Minggu)
    $firstDayOfMonth = $selectedDate->copy()->startOfMonth();
    $startDayOfWeek = $firstDayOfMonth->dayOfWeekIso; // 1 (Mon) to 7 (Sun)
    $emptyCellsCount = $startDayOfWeek - 1;

    $dayNames = [
        ['code' => 'SEN', 'sunday' => false],
        ['code' => 'SEL', 'sunday' => false],
        ['code' => 'RAB', 'sunday' => false],
        ['code' => 'KAM', 'sunday' => false],
        ['code' => 'JUM', 'sunday' => false],
        ['code' => 'SAB', 'sunday' => true],
        ['code' => 'MIN', 'sunday' => true],
    ];

    $hasSesis = isset($jadwalSesis) && $jadwalSesis->count() > 0;
    $hasAgendas = isset($agendas) && $agendas->count() > 0;
    $hasActivities = $hasSesis || $hasAgendas;
    $isToday = $selectedDate->isToday();
    $isSudahAbsenHariIni = !empty($todayPresensi);
@endphp

@push('topbar')
    @include('layouts.components.navigasi_atas', [
        'titleName' => 'Jadwal & Agenda',
        'subTitle' => $displayName . ' • Siswa PKBM',
        'dashRoute' => route('siswa.dashboard'),
        'backRoute' => route('siswa.dashboard'),
    ])
@endpush

@section('content')

    <div class="laporanPageWrapper">
        {{-- ── Header Card ── --}}
        <div class="laporanHeader">
            <div class="laporanHeaderCard">
                <div class="laporanHeaderInfo">
                    <div class="laporanHeaderLabel">JADWAL BELAJAR &amp; AGENDA KEGIATAN</div>
                    <h1 class="laporanHeaderTitle">Jadwal Siswa PKBM</h1>
                    <div class="laporanHeaderSub">
                        {{ $siswa?->kelas?->nama_kelas ?? 'Kelas Siswa' }} • {{ $siswa?->masterJenjang?->nama_jenjang ?? ($siswa?->jenjang_paket_label ?? 'Paket Belajar') }}
                    </div>
                    <p class="laporanHeaderDesc">
                        Pantau sesi belajar tutorial bersama tutor, jadwal kelas pengganti, dan kalender kegiatan resmi PKBM PIKAT.
                    </p>
                </div>
                <div class="laporanHeaderActions">
                    <a href="{{ route('siswa.presensi') }}" class="profileBtnPrimary">
                        <ion-icon name="camera-outline"></ion-icon> Presensi Mandiri
                    </a>
                </div>
            </div>
        </div>

        <div class="max-w-2xl px-0 mx-auto pb-6">

            {{-- ── 1. KALENDER BULANAN (MONTH GRID VIEW) ── --}}
            <div class="agendaHeaderCard">
                <!-- Header Navigasi Bulan -->
                <div class="agendaMonthNav">
                    <a href="{{ route('siswa.jadwal', ['tanggal' => $prevMonthDate]) }}" class="agendaNavBtn"
                        title="Bulan Sebelumnya">
                        <ion-icon name="chevron-back-outline"></ion-icon>
                    </a>
                    <div class="text-center">
                        <h3 class="agendaMonthTitle">{{ $selectedDate->translatedFormat('F Y') }}</h3>
                        <span class="text-xs font-bold text-muted">Kalender Belajar &amp; Kegiatan</span>
                    </div>
                    <a href="{{ route('siswa.jadwal', ['tanggal' => $nextMonthDate]) }}" class="agendaNavBtn"
                        title="Bulan Berikutnya">
                        <ion-icon name="chevron-forward-outline"></ion-icon>
                    </a>
                </div>

                <!-- 7 Kolom Header Hari -->
                <div class="agendaMonthGrid mb-1">
                    @foreach($dayNames as $dName)
                        <div class="agendaDayNameHeader {{ $dName['sunday'] ? 'sunday' : '' }}">
                            {{ $dName['code'] }}
                        </div>
                    @endforeach
                </div>

                <!-- Grid Sel Tanggal Bulanan -->
                <div class="agendaMonthGrid">
                    {{-- Sel kosong sebelum awal bulan --}}
                    @for($i = 0; $i < $emptyCellsCount; $i++)
                        <div class="agendaDayCell empty"></div>
                    @endfor

                    {{-- Sel tanggal dalam bulan --}}
                    @foreach($monthDays as $d)
                        @php
                            $dStr = $d->format('Y-m-d');
                            $isActive = $d->isSameDay($selectedDate);
                            $isCellToday = $d->isToday();
                            $eventCount = (int) ($monthCounts[$dStr] ?? 0);
                            $hasEvent = $eventCount > 0;
                        @endphp
                        <a href="{{ route('siswa.jadwal', ['tanggal' => $dStr]) }}"
                            class="agendaDayCell {{ $isActive ? 'active' : '' }} {{ $isCellToday ? 'today' : '' }} {{ $hasEvent ? 'hasEvent' : '' }}"
                            title="{{ $d->translatedFormat('d F Y') }} ({{ $eventCount }} Kegiatan/Sesi)">
                            <span class="agendaDayNum">{{ $d->day }}</span>
                            @if($hasEvent)
                                <span class="agendaEventIndicatorWrap">
                                    <span class="agendaEventDot"></span>
                                    @if($eventCount > 1)
                                        <span class="agendaEventCountText">{{ $eventCount }}</span>
                                    @endif
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>

                <!-- Legenda Kalender -->
                <div class="agendaCalendarLegend">
                    <div class="agendaLegendItem">
                        <span class="agendaLegendIndicator todayIndicator"></span>
                        <span>Hari Ini</span>
                    </div>
                    <div class="agendaLegendItem">
                        <span class="agendaLegendIndicator eventIndicator"></span>
                        <span>Ada Sesi/Kegiatan</span>
                    </div>
                    <div class="agendaLegendItem">
                        <span class="agendaLegendIndicator activeIndicator"></span>
                        <span>Tanggal Terpilih</span>
                    </div>
                </div>
            </div>

            {{-- ── 2. DAFTAR AKTIVITAS TANGGAL TERPILIH ── --}}
            <div class="agendaBannerBox">
                <div class="agendaBannerHeader">
                    <div class="flex-items-center gap-2">
                        <span class="agendaBannerBadge {{ $isToday ? 'today' : '' }}">
                            {{ $isToday ? 'Aktivitas Hari Ini' : 'Jadwal Terpilih' }}
                        </span>
                        <span class="text-xs font-bold text-primary">
                            {{ $selectedDate->translatedFormat('l, d F Y') }}
                        </span>
                    </div>
                </div>

                @if($hasActivities)
                    {{-- ── A. SESI BELAJAR BERSAMA TUTOR ── --}}
                    @if($hasSesis)
                        <div class="mb-4">
                            <div class="d-flex items-center gap-2 mb-3">
                                <ion-icon name="book-outline" class="text-primary text-lg"></ion-icon>
                                <h3 class="text-sm font-extrabold text-dark m-0">Sesi Belajar &amp; Tutorial</h3>
                                <span class="badge bg-primary text-white text-xs px-2 py-0.5 rounded-full">{{ $jadwalSesis->count() }} Sesi</span>
                            </div>

                            <div class="mobile-card-list">
                                @foreach($jadwalSesis as $sesi)
                                    @php
                                        $tutorNama = $sesi->tutor?->nama_lengkap ?? 'Tutor PKBM';
                                        $katNama = $sesi->kategoriTutorial?->nama_kategori ?? 'Tutorial KBM';
                                        $isHadirSesi = ($sesi->status_kehadiran_siswa === 'hadir');
                                    @endphp
                                    <div class="data-mobile-card mb-3">
                                        <div class="dmc-header">
                                            <div>
                                                <h4 class="dmc-title font-extrabold">{{ $katNama }}</h4>
                                                <div class="dmc-subtitle mt-1">
                                                    <span class="app-badge {{ $sesi->jenis_sesi === 'pengganti' ? 'badge-layanan-dl' : 'badge-layanan-komunitas' }}">
                                                        {{ $sesi->jenis_label }}
                                                    </span>
                                                    <span class="text-xs text-muted ml-1">&bull; Tutor: <strong>{{ $tutorNama }}</strong></span>
                                                </div>
                                            </div>
                                            <div>
                                                @if($isHadirSesi)
                                                    <span class="app-badge badge-status-aktif">Hadir</span>
                                                @elseif($sesi->status === 'berlangsung')
                                                    <span class="app-badge badge-layanan-dl">Sedang Berlangsung</span>
                                                @elseif($sesi->status === 'selesai')
                                                    <span class="app-badge badge-status-aktif">Selesai</span>
                                                @elseif($sesi->status === 'dibatalkan')
                                                    <span class="app-badge badge-status-nonaktif">Dibatalkan</span>
                                                @else
                                                    <span class="app-badge badge-reguler">Terjadwal</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="dmc-grid mt-2">
                                            <div class="dmc-field">
                                                <div class="dmc-label">Jam Belajar</div>
                                                <div class="dmc-value">
                                                    <span class="font-bold text-dark text-sm">{{ $sesi->jam_masuk_formatted }} - {{ $sesi->jam_pulang_formatted }} WIB</span>
                                                    <span class="text-xs text-muted">({{ $sesi->durasi_jam }} Jam)</span>
                                                </div>
                                            </div>

                                            @if($sesi->jadwalKerja)
                                                <div class="dmc-field">
                                                    <div class="dmc-label">Shift / Sesi</div>
                                                    <div class="dmc-value font-semibold text-dark text-xs">
                                                        {{ $sesi->jadwalKerja->nama_shift ?? 'Reguler' }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        @if($sesi->tanggal_asli)
                                            <div class="text-xs text-muted mt-2 pt-2 border-t-base">
                                                <span class="font-semibold text-dark">Menggantikan Sesi:</span> {{ $sesi->tanggal_asli->translatedFormat('d F Y') }}
                                                @if($sesi->alasan_penggantian)
                                                    <br><span class="font-semibold text-dark">Alasan:</span> {{ $sesi->alasan_penggantian }}
                                                @endif
                                            </div>
                                        @endif

                                        @if($sesi->catatan)
                                            <div class="text-xs text-muted mt-1">
                                                <span class="font-semibold text-dark">Catatan:</span> {{ $sesi->catatan }}
                                            </div>
                                        @endif

                                        @if($isToday && !$isSudahAbsenHariIni && $sesi->status !== 'dibatalkan')
                                            <div class="dmc-actions mt-3">
                                                <a href="{{ route('siswa.presensi.foto') }}" class="profileBtnPrimary text-xs py-1.5 px-3 w-full justify-center">
                                                    <ion-icon name="camera-outline"></ion-icon> Absen Masuk Sekarang
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ── B. AGENDA & PENGUMUMAN PKBM ── --}}
                    @if($hasAgendas)
                        <div class="mt-4">
                            <div class="d-flex items-center gap-2 mb-3">
                                <ion-icon name="megaphone-outline" class="text-primary text-lg"></ion-icon>
                                <h3 class="text-sm font-extrabold text-dark m-0">Agenda &amp; Kegiatan Sekolah</h3>
                                <span class="badge bg-secondary text-white text-xs px-2 py-0.5 rounded-full">{{ $agendas->count() }} Kegiatan</span>
                            </div>

                            <div>
                                @foreach($agendas as $j)
                                    @php
                                        $judul = $j->judul ?? 'Agenda Kegiatan';
                                        $deskripsi = $j->deskripsi ?? null;
                                        $lokasi = $j->lokasi ?? null;
                                    @endphp

                                    <div class="agendaCardItem {{ $isToday ? 'today' : '' }}">
                                        <h4 class="text-base font-extrabold text-dark m-0 mb-1">
                                            {{ $judul }}
                                        </h4>

                                        @if($deskripsi)
                                            <div class="text-sm text-muted mb-2">
                                                {{ $deskripsi }}
                                            </div>
                                        @endif

                                        @if($lokasi)
                                            <div class="agendaMetaRow">
                                                <ion-icon name="location-outline" class="text-primary"></ion-icon>
                                                <span><strong class="text-dark">Lokasi:</strong> {{ $lokasi }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                @else
                    <div class="emptyAgendaBox">
                        <div class="emptyAgendaIcon">
                            <ion-icon name="calendar-clear-outline"></ion-icon>
                        </div>
                        <div class="emptyAgendaTitle">Tidak Ada Jadwal atau Agenda</div>
                        <div class="emptyAgendaDesc">
                            Belum ada sesi tutorial belajar ataupun agenda kegiatan resmi PKBM pada <strong>{{ $selectedDate->translatedFormat('l, d F Y') }}</strong>.
                        </div>
                    </div>
                @endif
            </div>

        </div>

    </div>

@endsection
