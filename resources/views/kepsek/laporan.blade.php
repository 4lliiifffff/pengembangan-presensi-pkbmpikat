@extends('layouts.kepsek')

@section('title', 'Laporan Presensi & KBM — Kepala Sekolah')

@section('content')

@php
    $namaBulan = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
        5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
        9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
    ];
@endphp

<div class="lp-header">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
        <div>
            <span style="font-size:11px;font-weight:800;letter-spacing:1px;color:#0284c7;text-transform:uppercase;">Laporan Operasional Akademik</span>
            <h1 class="lp-title" style="margin-top:2px;">Laporan Presensi &amp; KBM Tutor</h1>
            <p class="lp-sub">Periode: {{ $namaBulan[$bulan] }} {{ $tahun }} • Monitoring kehadiran, moda belajar, dan jam mengajar</p>
        </div>
        <div>
            <a href="{{ route('kepsek.payroll.index', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="filterBtn" style="background:#4f46e5;text-decoration:none;display:inline-flex;align-items:center;gap:6px;padding:8px 14px;font-size:13px;border-radius:8px;color:#fff;box-shadow:0 4px 12px rgba(79,70,229,0.25);">
                <ion-icon name="wallet-outline" style="font-size:16px;"></ion-icon> Buka Rekapitulasi Payroll ({{ $namaBulan[$bulan] }})
            </a>
        </div>
    </div>
</div>

{{-- ── Filter Bulan / Tahun ── --}}
<form method="GET" action="{{ route('kepsek.laporan') }}">
    <div class="filterBar" style="flex-wrap: wrap;">
        <div style="display:flex; gap:8px; width: 100%;">
            <select name="bulan" class="filterSelect" aria-label="Pilih Bulan">
                @foreach($namaBulan as $num => $label)
                    <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select name="tahun" class="filterSelect" aria-label="Pilih Tahun" style="flex: 1;">
                @foreach($tahunOptions as $y)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <button type="submit" class="filterBtn">
                <ion-icon name="search-outline" style="font-size:15px;vertical-align:middle;"></ion-icon>
                Filter
            </button>
        </div>

        <div style="display:flex; gap:8px; width: 100%;">
            <select name="tutor_id" class="filterSelect" aria-label="Pilih Tutor">
                <option value="">Semua Tutor</option>
                @foreach($allTutors as $t_item)
                    <option value="{{ $t_item->id }}" {{ $tutorId == $t_item->id ? 'selected' : '' }}>{{ $t_item->nama_lengkap }}</option>
                @endforeach
            </select>

            <select name="siswa_id" class="filterSelect" aria-label="Pilih Siswa">
                <option value="">Semua Siswa</option>
                @foreach($allSiswas as $s_item)
                    <option value="{{ $s_item->id }}" {{ $siswaId == $s_item->id ? 'selected' : '' }}>{{ $s_item->nama_siswa }}</option>
                @endforeach
            </select>
        </div>
    </div>
</form>

{{-- ── Summary Stats ── --}}
<div class="statRow">
    <div class="statCard">
        <div class="statNum blue">{{ $totalHadir }}</div>
        <div class="statLabel">Total Hadir</div>
    </div>
    <div class="statCard">
        <div class="statNum green">{{ $totalIzin }}</div>
        <div class="statLabel">Total Izin</div>
    </div>
    <div class="statCard">
        <div class="statNum red">{{ $totalTutorAktif }}</div>
        <div class="statLabel">Tutor Aktif</div>
    </div>
</div>

{{-- ── Rekapitulasi Per Tutor ── --}}
<div class="sectionRow">
    <h2>Rekapitulasi Tutor</h2>
    <span class="badgeCount">{{ $totalTutorAktif }} Aktif</span>
</div>

<div class="tutorList">
    @forelse($rekapTutor as $rekap)
        @php
            $pct       = $rekap['pct_hadir'];
            $fillClass = $pct >= 80 ? 'high' : ($pct >= 50 ? 'mid' : 'low');
            $tutor     = $rekap['tutor'];
        @endphp
        <div class="tutorCard">
            <div class="tutorCardTop" style="margin-bottom: 0;">
                <div class="tutorInfo">
                    <div class="tutorName">{{ $tutor->nama_lengkap }}</div>
                    <div class="tutorJabatan">{{ $tutor->jabatan ?: 'Tutor' }}</div>
                </div>
                <div class="tutorJamBox">
                    <div class="tutorJamNum">{{ $rekap['jam_mengajar'] > 0 ? $rekap['jam_mengajar'].' Jam' : $rekap['hadir'].' Kali' }}</div>
                    <div class="tutorJamLabel">{{ $rekap['jam_mengajar'] > 0 ? 'Total Mengajar' : 'Total Hadir' }}</div>
                </div>
            </div>
        </div>
    @empty
        <div class="emptyLaporan">
            <ion-icon name="document-text-outline"></ion-icon>
            Belum ada data presensi untuk periode ini.
        </div>
    @endforelse
</div>

{{-- ── Unduh PDF ── --}}
@if($rekapTutor->count() > 0)
<div class="ctaArea">
    <a href="{{ route('kepsek.laporan.pdf', ['bulan' => $bulan, 'tahun' => $tahun, 'tutor_id' => $tutorId, 'siswa_id' => $siswaId]) }}"
       class="ctaBtn" id="btnUnduhPdf">
        <ion-icon name="download-outline"></ion-icon>
        UNDUH LAPORAN LENGKAP
    </a>
</div>
@endif

@endsection
