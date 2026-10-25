@extends('layouts.admin')

@section('title', 'Shift & Jam Kerja Magang - Admin')

@section('content')

    <div class="laporanPageWrapper">
        {{-- ── Header Card ── --}}
        <div class="laporanHeader">
            <div class="laporanHeaderCard">
                <div class="laporanHeaderInfo">
                    <div class="laporanHeaderLabel">KEBIJAKAN JAM PIKET OPERASIONAL</div>
                    <h1 class="laporanHeaderTitle">Shift &amp; Jam Kerja Magang (PKL)</h1>
                    <div class="laporanHeaderSub">Master jam piket, jam kepulangan, dan toleransi keterlambatan mahasiswa magang/PKL</div>
                    <p class="laporanHeaderDesc">Kelola konfigurasi jadwal shift operasional resmi untuk mahasiswa magang/PKL di lingkungan kantor PKBM Pikat.</p>
                </div>
                <div class="laporanHeaderActions header-actions-group">
                    <a href="{{ route('admin.jadwal-kerja.create') }}" class="profileBtnPrimary">
                        <ion-icon name="add-circle-outline" class="icon-sm"></ion-icon> Tambah Shift Magang
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Notice / Information Box ── --}}
        <div class="mb-4 p-4 rounded-xl border-base bg-card-alt flex items-start gap-3">
            <div class="text-primary text-xl mt-0.5">
                <ion-icon name="information-circle"></ion-icon>
            </div>
            <div class="text-sm">
                <div class="font-bold text-dark">Informasi Penting Pengaturan Shift:</div>
                <div class="text-muted mt-1 leading-relaxed">
                    Pengaturan shift ini <strong>khusus mengatur jam operasional piket Mahasiswa Magang/PKL</strong>. Untuk jadwal mengajar <strong>Tutor</strong> dan jam belajar <strong>Murid</strong>, dikelola secara independen dan dinamis melalui menu <a href="{{ route('admin.jadwal-sesi-kbm.index') }}" class="text-primary font-bold hover:underline">Jadwal Sesi KBM</a>.
                </div>
            </div>
        </div>

        {{-- ── KPI Summary Cards ── --}}
        <div class="kpi-grid mb-4">
            <div class="kpi-card emerald">
                <div class="kpi-top">
                    <div class="kpi-label">Total Shift Magang</div>
                    <div class="kpi-icon-wrap">
                        <ion-icon name="time-outline"></ion-icon>
                    </div>
                </div>
                <div>
                    <div class="kpi-val">{{ $stats['total'] }} Shift</div>
                    <div class="kpi-sub">Terdaftar di sistem presensi</div>
                </div>
            </div>

            <div class="kpi-card blue">
                <div class="kpi-top">
                    <div class="kpi-label">Shift Aktif</div>
                    <div class="kpi-icon-wrap">
                        <ion-icon name="checkmark-circle-outline"></ion-icon>
                    </div>
                </div>
                <div>
                    <div class="kpi-val">{{ $stats['active'] }} Shift</div>
                    <div class="kpi-sub">Dapat digunakan saat absensi</div>
                </div>
            </div>

            <div class="kpi-card cyan">
                <div class="kpi-top">
                    <div class="kpi-label">Rata-rata Durasi</div>
                    <div class="kpi-icon-wrap">
                        <ion-icon name="hourglass-outline"></ion-icon>
                    </div>
                </div>
                <div>
                    <div class="kpi-val">{{ $stats['avg_duration'] }} Jam</div>
                    <div class="kpi-sub">Durasi piket per hari</div>
                </div>
            </div>
        </div>

        {{-- ── Filter Card ── --}}
        <div class="laporanFilterCard mb-4">
            <div class="laporanFilterHeaderMobile">
                <div class="filterMobileSummary">
                    <span class="filterMobileIcon"><ion-icon name="filter-outline"></ion-icon></span>
                    <div class="filterMobileTexts">
                        <span class="filterMobileTitle">Filter Jadwal Shift</span>
                        <span class="filterMobileSub">{{ request('search') ? 'Cari: ' . request('search') : 'Semua Shift Magang' }}</span>
                    </div>
                </div>
                <button type="button" class="btnToggleFilterMobile" aria-expanded="false">
                    <span class="filterToggleText">Buka Filter</span>
                    <ion-icon name="chevron-down-outline" class="filterToggleIcon"></ion-icon>
                </button>
            </div>
            <form method="GET" action="{{ route('admin.jadwal-kerja.index') }}" class="laporanFilterFormBody">
                <div class="laporanFilterGrid">
                    <div class="filterField">
                        <label class="filterFieldLabel">Cari Shift</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama shift, kode, atau keterangan..." class="profileInput text-md">
                    </div>

                    <div class="filterField">
                        <label class="filterFieldLabel">Status Shift</label>
                        <select name="status" class="filterSelect" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                    </div>

                    <div class="filterActionsGroup">
                        <button type="submit" class="profileBtnPrimary">
                            <ion-icon name="filter-outline"></ion-icon> Cari
                        </button>
                        @if(request()->hasAny(['search', 'status']))
                            <a href="{{ route('admin.jadwal-kerja.index') }}" class="btnOutline" title="Reset Filter">
                                <ion-icon name="refresh-outline"></ion-icon> Reset
                            </a>
                        @endif
                    </div>
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
                                <th class="p-3">Nama &amp; Kode Shift</th>
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
                                        <div class="text-xs text-muted">Kode: <code class="badge-code font-bold">{{ $shift->kode_shift }}</code> &bull; {{ $shift->presensi_karyawans_count }} sesi hadir</div>
                                        @if($shift->keterangan)
                                            <div class="text-xs text-muted mt-1 italic">{{ $shift->keterangan }}</div>
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
                                        <div class="text-xs text-warning font-bold">Toleransi: {{ $shift->batas_toleransi_masuk }} WIB</div>
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
                                            <a href="{{ route('admin.jadwal-kerja.edit', $shift) }}" title="Edit Shift"
                                                class="smallBtn edit cursor-pointer btn-table-action">
                                                <ion-icon name="create-outline"></ion-icon> Edit
                                            </a>
                                            @if(($shift->presensis_count + $shift->presensi_karyawans_count) === 0)
                                                <form method="POST" action="{{ route('admin.jadwal-kerja.destroy', $shift) }}"
                                                    data-confirm="Apakah Anda yakin ingin menghapus jadwal shift ini?"
                                                    data-confirm-title="Hapus Shift" data-confirm-type="danger"
                                                    data-confirm-btn="Ya, Hapus" class="d-inline m-0">
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
                                    <td colspan="6" class="table-empty-cell">
                                        <div class="tableEmptyState">
                                            <div class="tableEmptyIconWrap">
                                                <ion-icon name="time-outline" class="tableEmptyIcon"></ion-icon>
                                            </div>
                                            <div class="tableEmptyTitle">Belum Ada Data Jadwal Shift Magang</div>
                                            <div class="tableEmptyDesc">Klik tombol "Tambah Shift Magang" untuk menambahkan jadwal jam piket magang baru.</div>
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

        {{-- ── Mobile Cards List (Layar HP / Tablet) ── --}}
        <div class="mobile-card-list">
            @forelse($shifts as $shift)
                <div class="data-mobile-card">
                    <div class="dmc-header">
                        <div>
                            <h3 class="dmc-title">{{ $shift->nama_shift }}</h3>
                            <div class="dmc-subtitle">
                                Kode: <code class="text-xs font-bold rounded-sm badge-code">{{ $shift->kode_shift }}</code>
                                &bull; {{ $shift->presensi_karyawans_count }} sesi hadir
                            </div>
                        </div>
                        <div class="flex-col gap-1 flex-items-end">
                            <form method="POST" action="{{ route('admin.jadwal-kerja.toggleStatus', $shift) }}" class="d-inline m-0">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="bg-none border-none cursor-pointer p-0" title="Ubah status">
                                    @if($shift->is_aktif)
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
                            <div class="dmc-label">Jam Operasional</div>
                            <div class="dmc-value">
                                <span class="font-bold text-dark text-sm">{{ $shift->jam_masuk_formatted }} - {{ $shift->jam_pulang_formatted }} WIB</span>
                                <div class="text-xs text-muted font-semibold">Durasi: {{ $shift->durasi_jam }} Jam</div>
                            </div>
                        </div>

                        <div class="dmc-field">
                            <div class="dmc-label">Batas Awal Masuk</div>
                            <div class="dmc-value">
                                <span class="font-bold text-dark text-xs">{{ $shift->batas_awal_masuk }} WIB</span>
                                <span class="text-muted text-xs">(-{{ $shift->earliest_minutes }} mnt)</span>
                            </div>
                        </div>

                        <div class="dmc-field">
                            <div class="dmc-label">Batas Toleransi</div>
                            <div class="dmc-value">
                                <span class="font-bold text-warning text-xs">{{ $shift->batas_toleransi_masuk }} WIB</span>
                                <span class="text-muted text-xs">(+{{ $shift->tolerance_minutes }} mnt)</span>
                            </div>
                        </div>
                    </div>

                    @if($shift->keterangan)
                        <div class="text-xs text-muted mt-2 pt-2 border-t-base">
                            <span class="font-semibold text-dark">Catatan:</span> {{ $shift->keterangan }}
                        </div>
                    @endif

                    <div class="dmc-actions mt-3">
                        <a href="{{ route('admin.jadwal-kerja.edit', $shift) }}"
                            class="smallBtn edit cursor-pointer btn-table-action" title="Edit Shift">
                            <ion-icon name="create-outline"></ion-icon> Edit
                        </a>
                        @if(($shift->presensis_count + $shift->presensi_karyawans_count) === 0)
                            <form method="POST" action="{{ route('admin.jadwal-kerja.destroy', $shift) }}"
                                data-confirm="Apakah Anda yakin ingin menghapus jadwal shift ini?" data-confirm-title="Hapus Shift"
                                data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="smallBtn delete cursor-pointer btn-table-action" title="Hapus Shift">
                                    <ion-icon name="trash-outline"></ion-icon> Hapus
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="tableEmptyCard">
                    <div class="tableEmptyIconWrap">
                        <ion-icon name="time-outline" class="tableEmptyIcon"></ion-icon>
                    </div>
                    <div class="tableEmptyTitle">Belum Ada Data Jadwal Shift Magang</div>
                    <div class="tableEmptyDesc">Klik tombol "Tambah Shift Magang" untuk menambahkan jadwal jam piket magang baru.</div>
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