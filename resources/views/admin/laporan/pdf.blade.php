<!DOCTYPE html>
<html>
<head>
    <title>Laporan Presensi</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; }
        .summary { margin-bottom: 20px; }
        .summary table { width: 50%; border-collapse: collapse; }
        .summary th, .summary td { border: 1px solid #ddd; padding: 5px; text-align: center; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 6px; }
        .data-table th { background: #f0f0f0; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Rekap Presensi</h2>
        <p>Periode: {{ $startDate->translatedFormat('d M Y') }} - {{ $endDate->translatedFormat('d M Y') }}</p>
    </div>

    <div class="summary">
        <table>
            <thead>
                <tr>
                    <th>Total Hadir</th>
                    <th>Total Izin</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $totalHadir }}</td>
                    <td>{{ $totalIzin }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Tutor</th>
                <th>Nama Siswa</th>
                <th>Masuk</th>
                <th>Selesai</th>
                <th>Foto (M/S)</th>
                <th>Lokasi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($presensis as $idx => $p)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($p->tgl_presensi)->format('d/m/Y') }}</td>
                <td>{{ $p->tutor->nama_lengkap ?? 'Tutor' }}</td>
                <td>{{ $p->siswa->nama_siswa ?? '-' }}</td>
                <td style="text-align: center;">{{ $p->jam_mulai ? \Carbon\Carbon::parse($p->jam_mulai)->format('H:i') : '-' }}</td>
                <td style="text-align: center;">{{ $p->jam_selesai ? \Carbon\Carbon::parse($p->jam_selesai)->format('H:i') : '-' }}</td>
                <td style="text-align: center;">
                    @if($p->foto_mulai) [Ada] @else [-] @endif / 
                    @if($p->foto_selesai) [Ada] @else [-] @endif
                </td>
                <td>{{ $p->lokasi_mulai ?? '-' }}</td>
                <td style="text-align: center;">{{ $p->calculated_status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
