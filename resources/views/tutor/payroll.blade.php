@extends('layouts.presensi')

@section('title', 'Slip Gaji Digital')

@section('content')

@php
    $user = auth()->user();
    $displayName = (string) (($user->nama_lengkap ?? $user->name) ?? 'Tutor');
@endphp

{{-- ── Top Bar ── --}}
<div class="llTopBar">
    <div class="llTopRow">
        <a href="{{ route('tutor.dashboard') }}" class="llBackBtn" aria-label="Kembali">
            <ion-icon name="arrow-back-outline" style="font-size:20px;"></ion-icon>
        </a>
        <div style="flex: 1;">
            <div class="llPageTitle">Slip Gaji Digital</div>
            <div class="llPageSub">Rincian honorarium mengajar bulanan</div>
        </div>
        <button class="llBackBtn" type="button" aria-label="Tema" id="themeToggleBtn">
            <ion-icon name="moon-outline" style="font-size:20px;" id="themeToggleIcon"></ion-icon>
        </button>
    </div>
</div>

<div style="padding:16px 16px 80px;">

    {{-- ── Filter Month & Year ── --}}
    <form method="GET" action="{{ route('tutor.payroll.index') }}" style="background:var(--bg-card,#fff);padding:12px 14px;border-radius:12px;border:1px solid var(--border-color,#e2e8f0);margin-bottom:16px;display:flex;gap:8px;align-items:center;">
        <select name="bulan" class="formControl" style="flex:1;padding:8px 10px;font-size:13px;" onchange="this.form.submit()">
            @foreach(range(1, 12) as $m)
                <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>

        <select name="tahun" class="formControl" style="flex:1;padding:8px 10px;font-size:13px;" onchange="this.form.submit()">
            @foreach($tahunOptions as $y)
                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>

        <a href="{{ route('tutor.payroll.pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btnPrimary" style="padding:8px 12px;font-size:13px;background:#059669;white-space:nowrap;display:inline-flex;align-items:center;gap:4px;">
            <ion-icon name="download-outline"></ion-icon> PDF
        </a>
    </form>

    {{-- ── Take Home Pay Header Card ── --}}
    <div style="background:linear-gradient(135deg, #059669 0%, #10b981 100%);color:#fff;padding:20px;border-radius:16px;box-shadow:0 4px 14px rgba(5,150,105,0.25);margin-bottom:16px;">
        <div style="font-size:12px;opacity:0.9;font-weight:600;">TOTAL HONORARIUM PERIODE {{ strtoupper($payroll['periode_label']) }}</div>
        <div style="font-size:28px;font-weight:800;margin-top:4px;">{{ $payroll['formatted_total_honor'] }}</div>

        <div style="display:flex;gap:16px;margin-top:14px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.2);font-size:12px;">
            <div>
                <span style="opacity:0.8;">Total Jam:</span>
                <strong>{{ $payroll['total_jam'] }} Jam</strong>
            </div>
            <div>
                <span style="opacity:0.8;">Total Sesi:</span>
                <strong>{{ $payroll['total_sesi_hadir'] }} Sesi</strong>
            </div>
            <div>
                <span style="opacity:0.8;">Izin/Sakit:</span>
                <strong>{{ $payroll['total_izin_sakit'] }} Hari</strong>
            </div>
        </div>
    </div>

    {{-- ── Rincian Per Siswa ── --}}
    <div style="background:var(--bg-card,#fff);border-radius:12px;border:1px solid var(--border-color,#e2e8f0);padding:16px;margin-bottom:16px;">
        <h4 style="margin:0 0 12px;font-size:15px;font-weight:700;color:var(--text-main,#0f172a);">Rincian Honor Per Siswa</h4>

        @forelse($payroll['siswa_summary'] as $s)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border-color,#f1f5f9);">
                <div>
                    <strong style="font-size:14px;display:block;color:var(--text-main,#0f172a);">{{ $s['nama_siswa'] }}</strong>
                    <span style="font-size:12px;color:var(--sub,#64748b);">
                        {{ $s['total_sesi'] }} sesi ({{ $s['total_jam'] }} jam) • {{ $s['formatted_tarif'] }}/jam
                    </span>
                </div>
                <div style="font-weight:700;color:#059669;font-size:14px;">
                    {{ $s['formatted_subtotal'] }}
                </div>
            </div>
        @empty
            <p style="text-align:center;color:var(--sub,#94a3b8);padding:16px;margin:0;">Belum ada rincian jam mengajar bulan ini.</p>
        @endforelse
    </div>

</div>
@endsection
