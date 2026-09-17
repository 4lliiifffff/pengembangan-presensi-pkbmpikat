@extends('layouts.presensi')

@section('title', 'Slip Gaji Digital — PKBM Pikat')

@php
    $user = auth()->user();
    $displayName = (string) (($user->nama_lengkap ?? $user->name) ?? 'Tutor');
@endphp

@push('topbar')
    @include('layouts.components.navigasi_atas', [
        'titleName' => 'Slip Gaji Digital',
        'subTitle' => $displayName . ' • Honorarium Sesi',
        'dashRoute' => route('tutor.dashboard'),
        'backRoute' => route('tutor.dashboard'),
    ])
@endpush

@section('content')

<div class="tutor-payroll-container">

    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">HONORARIUM &amp; PENGGAJIAN</div>
                <h1 class="laporanHeaderTitle">Slip Gaji Digital Tutor</h1>
                <div class="laporanHeaderSub">Periode: {{ strtoupper($payroll['periode_label']) }}</div>
                <p class="laporanHeaderDesc">Rincian honorarium mengajar terverifikasi, akumulasi jam KBM, dan pengunduhan berkas slip resmi.</p>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('tutor.payroll.pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="profileBtnPrimary btn-action-primary" target="_blank">
                    <ion-icon name="download-outline"></ion-icon> Cetak Slip PDF
                </a>
            </div>
        </div>
    </div>

    {{-- ── Filter Month & Year ── --}}
    <form method="GET" action="{{ route('tutor.payroll.index') }}" class="filter-box">
        <div  class="flex-1 min-w-120">
            <select name="bulan" onchange="this.form.submit()" class="formControl w-full text-md rounded-md font-semibold px-3 py-2">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div  class="flex-1 min-w-100">
            <select name="tahun" onchange="this.form.submit()" class="formControl w-full text-md rounded-md font-semibold px-3 py-2">
                @foreach($tahunOptions as $y)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div >
            <a href="{{ route('tutor.payroll.pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="profileBtnPrimary btn-action-primary" target="_blank">
                <ion-icon name="download-outline" class="icon-sm"></ion-icon> Cetak PDF
            </a>
        </div>
    </form>

    {{-- ── Hero Take Home Pay Card ── --}}
    <div class="hero-slip-card">
        <div class="hero-slip-badge">
            PERIODE {{ strtoupper($payroll['periode_label']) }}
        </div>
        <div class="hero-slip-amount">
            {{ $payroll['formatted_total_honor'] }}
        </div>

        <div class="hero-metrics-grid">
            <div class="hm-item">
                <div class="val">{{ $payroll['total_jam'] }} Jam</div>
                <div class="lbl">Total Mengajar</div>
            </div>
            <div class="hm-item">
                <div class="val">{{ $payroll['total_sesi_hadir'] }} Sesi</div>
                <div class="lbl">Hadir Valid</div>
            </div>
            <div class="hm-item">
                <div class="val">{{ $payroll['total_izin_sakit'] }} Hari</div>
                <div class="lbl">Izin / Sakit</div>
            </div>
        </div>
    </div>

    {{-- ── Rincian Per Siswa ── --}}
    <div class="section-card">
        <h4 class="section-card-title">
            <ion-icon name="school-outline" class="text-primary"></ion-icon>
            Rincian Honorarium Per Siswa
        </h4>

        @forelse($payroll['siswa_summary'] as $s)
            <div class="student-item-row">
                <div >
                    <div class="sir-name d-flex items-center gap-1">
                        <span >{{ $s['nama_siswa'] }}</span>
                        @if($s['is_abk'] ?? false)
                            <span class="app-badge badge-abk">ABK</span>
                        @endif
                    </div>
                    <div class="sir-sub">
                        {{ $s['total_sesi'] }} pertemuan ({{ $s['total_jam'] }} jam mengajar)
                    </div>
                </div>
                <div class="sir-price">
                    {{ $s['formatted_subtotal'] }}
                </div>
            </div>
        @empty
            <div class="empty-state-standard">
                <ion-icon name="information-circle-outline" class="empty-icon"></ion-icon>
                <div class="empty-desc">Belum ada rincian jam mengajar tervalidasi bulan ini.</div>
            </div>
        @endforelse
    </div>

    {{-- ── Log Ringkas Sesi Mengajar ── --}}
    @if(count($payroll['session_rows'] ?? []) > 0)
    <div class="section-card">
        <h4 class="section-card-title">
            <ion-icon name="time-outline" class="text-success"></ion-icon>
            Riwayat Sesi Mengajar
        </h4>

        @foreach($payroll['session_rows'] as $row)
            <div class="session-item-row">
                <div >
                    <div class="d-flex items-center gap-1">
                        <strong class="text-dark">{{ $row['nama_siswa'] }}</strong>
                        @if($row['is_abk'] ?? false)
                            <span class="app-badge badge-abk">ABK</span>
                        @endif
                    </div>
                    <span class="text-xs text-muted d-block mt-1">
                        {{ \Carbon\Carbon::parse($row['tgl_presensi'])->translatedFormat('d M Y') }} • {{ $row['jam_mulai'] }} - {{ $row['jam_selesai'] }} ({{ $row['durasi_jam'] }} J)
                    </span>
                    <span class="text-xs font-bold text-primary">
                        {{ $row['kategori_nama'] ?? 'Tutorial Komunitas' }}
                    </span>
                </div>
                <div class="text-right">
                    <div class="font-extrabold text-success text-md">{{ $row['formatted_subtotal'] }}</div>
                    <span class="app-badge badge-reguler">
                        {{ $row['moda_label'] }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
