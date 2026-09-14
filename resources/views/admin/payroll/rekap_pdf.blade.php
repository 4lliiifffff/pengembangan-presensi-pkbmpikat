<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Anggaran Payroll - {{ $summary['periode_label'] }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; margin: 0; padding: 15px; }
        .header { text-align: center; border-bottom: 2px solid #0b5ed7; padding-bottom: 8px; margin-bottom: 16px; }
        .header h2 { margin: 0; font-size: 16px; color: #0b5ed7; text-transform: uppercase; }
        .header p { margin: 2px 0 0; font-size: 10px; color: #64748b; }
        .title { text-align: center; font-size: 13px; font-weight: bold; margin-bottom: 14px; text-decoration: underline; }
        .summary-box { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .summary-box td { padding: 8px; background: #f8fafc; border: 1px solid #cbd5e1; text-align: center; }
        .table-data { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-data th { background: #0284c7; color: #ffffff; font-weight: bold; padding: 8px; border: 1px solid #0284c7; text-align: left; }
        .table-data td { padding: 8px; border: 1px solid #cbd5e1; }
        .signature-table { width: 100%; margin-top: 30px; }
        .signature-table td { text-align: center; vertical-align: top; width: 50%; }
        .signature-space { height: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>PKBM PIKAT</h2>
        <p>Pusat Kegiatan Belajar Masyarakat Pikat • Laporan Rekapitulasi Anggaran Penggajian Sekolah</p>
    </div>

    <div class="title">LAPORAN REKAPITULASI HONORARIUM TUTOR — PERIODE {{ strtoupper($summary['periode_label']) }}</div>

    <table class="summary-box">
        <tr>
            <td>
                <span style="font-size: 10px; color: #64748b;">TOTAL ANGGARAN PAYROLL</span><br>
                <strong style="font-size: 14px; color: #059669;">{{ $summary['formatted_total_anggaran'] }}</strong>
            </td>
            <td>
                <span style="font-size: 10px; color: #64748b;">TOTAL JAM MENGAJAR</span><br>
                <strong style="font-size: 14px; color: #0284c7;">{{ $summary['total_jam_global'] }} Jam</strong>
            </td>
            <td>
                <span style="font-size: 10px; color: #64748b;">TOTAL SESI HADIR</span><br>
                <strong style="font-size: 14px; color: #4f46e5;">{{ $summary['total_sesi_global'] }} Sesi</strong>
            </td>
            <td>
                <span style="font-size: 10px; color: #64748b;">JUMLAH TUTOR</span><br>
                <strong style="font-size: 14px; color: #d97706;">{{ $summary['total_tutor'] }} Orang</strong>
            </td>
        </tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 30%;">Nama Tutor</th>
                <th style="width: 15%;">NIK</th>
                <th style="width: 12%; text-align: center;">Sesi Hadir</th>
                <th style="width: 12%; text-align: center;">Izin / Sakit</th>
                <th style="width: 13%; text-align: center;">Total Jam</th>
                <th style="width: 20%; text-align: right;">Total Honorarium</th>
            </tr>
        </thead>
        <tbody>
            @forelse($summary['payrolls'] as $idx => $p)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td><strong>{{ $p['tutor']->nama_lengkap }}</strong></td>
                <td>{{ $p['tutor']->nik }}</td>
                <td style="text-align: center;">{{ $p['total_sesi_hadir'] }} sesi</td>
                <td style="text-align: center;">{{ $p['total_izin_sakit'] }} hari</td>
                <td style="text-align: center;">{{ $p['total_jam'] }} Jam</td>
                <td style="text-align: right; font-weight: bold; color: #059669;">{{ $p['formatted_total_honor'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #94a3b8;">Tidak ada data honorarium tutor.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                Mengetahui,<br>
                Bendahara PKBM Pikat,
                <div class="signature-space"></div>
                <strong>( Bendahara Sekolah )</strong>
            </td>
            <td>
                Sleman, {{ $tanggalCetak->translatedFormat('d F Y') }}<br>
                Kepala Sekolah PKBM Pikat,
                <div class="signature-space"></div>
                <strong>( Kepala Sekolah )</strong>
            </td>
        </tr>
    </table>
</body>
</html>
