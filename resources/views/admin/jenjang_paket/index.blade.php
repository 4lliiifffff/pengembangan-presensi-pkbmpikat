@extends('layouts.admin')

@section('title', 'Master Jenjang & Program Paket - Admin')

@section('content')

    <div class="laporanPageWrapper">
        {{-- ── Header Card ── --}}
        <div class="laporanHeader">
            <div class="laporanHeaderCard">
                <div class="laporanHeaderInfo">
                    <div class="laporanHeaderLabel">MASTER PENDIDIKAN &amp; KESETARAAN</div>
                    <h1 class="laporanHeaderTitle">Master Jenjang &amp; Program Paket</h1>
                    <div class="laporanHeaderSub">Struktur program kesetaraan (Paket A, B, C) dan pelatihan kejuruan PKBM PIKAT</div>
                    <p class="laporanHeaderDesc">Kelola klasifikasi jenjang, kode sistem, dan format tingkatan rombel belajar secara dinamis.</p>
                </div>
                <div class="laporanHeaderActions header-actions-group">
                    <a href="{{ route('admin.jenjang-paket.create') }}" class="profileBtnPrimary">
                        <ion-icon name="add-circle-outline" class="icon-sm"></ion-icon> Tambah Jenjang Baru
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Banner Informasi Kontekstual ── --}}
        <div class="mb-4 p-4 rounded-xl border-base bg-card-alt flex items-start gap-3">
            <div class="text-primary text-xl mt-0.5">
                <ion-icon name="information-circle"></ion-icon>
            </div>
            <div class="text-sm">
                <div class="font-bold text-dark">Panduan Master Jenjang &amp; Program:</div>
                <div class="text-muted mt-1 leading-relaxed">
                    Setiap rombel kelas di PKBM PIKAT dinaungi oleh jenjang program kesetaraan (<strong>Paket A</strong> setara SD, <strong>Paket B</strong> setara SMP, <strong>Paket C</strong> setara SMA) maupun program vokasi/kursus. Pengaturan ini secara otomatis mengelompokkan siswa, kurikulum, serta acuan presensi di seluruh sistem.
                </div>
            </div>
        </div>

        {{-- ── KPI Summary Cards ── --}}
        <div class="kpi-grid mb-4">
            <div class="kpi-card indigo">
                <div class="kpi-top">
                    <div class="kpi-label">Total Program Jenjang</div>
                    <div class="kpi-icon-wrap">
                        <ion-icon name="layers-outline"></ion-icon>
                    </div>
                </div>
                <div>
                    <div class="kpi-val">{{ $stats['total'] }} Program</div>
                    <div class="kpi-sub">Terdaftar di kurikulum lembaga</div>
                </div>
            </div>

            <div class="kpi-card emerald">
                <div class="kpi-top">
                    <div class="kpi-label">Program Aktif</div>
                    <div class="kpi-icon-wrap">
                        <ion-icon name="checkmark-done-circle-outline"></ion-icon>
                    </div>
                </div>
                <div>
                    <div class="kpi-val">{{ $stats['active'] }} Program</div>
                    <div class="kpi-sub">Dapat dipilih saat membuat kelas</div>
                </div>
            </div>

            <div class="kpi-card amber">
                <div class="kpi-top">
                    <div class="kpi-label">Rombel / Kelas Terkait</div>
                    <div class="kpi-icon-wrap">
                        <ion-icon name="school-outline"></ion-icon>
                    </div>
                </div>
                <div>
                    <div class="kpi-val">{{ $stats['total_kelas'] }} Kelas</div>
                    <div class="kpi-sub">Rombel aktif bernaung di jenjang ini</div>
                </div>
            </div>
        </div>

        {{-- ── Filter Card ── --}}
        <div class="laporanFilterCard mb-4">
            <div class="laporanFilterHeaderMobile">
                <div class="filterMobileSummary">
                    <span class="filterMobileIcon"><ion-icon name="filter-outline"></ion-icon></span>
                    <div class="filterMobileTexts">
                        <span class="filterMobileTitle">Filter Jenjang Paket</span>
                        <span class="filterMobileSub">{{ request('search') ? 'Cari: ' . request('search') : 'Semua Program' }}</span>
                    </div>
                </div>
                <button type="button" class="btnToggleFilterMobile" aria-expanded="false">
                    <span class="filterToggleText">Buka Filter</span>
                    <ion-icon name="chevron-down-outline" class="filterToggleIcon"></ion-icon>
                </button>
            </div>
            <form method="GET" action="{{ route('admin.jenjang-paket.index') }}" class="laporanFilterFormBody">
                <div class="laporanFilterGrid">
                    <div class="filterField">
                        <label class="filterFieldLabel">Cari Program / Jenjang</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama jenjang, kode unik, atau tingkatan..." class="profileInput text-md">
                    </div>

                    <div class="filterField">
                        <label class="filterFieldLabel">Status Keaktifan</label>
                        <select name="status" class="filterSelect" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                    </div>

                    <div class="filterActions">
                        <button type="submit" class="profileBtnPrimary">
                            <ion-icon name="search-outline"></ion-icon> Cari
                        </button>
                        @if (request()->hasAny(['search', 'status']))
                            <a href="{{ route('admin.jenjang-paket.index') }}" class="btnOutline">
                                <ion-icon name="refresh-outline"></ion-icon> Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- ── Tabel Master Jenjang Paket (Desktop) ── --}}
        <div class="table-responsive-desktop">
            <div class="table-wrapper-card">
                <div class="overflow-x-auto">
                    <table class="table-modern">
                        <thead>
                            <tr class="table-head-row">
                                <th class="table-col-num">Urutan</th>
                                <th class="p-3">Jenjang &amp; Program</th>
                                <th class="p-3">Kode Sistem</th>
                                <th class="p-3">Rentang Tingkatan</th>
                                <th class="p-3 text-center">Rombel / Kelas</th>
                                <th class="p-3 text-center">Status</th>
                                <th class="p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jenjangPakets as $jp)
                                <tr class="table-body-row">
                                    <td class="p-3 text-center font-bold text-muted">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 text-slate-700 font-bold text-xs">
                                            #{{ $jp->urutan ?: $loop->iteration }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <div class="font-extrabold text-dark text-md">{{ $jp->nama_jenjang }}</div>
                                        @if ($jp->keterangan)
                                            <div class="text-xs text-muted mt-1 max-w-md leading-relaxed">{{ $jp->keterangan }}</div>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        <code class="text-xs font-bold font-monospace bg-slate-100 text-slate-800 px-2.5 py-1 rounded-md border border-slate-200">
                                            {{ $jp->kode }}
                                        </code>
                                    </td>
                                    <td class="p-3">
                                        @if ($jp->tingkat_label)
                                            <span class="app-badge badge-jenjang-paket_c">
                                                <ion-icon name="ribbon-outline" class="mr-1"></ion-icon>
                                                {{ $jp->tingkat_label }}
                                            </span>
                                        @else
                                            <span class="text-muted text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center">
                                        <a href="{{ route('admin.kelas.index', ['jenjang' => $jp->kode]) }}"
                                            class="font-extrabold text-primary text-no-decor d-inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition-colors"
                                            title="Lihat kelas terdaftar pada jenjang ini">
                                            <span>{{ $jp->kelas_count }} Rombel</span>
                                            <ion-icon name="open-outline" class="text-sm"></ion-icon>
                                        </a>
                                    </td>
                                    <td class="p-3 text-center">
                                        <form method="POST" action="{{ route('admin.jenjang-paket.toggleStatus', $jp) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="bg-none border-none cursor-pointer p-0" title="Klik untuk mengubah status aktif/nonaktif">
                                                @if ($jp->is_aktif)
                                                    <span class="app-badge badge-status-aktif">
                                                        <ion-icon name="checkmark-circle-outline" class="mr-1"></ion-icon> Aktif
                                                    </span>
                                                @else
                                                    <span class="app-badge badge-status-nonaktif">
                                                        <ion-icon name="close-circle-outline" class="mr-1"></ion-icon> Nonaktif
                                                    </span>
                                                @endif
                                            </button>
                                        </form>
                                    </td>
                                    <td class="p-3 text-center">
                                        <div class="d-flex gap-1 flex-center">
                                            <a href="{{ route('admin.jenjang-paket.edit', $jp) }}" class="smallBtn edit cursor-pointer btn-table-action" title="Edit Master Jenjang">
                                                <ion-icon name="create-outline"></ion-icon> Edit
                                            </a>

                                            <form method="POST" action="{{ route('admin.jenjang-paket.destroy', $jp) }}"
                                                data-confirm="Apakah Anda yakin ingin menghapus Jenjang {{ $jp->nama_jenjang }}? {{ $jp->kelas_count > 0 ? 'Peringatan: terdapat ' . $jp->kelas_count . ' kelas terkait.' : '' }}"
                                                data-confirm-title="Hapus Master Jenjang" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="confirm_unlink" value="1">
                                                <button type="submit" title="Hapus Master Jenjang" class="smallBtn delete cursor-pointer btn-table-action">
                                                    <ion-icon name="trash-outline"></ion-icon> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="table-empty-cell">
                                        <div class="tableEmptyState">
                                            <div class="tableEmptyIconWrap indigo">
                                                <ion-icon name="layers-outline" class="tableEmptyIcon"></ion-icon>
                                            </div>
                                            <div class="tableEmptyTitle">Tidak Ada Jenjang Paket Ditemukan</div>
                                            <div class="tableEmptyDesc">
                                                @if (request()->hasAny(['search', 'status']))
                                                    Tidak ada jenjang yang cocok dengan kriteria filter saat ini.
                                                @else
                                                    Belum ada data program jenjang paket terdaftar. Klik "Tambah Jenjang Baru" untuk menambahkan.
                                                @endif
                                            </div>
                                            @if (request()->hasAny(['search', 'status']))
                                                <div class="mt-3">
                                                    <a href="{{ route('admin.jenjang-paket.index') }}" class="btnOutline">
                                                        <ion-icon name="refresh-outline"></ion-icon> Reset Filter
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
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
            @forelse($jenjangPakets as $jp)
                <div class="data-mobile-card">
                    <div class="dmc-header">
                        <div>
                            <div class="text-xs font-bold text-muted mb-0.5">Urutan #{{ $jp->urutan ?: $loop->iteration }}</div>
                            <h3 class="dmc-title">{{ $jp->nama_jenjang }}</h3>
                            <div class="dmc-subtitle mt-1">
                                Kode: <code class="text-xs font-bold bg-slate-100 text-slate-800 px-2 py-0.5 rounded border border-slate-200">{{ $jp->kode }}</code>
                            </div>
                        </div>
                        <div class="flex-col gap-1 flex-items-end">
                            <form method="POST" action="{{ route('admin.jenjang-paket.toggleStatus', $jp) }}" class="d-inline m-0">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="bg-none border-none cursor-pointer p-0" title="Ubah status">
                                    @if ($jp->is_aktif)
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
                            <div class="dmc-label">Format Tingkat</div>
                            <div class="dmc-value">
                                @if ($jp->tingkat_label)
                                    <span class="app-badge badge-jenjang-paket_c">{{ $jp->tingkat_label }}</span>
                                @else
                                    <span class="text-muted text-xs">-</span>
                                @endif
                            </div>
                        </div>

                        <div class="dmc-field">
                            <div class="dmc-label">Rombel Terdaftar</div>
                            <div class="dmc-value">
                                <a href="{{ route('admin.kelas.index', ['jenjang' => $jp->kode]) }}" class="font-bold text-primary text-no-decor d-inline-flex items-center gap-1">
                                    <span>{{ $jp->kelas_count }} Kelas</span>
                                    <ion-icon name="open-outline"></ion-icon>
                                </a>
                            </div>
                        </div>

                        @if ($jp->keterangan)
                            <div class="dmc-field full">
                                <div class="dmc-label">Keterangan Program</div>
                                <div class="dmc-value text-muted text-xs leading-relaxed">{{ $jp->keterangan }}</div>
                            </div>
                        @endif
                    </div>

                    <div class="dmc-footer">
                        <div class="text-xs font-bold text-muted">
                            Aksi Pengelolaan
                        </div>
                        <div class="dmc-actions">
                            <a href="{{ route('admin.jenjang-paket.edit', $jp) }}" class="smallBtn edit cursor-pointer btn-table-action" title="Edit Master Jenjang">
                                <ion-icon name="create-outline"></ion-icon> Edit
                            </a>

                            <form method="POST" action="{{ route('admin.jenjang-paket.destroy', $jp) }}"
                                data-confirm="Apakah Anda yakin ingin menghapus Jenjang {{ $jp->nama_jenjang }}? {{ $jp->kelas_count > 0 ? 'Peringatan: terdapat ' . $jp->kelas_count . ' kelas terkait.' : '' }}"
                                data-confirm-title="Hapus Master Jenjang" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="confirm_unlink" value="1">
                                <button type="submit" title="Hapus Master Jenjang" class="smallBtn delete cursor-pointer btn-table-action">
                                    <ion-icon name="trash-outline"></ion-icon> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="tableEmptyCard">
                    <div class="tableEmptyIconWrap indigo">
                        <ion-icon name="layers-outline" class="tableEmptyIcon"></ion-icon>
                    </div>
                    <div class="tableEmptyTitle">Tidak Ada Jenjang Paket Ditemukan</div>
                    <div class="tableEmptyDesc">Silakan ubah filter pencarian atau tambahkan program jenjang baru.</div>
                </div>
            @endforelse
        </div>
    </div>
@endsection
