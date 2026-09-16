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

    {{-- ── Filter Month & Year ── --}}
    <form method="GET" action="{{ route('tutor.payroll.index') }}" class="filter-box">
        <div style="flex: 1; min-width: 120px;">
            <select name="bulan" class="formControl" style="width:100%;padding:9px 12px;font-size:13px;border-radius:8px;font-weight:600;" onchange="this.form.submit()">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="flex: 1; min-width: 100px;">
            <select name="tahun" class="formControl" style="width:100%;padding:9px 12px;font-size:13px;border-radius:8px;font-weight:600;" onchange="this.form.submit()">
                @foreach($tahunOptions as $y)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <a href="{{ route('tutor.payroll.pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btnPrimary" style="padding:9px 14px;font-size:12px;font-weight:700;background:#059669;white-space:nowrap;display:inline-flex;align-items:center;gap:6px;border-radius:8px;text-decoration:none;">
                <ion-icon name="download-outline" style="font-size:16px;"></ion-icon> Cetak PDF
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
            <ion-icon name="school-outline" style="font-size:18px;color:#0284c7;"></ion-icon>
            Rincian Honorarium Per Siswa
        </h4>

        @forelse($payroll['siswa_summary'] as $s)
            <div class="student-item-row">
                <div>
                    <div class="sir-name" style="display:flex;align-items:center;gap:6px;">
                        <span>{{ $s['nama_siswa'] }}</span>
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
            <ion-icon name="time-outline" style="font-size:18px;color:#059669;"></ion-icon>
            Riwayat Sesi Mengajar
        </h4>

        @foreach($payroll['session_rows'] as $row)
            <div class="session-item-row">
                <div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <strong style="color:var(--text,#0f172a);">{{ $row['nama_siswa'] }}</strong>
                        @if($row['is_abk'] ?? false)
                            <span class="app-badge badge-abk">ABK</span>
                        @endif
                    </div>
                    <span style="font-size:11px;color:var(--muted,#64748b);display:block;margin-top:2px;">
                        {{ \Carbon\Carbon::parse($row['tgl_presensi'])->translatedFormat('d M Y') }} • {{ $row['jam_mulai'] }} - {{ $row['jam_selesai'] }} ({{ $row['durasi_jam'] }} J)
                    </span>
                    <span style="font-size:10px;color:#0284c7;font-weight:700;">
                        {{ $row['kategori_nama'] ?? 'Tutorial Komunitas' }}
                    </span>
                </div>
                <div style="text-align:right;">
                    <div style="font-weight:800;color:#059669;font-size:13px;">{{ $row['formatted_subtotal'] }}</div>
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
