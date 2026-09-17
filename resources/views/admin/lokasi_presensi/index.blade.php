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
            <div class="laporanHeaderActions header-actions-group">
                <a href="{{ route('admin.lokasi-presensi.create') }}" class="profileBtnPrimary">
                    <ion-icon name="add-circle-outline" class="icon-sm"></ion-icon> Tambah Titik Lokasi
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
                    <ion-icon name="location"></ion-icon>
                </div>
            </div>
            <div>
                <div class="kpi-val">{{ $stats['total'] }} Titik</div>
                <div class="kpi-sub">Terdaftar di sistem presensi</div>
            </div>
        </div>

        <div class="kpi-card blue">
            <div class="kpi-top">
                <div class="kpi-label">Titik Aktif (Bisa Diabsen)</div>
                <div class="kpi-icon-wrap">
                    <ion-icon name="checkmark-circle"></ion-icon>
                </div>
            </div>
            <div>
                <div class="kpi-val">{{ $stats['active'] }} Lokasi</div>
                <div class="kpi-sub">Muncul pada pilihan presensi tutor/magang</div>
            </div>
        </div>

        <div class="kpi-card amber">
            <div class="kpi-top">
                <div class="kpi-label">Titik Non-Aktif</div>
                <div class="kpi-icon-wrap">
                    <ion-icon name="pause-circle"></ion-icon>
                </div>
            </div>
            <div>
                <div class="kpi-val">{{ $stats['inactive'] }} Lokasi</div>
                <div class="kpi-sub">Sedang dinonaktifkan sementara</div>
            </div>
        </div>
    </div>

    {{-- ── Peta Visualisasi Sebaran Seluruh Titik ── --}}
    <div class="content-box mb-4">
        <div class="cardHeadRow mb-3">
            <div>
                <h3 class="font-bold text-dark text-md d-flex items-center gap-2">
                    <ion-icon name="map-outline" class="text-primary"></ion-icon> Peta Sebaran Titik Geofence Aktif
                </h3>
                <span class="text-xs text-muted">Menampilkan seluruh titik lokasi presensi yang sedang aktif</span>
            </div>
            <span class="badgeDate text-xs px-2 py-1">{{ count($allActiveLokasis) }} Titik Terpetakan</span>
        </div>
        <div id="overviewMap" style="height: 320px; width: 100%; border-radius: 12px; border: 1px solid var(--border); z-index: 1;"></div>
    </div>

    {{-- ── Filter Bar ── --}}
    <div class="filter-card mb-4">
        <form method="GET" action="{{ route('admin.lokasi-presensi.index') }}" class="filter-form">
            <div class="filter-group flex-1">
                <label class="filter-label">Cari Lokasi:</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama lokasi, alamat..." class="filter-select">
            </div>

            <div class="filter-group">
                <label class="filter-label">Tipe Lokasi:</label>
                <select name="tipe" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Tipe</option>
                    <option value="pusat" {{ request('tipe') == 'pusat' ? 'selected' : '' }}>Gedung Pusat</option>
                    <option value="cabang" {{ request('tipe') == 'cabang' ? 'selected' : '' }}>Cabang / Rombel</option>
                    <option value="mitra" {{ request('tipe') == 'mitra' ? 'selected' : '' }}>Instansi Mitra</option>
                    <option value="kegiatan" {{ request('tipe') == 'kegiatan' ? 'selected' : '' }}>Sentra / Kegiatan</option>
                    <option value="lainnya" {{ request('tipe') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Status:</label>
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>

            <div class="d-flex gap-1 items-end">
                <button type="submit" class="btnOutline">
                    <ion-icon name="filter-outline"></ion-icon> Filter
                </button>
                @if(request()->anyFilled(['search', 'tipe', 'status']))
                    <a href="{{ route('admin.lokasi-presensi.index') }}" class="btnOutline" title="Reset Filter">
                        <ion-icon name="refresh-outline"></ion-icon>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ── Desktop Table ── --}}
    <div class="table-responsive-desktop">
        <div class="table-wrapper-card">
            <div class="overflow-x-auto">
                <table class="table-modern">
                    <thead>
                        <tr class="table-head-row">
                            <th class="table-col-num">#</th>
                            <th class="p-3">Nama Titik Lokasi</th>
                            <th class="p-3 text-center">Tipe</th>
                            <th class="p-3">Koordinat GPS</th>
                            <th class="p-3 text-center">Radius Geofence</th>
                            <th class="p-3 text-center">Riwayat Presensi</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lokasis as $l)
                            @php
                                $tipeClass = match($l->tipe) {
                                    'pusat' => 'hadir',
                                    'cabang' => 'jam',
                                    'mitra' => 'izin',
                                    default => 'alpha',
                                };
                                $tipeLabel = match($l->tipe) {
                                    'pusat' => 'Gedung Pusat',
                                    'cabang' => 'Cabang Belajar',
                                    'mitra' => 'Mitra PKL',
                                    'kegiatan' => 'Sentra Kegiatan',
                                    default => ucfirst($l->tipe),
                                };
                            @endphp
                            <tr class="table-body-row">
                                <td class="p-3 text-center font-bold text-muted">{{ $lokasis->firstItem() + $loop->index }}</td>
                                <td class="p-3">
                                    <div class="font-bold text-dark text-md">{{ $l->nama_lokasi }}</div>
                                    <div class="text-xs text-muted">{{ $l->alamat ?: 'Tidak ada alamat tertulis' }}</div>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="badge-chip {{ $tipeClass }}">{{ $tipeLabel }}</span>
                                </td>
                                <td class="p-3">
                                    <div class="font-mono text-xs font-semibold text-primary">
                                        {{ number_format($l->latitude, 6) }}, {{ number_format($l->longitude, 6) }}
                                    </div>
                                    <a href="https://www.google.com/maps?q={{ $l->latitude }},{{ $l->longitude }}" target="_blank" class="text-xs text-muted d-inline-flex items-center gap-1 mt-1 text-no-decor hover:text-primary">
                                        <ion-icon name="open-outline"></ion-icon> Buka Google Maps
                                    </a>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="font-bold text-dark">{{ $l->radius_meter }} Meter</span>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="text-xs font-semibold text-muted">
                                        {{ $l->presensis_count + $l->presensi_karyawans_count }} Presensi
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <form method="POST" action="{{ route('admin.lokasi-presensi.toggleStatus', $l) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-status-toggle {{ $l->is_active ? 'text-success' : 'text-danger' }}" title="Klik untuk mengubah status">
                                            @if($l->is_active)
                                                <ion-icon name="checkmark-circle" class="align-middle"></ion-icon> Aktif
                                            @else
                                                <ion-icon name="close-circle" class="align-middle"></ion-icon> Non-Aktif
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td class="p-3 text-center">
                                    <div class="d-flex gap-1 justify-center">
                                        <a href="{{ route('admin.lokasi-presensi.edit', $l) }}" class="smallBtn edit btn-table-action" title="Edit Titik Lokasi">
                                            <ion-icon name="create-outline"></ion-icon> Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.lokasi-presensi.destroy', $l) }}" data-confirm="Hapus titik lokasi '{{ $l->nama_lokasi }}' dari master geofence?" data-confirm-title="Hapus Titik Lokasi" data-confirm-type="danger" data-confirm-btn="Ya, Hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="smallBtn delete btn-table-action" title="Hapus Titik">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted p-4">
                                    <ion-icon name="location-outline" class="icon-2xl d-block mx-auto mb-2 opacity-50"></ion-icon>
                                    Belum ada titik lokasi presensi yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($lokasis->hasPages())
                <div class="p-3 border-t border-slate-100 dark:border-slate-800">
                    {{ $lokasis->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ── Mobile List ── --}}
    <div class="mobile-payroll-list">
        @forelse($lokasis as $l)
            @php
                $tipeClass = match($l->tipe) {
                    'pusat' => 'hadir',
                    'cabang' => 'jam',
                    'mitra' => 'izin',
                    default => 'alpha',
                };
                $tipeLabel = match($l->tipe) {
                    'pusat' => 'Gedung Pusat',
                    'cabang' => 'Cabang Belajar',
                    'mitra' => 'Mitra PKL',
                    'kegiatan' => 'Sentra Kegiatan',
                    default => ucfirst($l->tipe),
                };
            @endphp
            <div class="payroll-mobile-card">
                <div class="pmc-header">
                    <div>
                        <h4 class="pmc-name">{{ $l->nama_lokasi }}</h4>
                        <div class="pmc-nik">{{ $l->alamat ?: 'Tidak ada alamat tertulis' }}</div>
                    </div>
                    <span class="badge-chip {{ $tipeClass }}">{{ $tipeLabel }}</span>
                </div>

                <div class="pmc-stats">
                    <div class="pmc-stat-item">
                        <div class="val text-primary">{{ $l->radius_meter }}m</div>
                        <div class="lbl">Radius Geofence</div>
                    </div>
                    <div class="pmc-stat-item">
                        <div class="val font-mono text-xs">{{ number_format($l->latitude, 4) }}, {{ number_format($l->longitude, 4) }}</div>
                        <div class="lbl">Koordinat GPS</div>
                    </div>
                    <div class="pmc-stat-item">
                        <form method="POST" action="{{ route('admin.lokasi-presensi.toggleStatus', $l) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-status-toggle w-full mt-1 {{ $l->is_active ? 'text-success' : 'text-danger' }}">
                                {{ $l->is_active ? '🟢 Aktif' : '🔴 Non-Aktif' }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="pmc-footer">
                    <div class="text-xs text-muted">
                        {{ $l->presensis_count + $l->presensi_karyawans_count }} Presensi Tercatat
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.lokasi-presensi.edit', $l) }}" class="smallBtn edit btn-table-action">
                            <ion-icon name="create-outline"></ion-icon> Edit
                        </a>
                        <form method="POST" action="{{ route('admin.lokasi-presensi.destroy', $l) }}" data-confirm="Hapus titik lokasi '{{ $l->nama_lokasi }}'?" data-confirm-title="Hapus Titik Lokasi" data-confirm-type="danger" data-confirm-btn="Ya, Hapus">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="smallBtn delete btn-table-action">
                                <ion-icon name="trash-outline"></ion-icon>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center table-empty-cell text-muted">
                <ion-icon name="location-outline" class="icon-2xl d-block mx-auto mb-2 opacity-50"></ion-icon>
                Belum ada titik lokasi presensi yang terdaftar.
            </div>
        @endforelse
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
            marker.bindPopup('<b>🏢 ' + loc.nama_lokasi + '</b><br><span style="font-size:11px;">' + (loc.alamat || '') + '</span><br><b>Radius Geofence:</b> ' + rad + ' Meter');

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
    });
</script>
@endsection
