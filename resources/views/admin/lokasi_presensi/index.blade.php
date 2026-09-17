@extends('layouts.admin')

@section('title', 'Master Titik Lokasi Presensi — Admin')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">MANAJEMEN GEOFENCE &amp; MULTI-LOKASI</div>
                <h1 class="laporanHeaderTitle">Master Titik Lokasi Presensi</h1>
                <div class="laporanHeaderSub">Kelola lokasi gedung pusat, cabang belajar, mitra magang, dan batas radius geofence</div>
                <p class="laporanHeaderDesc">Tutor, Mahasiswa Magang, dan Karyawan dapat memilih titik lokasi yang ditentukan saat melakukan absensi.</p>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.lokasi-presensi.create') }}" class="profileBtnPrimary">
                    <ion-icon name="add-circle-outline"></ion-icon> Tambah Titik Lokasi
                </a>
            </div>
        </div>
    </div>

    {{-- ── KPI Summary Cards ── --}}
    <div class="kpi-grid mb-4">
        <div class="kpi-card emerald">
            <div class="kpi-top">
                <div class="kpi-label">Total Titik Lokasi</div>
                <div class="kpi-icon-wrap">
                    <ion-icon name="location-outline"></ion-icon>
                </div>
            </div>
            <div>
                <div class="kpi-val">{{ $stats['total'] }} Titik</div>
                <div class="kpi-sub">Terdaftar di sistem presensi</div>
            </div>
        </div>

        <div class="kpi-card blue">
            <div class="kpi-top">
                <div class="kpi-label">Titik Aktif</div>
                <div class="kpi-icon-wrap">
                    <ion-icon name="checkmark-circle-outline"></ion-icon>
                </div>
            </div>
            <div>
                <div class="kpi-val">{{ $stats['active'] }} Lokasi</div>
                <div class="kpi-sub">Muncul pada pilihan presensi tutor &amp; magang</div>
            </div>
        </div>

        <div class="kpi-card amber">
            <div class="kpi-top">
                <div class="kpi-label">Titik Non-Aktif</div>
                <div class="kpi-icon-wrap">
                    <ion-icon name="pause-circle-outline"></ion-icon>
                </div>
            </div>
            <div>
                <div class="kpi-val">{{ $stats['inactive'] }} Lokasi</div>
                <div class="kpi-sub">Dinonaktifkan sementara</div>
            </div>
        </div>
    </div>

    {{-- ── Peta Visualisasi Sebaran Seluruh Titik ── --}}
    <div class="geofence-map-card">
        <div class="geofence-map-header">
            <div>
                <div class="geofence-map-title">
                    <ion-icon name="map-outline" class="text-primary"></ion-icon> Peta Sebaran Titik Geofence Aktif
                </div>
                <div class="geofence-map-sub">Menampilkan seluruh titik lokasi presensi yang sedang aktif beserta visualisasi radius geofence</div>
            </div>
            <span class="badgeCount">{{ count($allActiveLokasis) }} Titik Terpetakan</span>
        </div>
        <div id="overviewMap" class="overview-map-frame"></div>
    </div>

    {{-- ── Filter Card ── --}}
    <div class="laporanFilterCard mb-4">
        <form method="GET" action="{{ route('admin.lokasi-presensi.index') }}">
            <div class="laporanFilterGrid">
                <div class="filterField">
                    <label class="filterFieldLabel">Cari Lokasi</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama lokasi, alamat..." class="profileInput text-md">
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Tipe Lokasi</label>
                    <select name="tipe" class="filterSelect" onchange="this.form.submit()">
                        <option value="">Semua Tipe</option>
                        <option value="pusat" {{ request('tipe') === 'pusat' ? 'selected' : '' }}>Gedung Pusat</option>
                        <option value="cabang" {{ request('tipe') === 'cabang' ? 'selected' : '' }}>Cabang / Rombel</option>
                        <option value="mitra" {{ request('tipe') === 'mitra' ? 'selected' : '' }}>Instansi Mitra</option>
                        <option value="kegiatan" {{ request('tipe') === 'kegiatan' ? 'selected' : '' }}>Sentra / Kegiatan</option>
                        <option value="lainnya" {{ request('tipe') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Status</label>
                    <select name="status" class="filterSelect" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>

                <div class="filter-actions-full">
                    <button type="submit" class="btn-filter-primary">
                        <ion-icon name="filter-outline"></ion-icon> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'tipe', 'status']))
                        <a href="{{ route('admin.lokasi-presensi.index') }}" class="btn-filter-reset" title="Reset Filter">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- ── Section Title ── --}}
    <div class="sectionRow">
        <h2>Daftar Titik Geofence</h2>
        <span class="badgeCount">{{ $lokasis->total() }} Titik Ditemukan</span>
    </div>

    {{-- ── Desktop Table ── --}}
    <div class="table-responsive-desktop">
        <div class="tableContainer m-0 mb-4 rounded-xl border-base">
            <table class="laporanTable">
                <thead>
                    <tr>
                        <th class="table-col-num">NO</th>
                        <th>NAMA TITIK LOKASI</th>
                        <th class="text-center">TIPE</th>
                        <th>KOORDINAT GPS</th>
                        <th class="text-center">RADIUS GEOFENCE</th>
                        <th class="text-center">RIWAYAT PRESENSI</th>
                        <th class="text-center">STATUS</th>
                        <th class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lokasis as $index => $l)
                        @php
                            $tipeBadgeClass = match($l->tipe) {
                                'pusat' => 'badge-lokasi-pusat',
                                'cabang' => 'badge-lokasi-cabang',
                                'mitra' => 'badge-lokasi-mitra',
                                'kegiatan' => 'badge-lokasi-kegiatan',
                                default => 'badge-lokasi-lainnya',
                            };
                            $tipeLabel = match($l->tipe) {
                                'pusat' => 'Gedung Pusat',
                                'cabang' => 'Cabang Belajar',
                                'mitra' => 'Mitra PKL',
                                'kegiatan' => 'Sentra Kegiatan',
                                default => ucfirst($l->tipe),
                            };
                        @endphp
                        <tr>
                            <td class="text-center font-bold text-muted">{{ $lokasis->firstItem() + $index }}</td>
                            <td>
                                <div class="font-bold text-dark text-md">{{ $l->nama_lokasi }}</div>
                                <div class="text-xs text-muted">{{ $l->alamat ?: 'Tidak ada alamat tertulis' }}</div>
                            </td>
                            <td class="text-center">
                                <span class="app-badge {{ $tipeBadgeClass }}">{{ $tipeLabel }}</span>
                            </td>
                            <td>
                                <div class="font-mono text-xs font-semibold text-primary">
                                    {{ number_format($l->latitude, 6) }}, {{ number_format($l->longitude, 6) }}
                                </div>
                                <a href="https://www.google.com/maps?q={{ $l->latitude }},{{ $l->longitude }}" target="_blank" class="text-xs text-muted d-inline-flex items-center gap-1 mt-1 text-no-decor hover:text-primary">
                                    <ion-icon name="open-outline"></ion-icon> Buka Google Maps
                                </a>
                            </td>
                            <td class="text-center">
                                <span class="font-bold text-dark">{{ $l->radius_meter }} Meter</span>
                            </td>
                            <td class="text-center">
                                <span class="text-xs font-semibold text-muted">
                                    {{ $l->presensis_count + $l->presensi_karyawans_count }} Presensi
                                </span>
                            </td>
                            <td class="text-center">
                                <form method="POST" action="{{ route('admin.lokasi-presensi.toggleStatus', $l) }}" class="d-inline m-0">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="smallBtn btn-status-toggle btn-table-action" title="Klik untuk mengubah status">
                                        {{ $l->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 flex-center flex-wrap">
                                    <a href="{{ route('admin.lokasi-presensi.edit', $l) }}" class="smallBtn edit btn-table-action" title="Edit Titik Lokasi">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.lokasi-presensi.destroy', $l) }}" data-confirm="Hapus titik lokasi '{{ $l->nama_lokasi }}' dari master geofence?" data-confirm-title="Hapus Titik Lokasi" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="smallBtn delete cursor-pointer btn-table-action" title="Hapus Titik">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="table-empty-cell">
                                <div class="font-bold text-md mb-1">Belum Ada Titik Lokasi</div>
                                <div class="text-sm">Belum ada titik lokasi presensi yang terdaftar sesuai filter.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($lokasis->hasPages())
            <div class="mt-3">
                {{ $lokasis->links() }}
            </div>
        @endif
    </div>

    {{-- ── Mobile Cards List (Layar HP / Tablet) ── --}}
    <div class="mobile-card-list">
        @forelse($lokasis as $index => $l)
            @php
                $tipeBadgeClass = match($l->tipe) {
                    'pusat' => 'badge-lokasi-pusat',
                    'cabang' => 'badge-lokasi-cabang',
                    'mitra' => 'badge-lokasi-mitra',
                    'kegiatan' => 'badge-lokasi-kegiatan',
                    default => 'badge-lokasi-lainnya',
                };
                $tipeLabel = match($l->tipe) {
                    'pusat' => 'Gedung Pusat',
                    'cabang' => 'Cabang Belajar',
                    'mitra' => 'Mitra PKL',
                    'kegiatan' => 'Sentra Kegiatan',
                    default => ucfirst($l->tipe),
                };
            @endphp
            <div class="data-mobile-card">
                <div class="dmc-header">
                    <div>
                        <h3 class="dmc-title">{{ $l->nama_lokasi }}</h3>
                        <div class="dmc-subtitle">{{ $l->alamat ?: 'Tidak ada alamat tertulis' }}</div>
                    </div>
                    <div class="flex-col gap-1 flex-items-end">
                        <span class="app-badge {{ $tipeBadgeClass }}">{{ $tipeLabel }}</span>
                        @if($l->is_active)
                            <span class="app-badge badge-status-aktif">Aktif</span>
                        @else
                            <span class="app-badge badge-status-nonaktif">Non-Aktif</span>
                        @endif
                    </div>
                </div>

                <div class="dmc-grid">
                    <div class="dmc-field">
                        <div class="dmc-label">Radius Geofence</div>
                        <div class="dmc-value text-primary">{{ $l->radius_meter }} Meter</div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Total Presensi</div>
                        <div class="dmc-value">{{ $l->presensis_count + $l->presensi_karyawans_count }} Presensi</div>
                    </div>

                    <div class="dmc-field full">
                        <div class="dmc-label">Koordinat GPS</div>
                        <div class="dmc-value font-mono text-xs">{{ number_format($l->latitude, 6) }}, {{ number_format($l->longitude, 6) }}</div>
                        <a href="https://www.google.com/maps?q={{ $l->latitude }},{{ $l->longitude }}" target="_blank" class="text-xs text-muted d-inline-flex items-center gap-1 mt-1 text-no-decor hover:text-primary">
                            <ion-icon name="open-outline"></ion-icon> Buka Google Maps
                        </a>
                    </div>
                </div>

                <div class="dmc-footer">
                    <div class="text-xs font-bold text-muted">
                        No. {{ $lokasis->firstItem() + $index }}
                    </div>
                    <div class="dmc-actions">
                        <a href="{{ route('admin.lokasi-presensi.edit', $l) }}" class="smallBtn edit btn-table-action">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('admin.lokasi-presensi.toggleStatus', $l) }}" class="d-inline m-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="smallBtn btn-status-toggle btn-table-action">
                                {{ $l->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.lokasi-presensi.destroy', $l) }}" data-confirm="Hapus titik lokasi '{{ $l->nama_lokasi }}'?" data-confirm-title="Hapus Titik Lokasi" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="smallBtn delete btn-table-action">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="table-empty-cell">
                <div class="font-bold text-md mb-1">Belum Ada Titik Lokasi</div>
                <div class="text-sm">Belum ada titik lokasi presensi yang terdaftar sesuai filter.</div>
            </div>
        @endforelse

        @if($lokasis->hasPages())
            <div class="mt-3">
                {{ $lokasis->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const locations = {!! json_encode($allActiveLokasis) !!};
        const mapEl = document.getElementById('overviewMap');
        if (!mapEl || typeof L === 'undefined') return;

        const defaultLat = {{ config('lokasi.sekolah_lat', -7.8011945) }};
        const defaultLng = {{ config('lokasi.sekolah_lng', 110.364917) }};

        const map = L.map('overviewMap').setView([defaultLat, defaultLng], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const bounds = [];

        locations.forEach(function(loc) {
            const lat = parseFloat(loc.latitude);
            const lng = parseFloat(loc.longitude);
            const rad = parseInt(loc.radius_meter) || 100;

            if (isNaN(lat) || isNaN(lng)) return;

            bounds.push([lat, lng]);

            const marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup('<b>' + loc.nama_lokasi + '</b><br><span style="font-size:11px;">' + (loc.alamat || '') + '</span><br><b>Radius Geofence:</b> ' + rad + ' Meter');

            L.circle([lat, lng], {
                color: '#0284c7',
                fillColor: '#38bdf8',
                fillOpacity: 0.25,
                radius: rad
            }).addTo(map);
        });

        if (bounds.length > 1) {
            map.fitBounds(bounds, { padding: [40, 40] });
        } else if (bounds.length === 1) {
            map.setView(bounds[0], 16);
        }

        // Trigger map invalidateSize after rendering to ensure full tile rendering without grey edges
        setTimeout(function() {
            map.invalidateSize();
        }, 250);
    });
</script>
@endsection
