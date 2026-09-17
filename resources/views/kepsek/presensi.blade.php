@extends('layouts.kepsek')

@section('title', 'Monitoring Presensi Tutor — Kepala Sekolah')

@section('content')

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
                    <span >Buka Rekapitulasi Laporan</span>
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
        <h2 >Log Presensi Tutor &amp; Siswa</h2>
        <span class="badgeCount">{{ $presensi->total() }} Sesi Terdata</span>
    </div>

    {{-- ── Log Table Container (Desktop) ── --}}
    <div class="table-responsive-desktop">
        <div class="tableContainer m-0 mb-4 rounded-xl border-base">
            <table class="laporanTable">
                <thead >
                    <tr >
                        <th class="table-col-num">NO</th>
                        <th >TANGGAL</th>
                        <th >TUTOR</th>
                        <th >SISWA</th>
                        <th >JAM MASUK</th>
                        <th >JAM SELESAI</th>
                        <th >FOTO BUKTI</th>
                        <th class="text-center">GPS</th>
                    </tr>
                </thead>
                <tbody >
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
                        @endphp
                        <tr >
                            <td class="p-3 font-bold text-muted">{{ $presensi->firstItem() + $index }}</td>
                            <td class="font-bold white-space-nowrap">{{ $tgl }}</td>
                            <td class="font-extrabold text-dark">{{ $tutorName }}</td>
                            <td class="font-semibold">{{ $siswaName }}</td>
                            <td class="font-extrabold text-success">{{ $jamMasuk }}</td>
                            <td class="font-extrabold text-primary">{{ $jamSelesai }}</td>
                            <td >
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
                                    <button type="button" class="mapBtn" title="Lihat Peta Lokasi" onclick="openMapModal('{{ $lokasi }}')">
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
                        <tr >
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
            @endphp
            <div class="data-mobile-card">
                <div class="dmc-header">
                    <div >
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
                        <span class="dmc-label mb-0">Foto Bukti:</span>
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
                            <button type="button" onclick="openMapModal('{{ $lokasi }}')" class="btnOutline text-sm rounded-md d-inline-flex items-center gap-1 px-3 py-1">
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
                    <ion-icon name="people-outline"></ion-icon> Monitoring Presensi Staf & Admin Hari Ini
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
                                        <button type="button" class="mapBtn" title="Lihat Peta Lokasi" onclick="openMapModal('{{ $lokasi }}')">
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
                @endphp
                <div class="data-mobile-card">
                    <div class="dmc-header">
                        <div >
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
                                    <img src="{{ asset($kp->foto_mulai) }}" class="fotoThumbnail" title="Foto Mulai"
                                        onclick="openPhotoModal('{{ asset($kp->foto_mulai) }}', 'Foto Masuk — {{ $kpName }}')"
                                        class="cursor-pointer">
                                @endif
                                @if ($kp->foto_selesai)
                                    <img src="{{ asset($kp->foto_selesai) }}" class="fotoThumbnail"
                                        title="Foto Selesai"
                                        onclick="openPhotoModal('{{ asset($kp->foto_selesai) }}', 'Foto Pulang — {{ $kpName }}')"
                                        class="cursor-pointer">
                                @endif
                            </div>
                        </div>

                        @if ($lokasi && $lokasi !== '-')
                            <div class="d-flex items-center gap-2">
                                <button type="button" onclick="openMapModal('{{ $lokasi }}')" class="btnOutline text-sm rounded-md d-inline-flex items-center gap-1 px-3 py-1">
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
                <button class="modal-close" onclick="closeModal('photoModal')">&times;</button>
            </div>
            <div class="modal-body">
                <img id="photoModalImg" src="" alt="Foto presensi">
            </div>
        </div>
    </div>

    {{-- ── Modal Maps ── --}}
    <div class="modal-overlay" id="mapModal" onclick="if(event.target===this)closeModal('mapModal')">
        <div class="modal-box">
            <div class="modal-header">
                <span class="font-extrabold">Lokasi Presensi (Geolokasi GPS)</span>
                <button class="modal-close" onclick="closeModal('mapModal')">&times;</button>
            </div>
            <div class="modal-body">
                <iframe id="mapModalFrame" src="" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
</div>

<script >
    function openPhotoModal(src, title) {
        document.getElementById('photoModalImg').src = src;
        document.getElementById('photoModalTitle').textContent = title;
        document.getElementById('photoModal').classList.add('active');
    }

    function openMapModal(lokasi) {
        var q = encodeURIComponent(lokasi);
        document.getElementById('mapModalFrame').src = 'https://www.google.com/maps?q=' + q + '&z=17&hl=id&output=embed';
        document.getElementById('mapModal').classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
        if (id === 'mapModal') document.getElementById('mapModalFrame').src = '';
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
