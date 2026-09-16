@extends('layouts.kepsek')

@section('title', 'Laporan Presensi & KBM — Kepala Sekolah')

@section('content')

@php
    $namaBulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
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
                <a href="{{ route('kepsek.payroll.index', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btnPayrollShortcut">
                    <ion-icon name="wallet-outline"></ion-icon>
                    <span >Buka Rekapitulasi Payroll ({{ $namaBulan[$bulan] ?? '' }})</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ── Filter Card ── --}}
    <div class="laporanFilterCard">
        <form method="GET" action="{{ route('kepsek.laporan') }}">
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
                            <option value="{{ $t_item->id }}" {{ $tutorId == $t_item->id ? 'selected' : '' }}>{{ $t_item->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Siswa</label>
                    <select name="siswa_id" class="filterSelect" aria-label="Pilih Siswa">
                        <option value="">Semua Siswa</option>
                        @foreach($allSiswas as $s_item)
                            <option value="{{ $s_item->id }}" {{ $siswaId == $s_item->id ? 'selected' : '' }}>{{ $s_item->nama_siswa }}</option>
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
        <h2 >Rekapitulasi Kinerja Tutor</h2>
        <span class="badgeCount">{{ $totalTutorAktif }} Tutor Aktif</span>
    </div>

    <div class="tutorList">
        @forelse($rekapTutor as $rekap)
            @php
                $pct       = $rekap['pct_hadir'];
                $fillClass = $pct >= 80 ? 'high' : ($pct >= 50 ? 'mid' : 'low');
                $tutor     = $rekap['tutor'];
                $initials  = strtoupper(substr($tutor->nama_lengkap ?? 'TU', 0, 2));
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
                        <div class="tutorJamNum">{{ $rekap['jam_mengajar'] > 0 ? $rekap['jam_mengajar'].' Jam' : $rekap['hadir'].' Kali' }}</div>
                        <div class="tutorJamLabel">{{ $rekap['jam_mengajar'] > 0 ? 'Total Mengajar' : 'Total Hadir' }}</div>
                    </div>
                </div>

                <div class="progressRow">
                    <div class="progressMeta">
                        <span class="progressLabel">Tingkat Kehadiran ({{ $rekap['hadir'] }}/{{ $rekap['total_sesi'] }} Sesi)</span>
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
