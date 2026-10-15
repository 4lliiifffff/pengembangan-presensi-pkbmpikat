@extends('layouts.admin')

@section('title', 'Pusat Laporan & Rekapitulasi Presensi Terpadu - Admin')

@section('content')

    <div class="laporanPageWrapper">
        {{-- ── Header Laporan ── --}}
        <div class="laporanHeader">
            <div class="laporanHeaderCard">
                <div class="laporanHeaderInfo">
                    <div class="laporanHeaderLabel">PUSAT REKAPITULASI &amp; AUDIT OPERASIONAL</div>
                    <div class="laporanHeaderTitle">Pusat Laporan Presensi Terpadu PKBM Pikat</div>
                    <div class="laporanHeaderSub">
                        Periode: <b>{{ \Carbon\Carbon::parse($inputStartDate)->translatedFormat('d F Y') }}</b> s/d
                        <b>{{ \Carbon\Carbon::parse($inputEndDate)->translatedFormat('d F Y') }}</b>
                    </div>
                    <p class="laporanHeaderDesc">
                        Monitoring terintegrasi kehadiran 4 entitas: Tutor (KBM &amp; Honor SK), Presensi Mandiri Murid, Pegawai &amp; Manajemen, Mahasiswa Magang (PKL), Log Perizinan, serta Buku Induk Presensi Bulanan.
                    </p>
                </div>
                <div class="laporanHeaderActions">
                    <a href="{{ route('admin.payroll.index') }}" class="btnPayrollShortcut">
                        <ion-icon name="wallet-outline"></ion-icon>
                        <span>Rekap Payroll &amp; SK</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Summary Analytics KPI Cards ── --}}
        <div class="statRow">
            <div class="statCard">
                <div class="statNum green">{{ $tutorData['totalHadir'] }}</div>
                <div class="statLabel">Sesi KBM Tutor Hadir</div>
                <div class="text-xs text-muted mt-1">{{ $tutorData['totalJamKbm'] }} Jam Efektif KBM</div>
            </div>
            <div class="statCard">
                <div class="statNum blue">{{ ($muridData ?? $siswaData)['totalHadir'] }}</div>
                <div class="statLabel">Presensi Murid Hadir</div>
                <div class="text-xs text-muted mt-1">{{ ($muridData ?? $siswaData)['totalTerlambat'] }} Terlambat Masuk</div>
            </div>
            <div class="statCard">
                <div class="statNum purple" style="color: #8b5cf6;">{{ $pegawaiData['totalHadir'] + $magangData['totalHadir'] }}</div>
                <div class="statLabel">Pegawai &amp; Magang Hadir</div>
                <div class="text-xs text-muted mt-1">{{ $pegawaiData['totalPresensi'] }} Staf &bull; {{ $magangData['totalPresensi'] }} PKL</div>
            </div>
            <div class="statCard">
                <div class="statNum red">{{ $perizinanData['totalIzinSakit'] + $perizinanData['totalLupaLapor'] }}</div>
                <div class="statLabel">Izin / Lupa Lapor</div>
                <div class="text-xs text-muted mt-1">{{ $perizinanData['totalPending'] }} Menunggu Approval</div>
            </div>
        </div>

        {{-- ── Visual Analytics Charts ── --}}
        <div class="sectionRow">
            <h2>Analisis &amp; Visualisasi Kehadiran</h2>
        </div>
        <div class="chartContainer laporanChartsGrid">
            {{-- Chart 1: Tren Harian --}}
            <div class="chartCard">
                <div class="chartTitle">Tren Kehadiran Harian Lembaga</div>
                <div class="chartWrapper">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
            {{-- Chart 2: Komposisi Moda Pembelajaran --}}
            <div class="chartCard">
                <div class="chartTitle">Komposisi Moda Belajar KBM</div>
                <div class="chartWrapper" style="position: relative;">
                    <canvas id="modaChart"></canvas>
                </div>
            </div>
        </div>

        {{-- ── Navigasi 6 Tab Multi-Entitas ── --}}
        <div class="laporanNavTabsWrapper">
            <div class="laporanNavTabs">
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'tutor']) }}" class="laporanTabBtn {{ $activeTab === 'tutor' ? 'active' : '' }}">
                    <ion-icon name="school-outline"></ion-icon>
                    <span>Presensi Tutor</span>
                    <span class="tabBadge">{{ $tutorData['totalSesi'] }}</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'murid']) }}" class="laporanTabBtn {{ in_array($activeTab, ['murid', 'siswa']) ? 'active' : '' }}">
                    <ion-icon name="person-outline"></ion-icon>
                    <span>Presensi Mandiri Murid</span>
                    <span class="tabBadge">{{ ($muridData ?? $siswaData)['totalPresensi'] }}</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'pegawai']) }}" class="laporanTabBtn {{ $activeTab === 'pegawai' ? 'active' : '' }}">
                    <ion-icon name="briefcase-outline"></ion-icon>
                    <span>Pegawai &amp; Pimpinan</span>
                    <span class="tabBadge">{{ $pegawaiData['totalPresensi'] }}</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'magang']) }}" class="laporanTabBtn {{ $activeTab === 'magang' ? 'active' : '' }}">
                    <ion-icon name="id-card-outline"></ion-icon>
                    <span>Mahasiswa Magang</span>
                    <span class="tabBadge">{{ $magangData['totalPresensi'] }}</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'perizinan']) }}" class="laporanTabBtn {{ $activeTab === 'perizinan' ? 'active' : '' }}">
                    <ion-icon name="document-text-outline"></ion-icon>
                    <span>Perizinan &amp; Lupa Lapor</span>
                    <span class="tabBadge">{{ $perizinanData['totalIzinSakit'] + $perizinanData['totalLupaLapor'] }}</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'matriks']) }}" class="laporanTabBtn {{ $activeTab === 'matriks' ? 'active' : '' }}">
                    <ion-icon name="grid-outline"></ion-icon>
                    <span>Matriks Buku Induk (1–31)</span>
                    <span class="tabBadge">{{ $matriksData['totalSiswa'] }} Murid</span>
                </a>
            </div>
        </div>

        {{-- ── Filter Card Dinamis ── --}}
        <div class="laporanFilterCard">
            <div class="laporanFilterHeaderMobile" onclick="toggleMobileFilter()">
                <div class="filterMobileSummary">
                    <span class="filterMobileIcon"><ion-icon name="filter-outline"></ion-icon></span>
                    <div class="filterMobileTexts">
                        <span class="filterMobileTitle">Parameter &amp; Filter Laporan</span>
                        <span class="filterMobileSub">
                            @if($activeTab === 'matriks')
                                {{ \Carbon\Carbon::create(null, $inputBulan, 1)->translatedFormat('F') }} {{ $inputTahun }}
                            @else
                                {{ \Carbon\Carbon::parse($inputStartDate)->format('d/m/y') }} s/d {{ \Carbon\Carbon::parse($inputEndDate)->format('d/m/y') }}
                            @endif
                        </span>
                    </div>
                </div>
                <button type="button" class="btnToggleFilterMobile" id="btnToggleFilterMobile" aria-expanded="false">
                    <span id="filterToggleText">Buka Filter</span>
                    <ion-icon name="chevron-down-outline" id="filterToggleIcon"></ion-icon>
                </button>
            </div>
            <form method="GET" action="{{ route('admin.laporan.index') }}" id="laporanFilterForm" class="laporanFilterFormBody">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                
                @if($activeTab === 'matriks')
                    <div class="laporanFilterGrid">
                        <div class="filterField">
                            <label class="filterFieldLabel">Bulan</label>
                            <select name="bulan" class="filterSelect" onchange="document.getElementById('laporanFilterForm').submit()">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $inputBulan == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="filterField">
                            <label class="filterFieldLabel">Tahun</label>
                            <select name="tahun" class="filterSelect" onchange="document.getElementById('laporanFilterForm').submit()">
                                @for ($y = Carbon\Carbon::now('Asia/Jakarta')->year - 2; $y <= Carbon\Carbon::now('Asia/Jakarta')->year + 1; $y++)
                                    <option value="{{ $y }}" {{ $inputTahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="filterField">
                            <label class="filterFieldLabel">Jenjang / Program Paket</label>
                            <select name="jenjang_paket_id" class="filterSelect">
                                <option value="">Semua Jenjang</option>
                                @foreach ($jenjangPakets as $jp)
                                    <option value="{{ $jp->id }}" {{ ($filters['jenjang_paket_id'] ?? '') == $jp->id ? 'selected' : '' }}>
                                        {{ $jp->nama_jenjang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filterField">
                            <label class="filterFieldLabel">Kelas / Rombel</label>
                            <select name="kelas_id" class="filterSelect">
                                <option value="">Semua Kelas</option>
                                @foreach ($kelasList as $k)
                                    <option value="{{ $k->id }}" {{ ($filters['kelas_id'] ?? '') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-actions-full">
                            <button type="submit" class="btn-filter-primary">
                                <ion-icon name="filter-outline"></ion-icon> Terapkan Filter Matriks
                            </button>
                            <button type="submit" formtarget="_blank" formaction="{{ route('admin.laporan.exportPdf') }}" class="btn-filter-danger">
                                <ion-icon name="document-text-outline"></ion-icon> Cetak Ledger PDF
                            </button>
                            <a href="{{ route('admin.laporan.index', ['tab' => 'matriks']) }}" class="btn-filter-reset">Reset</a>
                        </div>
                    </div>
                @else
                    <div class="laporanFilterGrid">
                        <div class="filterField">
                            <label class="filterFieldLabel">Dari Tanggal</label>
                            <input type="date" name="start_date" value="{{ $inputStartDate }}" class="profileInput">
                        </div>

                        <div class="filterField">
                            <label class="filterFieldLabel">Sampai Tanggal</label>
                            <input type="date" name="end_date" value="{{ $inputEndDate }}" class="profileInput">
                        </div>

                        @if($activeTab === 'tutor' || $activeTab === 'perizinan')
                            <div class="filterField">
                                <label class="filterFieldLabel">Tutor</label>
                                <select name="tutor_id" class="filterSelect">
                                    <option value="">Semua Tutor</option>
                                    @foreach ($tutors as $tutor)
                                        <option value="{{ $tutor->id }}" {{ ($filters['tutor_id'] ?? '') == $tutor->id ? 'selected' : '' }}>
                                            {{ $tutor->nama_lengkap }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if($activeTab === 'tutor' || in_array($activeTab, ['murid', 'siswa']))
                            <div class="filterField">
                                <label class="filterFieldLabel">Murid</label>
                                <select name="murid_id" class="filterSelect">
                                    <option value="">Semua Murid</option>
                                    @foreach (($murids ?? $siswas) as $murid)
                                        <option value="{{ $murid->id }}" {{ (($filters['murid_id'] ?? ($filters['siswa_id'] ?? '')) == $murid->id) ? 'selected' : '' }}>
                                            {{ $murid->nama_murid ?? $murid->nama_siswa }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if($activeTab === 'tutor')
                            <div class="filterField">
                                <label class="filterFieldLabel">Moda Pembelajaran</label>
                                <select name="moda_pembelajaran" class="filterSelect">
                                    <option value="">Semua Moda</option>
                                    <option value="sekolah" {{ ($filters['moda_pembelajaran'] ?? '') == 'sekolah' ? 'selected' : '' }}>Tatap Muka di Sekolah</option>
                                    <option value="kunjungan_rumah" {{ ($filters['moda_pembelajaran'] ?? '') == 'kunjungan_rumah' ? 'selected' : '' }}>Kunjungan Rumah (Home Visit)</option>
                                    <option value="online" {{ ($filters['moda_pembelajaran'] ?? '') == 'online' ? 'selected' : '' }}>Distance Learning / Online</option>
                                </select>
                            </div>
                        @endif

                        @if($activeTab === 'pegawai')
                            <div class="filterField">
                                <label class="filterFieldLabel">Peran / Jabatan</label>
                                <select name="role" class="filterSelect">
                                    <option value="">Semua Peran</option>
                                    <option value="admin" {{ ($filters['role'] ?? '') == 'admin' ? 'selected' : '' }}>Administrator</option>
                                    <option value="kepala_sekolah" {{ ($filters['role'] ?? '') == 'kepala_sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                                    <option value="karyawan" {{ ($filters['role'] ?? '') == 'karyawan' ? 'selected' : '' }}>Staf / Karyawan</option>
                                </select>
                            </div>
                        @endif

                        @if($activeTab === 'magang')
                            <div class="filterField">
                                <label class="filterFieldLabel">Peserta Magang</label>
                                <select name="user_id" class="filterSelect">
                                    <option value="">Semua Peserta Magang</option>
                                    @foreach ($magangUsers as $mu)
                                        <option value="{{ $mu->id }}" {{ ($filters['user_id'] ?? '') == $mu->id ? 'selected' : '' }}>
                                            {{ $mu->nama_lengkap }} ({{ $mu->magang?->asal_instansi ?? 'PKL' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="filterField">
                            <label class="filterFieldLabel">Titik Lokasi Geofence</label>
                            <select name="lokasi_presensi_id" class="filterSelect">
                                <option value="">Semua Titik Lokasi</option>
                                @foreach ($lokasiPresensis as $lok)
                                    <option value="{{ $lok->id }}" {{ ($filters['lokasi_presensi_id'] ?? '') == $lok->id ? 'selected' : '' }}>
                                        {{ $lok->nama_lokasi }} ({{ $lok->radius_meter }}m)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filterField">
                            <label class="filterFieldLabel">Status Presensi</label>
                            <select name="status" class="filterSelect">
                                <option value="">Semua Status</option>
                                <option value="hadir" {{ ($filters['status'] ?? '') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="proses" {{ ($filters['status'] ?? '') == 'proses' ? 'selected' : '' }}>Sedang Berjalan</option>
                                <option value="izin" {{ ($filters['status'] ?? '') == 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ ($filters['status'] ?? '') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="alpha" {{ ($filters['status'] ?? '') == 'alpha' ? 'selected' : '' }}>Alpha / Tidak Hadir</option>
                            </select>
                        </div>

                        <div class="filter-actions-full">
                            <button type="submit" class="btn-filter-primary">
                                <ion-icon name="filter-outline"></ion-icon> Filter Data Presensi
                            </button>
                            <button type="submit" formtarget="_blank" formaction="{{ route('admin.laporan.exportExcel') }}" class="btn-filter-success">
                                <ion-icon name="document-outline"></ion-icon> Excel
                            </button>
                            <button type="submit" formtarget="_blank" formaction="{{ route('admin.laporan.exportPdf') }}" class="btn-filter-danger">
                                <ion-icon name="document-text-outline"></ion-icon> Cetak PDF
                            </button>
                            <button type="button" onclick="document.getElementById('importPresensiModal').style.display='flex'" class="btn-filter-info">
                                <ion-icon name="cloud-upload-outline"></ion-icon> Import Log
                            </button>
                            <a href="{{ route('admin.laporan.index', ['tab' => $activeTab]) }}" class="btn-filter-reset">Reset</a>
                        </div>
                    </div>
                @endif
            </form>
        </div>

        {{-- ── MODAL IMPOR PRESENSI RETROAKTIF ── --}}
        <div id="importPresensiModal" class="app-modal-backdrop" onclick="if(event.target===this) this.style.display='none'">
            <div class="app-modal-card">
                <div class="app-modal-header">
                    <div class="app-modal-header-left">
                        <div class="app-modal-badge-icon">
                            <ion-icon name="cloud-upload-outline"></ion-icon>
                        </div>
                        <div>
                            <h3 class="app-modal-title m-0">Impor Rekapan Presensi Manual</h3>
                            <div class="text-xs text-muted">Format Spreadsheet Excel / CSV</div>
                        </div>
                    </div>
                    <button type="button" onclick="document.getElementById('importPresensiModal').style.display='none'" class="app-modal-close" title="Tutup">&times;</button>
                </div>
                <p class="app-modal-desc">
                    Unggah berkas spreadsheet Excel/CSV untuk menyinkronkan rekapan data presensi fisik atau kegiatan offline luar jaringan secara massal.
                </p>
                <div class="import-template-banner">
                    <div class="import-template-info">
                        <div class="import-template-icon">
                            <ion-icon name="cloud-download-outline"></ion-icon>
                        </div>
                        <div class="import-template-texts">
                            <div class="import-template-title">Belum memiliki template?</div>
                            <div class="import-template-desc">Gunakan format resmi agar rekapan terbaca akurat.</div>
                        </div>
                    </div>
                    <a href="{{ route('admin.laporan.downloadTemplate') }}" class="btn-download-template" title="Download Template Presensi">
                        <ion-icon name="download-outline"></ion-icon> Unduh Template (.xlsx)
                    </a>
                </div>
                <form method="POST" action="{{ route('admin.laporan.importExcel') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-field-wrapper mb-3">
                        <label class="form-field-label">Pilih Berkas Rekap Presensi:</label>
                        <div class="fileUploadBox">
                            <input type="file" name="file_excel" id="laporanFileInput" accept=".xlsx,.xls,.csv" required onchange="handleExcelFileSelected(this, 'laporanFileFeedback')">
                            <div class="fileUploadIcon">
                                <ion-icon name="cloud-upload-outline"></ion-icon>
                            </div>
                            <div class="fileUploadText">Pilih atau seret berkas ke sini</div>
                            <div class="fileUploadSubtext">
                                <span class="fileUploadInfoPill"><ion-icon name="document-text-outline"></ion-icon> .XLSX, .XLS, .CSV</span>
                                <span class="fileUploadInfoPill"><ion-icon name="hardware-chip-outline"></ion-icon> Maks. 5 MB</span>
                            </div>
                        </div>
                        <div id="laporanFileFeedback" class="fileUploadFeedback"></div>
                    </div>
                    <div class="app-modal-footer">
                        <button type="button" onclick="document.getElementById('importPresensiModal').style.display='none'" class="btnOutline w-auto">Batal</button>
                        <button type="submit" class="profileBtnPrimary w-auto">
                            <ion-icon name="cloud-upload-outline"></ion-icon> Unggah &amp; Impor Presensi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- ── TAB 1: PRESENSI TUTOR & KBM ── --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @if($activeTab === 'tutor')
            <div class="sectionRow flex-between flex-wrap gap-2">
                <h2>Log Aktivitas Presensi Tutor &amp; KBM</h2>
                <span class="badgeCount">{{ $tutorData['records']->count() }} Sesi Terdata</span>
            </div>

            {{-- Desktop Table --}}
            <div class="table-responsive-desktop">
                <div class="tableContainer m-0 mb-4 rounded-xl border-base">
                    <table class="laporanTable">
                        <thead>
                            <tr>
                                <th class="table-col-num">NO</th>
                                <th>TANGGAL</th>
                                <th>TUTOR</th>
                                <th>MURID &amp; KELAS</th>
                                <th>MODA</th>
                                <th>MASUK - SELESAI</th>
                                <th>DURASI</th>
                                <th>HONOR SK</th>
                                <th>FOTO BUKTI</th>
                                <th class="text-center">LOKASI GPS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tutorData['records'] as $index => $presensi)
                                @php
                                    $tutorName = $presensi->tutor->nama_lengkap ?? 'Tutor';
                                    $muridObj = $presensi->murid ?? $presensi->siswa;
                                    $muridName = $muridObj->nama_murid ?? ($muridObj->nama_siswa ?? '-');
                                    $kelasName = $muridObj->relKelas->nama_kelas ?? '-';
                                    $tgl = \Carbon\Carbon::parse($presensi->tgl_presensi)->format('d/m/y');
                                    $jamMasuk = $presensi->jam_mulai ? \Carbon\Carbon::parse($presensi->jam_mulai)->format('H:i') : '-';
                                    $jamSelesai = $presensi->jam_selesai ? \Carbon\Carbon::parse($presensi->jam_selesai)->format('H:i') : '-';
                                    $lokasi = $presensi->lokasi_mulai ?? '-';
                                    $durasiMenit = ($presensi->jam_mulai && $presensi->jam_selesai) 
                                        ? \Carbon\Carbon::parse($presensi->jam_mulai)->diffInMinutes(\Carbon\Carbon::parse($presensi->jam_selesai)) 
                                        : 0;
                                @endphp
                                <tr>
                                    <td class="p-3 font-bold text-muted">{{ $index + 1 }}</td>
                                    <td class="font-bold white-space-nowrap">{{ $tgl }}</td>
                                    <td class="font-semibold text-dark">{{ $tutorName }}</td>
                                    <td>
                                        <div class="font-semibold text-dark">{{ $muridName }}</div>
                                        <div class="text-xs text-muted">{{ $kelasName }}</div>
                                    </td>
                                    <td>
                                        <span class="badge text-xs font-bold {{ $presensi->moda_pembelajaran === 'online' ? 'bg-info-light text-info' : ($presensi->moda_pembelajaran === 'kunjungan_rumah' ? 'bg-warning-light text-warning' : 'bg-success-light text-success') }}">
                                            {{ $presensi->moda_label ?? 'Tatap Muka' }}
                                        </span>
                                    </td>
                                    <td class="white-space-nowrap font-bold">
                                        <span class="text-success">{{ $jamMasuk }}</span> &bull; <span class="text-primary">{{ $jamSelesai }}</span>
                                    </td>
                                    <td class="font-semibold">{{ $durasiMenit > 0 ? round($durasiMenit/60, 1) . ' Jam' : '-' }}</td>
                                    <td class="font-bold text-dark font-mono text-xs">
                                        {{ $presensi->nominal_honor_snapshot ? 'Rp ' . number_format($presensi->nominal_honor_snapshot, 0, ',', '.') : '-' }}
                                    </td>
                                    <td>
                                        <div class="fotoStack">
                                            @if ($presensi->foto_mulai)
                                                <img src="{{ $presensi->foto_mulai_url }}" class="fotoThumbnail cursor-pointer" title="Foto Masuk" onclick="openPhotoModal('{{ $presensi->foto_mulai_url }}', 'Foto Masuk - {{ $tutorName }}')">
                                            @else
                                                <div class="fotoPlaceholder">M -</div>
                                            @endif
                                            @if ($presensi->foto_selesai)
                                                <img src="{{ $presensi->foto_selesai_url }}" class="fotoThumbnail cursor-pointer" title="Foto Selesai" onclick="openPhotoModal('{{ $presensi->foto_selesai_url }}', 'Foto Pulang - {{ $tutorName }}')">
                                            @else
                                                <div class="fotoPlaceholder">S -</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if ($lokasi !== '-')
                                            @php
                                                $titikLok = $presensi->lokasiPresensi;
                                                $tNama = $titikLok?->nama_lokasi ?? '';
                                                $tLat = $titikLok?->latitude ?? '';
                                                $tLng = $titikLok?->longitude ?? '';
                                                $tRad = $titikLok?->radius_meter ?? '';
                                            @endphp
                                            <button type="button" class="mapBtn" title="Lihat Peta" onclick="openMapModal('{{ $lokasi }}', '{{ addslashes($tutorName) }}', '{{ $presensi->moda_label ?? 'Tatap Muka' }}', '{{ addslashes($tNama) }}', '{{ $tLat }}', '{{ $tLng }}', '{{ $tRad }}')">
                                                <ion-icon name="map-outline"></ion-icon>
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="table-empty-cell">
                                        <div class="tableEmptyState">
                                            <div class="tableEmptyIconWrap"><ion-icon name="calendar-outline"></ion-icon></div>
                                            <div class="tableEmptyTitle">Belum Ada Rekap Presensi Tutor</div>
                                            <div class="tableEmptyDesc">Tidak ada catatan kegiatan mengajar pada filter yang dipilih.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile Cards --}}
            <div class="mobile-card-list">
                @forelse($tutorData['records'] as $index => $presensi)
                    @php
                        $tutorName = $presensi->tutor->nama_lengkap ?? 'Tutor';
                        $muridObj = $presensi->murid ?? $presensi->siswa;
                        $muridName = $muridObj->nama_murid ?? ($muridObj->nama_siswa ?? '-');
                        $tgl = \Carbon\Carbon::parse($presensi->tgl_presensi)->format('d/m/Y');
                        $jamMasuk = $presensi->jam_mulai ? \Carbon\Carbon::parse($presensi->jam_mulai)->format('H:i') : '-';
                        $jamSelesai = $presensi->jam_selesai ? \Carbon\Carbon::parse($presensi->jam_selesai)->format('H:i') : '-';
                        $lokasi = $presensi->lokasi_mulai ?? '-';
                    @endphp
                    <div class="data-mobile-card">
                        <div class="dmc-header">
                            <div>
                                <h3 class="dmc-title">{{ $tutorName }}</h3>
                                <div class="dmc-subtitle">{{ $tgl }} &bull; Murid: <b class="text-dark">{{ $muridName }}</b></div>
                            </div>
                            <span class="badge text-xs {{ $presensi->moda_pembelajaran === 'online' ? 'bg-info-light text-info' : 'bg-success-light text-success' }}">
                                {{ $presensi->moda_label ?? 'Tatap Muka' }}
                            </span>
                        </div>
                        <div class="dmc-grid">
                            <div class="dmc-field">
                                <div class="dmc-label">Jam Masuk</div>
                                <div class="dmc-value text-success">{{ $jamMasuk }}</div>
                            </div>
                            <div class="dmc-field">
                                <div class="dmc-label">Jam Selesai</div>
                                <div class="dmc-value text-primary">{{ $jamSelesai }}</div>
                            </div>
                        </div>
                        <div class="dmc-footer">
                            <div class="d-flex items-center gap-1">
                                <span class="dmc-label mb-0">Foto:</span>
                                <div class="fotoStack">
                                    @if ($presensi->foto_mulai)
                                        <img src="{{ $presensi->foto_mulai_url }}" class="fotoThumbnail cursor-pointer" onclick="openPhotoModal('{{ $presensi->foto_mulai_url }}', 'Foto Masuk - {{ $tutorName }}')">
                                    @endif
                                    @if ($presensi->foto_selesai)
                                        <img src="{{ $presensi->foto_selesai_url }}" class="fotoThumbnail cursor-pointer" onclick="openPhotoModal('{{ $presensi->foto_selesai_url }}', 'Foto Selesai - {{ $tutorName }}')">
                                    @endif
                                </div>
                            </div>
                            @if ($lokasi !== '-')
                                @php
                                    $titikLok = $presensi->lokasiPresensi;
                                    $tNama = $titikLok?->nama_lokasi ?? '';
                                    $tLat = $titikLok?->latitude ?? '';
                                    $tLng = $titikLok?->longitude ?? '';
                                    $tRad = $titikLok?->radius_meter ?? '';
                                @endphp
                                <button type="button" class="mapBtn" onclick="openMapModal('{{ $lokasi }}', '{{ addslashes($tutorName) }}', '{{ $presensi->moda_label ?? 'Tatap Muka' }}', '{{ addslashes($tNama) }}', '{{ $tLat }}', '{{ $tLng }}', '{{ $tRad }}')">
                                    <ion-icon name="map-outline"></ion-icon>
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="tableEmptyCard">
                        <div class="tableEmptyIconWrap"><ion-icon name="calendar-outline"></ion-icon></div>
                        <div class="tableEmptyTitle">Belum Ada Rekap Presensi Tutor</div>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- ── TAB 2: PRESENSI MANDIRI MURID ── --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @if($activeTab === 'murid' || $activeTab === 'siswa')
            <div class="sectionRow flex-between flex-wrap gap-2">
                <h2>Log Presensi Mandiri Murid</h2>
                <span class="badgeCount">{{ ($muridData ?? $siswaData)['records']->count() }} Rekaman Murid</span>
            </div>

            {{-- Desktop Table --}}
            <div class="table-responsive-desktop">
                <div class="tableContainer m-0 mb-4 rounded-xl border-base">
                    <table class="laporanTable">
                        <thead>
                            <tr>
                                <th class="table-col-num">NO</th>
                                <th>TANGGAL</th>
                                <th>NAMA MURID</th>
                                <th>KELAS / JENJANG</th>
                                <th>JAM CHECK-IN</th>
                                <th>STATUS</th>
                                <th>FOTO SELFIE</th>
                                <th class="text-center">LOKASI GPS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($siswaData['records'] as $index => $ps)
                                @php
                                    $sMurid = $ps->murid ?? $ps->siswa;
                                    $sName = $sMurid->nama_murid ?? ($sMurid->nama_siswa ?? '-');
                                    $sKelas = $sMurid->relKelas->nama_kelas ?? '-';
                                    $sJenjang = $sMurid->jenjang_paket_label ?? '-';
                                    $tgl = \Carbon\Carbon::parse($ps->tgl_presensi)->format('d/m/Y');
                                    $jamMasuk = $ps->jam_masuk ? \Carbon\Carbon::parse($ps->jam_masuk)->format('H:i') : '-';
                                    $lokasi = $ps->lokasi_masuk ?? '-';
                                @endphp
                                <tr>
                                    <td class="p-3 font-bold text-muted">{{ $index + 1 }}</td>
                                    <td class="font-bold white-space-nowrap">{{ $tgl }}</td>
                                    <td class="font-semibold text-dark">{{ $sName }}</td>
                                    <td>
                                        <div class="font-semibold text-dark">{{ $sKelas }}</div>
                                        <div class="text-xs text-muted">{{ $sJenjang }}</div>
                                    </td>
                                    <td class="font-bold text-success">{{ $jamMasuk }}</td>
                                    <td>
                                        <span class="badge text-xs font-bold {{ $ps->status === 'hadir' ? ($ps->isTerlambat() ? 'bg-warning-light text-warning' : 'bg-success-light text-success') : 'bg-danger-light text-danger' }}">
                                            {{ $ps->status_label }} {{ $ps->isTerlambat() ? '(+'.$ps->menit_keterlambatan.' mnt)' : '' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($ps->foto_masuk)
                                            <img src="{{ $ps->foto_masuk_url }}" class="fotoThumbnail cursor-pointer" onclick="openPhotoModal('{{ $ps->foto_masuk_url }}', 'Presensi Murid - {{ $sName }}')">
                                        @else
                                            <div class="fotoPlaceholder">M -</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($lokasi !== '-')
                                            @php
                                                $titikLok = $ps->lokasiPresensi;
                                                $tNama = $titikLok?->nama_lokasi ?? '';
                                                $tLat = $titikLok?->latitude ?? '';
                                                $tLng = $titikLok?->longitude ?? '';
                                                $tRad = $titikLok?->radius_meter ?? '';
                                            @endphp
                                            <button type="button" class="mapBtn" onclick="openMapModal('{{ $lokasi }}', '{{ addslashes($sName) }}', 'Presensi Murid Mandiri', '{{ addslashes($tNama) }}', '{{ $tLat }}', '{{ $tLng }}', '{{ $tRad }}')">
                                                <ion-icon name="map-outline"></ion-icon>
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="table-empty-cell">
                                        <div class="tableEmptyState">
                                            <div class="tableEmptyIconWrap"><ion-icon name="person-outline"></ion-icon></div>
                                            <div class="tableEmptyTitle">Belum Ada Presensi Mandiri Murid</div>
                                            <div class="tableEmptyDesc">Tidak ada log presensi murid pada filter tanggal yang dipilih.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile Cards --}}
            <div class="mobile-card-list">
                @forelse($siswaData['records'] as $index => $ps)
                    @php
                        $sMurid = $ps->murid ?? $ps->siswa;
                        $sName = $sMurid->nama_murid ?? ($sMurid->nama_siswa ?? '-');
                        $sKelas = $sMurid->relKelas->nama_kelas ?? '-';
                        $tgl = \Carbon\Carbon::parse($ps->tgl_presensi)->format('d/m/Y');
                        $jamMasuk = $ps->jam_masuk ? \Carbon\Carbon::parse($ps->jam_masuk)->format('H:i') : '-';
                    @endphp
                    <div class="data-mobile-card">
                        <div class="dmc-header">
                            <div>
                                <h3 class="dmc-title">{{ $sName }}</h3>
                                <div class="dmc-subtitle">{{ $tgl }} &bull; {{ $sKelas }}</div>
                            </div>
                            <span class="badge text-xs {{ $ps->status === 'hadir' ? 'bg-success-light text-success' : 'bg-danger-light text-danger' }}">
                                {{ $ps->status_label }}
                            </span>
                        </div>
                        <div class="dmc-grid">
                            <div class="dmc-field">
                                <div class="dmc-label">Jam Check-In</div>
                                <div class="dmc-value text-success">{{ $jamMasuk }}</div>
                            </div>
                            <div class="dmc-field">
                                <div class="dmc-label">Foto Selfie</div>
                                @if ($ps->foto_masuk)
                                    <img src="{{ $ps->foto_masuk_url }}" class="fotoThumbnail cursor-pointer" onclick="openPhotoModal('{{ $ps->foto_masuk_url }}', 'Presensi Murid - {{ $sName }}')">
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="tableEmptyCard">
                        <div class="tableEmptyIconWrap"><ion-icon name="person-outline"></ion-icon></div>
                        <div class="tableEmptyTitle">Belum Ada Presensi Murid</div>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- ── TAB 3: PRESENSI PEGAWAI & PIMPINAN ── --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @if($activeTab === 'pegawai')
            <div class="sectionRow flex-between flex-wrap gap-2">
                <h2>Presensi Karyawan, Admin &amp; Kepala Sekolah</h2>
                <span class="badgeCount">{{ $pegawaiData['records']->count() }} Rekaman</span>
            </div>

            <div class="table-responsive-desktop">
                <div class="tableContainer m-0 mb-4 rounded-xl border-base">
                    <table class="laporanTable">
                        <thead>
                            <tr>
                                <th class="table-col-num">NO</th>
                                <th>TANGGAL</th>
                                <th>NAMA PEGAWAI</th>
                                <th>ROLE / JABATAN</th>
                                <th>JAM MASUK</th>
                                <th>JAM PULANG</th>
                                <th>FOTO</th>
                                <th class="text-center">LOKASI GPS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pegawaiData['records'] as $index => $kp)
                                @php
                                    $kpTgl = \Carbon\Carbon::parse($kp->tgl_presensi)->format('d/m/Y');
                                    $kpJamMasuk = $kp->jam_mulai ? \Carbon\Carbon::parse($kp->jam_mulai)->format('H:i') : '-';
                                    $kpJamSelesai = $kp->jam_selesai ? \Carbon\Carbon::parse($kp->jam_selesai)->format('H:i') : '-';
                                    $kpName = $kp->user->nama_lengkap ?? '-';
                                    $kpRole = ucfirst($kp->user->role ?? '-');
                                    $lokasi = $kp->lokasi_mulai;
                                @endphp
                                <tr>
                                    <td class="p-3 font-bold text-muted">{{ $index + 1 }}</td>
                                    <td class="font-bold white-space-nowrap">{{ $kpTgl }}</td>
                                    <td class="font-semibold text-dark">{{ $kpName }}</td>
                                    <td>
                                        <span class="badge text-xs font-bold {{ $kp->user->role === 'admin' ? 'bg-primary-light text-primary' : ($kp->user->role === 'kepala_sekolah' ? 'bg-success-light text-success' : 'bg-muted-light text-muted') }}">
                                            {{ $kpRole }}
                                        </span>
                                    </td>
                                    <td class="font-bold text-success">{{ $kpJamMasuk }}</td>
                                    <td class="font-bold text-primary">{{ $kpJamSelesai }}</td>
                                    <td>
                                        <div class="fotoStack">
                                            @if ($kp->foto_mulai)
                                                <img src="{{ $kp->foto_mulai_url }}" class="fotoThumbnail cursor-pointer" onclick="openPhotoModal('{{ $kp->foto_mulai_url }}', 'Foto Masuk - {{ $kpName }}')">
                                            @endif
                                            @if ($kp->foto_selesai)
                                                <img src="{{ $kp->foto_selesai_url }}" class="fotoThumbnail cursor-pointer" onclick="openPhotoModal('{{ $kp->foto_selesai_url }}', 'Foto Pulang - {{ $kpName }}')">
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if ($lokasi && $lokasi !== '-')
                                            @php
                                                $titikLok = $kp->lokasiPresensi;
                                                $tNama = $titikLok?->nama_lokasi ?? '';
                                                $tLat = $titikLok?->latitude ?? '';
                                                $tLng = $titikLok?->longitude ?? '';
                                                $tRad = $titikLok?->radius_meter ?? '';
                                            @endphp
                                            <button type="button" class="mapBtn" onclick="openMapModal('{{ $lokasi }}', '{{ addslashes($kpName) }}', 'Staf / {{ $kpRole }}', '{{ addslashes($tNama) }}', '{{ $tLat }}', '{{ $tLng }}', '{{ $tRad }}')">
                                                <ion-icon name="map-outline"></ion-icon>
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="table-empty-cell">
                                        <div class="tableEmptyState">
                                            <div class="tableEmptyIconWrap"><ion-icon name="briefcase-outline"></ion-icon></div>
                                            <div class="tableEmptyTitle">Belum Ada Presensi Pegawai</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile Cards Pegawai --}}
            <div class="mobile-card-list">
                @forelse($pegawaiData['records'] as $index => $kp)
                    @php
                        $kpTgl = \Carbon\Carbon::parse($kp->tgl_presensi)->format('d/m/Y');
                        $kpJamMasuk = $kp->jam_mulai ? \Carbon\Carbon::parse($kp->jam_mulai)->format('H:i') : '-';
                        $kpJamSelesai = $kp->jam_selesai ? \Carbon\Carbon::parse($kp->jam_selesai)->format('H:i') : '-';
                        $kpName = $kp->user->nama_lengkap ?? '-';
                        $kpRole = ucfirst($kp->user->role ?? '-');
                        $lokasi = $kp->lokasi_mulai;
                    @endphp
                    <div class="data-mobile-card">
                        <div class="dmc-header">
                            <div>
                                <h3 class="dmc-title">{{ $kpName }}</h3>
                                <div class="dmc-subtitle">{{ $kpTgl }} &bull; Jabatan: <b class="text-dark">{{ $kpRole }}</b></div>
                            </div>
                            <span class="badge text-xs font-bold {{ $kp->user->role === 'admin' ? 'bg-primary-light text-primary' : ($kp->user->role === 'kepala_sekolah' ? 'bg-success-light text-success' : 'bg-muted-light text-muted') }}">
                                {{ $kpRole }}
                            </span>
                        </div>
                        <div class="dmc-grid">
                            <div class="dmc-field">
                                <div class="dmc-label">Jam Masuk</div>
                                <div class="dmc-value text-success font-bold">{{ $kpJamMasuk }}</div>
                            </div>
                            <div class="dmc-field">
                                <div class="dmc-label">Jam Pulang</div>
                                <div class="dmc-value text-primary font-bold">{{ $kpJamSelesai }}</div>
                            </div>
                        </div>
                        <div class="dmc-footer">
                            <div class="d-flex items-center gap-2">
                                <span class="dmc-label mb-0">Foto:</span>
                                <div class="fotoStack">
                                    @if ($kp->foto_mulai)
                                        <img src="{{ $kp->foto_mulai_url }}" class="fotoThumbnail cursor-pointer" onclick="openPhotoModal('{{ $kp->foto_mulai_url }}', 'Foto Masuk - {{ $kpName }}')">
                                    @else
                                        <div class="fotoPlaceholder">M -</div>
                                    @endif
                                    @if ($kp->foto_selesai)
                                        <img src="{{ $kp->foto_selesai_url }}" class="fotoThumbnail cursor-pointer" onclick="openPhotoModal('{{ $kp->foto_selesai_url }}', 'Foto Pulang - {{ $kpName }}')">
                                    @else
                                        <div class="fotoPlaceholder">P -</div>
                                    @endif
                                </div>
                            </div>
                            @if ($lokasi && $lokasi !== '-')
                                @php
                                    $titikLok = $kp->lokasiPresensi;
                                    $tNama = $titikLok?->nama_lokasi ?? '';
                                    $tLat = $titikLok?->latitude ?? '';
                                    $tLng = $titikLok?->longitude ?? '';
                                    $tRad = $titikLok?->radius_meter ?? '';
                                @endphp
                                <button type="button" class="mapBtn" onclick="openMapModal('{{ $lokasi }}', '{{ addslashes($kpName) }}', 'Staf / {{ $kpRole }}', '{{ addslashes($tNama) }}', '{{ $tLat }}', '{{ $tLng }}', '{{ $tRad }}')">
                                    <ion-icon name="map-outline"></ion-icon>
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="tableEmptyCard">
                        <div class="tableEmptyIconWrap"><ion-icon name="briefcase-outline"></ion-icon></div>
                        <div class="tableEmptyTitle">Belum Ada Presensi Pegawai</div>
                        <div class="tableEmptyDesc">Tidak ada rekaman presensi pegawai pada filter yang dipilih.</div>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- ── TAB 4: MAHASISWA MAGANG (PKL) ── --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @if($activeTab === 'magang')
            <div class="sectionRow flex-between flex-wrap gap-2">
                <h2>Log Presensi Mahasiswa Magang &amp; Siswa PKL</h2>
                <div class="d-flex items-center gap-2">
                    <span class="badgeCount">{{ $magangData['records']->count() }} Rekaman</span>
                    <span class="badge text-xs bg-success-light text-success font-bold px-3 py-1 rounded-pill">
                        Total {{ $magangData['totalJamKerja'] }} Jam Kerja
                    </span>
                </div>
            </div>

            <div class="table-responsive-desktop">
                <div class="tableContainer m-0 mb-4 rounded-xl border-base">
                    <table class="laporanTable">
                        <thead>
                            <tr>
                                <th class="table-col-num">NO</th>
                                <th>TANGGAL</th>
                                <th>NAMA MAHASISWA</th>
                                <th>ASAL INSTANSI</th>
                                <th>JAM MASUK</th>
                                <th>JAM PULANG</th>
                                <th>DURASI</th>
                                <th>FOTO</th>
                                <th class="text-center">LOKASI GPS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($magangData['records'] as $index => $mp)
                                @php
                                    $mpTgl = \Carbon\Carbon::parse($mp->tgl_presensi)->format('d/m/Y');
                                    $mpJamMasuk = $mp->jam_mulai ? \Carbon\Carbon::parse($mp->jam_mulai)->format('H:i') : '-';
                                    $mpJamSelesai = $mp->jam_selesai ? \Carbon\Carbon::parse($mp->jam_selesai)->format('H:i') : '-';
                                    $mpName = $mp->user->nama_lengkap ?? '-';
                                    $mpInstansi = $mp->user->magang?->asal_instansi ?? 'Magang PKBM';
                                    $lokasi = $mp->lokasi_mulai;
                                    $durasiMenit = ($mp->jam_mulai && $mp->jam_selesai) 
                                        ? \Carbon\Carbon::parse($mp->jam_mulai)->diffInMinutes(\Carbon\Carbon::parse($mp->jam_selesai)) 
                                        : 0;
                                @endphp
                                <tr>
                                    <td class="p-3 font-bold text-muted">{{ $index + 1 }}</td>
                                    <td class="font-bold white-space-nowrap">{{ $mpTgl }}</td>
                                    <td class="font-semibold text-dark">{{ $mpName }}</td>
                                    <td class="text-muted text-xs font-semibold">{{ $mpInstansi }}</td>
                                    <td class="font-bold text-success">{{ $mpJamMasuk }}</td>
                                    <td class="font-bold text-primary">{{ $mpJamSelesai }}</td>
                                    <td class="font-bold text-dark">{{ $durasiMenit > 0 ? round($durasiMenit/60, 1) . ' Jam' : '-' }}</td>
                                    <td>
                                        <div class="fotoStack">
                                            @if ($mp->foto_mulai)
                                                <img src="{{ $mp->foto_mulai_url }}" class="fotoThumbnail cursor-pointer" onclick="openPhotoModal('{{ $mp->foto_mulai_url }}', 'Foto Masuk - {{ $mpName }}')">
                                            @endif
                                            @if ($mp->foto_selesai)
                                                <img src="{{ $mp->foto_selesai_url }}" class="fotoThumbnail cursor-pointer" onclick="openPhotoModal('{{ $mp->foto_selesai_url }}', 'Foto Pulang - {{ $mpName }}')">
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if ($lokasi && $lokasi !== '-')
                                            @php
                                                $titikLok = $mp->lokasiPresensi;
                                                $tNama = $titikLok?->nama_lokasi ?? '';
                                                $tLat = $titikLok?->latitude ?? '';
                                                $tLng = $titikLok?->longitude ?? '';
                                                $tRad = $titikLok?->radius_meter ?? '';
                                            @endphp
                                            <button type="button" class="mapBtn" onclick="openMapModal('{{ $lokasi }}', '{{ addslashes($mpName) }}', 'Mahasiswa Magang', '{{ addslashes($tNama) }}', '{{ $tLat }}', '{{ $tLng }}', '{{ $tRad }}')">
                                                <ion-icon name="map-outline"></ion-icon>
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="table-empty-cell">
                                        <div class="tableEmptyState">
                                            <div class="tableEmptyIconWrap"><ion-icon name="id-card-outline"></ion-icon></div>
                                            <div class="tableEmptyTitle">Belum Ada Presensi Mahasiswa Magang</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile Cards Magang --}}
            <div class="mobile-card-list">
                @forelse($magangData['records'] as $index => $mp)
                    @php
                        $mpTgl = \Carbon\Carbon::parse($mp->tgl_presensi)->format('d/m/Y');
                        $mpJamMasuk = $mp->jam_mulai ? \Carbon\Carbon::parse($mp->jam_mulai)->format('H:i') : '-';
                        $mpJamSelesai = $mp->jam_selesai ? \Carbon\Carbon::parse($mp->jam_selesai)->format('H:i') : '-';
                        $mpName = $mp->user->nama_lengkap ?? '-';
                        $mpInstansi = $mp->user->magang?->asal_instansi ?? 'Magang PKBM';
                        $lokasi = $mp->lokasi_mulai;
                        $durasiMenit = ($mp->jam_mulai && $mp->jam_selesai) 
                            ? \Carbon\Carbon::parse($mp->jam_mulai)->diffInMinutes(\Carbon\Carbon::parse($mp->jam_selesai)) 
                            : 0;
                    @endphp
                    <div class="data-mobile-card">
                        <div class="dmc-header">
                            <div>
                                <h3 class="dmc-title">{{ $mpName }}</h3>
                                <div class="dmc-subtitle">{{ $mpTgl }} &bull; Instansi: <b class="text-dark">{{ $mpInstansi }}</b></div>
                            </div>
                            <span class="badge text-xs bg-success-light text-success font-bold px-2 py-1 rounded-pill">
                                {{ $durasiMenit > 0 ? round($durasiMenit/60, 1) . ' Jam' : '-' }}
                            </span>
                        </div>
                        <div class="dmc-grid">
                            <div class="dmc-field">
                                <div class="dmc-label">Jam Masuk</div>
                                <div class="dmc-value text-success font-bold">{{ $mpJamMasuk }}</div>
                            </div>
                            <div class="dmc-field">
                                <div class="dmc-label">Jam Pulang</div>
                                <div class="dmc-value text-primary font-bold">{{ $mpJamSelesai }}</div>
                            </div>
                        </div>
                        <div class="dmc-footer">
                            <div class="d-flex items-center gap-2">
                                <span class="dmc-label mb-0">Foto:</span>
                                <div class="fotoStack">
                                    @if ($mp->foto_mulai)
                                        <img src="{{ $mp->foto_mulai_url }}" class="fotoThumbnail cursor-pointer" onclick="openPhotoModal('{{ $mp->foto_mulai_url }}', 'Foto Masuk - {{ $mpName }}')">
                                    @else
                                        <div class="fotoPlaceholder">M -</div>
                                    @endif
                                    @if ($mp->foto_selesai)
                                        <img src="{{ $mp->foto_selesai_url }}" class="fotoThumbnail cursor-pointer" onclick="openPhotoModal('{{ $mp->foto_selesai_url }}', 'Foto Pulang - {{ $mpName }}')">
                                    @else
                                        <div class="fotoPlaceholder">P -</div>
                                    @endif
                                </div>
                            </div>
                            @if ($lokasi && $lokasi !== '-')
                                @php
                                    $titikLok = $mp->lokasiPresensi;
                                    $tNama = $titikLok?->nama_lokasi ?? '';
                                    $tLat = $titikLok?->latitude ?? '';
                                    $tLng = $titikLok?->longitude ?? '';
                                    $tRad = $titikLok?->radius_meter ?? '';
                                @endphp
                                <button type="button" class="mapBtn" onclick="openMapModal('{{ $lokasi }}', '{{ addslashes($mpName) }}', 'Mahasiswa Magang', '{{ addslashes($tNama) }}', '{{ $tLat }}', '{{ $tLng }}', '{{ $tRad }}')">
                                    <ion-icon name="map-outline"></ion-icon>
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="tableEmptyCard">
                        <div class="tableEmptyIconWrap"><ion-icon name="id-card-outline"></ion-icon></div>
                        <div class="tableEmptyTitle">Belum Ada Presensi Mahasiswa Magang</div>
                        <div class="tableEmptyDesc">Tidak ada rekaman presensi magang pada filter yang dipilih.</div>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- ── TAB 5: PERIZINAN & LUPA LAPOR ── --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @if($activeTab === 'perizinan')
            <div class="sectionRow flex-between flex-wrap gap-2">
                <h2>Pengajuan Izin, Sakit &amp; Rekonsiliasi Lupa Lapor</h2>
            </div>

            {{-- 1. Pengajuan Izin & Sakit --}}
            <div class="card mb-4 mt-2">
                <div class="cardTitle flex-between flex-wrap gap-2 mb-3">
                    <div class="d-flex items-center gap-2">
                        <ion-icon name="medkit-outline"></ion-icon> <span>Pengajuan Izin &amp; Sakit Mandiri Tutor</span>
                    </div>
                    <span class="badge text-xs bg-primary-light text-primary font-bold px-3 py-1 rounded-pill">
                        {{ $perizinanData['totalIzinSakit'] }} Permohonan
                    </span>
                </div>
                
                {{-- Desktop Table --}}
                <div class="table-responsive-desktop">
                    <div class="overflow-x-auto">
                        <table class="table-compact w-full text-left">
                            <thead>
                                <tr>
                                    <th class="p-3 w-10">No</th>
                                    <th>Tutor</th>
                                    <th>Jenis</th>
                                    <th>Periode Izin</th>
                                    <th>Alasan</th>
                                    <th>Surat Dokter / Bukti</th>
                                    <th>Status Verifikasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($perizinanData['izinRecords'] as $idx => $iz)
                                    <tr>
                                        <td class="p-3 font-bold text-muted">{{ $idx + 1 }}</td>
                                        <td class="font-semibold text-dark">{{ $iz->tutor->nama_lengkap ?? '-' }}</td>
                                        <td>
                                            <span class="badge text-xs {{ $iz->jenis === 'sakit' ? 'bg-danger-light text-danger' : 'bg-warning-light text-warning' }}">
                                                {{ $iz->jenis_label }}
                                            </span>
                                        </td>
                                        <td class="font-bold white-space-nowrap">
                                            {{ \Carbon\Carbon::parse($iz->tgl_mulai)->format('d/m/y') }} s/d {{ \Carbon\Carbon::parse($iz->tgl_selesai)->format('d/m/y') }}
                                        </td>
                                        <td class="text-xs text-dark">{{ $iz->alasan }}</td>
                                        <td>
                                            @if($iz->dokumen_url)
                                                <a href="{{ $iz->dokumen_url }}" target="_blank" class="btnNavMaps text-xs py-1 px-2">
                                                    <ion-icon name="document-attach-outline"></ion-icon> Buka Surat
                                                </a>
                                            @else
                                                <span class="text-muted text-xs">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge text-xs font-bold {{ $iz->status === 'disetujui' ? 'bg-success-light text-success' : ($iz->status === 'ditolak' ? 'bg-danger-light text-danger' : 'bg-warning-light text-warning') }}">
                                                {{ ucfirst($iz->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center p-4 text-muted">Tidak ada pengajuan izin/sakit pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Mobile Cards Izin & Sakit --}}
                <div class="mobile-card-list">
                    @forelse($perizinanData['izinRecords'] as $idx => $iz)
                        <div class="data-mobile-card">
                            <div class="dmc-header">
                                <div>
                                    <h3 class="dmc-title">{{ $iz->tutor->nama_lengkap ?? '-' }}</h3>
                                    <div class="dmc-subtitle">{{ \Carbon\Carbon::parse($iz->tgl_mulai)->format('d/m/y') }} s/d {{ \Carbon\Carbon::parse($iz->tgl_selesai)->format('d/m/y') }}</div>
                                </div>
                                <span class="badge text-xs font-bold {{ $iz->status === 'disetujui' ? 'bg-success-light text-success' : ($iz->status === 'ditolak' ? 'bg-danger-light text-danger' : 'bg-warning-light text-warning') }}">
                                    {{ ucfirst($iz->status) }}
                                </span>
                            </div>
                            <div class="dmc-grid mb-2">
                                <div class="dmc-field">
                                    <div class="dmc-label">Jenis</div>
                                    <span class="badge text-xs {{ $iz->jenis === 'sakit' ? 'bg-danger-light text-danger' : 'bg-warning-light text-warning' }}">
                                        {{ $iz->jenis_label }}
                                    </span>
                                </div>
                                <div class="dmc-field">
                                    <div class="dmc-label">Lampiran Bukti</div>
                                    @if($iz->dokumen_url)
                                        <a href="{{ $iz->dokumen_url }}" target="_blank" class="btnNavMaps text-xs py-1 px-2">
                                            <ion-icon name="document-attach-outline"></ion-icon> Buka Surat
                                        </a>
                                    @else
                                        <span class="text-muted text-xs">-</span>
                                    @endif
                                </div>
                            </div>
                            <div class="dmc-field">
                                <div class="dmc-label">Alasan</div>
                                <div class="text-xs text-dark" style="background: var(--bg, #f8fafc); padding: 8px 10px; border-radius: 8px; border: 1px solid var(--border);">{{ $iz->alasan }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="tableEmptyCard">
                            <div class="tableEmptyIconWrap"><ion-icon name="medkit-outline"></ion-icon></div>
                            <div class="tableEmptyTitle">Tidak Ada Pengajuan Izin/Sakit</div>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- 2. Pengajuan Lupa Lapor --}}
            <div class="card mb-4">
                <div class="cardTitle flex-between flex-wrap gap-2 mb-3">
                    <div class="d-flex items-center gap-2">
                        <ion-icon name="time-outline"></ion-icon> <span>Pengajuan Lupa Lapor (Retroaktif Tutor)</span>
                    </div>
                    <span class="badge text-xs bg-primary-light text-primary font-bold px-3 py-1 rounded-pill">
                        {{ $perizinanData['totalLupaLapor'] }} Pengajuan
                    </span>
                </div>
                
                {{-- Desktop Table --}}
                <div class="table-responsive-desktop">
                    <div class="overflow-x-auto">
                        <table class="table-compact w-full text-left">
                            <thead>
                                <tr>
                                    <th class="p-3 w-10">No</th>
                                    <th>Tutor</th>
                                    <th>Murid</th>
                                    <th>Tanggal Mengajar</th>
                                    <th>Jam Sesi</th>
                                    <th>Alasan</th>
                                    <th>Status Kepsek</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($perizinanData['lupaRecords'] as $idx => $lp)
                                    @php
                                        $lpMurid = $lp->murid ?? $lp->siswa;
                                    @endphp
                                    <tr>
                                        <td class="p-3 font-bold text-muted">{{ $idx + 1 }}</td>
                                        <td class="font-semibold text-dark">{{ $lp->tutor->nama_lengkap ?? '-' }}</td>
                                        <td class="font-semibold">{{ $lpMurid->nama_murid ?? ($lpMurid->nama_siswa ?? '-') }}</td>
                                        <td class="font-bold white-space-nowrap">{{ \Carbon\Carbon::parse($lp->tanggal)->format('d/m/Y') }}</td>
                                        <td class="font-bold text-success">{{ $lp->jam_mulai }} - {{ $lp->jam_selesai }}</td>
                                        <td class="text-xs text-dark">{{ $lp->alasan }}</td>
                                        <td>
                                            <span class="badge text-xs font-bold {{ $lp->status === 'disetujui' ? 'bg-success-light text-success' : ($lp->status === 'ditolak' ? 'bg-danger-light text-danger' : 'bg-warning-light text-warning') }}">
                                                {{ ucfirst($lp->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center p-4 text-muted">Tidak ada data pengajuan lupa lapor pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Mobile Cards Lupa Lapor --}}
                <div class="mobile-card-list">
                    @forelse($perizinanData['lupaRecords'] as $idx => $lp)
                        @php
                            $lpMurid = $lp->murid ?? $lp->siswa;
                        @endphp
                        <div class="data-mobile-card">
                            <div class="dmc-header">
                                <div>
                                    <h3 class="dmc-title">{{ $lp->tutor->nama_lengkap ?? '-' }}</h3>
                                    <div class="dmc-subtitle">Murid: <b class="text-dark">{{ $lpMurid->nama_murid ?? ($lpMurid->nama_siswa ?? '-') }}</b></div>
                                </div>
                                <span class="badge text-xs font-bold {{ $lp->status === 'disetujui' ? 'bg-success-light text-success' : ($lp->status === 'ditolak' ? 'bg-danger-light text-danger' : 'bg-warning-light text-warning') }}">
                                    {{ ucfirst($lp->status) }}
                                </span>
                            </div>
                            <div class="dmc-grid mb-2">
                                <div class="dmc-field">
                                    <div class="dmc-label">Tanggal Mengajar</div>
                                    <div class="dmc-value font-bold">{{ \Carbon\Carbon::parse($lp->tanggal)->format('d/m/Y') }}</div>
                                </div>
                                <div class="dmc-field">
                                    <div class="dmc-label">Jam Sesi</div>
                                    <div class="dmc-value text-success font-bold">{{ $lp->jam_mulai }} - {{ $lp->jam_selesai }}</div>
                                </div>
                            </div>
                            <div class="dmc-field">
                                <div class="dmc-label">Alasan Koreksi</div>
                                <div class="text-xs text-dark" style="background: var(--bg, #f8fafc); padding: 8px 10px; border-radius: 8px; border: 1px solid var(--border);">{{ $lp->alasan }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="tableEmptyCard">
                            <div class="tableEmptyIconWrap"><ion-icon name="time-outline"></ion-icon></div>
                            <div class="tableEmptyTitle">Tidak Ada Pengajuan Lupa Lapor</div>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- ── TAB 6: MATRIKS BUKU INDUK PRESENSI (1–31) ── --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @if($activeTab === 'matriks')
            <div class="sectionRow flex-between flex-wrap gap-2">
                <div>
                    <h2>Buku Induk Presensi Bulanan (Ledger Dapodik / Akreditasi)</h2>
                    <div class="text-xs text-muted">
                        Bulan: <b>{{ \Carbon\Carbon::create(null, $inputBulan, 1)->translatedFormat('F') }} {{ $inputTahun }}</b> &bull; Total: <b>{{ $matriksData['totalSiswa'] }} Murid Aktif</b>
                    </div>
                </div>
                <div class="d-flex items-center gap-2">
                    <span class="badge text-xs bg-success-light text-success font-bold px-2 py-1">H: Hadir</span>
                    <span class="badge text-xs bg-warning-light text-warning font-bold px-2 py-1">I: Izin</span>
                    <span class="badge text-xs bg-info-light text-info font-bold px-2 py-1">S: Sakit</span>
                    <span class="badge text-xs bg-danger-light text-danger font-bold px-2 py-1">A: Alpa</span>
            </div>

            {{-- Swipe Guide Hint untuk Mobile --}}
            <div class="matriksSwipeHint">
                <ion-icon name="swap-horizontal-outline"></ion-icon>
                <span>Geser tabel ke samping untuk melihat seluruh tanggal 1 s/d {{ $matriksData['daysInMonth'] }}</span>
            </div>

            <div class="tableContainer m-0 mb-4 rounded-xl border-base overflow-x-auto" style="max-width: 100%;">
                <table class="laporanTable table-matriks-ledger" style="font-size: 11px;">
                    <thead>
                        <tr>
                            <th class="p-2 w-8 text-center sticky-col-1">NO</th>
                            <th class="p-2 sticky-col-2" style="min-width: 160px;">NAMA MURID</th>
                            <th class="p-2" style="min-width: 110px;">KELAS</th>
                            @for($d = 1; $d <= $matriksData['daysInMonth']; $d++)
                                @php
                                    $dayCarbon = \Carbon\Carbon::createFromDate($inputTahun, $inputBulan, $d);
                                    $isSunday = ($dayCarbon->dayOfWeek === \Carbon\Carbon::SUNDAY);
                                @endphp
                                <th class="p-1 text-center {{ $isSunday ? 'bg-danger-subtle text-danger' : '' }}" style="width: 24px; min-width: 24px;">
                                    <div>{{ $d }}</div>
                                    <div style="font-size: 8px; font-weight: normal;">{{ $dayCarbon->isoFormat('dd') }}</div>
                                </th>
                            @endfor
                            <th class="p-1 text-center bg-success-light text-success font-bold">H</th>
                            <th class="p-1 text-center bg-warning-light text-warning font-bold">I</th>
                            <th class="p-1 text-center bg-info-light text-info font-bold">S</th>
                            <th class="p-1 text-center bg-danger-light text-danger font-bold">A</th>
                            <th class="p-2 text-center" style="min-width: 60px;">KEAKTIFAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matriksData['matrix'] as $index => $row)
                            @php
                                $murid = $row['murid'] ?? $row['siswa'];
                                $kelas = $murid->relKelas->nama_kelas ?? '-';
                            @endphp
                            <tr>
                                <td class="p-2 font-bold text-muted text-center sticky-col-1">{{ $index + 1 }}</td>
                                <td class="p-2 font-bold text-dark sticky-col-2 white-space-nowrap">{{ $murid->nama_murid ?? $murid->nama_siswa }}</td>
                                <td class="p-2 text-muted white-space-nowrap">{{ $kelas }}</td>
                                @foreach($row['days'] as $dNum => $dayInfo)
                                    @php
                                        $st = $dayInfo['status'];
                                        $cellClass = match($st) {
                                            'H' => 'bg-success-light text-success font-bold',
                                            'I' => 'bg-warning-light text-warning font-bold',
                                            'S' => 'bg-info-light text-info font-bold',
                                            'A' => 'bg-danger-light text-danger font-bold',
                                            'L' => 'bg-danger-subtle text-danger',
                                            default => 'text-muted'
                                        };
                                    @endphp
                                    <td class="p-1 text-center {{ $cellClass }}" style="width: 24px; font-size: 10px;">
                                        {{ $st }}
                                    </td>
                                @endforeach
                                <td class="p-1 text-center font-bold text-success">{{ $row['hadir'] }}</td>
                                <td class="p-1 text-center font-bold text-warning">{{ $row['izin'] }}</td>
                                <td class="p-1 text-center font-bold text-info">{{ $row['sakit'] }}</td>
                                <td class="p-1 text-center font-bold text-danger">{{ $row['alpha'] }}</td>
                                <td class="p-2 text-center font-bold {{ $row['keaktifan'] >= 80 ? 'text-success' : ($row['keaktifan'] >= 50 ? 'text-warning' : 'text-danger') }}">
                                    {{ $row['keaktifan'] }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 4 + $matriksData['daysInMonth'] + 5 }}" class="text-center p-6 text-muted">
                                    Tidak ada data murid aktif yang cocok dengan filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{-- ── Modal Foto ── --}}
        <div class="modal-overlay" id="photoModal" onclick="if(event.target===this)closeModal('photoModal')">
            <div class="modal-box">
                <div class="modal-header">
                    <span id="photoModalTitle">Foto Presensi</span>
                    <button class="modal-close" onclick="closeModal('photoModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <img id="photoModalImg" src="" alt="Foto presensi">
                </div>
            </div>
        </div>

        {{-- ── Modal Maps (Leaflet Interactive GIS) ── --}}
        <div class="modal-overlay" id="mapModal" onclick="if(event.target===this)closeModal('mapModal')">
            <div class="modal-box modal-box-map">
                <div class="modal-header-map">
                    <div class="modal-header-info">
                        <div class="modal-header-badge">VERIFIKASI GEOFENCE &amp; LOKASI</div>
                        <h3 id="mapModalTitle" class="modal-map-title">Verifikasi Lokasi Presensi</h3>
                        <div id="mapModalSub" class="modal-map-subtitle">Memuat koordinat GPS...</div>
                    </div>
                    <button type="button" class="modal-close" onclick="closeModal('mapModal')" aria-label="Tutup">&times;</button>
                </div>
                <div class="modal-body-map">
                    <div id="mapModalBadge" class="map-geofence-alert d-none"></div>
                    <div class="map-modal-frame-wrapper">
                        <div id="adminMapContainer" class="map-modal-leaflet"></div>
                    </div>
                    <div class="map-modal-info-grid">
                        <div class="map-modal-info-item">
                            <span class="map-info-lbl">Koordinat GPS Presensi</span>
                            <span id="mapModalCoords" class="map-info-val font-mono">-</span>
                        </div>
                        <div class="map-modal-info-item">
                            <span class="map-info-lbl">Pusat Titik &amp; Batas Radius</span>
                            <span id="mapModalTargetInfo" class="map-info-val">-</span>
                        </div>
                        <div class="map-modal-info-item full-width">
                            <div class="d-flex justify-between items-center flex-wrap gap-2">
                                <div class="text-xs text-muted">
                                    Jarak ke Titik Pusat: <strong id="mapModalDistance" class="text-dark">-</strong>
                                </div>
                                <a id="btnAdminGoogleMaps" href="#" target="_blank" class="btnNavMaps">
                                    <ion-icon name="open-outline"></ion-icon> Buka Google Maps
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Scripts & Charts Initialization ── --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Line Chart: Tren Kehadiran
            const trendCtx = document.getElementById('trendChart');
            if (trendCtx) {
                new Chart(trendCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($chartLabels) !!},
                        datasets: [
                            {
                                label: 'Hadir',
                                data: {!! json_encode($chartDataHadir) !!},
                                borderColor: '#16a34a',
                                backgroundColor: 'rgba(22, 163, 74, 0.1)',
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.35,
                                pointRadius: 3
                            },
                            {
                                label: 'Izin',
                                data: {!! json_encode($chartDataIzin) !!},
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.35,
                                pointRadius: 3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', align: 'end' }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } }
                        }
                    }
                });
            }

            // 2. Donut Chart: Moda Pembelajaran
            const modaCtx = document.getElementById('modaChart');
            if (modaCtx) {
                new Chart(modaCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($modaLabels) !!},
                        datasets: [{
                            data: {!! json_encode($modaData) !!},
                            backgroundColor: ['#10b981', '#f59e0b', '#0284c7'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }
        });

        // Map Constants

        const SEKOLAH_LAT = {{ config('lokasi.sekolah_lat', -6.9308526) }};
        const SEKOLAH_LNG = {{ config('lokasi.sekolah_lng', 107.6074633) }};
        const SEKOLAH_RADIUS = {{ config('lokasi.radius_meter', 100) }};
        const SEKOLAH_NAMA = @json(config('lokasi.sekolah_nama', 'PKBM Pikat'));

        let adminLeafletMap = null;
        let adminSekolahMarker = null;
        let adminPresensiMarker = null;
        let adminGeofenceCircle = null;
        let adminMeasureLine = null;

        function calcHaversine(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function openPhotoModal(src, title) {
            document.getElementById('photoModalImg').src = src;
            document.getElementById('photoModalTitle').textContent = title;
            document.getElementById('photoModal').classList.add('active');
        }

        function openMapModal(lokasi, nama, moda, titikNama, tLat, tLng, tRad) {
            var parts = (lokasi || '').split(',');
            if (parts.length < 2) {
                alert('Format koordinat lokasi tidak valid: ' + lokasi);
                return;
            }

            var lat = parseFloat(parts[0].trim());
            var lng = parseFloat(parts[1].trim());

            if (isNaN(lat) || isNaN(lng)) {
                alert('Koordinat GPS tidak valid.');
                return;
            }

            var targetLat = tLat ? parseFloat(tLat) : SEKOLAH_LAT;
            var targetLng = tLng ? parseFloat(tLng) : SEKOLAH_LNG;
            var targetRadius = tRad ? parseInt(tRad) : SEKOLAH_RADIUS;
            var targetNama = titikNama || SEKOLAH_NAMA;

            document.getElementById('mapModalTitle').textContent = 'Lokasi Presensi: ' + (nama || 'Presensi');
            document.getElementById('mapModalSub').textContent = 'Titik Absen: ' + targetNama + (moda ? ' • ' + moda : '');
            document.getElementById('mapModalCoords').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
            document.getElementById('mapModalTargetInfo').textContent = targetNama + ' (' + targetRadius + ' m)';
            document.getElementById('btnAdminGoogleMaps').href = 'https://www.google.com/maps?q=' + lat + ',' + lng;

            document.getElementById('mapModal').classList.add('active');

            setTimeout(function () {
                initAdminMap(lat, lng, nama, moda, targetLat, targetLng, targetRadius, targetNama);
            }, 50);
        }

        function initAdminMap(lat, lng, nama, moda, targetLat, targetLng, targetRadius, targetNama) {
            var container = document.getElementById('adminMapContainer');
            if (!container) return;

            if (typeof window.L === 'undefined') {
                setTimeout(function () {
                    initAdminMap(lat, lng, nama, moda, targetLat, targetLng, targetRadius, targetNama);
                }, 100);
                return;
            }

            var dist = calcHaversine(lat, lng, targetLat, targetLng);
            var distFormatted = dist.toFixed(1);
            var isWithin = dist <= targetRadius;

            var distEl = document.getElementById('mapModalDistance');
            if (distEl) distEl.textContent = distFormatted + ' Meter';

            var badge = document.getElementById('mapModalBadge');
            if (badge) {
                badge.classList.remove('d-none', 'within', 'outside', 'unrestricted');
                var isUnrestricted = (moda && (moda.toLowerCase().includes('online') || moda.toLowerCase().includes('home visit')));

                if (isUnrestricted) {
                    badge.classList.add('unrestricted');
                    badge.innerHTML = '<ion-icon name="information-circle"></ion-icon> <span><b>Moda ' + moda + '</b> (Bebas Radius Geofence &bull; Jarak: ' + distFormatted + ' m)</span>';
                } else if (isWithin) {
                    badge.classList.add('within');
                    badge.innerHTML = '<ion-icon name="checkmark-circle"></ion-icon> <span><b>Di Dalam Radius</b> (' + distFormatted + ' m &bull; Batas: ' + targetRadius + ' m)</span>';
                } else {
                    badge.classList.add('outside');
                    badge.innerHTML = '<ion-icon name="alert-circle"></ion-icon> <span><b>Di Luar Radius</b> (' + distFormatted + ' m &bull; Batas: ' + targetRadius + ' m)</span>';
                }
            }

            var targetPin = L.divIcon({
                className: 'custom-leaflet-marker',
                html: '<div style="background:#ef4444;width:24px;height:24px;border-radius:50%;border:3px solid #ffffff;box-shadow:0 2px 8px rgba(0,0,0,0.35);"></div>',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });

            var userPin = L.divIcon({
                className: 'custom-leaflet-marker',
                html: '<div style="background:#0284c7;width:24px;height:24px;border-radius:50%;border:3px solid #ffffff;box-shadow:0 2px 8px rgba(0,0,0,0.35);"></div>',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });

            if (!adminLeafletMap) {
                adminLeafletMap = L.map('adminMapContainer', { zoomControl: true, scrollWheelZoom: false }).setView([targetLat, targetLng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(adminLeafletMap);

                adminSekolahMarker = L.marker([targetLat, targetLng], { icon: targetPin }).addTo(adminLeafletMap);
                adminSekolahMarker.bindPopup('<b>' + targetNama + '</b><br>Pusat Geofence (' + targetRadius + ' m)');

                adminGeofenceCircle = L.circle([targetLat, targetLng], {
                    color: '#0284c7', fillColor: '#38bdf8', fillOpacity: 0.2, radius: targetRadius
                }).addTo(adminLeafletMap);
            } else {
                adminSekolahMarker.setLatLng([targetLat, targetLng]);
                adminGeofenceCircle.setLatLng([targetLat, targetLng]).setRadius(targetRadius);
            }

            if (adminPresensiMarker) {
                adminPresensiMarker.setLatLng([lat, lng]);
            } else {
                adminPresensiMarker = L.marker([lat, lng], { icon: userPin }).addTo(adminLeafletMap);
            }

            if (adminMeasureLine) {
                adminMeasureLine.setLatLngs([[lat, lng], [targetLat, targetLng]]);
            } else {
                adminMeasureLine = L.polyline([[lat, lng], [targetLat, targetLng]], {
                    color: isWithin ? '#10b981' : '#ef4444', weight: 3, dashArray: '6, 6'
                }).addTo(adminLeafletMap);
            }

            var bounds = L.latLngBounds([[targetLat, targetLng], [lat, lng]]);
            adminLeafletMap.fitBounds(bounds, { padding: [40, 40] });
            setTimeout(() => { if (adminLeafletMap) adminLeafletMap.invalidateSize(); }, 100);
        }

        function toggleMobileFilter() {
            var formBody = document.querySelector('.laporanFilterFormBody');
            var btn = document.getElementById('btnToggleFilterMobile');
            var text = document.getElementById('filterToggleText');
            var icon = document.getElementById('filterToggleIcon');
            if (!formBody) return;

            var isOpened = formBody.classList.toggle('filter-open');
            if (btn) btn.setAttribute('aria-expanded', isOpened ? 'true' : 'false');
            if (text) text.textContent = isOpened ? 'Tutup Filter' : 'Buka Filter';
            if (icon) icon.setAttribute('name', isOpened ? 'chevron-up-outline' : 'chevron-down-outline');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
            if (id === 'photoModal') document.getElementById('photoModalImg').src = '';
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeModal('photoModal');
                closeModal('mapModal');
            }
        });
    </script>
@endsection