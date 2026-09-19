@extends('layouts.admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">MASTER PENDIDIKAN &amp; KURSUS</div>
                <h1 class="laporanHeaderTitle">Master Jenjang &amp; Program Paket</h1>
                <div class="laporanHeaderSub">Program pendidikan kesetaraan (Paket A/B/C), vokasi keterampilan, dan kursus</div>
                <p class="laporanHeaderDesc">Kelola hierarki jenjang, kode sistem, dan format tingkatan kelas secara dinamis.</p>
            </div>
            <div class="laporanHeaderActions header-actions-group">
                <a href="{{ route('admin.jenjang-paket.create') }}" class="profileBtnPrimary">
                    <ion-icon name="add-circle-outline" class="icon-sm"></ion-icon> Tambah Jenjang Baru
                </a>
            </div>
        </div>
    </div>

    {{-- Banner Edukasi --}}
    <div class="info-callout-box mt-0 mb-4 d-flex items-center gap-3">
        <div class="icon-xl text-primary flex-shrink-0">
            <ion-icon name="information-circle-outline"></ion-icon>
        </div>
        <div class="info-callout-desc text-sm">
            Daftar Jenjang &amp; Program Paket di bawah ini digunakan secara <b>dinamis</b> pada form pembuatan kelas, filter rombel, <b>Universal Package Guard</b>, dan penentuan <b>Sesi Gabungan Komunitas</b>. Anda dapat menambah jenjang baru (misal: <i>Keaksaraan Fungsional, Vokasi Otomotif</i>) kapan saja.
        </div>
    </div>

    {{-- Tabel Master Jenjang Paket (Desktop) --}}
    <div class="table-responsive-desktop">
        <div class="table-wrapper-card">
            <div class="overflow-x-auto">
                <table class="table-modern">
                    <thead>
                        <tr class="table-head-row">
                            <th class="table-col-num">Urutan</th>
                            <th class="p-3">Kode Sistem</th>
                            <th class="p-3">Nama Jenjang &amp; Program</th>
                            <th class="p-3">Format Tingkat</th>
                            <th class="p-3 text-center">Rombel / Kelas</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jenjangPakets as $jp)
                            <tr class="table-body-row">
                                <td class="p-3 text-center font-bold text-muted">{{ $jp->urutan ?: $loop->iteration }}</td>
                                <td class="p-3">
                                    <code class="text-sm font-bold text-dark rounded-sm badge-code">{{ $jp->kode }}</code>
                                </td>
                                <td class="p-3">
                                    <div class="font-extrabold text-dark text-md">{{ $jp->nama_jenjang }}</div>
                                    @if($jp->keterangan)
                                        <div class="text-sm text-muted mt-1">{{ $jp->keterangan }}</div>
                                    @endif
                                </td>
                                <td class="p-3">
                                    @if($jp->tingkat_label)
                                        <span class="app-badge badge-jenjang-paket_c">
                                            {{ $jp->tingkat_label }}
                                        </span>
                                    @else
                                        <span class="text-muted text-xs">-</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    <a href="{{ route('admin.kelas.index', ['jenjang' => $jp->kode]) }}" class="font-extrabold text-primary text-no-decor d-inline-flex items-center gap-1">
                                        <span>{{ $jp->kelas_count }} Kelas</span>
                                        <ion-icon name="open-outline" class="text-md"></ion-icon>
                                    </a>
                                </td>
                                <td class="p-3 text-center">
                                    <form method="POST" action="{{ route('admin.jenjang-paket.toggleStatus', $jp) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="bg-none border-none cursor-pointer p-0" title="Klik untuk ubah status">
                                            @if($jp->is_aktif)
                                                <span class="app-badge badge-status-aktif">Aktif</span>
                                            @else
                                                <span class="app-badge badge-status-nonaktif">Nonaktif</span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td class="p-3 text-center">
                                    <div class="d-flex gap-1 flex-center">
                                        <a href="{{ route('admin.jenjang-paket.edit', $jp) }}" class="smallBtn edit cursor-pointer btn-table-action" title="Edit Master Jenjang">
                                            <ion-icon name="create-outline"></ion-icon> Edit
                                        </a>

                                        <form method="POST" action="{{ route('admin.jenjang-paket.destroy', $jp) }}" data-confirm="Apakah Anda yakin ingin menghapus Jenjang {{ $jp->nama_jenjang }}? {{ $jp->kelas_count > 0 ? 'Peringatan: terdapat ' . $jp->kelas_count . ' kelas terkait.' : '' }}" data-confirm-title="Hapus Master Jenjang" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
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
                                    <div class="tableEmptyTitle">Belum Ada Jenjang Paket</div>
                                    <div class="tableEmptyDesc">Klik tombol "Tambah Jenjang Baru" untuk mendaftarkan program paket kesetaraan.</div>
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
                        <h3 class="dmc-title">{{ $jp->nama_jenjang }}</h3>
                        <div class="dmc-subtitle">
                            Kode: <code class="text-xs font-bold rounded-sm badge-code">{{ $jp->kode }}</code>
                        </div>
                    </div>
                    <div class="flex-col gap-1 flex-items-end">
                        <form method="POST" action="{{ route('admin.jenjang-paket.toggleStatus', $jp) }}" class="d-inline m-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="bg-none border-none cursor-pointer p-0" title="Ubah status aktif/nonaktif">
                                @if($jp->is_aktif)
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
                            @if($jp->tingkat_label)
                                <span class="app-badge badge-jenjang-paket_c">{{ $jp->tingkat_label }}</span>
                            @else
                                <span class="text-muted">-</span>
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

                    @if($jp->keterangan)
                        <div class="dmc-field full">
                            <div class="dmc-label">Keterangan</div>
                            <div class="dmc-value text-muted text-xs">{{ $jp->keterangan }}</div>
                        </div>
                    @endif
                </div>

                <div class="dmc-footer">
                    <div class="text-xs font-bold text-muted">
                        Urutan: #{{ $jp->urutan ?: $loop->iteration }}
                    </div>
                    <div class="dmc-actions">
                        <a href="{{ route('admin.jenjang-paket.edit', $jp) }}" class="smallBtn edit cursor-pointer btn-table-action" title="Edit Master Jenjang">
                            <ion-icon name="create-outline"></ion-icon> Edit
                        </a>

                        <form method="POST" action="{{ route('admin.jenjang-paket.destroy', $jp) }}" data-confirm="Apakah Anda yakin ingin menghapus Jenjang {{ $jp->nama_jenjang }}? {{ $jp->kelas_count > 0 ? 'Peringatan: terdapat ' . $jp->kelas_count . ' kelas terkait.' : '' }}" data-confirm-title="Hapus Master Jenjang" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
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
                <div class="tableEmptyTitle">Belum Ada Jenjang Paket</div>
                <div class="tableEmptyDesc">Klik tombol "Tambah Jenjang Baru" untuk mendaftarkan program paket kesetaraan.</div>
            </div>
        @endforelse
    </div>
</div>
@endsection
