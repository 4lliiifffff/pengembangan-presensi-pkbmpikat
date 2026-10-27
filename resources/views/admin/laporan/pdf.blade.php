<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Presensi Resmi PKBM Pikat</title>
    <style>
        @page {
            margin: 20px 25px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.3;
        }
        .kop-table {
            width: 100%;
            border-bottom: 2.5px solid #0b5ed7;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .kop-logo {
            width: 60px;
            text-align: center;
            vertical-align: middle;
        }
        .kop-text {
            text-align: center;
            vertical-align: middle;
        }
        .kop-title {
            font-size: 14px;
            font-weight: bold;
            color: #0b5ed7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .kop-sub {
            font-size: 11px;
            font-weight: bold;
            color: #334155;
            margin: 2px 0;
        }
        .kop-contact {
            font-size: 8.5px;
            color: #64748b;
        }
        .report-title-box {
            text-align: center;
            margin-bottom: 12px;
        }
        .report-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
            margin: 0;
        }
        .report-period {
            font-size: 9.5px;
            color: #475569;
            margin-top: 3px;
        }
        .kpi-row {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .kpi-box {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            padding: 6px;
            text-align: center;
            border-radius: 4px;
        }
        .kpi-val {
            font-size: 13px;
            font-weight: bold;
            color: #0b5ed7;
        }
        .kpi-lbl {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 4.5px 5px;
            vertical-align: middle;
        }
        .data-table th {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-success { color: #16a34a; }
        .text-primary { color: #0b5ed7; }
        .text-danger { color: #dc2626; }
        .text-muted { color: #64748b; }
        .badge-status {
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
        }
        .sign-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }
        .sign-table td {
            vertical-align: top;
            width: 50%;
        }
        .sign-space {
            height: 45px;
        }
    </style>
</head>
<body>

    {{-- ── KOP SURAT RESMI ── --}}
    <table class="kop-table">
        <tr>
            <td class="kop-text">
                <div class="kop-title">PUSAT KEGIATAN BELAJAR MASYARAKAT (PKBM) PIKAT</div>
                <div class="kop-sub">SISTEM PRESENSI &amp; MANAJEMEN AKADEMIK TERPADU</div>
                <div class="kop-contact">
                    Jl. Cikutra No. 201 / Pasir Impun No. 22, Bandung &bull; NPSN: P9996916 &bull; Kontak: 0851-5645-2939 &bull; Email: admin@pkbmpikat.sch.id
                </div>
            </td>
        </tr>
    </table>

    {{-- ── JUDUL LAPORAN ── --}}
    <div class="report-title-box">
        @php
            $judul = match($tab) {
                'murid', 'siswa' => 'LAPORAN REKAPITULASI PRESENSI MANDIRI MURID',
                'pegawai' => 'LAPORAN PRESENSI PEGAWAI, STAF & PIMPINAN',
                'magang' => 'LAPORAN PRESENSI MAHASISWA MAGANG & SISWA PKL',
                'perizinan' => 'LAPORAN PERIZINAN, SAKIT & DISPENSASI LUPA LAPOR',
                'matriks' => 'BUKU INDUK LEDGER PRESENSI BULANAN MURID',
                default => 'LAPORAN REKAPITULASI PRESENSI TUTOR & KBM'
            };
        @endphp
        <div class="report-title">{{ $judul }}</div>
        <div class="report-period">
            @if($tab === 'matriks')
                Bulan: <b>{{ \Carbon\Carbon::create(null, $bulan, 1)->translatedFormat('F') }} {{ $tahun }}</b>
            @else
                Periode: <b>{{ $startDate->translatedFormat('d F Y') }}</b> s/d <b>{{ $endDate->translatedFormat('d F Y') }}</b>
            @endif
        </div>
    </div>

    {{-- ── SUMMARY KPI STRIP ── --}}
    @if($tab === 'tutor')
        <table class="kpi-row">
            <tr>
                <td class="kpi-box" style="width: 20%;">
                    <div class="kpi-val">{{ $tutorData['totalSesi'] }}</div>
                    <div class="kpi-lbl">Total Sesi KBM</div>
                </td>
                <td class="kpi-box" style="width: 20%;">
                    <div class="kpi-val text-success">{{ $tutorData['totalHadir'] }}</div>
                    <div class="kpi-lbl">Sesi Hadir Selesai</div>
                </td>
                <td class="kpi-box" style="width: 20%;">
                    <div class="kpi-val">{{ $tutorData['totalJamKbm'] }} Jam</div>
                    <div class="kpi-lbl">Durasi Efektif KBM</div>
                </td>
                <td class="kpi-box" style="width: 20%;">
                    <div class="kpi-val text-primary">Rp {{ number_format($tutorData['totalHonorEstimasi'], 0, ',', '.') }}</div>
                    <div class="kpi-lbl">Estimasi Honor SK</div>
                </td>
                <td class="kpi-box" style="width: 20%;">
                    <div class="kpi-val text-danger">{{ $tutorData['totalIzin'] + $tutorData['totalAlpha'] }}</div>
                    <div class="kpi-lbl">Izin / Alpha</div>
                </td>
            </tr>
        </table>
    @elseif($tab === 'murid' || $tab === 'siswa')
        @php $mData = $muridData ?? $siswaData; @endphp
        <table class="kpi-row">
            <tr>
                <td class="kpi-box" style="width: 25%;">
                    <div class="kpi-val">{{ $mData['totalPresensi'] }}</div>
                    <div class="kpi-lbl">Total Check-In</div>
                </td>
                <td class="kpi-box" style="width: 25%;">
                    <div class="kpi-val text-success">{{ $mData['totalHadir'] }}</div>
                    <div class="kpi-lbl">Tepat Waktu</div>
                </td>
                <td class="kpi-box" style="width: 25%;">
                    <div class="kpi-val" style="color:#f59e0b;">{{ $mData['totalTerlambat'] }}</div>
                    <div class="kpi-lbl">Terlambat</div>
                </td>
                <td class="kpi-box" style="width: 25%;">
                    <div class="kpi-val text-danger">{{ $mData['totalIzin'] + $mData['totalSakit'] + $mData['totalAlpha'] }}</div>
                    <div class="kpi-lbl">Izin/Sakit/Alpha</div>
                </td>
            </tr>
        </table>
    @endif

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- ── KONTEN TABEL SESUAI TAB ── --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}

    @if($tab === 'tutor')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 22px;">No</th>
                    <th style="width: 60px;">Tanggal</th>
                    <th>Nama Tutor</th>
                    <th>Nama Murid &amp; Kelas</th>
                    <th style="width: 80px;">Moda Belajar</th>
                    <th style="width: 65px;">Jam Masuk/Pulang</th>
                    <th style="width: 45px;">Durasi</th>
                    <th style="width: 70px;">Honor SK</th>
                    <th>Lokasi Titik Presensi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tutorData['records'] as $idx => $p)
                    @php
                        $durasiMenit = ($p->jam_mulai && $p->jam_selesai) ? \Carbon\Carbon::parse($p->jam_mulai)->diffInMinutes(\Carbon\Carbon::parse($p->jam_selesai)) : 0;
                        $muridObj = $p->murid ?? $p->siswa;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-center font-bold">{{ \Carbon\Carbon::parse($p->tgl_presensi)->format('d/m/Y') }}</td>
                        <td class="font-bold">{{ $p->tutor->nama_lengkap ?? '-' }}</td>
                        <td>
                            {{ $muridObj->nama_murid ?? ($muridObj->nama_siswa ?? '-') }} 
                            <span class="text-muted">({{ $muridObj->relKelas->nama_kelas ?? '-' }})</span>
                        </td>
                        <td class="text-center">{{ $p->moda_label ?? 'Tatap Muka' }}</td>
                        <td class="text-center font-bold">
                            <span class="text-success">{{ $p->jam_mulai ? \Carbon\Carbon::parse($p->jam_mulai)->format('H:i') : '-' }}</span> - 
                            <span class="text-primary">{{ $p->jam_selesai ? \Carbon\Carbon::parse($p->jam_selesai)->format('H:i') : '-' }}</span>
                        </td>
                        <td class="text-center">{{ $durasiMenit > 0 ? round($durasiMenit/60, 1).' Jam' : '-' }}</td>
                        <td class="text-right font-bold">
                            {{ $p->nominal_honor_snapshot ? 'Rp '.number_format($p->nominal_honor_snapshot, 0, ',', '.') : '-' }}
                        </td>
                        <td>{{ $p->lokasiPresensi?->nama_lokasi ?? 'Pusat PKBM' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted">Tidak ada data presensi tutor.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($tab === 'murid' || $tab === 'siswa')
        @php $mData = $muridData ?? $siswaData; @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 22px;">No</th>
                    <th style="width: 65px;">Tanggal</th>
                    <th>Nama Murid</th>
                    <th>Kelas / Jenjang</th>
                    <th style="width: 60px;">Jam Masuk</th>
                    <th style="width: 70px;">Status</th>
                    <th>Titik Lokasi Presensi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mData['records'] as $idx => $ps)
                    @php $m = $ps->murid ?? $ps->siswa; @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-center font-bold">{{ \Carbon\Carbon::parse($ps->tgl_presensi)->format('d/m/Y') }}</td>
                        <td class="font-bold">{{ $m->nama_murid ?? ($m->nama_siswa ?? '-') }}</td>
                        <td>{{ $m->relKelas->nama_kelas ?? '-' }} ({{ $m->jenjang_paket_label ?? '-' }})</td>
                        <td class="text-center font-bold text-success">{{ $ps->jam_masuk ? \Carbon\Carbon::parse($ps->jam_masuk)->format('H:i') : '-' }}</td>
                        <td class="text-center">{{ $ps->status_label }}</td>
                        <td>{{ $ps->lokasiPresensi?->nama_lokasi ?? 'Sekolah PKBM' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Tidak ada data presensi murid.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($tab === 'pegawai')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 22px;">No</th>
                    <th style="width: 65px;">Tanggal</th>
                    <th>Nama Pegawai</th>
                    <th style="width: 80px;">Role / Jabatan</th>
                    <th style="width: 65px;">Jam Masuk</th>
                    <th style="width: 65px;">Jam Pulang</th>
                    <th>Lokasi Presensi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pegawaiData['records'] as $idx => $kp)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-center font-bold">{{ \Carbon\Carbon::parse($kp->tgl_presensi)->format('d/m/Y') }}</td>
                        <td class="font-bold">{{ $kp->user->nama_lengkap ?? '-' }}</td>
                        <td class="text-center font-bold">{{ ucfirst($kp->user->role ?? '-') }}</td>
                        <td class="text-center text-success font-bold">{{ $kp->jam_mulai ? \Carbon\Carbon::parse($kp->jam_mulai)->format('H:i') : '-' }}</td>
                        <td class="text-center text-primary font-bold">{{ $kp->jam_selesai ? \Carbon\Carbon::parse($kp->jam_selesai)->format('H:i') : '-' }}</td>
                        <td>{{ $kp->lokasiPresensi?->nama_lokasi ?? 'Kantor PKBM' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Tidak ada data presensi pegawai.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($tab === 'magang')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 22px;">No</th>
                    <th style="width: 65px;">Tanggal</th>
                    <th>Nama Mahasiswa</th>
                    <th>Asal Instansi</th>
                    <th style="width: 60px;">Masuk</th>
                    <th style="width: 60px;">Pulang</th>
                    <th style="width: 50px;">Durasi</th>
                    <th>Lokasi Presensi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($magangData['records'] as $idx => $mp)
                    @php
                        $durasiMenit = ($mp->jam_mulai && $mp->jam_selesai) ? \Carbon\Carbon::parse($mp->jam_mulai)->diffInMinutes(\Carbon\Carbon::parse($mp->jam_selesai)) : 0;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-center font-bold">{{ \Carbon\Carbon::parse($mp->tgl_presensi)->format('d/m/Y') }}</td>
                        <td class="font-bold">{{ $mp->user->nama_lengkap ?? '-' }}</td>
                        <td>{{ $mp->user->magang?->asal_instansi ?? 'PKL PKBM' }}</td>
                        <td class="text-center text-success font-bold">{{ $mp->jam_mulai ? \Carbon\Carbon::parse($mp->jam_mulai)->format('H:i') : '-' }}</td>
                        <td class="text-center text-primary font-bold">{{ $mp->jam_selesai ? \Carbon\Carbon::parse($mp->jam_selesai)->format('H:i') : '-' }}</td>
                        <td class="text-center">{{ $durasiMenit > 0 ? round($durasiMenit/60, 1).' Jam' : '-' }}</td>
                        <td>{{ $mp->lokasiPresensi?->nama_lokasi ?? 'Gedung PKBM' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">Tidak ada data presensi mahasiswa magang.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($tab === 'matriks')
        <table class="data-table" style="font-size: 7.5px;">
            <thead>
                <tr>
                    <th style="width: 15px;">No</th>
                    <th style="width: 90px;">Nama Murid</th>
                    <th style="width: 60px;">Kelas</th>
                    @for($d = 1; $d <= $matriksData['daysInMonth']; $d++)
                        <th style="width: 12px; padding: 2px 1px;" class="text-center">{{ $d }}</th>
                    @endfor
                    <th style="width: 14px;">H</th>
                    <th style="width: 14px;">I</th>
                    <th style="width: 14px;">S</th>
                    <th style="width: 14px;">A</th>
                    <th style="width: 28px;">%</th>
                </tr>
            </thead>
            <tbody>
                @forelse($matriksData['matrix'] as $idx => $row)
                    @php $rowMurid = $row['murid'] ?? $row['siswa']; @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="font-bold">{{ $rowMurid->nama_murid ?? ($rowMurid->nama_siswa ?? '-') }}</td>
                        <td>{{ $rowMurid->relKelas->nama_kelas ?? '-' }}</td>
                        @foreach($row['days'] as $dayInfo)
                            <td class="text-center" style="padding: 2px 1px; font-weight: bold;">
                                {{ $dayInfo['status'] }}
                            </td>
                        @endforeach
                        <td class="text-center font-bold text-success">{{ $row['hadir'] }}</td>
                        <td class="text-center font-bold">{{ $row['izin'] }}</td>
                        <td class="text-center font-bold">{{ $row['sakit'] }}</td>
                        <td class="text-center font-bold text-danger">{{ $row['alpha'] }}</td>
                        <td class="text-center font-bold">{{ $row['keaktifan'] }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ 3 + $matriksData['daysInMonth'] + 5 }}" class="text-center text-muted">Tidak ada data murid.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

    {{-- ── LEMBAR PENGESAHAN & TANDA TANGAN ── --}}
    <table class="sign-table">
        <tr>
            <td class="text-left" style="padding-left: 20px;">
                <div>Mengetahui,</div>
                <div class="font-bold">Pengelola Data Presensi &amp; Akademik</div>
                <div class="sign-space"></div>
                <div class="font-bold">{{ auth()->user()->nama_lengkap ?? 'Administrator' }}</div>
                <div class="text-muted">{{ auth()->user()->nik ? 'NIK/NIP: ' . auth()->user()->nik : 'Petugas Administrator' }}</div>
            </td>
            <td class="text-right" style="padding-right: 20px;">
                <div>Bandung, {{ $tanggalCetak->translatedFormat('d F Y') }}</div>
                <div class="font-bold">Kepala Sekolah PKBM Pikat</div>
                <div class="sign-space"></div>
                <div class="font-bold">{{ \App\Models\KepalaSekolah::first()?->nama_lengkap ?? (\App\Models\User::where('role', 'kepala_sekolah')->first()?->nama_lengkap ?? 'Kepala Sekolah PKBM Pikat') }}</div>
                <div class="text-muted">{{ \App\Models\KepalaSekolah::first()?->nik ? 'NIK/NIP: ' . \App\Models\KepalaSekolah::first()?->nik : 'Kepala Sekolah' }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
