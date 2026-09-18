@extends('layouts.admin')

@section('title', 'Laporan Presensi & KBM — Admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- Header --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">LAPORAN OPERASIONAL &amp; KBM</div>
                <div class="laporanHeaderTitle">Rekapitulasi Presensi Tutor</div>
                <div class="laporanHeaderSub">
                    Periode: {{ \Carbon\Carbon::parse($inputStartDate)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($inputEndDate)->translatedFormat('d M Y') }}
                </div>
                <p class="laporanHeaderDesc">
                    Monitoring log kehadiran, jam masuk/keluar, moda pembelajaran, dan bukti foto GPS tutor untuk evaluasi akademik &amp; akreditasi.
                </p>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.payroll.index') }}" class="btnPayrollShortcut">
                    <ion-icon name="wallet-outline"></ion-icon>
                    <span >Buka Rekap Payroll &amp; Honor</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ── Filter Card ── --}}
    <div class="laporanFilterCard">
        <form method="GET" action="{{ route('admin.laporan.index') }}">
            <div class="laporanFilterGrid">
                <div class="filterField">
                    <label class="filterFieldLabel">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $inputStartDate }}" class="profileInput">
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $inputEndDate }}" class="profileInput">
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Tutor</label>
                    <select name="tutor_id" class="filterSelect">
                        <option value="">Semua Tutor</option>
                        @foreach ($tutors as $tutor)
                            <option value="{{ $tutor->id }}" {{ $tutorId == $tutor->id ? 'selected' : '' }}>
                                {{ $tutor->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Siswa</label>
                    <select name="siswa_id" class="filterSelect">
                        <option value="">Semua Siswa</option>
                        @foreach ($siswas as $siswa)
                            <option value="{{ $siswa->id }}" {{ $siswaId == $siswa->id ? 'selected' : '' }}>
                                {{ $siswa->nama_siswa }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Status Presensi</label>
                    <select name="status" class="filterSelect">
                        <option value="">Semua Status</option>
                        <option value="hadir" {{ $statusFilter == 'hadir' ? 'selected' : '' }}>Hadir (Selesai)</option>
                        <option value="proses" {{ $statusFilter == 'proses' ? 'selected' : '' }}>Sedang Berjalan</option>
                        <option value="izin" {{ $statusFilter == 'izin' ? 'selected' : '' }}>Izin / Sakit</option>
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
                        <ion-icon name="document-text-outline"></ion-icon> PDF
                    </button>
                    <button type="button" onclick="document.getElementById('importPresensiModal').style.display='flex'" class="btn-filter-info">
                        <ion-icon name="cloud-upload-outline"></ion-icon> Import Log
                    </button>
                    <a href="{{ route('admin.laporan.index') }}" class="btn-filter-reset">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Modal Impor Presensi Retroaktif / Log Manual ── --}}
    <div id="importPresensiModal" class="app-modal-backdrop" onclick="if(event.target===this) this.style.display='none'">
        <div class="app-modal-card">
            <div class="app-modal-header">
                <div class="app-modal-header-left">
                    <div class="app-modal-badge-icon">
                        <ion-icon name="time-outline"></ion-icon>
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
                            <span class="fileUploadInfoPill">Format: .XLSX, .CSV</span>
                            <span class="fileUploadInfoPill">Maks: 5 MB</span>
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

    <script>
        function handleExcelFileSelected(input, feedbackId) {
            const feedback = document.getElementById(feedbackId);
            if (!feedback) return;
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const sizeKb = Math.round(file.size / 1024);
                const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
                const sizeText = sizeKb > 1024 ? `${sizeMb} MB` : `${sizeKb} KB`;

                if (file.size > 5 * 1024 * 1024) {
                    feedback.style.display = 'block';
                    feedback.innerHTML = `
                        <div class="file-selected-card" style="border-color: #fca5a5; background: rgba(239, 68, 68, 0.1);">
                            <div class="fsc-info">
                                <ion-icon name="alert-circle-outline" class="fsc-icon" style="color: #dc2626;"></ion-icon>
                                <div class="fsc-details">
                                    <div class="fsc-name">${file.name}</div>
                                    <div class="text-xs font-bold" style="color: #dc2626;">Ukuran (${sizeText}) melebihi batas maksimal 5 MB!</div>
                                </div>
                            </div>
                            <button type="button" class="fsc-remove-btn" onclick="clearSelectedExcel('${input.id}', '${feedbackId}')" title="Hapus">
                                <ion-icon name="close-circle-outline"></ion-icon>
                            </button>
                        </div>`;
                    input.value = '';
                    return;
                }

                feedback.style.display = 'block';
                feedback.innerHTML = `
                    <div class="file-selected-card">
                        <div class="fsc-info">
                            <ion-icon name="document-attach-outline" class="fsc-icon"></ion-icon>
                            <div class="fsc-details">
                                <div class="fsc-name">${file.name}</div>
                                <div class="fsc-meta">${sizeText} &bull; Berkas siap diunggah</div>
                            </div>
                        </div>
                        <button type="button" class="fsc-remove-btn" onclick="clearSelectedExcel('${input.id}', '${feedbackId}')" title="Ganti berkas">
                            <ion-icon name="close-circle-outline"></ion-icon>
                        </button>
                    </div>`;
            } else {
                feedback.style.display = 'none';
                feedback.innerHTML = '';
            }
        }

        function clearSelectedExcel(inputId, feedbackId) {
            const input = document.getElementById(inputId);
            const feedback = document.getElementById(feedbackId);
            if (input) input.value = '';
            if (feedback) {
                feedback.style.display = 'none';
                feedback.innerHTML = '';
            }
        }
    </script>

    {{-- ── Summary Stats ── --}}
    <div class="statRow">
        <div class="statCard">
            <div class="statNum green">{{ $totalHadir }}</div>
            <div class="statLabel">Total Hadir</div>
        </div>
        <div class="statCard">
            <div class="statNum red">{{ $totalIzin }}</div>
            <div class="statLabel">Total Izin / Sakit</div>
        </div>
        <div class="statCard">
            <div class="statNum blue">{{ $totalHadir + $totalIzin }}</div>
            <div class="statLabel">Total Rekapan Presensi</div>
        </div>
    </div>

    {{-- ── Chart Section ── --}}
    <div class="sectionRow">
        <h2>Analisis Visual Kehadiran</h2>
    </div>
    <div class="chartContainer">
        <div class="chartCard">
            <div class="chartTitle">Tren Kehadiran Harian</div>
            <div class="chartWrapper">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Jadwal per Tutor ── --}}
    @if ($perTutor->isNotEmpty())
        <div class="sectionRow">
            <h2>Frekuensi KBM per Tutor</h2>
        </div>
        @php $maxJadwal = $perTutor->max('total_jadwal') ?: 1; @endphp
        <div class="tutorList">
            @foreach ($perTutor as $tutor)
                @php
                    $pct = (int) round(($tutor->total_jadwal / $maxJadwal) * 100);
                @endphp
                <div class="tutorCard">
                    <div class="tutorCardTop">
                        <div class="tutorCardName">{{ $tutor->nama_lengkap }}</div>
                        <div class="tutorCardCount">{{ $tutor->total_jadwal }} sesi</div>
                    </div>
                    <div class="progressBg">
                        <div class="progressFill" style="width: {{ $pct }}%;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── Recent Presensi Tutor (Desktop Table) ── --}}
    <div class="sectionRow">
        <h2>Monitoring Presensi Tutor</h2>
        <span class="badgeCount">{{ count($recentPresensi) }} Log Terkini</span>
    </div>
    <div class="table-responsive-desktop">
        <div class="tableContainer m-0 mb-4 rounded-xl border-base">
            <table class="laporanTable">
                <thead>
                    <tr>
                        <th class="table-col-num">NO</th>
                        <th>TANGGAL</th>
                        <th>NAMA TUTOR</th>
                        <th>NAMA SISWA</th>
                        <th>MASUK</th>
                        <th>SELESAI</th>
                        <th>FOTO BUKTI</th>
                        <th class="text-center">LOKASI GPS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPresensi as $index => $presensi)
                        @php
                            $tutor = $presensi->tutor;
                            $tutorName = $tutor->nama_lengkap ?? 'Tutor';
                            $siswaName = $presensi->siswa->nama_siswa ?? '-';
                            $tgl = \Carbon\Carbon::parse($presensi->tgl_presensi)->format('d/m/y');
                            $jamMasuk = $presensi->jam_mulai
                                ? \Carbon\Carbon::parse($presensi->jam_mulai)->format('H:i')
                                : '-';
                            $jamSelesai = $presensi->jam_selesai
                                ? \Carbon\Carbon::parse($presensi->jam_selesai)->format('H:i')
                                : '-';

                            $lokasi = $presensi->lokasi_mulai ?? '-';
                        @endphp
                        <tr>
                            <td class="p-3 font-bold text-muted">{{ $index + 1 }}</td>
                            <td class="font-bold white-space-nowrap">{{ $tgl }}</td>
                            <td class="font-semibold text-dark">{{ $tutorName }}</td>
                            <td>{{ $siswaName }}</td>
                            <td class="font-bold text-success">{{ $jamMasuk }}</td>
                            <td class="font-bold text-primary">{{ $jamSelesai }}</td>
                            <td>
                                <div class="fotoStack">
                                    @if ($presensi->foto_mulai)
                                        <img src="{{ asset($presensi->foto_mulai) }}" class="fotoThumbnail cursor-pointer"
                                            title="Foto Mulai"
                                            onclick="openPhotoModal('{{ asset($presensi->foto_mulai) }}', 'Foto Masuk — {{ $tutorName }}')">
                                    @else
                                        <div class="fotoPlaceholder" title="Belum ada foto masuk">M -</div>
                                    @endif

                                    @if ($presensi->foto_selesai)
                                        <img src="{{ asset($presensi->foto_selesai) }}" class="fotoThumbnail cursor-pointer"
                                            title="Foto Selesai"
                                            onclick="openPhotoModal('{{ asset($presensi->foto_selesai) }}', 'Foto Pulang — {{ $tutorName }}')">
                                    @else
                                        <div class="fotoPlaceholder" title="Belum ada foto selesai">S -</div>
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
                                    @if($tNama)
                                        <div class="text-xs text-primary font-bold mt-1 d-flex items-center justify-center gap-1">
                                            <ion-icon name="location-sharp"></ion-icon> {{ $tNama }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="table-empty-cell">
                                <ion-icon name="calendar-outline" class="icon-2xl d-block mx-auto mb-2 opacity-40"></ion-icon>
                                <div class="font-bold text-md mb-1">Belum Ada Data Presensi</div>
                                <div class="text-sm">Tidak ada rekaman aktivitas mengajar pada periode filter yang dipilih.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Recent Presensi Tutor (Mobile Cards) ── --}}
    <div class="mobile-card-list">
        @forelse($recentPresensi as $index => $presensi)
            @php
                $tutor = $presensi->tutor;
                $tutorName = $tutor->nama_lengkap ?? 'Tutor';
                $siswaName = $presensi->siswa->nama_siswa ?? '-';
                $tgl = \Carbon\Carbon::parse($presensi->tgl_presensi)->format('d/m/Y');
                $jamMasuk = $presensi->jam_mulai ? \Carbon\Carbon::parse($presensi->jam_mulai)->format('H:i') : '-';
                $jamSelesai = $presensi->jam_selesai ? \Carbon\Carbon::parse($presensi->jam_selesai)->format('H:i') : '-';
                $lokasi = $presensi->lokasi_mulai ?? '-';
            @endphp
            <div class="data-mobile-card">
                <div class="dmc-header">
                    <div>
                        <h3 class="dmc-title">{{ $tutorName }}</h3>
                        <div class="dmc-subtitle">{{ $tgl }} &bull; Siswa: <b class="text-dark">{{ $siswaName }}</b></div>
                    </div>
                    <span class="text-xs font-bold text-muted">
                        #{{ $index + 1 }}
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
                                <img src="{{ asset($presensi->foto_mulai) }}" class="fotoThumbnail cursor-pointer" title="Foto Mulai"
                                    onclick="openPhotoModal('{{ asset($presensi->foto_mulai) }}', 'Foto Masuk — {{ $tutorName }}')">
                            @endif
                            @if ($presensi->foto_selesai)
                                <img src="{{ asset($presensi->foto_selesai) }}" class="fotoThumbnail cursor-pointer" title="Foto Selesai"
                                    onclick="openPhotoModal('{{ asset($presensi->foto_selesai) }}', 'Foto Selesai — {{ $tutorName }}')">
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
                        <div class="d-flex items-center gap-2">
                            <button type="button" class="mapBtn" title="Lihat Peta" onclick="openMapModal('{{ $lokasi }}', '{{ addslashes($tutorName) }}', '{{ $presensi->moda_label ?? 'Tatap Muka' }}', '{{ addslashes($tNama) }}', '{{ $tLat }}', '{{ $tLng }}', '{{ $tRad }}')">
                                <ion-icon name="map-outline"></ion-icon>
                            </button>
                            @if($tNama)
                                <span class="text-xs text-primary font-bold"><ion-icon name="location-sharp"></ion-icon> {{ $tNama }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="table-empty-cell bg-card rounded-xl border-base p-4 text-center">
                <ion-icon name="calendar-outline" class="icon-2xl d-block mx-auto mb-2 opacity-40"></ion-icon>
                <div class="font-bold text-md mb-1">Belum Ada Data Presensi</div>
                <div class="text-sm text-muted">Tidak ada rekaman aktivitas mengajar pada periode filter yang dipilih.</div>
            </div>
        @endforelse
    </div>

    {{-- ── Karyawan Presensi (Desktop Table & Mobile Cards) ── --}}
    @if ($karyawanPresensi->isNotEmpty())
        <div class="card mb-4 mt-6">
            <div class="cardTitle flex-between flex-wrap gap-2">
                <div class="d-flex items-center gap-2">
                    <ion-icon name="people-outline"></ion-icon> Rekap Presensi Karyawan, Admin & Kepala Sekolah
                </div>
                <span class="badge text-xs bg-primary-light text-primary font-extrabold rounded-pill px-3 py-1">
                    {{ $karyawanPresensi->count() }} Rekaman
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="table-compact w-full text-left">
                    <thead>
                        <tr>
                            <th class="p-3 w-10">No</th>
                            <th>Tanggal</th>
                            <th>Nama Pegawai</th>
                            <th>Role</th>
                            <th>Jam Masuk</th>
                            <th>Jam Selesai</th>
                            <th>Foto</th>
                            <th class="text-center">Lokasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($karyawanPresensi as $index => $kp)
                            @php
                                $kpTgl = \Carbon\Carbon::parse($kp->tgl_presensi)->format('d/m/Y');
                                $kpJamMasuk = $kp->jam_mulai ? \Carbon\Carbon::parse($kp->jam_mulai)->format('H:i') : '-';
                                $kpJamSelesai = $kp->jam_selesai ? \Carbon\Carbon::parse($kp->jam_selesai)->format('H:i') : '-';
                                $kpName = $kp->user->nama_lengkap;
                                $kpRole = ucfirst($kp->user->role);
                                $lokasi = $kp->lokasi_mulai;
                                $kpTitik = $kp->lokasiPresensi;
                                $kpTitikNama = $kpTitik?->nama_lokasi ?? '';
                                $kpLat = $kpTitik?->latitude ?? '';
                                $kpLng = $kpTitik?->longitude ?? '';
                                $kpRad = $kpTitik?->radius_meter ?? '';
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
                                            <img src="{{ asset($kp->foto_mulai) }}" class="fotoThumbnail cursor-pointer"
                                                title="Foto Mulai"
                                                onclick="openPhotoModal('{{ asset($kp->foto_mulai) }}', 'Foto Masuk — {{ $kpName }}')">
                                        @else
                                            <div class="fotoPlaceholder">M -</div>
                                        @endif

                                        @if ($kp->foto_selesai)
                                            <img src="{{ asset($kp->foto_selesai) }}" class="fotoThumbnail cursor-pointer"
                                                title="Foto Selesai"
                                                onclick="openPhotoModal('{{ asset($kp->foto_selesai) }}', 'Foto Pulang — {{ $kpName }}')">
                                        @else
                                            <div class="fotoPlaceholder">S -</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($lokasi && $lokasi !== '-')
                                        <button type="button" class="mapBtn" title="Lihat Peta" onclick="openMapModal('{{ $lokasi }}', '{{ addslashes($kpName) }}', 'Karyawan / {{ $kpRole }}', '{{ addslashes($kpTitikNama) }}', '{{ $kpLat }}', '{{ $kpLng }}', '{{ $kpRad }}')">
                                            <ion-icon name="map-outline"></ion-icon>
                                        </button>
                                        @if($kpTitikNama)
                                            <div class="text-xs text-primary font-bold mt-1 d-flex items-center justify-center gap-1">
                                                <ion-icon name="location-sharp"></ion-icon> {{ $kpTitikNama }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile Cards for Karyawan Presensi --}}
        <div class="mobile-card-list">
            @foreach ($karyawanPresensi as $index => $kp)
                @php
                    $kpTgl = \Carbon\Carbon::parse($kp->tgl_presensi)->format('d/m/Y');
                    $kpJamMasuk = $kp->jam_mulai ? \Carbon\Carbon::parse($kp->jam_mulai)->format('H:i') : '-';
                    $kpJamSelesai = $kp->jam_selesai ? \Carbon\Carbon::parse($kp->jam_selesai)->format('H:i') : '-';
                    $kpName = $kp->user->nama_lengkap;
                    $kpRole = ucfirst($kp->user->role);
                    $lokasi = $kp->lokasi_mulai;
                    $kpTitik = $kp->lokasiPresensi;
                    $kpTitikNama = $kpTitik?->nama_lokasi ?? '';
                    $kpLat = $kpTitik?->latitude ?? '';
                    $kpLng = $kpTitik?->longitude ?? '';
                    $kpRad = $kpTitik?->radius_meter ?? '';
                @endphp
                <div class="data-mobile-card">
                    <div class="dmc-header">
                        <div>
                            <h3 class="dmc-title">{{ $kpName }}</h3>
                            <div class="dmc-subtitle">{{ $kpTgl }} &bull; Role: <b class="text-dark">{{ $kpRole }}</b></div>
                        </div>
                        <span class="text-xs font-bold text-muted">
                            #{{ $index + 1 }}
                        </span>
                    </div>

                    <div class="dmc-grid">
                        <div class="dmc-field">
                            <div class="dmc-label">Jam Masuk</div>
                            <div class="dmc-value text-success">{{ $kpJamMasuk }}</div>
                        </div>

                        <div class="dmc-field">
                            <div class="dmc-label">Jam Selesai</div>
                            <div class="dmc-value text-primary">{{ $kpJamSelesai }}</div>
                        </div>
                    </div>

                    <div class="dmc-footer">
                        <div class="d-flex items-center gap-1">
                            <span class="dmc-label mb-0">Foto:</span>
                            <div class="fotoStack">
                                @if ($kp->foto_mulai)
                                    <img src="{{ asset($kp->foto_mulai) }}" class="fotoThumbnail cursor-pointer" title="Foto Mulai"
                                        onclick="openPhotoModal('{{ asset($kp->foto_mulai) }}', 'Foto Masuk — {{ $kpName }}')">
                                @endif
                                @if ($kp->foto_selesai)
                                    <img src="{{ asset($kp->foto_selesai) }}" class="fotoThumbnail cursor-pointer" title="Foto Selesai"
                                        onclick="openPhotoModal('{{ asset($kp->foto_selesai) }}', 'Foto Selesai — {{ $kpName }}')">
                                @endif
                            </div>
                        </div>

                        @if ($lokasi && $lokasi !== '-')
                            <div class="d-flex items-center gap-2">
                                <button type="button" class="mapBtn" title="Lihat Peta" onclick="openMapModal('{{ $lokasi }}', '{{ addslashes($kpName) }}', 'Karyawan / {{ $kpRole }}', '{{ addslashes($kpTitikNama) }}', '{{ $kpLat }}', '{{ $kpLng }}', '{{ $kpRad }}')">
                                    <ion-icon name="map-outline"></ion-icon>
                                </button>
                                @if($kpTitikNama)
                                    <span class="text-xs text-primary font-bold"><ion-icon name="location-sharp"></ion-icon> {{ $kpTitikNama }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script >
        document.addEventListener('DOMContentLoaded', function() {
            const trendCtx = document.getElementById('trendChart');
            if (!trendCtx) return;

            new Chart(trendCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartLabels) !!},
                    datasets: [{
                            label: 'Hadir',
                            data: {!! json_encode($chartDataHadir) !!},
                            borderColor: '#16a34a',
                            backgroundColor: 'rgba(22, 163, 74, 0.1)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Izin',
                            data: {!! json_encode($chartDataIzin) !!},
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    resizeDelay: 50,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                usePointStyle: true,
                                font: {
                                    size: 11,
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            padding: 10,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 12,
                                font: {
                                    size: 10
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                precision: 0,
                                stepSize: 1,
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

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

    <script>
        const SEKOLAH_LAT = {{ config('lokasi.sekolah_lat', -7.8011945) }};
        const SEKOLAH_LNG = {{ config('lokasi.sekolah_lng', 110.364917) }};
        const SEKOLAH_RADIUS = {{ config('lokasi.radius_meter', 100) }};
        const SEKOLAH_NAMA = @json(config('lokasi.sekolah_nama', 'PKBM Pikat'));

        let adminLeafletMap = null;
        let adminSekolahMarker = null;
        let adminPresensiMarker = null;
        let adminGeofenceCircle = null;
        let adminMeasureLine = null;

        function ensureLeafletLoaded(callback) {
            callback();
        }

        function calcHaversine(lat1, lon1, lat2, lon2) {
            if (typeof window.haversineDistance === 'function') {
                return window.haversineDistance(lat1, lon1, lat2, lon2);
            }
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
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
            document.getElementById('mapModalSub').textContent = 'Titik Absen: ' + targetNama + (moda ? ' • Moda: ' + moda : '');
            document.getElementById('mapModalCoords').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
            document.getElementById('mapModalTargetInfo').textContent = targetNama + ' (' + targetRadius + ' m)';
            document.getElementById('btnAdminGoogleMaps').href = 'https://www.google.com/maps?q=' + lat + ',' + lng;

            document.getElementById('mapModal').classList.add('active');

            ensureLeafletLoaded(function() {
                setTimeout(function() {
                    initAdminMap(lat, lng, nama, moda, targetLat, targetLng, targetRadius, targetNama);
                }, 50);
            });
        }

        function initAdminMap(lat, lng, nama, moda, targetLat, targetLng, targetRadius, targetNama) {
            var container = document.getElementById('adminMapContainer');
            if (!container || typeof L === 'undefined') return;

            targetLat = targetLat || SEKOLAH_LAT;
            targetLng = targetLng || SEKOLAH_LNG;
            targetRadius = targetRadius || SEKOLAH_RADIUS;
            targetNama = targetNama || SEKOLAH_NAMA;

            var dist = calcHaversine(lat, lng, targetLat, targetLng);
            var distFormatted = dist.toFixed(1);
            var isWithin = dist <= targetRadius;

            var distEl = document.getElementById('mapModalDistance');
            if (distEl) {
                distEl.textContent = distFormatted + ' Meter';
            }

            var badge = document.getElementById('mapModalBadge');
            if (badge) {
                badge.classList.remove('d-none', 'within', 'outside', 'unrestricted');
                var isUnrestricted = (moda && (moda.toLowerCase().includes('online') || moda.toLowerCase().includes('home visit')));

                if (isUnrestricted) {
                    badge.classList.add('unrestricted');
                    badge.innerHTML = '<ion-icon name="information-circle"></ion-icon> <span><b>Moda ' + moda + '</b> (Bebas Radius Geofence &bull; Jarak: ' + distFormatted + ' m dari ' + targetNama + ')</span>';
                } else if (isWithin) {
                    badge.classList.add('within');
                    badge.innerHTML = '<ion-icon name="checkmark-circle"></ion-icon> <span><b>Presensi Terverifikasi di Dalam Radius</b> (' + distFormatted + ' m dari ' + targetNama + ' &bull; Batas Maks: ' + targetRadius + ' m)</span>';
                } else {
                    badge.classList.add('outside');
                    badge.innerHTML = '<ion-icon name="alert-circle"></ion-icon> <span><b>Presensi Terdeteksi di Luar Radius</b> (' + distFormatted + ' m dari ' + targetNama + ' &bull; Batas Maks: ' + targetRadius + ' m)</span>';
                }
            }

            var targetPin = L.divIcon({
                className: 'custom-leaflet-marker',
                html: '<div style="background:#ef4444;width:24px;height:24px;border-radius:50%;border:3px solid #ffffff;box-shadow:0 2px 8px rgba(0,0,0,0.35);display:flex;align-items:center;justify-content:center;"><div style="width:6px;height:6px;background:#ffffff;border-radius:50%;"></div></div>',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });

            var userPin = L.divIcon({
                className: 'custom-leaflet-marker',
                html: '<div style="background:#0284c7;width:24px;height:24px;border-radius:50%;border:3px solid #ffffff;box-shadow:0 2px 8px rgba(0,0,0,0.35);display:flex;align-items:center;justify-content:center;"><div style="width:6px;height:6px;background:#ffffff;border-radius:50%;"></div></div>',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });

            if (!adminLeafletMap) {
                adminLeafletMap = L.map('adminMapContainer', {
                    zoomControl: true,
                    scrollWheelZoom: false
                }).setView([targetLat, targetLng], 16);

                if (window.setupLeafletTileTheme) {
                    window.setupLeafletTileTheme(adminLeafletMap, 'adminMapContainer');
                } else {
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(adminLeafletMap);
                }

                adminSekolahMarker = L.marker([targetLat, targetLng], { icon: targetPin }).addTo(adminLeafletMap);
                adminSekolahMarker.bindPopup('<b>' + targetNama + '</b><br><span style="font-size:11px;">Pusat Geofence (Radius ' + targetRadius + ' m)</span>');

                adminGeofenceCircle = L.circle([targetLat, targetLng], {
                    color: '#0284c7',
                    fillColor: '#38bdf8',
                    fillOpacity: 0.2,
                    radius: targetRadius
                }).addTo(adminLeafletMap);
            } else {
                adminSekolahMarker.setLatLng([targetLat, targetLng]);
                adminSekolahMarker.setIcon(targetPin);
                adminSekolahMarker.setPopupContent('<b>' + targetNama + '</b><br><span style="font-size:11px;">Pusat Geofence (Radius ' + targetRadius + ' m)</span>');
                adminGeofenceCircle.setLatLng([targetLat, targetLng]);
                adminGeofenceCircle.setRadius(targetRadius);
            }

            if (adminPresensiMarker) {
                adminPresensiMarker.setLatLng([lat, lng]);
                adminPresensiMarker.setIcon(userPin);
            } else {
                adminPresensiMarker = L.marker([lat, lng], { icon: userPin }).addTo(adminLeafletMap);
            }
            adminPresensiMarker.bindPopup('<b>' + (nama || 'Presensi') + '</b><br><span style="font-size:11px;">Jarak ke ' + targetNama + ': ' + distFormatted + ' meter<br>Moda: ' + (moda || 'Tatap Muka') + '</span>');

            if (adminMeasureLine) {
                adminMeasureLine.setLatLngs([[lat, lng], [targetLat, targetLng]]);
            } else {
                adminMeasureLine = L.polyline([[lat, lng], [targetLat, targetLng]], {
                    color: isWithin ? '#10b981' : '#ef4444',
                    weight: 3,
                    dashArray: '6, 6',
                    opacity: 0.8
                }).addTo(adminLeafletMap);
            }

            adminMeasureLine.setStyle({
                color: isWithin ? '#10b981' : '#ef4444',
                dashArray: '6, 6'
            });

            var bounds = L.latLngBounds([[targetLat, targetLng], [lat, lng]]);
            adminLeafletMap.fitBounds(bounds, { padding: [40, 40] });

            setTimeout(function() {
                if (adminLeafletMap) adminLeafletMap.invalidateSize();
            }, 100);
            setTimeout(function() {
                if (adminLeafletMap) adminLeafletMap.invalidateSize();
            }, 300);
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
            if (id === 'photoModal') document.getElementById('photoModalImg').src = '';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('photoModal');
                closeModal('mapModal');
            }
        });
    </script>
@endsection
