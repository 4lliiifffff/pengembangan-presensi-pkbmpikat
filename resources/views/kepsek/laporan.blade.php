@extends('layout.kepsek')

@section('title', 'Laporan Kehadiran Tutor')

@section('content')

@php
    $namaBulan = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
        5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
        9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
    ];
@endphp

<style>
    /* ── Page Header ── */
    .lp-header {
        padding: 18px 16px 0;
    }
    .lp-title {
        font-size: 20px;
        font-weight: 900;
        color: var(--text);
        margin: 0 0 2px;
        letter-spacing: -0.3px;
    }
    .lp-sub {
        font-size: 12px;
        color: var(--muted);
        font-weight: 600;
        margin: 0 0 14px;
    }

    /* ── Filter Bar ── */
    .filterBar {
        display: flex;
        gap: 8px;
        padding: 0 16px 14px;
        align-items: center;
    }
    .filterSelect {
        flex: 1;
        padding: 10px 12px;
        border-radius: 14px;
        border: 1px solid var(--border);
        background: var(--input-bg);
        color: var(--text);
        font-size: 13px;
        font-weight: 700;
        outline: none;
        -webkit-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%2394a3b8' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 28px;
    }
    .filterSelect:focus {
        border-color: rgba(11,94,215,0.45);
        box-shadow: 0 0 0 3px rgba(11,94,215,0.12);
    }
    .filterBtn {
        padding: 10px 16px;
        border-radius: 14px;
        background: var(--blue2);
        color: #fff;
        border: none;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        white-space: nowrap;
    }

    /* ── Stat Cards ── */
    .statRow {
        display: flex;
        gap: 10px;
        padding: 0 16px 14px;
    }
    .statCard {
        flex: 1;
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 12px 10px;
        background: var(--card-alt);
        text-align: center;
    }
    .statNum {
        font-size: 26px;
        font-weight: 900;
        line-height: 1;
        margin-bottom: 4px;
    }
    .statNum.blue  { color: var(--blue2); }
    .statNum.green { color: var(--success); }
    .statNum.red   { color: var(--danger); }
    .statLabel {
        font-size: 10px;
        font-weight: 800;
        color: var(--muted);
        letter-spacing: 0.2px;
        text-transform: uppercase;
    }

    /* ── Section header ── */
    .sectionRow {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 4px 16px 12px;
    }
    .sectionRow h2 { margin: 0; font-size: 14px; font-weight: 900; }
    .badgeCount {
        font-size: 11px;
        font-weight: 900;
        padding: 5px 10px;
        border-radius: 999px;
        background: rgba(11,94,215,0.10);
        color: var(--blue2);
        border: 1px solid rgba(11,94,215,0.18);
    }

    /* ── Tutor Card ── */
    .tutorList { padding: 0 16px 10px; display: flex; flex-direction: column; gap: 10px; }

    .tutorCard {
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 14px;
        background: var(--card-alt2);
    }
    .tutorCardTop {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 10px;
    }
    .tutorInfo { min-width: 0; }
    .tutorName {
        font-size: 14px;
        font-weight: 900;
        color: var(--text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .tutorJabatan {
        font-size: 11px;
        color: var(--muted);
        font-weight: 700;
        margin-top: 2px;
    }
    .tutorJamBox {
        text-align: right;
        flex-shrink: 0;
    }
    .tutorJamNum {
        font-size: 18px;
        font-weight: 900;
        color: var(--blue2);
        line-height: 1;
    }
    .tutorJamLabel {
        font-size: 9px;
        font-weight: 800;
        color: var(--muted);
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    /* Progress bar row */
    .progressRow {
        margin-top: 4px;
    }
    .progressMeta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
    }
    .progressLabel {
        font-size: 11px;
        font-weight: 800;
        color: var(--muted);
    }
    .progressVal {
        font-size: 11px;
        font-weight: 900;
        color: var(--text);
    }
    .progressTrack {
        height: 6px;
        border-radius: 999px;
        background: var(--border);
        overflow: hidden;
    }
    .progressFill {
        height: 100%;
        border-radius: 999px;
        transition: width 0.4s ease;
    }
    .progressFill.high   { background: var(--success); }
    .progressFill.mid    { background: var(--blue2); }
    .progressFill.low    { background: var(--danger); }

    /* ── Empty ── */
    .emptyLaporan {
        margin: 10px 0 20px;
        padding: 30px 20px;
        text-align: center;
        border-radius: 16px;
        border: 1px dashed var(--empty-dashed);
        color: var(--muted);
        font-weight: 800;
        font-size: 13px;
    }
    .emptyLaporan ion-icon {
        font-size: 36px;
        display: block;
        margin: 0 auto 8px;
        opacity: 0.4;
    }

    /* ── CTA Download Button ── */
    .ctaArea {
        padding: 10px 16px 20px;
    }
    .ctaBtn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 16px;
        border-radius: 18px;
        background: linear-gradient(135deg, #0B5ED7 0%, #1A73E8 100%);
        color: #fff;
        font-size: 13px;
        font-weight: 900;
        letter-spacing: 0.5px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(11,94,215,0.28);
        transition: opacity 0.15s, transform 0.15s;
    }
    .ctaBtn:active {
        opacity: 0.88;
        transform: scale(0.98);
    }
    .ctaBtn ion-icon { font-size: 20px; }
</style>

<div class="lp-header">
    <h1 class="lp-title">Laporan Perhitungan</h1>
    <p class="lp-sub">Periode: {{ $namaBulan[$bulan] }} {{ $tahun }}</p>
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
