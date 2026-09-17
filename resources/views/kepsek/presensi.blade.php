@extends('layouts.kepsek')

@section('title', 'Monitoring Presensi Tutor — Kepala Sekolah')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="laporanPageWrapper">
    {{-- ── Header ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">LOG AKTIVITAS KBM</div>
                <h1 class="laporanHeaderTitle">Monitoring Presensi Mengajar</h1>
                <div class="laporanHeaderSub">Rekam log kehadiran, bukti foto GPS, dan sesi pembelajaran tutor PKBM</div>
                <p class="laporanHeaderDesc">Pantau jam masuk, jam selesai, status moda pembelajaran, dan verifikasi geolokasi.</p>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('kepsek.laporan') }}" class="btnPayrollShortcut btn-action-info">
                    <ion-icon name="document-text-outline"></ion-icon>
                    <span>Buka Rekapitulasi Laporan</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ── Filter Card ── --}}
    <div class="laporanFilterCard">
        <form method="GET" action="{{ route('kepsek.presensi-tutor') }}">
            <div class="laporanFilterGrid">
                <div class="filterField">
                    <label class="filterFieldLabel">Dari Tanggal</label>
                    <input type="date" name="start_date" class="profileInput" value="{{ $startDateStr }}">
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="profileInput" value="{{ $endDateStr }}">
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
                        <ion-icon name="filter-outline"></ion-icon> Terapkan Filter
                    </button>
                    <a href="{{ route('kepsek.presensi-tutor') }}" class="btn-filter-reset">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Section Title ── --}}
    <div class="sectionRow">
        <h2>Log Presensi Tutor &amp; Siswa</h2>
        <span class="badgeCount">{{ $presensi->total() }} Sesi Terdata</span>
    </div>

    {{-- ── Log Table Container (Desktop) ── --}}
    <div class="table-responsive-desktop">
        <div class="tableContainer m-0 mb-4 rounded-xl border-base">
            <table class="laporanTable">
                <thead>
                    <tr>
                        <th class="table-col-num">NO</th>
                        <th>TANGGAL</th>
                        <th>TUTOR</th>
                        <th>SISWA</th>
                        <th>JAM MASUK</th>
                        <th>JAM SELESAI</th>
                        <th>FOTO BUKTI</th>
                        <th class="text-center">GPS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($presensi as $index => $item)
                        @php
                            $tutorName = $item->tutor->nama_lengkap ?? 'Tutor';
                            $siswaName = $item->siswa->nama_siswa ?? '-';
                            $tgl = \Carbon\Carbon::parse($item->tgl_presensi)->format('d/m/Y');
                            $jamMasuk = $item->jam_mulai ? \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') : '-';
                            $jamSelesai = $item->jam_selesai
                                ? \Carbon\Carbon::parse($item->jam_selesai)->format('H:i')
                                : '-';

                            $lokasi = $item->lokasi_mulai ?? '-';
                            $tNama = $item->lokasiPresensi ? $item->lokasiPresensi->nama_lokasi : config('lokasi.sekolah_nama', 'PKBM Pikat');
                            $tLat = $item->lokasiPresensi ? $item->lokasiPresensi->latitude : config('lokasi.sekolah_lat', -7.8011945);
                            $tLng = $item->lokasiPresensi ? $item->lokasiPresensi->longitude : config('lokasi.sekolah_lng', 110.364917);
                            $tRad = $item->lokasiPresensi ? $item->lokasiPresensi->radius_meter : config('lokasi.radius_meter', 100);
                            $modaLabel = $item->moda_label ?? 'Tatap Muka';
                        @endphp
                        <tr>
                            <td class="p-3 font-bold text-muted">{{ $presensi->firstItem() + $index }}</td>
                            <td class="font-bold white-space-nowrap">{{ $tgl }}</td>
                            <td class="font-extrabold text-dark">{{ $tutorName }}</td>
                            <td class="font-semibold">{{ $siswaName }}</td>
                            <td class="font-extrabold text-success">{{ $jamMasuk }}</td>
                            <td class="font-extrabold text-primary">{{ $jamSelesai }}</td>
                            <td>
                                <div class="fotoStack">
                                    @if ($item->foto_mulai)
                                        <img src="{{ asset($item->foto_mulai) }}" class="fotoThumbnail cursor-pointer" title="Foto Mulai"
                                            onclick="openPhotoModal('{{ asset($item->foto_mulai) }}', 'Foto Masuk — {{ $tutorName }}')">
                                    @else
                                        <div class="fotoPlaceholder" title="Tidak ada foto masuk">M -</div>
                                    @endif

                                    @if ($item->foto_selesai)
                                        <img src="{{ asset($item->foto_selesai) }}" class="fotoThumbnail cursor-pointer" title="Foto Selesai"
                                            onclick="openPhotoModal('{{ asset($item->foto_selesai) }}', 'Foto Selesai — {{ $tutorName }}')">
                                    @else
                                        <div class="fotoPlaceholder" title="Belum foto selesai">S -</div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                @if ($lokasi !== '-')
                                    <button type="button" class="mapBtn" title="Lihat Peta Lokasi" onclick="openMapModal('{{ $lokasi }}', '{{ addslashes($tutorName) }}', '{{ addslashes($modaLabel) }}', '{{ addslashes($tNama) }}', '{{ $tLat }}', '{{ $tLng }}', '{{ $tRad }}')">
                                        <ion-icon name="map-outline"></ion-icon>
                                    </button>
                                    @if($item->lokasiPresensi)
                                        <div class="text-xs text-primary font-bold mt-1 d-flex items-center justify-center gap-1">
                                            <ion-icon name="location-sharp"></ion-icon> {{ $item->lokasiPresensi->nama_lokasi }}
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

    {{-- ── Mobile Cards List (Layar HP / Tablet) ── --}}
    <div class="mobile-card-list">
        @forelse($presensi as $index => $item)
            @php
                $tutorName = $item->tutor->nama_lengkap ?? 'Tutor';
                $siswaName = $item->siswa->nama_siswa ?? '-';
                $tgl = \Carbon\Carbon::parse($item->tgl_presensi)->format('d/m/Y');
                $jamMasuk = $item->jam_mulai ? \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') : '-';
                $jamSelesai = $item->jam_selesai
                    ? \Carbon\Carbon::parse($item->jam_selesai)->format('H:i')
                    : '-';
                $lokasi = $item->lokasi_mulai ?? '-';
                $tNama = $item->lokasiPresensi ? $item->lokasiPresensi->nama_lokasi : config('lokasi.sekolah_nama', 'PKBM Pikat');
                $tLat = $item->lokasiPresensi ? $item->lokasiPresensi->latitude : config('lokasi.sekolah_lat', -7.8011945);
                $tLng = $item->lokasiPresensi ? $item->lokasiPresensi->longitude : config('lokasi.sekolah_lng', 110.364917);
                $tRad = $item->lokasiPresensi ? $item->lokasiPresensi->radius_meter : config('lokasi.radius_meter', 100);
                $modaLabel = $item->moda_label ?? 'Tatap Muka';
            @endphp
            <div class="data-mobile-card">
                <div class="dmc-header">
                    <div>
                        <h3 class="dmc-title">{{ $tutorName }}</h3>
                        <div class="dmc-subtitle">{{ $tgl }} &bull; Siswa: <b class="text-dark">{{ $siswaName }}</b></div>
                    </div>
                    <span class="text-xs font-bold text-muted">
                        #{{ $presensi->firstItem() + $index }}
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
                            @if ($item->foto_mulai)
                                <img src="{{ asset($item->foto_mulai) }}" class="fotoThumbnail cursor-pointer" title="Foto Mulai"
                                    onclick="openPhotoModal('{{ asset($item->foto_mulai) }}', 'Foto Masuk — {{ $tutorName }}')">
                            @endif
                            @if ($item->foto_selesai)
                                <img src="{{ asset($item->foto_selesai) }}" class="fotoThumbnail cursor-pointer" title="Foto Selesai"
                                    onclick="openPhotoModal('{{ asset($item->foto_selesai) }}', 'Foto Selesai — {{ $tutorName }}')">
                            @endif
                        </div>
                    </div>

                    @if ($lokasi !== '-')
                        <div class="d-flex items-center gap-2">
                            <button type="button" onclick="openMapModal('{{ $lokasi }}', '{{ addslashes($tutorName) }}', '{{ addslashes($modaLabel) }}', '{{ addslashes($tNama) }}', '{{ $tLat }}', '{{ $tLng }}', '{{ $tRad }}')" class="btnOutline text-sm rounded-md d-inline-flex items-center gap-1 px-3 py-1">
                                <ion-icon name="map-outline"></ion-icon> Peta GPS
                            </button>
                            @if($item->lokasiPresensi)
                                <span class="text-xs text-primary font-bold"><ion-icon name="location-sharp"></ion-icon> {{ $item->lokasiPresensi->nama_lokasi }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="data-mobile-card table-empty-cell">
                <div class="font-bold text-md mb-1">Belum Ada Data Presensi</div>
                <div class="text-sm">Tidak ada rekaman aktivitas mengajar pada periode filter yang dipilih.</div>
            </div>
        @endforelse
    </div>

    {{-- ── Optional Presensi Karyawan / Staf ── --}}
    @if ($karyawanPresensi->isNotEmpty())
        <div class="card mb-4 mt-6">
            <div class="cardTitle flex-between flex-wrap gap-2">
                <div class="d-flex items-center gap-2">
                    <ion-icon name="people-outline"></ion-icon> Monitoring Presensi Staf &amp; Admin Hari Ini
                </div>
                <span class="badge text-xs bg-primary-light text-primary font-extrabold rounded-pill px-3 py-1">
                    {{ $karyawanPresensi->count() }} Staf
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
                            <th>Jam Pulang</th>
                            <th>Foto</th>
                            <th class="text-center">Lokasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($karyawanPresensi as $index => $kp)
                            @php
                                $kpTgl = \Carbon\Carbon::parse($kp->tgl_presensi)->format('d/m/Y');
                                $kpJamMasuk = $kp->jam_mulai
                                    ? \Carbon\Carbon::parse($kp->jam_mulai)->format('H:i')
                                    : '-';
                                $kpJamSelesai = $kp->jam_selesai
                                    ? \Carbon\Carbon::parse($kp->jam_selesai)->format('H:i')
                                    : '-';
                                $kpName = $kp->user->nama_lengkap ?? 'Staf';
                                $kpRole = ucfirst($kp->user->role ?? 'Karyawan');
                                $lokasi = $kp->lokasi_mulai;
                                $kpTitikNama = $kp->lokasiPresensi ? $kp->lokasiPresensi->nama_lokasi : config('lokasi.sekolah_nama', 'PKBM Pikat');
                                $kpLat = $kp->lokasiPresensi ? $kp->lokasiPresensi->latitude : config('lokasi.sekolah_lat', -7.8011945);
                                $kpLng = $kp->lokasiPresensi ? $kp->lokasiPresensi->longitude : config('lokasi.sekolah_lng', 110.364917);
                                $kpRad = $kp->lokasiPresensi ? $kp->lokasiPresensi->radius_meter : config('lokasi.radius_meter', 100);
                            @endphp
                            <tr>
                                <td class="p-3 font-bold text-muted">{{ $index + 1 }}</td>
                                <td class="font-bold white-space-nowrap">{{ $kpTgl }}</td>
                                <td class="font-semibold text-dark">{{ $kpName }}</td>
                                <td>
                                    <span class="badge text-xs font-bold {{ $kp->user?->role === 'admin' ? 'bg-primary-light text-primary' : 'bg-muted-light text-muted' }}">
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
                                        <button type="button" class="mapBtn" title="Lihat Peta Lokasi" onclick="openMapModal('{{ $lokasi }}', '{{ addslashes($kpName) }}', 'Karyawan / {{ $kpRole }}', '{{ addslashes($kpTitikNama) }}', '{{ $kpLat }}', '{{ $kpLng }}', '{{ $kpRad }}')">
                                            <ion-icon name="map-outline"></ion-icon>
                                        </button>
                                        @if($kp->lokasiPresensi)
                                            <div class="text-xs text-primary font-bold mt-1 d-flex items-center justify-center gap-1">
                                                <ion-icon name="location-sharp"></ion-icon> {{ $kp->lokasiPresensi->nama_lokasi }}
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

        <div class="mobile-card-list">
            @foreach ($karyawanPresensi as $index => $kp)
                @php
                    $kpTgl = \Carbon\Carbon::parse($kp->tgl_presensi)->format('d/m/Y');
                    $kpJamMasuk = $kp->jam_mulai ? \Carbon\Carbon::parse($kp->jam_mulai)->format('H:i') : '-';
                    $kpJamSelesai = $kp->jam_selesai
                        ? \Carbon\Carbon::parse($kp->jam_selesai)->format('H:i')
                        : '-';
                    $kpName = $kp->user->nama_lengkap ?? 'Staf';
                    $kpRole = ucfirst($kp->user->role ?? 'Karyawan');
                    $lokasi = $kp->lokasi_mulai;
                    $kpTitikNama = $kp->lokasiPresensi ? $kp->lokasiPresensi->nama_lokasi : config('lokasi.sekolah_nama', 'PKBM Pikat');
                    $kpLat = $kp->lokasiPresensi ? $kp->lokasiPresensi->latitude : config('lokasi.sekolah_lat', -7.8011945);
                    $kpLng = $kp->lokasiPresensi ? $kp->lokasiPresensi->longitude : config('lokasi.sekolah_lng', 110.364917);
                    $kpRad = $kp->lokasiPresensi ? $kp->lokasiPresensi->radius_meter : config('lokasi.radius_meter', 100);
                @endphp
                <div class="data-mobile-card">
                    <div class="dmc-header">
                        <div>
                            <h3 class="dmc-title">{{ $kpName }}</h3>
                            <div class="dmc-subtitle">{{ $kpTgl }} &bull; <span class="text-muted">{{ $kpRole }}</span></div>
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
                            <div class="dmc-label">Jam Pulang</div>
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
                                    <img src="{{ asset($kp->foto_selesai) }}" class="fotoThumbnail cursor-pointer"
                                        title="Foto Selesai"
                                        onclick="openPhotoModal('{{ asset($kp->foto_selesai) }}', 'Foto Pulang — {{ $kpName }}')">
                                @endif
                            </div>
                        </div>

                        @if ($lokasi && $lokasi !== '-')
                            <div class="d-flex items-center gap-2">
                                <button type="button" onclick="openMapModal('{{ $lokasi }}', '{{ addslashes($kpName) }}', 'Karyawan / {{ $kpRole }}', '{{ addslashes($kpTitikNama) }}', '{{ $kpLat }}', '{{ $kpLng }}', '{{ $kpRad }}')" class="btnOutline text-sm rounded-md d-inline-flex items-center gap-1 px-3 py-1">
                                    <ion-icon name="map-outline"></ion-icon> Peta GPS
                                </button>
                                @if($kp->lokasiPresensi)
                                    <span class="text-xs text-primary font-bold"><ion-icon name="location-sharp"></ion-icon> {{ $kp->lokasiPresensi->nama_lokasi }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Pagination --}}
    @if ($presensi->hasPages())
        <div class="paginatePad">
            {{ $presensi->withQueryString()->links() }}
        </div>
    @endif

    {{-- ── Modal Foto ── --}}
    <div class="modal-overlay" id="photoModal" onclick="if(event.target===this)closeModal('photoModal')">
        <div class="modal-box">
            <div class="modal-header">
                <span id="photoModalTitle" class="font-extrabold">Foto Presensi</span>
                <button type="button" class="modal-close" onclick="closeModal('photoModal')">&times;</button>
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
                    <div id="kepsekMapContainer" class="map-modal-leaflet"></div>
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
                            <a id="btnKepsekGoogleMaps" href="#" target="_blank" class="btnNavMaps">
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

    let kepsekLeafletMap = null;
    let kepsekSekolahMarker = null;
    let kepsekPresensiMarker = null;
    let kepsekGeofenceCircle = null;
    let kepsekMeasureLine = null;

    function ensureLeafletLoaded(callback) {
        if (typeof L !== 'undefined') {
            callback();
            return;
        }
        if (!document.getElementById('leaflet-css')) {
            const link = document.createElement('link');
            link.id = 'leaflet-css';
            link.rel = 'stylesheet';
            link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(link);
        }
        if (!document.getElementById('leaflet-js')) {
            const script = document.createElement('script');
            script.id = 'leaflet-js';
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.onload = function() {
                callback();
            };
            document.head.appendChild(script);
        } else {
            const checkInterval = setInterval(function() {
                if (typeof L !== 'undefined') {
                    clearInterval(checkInterval);
                    callback();
                }
            }, 50);
        }
    }

    function calcHaversine(lat1, lon1, lat2, lon2) {
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
        document.getElementById('btnKepsekGoogleMaps').href = 'https://www.google.com/maps?q=' + lat + ',' + lng;

        document.getElementById('mapModal').classList.add('active');

        ensureLeafletLoaded(function() {
            setTimeout(function() {
                initKepsekMap(lat, lng, nama, moda, targetLat, targetLng, targetRadius, targetNama);
            }, 50);
        });
    }

    function initKepsekMap(lat, lng, nama, moda, targetLat, targetLng, targetRadius, targetNama) {
        var container = document.getElementById('kepsekMapContainer');
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

        if (!kepsekLeafletMap) {
            kepsekLeafletMap = L.map('kepsekMapContainer', {
                zoomControl: true,
                scrollWheelZoom: false
            }).setView([targetLat, targetLng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(kepsekLeafletMap);

            kepsekSekolahMarker = L.marker([targetLat, targetLng], { icon: targetPin }).addTo(kepsekLeafletMap);
            kepsekSekolahMarker.bindPopup('<b>' + targetNama + '</b><br><span style="font-size:11px;">Pusat Geofence (Radius ' + targetRadius + ' m)</span>');

            kepsekGeofenceCircle = L.circle([targetLat, targetLng], {
                color: '#0284c7',
                fillColor: '#38bdf8',
                fillOpacity: 0.2,
                radius: targetRadius
            }).addTo(kepsekLeafletMap);
        } else {
            kepsekSekolahMarker.setLatLng([targetLat, targetLng]);
            kepsekSekolahMarker.setIcon(targetPin);
            kepsekSekolahMarker.setPopupContent('<b>' + targetNama + '</b><br><span style="font-size:11px;">Pusat Geofence (Radius ' + targetRadius + ' m)</span>');
            kepsekGeofenceCircle.setLatLng([targetLat, targetLng]);
            kepsekGeofenceCircle.setRadius(targetRadius);
        }

        if (kepsekPresensiMarker) {
            kepsekPresensiMarker.setLatLng([lat, lng]);
            kepsekPresensiMarker.setIcon(userPin);
        } else {
            kepsekPresensiMarker = L.marker([lat, lng], { icon: userPin }).addTo(kepsekLeafletMap);
        }
        kepsekPresensiMarker.bindPopup('<b>' + (nama || 'Presensi') + '</b><br><span style="font-size:11px;">Jarak ke ' + targetNama + ': ' + distFormatted + ' meter<br>Moda: ' + (moda || 'Tatap Muka') + '</span>');

        if (kepsekMeasureLine) {
            kepsekMeasureLine.setLatLngs([[lat, lng], [targetLat, targetLng]]);
        } else {
            kepsekMeasureLine = L.polyline([[lat, lng], [targetLat, targetLng]], {
                color: isWithin ? '#10b981' : '#ef4444',
                weight: 3,
                dashArray: '6, 6',
                opacity: 0.8
            }).addTo(kepsekLeafletMap);
        }

        kepsekMeasureLine.setStyle({
            color: isWithin ? '#10b981' : '#ef4444',
            dashArray: '6, 6'
        });

        var bounds = L.latLngBounds([[targetLat, targetLng], [lat, lng]]);
        kepsekLeafletMap.fitBounds(bounds, { padding: [40, 40] });

        setTimeout(function() {
            if (kepsekLeafletMap) kepsekLeafletMap.invalidateSize();
        }, 100);
        setTimeout(function() {
            if (kepsekLeafletMap) kepsekLeafletMap.invalidateSize();
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
