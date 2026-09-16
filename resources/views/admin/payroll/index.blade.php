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
            <span class="text-xs font-extrabold text-success text-uppercase letter-spacing-sm">Laporan Keuangan &amp; Payroll</span>
            <h2 >Rekapitulasi Penggajian &amp; Honorarium Tutor</h2>
            <p >Kalkulasi honorarium mengajar terverifikasi berbasis Master Tarif SK, durasi sesi KBM, dan anggaran bulanan lembaga.</p>
        </div>

        <div class="payroll-actions">
            <a href="{{ $rolePrefix === 'admin' ? route('admin.laporan.index') : route('kepsek.laporan', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btnOutline" class="btn-emerald-sm">
                <ion-icon name="bar-chart-outline" class="text-primary"></ion-icon> Log Presensi KBM
            </a>
            <a href="{{ route($rolePrefix . '.payroll.rekap-excel', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btnPrimary" class="btn-emerald-sm bg-success">
                <ion-icon name="document-outline" class="icon-sm"></ion-icon> Export Excel
            </a>
            <a href="{{ route($rolePrefix . '.payroll.rekap-pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btnPrimary" class="btn-emerald-sm bg-primary">
                <ion-icon name="document-text-outline" class="icon-sm"></ion-icon> Export PDF
            </a>
            @if($rolePrefix === 'admin')
            <a href="{{ route('admin.kategori-tutorial.index') }}" class="btnPrimary text-sm font-bold d-inline-flex items-center gap-1 btn-emerald-sm bg-purple">
                <ion-icon name="options-outline" class="icon-sm"></ion-icon> Master Tarif SK
            </a>
            @endif
            @if($rolePrefix === 'admin')
            <form method="POST" action="{{ route('admin.payroll.broadcast-notifikasi') }}" data-confirm="Kirim notifikasi pengumuman slip gaji periode ini ke seluruh perangkat tutor yang terdaftar?" data-confirm-title="Broadcast Slip Gaji" data-confirm-type="info" data-confirm-btn="Ya, Kirim Notifikasi" class="d-inline">
                @csrf
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                <button type="submit" class="btnPrimary text-sm font-bold d-inline-flex items-center gap-1 border-none cursor-pointer btn-emerald-sm bg-purple-light">
                    <ion-icon name="notifications-outline" class="icon-sm"></ion-icon> Umumkan ke Tutor
                </button>
            </form>
            @endif
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

            <div >
                <button type="submit" class="btnOutline rounded-md font-bold text-md cursor-pointer px-3 py-2 h-9">
                    <ion-icon name="funnel-outline" class="align-middle mr-1"></ion-icon> Terapkan Filter
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
            <div >
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
            <div >
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
            <div >
                <div class="kpi-val">{{ $summary['total_tutor'] }} Tutor</div>
                <div class="kpi-sub">Terdaftar Aktif di PKBM Pikat</div>
            </div>
        </div>
    </div>

    {{-- ── Table & Card Container ── --}}
    <div class="table-container">
        <div class="table-header-bar">
            <div class="table-header-title">
                <ion-icon name="list-outline" class="text-primary"></ion-icon>
                Daftar Honorarium Tutor — Periode {{ $summary['periode_label'] }}
            </div>
            <div class="text-sm font-bold text-muted">
                Menampilkan {{ count($summary['payrolls']) }} Tutor
            </div>
        </div>

        {{-- Desktop Table --}}
        <div class="table-responsive-desktop overflow-x-auto">
            <table class="modern-table">
                <thead >
                    <tr >
                        <th  class="text-center w-10">No</th>
                        <th >Nama Tutor &amp; Identitas</th>
                        <th class="text-center">Sesi Hadir</th>
                        <th class="text-center">Izin / Sakit</th>
                        <th class="text-center">Total Jam</th>
                        <th class="text-right">Total Honorarium</th>
                        <th  class="text-right min-w-140">Aksi</th>
                    </tr>
                </thead>
                <tbody >
                    @forelse($summary['payrolls'] as $idx => $p)
                    <tr >
                        <td class="text-center text-muted font-bold">{{ $idx + 1 }}</td>
                        <td >
                            <strong class="text-dark d-block text-md">{{ $p['tutor']->nama_lengkap }}</strong>
                            <span class="text-xs text-muted">NIK: {{ $p['tutor']->nik }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge-chip hadir">{{ $p['total_sesi_hadir'] }} sesi</span>
                        </td>
                        <td class="text-center">
                            <span class="badge-chip izin">{{ $p['total_izin_sakit'] }} hari</span>
                        </td>
                        <td class="text-center">
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
                        <td class="text-right font-extrabold text-success text-md">
                            {{ $p['formatted_total_honor'] }}
                        </td>
                        <td class="text-right">
                            <div class="d-flex gap-1 justify-end">
                                <a href="{{ route($rolePrefix . '.payroll.show', [$p['tutor']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="smallBtn edit" class="px-2 py-1 text-xs font-bold d-inline-flex items-center gap-1" title="Lihat rincian per siswa">
                                    <ion-icon name="eye-outline"></ion-icon> Detail
                                </a>
                                <a href="{{ route($rolePrefix . '.payroll.slip-pdf', [$p['tutor']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="smallBtn" class="btn-emerald-sm px-2 py-1 text-xs" title="Unduh Slip Gaji PDF">
                                    <ion-icon name="download-outline"></ion-icon> Slip
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr >
                        <td colspan="7" class="text-center text-muted p-4">
                            <ion-icon name="wallet-outline" class="icon-2xl d-block mx-auto mb-2 opacity-50"></ion-icon>
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
                        <div >
                            <h4 class="pmc-name">{{ $p['tutor']->nama_lengkap }}</h4>
                            <div class="pmc-nik">NIK: {{ $p['tutor']->nik }}</div>
                        </div>
                        <span class="text-xs font-bold text-muted">#{{ $idx + 1 }}</span>
                    </div>

                    <div class="pmc-stats">
                        <div class="pmc-stat-item">
                            <div class="val text-success">{{ $p['total_sesi_hadir'] }}</div>
                            <div class="lbl">Hadir</div>
                        </div>
                        <div class="pmc-stat-item">
                            <div class="val text-muted">{{ $p['total_izin_sakit'] }}</div>
                            <div class="lbl">Izin/Sakit</div>
                        </div>
                        <div class="pmc-stat-item">
                            <a href="{{ $logPresensiUrl }}" class="text-no-decor" title="Lihat log presensi">
                                <div class="val d-flex items-center justify-center gap-1 text-primary">
                                    {{ $p['total_jam'] }} J <ion-icon name="open-outline" class="text-xs"></ion-icon>
                                </div>
                                <div class="lbl text-primary">Jam</div>
                            </a>
                        </div>
                    </div>

                    <div class="pmc-footer">
                        <div >
                            <div class="pmc-honor-label">Take-Home Pay</div>
                            <div class="pmc-honor-val">{{ $p['formatted_total_honor'] }}</div>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="{{ route($rolePrefix . '.payroll.show', [$p['tutor']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="smallBtn edit" class="px-3 py-1 text-sm font-bold d-inline-flex items-center gap-1">
                                <ion-icon name="eye-outline"></ion-icon> Rincian
                            </a>
                            <a href="{{ route($rolePrefix . '.payroll.slip-pdf', [$p['tutor']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="smallBtn" class="btn-emerald-sm">
                                <ion-icon name="download-outline"></ion-icon> PDF
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center table-empty-cell text-muted">
                    <ion-icon name="wallet-outline" class="icon-2xl d-block mx-auto mb-2 opacity-50"></ion-icon>
                    Belum ada data honorarium untuk periode ini.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
