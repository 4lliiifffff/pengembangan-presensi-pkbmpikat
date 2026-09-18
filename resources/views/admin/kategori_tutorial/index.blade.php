@extends('layouts.admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">KEBIJAKAN HONORARIUM &amp; SK</div>
                <h1 class="laporanHeaderTitle">Master Kategori &amp; Tarif SK</h1>
                <div class="laporanHeaderSub">Kategori pembelajaran, durasi acuan, status ABK, dan besaran tarif honor tutor</div>
                <p class="laporanHeaderDesc">Standar acuan honorarium mengajar per pertemuan sesuai Surat Keputusan Kepala PKBM PIKAT.</p>
            </div>
            <div class="laporanHeaderActions header-actions-group">
                <a href="{{ route('admin.kategori-tutorial.create') }}" class="profileBtnPrimary">
                    <ion-icon name="add-circle-outline" class="icon-sm"></ion-icon> Tambah Kategori SK
                </a>
            </div>
        </div>
    </div>

    {{-- ── Grid / List Kategori Tutorial (Desktop) ── --}}
    <div class="table-responsive-desktop">
        <div class="table-wrapper-card">
            <div class="overflow-x-auto">
                <table class="table-modern">
                    <thead>
                        <tr class="table-head-row">
                            <th class="table-col-num">#</th>
                            <th class="p-3">Nama Kategori SK</th>
                            <th class="p-3">Layanan &amp; Durasi</th>
                            <th class="p-3 text-center">Klasifikasi</th>
                            <th class="table-col-action p-3">Nominal Honor</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kategoriList as $k)
                            <tr class="table-body-row">
                                <td class="p-3 text-center font-bold text-muted">{{ $k->urutan ?: $loop->iteration }}</td>
                                <td class="p-3">
                                    <div class="font-bold text-dark">{{ $k->nama_kategori }}</div>
                                    <div class="text-xs text-muted">{{ $k->presensis_count }} sesi presensi tercatat</div>
                                </td>
                                <td class="p-3">
                                    <div class="d-flex items-center gap-1">
                                        <span class="app-badge {{ $k->jenis_layanan_badge_class }}">
                                            {{ $k->jenis_layanan_label }}
                                        </span>
                                        <span class="font-bold text-dark text-sm">{{ $k->durasi_jam }} Jam</span>
                                    </div>
                                </td>
                                <td class="p-3 text-center">
                                    <div class="d-flex gap-1 justify-center flex-wrap">
                                        @if($k->is_abk)
                                            <span class="app-badge badge-abk">ABK</span>
                                        @else
                                            <span class="app-badge badge-reguler">Reguler</span>
                                        @endif

                                        @if($k->is_gabungan)
                                            <span class="app-badge badge-layanan-gabungan">Rombel Gabungan</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="table-col-action p-3">
                                    <span class="font-extrabold text-success text-md">{{ $k->formatted_nominal_honor }}</span>
                                    <span class="text-xs text-muted d-block">/ pertemuan</span>
                                </td>
                                <td class="p-3 text-center">
                                    <form method="POST" action="{{ route('admin.kategori-tutorial.toggleStatus', $k) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" title="Klik untuk ubah status" class="border-none bg-none cursor-pointer p-0">
                                            @if($k->is_aktif)
                                                <span class="app-badge badge-status-aktif">Aktif</span>
                                            @else
                                                <span class="app-badge badge-status-nonaktif">Nonaktif</span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td class="p-3 text-center">
                                    <div class="d-flex gap-1 justify-center">
                                        <a href="{{ route('admin.kategori-tutorial.edit', $k) }}" title="Edit Kategori" class="smallBtn edit cursor-pointer btn-table-action">
                                            <ion-icon name="create-outline"></ion-icon> Edit
                                        </a>
                                        @if($k->presensis_count === 0)
                                            <form method="POST" action="{{ route('admin.kategori-tutorial.destroy', $k) }}" data-confirm="Apakah Anda yakin ingin menghapus kategori tutorial ini?" data-confirm-title="Hapus Kategori" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus Kategori" class="smallBtn delete cursor-pointer btn-table-action">
                                                    <ion-icon name="trash-outline"></ion-icon> Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted p-4">
                                    Belum ada data kategori tutorial. Silakan klik tombol "Tambah Kategori SK" di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Mobile Cards List (Layar HP / Tablet) ── --}}
    <div class="mobile-card-list">
        @forelse($kategoriList as $k)
            <div class="data-mobile-card">
                <div class="dmc-header">
                    <div>
                        <h3 class="dmc-title">{{ $k->nama_kategori }}</h3>
                        <div class="dmc-subtitle">{{ $k->presensis_count }} sesi presensi tercatat</div>
                    </div>
                    <div class="flex-col gap-1 flex-items-end">
                        <form method="POST" action="{{ route('admin.kategori-tutorial.toggleStatus', $k) }}" class="d-inline m-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="bg-none border-none cursor-pointer p-0" title="Ubah status">
                                @if($k->is_aktif)
                                    <span class="app-badge badge-status-aktif">Aktif</span>
                                @else
                                    <span class="app-badge badge-status-nonaktif">Nonaktif</span>
                                @endif
                            </button>
                        </form>
                    </div>
                </div>

                <div class="dmc-grid">
                    <div class="dmc-field">
                        <div class="dmc-label">Layanan &amp; Durasi</div>
                        <div class="dmc-value">
                            <span class="app-badge {{ $k->jenis_layanan_badge_class }}">
                                {{ $k->jenis_layanan_label }}
                            </span>
                            <span class="font-bold text-dark text-xs ml-1">{{ $k->durasi_jam }} Jam</span>
                        </div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Klasifikasi</div>
                        <div class="dmc-value">
                            @if($k->is_abk)
                                <span class="app-badge badge-abk">ABK</span>
                            @else
                                <span class="app-badge badge-reguler">Reguler</span>
                            @endif

                            @if($k->is_gabungan)
                                <span class="app-badge badge-layanan-gabungan">Gabungan</span>
                            @endif
                        </div>
                    </div>

                    <div class="dmc-field full">
                        <div class="dmc-label">Nominal Honor / Pertemuan</div>
                        <div class="dmc-value font-extrabold text-success text-md">
                            {{ $k->formatted_nominal_honor }}
                        </div>
                    </div>
                </div>

                <div class="dmc-footer">
                    <div class="text-xs font-bold text-muted">
                        #{{ $k->urutan ?: $loop->iteration }}
                    </div>
                    <div class="dmc-actions">
                        <a href="{{ route('admin.kategori-tutorial.edit', $k) }}" title="Edit Kategori" class="smallBtn edit cursor-pointer btn-table-action">
                            <ion-icon name="create-outline"></ion-icon> Edit
                        </a>
                        @if($k->presensis_count === 0)
                            <form method="POST" action="{{ route('admin.kategori-tutorial.destroy', $k) }}" data-confirm="Apakah Anda yakin ingin menghapus kategori tutorial ini?" data-confirm-title="Hapus Kategori" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Hapus Kategori" class="smallBtn delete cursor-pointer btn-table-action">
                                    <ion-icon name="trash-outline"></ion-icon> Hapus
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="data-mobile-card table-empty-cell">
                <div class="font-bold text-md mb-1">Belum Ada Kategori Tutorial</div>
                <div class="text-sm">Silakan klik tombol "Tambah Kategori SK" di atas untuk mendaftarkan kategori baru.</div>
            </div>
        @endforelse
    </div>
</div>
@endsection
