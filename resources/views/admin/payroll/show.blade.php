@extends(auth()->user()->role === 'kepala_sekolah' ? 'layouts.kepsek' : 'layouts.admin')

@php
    $rolePrefix = auth()->user()->role === 'kepala_sekolah' ? 'kepsek' : 'admin';
@endphp

@section('title', 'Detail Slip Gaji Tutor')

@section('content')
<div class="pageHeaderRow" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <div>
        <a href="{{ route($rolePrefix . '.payroll.index', ['bulan' => $bulan, 'tahun' => $tahun]) }}" style="font-size:13px;color:#0284c7;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:4px;">
            <ion-icon name="arrow-back-outline"></ion-icon> Kembali ke Rekap Payroll
        </a>
        <h2 style="margin:0;font-size:20px;">Detail Slip Gaji: {{ $tutor->nama_lengkap }}</h2>
        <p style="margin:2px 0 0;color:#64748b;font-size:13px;">Periode {{ $payroll['periode_label'] }} • NIK: {{ $tutor->nik }}</p>
    </div>

    <div>
        <a href="{{ route($rolePrefix . '.payroll.slip-pdf', [$tutor->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btnPrimary" style="padding:8px 16px;background:#059669;">
            <ion-icon name="download-outline"></ion-icon> Cetak Slip Gaji PDF
        </a>
    </div>
</div>

{{-- ── Summary Card ── --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:12px;margin-bottom:20px;">
    <div style="background:#fff;padding:16px;border-radius:10px;border:1px solid #e2e8f0;border-left:4px solid #10b981;">
        <div style="font-size:12px;color:#64748b;font-weight:600;">Total Take Home Pay (Honorarium)</div>
        <div style="font-size:22px;font-weight:800;color:#059669;margin-top:4px;">{{ $payroll['formatted_total_honor'] }}</div>
    </div>
    <div style="background:#fff;padding:16px;border-radius:10px;border:1px solid #e2e8f0;border-left:4px solid #0284c7;">
        <div style="font-size:12px;color:#64748b;font-weight:600;">Total Akumulasi Jam Mengajar</div>
        <div style="font-size:22px;font-weight:800;color:#0284c7;margin-top:4px;">{{ $payroll['total_jam'] }} Jam</div>
    </div>
    <div style="background:#fff;padding:16px;border-radius:10px;border:1px solid #e2e8f0;border-left:4px solid #6366f1;">
        <div style="font-size:12px;color:#64748b;font-weight:600;">Total Sesi Mengajar Valid</div>
        <div style="font-size:22px;font-weight:800;color:#4f46e5;margin-top:4px;">{{ $payroll['total_sesi_hadir'] }} Sesi</div>
    </div>
</div>

{{-- ── Breakdown Per Siswa Table ── --}}
<div style="background:#fff;border-radius:10px;border:1px solid #e2e8f0;margin-bottom:20px;overflow:hidden;">
    <div style="padding:14px 16px;border-bottom:1px solid #e2e8f0;font-weight:700;font-size:15px;color:#1e293b;">
        Rincian Honorarium Berdasarkan Tarif Masing-Masing Siswa
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;text-align:left;font-size:13px;">
            <thead>
                <tr style="background:#f8fafc;color:#475569;border-bottom:1px solid #e2e8f0;">
                    <th style="padding:10px 14px;">Nama Siswa</th>
                    <th style="padding:10px 14px;">Total Sesi</th>
                    <th style="padding:10px 14px;">Total Jam</th>
                    <th style="padding:10px 14px;">Tarif Per Jam</th>
                    <th style="padding:10px 14px;text-align:right;">Subtotal Honor</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payroll['siswa_summary'] as $s)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:12px 14px;font-weight:600;color:#0f172a;">{{ $s['nama_siswa'] }}</td>
                    <td style="padding:12px 14px;">{{ $s['total_sesi'] }} kali</td>
                    <td style="padding:12px 14px;color:#0284c7;font-weight:600;">{{ $s['total_jam'] }} Jam</td>
                    <td style="padding:12px 14px;color:#475569;">{{ $s['formatted_tarif'] }} / jam</td>
                    <td style="padding:12px 14px;text-align:right;font-weight:700;color:#059669;">{{ $s['formatted_subtotal'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:24px;color:#94a3b8;">Belum ada rincian mengajar siswa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Rincian Log Sesi Mengajar ── --}}
<div style="background:#fff;border-radius:10px;border:1px solid #e2e8f0;overflow:hidden;">
    <div style="padding:14px 16px;border-bottom:1px solid #e2e8f0;font-weight:700;font-size:15px;color:#1e293b;">
        Log Rincian Sesi Kehadiran Terverifikasi
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;text-align:left;font-size:13px;">
            <thead>
                <tr style="background:#f8fafc;color:#475569;border-bottom:1px solid #e2e8f0;">
                    <th style="padding:10px 14px;">Tanggal</th>
                    <th style="padding:10px 14px;">Siswa</th>
                    <th style="padding:10px 14px;">Jam Sesi</th>
                    <th style="padding:10px 14px;">Durasi</th>
                    <th style="padding:10px 14px;">Moda</th>
                    <th style="padding:10px 14px;text-align:right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payroll['session_rows'] as $row)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:12px 14px;color:#334155;">{{ \Carbon\Carbon::parse($row['tgl_presensi'])->translatedFormat('d M Y') }}</td>
                    <td style="padding:12px 14px;font-weight:600;">{{ $row['nama_siswa'] }}</td>
                    <td style="padding:12px 14px;color:#64748b;">{{ $row['jam_mulai'] }} s/d {{ $row['jam_selesai'] }}</td>
                    <td style="padding:12px 14px;color:#0284c7;font-weight:600;">{{ $row['durasi_jam'] }} Jam</td>
                    <td style="padding:12px 14px;color:#475569;">{{ $row['moda_label'] }}</td>
                    <td style="padding:12px 14px;text-align:right;font-weight:700;color:#059669;">{{ $row['formatted_subtotal'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:24px;color:#94a3b8;">Belum ada log sesi mengajar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
