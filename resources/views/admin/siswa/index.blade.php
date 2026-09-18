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
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.siswa.exportExcel') }}" class="profileBtnPrimary btn-action-success">
                    <ion-icon name="document-outline"></ion-icon> Export Excel
                </a>
                <button type="button" onclick="document.getElementById('importSiswaModal').style.display='flex'" class="profileBtnPrimary btn-action-info">
                    <ion-icon name="cloud-upload-outline"></ion-icon> Import Siswa
                </button>
                <a href="{{ route('admin.siswa.create') }}" class="profileBtnPrimary">
                    <ion-icon name="add-circle-outline"></ion-icon> Tambah Siswa
                </a>
            </div>
        </div>
    </div>

    {{-- ── Modal Impor Siswa ── --}}
    <div id="importSiswaModal" class="app-modal-backdrop" onclick="if(event.target===this) this.style.display='none'">
        <div class="app-modal-card">
            <div class="app-modal-header">
                <div class="app-modal-header-left">
                    <div class="app-modal-badge-icon">
                        <ion-icon name="document-text-outline"></ion-icon>
                    </div>
                    <div>
                        <h3 class="app-modal-title m-0">Impor Data Siswa Massal</h3>
                        <div class="text-xs text-muted">Format Spreadsheet Excel / CSV</div>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('importSiswaModal').style.display='none'" class="app-modal-close" title="Tutup">&times;</button>
            </div>
            <p class="app-modal-desc">
                Unggah berkas spreadsheet Excel/CSV untuk mendaftarkan siswa baru secara massal atau memperbarui data siswa berdasarkan nomor induk/absen (NIS).
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
                <a href="{{ route('admin.siswa.downloadTemplate') }}" class="btn-download-template" title="Download Template Siswa">
                    <ion-icon name="download-outline"></ion-icon> Unduh Template (.xlsx)
                </a>
            </div>
            <form method="POST" action="{{ route('admin.siswa.importExcel') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-field-wrapper mb-3">
                    <label class="form-field-label">Pilih Berkas Spreadsheet Siswa:</label>
                    <div class="fileUploadBox">
                        <input type="file" name="file_excel" id="siswaFileInput" accept=".xlsx,.xls,.csv" required onchange="handleExcelFileSelected(this, 'siswaFileFeedback')">
                        <div class="fileUploadIcon">
                            <ion-icon name="cloud-upload-outline"></ion-icon>
                        </div>
                        <div class="fileUploadText">Pilih atau seret berkas ke sini</div>
                        <div class="fileUploadSubtext">
                            <span class="fileUploadInfoPill">Format: .XLSX, .CSV</span>
                            <span class="fileUploadInfoPill">Maks: 5 MB</span>
                        </div>
                    </div>
                    <div id="siswaFileFeedback" class="fileUploadFeedback"></div>
                </div>
                <div class="app-modal-footer">
                    <button type="button" onclick="document.getElementById('importSiswaModal').style.display='none'" class="btnOutline w-auto">Batal</button>
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


