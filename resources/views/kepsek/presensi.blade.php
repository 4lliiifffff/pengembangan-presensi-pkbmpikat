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
        <div class="tableContainer" style="margin: 0 0 20px; border-radius: 18px; border: 1px solid var(--border);">
            <table class="laporanTable">
                <thead>
                    <tr>
                        <th style="padding: 12px 14px; width: 50px;">NO</th>
                        <th>TANGGAL</th>
                        <th>TUTOR</th>
                        <th>SISWA</th>
                        <th>JAM MASUK</th>
                        <th>JAM SELESAI</th>
                        <th>FOTO BUKTI</th>
                        <th style="text-align: center;">GPS</th>
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
                        @endphp
                        <tr>
                            <td style="padding: 12px 14px; font-weight: 700; color: var(--muted);">{{ $presensi->firstItem() + $index }}</td>
                            <td style="font-weight: 700; white-space: nowrap;">{{ $tgl }}</td>
                            <td style="font-weight: 800; color: var(--text);">{{ $tutorName }}</td>
                            <td style="font-weight: 600;">{{ $siswaName }}</td>
                            <td style="font-weight: 800; color: #16a34a;">{{ $jamMasuk }}</td>
                            <td style="font-weight: 800; color: var(--blue2);">{{ $jamSelesai }}</td>
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
                            <td style="text-align: center;">
                                @if ($lokasi !== '-')
                                    <button type="button" class="mapBtn" title="Lihat Peta Lokasi"
                                        onclick="openMapModal('{{ $lokasi }}')">
                                        <ion-icon name="map-outline"></ion-icon>
                                    </button>
                                @else
                                    <span style="color: var(--muted);">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--muted); padding: 36px 16px;">
                                <ion-icon name="calendar-outline" style="font-size: 36px; opacity: 0.4; display: block; margin: 0 auto 8px;"></ion-icon>
                                <div style="font-weight: 700; font-size: 13.5px; margin-bottom: 2px;">Belum Ada Data Presensi</div>
                                <div style="font-size: 12px;">Tidak ada rekaman aktivitas mengajar pada periode filter yang dipilih.</div>
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
                    <div>
                        <h3 class="dmc-title">{{ $tutorName }}</h3>
                        <div class="dmc-subtitle">{{ $tgl }} &bull; Siswa: <b style="color:var(--text);">{{ $siswaName }}</b></div>
                    </div>
                    <span style="font-size:11px;font-weight:700;color:var(--muted);">
                        #{{ $presensi->firstItem() + $index }}
                    </span>
                </div>

                <div class="dmc-grid">
                    <div class="dmc-field">
                        <div class="dmc-label">Jam Masuk</div>
                        <div class="dmc-value" style="color:#16a34a;">{{ $jamMasuk }}</div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Jam Selesai</div>
                        <div class="dmc-value" style="color:var(--blue2);">{{ $jamSelesai }}</div>
                    </div>
                </div>

                <div class="dmc-footer">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span class="dmc-label" style="margin-bottom:0;">Foto Bukti:</span>
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
                        <button type="button" class="btnOutline" style="padding:6px 12px;font-size:11.5px;border-radius:8px;display:inline-flex;align-items:center;gap:4px;" onclick="openMapModal('{{ $lokasi }}')">
                            <ion-icon name="map-outline"></ion-icon> Peta GPS
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="data-mobile-card" style="text-align:center;padding:32px 16px;color:var(--muted);">
                <div style="font-weight: 700; font-size: 13.5px; margin-bottom: 2px;">Belum Ada Data Presensi</div>
                <div style="font-size: 12px;">Tidak ada rekaman aktivitas mengajar pada periode filter yang dipilih.</div>
            </div>
        @endforelse
    </div>

    {{-- ── Optional Presensi Karyawan / Staf ── --}}
    @if (isset($karyawanPresensi) && $karyawanPresensi->isNotEmpty())
        <div class="sectionRow" style="margin-top: 24px;">
            <h2>Presensi Staf / Karyawan</h2>
            <span class="badgeCount">{{ $karyawanPresensi->count() }} Orang</span>
        </div>
        <div class="table-responsive-desktop">
            <div class="tableContainer" style="margin: 0 0 20px; border-radius: 18px; border: 1px solid var(--border);">
                <table class="laporanTable">
                    <thead>
                        <tr>
                            <th style="padding: 12px 14px; width: 50px;">NO</th>
                            <th>TANGGAL</th>
                            <th>NAMA KARYAWAN</th>
                            <th>ROLE</th>
                            <th>JAM MASUK</th>
                            <th>JAM SELESAI</th>
                            <th>FOTO</th>
                            <th style="text-align: center;">GPS</th>
                        </tr>
                    </thead>
                    <tbody>
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
                            <tr>
                                <td style="padding: 12px 14px; font-weight: 700; color: var(--muted);">{{ $index + 1 }}</td>
                                <td style="font-weight: 700; white-space: nowrap;">{{ $kpTgl }}</td>
                                <td style="font-weight: 800; color: var(--text);">{{ $kpName }}</td>
                                <td>
                                    <span style="background: rgba(100,116,139,0.12); padding: 4px 8px; border-radius: 6px; font-size: 10.5px; font-weight: 800; color: var(--muted);">
                                        {{ $kpRole }}
                                    </span>
                                </td>
                                <td style="font-weight: 800; color: #16a34a;">{{ $kpJamMasuk }}</td>
                                <td style="font-weight: 800; color: var(--blue2);">{{ $kpJamSelesai }}</td>
                                <td>
                                    <div class="fotoStack">
                                        @if ($kp->foto_mulai)
                                            <img src="{{ asset($kp->foto_mulai) }}" class="fotoThumbnail" title="Foto Mulai"
                                                onclick="openPhotoModal('{{ asset($kp->foto_mulai) }}', 'Foto Masuk — {{ $kpName }}')"
                                                style="cursor:pointer;">
                                        @else
                                            <div class="fotoPlaceholder">M -</div>
                                        @endif

                                        @if ($kp->foto_selesai)
                                            <img src="{{ asset($kp->foto_selesai) }}" class="fotoThumbnail"
                                                title="Foto Selesai"
                                                onclick="openPhotoModal('{{ asset($kp->foto_selesai) }}', 'Foto Pulang — {{ $kpName }}')"
                                                style="cursor:pointer;">
                                        @else
                                            <div class="fotoPlaceholder">S -</div>
                                        @endif
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    @if ($lokasi && $lokasi !== '-')
                                        <button type="button" class="mapBtn" title="Lihat Peta Lokasi"
                                            onclick="openMapModal('{{ $lokasi }}')">
                                            <ion-icon name="map-outline"></ion-icon>
                                        </button>
                                    @else
                                        <span style="color: var(--muted);">-</span>
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
                        <div>
                            <h3 class="dmc-title">{{ $kpName }}</h3>
                            <div class="dmc-subtitle">{{ $kpTgl }} &bull; <span style="color:var(--muted);">{{ $kpRole }}</span></div>
                        </div>
                        <span style="font-size:11px;font-weight:700;color:var(--muted);">
                            #{{ $index + 1 }}
                        </span>
                    </div>

                    <div class="dmc-grid">
                        <div class="dmc-field">
                            <div class="dmc-label">Jam Masuk</div>
                            <div class="dmc-value" style="color:#16a34a;">{{ $kpJamMasuk }}</div>
                        </div>

                        <div class="dmc-field">
                            <div class="dmc-label">Jam Pulang</div>
                            <div class="dmc-value" style="color:var(--blue2);">{{ $kpJamSelesai }}</div>
                        </div>
                    </div>

                    <div class="dmc-footer">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span class="dmc-label" style="margin-bottom:0;">Foto:</span>
                            <div class="fotoStack">
                                @if ($kp->foto_mulai)
                                    <img src="{{ asset($kp->foto_mulai) }}" class="fotoThumbnail" title="Foto Mulai"
                                        onclick="openPhotoModal('{{ asset($kp->foto_mulai) }}', 'Foto Masuk — {{ $kpName }}')"
                                        style="cursor:pointer;">
                                @endif
                                @if ($kp->foto_selesai)
                                    <img src="{{ asset($kp->foto_selesai) }}" class="fotoThumbnail"
                                        title="Foto Selesai"
                                        onclick="openPhotoModal('{{ asset($kp->foto_selesai) }}', 'Foto Pulang — {{ $kpName }}')"
                                        style="cursor:pointer;">
                                @endif
                            </div>
                        </div>

                        @if ($lokasi && $lokasi !== '-')
                            <button type="button" class="btnOutline" style="padding:6px 12px;font-size:11.5px;border-radius:8px;display:inline-flex;align-items:center;gap:4px;" onclick="openMapModal('{{ $lokasi }}')">
                                <ion-icon name="map-outline"></ion-icon> Peta GPS
                            </button>
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
                <span id="photoModalTitle" style="font-weight: 800;">Foto Presensi</span>
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
                <span style="font-weight: 800;">Lokasi Presensi (Geolokasi GPS)</span>
                <button class="modal-close" onclick="closeModal('mapModal')">&times;</button>
            </div>
            <div class="modal-body">
                <iframe id="mapModalFrame" src="" allowfullscreen loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
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
