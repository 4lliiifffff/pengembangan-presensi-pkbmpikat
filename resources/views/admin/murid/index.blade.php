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
                <a href="{{ route('admin.murid.exportExcel', request()->query()) }}" class="profileBtnPrimary btn-action-success">
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
                            <span class="fileUploadInfoPill"><ion-icon name="document-text-outline"></ion-icon> .XLSX, .XLS, .CSV</span>
                            <span class="fileUploadInfoPill"><ion-icon name="hardware-chip-outline"></ion-icon> Maks. 5 MB</span>
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

    {{-- ── Quick Filter Chips Bar ── --}}
    <div class="filterChipsBar">
        <a href="{{ route('admin.murid.index', ['status' => 'semua', 'pembimbing' => 'semua']) }}"
           class="filterChip {{ ($status === 'semua' && $pembimbing === 'semua' && !request()->filled('tutor_id') && !request()->filled('kelas_id') && !request()->filled('q')) ? 'active' : '' }}">
            <ion-icon name="list-outline"></ion-icon>
            <span>Semua Murid</span>
            <span class="filterChipBadge">{{ $stats['total'] ?? 0 }}</span>
        </a>

        <a href="{{ route('admin.murid.index', ['status' => 'aktif', 'pembimbing' => 'semua']) }}"
           class="filterChip {{ ($status === 'aktif' && $pembimbing === 'semua' && !request()->filled('tutor_id') && !request()->filled('kelas_id') && !request()->filled('q')) ? 'active' : '' }}">
            <ion-icon name="checkmark-circle-outline"></ion-icon>
            <span>Murid Aktif</span>
            <span class="filterChipBadge">{{ $stats['aktif'] ?? 0 }}</span>
        </a>

        <a href="{{ route('admin.murid.index', array_filter(array_merge(request()->query(), ['pembimbing' => 'ada', 'tutor_id' => null, 'page' => null]))) }}"
           class="filterChip {{ $pembimbing === 'ada' && !request()->filled('tutor_id') ? 'active' : '' }}">
            <ion-icon name="shield-checkmark-outline"></ion-icon>
            <span>Sudah Ada Pembimbing</span>
            <span class="filterChipBadge">{{ $stats['ada_pembimbing'] ?? 0 }}</span>
        </a>

        <a href="{{ route('admin.murid.index', array_filter(array_merge(request()->query(), ['pembimbing' => 'belum', 'tutor_id' => null, 'page' => null]))) }}"
           class="filterChip {{ $pembimbing === 'belum' ? 'active' : '' }}">
            <ion-icon name="alert-circle-outline"></ion-icon>
            <span>Belum Ada Pembimbing</span>
            <span class="filterChipBadge {{ ($stats['tanpa_pembimbing'] ?? 0) > 0 ? 'badge-danger-chip' : '' }}">
                {{ $stats['tanpa_pembimbing'] ?? 0 }}
            </span>
        </a>
    </div>

    {{-- ── Filter Card ── --}}
    <div class="laporanFilterCard">
        <form method="GET" action="{{ route('admin.murid.index') }}" id="filterMuridForm">
            <div class="laporanFilterGrid muridFilterGrid">
                <div class="filterField filterFieldSearch">
                    <label class="filterFieldLabel">Pencarian</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama murid, NIS, wali, atau tutor..." class="profileInput text-md">
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Status Murid</label>
                    <select name="status" class="filterSelect" onchange="document.getElementById('filterMuridForm').submit()">
                        <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif ({{ $stats['aktif'] ?? 0 }})</option>
                        <option value="alumni" {{ $status === 'alumni' ? 'selected' : '' }}>Lulus / Alumni ({{ $stats['alumni'] ?? 0 }})</option>
                        <option value="cuti" {{ $status === 'cuti' ? 'selected' : '' }}>Cuti Belajar ({{ $stats['cuti'] ?? 0 }})</option>
                        <option value="nonaktif" {{ $status === 'nonaktif' ? 'selected' : '' }}>Nonaktif / Keluar ({{ $stats['nonaktif'] ?? 0 }})</option>
                        <option value="semua" {{ $status === 'semua' ? 'selected' : '' }}>Semua Status ({{ $stats['total'] ?? 0 }})</option>
                    </select>
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Status Pembimbing</label>
                    <select name="pembimbing" id="selectPembimbing" class="filterSelect" onchange="handlePembimbingChange(this)">
                        <option value="semua" {{ $pembimbing === 'semua' ? 'selected' : '' }}>Semua Pembimbing</option>
                        <option value="ada" {{ $pembimbing === 'ada' ? 'selected' : '' }}>Sudah Ada Pembimbing ({{ $stats['ada_pembimbing'] ?? 0 }})</option>
                        <option value="belum" {{ $pembimbing === 'belum' ? 'selected' : '' }}>Belum Ada Pembimbing ({{ $stats['tanpa_pembimbing'] ?? 0 }})</option>
                    </select>
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Tutor Pembimbing</label>
                    <select name="tutor_id" id="selectTutor" class="filterSelect" {{ $pembimbing === 'belum' ? 'disabled' : '' }} onchange="handleTutorSelectChange(this)">
                        <option value="">Semua Tutor Pembimbing</option>
                        @foreach($tutors as $tutor)
                            <option value="{{ $tutor->id }}" {{ (string) request('tutor_id') === (string) $tutor->id ? 'selected' : '' }}>
                                {{ $tutor->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Kelas / Rombel</label>
                    <select name="kelas_id" class="filterSelect" onchange="document.getElementById('filterMuridForm').submit()">
                        <option value="">Semua Kelas / Rombel</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ (string) request('kelas_id') === (string) $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }} ({{ $kelas->jenjang_paket_label }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-actions-full">
                    <button type="submit" class="btn-filter-primary">
                        <ion-icon name="filter-outline"></ion-icon> Terapkan Filter
                    </button>
                    <a href="{{ route('admin.murid.index') }}" class="btn-filter-reset">
                        Reset
                    </a>

                    @if(request()->filled('q') || request()->filled('tutor_id') || request()->filled('kelas_id') || request('pembimbing', 'semua') !== 'semua' || (request('status') && request('status') !== 'aktif'))
                        <div class="activeFilterWrap">
                            <span class="activeFilterLead"><ion-icon name="funnel-outline"></ion-icon> Filter Aktif:</span>
                            @if(request('pembimbing') === 'ada')
                                <span class="activeFilterTag">Sudah Ada Pembimbing</span>
                            @elseif(request('pembimbing') === 'belum')
                                <span class="activeFilterTag tag-warning">Belum Ada Pembimbing</span>
                            @endif
                            @if(request()->filled('tutor_id'))
                                @php $selectedTutor = $tutors->firstWhere('id', request('tutor_id')); @endphp
                                @if($selectedTutor)
                                    <span class="activeFilterTag">Tutor: {{ $selectedTutor->nama_lengkap }}</span>
                                @endif
                            @endif
                            @if(request()->filled('kelas_id'))
                                @php $selectedKelas = $kelasList->firstWhere('id', request('kelas_id')); @endphp
                                @if($selectedKelas)
                                    <span class="activeFilterTag">Kelas: {{ $selectedKelas->nama_kelas }}</span>
                                @endif
                            @endif
                            @if(request('status') && request('status') !== 'aktif' && request('status') !== 'semua')
                                <span class="activeFilterTag">Status: {{ ucfirst(request('status')) }}</span>
                            @endif
                            @if(request()->filled('q'))
                                <span class="activeFilterTag">Kata Kunci: "{{ request('q') }}"</span>
                            @endif
                        </div>
                    @endif
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
                            <td>
                                @if($murid->tutor)
                                    <div class="tutor-assigned-pill" title="Dibimbing oleh {{ $murid->tutor->nama_lengkap }}">
                                        <span class="tutor-avatar-mini">{{ strtoupper(mb_substr($murid->tutor->nama_lengkap, 0, 1)) }}</span>
                                        <span class="tutor-name-text">{{ $murid->tutor->nama_lengkap }}</span>
                                    </div>
                                @else
                                    <div class="tutor-unassigned-box">
                                        <span class="app-badge badge-tutor-unassigned" title="Belum memiliki tutor pembimbing">
                                            <ion-icon name="alert-circle-outline"></ion-icon> Belum Ditugaskan
                                        </span>
                                        <a href="{{ route('admin.murid.edit', $murid) }}#tutor_id" class="btn-assign-quick" title="Tugaskan Tutor Pembimbing">
                                            <ion-icon name="person-add-outline"></ion-icon> Tugaskan
                                        </a>
                                    </div>
                                @endif
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
                        <div class="dmc-value">
                            @if($murid->tutor)
                                <div class="tutor-assigned-pill mt-1" title="Dibimbing oleh {{ $murid->tutor->nama_lengkap }}">
                                    <span class="tutor-avatar-mini">{{ strtoupper(mb_substr($murid->tutor->nama_lengkap, 0, 1)) }}</span>
                                    <span class="tutor-name-text">{{ $murid->tutor->nama_lengkap }}</span>
                                </div>
                            @else
                                <div class="tutor-unassigned-box mt-1">
                                    <span class="app-badge badge-tutor-unassigned">
                                        <ion-icon name="alert-circle-outline"></ion-icon> Belum Ditugaskan
                                    </span>
                                    <a href="{{ route('admin.murid.edit', $murid) }}#tutor_id" class="btn-assign-quick mt-1">
                                        <ion-icon name="person-add-outline"></ion-icon> Tugaskan Tutor
                                    </a>
                                </div>
                            @endif
                        </div>
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

<script>
function handlePembimbingChange(select) {
    const tutorSelect = document.getElementById('selectTutor');
    if (select.value === 'belum') {
        tutorSelect.value = '';
        tutorSelect.disabled = true;
    } else {
        tutorSelect.disabled = false;
    }
    document.getElementById('filterMuridForm').submit();
}

function handleTutorSelectChange(select) {
    const pembimbingSelect = document.getElementById('selectPembimbing');
    if (select.value && select.value !== '') {
        pembimbingSelect.value = 'ada';
    }
    document.getElementById('filterMuridForm').submit();
}
</script>

@endsection
