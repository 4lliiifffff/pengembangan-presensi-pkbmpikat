@extends('layouts.admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">MANAJEMEN MURID</div>
                <h1 class="laporanHeaderTitle">Data Murid</h1>
                <div class="laporanHeaderSub">Kelola data murid, wali murid, status siklus, dan rombongan belajar</div>
                <p class="laporanHeaderDesc">Daftar murid, pemetaan paket kesetaraan, data ABK, dan histori rombel.</p>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.murid.exportExcel') }}" class="profileBtnPrimary btn-action-success">
                    <ion-icon name="document-outline"></ion-icon> Export Excel
                </a>
                <button type="button" onclick="document.getElementById('importMuridModal').style.display='flex'" class="profileBtnPrimary btn-action-info">
                    <ion-icon name="cloud-upload-outline"></ion-icon> Import Murid
                </button>
                <a href="{{ route('admin.murid.create') }}" class="profileBtnPrimary">
                    <ion-icon name="add-circle-outline"></ion-icon> Tambah Murid
                </a>
            </div>
        </div>
    </div>

    {{-- ── Modal Impor Murid ── --}}
    <div id="importMuridModal" class="app-modal-backdrop" onclick="if(event.target===this) this.style.display='none'">
        <div class="app-modal-card">
            <div class="app-modal-header">
                <div class="app-modal-header-left">
                    <div class="app-modal-badge-icon">
                        <ion-icon name="document-text-outline"></ion-icon>
                    </div>
                    <div>
                        <h3 class="app-modal-title m-0">Impor Data Murid Massal</h3>
                        <div class="text-xs text-muted">Format Spreadsheet Excel / CSV</div>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('importMuridModal').style.display='none'" class="app-modal-close" title="Tutup">&times;</button>
            </div>
            <p class="app-modal-desc">
                Unggah berkas spreadsheet Excel/CSV untuk mendaftarkan murid baru secara massal atau memperbarui data murid berdasarkan nomor induk/absen (NIS).
            </p>
            <div class="import-template-banner">
                <div class="import-template-info">
                    <div class="import-template-icon">
                        <ion-icon name="cloud-download-outline"></ion-icon>
                    </div>
                    <div class="import-template-texts">
                        <div class="import-template-title">Belum memiliki template?</div>
                        <div class="import-template-desc">Gunakan format kolom resmi agar data terbaca otomatis.</div>
                    </div>
                </div>
                <a href="{{ route('admin.murid.downloadTemplate') }}" class="btn-download-template" title="Download Template Murid">
                    <ion-icon name="download-outline"></ion-icon> Unduh Template (.xlsx)
                </a>
            </div>
            <form method="POST" action="{{ route('admin.murid.importExcel') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-field-wrapper mb-3">
                    <label class="form-field-label">Pilih Berkas Spreadsheet Murid:</label>
                    <div class="fileUploadBox">
                        <input type="file" name="file_excel" id="muridFileInput" accept=".xlsx,.xls,.csv" required onchange="handleExcelFileSelected(this, 'muridFileFeedback')">
                        <div class="fileUploadIcon">
                            <ion-icon name="cloud-upload-outline"></ion-icon>
                        </div>
                        <div class="fileUploadText">Pilih atau seret berkas ke sini</div>
                        <div class="fileUploadSubtext">
                            <span class="fileUploadInfoPill">Format: .XLSX, .CSV</span>
                            <span class="fileUploadInfoPill">Maks: 5 MB</span>
                        </div>
                    </div>
                    <div id="muridFileFeedback" class="fileUploadFeedback"></div>
                </div>
                <div class="app-modal-footer">
                    <button type="button" onclick="document.getElementById('importMuridModal').style.display='none'" class="btnOutline w-auto">Batal</button>
                    <button type="submit" class="profileBtnPrimary w-auto">
                        <ion-icon name="cloud-upload-outline"></ion-icon> Unggah &amp; Impor
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function handleExcelFileSelected(input, feedbackId) {
            const feedback = document.getElementById(feedbackId);
            if (!feedback) return;
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const sizeKb = Math.round(file.size / 1024);
                const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
                const sizeText = sizeKb > 1024 ? `${sizeMb} MB` : `${sizeKb} KB`;

                if (file.size > 5 * 1024 * 1024) {
                    feedback.style.display = 'block';
                    feedback.innerHTML = `
                        <div class="file-selected-card" style="border-color: #fca5a5; background: rgba(239, 68, 68, 0.1);">
                            <div class="fsc-info">
                                <ion-icon name="alert-circle-outline" class="fsc-icon" style="color: #dc2626;"></ion-icon>
                                <div class="fsc-details">
                                    <div class="fsc-name">${file.name}</div>
                                    <div class="text-xs font-bold" style="color: #dc2626;">Ukuran (${sizeText}) melebihi batas maksimal 5 MB!</div>
                                </div>
                            </div>
                            <button type="button" class="fsc-remove-btn" onclick="clearSelectedExcel('${input.id}', '${feedbackId}')" title="Hapus">
                                <ion-icon name="close-circle-outline"></ion-icon>
                            </button>
                        </div>`;
                    input.value = '';
                    return;
                }

                feedback.style.display = 'block';
                feedback.innerHTML = `
                    <div class="file-selected-card">
                        <div class="fsc-info">
                            <ion-icon name="document-attach-outline" class="fsc-icon"></ion-icon>
                            <div class="fsc-details">
                                <div class="fsc-name">${file.name}</div>
                                <div class="fsc-meta">${sizeText} &bull; Berkas siap diunggah</div>
                            </div>
                        </div>
                        <button type="button" class="fsc-remove-btn" onclick="clearSelectedExcel('${input.id}', '${feedbackId}')" title="Ganti berkas">
                            <ion-icon name="close-circle-outline"></ion-icon>
                        </button>
                    </div>`;
            } else {
                feedback.style.display = 'none';
                feedback.innerHTML = '';
            }
        }

        function clearSelectedExcel(inputId, feedbackId) {
            const input = document.getElementById(inputId);
            const feedback = document.getElementById(feedbackId);
            if (input) input.value = '';
            if (feedback) {
                feedback.style.display = 'none';
                feedback.innerHTML = '';
            }
        }
    </script>

    {{-- ── Filter Card ── --}}
    <div class="laporanFilterCard">
        <form method="GET" action="{{ route('admin.murid.index') }}">
            <div class="laporanFilterGrid">
                <div class="filterField">
                    <label class="filterFieldLabel">Pencarian</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama, Absen/NIS, Wali..." class="profileInput text-md">
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Status Murid</label>
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
                    <a href="{{ route('admin.murid.index') }}" class="btn-filter-reset">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Section Title ── --}}
    <div class="sectionRow">
        <h2>Daftar Murid</h2>
        <span class="badgeCount">{{ $murids->total() }} Murid Terdata</span>
    </div>

    {{-- ── Data Table Container (Desktop) ── --}}
    <div class="table-responsive-desktop">
        <div class="tableContainer m-0 mb-4 rounded-xl border-base">
            <table class="laporanTable">
                <thead>
                    <tr>
                        <th class="table-col-num">NO</th>
                        <th>ABSEN / NIS</th>
                        <th>NAMA LENGKAP</th>
                        <th>KELAS / ROMBEL</th>
                        <th>JENJANG</th>
                        <th>STATUS SIKLUS</th>
                        <th>NAMA WALI</th>
                        <th>TUTOR</th>
                        <th class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($murids as $index => $murid)
                        <tr>
                            <td class="p-3 font-bold text-muted">
                                {{ $murids->firstItem() + $index }}
                            </td>
                            <td class="font-bold text-dark">
                                {{ $murid->no_absen ?? '-' }}
                            </td>
                            <td class="font-extrabold text-dark">
                                {{ $murid->nama_murid }}
                            </td>
                            <td class="font-semibold">
                                {{ $murid->relKelas->nama_kelas ?? '-' }}
                            </td>
                            <td class="font-bold text-primary">
                                {{ $murid->jenjang_paket_label }}
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap items-center">
                                    <span class="app-badge badge-status-{{ $murid->status_murid }}">
                                        {{ $murid->status_label }}
                                    </span>
                                    @if($murid->is_abk)
                                        <span class="app-badge badge-abk">
                                            ABK
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-muted text-sm">
                                {{ $murid->nama_wali ?? '-' }}
                            </td>
                            <td class="text-sm font-semibold">
                                {{ $murid->tutor->nama_lengkap ?? '-' }}
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 flex-center flex-wrap">
                                    <a class="smallBtn btn-status-toggle btn-table-action" href="{{ route('admin.murid.show', $murid) }}" title="Lihat Detail Murid">
                                        Detail
                                    </a>

                                    <a href="{{ route('admin.murid.edit', $murid) }}" class="smallBtn edit btn-table-action" title="Edit Data Murid">
                                        Edit
                                    </a>

                                    <form method="POST" action="{{ route('admin.murid.destroy', $murid) }}" data-confirm="Arsipkan data murid ini (Soft Delete)? Seluruh data riwayat presensi dan honor mengajar tutor akan tetap aman tersimpan." data-confirm-title="Arsipkan Murid" data-confirm-type="danger" data-confirm-btn="Ya, Arsipkan" class="d-inline m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Arsipkan Murid" class="smallBtn delete cursor-pointer btn-table-action">
                                            Arsipkan
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="table-empty-cell">
                                <div class="tableEmptyState">
                                    <div class="tableEmptyIconWrap">
                                        <ion-icon name="school-outline" class="tableEmptyIcon"></ion-icon>
                                    </div>
                                    <div class="tableEmptyTitle">Belum Ada Data Murid</div>
                                    <div class="tableEmptyDesc">Tidak ada data murid pada filter yang dipilih.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Mobile Cards List (Layar HP / Tablet) ── --}}
    <div class="mobile-card-list">
        @forelse($murids as $index => $murid)
            <div class="data-mobile-card">
                <div class="dmc-header">
                    <div>
                        <h3 class="dmc-title">{{ $murid->nama_murid }}</h3>
                        <div class="dmc-subtitle">Absen / NIS: {{ $murid->no_absen ?? '-' }}</div>
                    </div>
                    <div class="flex-col gap-1 flex-items-end">
                        <span class="app-badge badge-status-{{ $murid->status_murid }}">
                            {{ $murid->status_label }}
                        </span>
                        @if($murid->is_abk)
                            <span class="app-badge badge-abk">
                                ABK
                            </span>
                        @endif
                    </div>
                </div>

                <div class="dmc-grid">
                    <div class="dmc-field">
                        <div class="dmc-label">Kelas / Rombel</div>
                        <div class="dmc-value">{{ $murid->relKelas->nama_kelas ?? '-' }}</div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Jenjang Paket</div>
                        <div class="dmc-value text-primary">{{ $murid->jenjang_paket_label }}</div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Tutor Pembimbing</div>
                        <div class="dmc-value text-sm">{{ $murid->tutor->nama_lengkap ?? '-' }}</div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Wali Murid</div>
                        <div class="dmc-value text-sm">{{ $murid->nama_wali ?? '-' }}</div>
                    </div>
                </div>

                <div class="dmc-footer">
                    <div class="text-xs font-bold text-muted">
                        No. {{ $murids->firstItem() + $index }}
                    </div>
                    <div class="dmc-actions">
                        <a class="smallBtn btn-status-toggle btn-table-action" href="{{ route('admin.murid.show', $murid) }}" title="Lihat Detail Murid">
                            Detail
                        </a>

                        <a href="{{ route('admin.murid.edit', $murid) }}" class="smallBtn edit btn-table-action" title="Edit Data Murid">
                            Edit
                        </a>

                        <form method="POST" action="{{ route('admin.murid.destroy', $murid) }}" data-confirm="Arsipkan data murid ini (Soft Delete)? Seluruh data riwayat presensi dan honor mengajar tutor akan tetap aman tersimpan." data-confirm-title="Arsipkan Murid" data-confirm-type="danger" data-confirm-btn="Ya, Arsipkan" class="d-inline m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="smallBtn delete cursor-pointer btn-table-action" title="Arsipkan Murid">
                                Arsipkan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        @empty
            <div class="tableEmptyCard">
                <div class="tableEmptyIconWrap">
                    <ion-icon name="school-outline" class="tableEmptyIcon"></ion-icon>
                </div>
                <div class="tableEmptyTitle">Belum Ada Data Murid</div>
                <div class="tableEmptyDesc">Tidak ada data murid pada filter yang dipilih.</div>
            </div>
        @endforelse
    </div>

    @if(method_exists($murids, 'links'))
        <div class="paginatePad py-4">
            {{ $murids->links() }}
        </div>
    @endif
</div>

@endsection
