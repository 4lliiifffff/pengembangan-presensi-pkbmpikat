@extends('layouts.admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">KEBIJAKAN JAM KERJA &amp; SK</div>
                <h1 class="laporanHeaderTitle">Master Jadwal &amp; Shift Kerja</h1>
                <div class="laporanHeaderSub">Pengaturan jam masuk, jam pulang, toleransi keterlambatan, dan integrasi durasi KBM</div>
                <p class="laporanHeaderDesc">Kelola konfigurasi shift kerja resmi untuk karyawan, mahasiswa magang/PKL, dan tutor mengajar.</p>
            </div>
            <div class="laporanHeaderActions header-actions-group">
                <a href="{{ route('admin.jadwal-kerja.create') }}" class="profileBtnPrimary">
                    <ion-icon name="add-circle-outline" class="icon-sm"></ion-icon> Tambah Shift Baru
                </a>
            </div>
        </div>
    </div>

    {{-- ── Quick Stats ── --}}
    <div class="statsGrid mb-4">
        <div class="statCard">
            <div class="statNum primary">{{ $stats['total'] }}</div>
            <div class="statLabel">Total Jadwal Shift</div>
        </div>
        <div class="statCard">
            <div class="statNum emerald">{{ $stats['active'] }}</div>
            <div class="statLabel">Shift Aktif</div>
        </div>
        <div class="statCard">
            <div class="statNum cyan">{{ $stats['umum'] }}</div>
            <div class="statLabel">Shift Umum (Staf/Magang)</div>
        </div>
        <div class="statCard">
            <div class="statNum purple">{{ $stats['kbm'] }}</div>
            <div class="statLabel">Shift KBM Tutor</div>
        </div>
    </div>

    {{-- ── Filter & Search Bar ── --}}
    <form method="GET" action="{{ route('admin.jadwal-kerja.index') }}" class="mb-4">
        <div class="searchFilterCard">
            <div class="searchFilterGrid">
                <div class="searchGroup">
                    <label class="formLabel">Cari Shift</label>
                    <div class="searchInputWrap">
                        <ion-icon name="search-outline"></ion-icon>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama shift, kode, atau keterangan..." class="filterInput">
                    </div>
                </div>
                <div class="filterGroup">
                    <label class="formLabel">Jenis Shift</label>
                    <select name="jenis_shift" class="filterInput">
                        <option value="">Semua Jenis Shift</option>
                        <option value="umum" {{ request('jenis_shift') === 'umum' ? 'selected' : '' }}>Umum (Staf &amp; Magang)</option>
                        <option value="kbm" {{ request('jenis_shift') === 'kbm' ? 'selected' : '' }}>KBM Tutor (Pembelajaran)</option>
                    </select>
                </div>
                <div class="filterGroup">
                    <label class="formLabel">Status</label>
                    <select name="status" class="filterInput">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>
                <div class="filterBtnGroup">
                    <button type="submit" class="profileBtnPrimary filterSubmitBtn">
                        <ion-icon name="filter-outline"></ion-icon> Filter
                    </button>
                    @if(request()->hasAny(['search', 'jenis_shift', 'status']))
                        <a href="{{ route('admin.jadwal-kerja.index') }}" class="profileBtnSecondary filterResetBtn" title="Reset Filter">
                            <ion-icon name="refresh-outline"></ion-icon>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    {{-- ── Desktop Table ── --}}
    <div class="table-responsive-desktop">
        <div class="table-wrapper-card">
            <div class="overflow-x-auto">
                <table class="table-modern">
                    <thead>
                        <tr class="table-head-row">
                            <th class="table-col-num">#</th>
                            <th class="p-3">Nama &amp; Kode Shift</th>
                            <th class="p-3">Jenis &amp; Kategori SK</th>
                            <th class="p-3 text-center">Jam Kerja &amp; Durasi</th>
                            <th class="p-3 text-center">Batas Awal &amp; Toleransi</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shifts as $shift)
                            <tr class="table-body-row">
                                <td class="p-3 text-center font-bold text-muted">{{ $shift->urutan ?: $loop->iteration }}</td>
                                <td class="p-3">
                                    <div class="font-bold text-dark">{{ $shift->nama_shift }}</div>
                                    <div class="text-xs text-muted">Kode: <code>{{ $shift->kode_shift }}</code> &bull; {{ $shift->presensis_count + $shift->presensi_karyawans_count }} log presensi</div>
                                </td>
                                <td class="p-3">
                                    <span class="app-badge {{ $shift->jenis_shift === 'kbm' ? 'badge-layanan-dl' : 'badge-layanan-komunitas' }}">
                                        {{ $shift->jenis_label }}
                                    </span>
                                    @if($shift->kategoriTutorial)
                                        <div class="text-xs text-primary font-semibold mt-1">
                                            <ion-icon name="ribbon-outline"></ion-icon> {{ $shift->kategoriTutorial->nama_kategori }}
                                        </div>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    <div class="font-extrabold text-dark text-sm">
                                        {{ $shift->jam_masuk_formatted }} - {{ $shift->jam_pulang_formatted }} WIB
                                    </div>
                                    <div class="text-xs text-muted font-semibold">Durasi: {{ $shift->durasi_jam }} Jam</div>
                                </td>
                                <td class="p-3 text-center">
                                    <div class="text-xs font-bold text-dark">Buka: {{ $shift->batas_awal_masuk }} WIB</div>
                                    <div class="text-xs text-warning font-bold">Toleransi s.d: {{ $shift->batas_toleransi_masuk }} WIB</div>
                                </td>
                                <td class="p-3 text-center">
                                    <form method="POST" action="{{ route('admin.jadwal-kerja.toggleStatus', $shift) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" title="Klik untuk ubah status" class="border-none bg-none cursor-pointer p-0">
                                            @if($shift->is_aktif)
                                                <span class="app-badge badge-status-aktif">Aktif</span>
                                            @else
                                                <span class="app-badge badge-status-nonaktif">Nonaktif</span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td class="p-3 text-center">
                                    <div class="d-flex gap-1 justify-center">
                                        <a href="{{ route('admin.jadwal-kerja.edit', $shift) }}" title="Edit Shift" class="smallBtn edit cursor-pointer btn-table-action">
                                            <ion-icon name="create-outline"></ion-icon> Edit
                                        </a>
                                        @if(($shift->presensis_count + $shift->presensi_karyawans_count) === 0)
                                            <form method="POST" action="{{ route('admin.jadwal-kerja.destroy', $shift) }}" data-confirm="Apakah Anda yakin ingin menghapus jadwal shift ini?" data-confirm-title="Hapus Shift" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus Shift" class="smallBtn delete cursor-pointer btn-table-action">
                                                    <ion-icon name="trash-outline"></ion-icon> Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="table-empty-cell">
                                    <div class="tableEmptyState">
                                        <ion-icon name="time-outline" class="tableEmptyIcon"></ion-icon>
                                        <div class="tableEmptyTitle">Belum Ada Data Jadwal Shift</div>
                                        <div class="tableEmptyDesc">Klik tombol "Tambah Shift Baru" untuk menambahkan jadwal kerja atau sesi KBM.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($shifts->hasPages())
                <div class="p-3 border-t-base bg-card-alt">
                    {{ $shifts->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ── Mobile Card View ── --}}
    <div class="table-responsive-mobile">
        @forelse($shifts as $shift)
            <div class="card mb-3 p-3 rounded-xl border-base">
                <div class="d-flex items-center justify-between gap-2 mb-2">
                    <div>
                        <div class="font-extrabold text-dark text-md">{{ $shift->nama_shift }}</div>
                        <div class="text-xs text-muted">Kode: <code>{{ $shift->kode_shift }}</code></div>
                    </div>
                    <form method="POST" action="{{ route('admin.jadwal-kerja.toggleStatus', $shift) }}" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="border-none bg-none cursor-pointer p-0">
                            @if($shift->is_aktif)
                                <span class="app-badge badge-status-aktif">Aktif</span>
                            @else
                                <span class="app-badge badge-status-nonaktif">Nonaktif</span>
                            @endif
                        </button>
                    </form>
                </div>

                <div class="p-2 rounded-lg bg-card-alt border-base mb-2 text-xs">
                    <div class="d-flex justify-between py-1">
                        <span class="text-muted">Jam Masuk - Pulang:</span>
                        <span class="font-bold text-dark">{{ $shift->jam_masuk_formatted }} - {{ $shift->jam_pulang_formatted }} WIB ({{ $shift->durasi_jam }}j)</span>
                    </div>
                    <div class="d-flex justify-between py-1">
                        <span class="text-muted">Batas Awal:</span>
                        <span class="font-bold text-dark">{{ $shift->batas_awal_masuk }} WIB (-{{ $shift->earliest_minutes }}m)</span>
                    </div>
                    <div class="d-flex justify-between py-1">
                        <span class="text-muted">Toleransi Keterlambatan:</span>
                        <span class="font-bold text-warning">{{ $shift->batas_toleransi_masuk }} WIB (+{{ $shift->tolerance_minutes }}m)</span>
                    </div>
                    @if($shift->kategoriTutorial)
                        <div class="d-flex justify-between py-1 border-t-base mt-1 pt-1">
                            <span class="text-muted">Kategori SK:</span>
                            <span class="font-bold text-primary">{{ $shift->kategoriTutorial->nama_kategori }}</span>
                        </div>
                    @endif
                </div>

                <div class="d-flex gap-2 justify-end">
                    <a href="{{ route('admin.jadwal-kerja.edit', $shift) }}" class="profileBtnSecondary btn-sm py-1 px-3 text-xs">
                        <ion-icon name="create-outline"></ion-icon> Edit
                    </a>
                    @if(($shift->presensis_count + $shift->presensi_karyawans_count) === 0)
                        <form method="POST" action="{{ route('admin.jadwal-kerja.destroy', $shift) }}" data-confirm="Hapus jadwal shift ini?" data-confirm-title="Hapus Shift" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="profileBtnSecondary btn-sm py-1 px-3 text-xs text-danger">
                                <ion-icon name="trash-outline"></ion-icon> Hapus
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="card p-4 text-center rounded-xl border-base">
                <ion-icon name="time-outline" class="text-3xl text-muted mb-2"></ion-icon>
                <div class="font-bold text-dark">Belum ada jadwal shift</div>
            </div>
        @endforelse

        @if($shifts->hasPages())
            <div class="mt-3">
                {{ $shifts->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
