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
    {{-- ── Top Navigation & Header ── --}}
    <div>
        <a href="{{ route($rolePrefix . '.payroll.index', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="back-nav-link">
            <ion-icon name="arrow-back-outline"></ion-icon> Kembali ke Rekapitulasi Payroll
        </a>
    </div>

    <div class="show-header-row">
        <div class="show-title-box">
            <span style="font-size:11px;font-weight:800;letter-spacing:1px;color:#059669;text-transform:uppercase;">Rincian Slip Gaji Personal</span>
            <h2>Detail Slip Gaji: {{ $tutor->nama_lengkap }}</h2>
            <p>Periode {{ $payroll['periode_label'] }} • NIK: {{ $tutor->nik }}</p>
        </div>

        <div class="show-header-actions">
            <a href="{{ $logPresensiUrl }}" class="btnOutline" style="padding:9px 14px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:700;">
                <ion-icon name="bar-chart-outline" style="font-size:16px;color:#0284c7;"></ion-icon> Buka Log Presensi KBM
            </a>
            <a href="{{ route($rolePrefix . '.payroll.slip-pdf', [$tutor->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btnPrimary" style="padding:9px 16px;background:linear-gradient(135deg,#059669,#10b981);font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:6px;border:none;">
                <ion-icon name="download-outline" style="font-size:16px;"></ion-icon> Cetak Slip Gaji PDF
            </a>
        </div>
    </div>

    {{-- ── Summary KPI Cards ── --}}
    <div class="kpi-grid">
        <div class="kpi-card emerald">
            <div class="kpi-label">Total Take Home Pay</div>
            <div class="kpi-val">{{ $payroll['formatted_total_honor'] }}</div>
            <div style="font-size:12px;color:var(--muted,#64748b);margin-top:4px;">Honorarium Terakumulasi</div>
        </div>
        <div class="kpi-card blue">
            <div class="kpi-label">Total Jam Mengajar</div>
            <div class="kpi-val">{{ $payroll['total_jam'] }} Jam</div>
            <div style="font-size:12px;color:var(--muted,#64748b);margin-top:4px;">Durasi Sesi Tervalidasi</div>
        </div>
        <div class="kpi-card indigo">
            <div class="kpi-label">Sesi Kehadiran Valid</div>
            <div class="kpi-val">{{ $payroll['total_sesi_hadir'] }} Sesi</div>
            <div style="font-size:12px;color:var(--muted,#64748b);margin-top:4px;">Izin/Sakit: {{ $payroll['total_izin_sakit'] }} Hari</div>
        </div>
    </div>

    {{-- ── Rincian Per Siswa ── --}}
    <div class="content-box">
        <div class="content-box-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <ion-icon name="school-outline" style="font-size:18px;color:#0284c7;"></ion-icon>
                Rincian Honorarium Pembelajaran Siswa
            </div>
            <div style="font-size:12px;font-weight:700;color:var(--muted,#64748b);">
                {{ count($payroll['siswa_summary']) }} Siswa Diajar
            </div>
        </div>

        {{-- Desktop View --}}
        <div class="table-responsive-desktop" style="overflow-x:auto;">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Nama Siswa</th>
                        <th style="text-align:center;">Total Sesi</th>
                        <th style="text-align:center;">Total Jam</th>
                        <th>Program / Layanan</th>
                        <th style="text-align:right;">Subtotal Honor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payroll['siswa_summary'] as $s)
                    <tr>
                        <td>
                            <strong style="color:var(--text,#0f172a);font-size:14px;">{{ $s['nama_siswa'] }}</strong>
                        </td>
                        <td style="text-align:center;">{{ $s['total_sesi'] }} kali</td>
                        <td style="text-align:center;color:#0284c7;font-weight:700;">{{ $s['total_jam'] }} Jam</td>
                        <td>
                            <span style="font-size:11px;font-weight:800;padding:4px 8px;border-radius:6px;background:{{ $s['is_abk'] ? 'rgba(245,158,11,0.12)' : 'rgba(11,94,215,0.1)' }};color:{{ $s['is_abk'] ? '#d97706' : '#0284c7' }};">
                                {{ $s['is_abk_label'] }}
                            </span>
                        </td>
                        <td style="text-align:right;font-weight:800;color:#059669;font-size:14px;">{{ $s['formatted_subtotal'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:30px;color:var(--muted,#94a3b8);">Belum ada rincian mengajar siswa.</td>
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
                        <div><strong>{{ $s['total_sesi'] }}</strong> Sesi</div>
                        <div>•</div>
                        <div style="color:#0284c7;"><strong>{{ $s['total_jam'] }}</strong> Jam</div>
                        <div>•</div>
                        <div>{{ $s['is_abk_label'] }}</div>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:24px;color:var(--muted,#94a3b8);">Belum ada rincian mengajar siswa.</div>
            @endforelse
        </div>
    </div>

    {{-- ── Log Rincian Sesi Mengajar ── --}}
    <div class="content-box">
        <div class="content-box-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <ion-icon name="time-outline" style="font-size:18px;color:#059669;"></ion-icon>
                Log Sesi Kehadiran Terverifikasi (KBM)
            </div>
            <div style="font-size:12px;font-weight:700;color:var(--muted,#64748b);">
                {{ count($payroll['session_rows']) }} Sesi Masuk
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Waktu Mengajar</th>
                        <th style="text-align:center;">Durasi</th>
                        <th>Moda Belajar</th>
                        <th style="text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payroll['session_rows'] as $row)
                    <tr>
                        <td style="color:var(--text,#1e293b);font-weight:600;white-space:nowrap;">
                            {{ \Carbon\Carbon::parse($row['tgl_presensi'])->translatedFormat('d M Y') }}
                        </td>
                        <td>
                            <strong style="color:var(--text,#0f172a);">{{ $row['nama_siswa'] }}</strong>
                        </td>
                        <td style="color:var(--muted,#64748b);font-size:12px;white-space:nowrap;">
                            {{ $row['jam_mulai'] }} s/d {{ $row['jam_selesai'] }}
                        </td>
                        <td style="text-align:center;color:#0284c7;font-weight:700;">
                            {{ $row['durasi_jam'] }} Jam
                        </td>
                        <td>
                            <span style="font-size:11px;padding:3px 8px;border-radius:6px;background:var(--card-alt,#f1f5f9);font-weight:700;color:var(--text,#334155);">
                                {{ $row['moda_label'] }}
                            </span>
                        </td>
                        <td style="text-align:right;font-weight:800;color:#059669;">
                            {{ $row['formatted_subtotal'] }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:30px;color:var(--muted,#94a3b8);">Belum ada log sesi mengajar terverifikasi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
