<!DOCTYPE html>
<html >
<head >
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title >Laporan Presensi Magang / PKL</title>
    <style >
        body { font-family: sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0 0 4px; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 0; font-size: 12px; color: #555; }
        .info-box { margin-bottom: 15px; }
        .info-table { width: 100%; margin-bottom: 12px; }
        .info-table td { padding: 3px 0; font-size: 11px; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th, .data-table td { border: 1px solid #777; padding: 6px 8px; font-size: 10px; }
        .data-table th { background: #f2f2f2; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }
        .footer { margin-top: 30px; width: 100%; }
        .footer td { font-size: 11px; vertical-align: top; }
    </style>
</head>
<body >
    <div class="header">
        <h2 >PKBM PIKAT</h2>
        <p ><strong >Laporan Rekap Presensi Mahasiswa / Siswa Magang (PKL)</strong></p>
        <p >Periode: {{ \Carbon\Carbon::parse($startDateStr)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDateStr)->translatedFormat('d F Y') }}</p>
    </div>

    @if($selectedUser)
        <div class="info-box">
            <table class="info-table">
                <tr >
                    <td width="20%"><strong >Nama Peserta</strong></td>
                    <td width="3%">:</td>
                    <td >{{ $selectedUser->nama_lengkap ?? $selectedUser->name }}</td>
                </tr>
                <tr >
                    <td ><strong >NIM / NIK</strong></td>
                    <td >:</td>
                    <td >{{ $selectedUser->magang?->nim_nisn ?: $selectedUser->nik }}</td>
                </tr>
                <tr >
                    <td ><strong >Asal Instansi / Jurusan</strong></td>
                    <td >:</td>
                    <td >{{ $selectedUser->magang?->asal_instansi ?: '-' }} ({{ $selectedUser->magang?->jurusan ?: '-' }})</td>
                </tr>
                <tr >
                    <td ><strong >Dosen / Guru Pembimbing</strong></td>
                    <td >:</td>
                    <td >{{ $selectedUser->magang?->pembimbing_lapangan ?: '-' }}</td>
                </tr>
            </table>
        </div>
    @endif

    <table class="data-table">
        <thead >
            <tr >
                <th width="4%">No</th>
                <th width="14%">Tanggal</th>
                @if(!$selectedUser)
                    <th width="22%">Nama Magang & Instansi</th>
                @endif
                <th width="12%">Jam Masuk</th>
                <th width="12%">Jam Pulang</th>
                <th width="14%">Durasi</th>
                <th width="12%">Foto (M/P)</th>
                <th width="12%">Status</th>
            </tr>
        </thead>
        <tbody >
            @forelse($items as $idx => $p)
                @php
                    $u = $p->user;
                    $masuk = $p->jam_mulai ? substr((string)$p->jam_mulai, 0, 5) : '—';
                    $pulang = $p->jam_selesai ? substr((string)$p->jam_selesai, 0, 5) : '—';
                    
                    $durasi = '—';
                    if ($p->jam_mulai && $p->jam_selesai) {
                        try {
                            $dtM = \Carbon\Carbon::parse($p->tgl_presensi . ' ' . $p->jam_mulai);
                            $dtS = \Carbon\Carbon::parse($p->tgl_presensi . ' ' . $p->jam_selesai);
                            $diffMin = $dtM->diffInMinutes($dtS);
                            $durasi = floor($diffMin / 60) . 'j ' . ($diffMin % 60) . 'm';
                        } catch (\Throwable) {}
                    }
                @endphp
                <tr >
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td >{{ \Carbon\Carbon::parse($p->tgl_presensi)->translatedFormat('d/m/Y') }}</td>
                    @if(!$selectedUser)
                        <td >
                            <strong >{{ $u->nama_lengkap ?? $u->name }}</strong><br >
                            <small >{{ $u->magang?->asal_instansi ?: '-' }}</small>
                        </td>
                    @endif
                    <td class="text-center">{{ $masuk }} WIB</td>
                    <td class="text-center">{{ $pulang }} {{ $p->jam_selesai ? 'WIB' : '' }}</td>
                    <td class="text-center">{{ $durasi }}</td>
                    <td class="text-center">
                        {{ $p->foto_mulai ? '[Ada]' : '[-]' }} / {{ $p->foto_selesai ? '[Ada]' : '[-]' }}
                    </td>
                    <td class="text-center">
                        @if($p->jam_selesai)
                            <strong >Hadir</strong>
                        @else
                            Proses
                        @endif
                    </td>
                </tr>
            @empty
                <tr >
                    <td colspan="{{ $selectedUser ? 7 : 8 }}" class="text-center">Tidak ada catatan presensi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <table class="w-full">
            <tr >
                <td width="60%"></td>
                <td width="40%" class="text-center">
                    <p >Yogyakarta, {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y') }}</p>
                    <p >Pengelola / Kepala PKBM,</p>
                    <br ><br ><br ><br >
                    <p ><strong >( .................................................... )</strong></p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
