@extends('layouts.presensi')

@section('title', 'Jadwal Sesi Belajar & Pengganti — Tutor')

@php
    use Carbon\Carbon;
    $user = auth()->user();
    $displayName = (string) (($user->nama_lengkap ?? $user->name) ?? 'Tutor');

    $tz = 'Asia/Jakarta';
    $todayDate = Carbon::today($tz);
    $selectedDate = $selectedDate ?? Carbon::parse(request('tanggal', $todayDate->toDateString()))->startOfDay();

    $prevMonthDate = $selectedDate->copy()->subMonth()->toDateString();
    $nextMonthDate = $selectedDate->copy()->addMonth()->toDateString();

    $firstDayOfMonth = $selectedDate->copy()->startOfMonth();
    $startDayOfWeek = $firstDayOfMonth->dayOfWeekIso;
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

    $activeTab = request('tab', 'kalender');
@endphp

@push('topbar')
    @include('layouts.components.navigasi_atas', [
        'titleName' => $activeTab === 'agenda' ? 'Agenda & Pengumuman PKBM' : ($activeTab === 'rutin' ? 'Pola Rutin Mengajar' : 'Jadwal Sesi & Pengganti'),
        'subTitle' => $displayName . ' • Perencanaan KBM',
        'dashRoute' => route('tutor.dashboard'),
        'backRoute' => route('tutor.dashboard'),
    ])
@endpush

@section('content')

    <div class="laporanPageWrapper">
        {{-- ── Header Card ── --}}
        <div class="laporanHeader">
            <div class="laporanHeaderCard">
                <div class="laporanHeaderInfo">
                    @if ($activeTab === 'rutin')
                        <div class="laporanHeaderLabel">MASTER POLA BERULANG</div>
                        <h1 class="laporanHeaderTitle">Pola Rutin Mengajar</h1>
                        <div class="laporanHeaderSub">Kelola pola hari dan jam belajar mingguan mandiri bersama peserta didik bimbingan Anda.</div>
                        <p class="laporanHeaderDesc">Pola rutin otomatis berulang setiap minggu dan meng-generate sesi kalender belajar murid.</p>
                    @elseif ($activeTab === 'agenda')
                        <div class="laporanHeaderLabel">AGENDA RESMI &amp; PENGUMUMAN</div>
                        <h1 class="laporanHeaderTitle">Agenda KBM &amp; Libur Sekolah</h1>
                        <div class="laporanHeaderSub">Kalender akademik resmi, agenda kegiatan sekolah, dan hari libur PKBM PIKAT.</div>
                        <p class="laporanHeaderDesc">Pantau agenda kegiatan resmi sekolah dan pastikan tidak bertabrakan dengan jadwal bimbingan belajar murid Anda.</p>
                    @else
                        <div class="laporanHeaderLabel">PERENCANAAN KBM &amp; RESCHEDULE</div>
                        <h1 class="laporanHeaderTitle">Jadwal Sesi &amp; Pengganti</h1>
                        <div class="laporanHeaderSub">Atur jadwal belajar murid, buat jadwal sesi tunggal, atau jadwalkan sesi pengganti (*make-up class*)</div>
                        <p class="laporanHeaderDesc">Jadwal yang dibuat otomatis menjadi acuan smart time-gating presensi murid dan evaluasi jam masuk foto Anda.</p>
                    @endif
                </div>
                <div class="laporanHeaderActions header-actions-group">
                    @if ($activeTab === 'rutin')
                        <button type="button" onclick="bukaModalJadwalBaru(true)" class="profileBtnPrimary">
                            <ion-icon name="add-circle-outline" class="icon-sm"></ion-icon> Tambah Pola Rutin
                        </button>
                    @else
                        <button type="button" onclick="bukaModalJadwalBaru(false)" class="profileBtnPrimary">
                            <ion-icon name="add-circle-outline" class="icon-sm"></ion-icon> Buat Jadwal Belajar
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Tab Switcher Navigasi Terpadu ── --}}
        <div class="calendarNavTabsContainer">
            <a href="{{ route('tutor.jadwal-sesi.index', ['tab' => 'kalender', 'tanggal' => $selectedDate->toDateString()]) }}"
                class="calendarNavTab {{ $activeTab === 'kalender' ? 'active' : '' }}">
                <ion-icon name="calendar-outline"></ion-icon>
                <span>Kalender Sesi Belajar</span>
                @if(isset($todaySesiCount) && $todaySesiCount > 0)
                    <span class="calendarNavBadge">{{ $todaySesiCount }} Hari Ini</span>
                @elseif(isset($sesiHariIni) && $sesiHariIni->count() > 0)
                    <span class="calendarNavBadge">{{ $sesiHariIni->count() }} Sesi</span>
                @endif
            </a>
            <a href="{{ route('tutor.jadwal-sesi.index', ['tab' => 'rutin']) }}"
                class="calendarNavTab {{ $activeTab === 'rutin' ? 'active' : '' }}">
                <ion-icon name="repeat-outline"></ion-icon>
                <span>Pola Rutin Saya</span>
                <span class="calendarNavBadge">{{ $jadwalRutins->count() }}</span>
            </a>
            <a href="{{ route('tutor.jadwal-sesi.index', ['tab' => 'agenda', 'tanggal' => $selectedDate->toDateString()]) }}"
                class="calendarNavTab {{ $activeTab === 'agenda' ? 'active' : '' }}">
                <ion-icon name="megaphone-outline"></ion-icon>
                <span>Agenda &amp; Pengumuman PKBM</span>
                @if(isset($totalAgendaCount) && $totalAgendaCount > 0)
                    <span class="calendarNavBadge amber">{{ $totalAgendaCount }}</span>
                @endif
            </a>
        </div>

        @if ($activeTab === 'rutin')
            {{-- ══════════════════════════════════════════════════════════════ --}}
            {{-- ── TAB 2: POLA RUTIN MINGGUAN MANDIRI TUTOR ── --}}
            {{-- ══════════════════════════════════════════════════════════════ --}}
            <div class="max-w-4xl mx-auto pb-6">
                <div class="mb-3">
                    <h2 class="text-md font-extrabold text-dark m-0">
                        <ion-icon name="sync-outline" class="text-primary"></ion-icon> Master Pola Rutin Mingguan Anda
                    </h2>
                    <p class="text-xs text-muted m-0 mt-1">Jadwal ini berulang otomatis tiap minggu dan meng-generate sesi kalender belajar siswa.</p>
                </div>

                @if ($jadwalRutins->count() > 0)
                    <div class="agendaBannerBox rutin">
                        <div class="agendaBannerHeader rutin">
                            <div class="flex-items-center gap-2">
                                <span class="agendaBannerBadge rutin">
                                    Master Pola Rutin
                                </span>
                                <span class="text-xs font-bold" style="color: #059669;">
                                    {{ $jadwalRutins->count() }} Pola Mingguan
                                </span>
                            </div>
                            <div class="agendaBannerDate">
                                Berulang Otomatis Tiap Pekan
                            </div>
                        </div>

                        <div class="mobile-card-list" style="display: flex !important; gap: 12px;">
                            @foreach ($jadwalRutins as $rutin)
                                @php
                                    $hariSlug = strtolower((string) $rutin->hari);
                                @endphp
                                <div class="data-mobile-card">
                                    <div class="dmc-header">
                                        <div>
                                            <h3 class="dmc-title">{{ $rutin->siswa->nama_siswa ?? 'Peserta Didik' }}</h3>
                                            <div class="dmc-subtitle">
                                                No. Absen: <strong>{{ $rutin->siswa->no_absen ?? '-' }}</strong> ·
                                                {{ $rutin->siswa->nama_kelas_lengkap ?? 'Reguler' }}
                                            </div>
                                        </div>
                                        <div class="flex-col gap-1 flex-items-end">
                                            <span class="app-badge badge-hari-{{ $hariSlug }}">
                                                {{ strtoupper((string) $rutin->nama_hari_label) }}
                                            </span>
                                            @if ($rutin->is_active)
                                                <span class="app-badge badge-status-aktif">Aktif Rutin</span>
                                            @else
                                                <span class="app-badge badge-status-nonaktif">Nonaktif</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="dmc-grid">
                                        <div class="dmc-field">
                                            <div class="dmc-label">Jam Belajar KBM</div>
                                            <div class="dmc-value text-primary font-bold">{{ $rutin->jam_masuk_formatted }} -
                                                {{ $rutin->jam_pulang_formatted }} WIB</div>
                                        </div>

                                        <div class="dmc-field">
                                            <div class="dmc-label">Kategori SK / Durasi</div>
                                            <div class="dmc-value">{{ $rutin->kategoriTutorial->nama_kategori ?? 'Tutorial KBM' }}
                                                ({{ number_format((float) $rutin->durasi_jam, 1) }} Jam)</div>
                                        </div>

                                        <div class="dmc-field">
                                            <div class="dmc-label">Masa Berlaku</div>
                                            <div class="dmc-value text-xs font-semibold">
                                                Mulai: {{ $rutin->berlaku_mulai ? $rutin->berlaku_mulai->format('d/m/Y') : 'Sekarang' }}
                                                @if ($rutin->berlaku_sampai)
                                                    <br>Selesai: {{ $rutin->berlaku_sampai->format('d/m/Y') }}
                                                @else
                                                    <br><span class="text-muted">(Terus berlanjut tanpa batas)</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="dmc-field">
                                            <div class="dmc-label">Sesi Tergenerate</div>
                                            <div class="dmc-value font-bold">{{ $rutin->jadwal_sesis_count }} Sesi Kalender</div>
                                        </div>

                                        @if ($rutin->keterangan)
                                            <div class="dmc-field full">
                                                <div class="dmc-label">Catatan / Kesepakatan</div>
                                                <div class="dmc-value text-xs text-muted">{{ $rutin->keterangan }}</div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="dmc-footer">
                                        <div class="text-xs text-muted">
                                            Dibuat: {{ $rutin->created_at->translatedFormat('d M Y') }}
                                        </div>
                                        <div class="dmc-actions">
                                            <form action="{{ route('tutor.jadwal-rutin.toggleStatus', $rutin) }}" method="POST"
                                                class="d-inline m-0">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="smallBtn btn-table-action"
                                                    title="{{ $rutin->is_active ? 'Pause Pola Rutin' : 'Aktifkan Pola Rutin' }}"
                                                    style="background: var(--card-alt); border: 1px solid var(--border); color: {{ $rutin->is_active ? '#d97706' : '#059669' }};">
                                                    <ion-icon
                                                        name="{{ $rutin->is_active ? 'pause-circle-outline' : 'play-circle-outline' }}"></ion-icon>
                                                    {{ $rutin->is_active ? 'Pause' : 'Aktifkan' }}
                                                </button>
                                            </form>

                                            <form action="{{ route('tutor.jadwal-rutin.destroy', $rutin) }}" method="POST"
                                                class="d-inline m-0"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus pola jadwal rutin ini? Sesi kalender masa depan yang belum presensi akan dihentikan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="smallBtn delete cursor-pointer btn-table-action"
                                                    title="Hapus Pola Rutin">
                                                    <ion-icon name="trash-outline"></ion-icon> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="emptyAgendaBox">
                        <div class="emptyAgendaIcon rutin">
                            <ion-icon name="repeat-outline"></ion-icon>
                        </div>
                        <div class="emptyAgendaTitle">Belum Ada Master Pola Rutin</div>
                        <div class="emptyAgendaDesc">Anda belum membuat pola jadwal berulang mingguan dengan siswa. Pola rutin otomatis men-generate sesi kalender belajar setiap pekannya.</div>
                        <div class="mt-3">
                            <button type="button" onclick="bukaModalJadwalBaru(true)" class="profileBtnPrimary text-xs py-2 px-4 d-inline-flex items-center gap-1" style="background: #059669;">
                                <ion-icon name="add-circle-outline"></ion-icon> + Tambah Pola Rutin Sekarang
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @elseif ($activeTab === 'agenda')
            {{-- ══════════════════════════════════════════════════════════════ --}}
            {{-- ── TAB 3: KALENDER AGENDA & PENGUMUMAN RESMI PKBM ── --}}
            {{-- ══════════════════════════════════════════════════════════════ --}}
            <div class="max-w-4xl mx-auto pb-6">
                {{-- ── Kalender Bulanan Agenda ── --}}
                <div class="agendaHeaderCard mb-4">
                    <div class="agendaMonthNav">
                        <a href="{{ route('tutor.jadwal-sesi.index', ['tab' => 'agenda', 'tanggal' => $prevMonthDate]) }}" class="agendaNavBtn"
                            title="Bulan Sebelumnya">
                            <ion-icon name="chevron-back-outline"></ion-icon>
                        </a>
                        <div class="text-center">
                            <h3 class="agendaMonthTitle">{{ $selectedDate->translatedFormat('F Y') }}</h3>
                            <span class="text-xs font-bold text-muted">Kalender Agenda &amp; Sesi KBM</span>
                        </div>
                        <a href="{{ route('tutor.jadwal-sesi.index', ['tab' => 'agenda', 'tanggal' => $nextMonthDate]) }}" class="agendaNavBtn"
                            title="Bulan Berikutnya">
                            <ion-icon name="chevron-forward-outline"></ion-icon>
                        </a>
                    </div>

                    <div class="agendaMonthGrid mb-1">
                        @foreach($dayNames as $dName)
                            <div class="agendaDayNameHeader {{ $dName['sunday'] ? 'sunday' : '' }}">
                                {{ $dName['code'] }}
                            </div>
                        @endforeach
                    </div>

                    <div class="agendaMonthGrid">
                        @for($i = 0; $i < $emptyCellsCount; $i++)
                            <div class="agendaDayCell empty"></div>
                        @endfor

                        @foreach($monthDays as $d)
                            @php
                                $dStr = $d->format('Y-m-d');
                                $isActive = $d->isSameDay($selectedDate);
                                $isCellToday = $d->isToday();
                                $agendaCount = (int) ($agendaMonthCounts[$dStr] ?? 0);
                                $sesiCount = (int) ($monthCounts[$dStr] ?? 0);
                                $hasAgenda = $agendaCount > 0;
                                $hasSesi = $sesiCount > 0;
                                $hasEvent = $hasAgenda || $hasSesi;
                            @endphp
                            <a href="{{ route('tutor.jadwal-sesi.index', ['tab' => 'agenda', 'tanggal' => $dStr]) }}"
                                class="agendaDayCell {{ $isActive ? 'active' : '' }} {{ $isCellToday ? 'today' : '' }} {{ $hasEvent ? 'hasEvent' : '' }}"
                                title="{{ $d->translatedFormat('d F Y') }} ({{ $agendaCount }} Agenda PKBM, {{ $sesiCount }} Sesi Belajar)">
                                <span class="agendaDayNum">{{ $d->day }}</span>
                                @if($hasEvent)
                                    <span class="agendaMultiDotWrap">
                                        @if($hasSesi)
                                            <span class="agendaDotSesi" title="{{ $sesiCount }} Sesi Belajar"></span>
                                        @endif
                                        @if($hasAgenda)
                                            <span class="agendaDotAgenda" title="{{ $agendaCount }} Agenda PKBM"></span>
                                        @endif
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>

                    <div class="agendaCalendarLegend">
                        <div class="agendaLegendItem">
                            <span class="agendaLegendIndicator todayIndicator"></span>
                            <span>Hari Ini</span>
                        </div>
                        <div class="agendaLegendItem">
                            <span class="agendaDotSesi"></span>
                            <span>Sesi Belajar Murid</span>
                        </div>
                        <div class="agendaLegendItem">
                            <span class="agendaDotAgenda"></span>
                            <span>Agenda / Libur PKBM</span>
                        </div>
                        <div class="agendaLegendItem">
                            <span class="agendaLegendIndicator activeIndicator"></span>
                            <span>Tanggal Terpilih</span>
                        </div>
                    </div>
                </div>

                {{-- ── Ringkasan Sesi Mengajar Tutor pada tanggal ini (Jika ada) ── --}}
                @if(isset($sesiHariIni) && $sesiHariIni->count() > 0)
                    <div class="sesiSummaryBox mb-4">
                        <div class="sesiSummaryHeader">
                            <div class="d-flex items-center gap-2">
                                <span class="app-badge badge-reguler">
                                    <ion-icon name="school-outline"></ion-icon> Sesi Belajar Terjadwal
                                </span>
                                <span class="text-xs font-bold text-dark">{{ $sesiHariIni->count() }} Sesi Mengajar Anda</span>
                            </div>
                            <a href="{{ route('tutor.jadwal-sesi.index', ['tab' => 'kalender', 'tanggal' => $selectedDate->toDateString()]) }}"
                                class="text-xs font-bold text-primary d-inline-flex items-center gap-1">
                                Kelola di Kalender Sesi <ion-icon name="arrow-forward-outline"></ion-icon>
                            </a>
                        </div>
                        <div>
                            @foreach($sesiHariIni as $sesi)
                                <div class="sesiSummaryItem">
                                    <div>
                                        <div class="font-bold text-sm text-dark">{{ $sesi->siswa->nama_siswa ?? 'Peserta Didik' }}</div>
                                        <div class="text-xs text-muted">
                                            {{ $sesi->kategoriTutorial->nama_kategori ?? 'Tutorial KBM' }} &bull;
                                            {{ $sesi->durasi_jam }} Jam
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs font-extrabold text-primary">{{ $sesi->jam_rencana_formatted }} WIB</div>
                                        <span class="app-badge {{ $sesi->status_badge_class }} text-2xs">{{ ucfirst($sesi->status) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ── Daftar Agenda Resmi PKBM pada Tanggal Terpilih ── --}}
                @php
                    $hasAgendasToday = isset($agendasHariIni) && $agendasHariIni->count() > 0;
                    $isToday = $selectedDate->isToday();
                @endphp
                @if($hasAgendasToday)
                    <div class="agendaBannerBox">
                        <div class="agendaBannerHeader">
                            <div class="flex-items-center gap-2">
                                <span class="agendaBannerBadge {{ $isToday ? 'today' : '' }}">
                                    {{ $isToday ? 'Kegiatan PKBM Hari Ini' : 'Agenda Resmi Terjadwal' }}
                                </span>
                                <span class="text-xs font-bold text-primary">
                                    {{ $agendasHariIni->count() }} Kegiatan
                                </span>
                            </div>
                            <div class="agendaBannerDate">
                                {{ $selectedDate->translatedFormat('l, d F Y') }}
                            </div>
                        </div>

                        <div>
                            @foreach($agendasHariIni as $j)
                                @php
                                    $judul = $j->judul ?? 'Agenda Kegiatan';
                                    $deskripsi = $j->deskripsi ?? null;
                                    $lokasi = $j->lokasi ?? null;
                                @endphp

                                <div class="agendaCardItem {{ $isToday ? 'today' : '' }}">
                                    <h4 class="text-lg font-extrabold text-dark m-0 mb-1">
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
                @else
                    <div class="emptyAgendaBox">
                        <div class="emptyAgendaIcon">
                            <ion-icon name="calendar-clear-outline"></ion-icon>
                        </div>
                        <div class="emptyAgendaTitle">Tidak Ada Agenda Resmi PKBM</div>
                        <div class="emptyAgendaDesc">Belum ada agenda sekolah atau pengumuman akademik terjadwal pada
                            <strong>{{ $selectedDate->translatedFormat('l, d F Y') }}</strong>.</div>
                    </div>
                @endif
            </div>
        @else
            {{-- ══════════════════════════════════════════════════════════════ --}}
            {{-- ── TAB 1: KALENDER & DAFTAR SESI AKTUAL ── --}}
            {{-- ══════════════════════════════════════════════════════════════ --}}
            <div class="max-w-4xl mx-auto pb-6">
                {{-- ── Kalender Sesi Bulanan ── --}}
                <div class="agendaHeaderCard mb-4">
                    <div class="agendaMonthNav">
                        <a href="{{ route('tutor.jadwal-sesi.index', ['tab' => 'kalender', 'tanggal' => $prevMonthDate]) }}" class="agendaNavBtn"
                            title="Bulan Sebelumnya">
                            <ion-icon name="chevron-back-outline"></ion-icon>
                        </a>
                        <div class="text-center">
                            <h3 class="agendaMonthTitle">{{ $selectedDate->translatedFormat('F Y') }}</h3>
                            <span class="text-xs font-bold text-muted">Jadwal Sesi Mengajar Tutor</span>
                        </div>
                        <a href="{{ route('tutor.jadwal-sesi.index', ['tab' => 'kalender', 'tanggal' => $nextMonthDate]) }}" class="agendaNavBtn"
                            title="Bulan Berikutnya">
                            <ion-icon name="chevron-forward-outline"></ion-icon>
                        </a>
                    </div>

                    <div class="agendaMonthGrid mb-1">
                        @foreach($dayNames as $dName)
                            <div class="agendaDayNameHeader {{ $dName['sunday'] ? 'sunday' : '' }}">
                                {{ $dName['code'] }}
                            </div>
                        @endforeach
                    </div>

                    <div class="agendaMonthGrid">
                        @for($i = 0; $i < $emptyCellsCount; $i++)
                            <div class="agendaDayCell empty"></div>
                        @endfor

                        @foreach($monthDays as $d)
                            @php
                                $dStr = $d->format('Y-m-d');
                                $isActive = $d->isSameDay($selectedDate);
                                $isCellToday = $d->isToday();
                                $sesiCount = (int) ($monthCounts[$dStr] ?? 0);
                                $agendaCount = (int) ($agendaMonthCounts[$dStr] ?? 0);
                                $hasSesi = $sesiCount > 0;
                                $hasAgenda = $agendaCount > 0;
                                $hasEvent = $hasSesi || $hasAgenda;
                            @endphp
                            <a href="{{ route('tutor.jadwal-sesi.index', ['tanggal' => $dStr, 'tab' => 'kalender']) }}"
                                class="agendaDayCell {{ $isActive ? 'active' : '' }} {{ $isCellToday ? 'today' : '' }} {{ $hasEvent ? 'hasEvent' : '' }}"
                                title="{{ $d->translatedFormat('d F Y') }} ({{ $sesiCount }} Sesi Belajar, {{ $agendaCount }} Agenda PKBM)">
                                <span class="agendaDayNum">{{ $d->day }}</span>
                                @if($hasEvent)
                                    <span class="agendaMultiDotWrap">
                                        @if($hasSesi)
                                            <span class="agendaDotSesi" title="{{ $sesiCount }} Sesi Belajar"></span>
                                        @endif
                                        @if($hasAgenda)
                                            <span class="agendaDotAgenda" title="{{ $agendaCount }} Agenda PKBM"></span>
                                        @endif
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>

                    <!-- Legenda Kalender Terpadu -->
                    <div class="agendaCalendarLegend">
                        <div class="agendaLegendItem">
                            <span class="agendaLegendIndicator todayIndicator"></span>
                            <span>Hari Ini</span>
                        </div>
                        <div class="agendaLegendItem">
                            <span class="agendaDotSesi"></span>
                            <span>Sesi Belajar Murid</span>
                        </div>
                        <div class="agendaLegendItem">
                            <span class="agendaDotAgenda"></span>
                            <span>Agenda / Libur PKBM</span>
                        </div>
                        <div class="agendaLegendItem">
                            <span class="agendaLegendIndicator activeIndicator"></span>
                            <span>Tanggal Terpilih</span>
                        </div>
                    </div>
                </div>

                {{-- ── Banner Integrasi Agenda PKBM Hari Ini (Jika Ada) ── --}}
                @if(isset($agendasHariIni) && $agendasHariIni->count() > 0)
                    <div class="agendaNoticeBox mb-4">
                        <div class="agendaNoticeHeader">
                            <div class="d-flex items-center gap-2">
                                <span class="agendaNoticeBadge">
                                    <ion-icon name="megaphone"></ion-icon> Agenda / Pengumuman PKBM
                                </span>
                                <span class="text-xs font-bold text-dark">{{ $agendasHariIni->count() }} Kegiatan pada
                                    {{ $selectedDate->translatedFormat('d M Y') }}</span>
                            </div>
                            <a href="{{ route('tutor.jadwal-sesi.index', ['tab' => 'agenda', 'tanggal' => $selectedDate->toDateString()]) }}"
                                class="text-xs font-bold text-primary d-inline-flex items-center gap-1">
                                Lihat di Agenda <ion-icon name="arrow-forward-outline"></ion-icon>
                            </a>
                        </div>
                        @foreach($agendasHariIni as $agenda)
                            <div class="agendaNoticeItem">
                                <div class="agendaNoticeTitle">{{ $agenda->judul ?? 'Agenda Sekolah' }}</div>
                                @if($agenda->deskripsi)
                                    <div class="agendaNoticeDesc">{{ $agenda->deskripsi }}</div>
                                @endif
                                @if($agenda->lokasi)
                                    <div class="agendaNoticeLocation">
                                        <ion-icon name="location-outline"></ion-icon> {{ $agenda->lokasi }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- ── Daftar Sesi Pada Tanggal Terpilih ── --}}
                @php
                    $isToday = $selectedDate->isToday();
                    $hasSesiToday = $sesiHariIni->count() > 0;
                @endphp
                @if($hasSesiToday)
                    <div class="agendaBannerBox">
                        <div class="agendaBannerHeader">
                            <div class="flex-items-center gap-2">
                                <span class="agendaBannerBadge {{ $isToday ? 'today' : '' }}">
                                    {{ $isToday ? 'Sesi KBM Hari Ini' : 'Sesi KBM Terjadwal' }}
                                </span>
                                <span class="text-xs font-bold text-primary">
                                    {{ $sesiHariIni->count() }} Sesi Mengajar
                                </span>
                            </div>
                            <div class="agendaBannerDate">
                                {{ $selectedDate->translatedFormat('l, d F Y') }}
                            </div>
                        </div>

                        <div class="mobile-card-list" style="display: flex !important; gap: 12px;">
                            @foreach($sesiHariIni as $sesi)
                                <div class="data-mobile-card">
                                    <div class="dmc-header">
                                        <div>
                                            <h3 class="dmc-title">{{ $sesi->siswa->nama_siswa ?? 'Peserta Didik' }}</h3>
                                            <div class="dmc-subtitle">
                                                <span
                                                    class="app-badge {{ $sesi->jenis_sesi === 'pengganti' ? 'badge-layanan-dl' : 'badge-layanan-komunitas' }}">
                                                    {{ $sesi->jenis_label }}
                                                </span>
                                                @if($sesi->kategoriTutorial)
                                                    <span class="text-xs text-muted ml-1">&bull;
                                                        {{ $sesi->kategoriTutorial->nama_kategori }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            @if($sesi->status === 'selesai')
                                                <span class="app-badge badge-status-aktif">Selesai Presensi</span>
                                            @elseif($sesi->status === 'dibatalkan')
                                                <span class="app-badge badge-status-nonaktif">Dibatalkan</span>
                                            @elseif($sesi->status === 'berlangsung')
                                                <span class="app-badge badge-role-tutor">Sedang KBM</span>
                                            @else
                                                <span class="app-badge badge-reguler">Terjadwal</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="dmc-grid">
                                        <div class="dmc-field">
                                            <div class="dmc-label">Jam Rencana KBM</div>
                                            <div class="dmc-value">
                                                <span class="font-bold text-dark text-sm">{{ $sesi->jam_masuk_formatted }} -
                                                    {{ $sesi->jam_pulang_formatted }} WIB</span>
                                                <span class="text-xs text-muted">({{ $sesi->durasi_jam }} Jam)</span>
                                            </div>
                                        </div>

                                        <div class="dmc-field">
                                            <div class="dmc-label">Kehadiran Siswa</div>
                                            <div class="dmc-value">
                                                @if($sesi->status_kehadiran_siswa === 'hadir')
                                                    <span class="app-badge badge-status-aktif">Hadir Mandiri</span>
                                                @elseif($sesi->status_kehadiran_siswa === 'izin')
                                                    <span class="app-badge badge-abk">Izin</span>
                                                @elseif($sesi->status_kehadiran_siswa === 'sakit')
                                                    <span class="app-badge badge-status-cuti">Sakit</span>
                                                @else
                                                    <span class="text-xs text-muted">Belum Presensi</span>
                                                @endif
                                            </div>
                                        </div>

                                        @if($sesi->tanggal_asli)
                                            <div class="dmc-field full">
                                                <div class="dmc-label">Menggantikan Sesi Sebelumnya</div>
                                                <div class="dmc-value">
                                                    <span
                                                        class="font-bold text-primary text-xs">{{ $sesi->tanggal_asli->translatedFormat('l, d F Y') }}</span>
                                                </div>
                                            </div>
                                        @endif

                                        @if($sesi->alasan_penggantian)
                                            <div class="dmc-field full">
                                                <div class="dmc-label">Alasan Reschedule / Penggantian</div>
                                                <div class="dmc-value text-xs text-muted">{{ $sesi->alasan_penggantian }}</div>
                                            </div>
                                        @endif

                                        @if($sesi->catatan)
                                            <div class="dmc-field full">
                                                <div class="dmc-label">Catatan Tambahan</div>
                                                <div class="dmc-value text-xs text-muted">{{ $sesi->catatan }}</div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="dmc-footer">
                                        <div class="text-xs text-muted">
                                            ID Sesi: #{{ $sesi->id }}
                                        </div>

                                        <div class="dmc-actions">
                                            @if($sesi->status === 'terjadwal')
                                                <a href="{{ route('tutor.presensi') }}" class="profileBtnPrimary text-xs py-1 px-3">
                                                    <ion-icon name="camera-outline"></ion-icon> Absen Sesi Ini
                                                </a>

                                                <button type="button"
                                                    onclick="bukaModalReschedule({{ $sesi->id }}, '{{ addslashes($sesi->siswa->nama_siswa ?? 'Siswa') }}', '{{ $sesi->tanggal_rencana->format('Y-m-d') }}', '{{ substr((string) $sesi->jam_masuk_rencana, 0, 5) }}', '{{ substr((string) $sesi->jam_pulang_rencana, 0, 5) }}')"
                                                    class="smallBtn btn-table-action"
                                                    style="background: rgba(37,99,235,0.08); color: #2563eb; border: 1px solid rgba(37,99,235,0.2);">
                                                    <ion-icon name="repeat-outline"></ion-icon> Reschedule
                                                </button>

                                                <form method="POST" action="{{ route('tutor.jadwal-sesi.destroy', $sesi) }}"
                                                    data-confirm="Hapus sesi mengajar ini?" data-confirm-title="Hapus Sesi"
                                                    data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="smallBtn delete cursor-pointer btn-table-action"
                                                        title="Hapus Sesi">
                                                        <ion-icon name="trash-outline"></ion-icon> Hapus
                                                    </button>
                                                </form>
                                            @elseif($sesi->status === 'dibatalkan')
                                                <span class="text-xs text-danger font-semibold d-flex items-center gap-1">
                                                    <ion-icon name="close-circle"></ion-icon> Dibatalkan:
                                                    {{ $sesi->alasan_penggantian ?: 'Reschedule' }}
                                                </span>
                                            @else
                                                <span class="text-xs text-success font-bold d-flex items-center gap-1">
                                                    <ion-icon name="checkmark-done-circle"></ion-icon> Presensi Selesai
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="emptyAgendaBox">
                        <div class="emptyAgendaIcon">
                            <ion-icon name="school-outline"></ion-icon>
                        </div>
                        <div class="emptyAgendaTitle">Belum Ada Sesi KBM Terjadwal</div>
                        <div class="emptyAgendaDesc">Tidak ada agenda sesi belajar mengajar dengan peserta didik pada <strong>{{ $selectedDate->translatedFormat('l, d F Y') }}</strong>.</div>
                        <div class="mt-3">
                            <button type="button" onclick="bukaModalJadwalBaru(false)" class="profileBtnPrimary text-xs py-2 px-4 d-inline-flex items-center gap-1">
                                <ion-icon name="add-circle-outline"></ion-icon> Jadwalkan Sesi Hari Ini
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- ── MODAL 1: BUAT JADWAL BELAJAR BARU (RUTIN ATAU SINGLE) ── --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div id="modalJadwalBaru" class="navDrawerBackdrop" onclick="if(event.target === this) tutupModalJadwalBaru()">
        <div class="navDrawerSheet max-w-lg mx-auto" onclick="event.stopPropagation()">
            <div class="navDrawerHandle"></div>

            <div class="navDrawerHeader">
                <div class="navDrawerTitleWrap">
                    <div class="navDrawerSub">Perencanaan KBM • Mandiri Tutor</div>
                    <h3 class="navDrawerTitle" id="modalJadwalTitle">Buat Jadwal Belajar</h3>
                </div>
                <button type="button" class="navDrawerCloseBtn" onclick="tutupModalJadwalBaru()" aria-label="Tutup">
                    <ion-icon name="close"></ion-icon>
                </button>
            </div>

            <div class="navDrawerBody">
                <form method="POST" action="{{ route('tutor.jadwal-sesi.store') }}">
                    @csrf

                    {{-- Mode Switcher: Rutin vs Single --}}
                    <div class="p-2 bg-card-alt rounded-xl border-base mb-3 d-flex gap-1">
                        <label
                            class="cursor-pointer flex-1 text-center py-2 px-3 rounded-lg text-xs font-bold transition-all"
                            id="labelModeRutin" onclick="setScheduleMode(true)">
                            <input type="radio" name="is_recurring" id="radioModeRutin" value="1" class="d-none">
                            <ion-icon name="repeat-outline"></ion-icon> Ulangi Setiap Minggu (Rutin)
                        </label>
                        <label
                            class="cursor-pointer flex-1 text-center py-2 px-3 rounded-lg text-xs font-bold transition-all"
                            id="labelModeSingle" onclick="setScheduleMode(false)">
                            <input type="radio" name="is_recurring" id="radioModeSingle" value="0" class="d-none" checked>
                            <ion-icon name="calendar-outline"></ion-icon> Hanya Tanggal Ini (Sekali)
                        </label>
                    </div>

                    {{-- Field: Siswa --}}
                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Pilih Murid / Siswa <span class="text-danger">*</span></label>
                        <select name="siswa_id" class="filterSelect" required>
                            <option value="">-- Pilih Siswa Bimbingan --</option>
                            @foreach($siswas as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_siswa }} (No. {{ $s->no_absen ?? '-' }} -
                                    {{ $s->nama_kelas_lengkap ?? 'Reguler' }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Form Bagian A: Jika Mode Rutin Mingguan --}}
                    <div id="sectionRutin" class="d-none">
                        <div class="form-field-wrapper mb-3">
                            <label class="filterFieldLabel">Hari Belajar Mingguan <span class="text-danger">*</span></label>
                            <select name="hari" id="modalHari" class="filterSelect">
                                <option value="senin">Setiap Hari Senin</option>
                                <option value="selasa">Setiap Hari Selasa</option>
                                <option value="rabu">Setiap Hari Rabu</option>
                                <option value="kamis">Setiap Hari Kamis</option>
                                <option value="jumat">Setiap Hari Jumat</option>
                                <option value="sabtu">Setiap Hari Sabtu</option>
                                <option value="minggu">Setiap Hari Minggu</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div class="form-field-wrapper">
                                <label class="filterFieldLabel">Berlaku Mulai</label>
                                <input type="date" name="berlaku_mulai" value="{{ date('Y-m-d') }}" class="profileInput">
                            </div>
                            <div class="form-field-wrapper">
                                <label class="filterFieldLabel">Berlaku Sampai (Bulan/Tanggal Selesai)</label>
                                <input type="date" name="berlaku_sampai" class="profileInput"
                                    placeholder="Pilih batas tanggal">
                                <span class="field-help-text">Kosongkan jika terus berlanjut tanpa batas.</span>
                            </div>
                        </div>
                    </div>

                    {{-- Form Bagian B: Jika Mode Single Sesi --}}
                    <div id="sectionSingle">
                        <div class="form-field-wrapper mb-3">
                            <label class="filterFieldLabel">Jenis Sesi <span class="text-danger">*</span></label>
                            <select name="jenis_sesi" id="modalJenisSesi" onchange="toggleTanggalAsli()"
                                class="filterSelect">
                                <option value="reguler">Sesi Reguler</option>
                                <option value="pengganti">Sesi Pengganti (Make-up Class / Reschedule)</option>
                                <option value="tambahan">Sesi Tambahan / Pengayaan</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div class="form-field-wrapper">
                                <label class="filterFieldLabel">Tanggal Rencana <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_rencana" id="modalTanggalRencana"
                                    value="{{ $selectedDate->format('Y-m-d') }}" class="profileInput">
                            </div>

                            <div class="form-field-wrapper d-none" id="boxTanggalAsli">
                                <label class="filterFieldLabel">Menggantikan Sesi Tanggal</label>
                                <input type="date" name="tanggal_asli" class="profileInput">
                                <span class="field-help-text">Tanggal sesi semula yang dilewati.</span>
                            </div>
                        </div>
                    </div>

                    {{-- Kategori SK --}}
                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Kategori Tutorial SK <span class="text-xs text-muted font-normal">(Standar Tarif Resmi)</span></label>
                        <select name="kategori_tutorial_id" id="modalKategoriTutorial" onchange="kategoriUniversalChange()"
                            class="filterSelect">
                            <option value="">-- Pilih Kategori SK (Otomatis Atur Jam) --</option>
                            @foreach($kategoriTutorials as $kat)
                                <option value="{{ $kat->id }}" data-durasi="{{ $kat->durasi_jam }}" data-nominal="{{ $kat->nominal_honor }}">
                                    {{ $kat->nama_kategori }} ({{ $kat->durasi_jam }} Jam &bull; {{ $kat->formatted_nominal_honor }})
                                </option>
                            @endforeach
                            <option value="custom">-- Durasi Khusus (Belum Diatur SK) --</option>
                        </select>
                    </div>

                    {{-- Jam Belajar & Durasi (Dipakai kedua mode) --}}
                    <div class="p-3 bg-card-alt rounded-xl border-base mb-3">
                        <div class="text-xs font-bold uppercase tracking-wider text-muted mb-2">
                            <ion-icon name="time-outline"></ion-icon> Jam Belajar &amp; Durasi KBM
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <div class="form-field-wrapper">
                                <label class="filterFieldLabel">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_masuk" id="modalJamMasukRutin" value="10:00"
                                    onchange="hitungDurasiUniversal(true)" class="profileInput d-none">
                                <input type="time" name="jam_masuk_rencana" id="modalJamMasukSingle" value="10:00"
                                    onchange="hitungDurasiUniversal(true)" class="profileInput" required>
                            </div>
                            <div class="form-field-wrapper">
                                <label class="filterFieldLabel">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_pulang" id="modalJamPulangRutin" value="12:00"
                                    onchange="hitungDurasiUniversal(false)" class="profileInput d-none">
                                <input type="time" name="jam_pulang_rencana" id="modalJamPulangSingle" value="12:00"
                                    onchange="hitungDurasiUniversal(false)" class="profileInput" required>
                            </div>
                            <div class="form-field-wrapper">
                                <label class="filterFieldLabel">Durasi (Jam)</label>
                                <input type="number" step="0.25" min="0.5" max="12" name="durasi_jam"
                                    id="modalDurasiUniversal" value="2.00" class="profileInput" readonly style="background: var(--card);">
                            </div>
                        </div>

                        {{-- Alert Callout Interaktif Jika Durasi Non-SK --}}
                        <div id="boxWarningNonSk" class="mt-3 p-3 rounded-xl d-none" style="background: rgba(217, 119, 6, 0.08); border: 1px solid rgba(217, 119, 6, 0.25);">
                            <div class="d-flex items-start gap-2">
                                <ion-icon name="alert-circle-outline" style="font-size: 20px; color: #d97706; flex-shrink: 0; margin-top: 1px;"></ion-icon>
                                <div class="text-xs" style="color: var(--text); line-height: 1.5;">
                                    <strong style="color: #d97706;">Durasi Non-SK (<span id="labelDurasiNonSk">0</span> Jam):</strong>
                                    Durasi ini belum terdaftar dalam SK Tarif Resmi PKBM. Sesi ini akan ditandai untuk verifikasi Admin/Kepsek, dan kompensasi honorarium akan mengikuti tarif flat default SK terdekat (Rp 75.000,-). Hubungi Admin jika memerlukan SK tarif khusus.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-field-wrapper mb-3" id="boxAlasanSingle">
                        <label class="filterFieldLabel">Keterangan / Alasan Reschedule</label>
                        <input type="text" name="alasan_penggantian"
                            placeholder="Misal: Permintaan orang tua, persiapan ujian, dsb." class="profileInput">
                    </div>

                    <div class="form-field-wrapper mb-4">
                        <label class="filterFieldLabel">Catatan Tambahan (Opsional)</label>
                        <textarea name="catatan" rows="2" placeholder="Catatan materi atau lokasi KBM..."
                            class="profileInput" style="height: auto;"></textarea>
                    </div>

                    <div class="d-flex justify-end gap-2 border-t-base pt-3">
                        <button type="button" onclick="tutupModalJadwalBaru()" class="btnOutline">Batal</button>
                        <button type="submit" class="profileBtnPrimary">
                            <ion-icon name="save-outline"></ion-icon> Simpan Jadwal Belajar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- ── MODAL 2: RESCHEDULE SESI BELAJAR (OPSI 2) ── --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div id="modalRescheduleSesi" class="navDrawerBackdrop" onclick="if(event.target === this) tutupModalReschedule()">
        <div class="navDrawerSheet max-w-md mx-auto" onclick="event.stopPropagation()">
            <div class="navDrawerHandle"></div>

            <div class="navDrawerHeader">
                <div class="navDrawerTitleWrap">
                    <div class="navDrawerSub">Pindah Jadwal • Sesi Pengganti</div>
                    <h3 class="navDrawerTitle">Reschedule Sesi KBM</h3>
                </div>
                <button type="button" class="navDrawerCloseBtn" onclick="tutupModalReschedule()" aria-label="Tutup">
                    <ion-icon name="close"></ion-icon>
                </button>
            </div>

            <div class="navDrawerBody">
                <form id="formReschedule" method="POST" action="">
                    @csrf

                    <div class="info-callout-box mb-3">
                        <div class="info-callout-title">
                            <span>Sesi yang Direschedule</span>
                        </div>
                        <div class="info-callout-desc font-bold text-dark" id="rescheduleSesiInfo">
                            -
                        </div>
                        <div class="text-xs text-muted mt-1">
                            Sesi pada tanggal lama akan berstatus <strong>Dibatalkan</strong> (dengan alasan), dan sesi
                            pengganti baru akan dicatat pada tanggal/jam baru.
                        </div>
                    </div>

                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Tanggal Baru Hasil Reschedule <span
                                class="text-danger">*</span></label>
                        <input type="date" name="tanggal_baru" id="rescheduleTanggalBaru" class="profileInput" required>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div class="form-field-wrapper">
                            <label class="filterFieldLabel">Jam Mulai Baru <span class="text-danger">*</span></label>
                            <input type="time" name="jam_masuk_baru" id="rescheduleJamMasuk" class="profileInput" required>
                        </div>
                        <div class="form-field-wrapper">
                            <label class="filterFieldLabel">Jam Selesai Baru <span class="text-danger">*</span></label>
                            <input type="time" name="jam_pulang_baru" id="rescheduleJamPulang" class="profileInput"
                                required>
                        </div>
                    </div>

                    <div class="form-field-wrapper mb-4">
                        <label class="filterFieldLabel">Alasan Pemindahan / Reschedule <span
                                class="text-danger">*</span></label>
                        <textarea name="alasan_penggantian" rows="2"
                            placeholder="Contoh: Siswa sedang sakit demam, diganti hari Sabtu..." class="profileInput"
                            style="height: auto;" required></textarea>
                    </div>

                    <div class="d-flex justify-end gap-2 border-t-base pt-3">
                        <button type="button" onclick="tutupModalReschedule()" class="btnOutline">Batal</button>
                        <button type="submit" class="profileBtnPrimary">
                            <ion-icon name="repeat-outline"></ion-icon> Konfirmasi Reschedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let isRecurringMode = false;

        function setScheduleMode(isRecurring) {
            isRecurringMode = isRecurring;
            const secRutin = document.getElementById('sectionRutin');
            const secSingle = document.getElementById('sectionSingle');
            const radioRutin = document.getElementById('radioModeRutin');
            const radioSingle = document.getElementById('radioModeSingle');
            const labelRutin = document.getElementById('labelModeRutin');
            const labelSingle = document.getElementById('labelModeSingle');
            const title = document.getElementById('modalJadwalTitle');

            const inMasukRutin = document.getElementById('modalJamMasukRutin');
            const inPulangRutin = document.getElementById('modalJamPulangRutin');
            const inMasukSingle = document.getElementById('modalJamMasukSingle');
            const inPulangSingle = document.getElementById('modalJamPulangSingle');

            if (isRecurring) {
                radioRutin.checked = true;
                radioSingle.checked = false;
                secRutin.classList.remove('d-none');
                secSingle.classList.add('d-none');
                labelRutin.style.background = 'var(--card)';
                labelRutin.style.boxShadow = '0 2px 6px rgba(0,0,0,0.08)';
                labelRutin.style.color = 'var(--primary, #0B5ED7)';
                labelSingle.style.background = 'transparent';
                labelSingle.style.boxShadow = 'none';
                labelSingle.style.color = 'var(--muted)';
                title.textContent = 'Buat Jadwal Rutin Mingguan Siswa';

                inMasukRutin.classList.remove('d-none');
                inPulangRutin.classList.remove('d-none');
                inMasukRutin.required = true;
                inPulangRutin.required = true;

                inMasukSingle.classList.add('d-none');
                inPulangSingle.classList.add('d-none');
                inMasukSingle.required = false;
                inPulangSingle.required = false;
            } else {
                radioSingle.checked = true;
                radioRutin.checked = false;
                secSingle.classList.remove('d-none');
                secRutin.classList.add('d-none');
                labelSingle.style.background = 'var(--card)';
                labelSingle.style.boxShadow = '0 2px 6px rgba(0,0,0,0.08)';
                labelSingle.style.color = 'var(--primary, #0B5ED7)';
                labelRutin.style.background = 'transparent';
                labelRutin.style.boxShadow = 'none';
                labelRutin.style.color = 'var(--muted)';
                title.textContent = 'Buat Jadwal Sesi / Pengganti';

                inMasukSingle.classList.remove('d-none');
                inPulangSingle.classList.remove('d-none');
                inMasukSingle.required = true;
                inPulangSingle.required = true;

                inMasukRutin.classList.add('d-none');
                inPulangRutin.classList.add('d-none');
                inMasukRutin.required = false;
                inPulangRutin.required = false;
            }

            const boxAlasanSingle = document.getElementById('boxAlasanSingle');
            if (boxAlasanSingle) {
                if (isRecurring) {
                    boxAlasanSingle.classList.add('d-none');
                } else {
                    boxAlasanSingle.classList.remove('d-none');
                }
            }
        }

        function bukaModalJadwalBaru(preferRecurring = false) {
            const modal = document.getElementById('modalJadwalBaru');
            if (modal) {
                setScheduleMode(preferRecurring);
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function tutupModalJadwalBaru() {
            const modal = document.getElementById('modalJadwalBaru');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        function toggleTanggalAsli() {
            const jenis = document.getElementById('modalJenisSesi').value;
            const box = document.getElementById('boxTanggalAsli');
            if (jenis === 'pengganti') {
                box.classList.remove('d-none');
            } else {
                box.classList.add('d-none');
            }
        }

        const activeSkDurasiList = [
            @foreach($kategoriTutorials as $kat)
                { id: {{ $kat->id }}, durasi: {{ (float) $kat->durasi_jam }} },
            @endforeach
        ];

        function kategoriUniversalChange() {
            const sel = document.getElementById('modalKategoriTutorial');
            const durasiInput = document.getElementById('modalDurasiUniversal');
            const opt = sel.selectedOptions[0];
            const warnBox = document.getElementById('boxWarningNonSk');

            if (opt && opt.dataset && opt.dataset.durasi) {
                const dur = parseFloat(opt.dataset.durasi);
                durasiInput.value = dur.toFixed(2);
                if (warnBox) warnBox.classList.add('d-none');

                // Otomatis hitung Jam Selesai = Jam Mulai + Durasi SK
                let m = isRecurringMode ? document.getElementById('modalJamMasukRutin').value : document.getElementById('modalJamMasukSingle').value;
                if (m) {
                    const [h, min] = m.split(':').map(Number);
                    const totalMenitMulai = h * 60 + min;
                    const totalMenitSelesai = totalMenitMulai + Math.round(dur * 60);
                    const hSelesai = Math.floor((totalMenitSelesai % (24 * 60)) / 60);
                    const minSelesai = totalMenitSelesai % 60;
                    const pStr = String(hSelesai).padStart(2, '0') + ':' + String(minSelesai).padStart(2, '0');
                    if (isRecurringMode) {
                        document.getElementById('modalJamPulangRutin').value = pStr;
                    } else {
                        document.getElementById('modalJamPulangSingle').value = pStr;
                    }
                }
            } else if (sel.value === 'custom') {
                hitungDurasiUniversal(false);
            } else {
                hitungDurasiUniversal(false);
            }
        }

        function hitungDurasiUniversal(isMulaiChanged = false) {
            const selKat = document.getElementById('modalKategoriTutorial');
            const opt = selKat ? selKat.selectedOptions[0] : null;

            // Jika kategori SK tertentu dipilih (bukan custom) dan jam mulai berubah, otomatis sesuaikan jam selesai
            if (isMulaiChanged && opt && opt.dataset && opt.dataset.durasi) {
                kategoriUniversalChange();
                return;
            }

            let m = isRecurringMode ? document.getElementById('modalJamMasukRutin').value : document.getElementById('modalJamMasukSingle').value;
            let p = isRecurringMode ? document.getElementById('modalJamPulangRutin').value : document.getElementById('modalJamPulangSingle').value;
            const d = document.getElementById('modalDurasiUniversal');
            const warnBox = document.getElementById('boxWarningNonSk');
            const labelDurasi = document.getElementById('labelDurasiNonSk');

            if (m && p) {
                const [h1, m1] = m.split(':').map(Number);
                const [h2, m2] = p.split(':').map(Number);
                let diff = (h2 * 60 + m2) - (h1 * 60 + m1);
                if (diff < 0) diff += 24 * 60;
                const durasiJam = (diff / 60);
                d.value = durasiJam.toFixed(2);

                // Cek apakah durasi ini cocok dengan salah satu kategori SK
                const matchedSk = activeSkDurasiList.find(item => Math.abs(item.durasi - durasiJam) < 0.01);
                if (matchedSk) {
                    if (selKat && selKat.value !== String(matchedSk.id)) {
                        selKat.value = String(matchedSk.id);
                    }
                    if (warnBox) warnBox.classList.add('d-none');
                } else {
                    // Durasi Non-SK
                    if (selKat && selKat.value !== 'custom') {
                        selKat.value = 'custom';
                    }
                    if (warnBox) {
                        warnBox.classList.remove('d-none');
                        if (labelDurasi) labelDurasi.textContent = durasiJam.toFixed(2);
                    }
                }
            }
        }

        function bukaModalReschedule(id, namaSiswa, tanggalRencana, jamMasuk, jamPulang) {
            const modal = document.getElementById('modalRescheduleSesi');
            const form = document.getElementById('formReschedule');
            const info = document.getElementById('rescheduleSesiInfo');
            const inTgl = document.getElementById('rescheduleTanggalBaru');
            const inMasuk = document.getElementById('rescheduleJamMasuk');
            const inPulang = document.getElementById('rescheduleJamPulang');

            if (modal && form) {
                form.action = '/tutor/jadwal-sesi/' + id + '/reschedule';
                info.textContent = namaSiswa + ' • Sesi Tanggal: ' + tanggalRencana + ' (' + jamMasuk + ' - ' + jamPulang + ' WIB)';
                inTgl.value = tanggalRencana;
                inMasuk.value = jamMasuk;
                inPulang.value = jamPulang;

                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function tutupModalReschedule() {
            const modal = document.getElementById('modalRescheduleSesi');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        // Default to Single mode initially
        setScheduleMode(false);

        // Auto-open modal jika diarahkan dari halaman Agenda PKBM dengan parameter open_modal
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('open_modal') === '1' || urlParams.get('open_modal') === 'rutin') {
            bukaModalJadwalBaru(urlParams.get('open_modal') === 'rutin');
        }
    </script>

@endsection