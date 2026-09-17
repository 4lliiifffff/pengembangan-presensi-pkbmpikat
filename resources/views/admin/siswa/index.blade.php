@extends('layouts.admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">MANAJEMEN PESERTA DIDIK</div>
                <h1 class="laporanHeaderTitle">Data Siswa</h1>
                <div class="laporanHeaderSub">Kelola data peserta didik, wali murid, status siklus, dan rombongan belajar</div>
                <p class="laporanHeaderDesc">Daftar siswa, pemetaan paket kesetaraan, data ABK, dan histori rombel.</p>
            </div>
            <div class="laporanHeaderActions header-actions-group">
                <a href="{{ route('admin.siswa.exportExcel') }}" class="profileBtnPrimary btn-action-success">
                    Export Excel
                </a>
                <button type="button" onclick="document.getElementById('importSiswaModal').style.display='flex'" class="profileBtnPrimary btn-action-info">
                    Import Siswa
                </button>
                <a href="{{ route('admin.siswa.create') }}" class="profileBtnPrimary">
                    Tambah Siswa
                </a>
            </div>
        </div>
    </div>

    {{-- ── Modal Impor Siswa ── --}}
    <div id="importSiswaModal" class="app-modal-backdrop">
        <div class="app-modal-card">
            <div class="app-modal-header">
                <h3 class="app-modal-title">Impor Data Siswa Massal</h3>
                <button type="button" onclick="document.getElementById('importSiswaModal').style.display='none'" class="app-modal-close">&times;</button>
            </div>
            <p class="app-modal-desc">
                Unggah berkas spreadsheet Excel/CSV untuk mendaftarkan siswa baru secara massal atau memperbarui data siswa berdasarkan nomor absen (NIS).
            </p>
            <div class="mb-4">
                <a href="{{ route('admin.siswa.downloadTemplate') }}" class="btnOutline">
                    Download Template Siswa (.xlsx)
                </a>
            </div>
            <form method="POST" action="{{ route('admin.siswa.importExcel') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-field-wrapper">
                    <label class="form-field-label">Pilih Berkas Data Siswa:</label>
                    <div class="fileUploadBox">
                        <input type="file" name="file_excel" id="siswaFileInput" accept=".xlsx,.xls,.csv" required onchange="handleFileSelected(this, 'siswaFileFeedback')">
                        <div class="fileUploadText">Pilih atau seret berkas ke sini</div>
                        <div class="fileUploadSubtext">
                            <span class="fileUploadInfoPill">Format: .XLSX, .CSV</span>
                            <span class="fileUploadInfoPill">Maks: 5 MB</span>
                        </div>
                    </div>
                    <div id="siswaFileFeedback" class="fileUploadFeedback"></div>
                </div>
                <div class="app-modal-footer">
                    <button type="button" onclick="document.getElementById('importSiswaModal').style.display='none'" class="profileBtnDanger w-auto px-4 text-md">Batal</button>
                    <button type="submit" class="profileBtnPrimary w-auto px-4 text-md">Unggah &amp; Impor</button>
                </div>
            </form>
        </div>
    </div>

    <script >
        function handleFileSelected(input, feedbackId) {
            const feedback = document.getElementById(feedbackId);
            if (!feedback) return;
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const sizeKb = Math.round(file.size / 1024);
                feedback.innerHTML = '<span >' + file.name + ' (' + sizeKb + ' KB)</span>';
                feedback.style.display = 'flex';
            } else {
                feedback.style.display = 'none';
            }
        }
    </script>

    {{-- ── Filter Card ── --}}
    <div class="laporanFilterCard">
        <form method="GET" action="{{ route('admin.siswa.index') }}">
            <div class="laporanFilterGrid">
                <div class="filterField">
                    <label class="filterFieldLabel">Pencarian</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama, Absen/NIS, Wali..." class="profileInput text-md">
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Status Siswa</label>
                    <select name="status" class="filterSelect">
                        <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif ({{ $stats['aktif'] ?? 0 }})</option>
                        <option value="alumni" {{ $status === 'alumni' ? 'selected' : '' }}>Lulus / Alumni ({{ $stats['alumni'] ?? 0 }})</option>
                        <option value="cuti" {{ $status === 'cuti' ? 'selected' : '' }}>Cuti Belajar ({{ $stats['cuti'] ?? 0 }})</option>
                        <option value="nonaktif" {{ $status === 'nonaktif' ? 'selected' : '' }}>Nonaktif / Keluar ({{ $stats['nonaktif'] ?? 0 }})</option>
                        <option value="semua" {{ $status === 'semua' ? 'selected' : '' }}>Semua Status ({{ $stats['total'] ?? 0 }})</option>
                    </select>
                </div>

                <div class="filter-actions-full">
                    <button type="submit" class="btn-filter-primary">
                        <ion-icon name="filter-outline"></ion-icon> Terapkan Filter
                    </button>
                    <a href="{{ route('admin.siswa.index') }}" class="btn-filter-reset">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Section Title ── --}}
    <div class="sectionRow">
        <h2 >Daftar Peserta Didik</h2>
        <span class="badgeCount">{{ $siswas->total() }} Siswa Terdata</span>
    </div>

    {{-- ── Data Table Container (Desktop) ── --}}
    <div class="table-responsive-desktop">
        <div class="tableContainer m-0 mb-4 rounded-xl border-base">
            <table class="laporanTable">
                <thead >
                    <tr >
                        <th class="table-col-num">NO</th>
                        <th >ABSEN / NIS</th>
                        <th >NAMA LENGKAP</th>
                        <th >KELAS / ROMBEL</th>
                        <th >JENJANG</th>
                        <th >STATUS SIKLUS</th>
                        <th >NAMA WALI</th>
                        <th >TUTOR</th>
                        <th class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody >
                    @forelse($siswas as $index => $siswa)
                        <tr >
                            <td class="p-3 font-bold text-muted">
                                {{ $siswas->firstItem() + $index }}
                            </td>
                            <td class="font-bold text-dark">
                                {{ $siswa->no_absen ?? '-' }}
                            </td>
                            <td class="font-extrabold text-dark">
                                {{ $siswa->nama_siswa }}
                            </td>
                            <td class="font-semibold">
                                {{ $siswa->relKelas->nama_kelas ?? '-' }}
                            </td>
                            <td class="font-bold text-primary">
                                {{ $siswa->jenjang_paket_label }}
                            </td>
                            <td >
                                <div class="d-flex gap-1 flex-wrap items-center">
                                    <span class="app-badge badge-status-{{ $siswa->status_siswa }}">
                                        {{ $siswa->status_label }}
                                    </span>
                                    @if($siswa->is_abk)
                                        <span class="app-badge badge-abk">
                                            ABK
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-muted text-sm">
                                {{ $siswa->nama_wali ?? '-' }}
                            </td>
                            <td class="text-sm font-semibold">
                                {{ $siswa->tutor->nama_lengkap ?? '-' }}
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 flex-center flex-wrap">
                                    <a class="smallBtn btn-status-toggle btn-table-action" href="{{ route('admin.siswa.show', $siswa) }}" title="Lihat Detail Siswa">
                                        Detail
                                    </a>

                                    <a href="{{ route('admin.siswa.edit', $siswa) }}" class="smallBtn edit btn-table-action" title="Edit Data Siswa">
                                        Edit
                                    </a>

                                    <form method="POST" action="{{ route('admin.siswa.destroy', $siswa) }}" data-confirm="Arsipkan data siswa ini (Soft Delete)? Seluruh data riwayat presensi dan honor mengajar tutor akan tetap aman tersimpan." data-confirm-title="Arsipkan Siswa" data-confirm-type="danger" data-confirm-btn="Ya, Arsipkan" class="d-inline m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Arsipkan Siswa" class="smallBtn delete cursor-pointer btn-table-action">
                                            Arsipkan
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr >
                            <td colspan="9" class="table-empty-cell">
                                <div class="font-bold text-md mb-1">Belum Ada Data Siswa</div>
                                <div class="text-sm">Tidak ada data peserta didik pada filter yang dipilih.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Mobile Cards List (Layar HP / Tablet) ── --}}
    <div class="mobile-card-list">
        @forelse($siswas as $index => $siswa)
            <div class="data-mobile-card">
                <div class="dmc-header">
                    <div >
                        <h3 class="dmc-title">{{ $siswa->nama_siswa }}</h3>
                        <div class="dmc-subtitle">Absen / NIS: {{ $siswa->no_absen ?? '-' }}</div>
                    </div>
                    <div class="flex-col gap-1 flex-items-end">
                        <span class="app-badge badge-status-{{ $siswa->status_siswa }}">
                            {{ $siswa->status_label }}
                        </span>
                        @if($siswa->is_abk)
                            <span class="app-badge badge-abk">
                                ABK
                            </span>
                        @endif
                    </div>
                </div>

                <div class="dmc-grid">
                    <div class="dmc-field">
                        <div class="dmc-label">Kelas / Rombel</div>
                        <div class="dmc-value">{{ $siswa->relKelas->nama_kelas ?? '-' }}</div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Jenjang Paket</div>
                        <div class="dmc-value text-primary">{{ $siswa->jenjang_paket_label }}</div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Tutor Pembimbing</div>
                        <div class="dmc-value text-sm">{{ $siswa->tutor->nama_lengkap ?? '-' }}</div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Wali Murid</div>
                        <div class="dmc-value text-sm">{{ $siswa->nama_wali ?? '-' }}</div>
                    </div>
                </div>

                <div class="dmc-footer">
                    <div class="text-xs font-bold text-muted">
                        No. {{ $siswas->firstItem() + $index }}
                    </div>
                    <div class="dmc-actions">
                        <a class="smallBtn btn-status-toggle btn-table-action" href="{{ route('admin.siswa.show', $siswa) }}" title="Lihat Detail Siswa">
                            Detail
                        </a>

                        <a href="{{ route('admin.siswa.edit', $siswa) }}" class="smallBtn edit btn-table-action" title="Edit Data Siswa">
                            Edit
                        </a>

                        <form method="POST" action="{{ route('admin.siswa.destroy', $siswa) }}" data-confirm="Arsipkan data siswa ini (Soft Delete)? Seluruh data riwayat presensi dan honor mengajar tutor akan tetap aman tersimpan." data-confirm-title="Arsipkan Siswa" data-confirm-type="danger" data-confirm-btn="Ya, Arsipkan" class="d-inline m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="smallBtn delete cursor-pointer btn-table-action" title="Arsipkan Siswa">
                                Arsipkan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        @empty
            <div class="data-mobile-card table-empty-cell">
                <div class="font-bold text-md mb-1">Belum Ada Data Siswa</div>
                <div class="text-sm">Tidak ada data peserta didik pada filter yang dipilih.</div>
            </div>
        @endforelse
    </div>

    @if(method_exists($siswas, 'links'))
        <div class="paginatePad py-4">
            {{ $siswas->links() }}
        </div>
    @endif
</div>

@endsection


