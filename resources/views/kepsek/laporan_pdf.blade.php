<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Kehadiran Tutor</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #fff;
        }

        /* ── Header ── */
        .header {
            text-align: center;
            padding: 24px 0 16px;
            border-bottom: 2px solid #0B5ED7;
            margin-bottom: 20px;
        }
        .header-badge {
            display: inline-block;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0B5ED7;
            border: 1px solid #0B5ED7;
            border-radius: 4px;
            padding: 2px 8px;
            margin-bottom: 6px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .header p {
            font-size: 11px;
            color: #64748b;
        }

        /* ── Summary Boxes ── */
        .summary-row {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin-bottom: 20px;
        }
        .summary-cell {
            display: table-cell;
            width: 33.33%;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            background: #f8fbff;
        }
        .summary-num {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .summary-num.blue  { color: #1A73E8; }
        .summary-num.green { color: #16a34a; }
        .summary-num.red   { color: #ef4444; }
        .summary-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            font-weight: bold;
        }

        /* ── Section title ── */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            border-left: 3px solid #0B5ED7;
            padding-left: 8px;
            margin-bottom: 10px;
        }

        /* ── Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        thead tr {
            background: #0B5ED7;
            color: #fff;
        }
        thead th {
            padding: 8px 10px;
            font-size: 10px;
            font-weight: bold;
            text-align: left;
            letter-spacing: 0.3px;
        }
        thead th.center { text-align: center; }

        tbody tr {
            border-bottom: 1px solid #f1f5f9;
        }
        tbody tr:nth-child(even) { background: #f8fbff; }
        tbody tr:last-child { border-bottom: 2px solid #e5e7eb; }

        tbody td {
            padding: 9px 10px;
            font-size: 10px;
            color: #1e293b;
        }
        tbody td.center { text-align: center; }

        /* ── Progress bar (CSS bar) ── */
        .pb-track {
            width: 80px;
            height: 5px;
            background: #e5e7eb;
            border-radius: 4px;
            display: inline-block;
            vertical-align: middle;
            margin-right: 4px;
            overflow: hidden;
        }
        .pb-fill {
            height: 100%;
            border-radius: 4px;
        }
        .pb-fill.high { background: #16a34a; }
        .pb-fill.mid  { background: #1A73E8; }
        .pb-fill.low  { background: #ef4444; }

        /* ── Footer ── */
        .footer {
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
            font-size: 9px;
            color: #94a3b8;
        }
        .footer-right {
            display: table-cell;
            text-align: right;
            font-size: 9px;
            color: #94a3b8;
        }
        .sign-area {
            text-align: center;
            margin-top: 30px;
        }
        .sign-box {
            display: inline-block;
            text-align: center;
        }
        .sign-label {
            font-size: 10px;
            color: #475569;
            margin-bottom: 50px;
        }
        .sign-name {
            border-top: 1px solid #475569;
            padding-top: 4px;
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            min-width: 160px;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <div class="header-badge">PKBM Pintar Berbakat Bandung</div>
        <h1>Rekap Kehadiran Tutor</h1>
        <p>Periode: {{ $startDate->translatedFormat('d F Y') }} &ndash; {{ $endDate->translatedFormat('d F Y') }}</p>
        <p>Dicetak: {{ $tanggalCetak->translatedFormat('d F Y, H:i') }} WIB</p>
    </div>

    {{-- Summary --}}
    <table style="margin-bottom:20px;">
        <tr>
            <td style="width:48%;border:1px solid #e5e7eb;border-radius:8px;padding:10px;text-align:center;background:#f8fbff;">
                <div style="font-size:22px;font-weight:bold;color:#1A73E8;">{{ $totalHadir }}</div>
                <div style="font-size:9px;color:#64748b;text-transform:uppercase;font-weight:bold;">Total Sesi Hadir</div>
            </td>
            <td style="width:4%;"></td>
            <td style="width:48%;border:1px solid #e5e7eb;border-radius:8px;padding:10px;text-align:center;background:#f8fbff;">
                <div style="font-size:22px;font-weight:bold;color:#ef4444;">{{ $totalTutorAktif }}</div>
                <div style="font-size:9px;color:#64748b;text-transform:uppercase;font-weight:bold;">Tutor Aktif</div>
            </td>
        </tr>
    </table>

    {{-- Rekapitulasi Tabel --}}
    <div class="section-title">Rekapitulasi Kehadiran per Tutor</div>

    <table>
        <thead>
            <tr>
                <th style="width:5%;">No</th>
                <th style="width:35%;">Nama Tutor</th>
                <th style="width:20%;">Jabatan</th>
                <th class="center" style="width:20%;">Kali Hadir</th>
                <th class="center" style="width:20%;">Total Mengajar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekapTutor as $i => $rekap)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td style="font-weight:bold;">{{ $rekap['tutor']->nama_lengkap }}</td>
                <td style="color:#64748b;">{{ $rekap['tutor']->jabatan ?: 'Tutor' }}</td>
                <td class="center" style="color:#16a34a;font-weight:bold;">{{ $rekap['hadir'] }}</td>
                <td class="center" style="font-weight:bold;">
                    {{ $rekap['jam_mengajar'] > 0 ? $rekap['jam_mengajar'].' Jam' : '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="center" style="color:#94a3b8;padding:20px;">
                    Tidak ada data presensi pada periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Tanda Tangan --}}
    <div style="text-align:right;margin-top:20px;">
        <div style="display:inline-block;text-align:center;min-width:180px;">
            <div style="font-size:10px;color:#475569;margin-bottom:50px;">
                Kepala Sekolah,<br>
                {{ $tanggalCetak->translatedFormat('d F Y') }}
            </div>
            <div style="border-top:1px solid #475569;padding-top:4px;font-size:10px;font-weight:bold;color:#0f172a;">
                (.................................)
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div style="margin-top:20px;border-top:1px solid #e5e7eb;padding-top:8px;">
        <table style="margin:0;">
            <tr>
                <td style="font-size:8px;color:#94a3b8;">Dicetak oleh sistem Smart Presensi — PKBM</td>
                <td style="text-align:right;font-size:8px;color:#94a3b8;">{{ $startDate->translatedFormat('F Y') }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
