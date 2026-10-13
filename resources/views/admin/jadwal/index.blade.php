@extends('layouts.admin')

@section('title', 'Pusat Jadwal KBM & Sesi Belajar - Admin')

@section('content')

    @php
        use Carbon\Carbon;

        $dayNames = [
            ['code' => 'SEN', 'sunday' => false],
            ['code' => 'SEL', 'sunday' => false],
            ['code' => 'RAB', 'sunday' => false],
            ['code' => 'KAM', 'sunday' => false],
            ['code' => 'JUM', 'sunday' => false],
            ['code' => 'SAB', 'sunday' => true],
            ['code' => 'MIN', 'sunday' => true],
        ];
        $prevMonthDate = $selectedDate->copy()->subMonth()->toDateString();
        $nextMonthDate = $selectedDate->copy()->addMonth()->toDateString();
        $firstDayOfMonth = $selectedDate->copy()->startOfMonth();
        $startDayOfWeek = $firstDayOfMonth->dayOfWeekIso; // 1 (Mon) to 7 (Sun)
        $emptyCellsCount = $startDayOfWeek - 1;
        $isToday = $selectedDate->isToday();
        $activeTab = $activeTab ?? request('tab', 'sesi');
    @endphp

    <div class="laporanPageWrapper">
        {{-- ── Header Card ── --}}
        <div class="laporanHeader">
            <div class="laporanHeaderCard">
                <div class="laporanHeaderInfo">
                    @if ($activeTab === 'rutin')
                        <div class="laporanHeaderLabel">MASTER POLA BERULANG</div>
                        <h1 class="laporanHeaderTitle">Pola Rutin KBM (Mingguan)</h1>
                        <div class="laporanHeaderSub">Kelola pola hari dan jam belajar mingguan seluruh tutor bersama murid bimbingannya.</div>
                        <p class="laporanHeaderDesc">Master Jadwal Rutin KBM Murid. Pola rutin otomatis berulang setiap pekan dan meng-generate sesi kalender belajar harian.</p>
                    @elseif ($activeTab === 'agenda')
                        <div class="laporanHeaderLabel">AGENDA RESMI &amp; PENGUMUMAN</div>
                        <h1 class="laporanHeaderTitle">Agenda Acara &amp; Libur PKBM</h1>
                        <div class="laporanHeaderSub">Kalender akademik resmi, agenda kegiatan sekolah, ujian, dan hari libur PKBM PIKAT.</div>
                        <p class="laporanHeaderDesc">Pantau agenda kegiatan resmi lembaga agar tidak berbenturan dengan aktivitas bimbingan KBM tutor.</p>
                    @else
                        <div class="laporanHeaderLabel">PEMANTAUAN KBM HARIAN</div>
                        <h1 class="laporanHeaderTitle">Jadwal Sesi KBM Seluruh Tutor</h1>
                        <div class="laporanHeaderSub">{{ $selectedDate->translatedFormat('l, d F Y') }}</div>
                        <p class="laporanHeaderDesc">Pantau aktivitas KBM harian, status kehadiran presensi tutor &amp; murid di semua wilayah, dan bantu reschedule sesi jika ada kendala.</p>
                    @endif
                </div>
                <div class="laporanHeaderActions header-actions-group">
                    @if ($activeTab === 'rutin')
                        <form action="{{ route('admin.jadwal.generateManualRutin') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="profileBtnPrimary btn-action-info" title="Generate sesi kalender 4 minggu ke depan dari pola rutin">
                                <ion-icon name="sync-outline" class="icon-sm"></ion-icon> Generate 4 Pekan
                            </button>
                        </form>
                        <button type="button" onclick="bukaModalJadwalAdmin(true)" class="profileBtnPrimary">
                            <ion-icon name="add-circle-outline" class="icon-sm"></ion-icon> Tambah Pola Rutin
                        </button>
                    @elseif ($activeTab === 'agenda')
                        <a href="{{ route('admin.jadwal.exportExcel') }}" class="profileBtnPrimary btn-action-success" title="Export Agenda ke Spreadsheet Excel">
                            <ion-icon name="document-outline" class="icon-sm"></ion-icon> Export Excel
                        </a>
                        <button type="button" onclick="bukaModalImportAgendaAdmin()" class="profileBtnPrimary btn-action-info" title="Import Agenda dari Spreadsheet">
                            <ion-icon name="cloud-upload-outline" class="icon-sm"></ion-icon> Import Agenda
                        </button>
                        <button type="button" onclick="bukaModalAgendaAdmin()" class="profileBtnPrimary">
                            <ion-icon name="add-circle-outline" class="icon-sm"></ion-icon> Tambah Agenda PKBM
                        </button>
                    @else
                        <button type="button" onclick="bukaModalJadwalAdmin(true)" class="profileBtnPrimary btn-action-info">
                            <ion-icon name="repeat-outline" class="icon-sm"></ion-icon> + Pola Rutin
                        </button>
                        <button type="button" onclick="bukaModalJadwalAdmin(false)" class="profileBtnPrimary">
                            <ion-icon name="add-circle-outline" class="icon-sm"></ion-icon> Buat Jadwal Belajar
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Alert Flash Message ── --}}
        @if (session('success'))
            <div class="max-w-4xl mx-auto mb-3 p-3 rounded-xl border border-success bg-success-subtle text-success text-sm d-flex items-center gap-2">
                <ion-icon name="checkmark-circle-outline" style="font-size: 20px; flex-shrink: 0;"></ion-icon>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('warning'))
            <div class="max-w-4xl mx-auto mb-3 p-3 rounded-xl border border-warning bg-warning-subtle text-warning text-sm d-flex items-center gap-2">
                <ion-icon name="warning-outline" style="font-size: 20px; flex-shrink: 0;"></ion-icon>
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="max-w-4xl mx-auto mb-3 p-3 rounded-xl border border-danger bg-danger-subtle text-danger text-sm">
                <div class="font-bold d-flex items-center gap-2 mb-1">
                    <ion-icon name="alert-circle-outline" style="font-size: 20px; flex-shrink: 0;"></ion-icon>
                    <span>Jadwal gagal disimpan karena kesalahan validasi:</span>
                </div>
                <ul class="mb-0 ps-4 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── Tab Switcher Navigasi Terpadu Admin (Unified Schedule Hub) ── --}}
        <div class="calendarNavTabsContainer">
            <a href="{{ route('admin.jadwal.index', ['tab' => 'sesi', 'tanggal' => $selectedDate->toDateString()]) }}"
                class="calendarNavTab {{ $activeTab === 'sesi' ? 'active' : '' }}">
                <ion-icon name="calendar-outline"></ion-icon>
                <span>Jadwal Sesi KBM (Harian)</span>
                <span class="calendarNavBadge">{{ $stats['total'] ?? 0 }}</span>
            </a>
            <a href="{{ route('admin.jadwal.index', ['tab' => 'rutin']) }}"
                class="calendarNavTab {{ $activeTab === 'rutin' ? 'active' : '' }}">
                <ion-icon name="repeat-outline"></ion-icon>
                <span>Pola Rutin KBM</span>
                <span class="calendarNavBadge">{{ $jadwalRutins->count() }}</span>
            </a>
            <a href="{{ route('admin.jadwal.index', ['tab' => 'agenda', 'tanggal' => $selectedDate->toDateString()]) }}"
                class="calendarNavTab {{ $activeTab === 'agenda' ? 'active' : '' }}">
                <ion-icon name="megaphone-outline"></ion-icon>
                <span>Agenda Acara PKBM</span>
                @if(isset($totalAgendaCount) && $totalAgendaCount > 0)
                    <span class="calendarNavBadge amber">{{ $totalAgendaCount }}</span>
                @endif
            </a>
        </div>

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- ── TAB 1: KALENDER & PEMANTAUAN SESI KBM HARIAN ── --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @if ($activeTab === 'sesi')
            <div class="max-w-4xl mx-auto pb-6">
                {{-- KPI Summary Cards --}}
                <div class="kpi-grid mb-4">
                    <div class="kpi-card emerald">
                        <div class="kpi-top">
                            <div class="kpi-label">Total Sesi Hari Ini</div>
                            <div class="kpi-icon-wrap">
                                <ion-icon name="calendar-outline"></ion-icon>
                            </div>
                        </div>
                        <div class="kpi-value">{{ $stats['total'] }}</div>
                        <div class="kpi-sub">{{ $selectedDate->translatedFormat('d M Y') }}</div>
                    </div>

                    <div class="kpi-card blue">
                        <div class="kpi-top">
                            <div class="kpi-label">Sesi Selesai (Presensi)</div>
                            <div class="kpi-icon-wrap">
                                <ion-icon name="checkmark-done-circle-outline"></ion-icon>
                            </div>
                        </div>
                        <div class="kpi-value">{{ $stats['selesai'] }}</div>
                        <div class="kpi-sub">KBM terlaksana</div>
                    </div>

                    <div class="kpi-card amber">
                        <div class="kpi-top">
                            <div class="kpi-label">Terjadwal / Menunggu</div>
                            <div class="kpi-icon-wrap">
                                <ion-icon name="time-outline"></ion-icon>
                            </div>
                        </div>
                        <div class="kpi-value">{{ $stats['terjadwal'] }}</div>
                        <div class="kpi-sub">Belum / sedang berlangsung</div>
                    </div>

                    <div class="kpi-card rose">
                        <div class="kpi-top">
                            <div class="kpi-label">Dibatalkan / Rescheduled</div>
                            <div class="kpi-icon-wrap">
                                <ion-icon name="close-circle-outline"></ion-icon>
                            </div>
                        </div>
                        <div class="kpi-value">{{ $stats['dibatalkan'] }}</div>
                        <div class="kpi-sub">Sesi dipindahkan</div>
                    </div>
                </div>

                {{-- Filter & Search Toolbar --}}
                <div class="cardBox mb-4">
                    <form action="{{ route('admin.jadwal.index') }}" method="GET" class="filterBar">
                        <input type="hidden" name="tab" value="sesi">
                        <div class="filterGrid">
                            <div class="filterField">
                                <label class="filterFieldLabel">Pilih Tanggal</label>
                                <input type="date" name="tanggal" value="{{ $selectedDate->toDateString() }}" class="profileInput">
                            </div>

                            <div class="filterField">
                                <label class="filterFieldLabel">Filter Tutor</label>
                                <select name="tutor_id" class="filterSelect">
                                    <option value="">-- Semua Tutor --</option>
                                    @foreach ($tutors as $t)
                                        <option value="{{ $t->id }}" {{ request('tutor_id') == $t->id ? 'selected' : '' }}>
                                             {{ $t->nama_lengkap }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="filterField">
                                <label class="filterFieldLabel">Status Sesi</label>
                                <select name="status" class="filterSelect">
                                    <option value="">-- Semua Status --</option>
                                    <option value="terjadwal" {{ request('status') === 'terjadwal' ? 'selected' : '' }}>Terjadwal</option>
                                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai Presensi</option>
                                    <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan / Rescheduled</option>
                                </select>
                            </div>

                            <div class="filterField">
                                <label class="filterFieldLabel">Cari Murid / Tutor</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama atau catatan..." class="profileInput">
                            </div>

                            <div class="filterField d-flex items-end gap-2">
                                <button type="submit" class="profileBtnPrimary w-full">
                                    <ion-icon name="filter-outline"></ion-icon> Filter
                                </button>
                                <a href="{{ route('admin.jadwal.index', ['tab' => 'sesi']) }}" class="btn-filter-reset" title="Reset Filter">
                                    <ion-icon name="refresh-outline"></ion-icon>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Kalender Sesi Bulanan (Style Persis Tutor) --}}
                <div class="agendaHeaderCard mb-4">
                    <div class="agendaMonthNav">
                        <a href="{{ route('admin.jadwal.index', array_merge(request()->except('tanggal'), ['tab' => 'sesi', 'tanggal' => $prevMonthDate])) }}"
                            class="agendaNavBtn" title="Bulan Sebelumnya">
                            <ion-icon name="chevron-back-outline"></ion-icon>
                        </a>
                        <div class="text-center">
                            <h3 class="agendaMonthTitle">{{ $selectedDate->translatedFormat('F Y') }}</h3>
                            <span class="text-xs font-bold text-muted">Pemantauan Sesi KBM Seluruh Tutor</span>
                        </div>
                        <a href="{{ route('admin.jadwal.index', array_merge(request()->except('tanggal'), ['tab' => 'sesi', 'tanggal' => $nextMonthDate])) }}"
                            class="agendaNavBtn" title="Bulan Berikutnya">
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
                            <a href="{{ route('admin.jadwal.index', array_merge(request()->except('tanggal'), ['tab' => 'sesi', 'tanggal' => $dStr])) }}"
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
                            <span>Sesi Belajar KBM</span>
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

                {{-- Banner Agenda Resmi PKBM Hari Ini (Jika Ada) --}}
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
                            <a href="{{ route('admin.jadwal.index', ['tab' => 'agenda', 'tanggal' => $selectedDate->toDateString()]) }}"
                                class="text-xs font-bold text-primary d-inline-flex items-center gap-1">
                                Lihat di Agenda <ion-icon name="arrow-forward-outline"></ion-icon>
                            </a>
                        </div>
                        @foreach($agendasHariIni as $agenda)
                            <div class="agendaNoticeItem">
                                <div class="agendaNoticeTitle">{{ $agenda->nama_agenda ?? ($agenda->judul ?? 'Agenda Sekolah') }}</div>
                                @if($agenda->deskripsi)
                                    <div class="agendaNoticeDesc">{{ $agenda->deskripsi }}</div>
                                @endif
                                @if($agenda->lokasiRingkasan() ?? $agenda->lokasi)
                                    <div class="agendaNoticeLocation d-flex items-center justify-between flex-wrap gap-1">
                                        <div class="d-flex items-center gap-1">
                                            <ion-icon name="location-outline"></ion-icon>
                                            <span>{{ $agenda->lokasiRingkasan() ?? $agenda->lokasi }}</span>
                                            @if($agenda->lokasiPresensi)
                                                <span class="app-badge badge-layanan-dl text-2xs">Titik Absen: {{ $agenda->lokasiPresensi->nama_lokasi }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Daftar Sesi Belajar KBM (Card View Persis Tutor) --}}
                @if($sesiHariIni->count() > 0)
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
                                @php
                                    $sesiMurid = $sesi->murid ?? $sesi->siswa;
                                    $tutor = $sesi->tutor;
                                    $jamMulai = substr((string)$sesi->jam_masuk_rencana, 0, 5);
                                    $jamSelesai = substr((string)$sesi->jam_pulang_rencana, 0, 5);
                                    $isDone = ($sesi->status === 'selesai' || $sesi->presensi_id);
                                    $isCancelled = ($sesi->status === 'dibatalkan');
                                    $lokasiSesi = $sesi->lokasiPresensi ?? $sesi->jadwalRutin?->lokasiPresensi;
                                @endphp
                                <div class="data-mobile-card">
                                    <div class="dmc-header">
                                        <div>
                                            <h3 class="dmc-title">{{ $sesiMurid->nama_murid ?? ($sesiMurid->nama_siswa ?? 'Murid') }}</h3>
                                            <div class="dmc-subtitle">
                                                <span class="app-badge {{ $sesi->jenis_sesi === 'pengganti' ? 'badge-layanan-dl' : 'badge-layanan-komunitas' }}">
                                                    {{ $sesi->jenis_label }}
                                                </span>
                                                @if($sesi->kategoriTutorial)
                                                    <span class="text-xs text-muted ml-1">&bull; {{ $sesi->kategoriTutorial->nama_kategori }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            @if($isDone)
                                                <span class="app-badge badge-status-aktif">Selesai Presensi</span>
                                            @elseif($isCancelled)
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
                                                <span class="font-bold text-dark text-sm">{{ $jamMulai }} - {{ $jamSelesai }} WIB</span>
                                                <span class="text-xs text-muted">({{ $sesi->durasi_jam ?? 2 }} Jam)</span>
                                            </div>
                                        </div>

                                        <div class="dmc-field">
                                            <div class="dmc-label">Tutor Pengampu</div>
                                            <div class="dmc-value font-bold text-dark text-xs">
                                                {{ $tutor?->nama_lengkap ?? 'Tutor Tidak Ditemukan' }}
                                            </div>
                                        </div>

                                        <div class="dmc-field">
                                            <div class="dmc-label">Kehadiran Murid</div>
                                            <div class="dmc-value">
                                                @if($sesi->status_kehadiran_siswa === 'hadir' || $sesi->status_kehadiran_murid === 'hadir')
                                                    <span class="app-badge badge-status-aktif">Hadir Mandiri</span>
                                                @elseif($sesi->status_kehadiran_siswa === 'izin' || $sesi->status_kehadiran_murid === 'izin')
                                                    <span class="app-badge badge-abk">Izin</span>
                                                @elseif($sesi->status_kehadiran_siswa === 'sakit' || $sesi->status_kehadiran_murid === 'sakit')
                                                    <span class="app-badge badge-status-cuti">Sakit</span>
                                                @else
                                                    <span class="text-xs text-muted">Belum Presensi</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="dmc-field">
                                            <div class="dmc-label">Rombel / Kelas</div>
                                            <div class="dmc-value text-xs font-semibold">
                                                {{ $sesiMurid?->nama_kelas_lengkap ?? ($sesiMurid?->jenjang_paket_label ?? 'Reguler') }}
                                                @if($sesiMurid?->is_abk)
                                                    <span class="app-badge text-[10px] py-0 px-1 ml-1" style="background:#fff1f2; color:#be123c;">ABK</span>
                                                @endif
                                            </div>
                                        </div>

                                        @if($lokasiSesi)
                                            <div class="dmc-field full">
                                                <div class="dmc-label">Titik Lokasi Presensi KBM</div>
                                                <div class="dmc-value d-flex items-center justify-between">
                                                    <span class="d-inline-flex items-center gap-1 text-xs font-semibold text-dark">
                                                        <ion-icon name="location" class="text-danger"></ion-icon>
                                                        {{ $lokasiSesi->nama_lokasi }}
                                                        @if($lokasiSesi->radius_meter)
                                                            <span class="text-muted font-normal">({{ $lokasiSesi->radius_meter }}m)</span>
                                                        @endif
                                                    </span>
                                                    @if($lokasiSesi->latitude && $lokasiSesi->longitude)
                                                        <a href="https://www.google.com/maps?q={{ $lokasiSesi->latitude }},{{ $lokasiSesi->longitude }}"
                                                            target="_blank" rel="noopener noreferrer"
                                                            class="text-xs text-primary font-bold d-inline-flex items-center gap-1 hover:underline">
                                                            <ion-icon name="navigate-outline"></ion-icon> Peta
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        @if($sesi->tanggal_asli)
                                            <div class="dmc-field full">
                                                <div class="dmc-label">Menggantikan Sesi Sebelumnya</div>
                                                <div class="dmc-value">
                                                    <span class="font-bold text-primary text-xs">{{ $sesi->tanggal_asli->translatedFormat('l, d F Y') }}</span>
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
                                            @if(!$isDone && !$isCancelled)
                                                <button type="button" class="smallBtn btn-table-action"
                                                    onclick="bukaModalRescheduleAdmin({{ $sesi->id }}, '{{ addslashes($sesiMurid?->nama_murid ?? 'Murid') }}', '{{ addslashes($tutor?->nama_lengkap ?? 'Tutor') }}', '{{ $sesi->tanggal_rencana->toDateString() }}', '{{ $jamMulai }}', '{{ $jamSelesai }}', '{{ $sesi->lokasi_presensi_id }}')"
                                                    title="Reschedule Sesi Belajar">
                                                    <ion-icon name="swap-horizontal-outline"></ion-icon> Reschedule
                                                </button>

                                                <button type="button" class="smallBtn edit btn-table-action"
                                                    onclick="bukaModalEditSesiAdmin({{ $sesi->id }}, {{ $sesi->tutor_id }}, {{ $sesi->siswa_id ?? $sesi->murid_id }}, '{{ $sesi->kategori_tutorial_id }}', '{{ $sesi->tanggal_rencana->toDateString() }}', '{{ $jamMulai }}', '{{ $jamSelesai }}', '{{ $sesi->lokasi_presensi_id }}', '{{ addslashes($sesi->catatan ?? '') }}')"
                                                    title="Edit Jam atau Lokasi Sesi">
                                                    <ion-icon name="create-outline"></ion-icon> Edit
                                                </button>

                                                <form action="{{ route('admin.jadwal.destroy', $sesi->id) }}" method="POST"
                                                    class="d-inline m-0"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin membatalkan dan menghapus sesi KBM ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="smallBtn delete btn-table-action cursor-pointer" title="Hapus sesi">
                                                        <ion-icon name="trash-outline"></ion-icon> Hapus
                                                    </button>
                                                </form>
                                            @endif

                                            @if($sesi->presensi)
                                                <a href="{{ route('admin.laporan.show', $sesi->presensi->id) }}" class="smallBtn btn-action-success text-xs">
                                                    <ion-icon name="eye-outline"></ion-icon> Cek Presensi
                                                </a>
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
                            <ion-icon name="calendar-clear-outline"></ion-icon>
                        </div>
                        <div class="emptyAgendaTitle">Tidak Ada Sesi Belajar Terjadwal</div>
                        <div class="emptyAgendaDesc">Tidak ada aktivitas bimbingan belajar KBM pada <strong>{{ $selectedDate->translatedFormat('l, d F Y') }}</strong> untuk filter yang dipilih.</div>
                        <div class="mt-3">
                            <button type="button" onclick="bukaModalJadwalAdmin(false)" class="profileBtnPrimary text-xs py-2 px-4 d-inline-flex items-center gap-1">
                                <ion-icon name="add-circle-outline"></ion-icon> Buat Jadwal Belajar Sekarang
                            </button>
                        </div>
                    </div>
                @endif
            </div>

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- ── TAB 2: MASTER POLA RUTIN MINGGUAN KBM ── --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @elseif ($activeTab === 'rutin')
            <div class="max-w-4xl mx-auto pb-6">
                {{-- Filter Bar Pola Rutin --}}
                <div class="cardBox mb-4">
                    <form action="{{ route('admin.jadwal.index') }}" method="GET" class="filterBar">
                        <input type="hidden" name="tab" value="rutin">
                        <div class="filterGrid">
                            <div class="filterField">
                                <label class="filterFieldLabel">Filter Tutor</label>
                                <select name="tutor_id" class="filterSelect">
                                    <option value="">-- Semua Tutor --</option>
                                    @foreach ($tutors as $t)
                                        <option value="{{ $t->id }}" {{ request('tutor_id') == $t->id ? 'selected' : '' }}>
                                            {{ $t->nama_lengkap }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="filterField">
                                <label class="filterFieldLabel">Filter Hari</label>
                                <select name="hari" class="filterSelect">
                                    <option value="">-- Semua Hari --</option>
                                    <option value="senin" {{ request('hari') === 'senin' ? 'selected' : '' }}>Senin</option>
                                    <option value="selasa" {{ request('hari') === 'selasa' ? 'selected' : '' }}>Selasa</option>
                                    <option value="rabu" {{ request('hari') === 'rabu' ? 'selected' : '' }}>Rabu</option>
                                    <option value="kamis" {{ request('hari') === 'kamis' ? 'selected' : '' }}>Kamis</option>
                                    <option value="jumat" {{ request('hari') === 'jumat' ? 'selected' : '' }}>Jumat</option>
                                    <option value="sabtu" {{ request('hari') === 'sabtu' ? 'selected' : '' }}>Sabtu</option>
                                    <option value="minggu" {{ request('hari') === 'minggu' ? 'selected' : '' }}>Minggu</option>
                                </select>
                            </div>

                            <div class="filterField">
                                <label class="filterFieldLabel">Cari Murid / Keterangan</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama murid..." class="profileInput">
                            </div>

                            <div class="filterField d-flex items-end gap-2">
                                <button type="submit" class="profileBtnPrimary w-full">
                                    <ion-icon name="filter-outline"></ion-icon> Filter
                                </button>
                                <a href="{{ route('admin.jadwal.index', ['tab' => 'rutin']) }}" class="btn-filter-reset" title="Reset Filter">
                                    <ion-icon name="refresh-outline"></ion-icon>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                @if ($jadwalRutins->count() > 0)
                    <div class="agendaBannerBox rutin">
                        <div class="agendaBannerHeader rutin">
                            <div class="flex-items-center gap-2">
                                <span class="agendaBannerBadge rutin">
                                    Master Pola Rutin KBM
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
                                    $rutinMurid = $rutin->murid ?? $rutin->siswa;
                                    $jamMulai = substr((string)$rutin->jam_masuk, 0, 5);
                                    $jamSelesai = substr((string)$rutin->jam_pulang, 0, 5);
                                @endphp
                                <div class="data-mobile-card">
                                    <div class="dmc-header">
                                        <div>
                                            <h3 class="dmc-title">{{ $rutinMurid->nama_murid ?? ($rutinMurid->nama_siswa ?? 'Murid') }}</h3>
                                            <div class="dmc-subtitle">
                                                No. Absen: <strong>{{ $rutinMurid->no_absen ?? '-' }}</strong> ·
                                                {{ $rutinMurid->nama_kelas_lengkap ?? 'Reguler' }}
                                            </div>
                                        </div>
                                        <div class="flex-col gap-1 flex-items-end">
                                            <span class="app-badge badge-hari-{{ $hariSlug }}">
                                                {{ strtoupper((string) ($rutin->nama_hari_label ?? $rutin->hari)) }}
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
                                            <div class="dmc-value text-primary font-bold">
                                                {{ $jamMulai }} - {{ $jamSelesai }} WIB
                                            </div>
                                        </div>

                                        <div class="dmc-field">
                                            <div class="dmc-label">Tutor Pengampu</div>
                                            <div class="dmc-value font-bold text-dark text-xs">
                                                {{ $rutin->tutor?->nama_lengkap ?? '-' }}
                                            </div>
                                        </div>

                                        <div class="dmc-field">
                                            <div class="dmc-label">Kategori SK / Durasi</div>
                                            <div class="dmc-value">{{ $rutin->kategoriTutorial->nama_kategori ?? 'Tutorial KBM' }}
                                                ({{ number_format((float) ($rutin->durasi_jam ?? 2), 1) }} Jam)</div>
                                        </div>

                                        <div class="dmc-field">
                                            <div class="dmc-label">Sesi Tergenerate</div>
                                            <div class="dmc-value font-bold">{{ $rutin->jadwal_sesis_count ?? 0 }} Sesi Kalender</div>
                                        </div>

                                        <div class="dmc-field">
                                            <div class="dmc-label">Masa Berlaku</div>
                                            <div class="dmc-value text-xs font-semibold">
                                                Mulai: {{ $rutin->berlaku_mulai ? (is_object($rutin->berlaku_mulai) ? $rutin->berlaku_mulai->format('d/m/Y') : $rutin->berlaku_mulai) : 'Sekarang' }}
                                                @if ($rutin->berlaku_sampai)
                                                    <br>Selesai: {{ is_object($rutin->berlaku_sampai) ? $rutin->berlaku_sampai->format('d/m/Y') : $rutin->berlaku_sampai }}
                                                @else
                                                    <br><span class="text-muted">(Terus berlanjut)</span>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($rutin->lokasiPresensi)
                                            <div class="dmc-field full">
                                                <div class="dmc-label">Titik Lokasi Presensi Rutin</div>
                                                <div class="dmc-value d-flex items-center justify-between">
                                                    <span class="d-inline-flex items-center gap-1 text-xs font-semibold text-dark">
                                                        <ion-icon name="location" class="text-danger"></ion-icon>
                                                        {{ $rutin->lokasiPresensi->nama_lokasi }}
                                                        @if($rutin->lokasiPresensi->radius_meter)
                                                            <span class="text-muted font-normal">({{ $rutin->lokasiPresensi->radius_meter }}m)</span>
                                                        @endif
                                                    </span>
                                                    @if($rutin->lokasiPresensi->latitude && $rutin->lokasiPresensi->longitude)
                                                        <a href="https://www.google.com/maps?q={{ $rutin->lokasiPresensi->latitude }},{{ $rutin->lokasiPresensi->longitude }}"
                                                            target="_blank" rel="noopener noreferrer"
                                                            class="text-xs text-primary font-bold d-inline-flex items-center gap-1 hover:underline">
                                                            <ion-icon name="navigate-outline"></ion-icon> Peta
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        @if ($rutin->keterangan)
                                            <div class="dmc-field full">
                                                <div class="dmc-label">Catatan / Keterangan</div>
                                                <div class="dmc-value text-xs text-muted">{{ $rutin->keterangan }}</div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="dmc-footer">
                                        <div class="text-xs text-muted">
                                            Dibuat: {{ is_object($rutin->created_at) ? $rutin->created_at->translatedFormat('d M Y') : '-' }}
                                        </div>
                                        <div class="dmc-actions">
                                            <form action="{{ route('admin.jadwal.toggleStatusRutin', $rutin->id) }}" method="POST" class="d-inline m-0">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="smallBtn btn-table-action"
                                                    title="{{ $rutin->is_active ? 'Pause Pola Rutin' : 'Aktifkan Pola Rutin' }}"
                                                    style="background: var(--card-alt); border: 1px solid var(--border); color: {{ $rutin->is_active ? '#d97706' : '#059669' }};">
                                                    <ion-icon name="{{ $rutin->is_active ? 'pause-circle-outline' : 'play-circle-outline' }}"></ion-icon>
                                                    {{ $rutin->is_active ? 'Pause' : 'Aktifkan' }}
                                                </button>
                                            </form>

                                            <button type="button" class="smallBtn edit btn-table-action"
                                                data-rutin-id="{{ $rutin->id }}"
                                                onclick="bukaModalEditRutinAdmin({{ $rutin->id }}, {{ $rutin->murid_id ?? $rutin->siswa_id }}, {{ $rutin->tutor_id }}, '{{ $rutin->hari }}', '{{ $jamMulai }}', '{{ $jamSelesai }}', '{{ $rutin->kategori_tutorial_id }}', '{{ $rutin->lokasi_presensi_id }}', '{{ addslashes($rutin->keterangan ?? '') }}')"
                                                title="Edit Pola Rutin">
                                                <ion-icon name="create-outline"></ion-icon> Edit
                                            </button>

                                            <form action="{{ route('admin.jadwal.destroyRutin', $rutin->id) }}" method="POST"
                                                class="d-inline m-0"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus pola jadwal rutin ini? Sesi kalender masa depan yang belum presensi akan dihentikan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="smallBtn delete cursor-pointer btn-table-action" title="Hapus Pola Rutin">
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
                        <div class="emptyAgendaTitle">Belum Ada Master Pola Rutin KBM</div>
                        <div class="emptyAgendaDesc">Belum ada pola jadwal berulang mingguan yang tercatat. Pola rutin otomatis men-generate sesi kalender belajar murid setiap pekannya.</div>
                        <div class="mt-3">
                            <button type="button" onclick="bukaModalJadwalAdmin(true)"
                                class="profileBtnPrimary text-xs py-2 px-4 d-inline-flex items-center gap-1"
                                style="background: #059669;">
                                <ion-icon name="add-circle-outline"></ion-icon> + Tambah Pola Rutin Sekarang
                            </button>
                        </div>
                    </div>
                @endif
            </div>

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- ── TAB 3: KALENDER AGENDA & PENGUMUMAN RESMI PKBM ── --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @elseif ($activeTab === 'agenda')
            <div class="max-w-4xl mx-auto pb-6">
                {{-- Kalender Bulanan Agenda (Style Persis Tutor) --}}
                <div class="agendaHeaderCard mb-4">
                    <div class="agendaMonthNav">
                        <a href="{{ route('admin.jadwal.index', ['tab' => 'agenda', 'tanggal' => $prevMonthDate]) }}"
                            class="agendaNavBtn" title="Bulan Sebelumnya">
                            <ion-icon name="chevron-back-outline"></ion-icon>
                        </a>
                        <div class="text-center">
                            <h3 class="agendaMonthTitle">{{ $selectedDate->translatedFormat('F Y') }}</h3>
                            <span class="text-xs font-bold text-muted">Agenda &amp; Kegiatan Resmi Sekolah</span>
                        </div>
                        <a href="{{ route('admin.jadwal.index', ['tab' => 'agenda', 'tanggal' => $nextMonthDate]) }}"
                            class="agendaNavBtn" title="Bulan Berikutnya">
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
                            <a href="{{ route('admin.jadwal.index', ['tab' => 'agenda', 'tanggal' => $dStr]) }}"
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
                            <span>Sesi Belajar KBM</span>
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

                {{-- Ringkasan Sesi Mengajar pada tanggal ini jika ada --}}
                @if(isset($sesiHariIni) && $sesiHariIni->count() > 0)
                    <div class="sesiSummaryBox mb-4">
                        <div class="sesiSummaryHeader">
                            <div class="d-flex items-center gap-2">
                                <span class="app-badge badge-reguler">
                                    <ion-icon name="school-outline"></ion-icon> Sesi Belajar Terjadwal
                                </span>
                                <span class="text-xs font-bold text-dark">{{ $sesiHariIni->count() }} Sesi KBM Hari Ini</span>
                            </div>
                            <a href="{{ route('admin.jadwal.index', ['tab' => 'sesi', 'tanggal' => $selectedDate->toDateString()]) }}"
                                class="text-xs font-bold text-primary d-inline-flex items-center gap-1">
                                Kelola di Kalender Sesi <ion-icon name="arrow-forward-outline"></ion-icon>
                            </a>
                        </div>
                        <div>
                            @foreach($sesiHariIni as $sesi)
                                @php $sesiMurid = $sesi->murid ?? $sesi->siswa; @endphp
                                <div class="sesiSummaryItem">
                                    <div>
                                        <div class="font-bold text-sm text-dark">{{ $sesiMurid->nama_murid ?? ($sesiMurid->nama_siswa ?? 'Murid') }}</div>
                                        <div class="text-xs text-muted">
                                            {{ $sesi->tutor?->nama_lengkap ?? '-' }} &bull;
                                            {{ $sesi->kategoriTutorial->nama_kategori ?? 'Tutorial KBM' }} &bull;
                                            {{ $sesi->durasi_jam ?? 2 }} Jam
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs font-extrabold text-primary">{{ substr((string)$sesi->jam_masuk_rencana, 0, 5) }} - {{ substr((string)$sesi->jam_pulang_rencana, 0, 5) }} WIB</div>
                                        <span class="app-badge {{ $sesi->status_badge_class ?? 'badge-reguler' }} text-2xs">{{ ucfirst($sesi->status) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Daftar Agenda Resmi PKBM pada Tanggal Terpilih (Card View Persis Tutor) --}}
                @if($agendasHariIni->count() > 0)
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
                                    $judul = $j->nama_agenda ?? ($j->judul ?? 'Agenda Kegiatan');
                                    $deskripsi = $j->deskripsi ?? null;
                                    $lokasi = $j->lokasiRingkasan() ?? $j->lokasi;
                                    $isLibur = str_contains(strtolower($judul), 'libur') || str_contains(strtolower((string)$deskripsi), 'libur');
                                @endphp

                                <div class="agendaCardItem {{ $isToday ? 'today' : '' }}">
                                    <div class="d-flex items-center justify-between gap-2 mb-1">
                                        <span class="app-badge {{ $isLibur ? 'badge-status-nonaktif' : 'badge-status-cuti' }}">
                                            {{ $isLibur ? 'Hari Libur / Cuti' : 'Agenda Resmi PKBM' }}
                                        </span>
                                        <span class="text-xs text-muted">
                                            {{ is_object($j->tanggal) ? $j->tanggal->translatedFormat('d M Y') : $j->tanggal }}
                                        </span>
                                    </div>

                                    <h4 class="text-lg font-extrabold text-dark m-0 mb-1">
                                        {{ $judul }}
                                    </h4>

                                    @if($deskripsi)
                                        <div class="text-sm text-muted mb-2">
                                            {{ $deskripsi }}
                                        </div>
                                    @endif

                                    @if($lokasi)
                                        <div class="agendaMetaRow d-flex items-center justify-between">
                                            <div class="d-flex items-center gap-1 flex-wrap">
                                                <ion-icon name="location-outline" class="text-primary"></ion-icon>
                                                <span><strong class="text-dark">Lokasi:</strong> {{ $lokasi }}</span>
                                                @if($j->lokasiPresensi)
                                                    <span class="app-badge badge-layanan-dl text-2xs">Titik Absen: {{ $j->lokasiPresensi->nama_lokasi }}</span>
                                                @endif
                                            </div>
                                            @if($j->lokasiPetaUrl())
                                                <a href="{{ $j->lokasiPetaUrl() }}" target="_blank" rel="noopener noreferrer"
                                                    class="text-xs text-primary font-bold d-inline-flex items-center gap-1 hover:underline ml-2">
                                                    <ion-icon name="navigate-outline"></ion-icon> Petunjuk Peta
                                                </a>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="d-flex items-center justify-end gap-2 mt-3 pt-2 border-t-base">
                                        <button type="button" class="smallBtn edit btn-table-action"
                                            data-agenda-id="{{ $j->id }}"
                                            onclick="bukaModalEditAgendaAdmin({{ $j->id }}, '{{ addslashes($judul) }}', '{{ is_object($j->tanggal) ? $j->tanggal->format('Y-m-d') : substr((string)$j->tanggal, 0, 10) }}', '{{ $j->lokasi_presensi_id }}', '{{ addslashes($j->lokasi ?? '') }}', '{{ addslashes($deskripsi ?? '') }}')"
                                            title="Edit Agenda">
                                            <ion-icon name="create-outline"></ion-icon> Edit
                                        </button>

                                        <form action="{{ route('admin.jadwal.destroyAgenda', $j->id) }}" method="POST"
                                            class="d-inline m-0"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus agenda kegiatan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="smallBtn delete btn-table-action cursor-pointer" title="Hapus Agenda">
                                                <ion-icon name="trash-outline"></ion-icon> Hapus
                                            </button>
                                        </form>
                                    </div>
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
                        <div class="emptyAgendaDesc">Belum ada agenda sekolah atau kegiatan akademik terjadwal pada <strong>{{ $selectedDate->translatedFormat('l, d F Y') }}</strong>.</div>
                        <div class="mt-3">
                            <button type="button" onclick="bukaModalAgendaAdmin()" class="profileBtnPrimary text-xs py-2 px-4 d-inline-flex items-center gap-1">
                                <ion-icon name="add-circle-outline"></ion-icon> + Tambah Agenda PKBM Sekarang
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- ── MODAL 1: BUAT JADWAL BELAJAR ADMIN (UNIFIED MODAL) ── --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div id="modalJadwalBaruAdmin" class="navDrawerBackdrop" onclick="if(event.target === this) tutupModalJadwalAdmin()">
        <div class="navDrawerSheet max-w-lg mx-auto" onclick="event.stopPropagation()">
            <div class="navDrawerHandle"></div>

            <div class="navDrawerHeader">
                <div class="navDrawerTitleWrap">
                    <div class="navDrawerSub">Perencanaan KBM • Administrasi Terpusat</div>
                    <h3 class="navDrawerTitle" id="modalJadwalTitleAdmin">Buat Jadwal Belajar</h3>
                </div>
                <button type="button" class="navDrawerCloseBtn" onclick="tutupModalJadwalAdmin()" aria-label="Tutup">
                    <ion-icon name="close"></ion-icon>
                </button>
            </div>

            <div class="navDrawerBody">
                <form method="POST" action="{{ route('admin.jadwal.store') }}" id="formTambahJadwalAdmin" onsubmit="return validateMuridSelectionAdmin(event)">
                    @csrf

                    {{-- Switcher Mode: Rutin vs Single --}}
                    <div class="p-2 bg-card-alt rounded-xl border-base mb-3 d-flex gap-1">
                        <label class="cursor-pointer flex-1 text-center py-2 px-3 rounded-lg text-xs font-bold transition-all"
                            id="labelModeRutinAdmin" onclick="setScheduleModeAdmin(true)">
                            <input type="radio" name="is_recurring" id="radioModeRutinAdmin" value="1" class="d-none" {{ old('is_recurring') === '1' ? 'checked' : '' }}>
                            <ion-icon name="repeat-outline"></ion-icon> Ulangi Setiap Minggu (Rutin)
                        </label>
                        <label class="cursor-pointer flex-1 text-center py-2 px-3 rounded-lg text-xs font-bold transition-all"
                            id="labelModeSingleAdmin" onclick="setScheduleModeAdmin(false)">
                            <input type="radio" name="is_recurring" id="radioModeSingleAdmin" value="0" class="d-none" {{ old('is_recurring', '0') === '0' ? 'checked' : '' }}>
                            <ion-icon name="calendar-outline"></ion-icon> Hanya Tanggal Ini (Sekali)
                        </label>
                    </div>

                    {{-- Pilihan Tutor Pengampu (Wajib Admin) --}}
                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Tutor Pengampu <span class="text-danger">*</span></label>
                        <select name="tutor_id" id="modalTutorIdAdmin" class="filterSelect" required onchange="filterMuridListAdmin()">
                            <option value="">-- Pilih Tutor Pengampu --</option>
                            @foreach($tutors as $tut)
                                <option value="{{ $tut->id }}" {{ (string)old('tutor_id') === (string)$tut->id ? 'selected' : '' }}>
                                    {{ $tut->nama_lengkap }} ({{ $tut->user?->email ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Multi-Murid & Rombel Picker --}}
                    <div class="form-field-wrapper mb-3">
                        <div class="murid-picker-header mb-1">
                            <label class="filterFieldLabel m-0">
                                Pilih Murid Diajar <span class="text-danger">*</span>
                                <span class="text-xs text-muted font-normal">(Bisa pilih lebih dari 1 murid)</span>
                            </label>
                            <span id="selectedMuridCounterAdmin" class="murid-picker-badge-counter">
                                <ion-icon name="people-outline"></ion-icon>
                                <span id="selectedCountTextAdmin">0 murid terpilih</span>
                            </span>
                        </div>

                        <div class="murid-picker-toolbar mb-2">
                            {{-- Filter Berdasarkan Rombel / Bimbingan --}}
                            <select id="pickerFilterRombelAdmin" class="murid-picker-filter-select" onchange="filterMuridListAdmin()">
                                <option value="all">-- Semua Rombel & Jenjang --</option>
                                <option value="bimbingan_tutor">★ Murid Bimbingan Tutor Ini Saja</option>
                                <optgroup label="Kelompok Jenjang">
                                    <option value="paket_a">Semua Paket A (Setara SD)</option>
                                    <option value="paket_b">Semua Paket B (Setara SMP)</option>
                                    <option value="paket_c">Semua Paket C (Setara SMA)</option>
                                    <option value="vokasi">Semua Vokasi / Kursus</option>
                                </optgroup>
                                @if(isset($kelasList) && $kelasList->isNotEmpty())
                                    <optgroup label="Spesifik Rombel / Kelas">
                                        @foreach($kelasList as $k)
                                            <option value="kelas_{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>

                            {{-- Input Pencarian Nama --}}
                            <input type="text" id="pickerSearchMuridAdmin" class="murid-picker-search"
                                placeholder="Cari nama / no. absen..." oninput="filterMuridListAdmin()">

                            {{-- Aksi Cepat Pilih --}}
                            <div class="murid-picker-actions">
                                <button type="button" class="btn-picker-action" onclick="selectAllVisibleMuridAdmin(true)"
                                    title="Pilih semua murid yang sedang tampil di filter ini">
                                    <ion-icon name="checkmark-done-outline"></ion-icon>
                                    <span>Pilih Semua di Rombel</span>
                                </button>
                                <button type="button" class="btn-picker-action btn-picker-clear" onclick="selectAllVisibleMuridAdmin(false)"
                                    title="Batalkan pilihan murid terpilih">
                                    <ion-icon name="close-outline"></ion-icon>
                                    <span>Batal</span>
                                </button>
                            </div>
                        </div>

                        {{-- Kotak Checklist Murid --}}
                        <div class="murid-picker-container" id="muridPickerContainerAdmin">
                            @php
                                $oldSiswaIds = old('siswa_ids', old('siswa_id') ? [old('siswa_id')] : []);
                                if (!is_array($oldSiswaIds)) {
                                    $oldSiswaIds = [$oldSiswaIds];
                                }
                            @endphp
                            @forelse($siswas as $s)
                                @php
                                    $jenjangKode = $s->jenjang_paket ?? 'umum';
                                    $namaMurid = $s->nama_murid ?? $s->nama_siswa ?? 'Murid';
                                    $initial = strtoupper(substr($namaMurid, 0, 1));
                                    $isChecked = in_array($s->id, $oldSiswaIds);
                                    $badgeClass = match($jenjangKode) {
                                        'paket_a' => 'badge-paket-a',
                                        'paket_b' => 'badge-paket-b',
                                        'paket_c' => 'badge-paket-c',
                                        default => '',
                                    };
                                @endphp
                                <label class="murid-picker-item {{ $isChecked ? 'is-checked' : '' }}"
                                    id="muridItemAdmin_{{ $s->id }}"
                                    data-kelas-id="{{ $s->kelas_id }}"
                                    data-jenjang="{{ $jenjangKode }}"
                                    data-tutor-id="{{ $s->tutor_id }}"
                                    data-nama="{{ strtolower($namaMurid) }}"
                                    data-absen="{{ strtolower((string)$s->no_absen) }}">
                                    <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}"
                                        class="murid-picker-checkbox murid-checkbox-admin"
                                        {{ $isChecked ? 'checked' : '' }}
                                        onchange="onMuridCheckboxChangeAdmin(this)">
                                    
                                    <div class="murid-picker-avatar">
                                        {{ $initial }}
                                    </div>

                                    <div class="murid-picker-info">
                                        <div class="murid-picker-name">
                                            {{ $namaMurid }}
                                        </div>
                                        <div class="murid-picker-meta">
                                            @if($s->no_absen)
                                                <span class="murid-picker-badge-rombel">No. {{ $s->no_absen }}</span>
                                            @endif
                                            <span class="murid-picker-badge-rombel {{ $badgeClass }}">
                                                {{ $s->nama_kelas_lengkap ?? $s->jenjang_paket_label }}
                                            </span>
                                            @if($s->tutor)
                                                <span class="murid-picker-badge-bimbingan">Bimb. {{ $s->tutor->nama_lengkap }}</span>
                                            @endif
                                            @if($s->is_abk)
                                                <span class="murid-picker-badge-rombel" style="background:#fff1f2; color:#be123c; border-color:#fecdd3;">ABK</span>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @empty
                                <div class="p-4 text-center text-muted text-xs">
                                    Belum ada data murid aktif.
                                </div>
                            @endforelse
                        </div>

                        <div id="muridPickerErrorAdmin" class="text-xs text-danger font-semibold mt-1 d-none">
                            <ion-icon name="alert-circle-outline"></ion-icon> Silakan pilih minimal 1 murid untuk dijadwalkan.
                        </div>
                    </div>

                    {{-- Form Bagian A: Jika Mode Rutin Mingguan --}}
                    <div id="sectionRutinAdmin" class="d-none">
                        <div class="form-field-wrapper mb-3">
                            <label class="filterFieldLabel">Hari Belajar Mingguan <span class="text-danger">*</span></label>
                            <select name="hari" id="modalHariAdmin" class="filterSelect">
                                <option value="senin" {{ old('hari') === 'senin' ? 'selected' : '' }}>Setiap Hari Senin</option>
                                <option value="selasa" {{ old('hari') === 'selasa' ? 'selected' : '' }}>Setiap Hari Selasa</option>
                                <option value="rabu" {{ old('hari') === 'rabu' ? 'selected' : '' }}>Setiap Hari Rabu</option>
                                <option value="kamis" {{ old('hari') === 'kamis' ? 'selected' : '' }}>Setiap Hari Kamis</option>
                                <option value="jumat" {{ old('hari') === 'jumat' ? 'selected' : '' }}>Setiap Hari Jumat</option>
                                <option value="sabtu" {{ old('hari') === 'sabtu' ? 'selected' : '' }}>Setiap Hari Sabtu</option>
                                <option value="minggu" {{ old('hari') === 'minggu' ? 'selected' : '' }}>Setiap Hari Minggu</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div class="form-field-wrapper">
                                <label class="filterFieldLabel">Berlaku Mulai</label>
                                <input type="date" name="berlaku_mulai" value="{{ old('berlaku_mulai', date('Y-m-d')) }}" class="profileInput">
                            </div>
                            <div class="form-field-wrapper">
                                <label class="filterFieldLabel">Berlaku Sampai</label>
                                <input type="date" name="berlaku_sampai" value="{{ old('berlaku_sampai') }}" class="profileInput">
                                <span class="field-help-text">Kosongkan jika terus berlanjut tanpa batas.</span>
                            </div>
                        </div>
                    </div>

                    {{-- Form Bagian B: Jika Mode Single Sesi --}}
                    <div id="sectionSingleAdmin">
                        <div class="form-field-wrapper mb-3">
                            <label class="filterFieldLabel">Jenis Sesi <span class="text-danger">*</span></label>
                            <select name="jenis_sesi" id="modalJenisSesiAdmin" onchange="toggleTanggalAsliAdmin()" class="filterSelect">
                                <option value="reguler" {{ old('jenis_sesi') === 'reguler' ? 'selected' : '' }}>Sesi Reguler</option>
                                <option value="pengganti" {{ old('jenis_sesi') === 'pengganti' ? 'selected' : '' }}>Sesi Pengganti (Reschedule / Make-up Class)</option>
                                <option value="tambahan" {{ old('jenis_sesi') === 'tambahan' ? 'selected' : '' }}>Sesi Tambahan / Pengayaan</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div class="form-field-wrapper">
                                <label class="filterFieldLabel">Tanggal Rencana <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_rencana" id="modalTanggalRencanaAdmin"
                                    value="{{ old('tanggal_rencana', $selectedDate->format('Y-m-d')) }}" class="profileInput">
                            </div>

                            <div class="form-field-wrapper d-none" id="boxTanggalAsliAdmin">
                                <label class="filterFieldLabel">Menggantikan Sesi Tanggal</label>
                                <input type="date" name="tanggal_asli" value="{{ old('tanggal_asli') }}" class="profileInput">
                                <span class="field-help-text">Tanggal sesi semula yang dilewati.</span>
                            </div>
                        </div>
                    </div>

                    {{-- Kategori SK --}}
                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Kategori Tutorial SK <span class="text-xs text-muted font-normal">(Standar Tarif Resmi)</span></label>
                        <select name="kategori_tutorial_id" id="modalKategoriTutorialAdmin" onchange="kategoriUniversalChangeAdmin()" class="filterSelect">
                            <option value="">-- Pilih Kategori SK (Otomatis Atur Jam) --</option>
                            @foreach($kategoriTutorials as $kat)
                                <option value="{{ $kat->id }}" data-durasi="{{ $kat->durasi_jam }}" data-nominal="{{ $kat->nominal_honor }}"
                                    {{ (string) old('kategori_tutorial_id') === (string) $kat->id ? 'selected' : '' }}>
                                    {{ $kat->nama_kategori }} ({{ $kat->durasi_jam }} Jam &bull; {{ $kat->formatted_nominal_honor }})
                                </option>
                            @endforeach
                            <option value="custom" {{ old('kategori_tutorial_id') === 'custom' ? 'selected' : '' }}>-- Durasi Khusus (Belum Diatur SK) --</option>
                        </select>
                    </div>

                    {{-- Jam Belajar & Durasi --}}
                    <div class="p-3 bg-card-alt rounded-xl border-base mb-3">
                        <div class="text-xs font-bold uppercase tracking-wider text-muted mb-2">
                            <ion-icon name="time-outline"></ion-icon> Jam Belajar &amp; Durasi KBM
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <div class="form-field-wrapper">
                                <label class="filterFieldLabel">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_masuk" id="modalJamMasukRutinAdmin"
                                    value="{{ old('jam_masuk', '10:00') }}" onchange="hitungDurasiUniversalAdmin(true)" class="profileInput d-none">
                                <input type="time" name="jam_masuk_rencana" id="modalJamMasukSingleAdmin"
                                    value="{{ old('jam_masuk_rencana', '10:00') }}" onchange="hitungDurasiUniversalAdmin(true)" class="profileInput" required>
                            </div>
                            <div class="form-field-wrapper">
                                <label class="filterFieldLabel">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_pulang" id="modalJamPulangRutinAdmin"
                                    value="{{ old('jam_pulang', '12:00') }}" onchange="hitungDurasiUniversalAdmin(false)" class="profileInput d-none">
                                <input type="time" name="jam_pulang_rencana" id="modalJamPulangSingleAdmin"
                                    value="{{ old('jam_pulang_rencana', '12:00') }}" onchange="hitungDurasiUniversalAdmin(false)" class="profileInput" required>
                            </div>
                            <div class="form-field-wrapper">
                                <label class="filterFieldLabel">Durasi (Jam)</label>
                                <input type="number" step="0.25" min="0.5" max="12" name="durasi_jam"
                                    id="modalDurasiUniversalAdmin" value="{{ old('durasi_jam', '2.00') }}" class="profileInput" readonly style="background: var(--card);">
                            </div>
                        </div>
                    </div>

                    {{-- Titik Lokasi Presensi --}}
                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Titik Lokasi Presensi</label>
                        <select name="lokasi_presensi_id" class="filterSelect">
                            <option value="">-- Gunakan Lokasi Default / Pusat --</option>
                            @foreach($lokasiPresensis as $lok)
                                <option value="{{ $lok->id }}" {{ (string)old('lokasi_presensi_id') === (string)$lok->id ? 'selected' : '' }}>
                                    {{ $lok->nama_lokasi }} (Radius: {{ $lok->radius_meter }}m)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Catatan / Keterangan --}}
                    <div class="form-field-wrapper mb-4">
                        <label class="filterFieldLabel">Keterangan / Catatan KBM (Opsional)</label>
                        <textarea name="catatan" id="modalCatatanAdmin" rows="2" class="profileInput" style="height: auto;"
                            placeholder="Materi pengayaan, target bab, atau instruksi khusus..."></textarea>
                    </div>

                    <div class="d-flex justify-end gap-2 border-t-base pt-3">
                        <button type="button" onclick="tutupModalJadwalAdmin()" class="btnOutline">Batal</button>
                        <button type="submit" class="profileBtnPrimary">
                            <ion-icon name="checkmark-done-outline"></ion-icon> Simpan Jadwal KBM
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- ── MODAL 2: BANTU RESCHEDULE SESI OLEH ADMIN ── --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div id="modalRescheduleAdmin" class="app-modal-backdrop" style="display: none;"
        onclick="if(event.target===this) tutupModalRescheduleAdmin()">
        <div class="app-modal-card">
            <div class="app-modal-header">
                <div class="app-modal-header-left">
                    <div class="app-modal-badge-icon" style="background: rgba(245, 158, 11, 0.12); color: #d97706;">
                        <ion-icon name="swap-horizontal-outline"></ion-icon>
                    </div>
                    <div>
                        <h3 class="app-modal-title m-0">Bantu Reschedule Sesi KBM</h3>
                        <div class="text-xs text-muted" id="rescheduleInfoMurid">Penyesuaian tanggal atau jam sesi belajar</div>
                    </div>
                </div>
                <button type="button" onclick="tutupModalRescheduleAdmin()" class="app-modal-close">&times;</button>
            </div>

            <form id="formRescheduleAdmin" method="POST" action="">
                @csrf
                <div class="p-3 mb-3 rounded-xl bg-card-alt text-xs text-muted">
                    <strong class="text-base" id="rescheduleSesiDetail">-</strong>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                    <div class="form-field-wrapper">
                        <label class="form-field-label">Tanggal Pelaksanaan Baru <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_baru" id="inTanggalBaru" class="profileInput" required min="{{ date('Y-m-d') }}">
                    </div>

                    <div class="form-field-wrapper">
                        <label class="form-field-label">Titik Lokasi Presensi</label>
                        <select name="lokasi_presensi_id" id="inLokasiPresensi" class="filterSelect">
                            <option value="">-- Tetap Sesuai Semula --</option>
                            @foreach ($lokasiPresensis as $lp)
                                <option value="{{ $lp->id }}">{{ $lp->nama_lokasi }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                    <div class="form-field-wrapper">
                        <label class="form-field-label">Jam Mulai Baru <span class="text-danger">*</span></label>
                        <input type="time" name="jam_masuk_baru" id="inJamMasukBaru" class="profileInput" required>
                    </div>

                    <div class="form-field-wrapper">
                        <label class="form-field-label">Jam Selesai Baru <span class="text-danger">*</span></label>
                        <input type="time" name="jam_pulang_baru" id="inJamPulangBaru" class="profileInput" required>
                    </div>
                </div>

                <div class="form-field-wrapper mb-3">
                    <label class="form-field-label">Alasan Reschedule / Penyesuaian <span class="text-danger">*</span></label>
                    <textarea name="alasan_penggantian" rows="2" class="profileInput" required
                        placeholder="Contoh: Kesepakatan tutor & murid karena kendala cuaca / permintaan orang tua..." style="height: auto;"></textarea>
                    <div class="field-help-text">Alasan akan dicatat sebagai riwayat resmi dan otomatis dikirimkan via notifikasi ke Tutor dan Murid.</div>
                </div>

                <div class="app-modal-footer">
                    <button type="button" onclick="tutupModalRescheduleAdmin()" class="btnOutline w-auto">Batal</button>
                    <button type="submit" class="profileBtnPrimary w-auto">
                        <ion-icon name="checkmark-done-outline"></ion-icon> Simpan Reschedule Sesi
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- ── MODAL 3: TAMBAH AGENDA ACARA & LIBUR PKBM ── --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div id="modalAgendaBaruAdmin" class="navDrawerBackdrop" onclick="if(event.target === this) tutupModalAgendaAdmin()">
        <div class="navDrawerSheet max-w-md mx-auto" onclick="event.stopPropagation()">
            <div class="navDrawerHandle"></div>

            <div class="navDrawerHeader">
                <div class="navDrawerTitleWrap">
                    <div class="navDrawerSub">Kalender Akademik • Agenda PKBM</div>
                    <h3 class="navDrawerTitle">Tambah Agenda &amp; Libur Sekolah</h3>
                </div>
                <button type="button" class="navDrawerCloseBtn" onclick="tutupModalAgendaAdmin()" aria-label="Tutup">
                    <ion-icon name="close"></ion-icon>
                </button>
            </div>

            <div class="navDrawerBody">
                <form method="POST" action="{{ route('admin.jadwal.storeAgenda') }}" id="formTambahAgendaAdmin">
                    @csrf

                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Judul Agenda / Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="profileInput" placeholder="Contoh: Ujian Tengah Semester, Rapat Wali Murid, Libur Nasional..." required>
                    </div>

                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" value="{{ $selectedDate->format('Y-m-d') }}" class="profileInput" required>
                    </div>

                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Titik Lokasi Resmi PKBM (Opsional)</label>
                        <select name="lokasi_presensi_id" class="filterSelect">
                            <option value="">-- Tanpa Titik Resmi / Kampus Pusat --</option>
                            @foreach($lokasiPresensis as $lp)
                                <option value="{{ $lp->id }}">
                                    {{ $lp->nama_lokasi }} (Radius: {{ $lp->radius_meter }}m)
                                </option>
                            @endforeach
                        </select>
                        <span class="field-help-text">Pilih jika agenda memiliki titik koordinat radius presensi.</span>
                    </div>

                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Keterangan Ruangan / Lokasi Khusus (Opsional)</label>
                        <input type="text" name="lokasi" class="profileInput" placeholder="Contoh: Ruang Aula Lt. 2, Lapangan Utama, atau Daring Zoom">
                    </div>

                    <div class="form-field-wrapper mb-4">
                        <label class="filterFieldLabel">Deskripsi / Catatan Tambahan (Opsional)</label>
                        <textarea name="deskripsi" rows="3" class="profileInput" style="height: auto;"
                            placeholder="Tuliskan keterangan detail kegiatan atau pengumuman libur..."></textarea>
                    </div>

                    <div class="d-flex justify-end gap-2 border-t-base pt-3">
                        <button type="button" onclick="tutupModalAgendaAdmin()" class="btnOutline">Batal</button>
                        <button type="submit" class="profileBtnPrimary">
                            <ion-icon name="save-outline"></ion-icon> Simpan Agenda PKBM
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- ── MODAL 4: EDIT AGENDA ACARA & LIBUR PKBM ── --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div id="modalEditAgendaAdmin" class="navDrawerBackdrop" onclick="if(event.target === this) tutupModalEditAgendaAdmin()">
        <div class="navDrawerSheet max-w-md mx-auto" onclick="event.stopPropagation()">
            <div class="navDrawerHandle"></div>

            <div class="navDrawerHeader">
                <div class="navDrawerTitleWrap">
                    <div class="navDrawerSub">Kalender Akademik • Edit Agenda</div>
                    <h3 class="navDrawerTitle">Edit Agenda &amp; Libur Sekolah</h3>
                </div>
                <button type="button" class="navDrawerCloseBtn" onclick="tutupModalEditAgendaAdmin()" aria-label="Tutup">
                    <ion-icon name="close"></ion-icon>
                </button>
            </div>

            <div class="navDrawerBody">
                <form id="formEditAgendaAdmin" method="POST" action="">
                    @csrf
                    @method('PUT')

                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Judul Agenda / Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" name="judul" id="editAgendaJudul" class="profileInput" required>
                    </div>

                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" id="editAgendaTanggal" class="profileInput" required>
                    </div>

                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Titik Lokasi Resmi PKBM (Opsional)</label>
                        <select name="lokasi_presensi_id" id="editAgendaLokasiPresensi" class="filterSelect">
                            <option value="">-- Tanpa Titik Resmi / Kampus Pusat --</option>
                            @foreach($lokasiPresensis as $lp)
                                <option value="{{ $lp->id }}">
                                    {{ $lp->nama_lokasi }} (Radius: {{ $lp->radius_meter }}m)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Keterangan Ruangan / Lokasi Khusus (Opsional)</label>
                        <input type="text" name="lokasi" id="editAgendaLokasi" class="profileInput">
                    </div>

                    <div class="form-field-wrapper mb-4">
                        <label class="filterFieldLabel">Deskripsi / Catatan Tambahan (Opsional)</label>
                        <textarea name="deskripsi" id="editAgendaDeskripsi" rows="3" class="profileInput" style="height: auto;"></textarea>
                    </div>

                    <div class="d-flex justify-end gap-2 border-t-base pt-3">
                        <button type="button" onclick="tutupModalEditAgendaAdmin()" class="btnOutline">Batal</button>
                        <button type="submit" class="profileBtnPrimary">
                            <ion-icon name="save-outline"></ion-icon> Perbarui Agenda PKBM
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- ── MODAL 5: EDIT SESI KBM HARIAN ── --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div id="modalEditSesiAdmin" class="navDrawerBackdrop" onclick="if(event.target === this) tutupModalEditSesiAdmin()">
        <div class="navDrawerSheet max-w-md mx-auto" onclick="event.stopPropagation()">
            <div class="navDrawerHandle"></div>

            <div class="navDrawerHeader">
                <div class="navDrawerTitleWrap">
                    <div class="navDrawerSub">Perencanaan KBM • Edit Sesi</div>
                    <h3 class="navDrawerTitle">Edit Jadwal Sesi KBM</h3>
                </div>
                <button type="button" class="navDrawerCloseBtn" onclick="tutupModalEditSesiAdmin()" aria-label="Tutup">
                    <ion-icon name="close"></ion-icon>
                </button>
            </div>

            <div class="navDrawerBody">
                <form id="formEditSesiAdmin" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="murid_id" id="editSesiMuridId">
                    <input type="hidden" name="jenis_sesi" value="reguler">

                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Tutor Pengampu <span class="text-danger">*</span></label>
                        <select name="tutor_id" id="editSesiTutorId" class="filterSelect" required>
                            @foreach($tutors as $t)
                                <option value="{{ $t->id }}">{{ $t->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_rencana" id="editSesiTanggal" class="profileInput" required>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div class="form-field-wrapper">
                            <label class="filterFieldLabel">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_masuk_rencana" id="editSesiJamMasuk" class="profileInput" required>
                        </div>
                        <div class="form-field-wrapper">
                            <label class="filterFieldLabel">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_pulang_rencana" id="editSesiJamPulang" class="profileInput" required>
                        </div>
                    </div>

                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Kategori Tutorial SK</label>
                        <select name="kategori_tutorial_id" id="editSesiKategoriId" class="filterSelect">
                            <option value="">-- Pilih Kategori SK --</option>
                            @foreach($kategoriTutorials as $kat)
                                <option value="{{ $kat->id }}">{{ $kat->nama_kategori }} ({{ $kat->durasi_jam }} Jam)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Titik Lokasi Presensi</label>
                        <select name="lokasi_presensi_id" id="editSesiLokasiId" class="filterSelect">
                            <option value="">-- Lokasi Default PKBM / Sesuai Murid --</option>
                            @foreach($lokasiPresensis as $lp)
                                <option value="{{ $lp->id }}">{{ $lp->nama_lokasi }} (Radius: {{ $lp->radius_meter }}m)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field-wrapper mb-4">
                        <label class="filterFieldLabel">Catatan Tambahan (Opsional)</label>
                        <textarea name="catatan" id="editSesiCatatan" rows="2" class="profileInput" style="height: auto;"></textarea>
                    </div>

                    <div class="d-flex justify-end gap-2 border-t-base pt-3">
                        <button type="button" onclick="tutupModalEditSesiAdmin()" class="btnOutline">Batal</button>
                        <button type="submit" class="profileBtnPrimary">
                            <ion-icon name="save-outline"></ion-icon> Simpan Perubahan Sesi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- ── MODAL 6: EDIT POLA RUTIN KBM ── --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div id="modalEditRutinAdmin" class="navDrawerBackdrop" onclick="if(event.target === this) tutupModalEditRutinAdmin()">
        <div class="navDrawerSheet max-w-md mx-auto" onclick="event.stopPropagation()">
            <div class="navDrawerHandle"></div>

            <div class="navDrawerHeader">
                <div class="navDrawerTitleWrap">
                    <div class="navDrawerSub">Pola Berulang • Edit Master</div>
                    <h3 class="navDrawerTitle">Edit Pola Rutin KBM</h3>
                </div>
                <button type="button" class="navDrawerCloseBtn" onclick="tutupModalEditRutinAdmin()" aria-label="Tutup">
                    <ion-icon name="close"></ion-icon>
                </button>
            </div>

            <div class="navDrawerBody">
                <form id="formEditRutinAdmin" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="murid_id" id="editRutinMuridId">

                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Tutor Pengampu <span class="text-danger">*</span></label>
                        <select name="tutor_id" id="editRutinTutorId" class="filterSelect" required>
                            @foreach($tutors as $t)
                                <option value="{{ $t->id }}">{{ $t->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Hari Belajar Mingguan <span class="text-danger">*</span></label>
                        <select name="hari" id="editRutinHari" class="filterSelect" required>
                            <option value="senin">Setiap Hari Senin</option>
                            <option value="selasa">Setiap Hari Selasa</option>
                            <option value="rabu">Setiap Hari Rabu</option>
                            <option value="kamis">Setiap Hari Kamis</option>
                            <option value="jumat">Setiap Hari Jumat</option>
                            <option value="sabtu">Setiap Hari Sabtu</option>
                            <option value="minggu">Setiap Hari Minggu</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div class="form-field-wrapper">
                            <label class="filterFieldLabel">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_masuk" id="editRutinJamMasuk" class="profileInput" required>
                        </div>
                        <div class="form-field-wrapper">
                            <label class="filterFieldLabel">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_pulang" id="editRutinJamPulang" class="profileInput" required>
                        </div>
                    </div>

                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Kategori Tutorial SK</label>
                        <select name="kategori_tutorial_id" id="editRutinKategoriId" class="filterSelect">
                            <option value="">-- Pilih Kategori SK --</option>
                            @foreach($kategoriTutorials as $kat)
                                <option value="{{ $kat->id }}">{{ $kat->nama_kategori }} ({{ $kat->durasi_jam }} Jam)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field-wrapper mb-3">
                        <label class="filterFieldLabel">Titik Lokasi Presensi</label>
                        <select name="lokasi_presensi_id" id="editRutinLokasiId" class="filterSelect">
                            <option value="">-- Lokasi Default PKBM / Sesuai Murid --</option>
                            @foreach($lokasiPresensis as $lp)
                                <option value="{{ $lp->id }}">{{ $lp->nama_lokasi }} (Radius: {{ $lp->radius_meter }}m)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field-wrapper mb-4">
                        <label class="filterFieldLabel">Catatan / Keterangan (Opsional)</label>
                        <textarea name="keterangan" id="editRutinKeterangan" rows="2" class="profileInput" style="height: auto;"></textarea>
                    </div>

                    <div class="d-flex justify-end gap-2 border-t-base pt-3">
                        <button type="button" onclick="tutupModalEditRutinAdmin()" class="btnOutline">Batal</button>
                        <button type="submit" class="profileBtnPrimary">
                            <ion-icon name="save-outline"></ion-icon> Simpan Perubahan Pola
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- ── MODAL 7: IMPOR AGENDA DARI EXCEL/CSV ── --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div id="modalImportAgendaAdmin" class="app-modal-backdrop" onclick="if(event.target===this) tutupModalImportAgendaAdmin()">
        <div class="app-modal-card">
            <div class="app-modal-header">
                <div class="app-modal-header-left">
                    <div class="app-modal-badge-icon">
                        <ion-icon name="calendar-outline"></ion-icon>
                    </div>
                    <div>
                        <h3 class="app-modal-title m-0">Impor Agenda &amp; Kalender Massal</h3>
                        <div class="text-xs text-muted">Format Spreadsheet Excel / CSV</div>
                    </div>
                </div>
                <button type="button" onclick="tutupModalImportAgendaAdmin()"
                    class="app-modal-close" title="Tutup">&times;</button>
            </div>
            <p class="app-modal-desc">
                Unggah berkas spreadsheet Excel/CSV untuk menambahkan agenda kegiatan belajar dan kalender akademik
                secara massal ke sistem.
            </p>
            <div class="import-template-banner">
                <div class="import-template-info">
                    <div class="import-template-icon">
                        <ion-icon name="cloud-download-outline"></ion-icon>
                    </div>
                    <div class="import-template-texts">
                        <div class="import-template-title">Belum memiliki template?</div>
                        <div class="import-template-desc">Gunakan format resmi agar data kegiatan terimpor presisi.</div>
                    </div>
                </div>
                <a href="{{ route('admin.jadwal.downloadTemplate') }}" class="btn-download-template"
                    title="Download Template Jadwal">
                    <ion-icon name="download-outline"></ion-icon> Unduh Template (.xlsx)
                </a>
            </div>
            <form method="POST" action="{{ route('admin.jadwal.importExcel') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-field-wrapper mb-3">
                    <label class="form-field-label">Pilih Berkas Spreadsheet:</label>
                    <div class="fileUploadBox">
                        <input type="file" name="file_excel" id="jadwalFileInputAdmin" accept=".xlsx,.xls,.csv" required
                            onchange="handleExcelFileSelectedAdmin(this, 'jadwalFileFeedbackAdmin')">
                        <div class="fileUploadIcon">
                            <ion-icon name="cloud-upload-outline"></ion-icon>
                        </div>
                        <div class="fileUploadText">Pilih atau seret berkas ke sini</div>
                        <div class="fileUploadSubtext">
                            <span class="fileUploadInfoPill"><ion-icon name="document-text-outline"></ion-icon> .XLSX, .XLS, .CSV</span>
                            <span class="fileUploadInfoPill"><ion-icon name="hardware-chip-outline"></ion-icon> Maks. 5 MB</span>
                        </div>
                    </div>
                    <div id="jadwalFileFeedbackAdmin" class="fileUploadFeedback"></div>
                </div>
                <div class="app-modal-footer">
                    <button type="button" onclick="tutupModalImportAgendaAdmin()"
                        class="btnOutline w-auto">Batal</button>
                    <button type="submit" class="profileBtnPrimary w-auto">
                        <ion-icon name="cloud-upload-outline"></ion-icon> Unggah &amp; Impor Agenda
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- ── SCRIPT LOGIC TERPADU ADMIN ── --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <script>
        let isRecurringModeAdmin = false;

        function setScheduleModeAdmin(isRecurring) {
            isRecurringModeAdmin = isRecurring;
            const secRutin = document.getElementById('sectionRutinAdmin');
            const secSingle = document.getElementById('sectionSingleAdmin');
            const radioRutin = document.getElementById('radioModeRutinAdmin');
            const radioSingle = document.getElementById('radioModeSingleAdmin');
            const labelRutin = document.getElementById('labelModeRutinAdmin');
            const labelSingle = document.getElementById('labelModeSingleAdmin');
            const title = document.getElementById('modalJadwalTitleAdmin');

            const jamMasukRutin = document.getElementById('modalJamMasukRutinAdmin');
            const jamMasukSingle = document.getElementById('modalJamMasukSingleAdmin');
            const jamPulangRutin = document.getElementById('modalJamPulangRutinAdmin');
            const jamPulangSingle = document.getElementById('modalJamPulangSingleAdmin');

            if (isRecurring) {
                secRutin.classList.remove('d-none');
                secSingle.classList.add('d-none');
                radioRutin.checked = true;
                title.textContent = 'Tambah Pola Rutin KBM (Mingguan)';

                labelRutin.style.background = '#2563eb';
                labelRutin.style.color = '#ffffff';
                labelSingle.style.background = 'transparent';
                labelSingle.style.color = 'var(--text-muted)';

                jamMasukRutin.classList.remove('d-none');
                jamMasukRutin.required = true;
                jamMasukSingle.classList.add('d-none');
                jamMasukSingle.required = false;

                jamPulangRutin.classList.remove('d-none');
                jamPulangRutin.required = true;
                jamPulangSingle.classList.add('d-none');
                jamPulangSingle.required = false;
            } else {
                secRutin.classList.add('d-none');
                secSingle.classList.remove('d-none');
                radioSingle.checked = true;
                title.textContent = 'Buat Jadwal Belajar Sekali';

                labelSingle.style.background = '#2563eb';
                labelSingle.style.color = '#ffffff';
                labelRutin.style.background = 'transparent';
                labelRutin.style.color = 'var(--text-muted)';

                jamMasukSingle.classList.remove('d-none');
                jamMasukSingle.required = true;
                jamMasukRutin.classList.add('d-none');
                jamMasukRutin.required = false;

                jamPulangSingle.classList.remove('d-none');
                jamPulangSingle.required = true;
                jamPulangRutin.classList.add('d-none');
                jamPulangRutin.required = false;
            }
            hitungDurasiUniversalAdmin(false);
        }

        function bukaModalJadwalAdmin(isRecurring = false) {
            const modal = document.getElementById('modalJadwalBaruAdmin');
            if (modal) {
                modal.classList.add('active');
                setScheduleModeAdmin(isRecurring);
                updateSelectedMuridCounterAdmin();
            }
        }

        function tutupModalJadwalAdmin() {
            const modal = document.getElementById('modalJadwalBaruAdmin');
            if (modal) {
                modal.classList.remove('active');
            }
        }

        function bukaModalAgendaAdmin() {
            const modal = document.getElementById('modalAgendaBaruAdmin');
            if (modal) {
                modal.classList.add('active');
            }
        }

        function tutupModalAgendaAdmin() {
            const modal = document.getElementById('modalAgendaBaruAdmin');
            if (modal) {
                modal.classList.remove('active');
            }
        }

        function toggleTanggalAsliAdmin() {
            const jenis = document.getElementById('modalJenisSesiAdmin')?.value;
            const box = document.getElementById('boxTanggalAsliAdmin');
            if (box) {
                if (jenis === 'pengganti') {
                    box.classList.remove('d-none');
                } else {
                    box.classList.add('d-none');
                }
            }
        }

        function kategoriUniversalChangeAdmin() {
            const sel = document.getElementById('modalKategoriTutorialAdmin');
            const durasiIn = document.getElementById('modalDurasiUniversalAdmin');
            if (!sel || !durasiIn) return;

            const opt = sel.options[sel.selectedIndex];
            const durasi = opt ? opt.getAttribute('data-durasi') : null;

            if (durasi && parseFloat(durasi) > 0) {
                durasiIn.value = parseFloat(durasi).toFixed(2);
                hitungJamPulangDariDurasiAdmin(parseFloat(durasi));
            }
        }

        function hitungJamPulangDariDurasiAdmin(durasiJam) {
            const inMasuk = isRecurringModeAdmin
                ? document.getElementById('modalJamMasukRutinAdmin')
                : document.getElementById('modalJamMasukSingleAdmin');
            const inPulang = isRecurringModeAdmin
                ? document.getElementById('modalJamPulangRutinAdmin')
                : document.getElementById('modalJamPulangSingleAdmin');

            if (!inMasuk || !inPulang || !inMasuk.value) return;

            const parts = inMasuk.value.split(':');
            let h = parseInt(parts[0], 10);
            let m = parseInt(parts[1], 10);

            const totalMenitTambah = Math.round(durasiJam * 60);
            const totalMenitSelesai = (h * 60 + m + totalMenitTambah) % (24 * 60);

            const endH = String(Math.floor(totalMenitSelesai / 60)).padStart(2, '0');
            const endM = String(totalMenitSelesai % 60).padStart(2, '0');

            inPulang.value = `${endH}:${endM}`;
            if (isRecurringModeAdmin) {
                document.getElementById('modalJamPulangSingleAdmin').value = inPulang.value;
            } else {
                document.getElementById('modalJamPulangRutinAdmin').value = inPulang.value;
            }
        }

        function hitungDurasiUniversalAdmin(adjustPulang = false) {
            const inMasuk = isRecurringModeAdmin
                ? document.getElementById('modalJamMasukRutinAdmin')
                : document.getElementById('modalJamMasukSingleAdmin');
            const inPulang = isRecurringModeAdmin
                ? document.getElementById('modalJamPulangRutinAdmin')
                : document.getElementById('modalJamPulangSingleAdmin');
            const inDurasi = document.getElementById('modalDurasiUniversalAdmin');

            if (!inMasuk || !inPulang || !inDurasi) return;

            if (isRecurringModeAdmin) {
                document.getElementById('modalJamMasukSingleAdmin').value = inMasuk.value;
                document.getElementById('modalJamPulangSingleAdmin').value = inPulang.value;
            } else {
                document.getElementById('modalJamMasukRutinAdmin').value = inMasuk.value;
                document.getElementById('modalJamPulangRutinAdmin').value = inPulang.value;
            }

            if (!inMasuk.value || !inPulang.value) return;

            const t1 = inMasuk.value.split(':');
            const t2 = inPulang.value.split(':');
            const m1 = parseInt(t1[0], 10) * 60 + parseInt(t1[1], 10);
            const m2 = parseInt(t2[0], 10) * 60 + parseInt(t2[1], 10);

            if (m2 > m1) {
                const diffMenit = m2 - m1;
                inDurasi.value = (diffMenit / 60).toFixed(2);
            } else if (adjustPulang) {
                const durasiCurr = parseFloat(inDurasi.value) || 2;
                hitungJamPulangDariDurasiAdmin(durasiCurr);
            }
        }

        // ── Logic Multi-Murid & Rombel Picker Admin ──
        function updateSelectedMuridCounterAdmin() {
            const checkboxes = document.querySelectorAll('.murid-checkbox-admin:checked');
            const counterText = document.getElementById('selectedCountTextAdmin');
            const counterBadge = document.getElementById('selectedMuridCounterAdmin');
            const errorBox = document.getElementById('muridPickerErrorAdmin');

            const count = checkboxes.length;
            if (counterText) {
                counterText.textContent = `${count} murid terpilih`;
            }
            if (counterBadge) {
                if (count > 0) {
                    counterBadge.classList.add('has-selected');
                } else {
                    counterBadge.classList.remove('has-selected');
                }
            }
            if (count > 0 && errorBox) {
                errorBox.classList.add('d-none');
            }
        }

        function onMuridCheckboxChangeAdmin(cb) {
            const labelItem = cb.closest('.murid-picker-item');
            if (labelItem) {
                if (cb.checked) {
                    labelItem.classList.add('is-checked');
                } else {
                    labelItem.classList.remove('is-checked');
                }
            }
            updateSelectedMuridCounterAdmin();
        }

        function filterMuridListAdmin() {
            const filterRombel = document.getElementById('pickerFilterRombelAdmin')?.value || 'all';
            const searchVal = (document.getElementById('pickerSearchMuridAdmin')?.value || '').toLowerCase().trim();
            const tutorSelectVal = document.getElementById('modalTutorIdAdmin')?.value || '';

            const items = document.querySelectorAll('#muridPickerContainerAdmin .murid-picker-item');

            items.forEach(item => {
                const itemKelasId = item.getAttribute('data-kelas-id');
                const itemJenjang = item.getAttribute('data-jenjang');
                const itemTutorId = item.getAttribute('data-tutor-id');
                const itemNama = item.getAttribute('data-nama') || '';
                const itemAbsen = item.getAttribute('data-absen') || '';

                let matchRombel = true;
                if (filterRombel === 'bimbingan_tutor') {
                    matchRombel = (tutorSelectVal !== '' && String(itemTutorId) === String(tutorSelectVal));
                } else if (filterRombel.startsWith('kelas_')) {
                    const targetKelasId = filterRombel.replace('kelas_', '');
                    matchRombel = (String(itemKelasId) === String(targetKelasId));
                } else if (filterRombel !== 'all') {
                    matchRombel = (itemJenjang === filterRombel);
                }

                let matchSearch = true;
                if (searchVal.length > 0) {
                    matchSearch = itemNama.includes(searchVal) || itemAbsen.includes(searchVal);
                }

                if (matchRombel && matchSearch) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function selectAllVisibleMuridAdmin(select = true) {
            const visibleItems = document.querySelectorAll('#muridPickerContainerAdmin .murid-picker-item');
            visibleItems.forEach(item => {
                if (item.style.display !== 'none') {
                    const cb = item.querySelector('.murid-checkbox-admin');
                    if (cb) {
                        cb.checked = select;
                        if (select) {
                            item.classList.add('is-checked');
                        } else {
                            item.classList.remove('is-checked');
                        }
                    }
                }
            });
            updateSelectedMuridCounterAdmin();
        }

        function validateMuridSelectionAdmin(event) {
            const checkedCount = document.querySelectorAll('.murid-checkbox-admin:checked').length;
            const errorBox = document.getElementById('muridPickerErrorAdmin');

            if (checkedCount === 0) {
                if (errorBox) {
                    errorBox.classList.remove('d-none');
                }
                const container = document.getElementById('muridPickerContainerAdmin');
                if (container) {
                    container.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                event.preventDefault();
                return false;
            }
            return true;
        }

        // ── Modal Reschedule Sesi Admin ──
        function bukaModalRescheduleAdmin(id, namaMurid, namaTutor, tanggalRencana, jamMasuk, jamPulang, lokasiId = '') {
            const modal = document.getElementById('modalRescheduleAdmin');
            const form = document.getElementById('formRescheduleAdmin');
            const infoDetail = document.getElementById('rescheduleSesiDetail');
            const infoMurid = document.getElementById('rescheduleInfoMurid');
            const inTanggal = document.getElementById('inTanggalBaru');
            const inMasuk = document.getElementById('inJamMasukBaru');
            const inPulang = document.getElementById('inJamPulangBaru');
            const inLokasi = document.getElementById('inLokasiPresensi');

            if (modal && form) {
                form.action = '/admin/jadwal/' + id + '/reschedule';
                infoDetail.textContent = 'Murid: ' + namaMurid + ' • Tutor: ' + namaTutor + ' (' + tanggalRencana + ' pukul ' + jamMasuk + ' - ' + jamPulang + ' WIB)';
                infoMurid.textContent = 'Menyesuaikan jadwal KBM murid ' + namaMurid;
                inTanggal.value = tanggalRencana;
                inMasuk.value = jamMasuk;
                inPulang.value = jamPulang;
                if (inLokasi) {
                    inLokasi.value = lokasiId || '';
                }

                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function tutupModalRescheduleAdmin() {
            const modal = document.getElementById('modalRescheduleAdmin');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        }

        // ── Modal Edit Agenda Admin ──
        function bukaModalEditAgendaAdmin(id, judul, tanggal, lokasiPresensiId = '', lokasi = '', deskripsi = '') {
            const modal = document.getElementById('modalEditAgendaAdmin');
            const form = document.getElementById('formEditAgendaAdmin');
            if (modal && form) {
                form.action = '/admin/jadwal/agenda/' + id;
                document.getElementById('editAgendaJudul').value = judul;
                document.getElementById('editAgendaTanggal').value = tanggal;
                document.getElementById('editAgendaLokasiPresensi').value = lokasiPresensiId || '';
                document.getElementById('editAgendaLokasi').value = lokasi || '';
                document.getElementById('editAgendaDeskripsi').value = deskripsi || '';
                modal.classList.add('active');
            }
        }

        function tutupModalEditAgendaAdmin() {
            const modal = document.getElementById('modalEditAgendaAdmin');
            if (modal) {
                modal.classList.remove('active');
            }
        }

        // ── Modal Edit Sesi KBM Admin ──
        function bukaModalEditSesiAdmin(id, tutorId, muridId, kategoriId = '', tanggal = '', jamMasuk = '', jamPulang = '', lokasiId = '', catatan = '') {
            const modal = document.getElementById('modalEditSesiAdmin');
            const form = document.getElementById('formEditSesiAdmin');
            if (modal && form) {
                form.action = '/admin/jadwal/' + id;
                document.getElementById('editSesiMuridId').value = muridId;
                document.getElementById('editSesiTutorId').value = tutorId;
                document.getElementById('editSesiTanggal').value = tanggal;
                document.getElementById('editSesiJamMasuk').value = jamMasuk;
                document.getElementById('editSesiJamPulang').value = jamPulang;
                document.getElementById('editSesiKategoriId').value = kategoriId || '';
                document.getElementById('editSesiLokasiId').value = lokasiId || '';
                document.getElementById('editSesiCatatan').value = catatan || '';
                modal.classList.add('active');
            }
        }

        function tutupModalEditSesiAdmin() {
            const modal = document.getElementById('modalEditSesiAdmin');
            if (modal) {
                modal.classList.remove('active');
            }
        }

        // ── Modal Edit Pola Rutin Admin ──
        function bukaModalEditRutinAdmin(id, muridId, tutorId, hari = 'senin', jamMasuk = '', jamPulang = '', kategoriId = '', lokasiId = '', keterangan = '') {
            const modal = document.getElementById('modalEditRutinAdmin');
            const form = document.getElementById('formEditRutinAdmin');
            if (modal && form) {
                form.action = '/admin/jadwal/rutin/' + id;
                document.getElementById('editRutinMuridId').value = muridId;
                document.getElementById('editRutinTutorId').value = tutorId;
                document.getElementById('editRutinHari').value = hari.toLowerCase();
                document.getElementById('editRutinJamMasuk').value = jamMasuk;
                document.getElementById('editRutinJamPulang').value = jamPulang;
                document.getElementById('editRutinKategoriId').value = kategoriId || '';
                document.getElementById('editRutinLokasiId').value = lokasiId || '';
                document.getElementById('editRutinKeterangan').value = keterangan || '';
                modal.classList.add('active');
            }
        }

        function tutupModalEditRutinAdmin() {
            const modal = document.getElementById('modalEditRutinAdmin');
            if (modal) {
                modal.classList.remove('active');
            }
        }

        // ── Modal Impor Agenda Massal ──
        function bukaModalImportAgendaAdmin() {
            const m = document.getElementById('modalImportAgendaAdmin');
            if (m) m.style.display = 'flex';
        }

        function tutupModalImportAgendaAdmin() {
            const m = document.getElementById('modalImportAgendaAdmin');
            if (m) m.style.display = 'none';
        }

        function handleExcelFileSelectedAdmin(input, feedbackId) {
            const fb = document.getElementById(feedbackId);
            if (!fb) return;
            if (input.files && input.files[0]) {
                const f = input.files[0];
                fb.innerHTML = '<span class="text-success font-semibold" style="font-size: 12px; margin-top: 6px; display: inline-block;">✓ Berkas dipilih: ' + f.name + ' (' + (f.size / 1024).toFixed(1) + ' KB)</span>';
            } else {
                fb.innerHTML = '';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateSelectedMuridCounterAdmin();
            setScheduleModeAdmin({{ old('is_recurring') === '1' ? 'true' : 'false' }});

            const urlParams = new URLSearchParams(window.location.search);
            const tambah = urlParams.get('tambah');
            const tab = urlParams.get('tab') || 'sesi';
            const editAgendaId = urlParams.get('edit_agenda');
            const editRutinId = urlParams.get('edit_rutin');

            if (tambah === '1') {
                if (tab === 'agenda') {
                    bukaModalAgendaAdmin();
                } else if (tab === 'rutin') {
                    bukaModalJadwalAdmin(true);
                } else {
                    bukaModalJadwalAdmin(false);
                }
            } else if (editAgendaId) {
                const btn = document.querySelector(`[data-agenda-id="${editAgendaId}"]`);
                if (btn) btn.click();
            } else if (editRutinId) {
                const btn = document.querySelector(`[data-rutin-id="${editRutinId}"]`);
                if (btn) btn.click();
            }
        });
    </script>

@endsection
