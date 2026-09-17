@extends(auth()->user()->role === 'kepala_sekolah' ? 'layouts.kepsek' : 'layouts.admin')

@php
    $rolePrefix = auth()->user()->role === 'kepala_sekolah' ? 'kepsek' : 'admin';
    $startDateMonth = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->toDateString();
    $endDateMonth = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();
    $logPresensiUrl = $rolePrefix === 'admin'
        ? route('admin.laporan.index', ['tutor_id' => $tutor->id, 'start_date' => $startDateMonth, 'end_date' => $endDateMonth])
        : route('kepsek.laporan', ['tutor_id' => $tutor->id, 'bulan' => $bulan, 'tahun' => $tahun]);
@endphp

@section('title', 'Detail Slip Gaji: ' . $tutor->nama_lengkap)

@section('content')
<div class="show-payroll-container">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">RINCIAN SLIP GAJI PERSONAL</div>
                <h1 class="laporanHeaderTitle">Detail Slip Gaji: {{ $tutor->nama_lengkap }}</h1>
                <div class="laporanHeaderSub">Periode: {{ strtoupper($payroll['periode_label']) }} • NIK: {{ $tutor->nik }}</div>
                <p class="laporanHeaderDesc">Rincian honorarium mengajar terverifikasi, akumulasi jam KBM, serta pengunduhan berkas slip resmi.</p>
            </div>

            <div class="laporanHeaderActions header-actions-group">
                <a href="{{ route($rolePrefix . '.payroll.index', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btnOutline">
                    <ion-icon name="arrow-back-outline"></ion-icon> Kembali
                </a>
                <a href="{{ $logPresensiUrl }}" class="btnOutline">
                    <ion-icon name="bar-chart-outline" class="text-primary"></ion-icon> Log Presensi KBM
                </a>
                <a href="{{ route($rolePrefix . '.payroll.slip-pdf', [$tutor->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="profileBtnPrimary btn-action-primary" target="_blank">
                    <ion-icon name="download-outline"></ion-icon> Cetak Slip PDF
                </a>
            </div>
        </div>
    </div>

    {{-- ── Summary KPI Cards ── --}}
    <div class="kpi-grid">
        <div class="kpi-card emerald">
            <div class="kpi-label">Total Take Home Pay</div>
            <div class="kpi-val">{{ $payroll['formatted_total_honor'] }}</div>
            <div class="text-sm text-muted mt-1">Honorarium Terakumulasi</div>
        </div>
        <div class="kpi-card blue">
            <div class="kpi-label">Total Jam Mengajar</div>
            <div class="kpi-val">{{ $payroll['total_jam'] }} Jam</div>
            <div class="text-sm text-muted mt-1">Durasi Sesi Tervalidasi</div>
        </div>
        <div class="kpi-card indigo">
            <div class="kpi-label">Sesi Kehadiran Valid</div>
            <div class="kpi-val">{{ $payroll['total_sesi_hadir'] }} Sesi</div>
            <div class="text-sm text-muted mt-1">Izin/Sakit: {{ $payroll['total_izin_sakit'] }} Hari</div>
        </div>
    </div>

    {{-- ── Rincian Per Siswa ── --}}
    <div class="content-box">
        <div class="content-box-header">
            <div class="d-flex items-center gap-2">
                <ion-icon name="school-outline" class="text-primary"></ion-icon>
                Rincian Honorarium Pembelajaran Siswa
            </div>
            <div class="text-sm font-bold text-muted">
                {{ count($payroll['siswa_summary']) }} Siswa Diajar
            </div>
        </div>

        {{-- Desktop View --}}
        <div class="table-responsive-desktop overflow-x-auto">
            <table class="modern-table">
                <thead >
                    <tr >
                        <th >Nama Siswa</th>
                        <th class="text-center">Total Sesi</th>
                        <th class="text-center">Total Jam</th>
                        <th >Program / Layanan</th>
                        <th class="text-right">Subtotal Honor</th>
                    </tr>
                </thead>
                <tbody >
                    @forelse($payroll['siswa_summary'] as $s)
                    <tr >
                        <td >
                            <strong class="text-dark text-md">{{ $s['nama_siswa'] }}</strong>
                        </td>
                        <td class="text-center">{{ $s['total_sesi'] }} kali</td>
                        <td class="text-center font-bold text-primary">{{ $s['total_jam'] }} Jam</td>
                        <td >
                            <span class="{{ $s['is_abk'] ? 'badge-abk' : 'badge-reguler' }} text-xs">
                                {{ $s['is_abk_label'] }}
                            </span>
                        </td>
                        <td class="text-right font-extrabold text-success text-md">{{ $s['formatted_subtotal'] }}</td>
                    </tr>
                    @empty
                    <tr >
                        <td colspan="5" class="text-center p-4 text-muted">Belum ada rincian mengajar siswa.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="mobile-siswa-cards">
            @forelse($payroll['siswa_summary'] as $s)
                <div class="siswa-card-item">
                    <div class="sci-header">
                        <div class="sci-name">{{ $s['nama_siswa'] }}</div>
                        <div class="sci-subtotal">{{ $s['formatted_subtotal'] }}</div>
                    </div>
                    <div class="sci-meta">
                        <div ><strong >{{ $s['total_sesi'] }}</strong> Sesi</div>
                        <div >•</div>
                        <div class="text-primary"><strong >{{ $s['total_jam'] }}</strong> Jam</div>
                        <div >•</div>
                        <div >{{ $s['is_abk_label'] }}</div>
                    </div>
                </div>
            @empty
                <div class="text-center p-4 text-muted">Belum ada rincian mengajar siswa.</div>
            @endforelse
        </div>
    </div>

    {{-- ── Log Rincian Sesi Mengajar ── --}}
    <div class="content-box">
        <div class="content-box-header">
            <div class="d-flex items-center gap-2">
                <ion-icon name="time-outline" class="text-success"></ion-icon>
                Log Sesi Kehadiran Terverifikasi (KBM)
            </div>
            <div class="text-sm font-bold text-muted">
                {{ count($payroll['session_rows']) }} Sesi Masuk
            </div>
        </div>

        {{-- Desktop View --}}
        <div class="table-responsive-desktop overflow-x-auto">
            <table class="modern-table">
                <thead >
                    <tr >
                        <th >Tanggal</th>
                        <th >Siswa</th>
                        <th >Waktu Mengajar</th>
                        <th class="text-center">Durasi</th>
                        <th >Moda Belajar</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody >
                    @forelse($payroll['session_rows'] as $row)
                    <tr >
                        <td  class="font-semibold white-space-nowrap text-dark">
                            {{ \Carbon\Carbon::parse($row['tgl_presensi'])->translatedFormat('d M Y') }}
                        </td>
                        <td >
                            <strong class="text-dark">{{ $row['nama_siswa'] }}</strong>
                        </td>
                        <td class="text-muted text-sm white-space-nowrap">
                            {{ $row['jam_mulai'] }} s/d {{ $row['jam_selesai'] }}
                        </td>
                        <td class="text-center font-bold text-primary">
                            {{ $row['durasi_jam'] }} Jam
                        </td>
                        <td >
                            <span  class="text-xs rounded-sm font-bold badge-code">
                                {{ $row['moda_label'] }}
                            </span>
                        </td>
                        <td class="text-right font-extrabold text-success">
                            {{ $row['formatted_subtotal'] }}
                        </td>
                    </tr>
                    @empty
                    <tr >
                        <td colspan="6" class="text-center p-4 text-muted">Belum ada log sesi mengajar terverifikasi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Session Cards --}}
        <div class="mobile-session-cards">
            @forelse($payroll['session_rows'] as $row)
                <div class="session-card-item">
                    <div class="ses-header">
                        <div>
                            <div class="ses-date">{{ \Carbon\Carbon::parse($row['tgl_presensi'])->translatedFormat('d M Y') }}</div>
                            <div class="font-bold text-dark text-sm mt-1">{{ $row['nama_siswa'] }}</div>
                        </div>
                        <div class="ses-subtotal">{{ $row['formatted_subtotal'] }}</div>
                    </div>
                    <div class="d-flex items-center justify-between text-xs text-muted mt-2 pt-2 border-t-base">
                        <div>{{ $row['jam_mulai'] }} - {{ $row['jam_selesai'] }} ({{ $row['durasi_jam'] }} J)</div>
                        <span class="badge-code text-xs rounded-sm font-bold">{{ $row['moda_label'] }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center p-4 text-muted">Belum ada log sesi mengajar terverifikasi.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
