@extends('layouts.admin')

@section('title', 'Rekapitulasi Penggajian & Honorarium')

@section('content')
<div class="pageHeaderRow" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <div>
        <h2 style="margin:0;font-size:20px;">Rekapitulasi Penggajian & Honorarium</h2>
        <p style="margin:4px 0 0;color:#64748b;font-size:13px;">Laporan anggaran honorarium tutor berbasis tarif jam mengajar per siswa</p>
    </div>

    <div>
        <a href="{{ route('admin.payroll.rekap-pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btnPrimary" style="padding:8px 16px;background:#0284c7;">
            <ion-icon name="document-text-outline"></ion-icon> Export PDF Rekap Anggaran
        </a>
    </div>
</div>

{{-- ── Filter Bar ── --}}
<div style="background:#fff;padding:14px;border-radius:10px;border:1px solid #e2e8f0;margin-bottom:16px;">
    <form method="GET" action="{{ route('admin.payroll.index') }}" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <div>
            <label style="font-size:12px;font-weight:600;color:#475569;">Bulan:</label>
            <select name="bulan" class="input" style="padding:6px 12px;font-size:13px;" onchange="this.form.submit()">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="font-size:12px;font-weight:600;color:#475569;">Tahun:</label>
            <select name="tahun" class="input" style="padding:6px 12px;font-size:13px;" onchange="this.form.submit()">
                @foreach($tahunOptions as $y)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-top:18px;">
            <button type="submit" class="btnOutline" style="padding:6px 14px;">Terapkan Filter</button>
        </div>
    </form>
</div>

{{-- ── Summary Cards ── --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:12px;margin-bottom:20px;">
    <div style="background:#fff;padding:16px;border-radius:10px;border:1px solid #e2e8f0;border-left:4px solid #10b981;">
        <div style="font-size:12px;color:#64748b;font-weight:600;">Total Anggaran Honorarium</div>
        <div style="font-size:22px;font-weight:800;color:#059669;margin-top:4px;">{{ $summary['formatted_total_anggaran'] }}</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px;">Periode {{ $summary['periode_label'] }}</div>
    </div>

    <div style="background:#fff;padding:16px;border-radius:10px;border:1px solid #e2e8f0;border-left:4px solid #0284c7;">
        <div style="font-size:12px;color:#64748b;font-weight:600;">Total Jam Mengajar</div>
        <div style="font-size:22px;font-weight:800;color:#0284c7;margin-top:4px;">{{ $summary['total_jam_global'] }} Jam</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px;">{{ $summary['total_sesi_global'] }} Total Sesi Terverifikasi</div>
    </div>

    <div style="background:#fff;padding:16px;border-radius:10px;border:1px solid #e2e8f0;border-left:4px solid #f59e0b;">
        <div style="font-size:12px;color:#64748b;font-weight:600;">Jumlah Tutor Penerima</div>
        <div style="font-size:22px;font-weight:800;color:#d97706;margin-top:4px;">{{ $summary['total_tutor'] }} Tutor</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px;">Terdaftar di PKBM Pikat</div>
    </div>
</div>

{{-- ── Table Rekap Per Tutor ── --}}
<div style="background:#fff;border-radius:10px;border:1px solid #e2e8f0;overflow:hidden;">
    <div style="padding:14px 16px;border-bottom:1px solid #e2e8f0;font-weight:700;font-size:15px;color:#1e293b;">
        Daftar Honorarium Tutor — Periode {{ $summary['periode_label'] }}
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;text-align:left;font-size:13px;">
            <thead>
                <tr style="background:#f8fafc;color:#475569;border-bottom:1px solid #e2e8f0;">
                    <th style="padding:10px 14px;">No</th>
                    <th style="padding:10px 14px;">Nama Tutor</th>
                    <th style="padding:10px 14px;">Sesi Hadir</th>
                    <th style="padding:10px 14px;">Izin / Sakit</th>
                    <th style="padding:10px 14px;">Total Jam</th>
                    <th style="padding:10px 14px;">Total Honorarium</th>
                    <th style="padding:10px 14px;text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($summary['payrolls'] as $idx => $p)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:12px 14px;color:#64748b;">{{ $idx + 1 }}</td>
                    <td style="padding:12px 14px;">
                        <strong style="color:#0f172a;display:block;">{{ $p['tutor']->nama_lengkap }}</strong>
                        <span style="font-size:11px;color:#64748b;">NIK: {{ $p['tutor']->nik }}</span>
                    </td>
                    <td style="padding:12px 14px;">{{ $p['total_sesi_hadir'] }} sesi</td>
                    <td style="padding:12px 14px;color:#64748b;">{{ $p['total_izin_sakit'] }} hari</td>
                    <td style="padding:12px 14px;font-weight:600;color:#0284c7;">{{ $p['total_jam'] }} Jam</td>
                    <td style="padding:12px 14px;font-weight:700;color:#059669;">{{ $p['formatted_total_honor'] }}</td>
                    <td style="padding:12px 14px;text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;">
                            <a href="{{ route('admin.payroll.show', [$p['tutor']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="smallBtn edit" style="padding:4px 10px;font-size:12px;">
                                <ion-icon name="eye-outline"></ion-icon> Detail
                            </a>
                            <a href="{{ route('admin.payroll.slip-pdf', [$p['tutor']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="smallBtn" style="padding:4px 10px;font-size:12px;background:#059669;color:#fff;" title="Cetak Slip PDF">
                                <ion-icon name="download-outline"></ion-icon> Slip PDF
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:32px;color:#94a3b8;">
                        Belum ada data presensi/honorarium untuk periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
