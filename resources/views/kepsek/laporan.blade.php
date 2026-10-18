@extends('layouts.kepsek')

@section('title', 'Laporan Presensi & KBM - Kepala Sekolah')

@section('content')

    @php
        $namaBulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
    @endphp

    <div class="laporanPageWrapper">
        {{-- ── Header ── --}}
        <div class="laporanHeader">
            <div class="laporanHeaderCard">
                <div class="laporanHeaderInfo">
                    <div class="laporanHeaderLabel">LAPORAN OPERASIONAL AKADEMIK</div>
                    <h1 class="laporanHeaderTitle">Laporan Presensi &amp; KBM Tutor</h1>
                    <div class="laporanHeaderSub">Periode: {{ $namaBulan[$bulan] ?? 'Semua' }} {{ $tahun }}</div>
                    <p class="laporanHeaderDesc">Monitoring log kehadiran, moda pembelajaran, dan jam mengajar tutor.</p>
                </div>
                <div class="laporanHeaderActions">
                    <a href="{{ route('kepsek.payroll.index', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                        class="btnPayrollShortcut">
                        <ion-icon name="wallet-outline"></ion-icon>
                        <span>Buka Rekapitulasi Payroll ({{ $namaBulan[$bulan] ?? '' }})</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Quick Filter Chips Bar --}}
        @php
            $currentMonth = \Carbon\Carbon::now('Asia/Jakarta')->month;
            $currentYear = \Carbon\Carbon::now('Asia/Jakarta')->year;
            $prev1 = \Carbon\Carbon::now('Asia/Jakarta')->subMonth();
            $prev2 = \Carbon\Carbon::now('Asia/Jakarta')->subMonths(2);
        @endphp
        <div class="filterChipsBar">
            <a href="{{ route('kepsek.laporan', array_merge(request()->except(['bulan', 'tahun']), ['bulan' => $currentMonth, 'tahun' => $currentYear])) }}"
               class="filterChip {{ ($bulan == $currentMonth && $tahun == $currentYear) ? 'active' : '' }}">
                <ion-icon name="calendar-outline"></ion-icon>
                <span>Bulan Ini ({{ $namaBulan[$currentMonth] ?? '' }})</span>
            </a>
            <a href="{{ route('kepsek.laporan', array_merge(request()->except(['bulan', 'tahun']), ['bulan' => $prev1->month, 'tahun' => $prev1->year])) }}"
               class="filterChip {{ ($bulan == $prev1->month && $tahun == $prev1->year) ? 'active' : '' }}">
                <ion-icon name="time-outline"></ion-icon>
                <span>Bulan Lalu ({{ $namaBulan[$prev1->month] ?? '' }})</span>
            </a>
            <a href="{{ route('kepsek.laporan', array_merge(request()->except(['bulan', 'tahun']), ['bulan' => $prev2->month, 'tahun' => $prev2->year])) }}"
               class="filterChip {{ ($bulan == $prev2->month && $tahun == $prev2->year) ? 'active' : '' }}">
                <ion-icon name="calendar-number-outline"></ion-icon>
                <span>{{ $namaBulan[$prev2->month] ?? '' }} {{ $prev2->year }}</span>
            </a>
        </div>

        {{-- ── Filter Card ── --}}
        <div class="laporanFilterCard">
            <div class="laporanFilterHeaderMobile">
                <div class="filterMobileSummary">
                    <span class="filterMobileIcon"><ion-icon name="filter-outline"></ion-icon></span>
                    <div class="filterMobileTexts">
                        <span class="filterMobileTitle">Parameter &amp; Filter Laporan</span>
                        <span class="filterMobileSub">{{ $namaBulan[$bulan] ?? 'Semua' }} {{ $tahun }}</span>
                    </div>
                </div>
                <button type="button" class="btnToggleFilterMobile" id="btnToggleKepsekFilter" aria-expanded="false">
                    <span class="filterToggleText" id="kepsekFilterToggleText">Buka Filter</span>
                    <ion-icon name="chevron-down-outline" class="filterToggleIcon" id="kepsekFilterToggleIcon"></ion-icon>
                </button>
            </div>
            <form method="GET" action="{{ route('kepsek.laporan') }}" class="laporanFilterFormBody" id="kepsekFilterForm">
                <div class="laporanFilterGrid">
                    <div class="filterField">
                        <label class="filterFieldLabel">Bulan</label>
                        <select name="bulan" class="filterSelect" aria-label="Pilih Bulan">
                            @foreach($namaBulan as $num => $label)
                                <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filterField">
                        <label class="filterFieldLabel">Tahun</label>
                        <select name="tahun" class="filterSelect" aria-label="Pilih Tahun">
                            @foreach($tahunOptions as $y)
                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filterField">
                        <label class="filterFieldLabel">Tutor</label>
                        <select name="tutor_id" class="filterSelect" aria-label="Pilih Tutor">
                            <option value="">Semua Tutor</option>
                            @foreach($allTutors as $t_item)
                                <option value="{{ $t_item->id }}" {{ $tutorId == $t_item->id ? 'selected' : '' }}>
                                    {{ $t_item->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filterField">
                        <label class="filterFieldLabel">Murid</label>
                        <select name="siswa_id" class="filterSelect" aria-label="Pilih Murid">
                            <option value="">Semua Murid</option>
                            @foreach($allSiswas as $s_item)
                                <option value="{{ $s_item->id }}" {{ $siswaId == $s_item->id ? 'selected' : '' }}>
                                    {{ $s_item->nama_murid ?? $s_item->nama_siswa }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-actions-full">
                        <button type="submit" class="btn-filter-primary">
                            <ion-icon name="search-outline"></ion-icon> Filter Data
                        </button>
                        @if($rekapTutor->count() > 0)
                            <a href="{{ route('kepsek.laporan.pdf', ['bulan' => $bulan, 'tahun' => $tahun, 'tutor_id' => $tutorId, 'siswa_id' => $siswaId]) }}"
                                class="btn-filter-danger" target="_blank">
                                <ion-icon name="download-outline"></ion-icon> Ekspor PDF
                            </a>
                        @endif
                        <a href="{{ route('kepsek.laporan') }}" class="btn-filter-reset">
                            Reset
                        </a>

                        @php
                            $hasActiveKepsekFilters = request()->filled('tutor_id') || request()->filled('siswa_id') || ($bulan != $currentMonth || $tahun != $currentYear);
                        @endphp
                        @if($hasActiveKepsekFilters)
                            <div class="activeFilterWrap">
                                <span class="activeFilterLead"><ion-icon name="funnel-outline"></ion-icon> Filter Aktif:</span>
                                <span class="activeFilterTag">Periode: {{ $namaBulan[$bulan] ?? '' }} {{ $tahun }}</span>
                                @if(request()->filled('tutor_id'))
                                    @php $selTut = $allTutors->firstWhere('id', request('tutor_id')); @endphp
                                    @if($selTut)
                                        <span class="activeFilterTag">Tutor: {{ $selTut->nama_lengkap }}</span>
                                    @endif
                                @endif
                                @if(request()->filled('siswa_id'))
                                    @php $selSis = $allSiswas->firstWhere('id', request('siswa_id')); @endphp
                                    @if($selSis)
                                        <span class="activeFilterTag">Murid: {{ $selSis->nama_murid ?? $selSis->nama_siswa }}</span>
                                    @endif
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- ── Summary Stats ── --}}
        <div class="statRow">
            <div class="statCard">
                <div class="statNum blue">{{ $totalHadir }}</div>
                <div class="statLabel">Total Sesi Hadir</div>
            </div>
            <div class="statCard">
                <div class="statNum purple">{{ $totalSesi }}</div>
                <div class="statLabel">Total Sesi Terjadwal</div>
            </div>
            <div class="statCard">
                <div class="statNum green">{{ $totalTutorAktif }}</div>
                <div class="statLabel">Tutor Aktif Mengajar</div>
            </div>
        </div>

        {{-- ── Rekapitulasi Per Tutor ── --}}
        <div class="sectionRow">
            <h2>Rekapitulasi Kinerja Tutor</h2>
            <span class="badgeCount">{{ $totalTutorAktif }} Tutor Aktif</span>
        </div>

        <div class="tutorList">
            @forelse($rekapTutor as $rekap)
                @php
                    $pct = $rekap['pct_hadir'];
                    $fillClass = $pct >= 80 ? 'high' : ($pct >= 50 ? 'mid' : 'low');
                    $tutor = $rekap['tutor'];
                    $initials = strtoupper(substr($tutor->nama_lengkap ?? 'TU', 0, 2));
                @endphp
                <div class="tutorCard">
                    <div class="tutorCardTop">
                        <div class="tutorAvatarGroup">
                            <div class="tutorAvatar">{{ $initials }}</div>
                            <div class="tutorInfo">
                                <div class="tutorName">{{ $tutor->nama_lengkap }}</div>
                                <div class="tutorJabatan">{{ $tutor->jabatan ?: 'Tutor PKBM' }}</div>
                            </div>
                        </div>
                        <div class="tutorJamBox">
                            <div class="tutorJamNum">
                                {{ $rekap['jam_mengajar'] > 0 ? $rekap['jam_mengajar'] . ' Jam' : $rekap['hadir'] . ' Kali' }}</div>
                            <div class="tutorJamLabel">{{ $rekap['jam_mengajar'] > 0 ? 'Total Mengajar' : 'Total Hadir' }}</div>
                        </div>
                    </div>

                    <div class="progressRow">
                        <div class="progressMeta">
                            <span class="progressLabel">Tingkat Kehadiran ({{ $rekap['hadir'] }}/{{ $rekap['total_sesi'] }}
                                Sesi)</span>
                            <span class="progressVal">{{ $pct }}%</span>
                        </div>
                        <div class="progressTrack">
                            <div class="progressFill {{ $fillClass }}" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state-standard">
                    <ion-icon name="document-text-outline" class="empty-icon"></ion-icon>
                    <div class="empty-title">Belum Ada Rekapitulasi</div>
                    <div class="empty-desc">Tidak ada data presensi atau sesi KBM untuk filter periode yang dipilih.</div>
                </div>
            @endforelse
        </div>

        {{-- ── Unduh PDF ── --}}
        @if($rekapTutor->count() > 0)
            <div class="ctaArea">
                <a href="{{ route('kepsek.laporan.pdf', ['bulan' => $bulan, 'tahun' => $tahun, 'tutor_id' => $tutorId, 'siswa_id' => $siswaId]) }}"
                    class="ctaBtn" id="btnUnduhPdf" target="_blank">
                    <ion-icon name="download-outline"></ion-icon>
                    UNDUH DOKUMEN LAPORAN LENGKAP (PDF)
                </a>
            </div>
        @endif
    </div>


@endsection