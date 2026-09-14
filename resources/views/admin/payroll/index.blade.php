@extends(auth()->user()->role === 'kepala_sekolah' ? 'layouts.kepsek' : 'layouts.admin')

@php
    $rolePrefix = auth()->user()->role === 'kepala_sekolah' ? 'kepsek' : 'admin';
@endphp

@section('title', 'Rekapitulasi Payroll & Honorarium — ' . ($rolePrefix === 'kepsek' ? 'Kepala Sekolah' : 'Admin'))

@section('content')
<div class="payroll-container">
    {{-- ── Page Header ── --}}
    <div class="payroll-header">
        <div class="payroll-title-box">
            <span style="font-size:11px;font-weight:800;letter-spacing:1px;color:#059669;text-transform:uppercase;">Laporan Keuangan &amp; Payroll</span>
            <h2>Rekapitulasi Penggajian &amp; Honorarium Tutor</h2>
            <p>Kalkulasi jam mengajar terverifikasi, tarif per jam per siswa, dan total anggaran honorarium lembaga</p>
        </div>

        <div class="payroll-actions">
            <a href="{{ $rolePrefix === 'admin' ? route('admin.laporan.index') : route('kepsek.laporan', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btnOutline" style="padding:9px 14px;display:inline-flex;align-items:center;gap:6px;font-size:12px;text-decoration:none;font-weight:700;">
                <ion-icon name="bar-chart-outline" style="font-size:16px;color:#0284c7;"></ion-icon> Log Presensi KBM
            </a>
            <a href="{{ route($rolePrefix . '.payroll.rekap-excel', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btnPrimary" style="padding:9px 14px;background:#16a34a;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                <ion-icon name="document-outline" style="font-size:16px;"></ion-icon> Export Excel
            </a>
            <a href="{{ route($rolePrefix . '.payroll.rekap-pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btnPrimary" style="padding:9px 14px;background:#0284c7;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                <ion-icon name="document-text-outline" style="font-size:16px;"></ion-icon> Export PDF
            </a>
            <button type="button" onclick="document.getElementById('importTarifModal').style.display='flex'" class="btnPrimary" style="padding:9px 14px;background:#f59e0b;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:6px;border:none;cursor:pointer;">
                <ion-icon name="cloud-upload-outline" style="font-size:16px;"></ion-icon> Update Tarif
            </button>
        </div>
    </div>

    {{-- ── Modal Update Massal Tarif Siswa ── --}}
    <div id="importTarifModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:var(--card,#fff);border-radius:20px;max-width:480px;width:100%;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.25);border:1px solid var(--border,#e2e8f0);animation:fadeIn 0.2s ease;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <h3 style="margin:0;font-size:17px;font-weight:800;color:var(--text,#0f172a);">Update Massal Tarif Honor Siswa</h3>
                <button type="button" onclick="document.getElementById('importTarifModal').style.display='none'" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted,#64748b);line-height:1;">&times;</button>
            </div>
            <p style="font-size:13px;color:var(--muted,#64748b);margin-bottom:16px;line-height:1.5;">
                Unduh spreadsheet data tarif siswa saat ini, ubah nominal tarif pada kolom Excel, lalu unggah kembali untuk memperbarui database secara serentak.
            </p>
            <div style="margin-bottom:18px;">
                <a href="{{ route($rolePrefix . '.payroll.download-tarif-template') }}" class="btnOutline" style="display:inline-flex;align-items:center;gap:6px;padding:9px 14px;font-size:12px;font-weight:700;text-decoration:none;">
                    <ion-icon name="download-outline" style="font-size:16px;color:#0284c7;"></ion-icon> Unduh Data &amp; Template Tarif (.xlsx)
                </a>
            </div>
            <form method="POST" action="{{ route($rolePrefix . '.payroll.import-tarif') }}" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:18px;">
                    <label style="display:block;font-size:12px;font-weight:700;margin-bottom:6px;color:var(--text,#0f172a);">Pilih Berkas Spreadsheet (.xlsx / .csv):</label>
                    <input type="file" name="file_excel" accept=".xlsx,.xls,.csv" required style="width:100%;padding:10px;border:1px dashed var(--border,#cbd5e1);border-radius:10px;font-size:13px;background:var(--card-alt,#f8fafc);color:var(--text,#0f172a);">
                </div>
                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <button type="button" onclick="document.getElementById('importTarifModal').style.display='none'" class="btnOutline" style="padding:9px 16px;border-radius:8px;cursor:pointer;">Batal</button>
                    <button type="submit" class="btnPrimary" style="padding:9px 20px;background:linear-gradient(135deg,#059669,#10b981);border:none;border-radius:8px;cursor:pointer;font-weight:700;">Unggah &amp; Perbarui</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Filter Bar ── --}}
    <div class="filter-card">
        <form method="GET" action="{{ route($rolePrefix . '.payroll.index') }}" class="filter-form">
            <div class="filter-group">
                <label class="filter-label">Bulan Penggajian:</label>
                <select name="bulan" class="filter-select" onchange="this.form.submit()">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Tahun Anggaran:</label>
                <select name="tahun" class="filter-select" onchange="this.form.submit()">
                    @foreach($tahunOptions as $y)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="btnOutline" style="padding:9px 16px;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;height:38px;">
                    <ion-icon name="funnel-outline" style="vertical-align:middle;margin-right:3px;"></ion-icon> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    {{-- ── KPI Summary Cards ── --}}
    <div class="kpi-grid">
        <div class="kpi-card emerald">
            <div class="kpi-top">
                <div class="kpi-label">Total Anggaran Honorarium</div>
                <div class="kpi-icon-wrap">
                    <ion-icon name="wallet"></ion-icon>
                </div>
            </div>
            <div>
                <div class="kpi-val">{{ $summary['formatted_total_anggaran'] }}</div>
                <div class="kpi-sub">Periode {{ $summary['periode_label'] }}</div>
            </div>
        </div>

        <div class="kpi-card blue">
            <div class="kpi-top">
                <div class="kpi-label">Total Jam Mengajar</div>
                <div class="kpi-icon-wrap">
                    <ion-icon name="time"></ion-icon>
                </div>
            </div>
            <div>
                <div class="kpi-val">{{ $summary['total_jam_global'] }} Jam</div>
                <div class="kpi-sub">{{ $summary['total_sesi_global'] }} Total Sesi Terverifikasi</div>
            </div>
        </div>

        <div class="kpi-card amber">
            <div class="kpi-top">
                <div class="kpi-label">Tutor Penerima Honor</div>
                <div class="kpi-icon-wrap">
                    <ion-icon name="people"></ion-icon>
                </div>
            </div>
            <div>
                <div class="kpi-val">{{ $summary['total_tutor'] }} Tutor</div>
                <div class="kpi-sub">Terdaftar Aktif di PKBM Pikat</div>
            </div>
        </div>
    </div>

    {{-- ── Table & Card Container ── --}}
    <div class="table-container">
        <div class="table-header-bar">
            <div class="table-header-title">
                <ion-icon name="list-outline" style="font-size:18px;color:#0284c7;"></ion-icon>
                Daftar Honorarium Tutor — Periode {{ $summary['periode_label'] }}
            </div>
            <div style="font-size:12px;font-weight:700;color:var(--muted,#64748b);">
                Menampilkan {{ count($summary['payrolls']) }} Tutor
            </div>
        </div>

        {{-- Desktop Table --}}
        <div class="table-responsive-desktop" style="overflow-x:auto;">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center;">No</th>
                        <th>Nama Tutor &amp; Identitas</th>
                        <th style="text-align:center;">Sesi Hadir</th>
                        <th style="text-align:center;">Izin / Sakit</th>
                        <th style="text-align:center;">Total Jam</th>
                        <th style="text-align:right;">Total Honorarium</th>
                        <th style="text-align:right;width:140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summary['payrolls'] as $idx => $p)
                    <tr>
                        <td style="text-align:center;color:var(--muted,#64748b);font-weight:700;">{{ $idx + 1 }}</td>
                        <td>
                            <strong style="color:var(--text,#0f172a);display:block;font-size:14px;">{{ $p['tutor']->nama_lengkap }}</strong>
                            <span style="font-size:11px;color:var(--muted,#64748b);">NIK: {{ $p['tutor']->nik }}</span>
                        </td>
                        <td style="text-align:center;">
                            <span class="badge-chip hadir">{{ $p['total_sesi_hadir'] }} sesi</span>
                        </td>
                        <td style="text-align:center;">
                            <span class="badge-chip izin">{{ $p['total_izin_sakit'] }} hari</span>
                        </td>
                        <td style="text-align:center;">
                            @php
                                $startDateMonth = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->toDateString();
                                $endDateMonth = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();
                                $logPresensiUrl = $rolePrefix === 'admin'
                                    ? route('admin.laporan.index', ['tutor_id' => $p['tutor']->id, 'start_date' => $startDateMonth, 'end_date' => $endDateMonth])
                                    : route('kepsek.laporan', ['tutor_id' => $p['tutor']->id, 'bulan' => $bulan, 'tahun' => $tahun]);
                            @endphp
                            <a href="{{ $logPresensiUrl }}" class="badge-chip jam" title="Klik untuk membuka log presensi tutor ini di Laporan">
                                {{ $p['total_jam'] }} Jam <ion-icon name="open-outline"></ion-icon>
                            </a>
                        </td>
                        <td style="text-align:right;font-weight:800;color:#059669;font-size:14px;">
                            {{ $p['formatted_total_honor'] }}
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex;gap:6px;justify-content:flex-end;">
                                <a href="{{ route($rolePrefix . '.payroll.show', [$p['tutor']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="smallBtn edit" style="padding:6px 10px;font-size:11px;font-weight:700;display:inline-flex;align-items:center;gap:3px;" title="Lihat rincian per siswa">
                                    <ion-icon name="eye-outline"></ion-icon> Detail
                                </a>
                                <a href="{{ route($rolePrefix . '.payroll.slip-pdf', [$p['tutor']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="smallBtn" style="padding:6px 10px;font-size:11px;font-weight:700;background:#059669;color:#fff;display:inline-flex;align-items:center;gap:3px;" title="Unduh Slip Gaji PDF">
                                    <ion-icon name="download-outline"></ion-icon> Slip
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px 20px;color:var(--muted,#94a3b8);">
                            <ion-icon name="wallet-outline" style="font-size:36px;display:block;margin:0 auto 8px;opacity:0.5;"></ion-icon>
                            Belum ada data presensi/honorarium untuk periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards List --}}
        <div class="mobile-payroll-list">
            @forelse($summary['payrolls'] as $idx => $p)
                @php
                    $startDateMonth = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->toDateString();
                    $endDateMonth = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();
                    $logPresensiUrl = $rolePrefix === 'admin'
                        ? route('admin.laporan.index', ['tutor_id' => $p['tutor']->id, 'start_date' => $startDateMonth, 'end_date' => $endDateMonth])
                        : route('kepsek.laporan', ['tutor_id' => $p['tutor']->id, 'bulan' => $bulan, 'tahun' => $tahun]);
                @endphp
                <div class="payroll-mobile-card">
                    <div class="pmc-header">
                        <div>
                            <h4 class="pmc-name">{{ $p['tutor']->nama_lengkap }}</h4>
                            <div class="pmc-nik">NIK: {{ $p['tutor']->nik }}</div>
                        </div>
                        <span style="font-size:11px;font-weight:700;color:var(--muted,#94a3b8);">#{{ $idx + 1 }}</span>
                    </div>

                    <div class="pmc-stats">
                        <div class="pmc-stat-item">
                            <div class="val" style="color:#059669;">{{ $p['total_sesi_hadir'] }}</div>
                            <div class="lbl">Hadir</div>
                        </div>
                        <div class="pmc-stat-item">
                            <div class="val" style="color:#64748b;">{{ $p['total_izin_sakit'] }}</div>
                            <div class="lbl">Izin/Sakit</div>
                        </div>
                        <div class="pmc-stat-item">
                            <a href="{{ $logPresensiUrl }}" style="text-decoration:none;" title="Lihat log presensi">
                                <div class="val" style="color:#0284c7;display:flex;align-items:center;justify-content:center;gap:2px;">
                                    {{ $p['total_jam'] }} J <ion-icon name="open-outline" style="font-size:10px;"></ion-icon>
                                </div>
                                <div class="lbl" style="color:#0284c7;">Jam</div>
                            </a>
                        </div>
                    </div>

                    <div class="pmc-footer">
                        <div>
                            <div class="pmc-honor-label">Take-Home Pay</div>
                            <div class="pmc-honor-val">{{ $p['formatted_total_honor'] }}</div>
                        </div>
                        <div style="display:flex;gap:6px;">
                            <a href="{{ route($rolePrefix . '.payroll.show', [$p['tutor']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="smallBtn edit" style="padding:6px 12px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:4px;">
                                <ion-icon name="eye-outline"></ion-icon> Rincian
                            </a>
                            <a href="{{ route($rolePrefix . '.payroll.slip-pdf', [$p['tutor']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="smallBtn" style="padding:6px 12px;font-size:12px;font-weight:700;background:#059669;color:#fff;display:inline-flex;align-items:center;gap:4px;">
                                <ion-icon name="download-outline"></ion-icon> PDF
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:32px 16px;color:var(--muted,#94a3b8);">
                    <ion-icon name="wallet-outline" style="font-size:36px;display:block;margin:0 auto 8px;opacity:0.5;"></ion-icon>
                    Belum ada data honorarium untuk periode ini.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
