@extends('layouts.admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">MANAJEMEN PENDIDIK &amp; STAF</div>
                <h1 class="laporanHeaderTitle">Data Karyawan &amp; Tutor</h1>
                <div class="laporanHeaderSub">Kelola tenaga pendidik, staf pengajar, dan hak akses akun sistem</div>
                <p class="laporanHeaderDesc">Daftar staf, pembaruan data login, status keaktifan, dan impor/ekspor data.</p>
            </div>
            <div class="header-actions-group">
                <a href="{{ route('admin.magang.index') }}" class="btnOutline">
                    Peserta Magang
                </a>
                <a href="{{ route('admin.karyawan.exportExcel') }}" class="profileBtnPrimary btn-action-success w-auto text-no-decor">
                    Export Excel
                </a>
                <button type="button" onclick="document.getElementById('importKaryawanModal').style.display='flex'" class="profileBtnPrimary btn-action-info w-auto">
                    Import Tutor
                </button>
                <a href="{{ route('admin.karyawan.create') }}" class="profileBtnPrimary w-auto text-no-decor">
                    Tambah Staf
                </a>
            </div>
        </div>
    </div>

    {{-- ── Modal Impor Tutor / Karyawan ── --}}
    <div id="importKaryawanModal" class="app-modal-backdrop">
        <div class="app-modal-card">
            <div class="app-modal-header">
                <h3 class="app-modal-title">Impor Data Tutor &amp; Karyawan</h3>
                <button type="button" onclick="document.getElementById('importKaryawanModal').style.display='none'" class="app-modal-close">&times;</button>
            </div>
            <p class="app-modal-desc">
                Unggah berkas spreadsheet Excel/CSV untuk mendaftarkan tutor dan staf baru secara massal. Akun login akan otomatis digenerate dengan password default berbasis NIK.
            </p>
            <div class="mb-4">
                <a href="{{ route('admin.karyawan.downloadTemplate') }}" class="btnOutline">
                    Download Template Tutor (.xlsx)
                </a>
            </div>
            <form method="POST" action="{{ route('admin.karyawan.importExcel') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="filterFieldLabel">Pilih Berkas Data Tutor/Staf:</label>
                    <div class="fileUploadBox">
                        <input type="file" name="file_excel" id="karyawanFileInput" accept=".xlsx,.xls,.csv" required onchange="handleFileSelected(this, 'karyawanFileFeedback')">
                        <div class="fileUploadText">Pilih atau seret berkas ke sini</div>
                        <div class="fileUploadSubtext">
                            <span class="fileUploadInfoPill">Format: .XLSX, .CSV</span>
                            <span class="fileUploadInfoPill">Maks: 5 MB</span>
                        </div>
                    </div>
                    <div id="karyawanFileFeedback" class="fileUploadFeedback"></div>
                </div>
                <div class="app-modal-footer">
                    <button type="button" onclick="document.getElementById('importKaryawanModal').style.display='none'" class="btnOutline w-auto">Batal</button>
                    <button type="submit" class="profileBtnPrimary w-auto">Unggah &amp; Impor Tutor</button>
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
        <form method="GET" action="{{ route('admin.karyawan.index') }}">
            <div class="laporanFilterGrid">
                <div class="filterField">
                    <label class="filterFieldLabel">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') ?? request('q') }}" placeholder="Nama, NIK, No HP..." class="profileInput text-md">
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Status Akun</label>
                    <select name="status" class="filterSelect">
                        <option value="aktif" {{ ($status ?? 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif ({{ $aktif ?? 0 }})</option>
                        <option value="nonaktif" {{ ($status ?? '') === 'nonaktif' ? 'selected' : '' }}>Nonaktif ({{ $nonaktif ?? 0 }})</option>
                        <option value="semua" {{ ($status ?? '') === 'semua' ? 'selected' : '' }}>Semua Status ({{ $total ?? 0 }})</option>
                    </select>
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Role / Jabatan</label>
                    <select name="role" class="filterSelect">
                        <option value="">Semua Role</option>
                        <option value="tutor" {{ request('role') === 'tutor' ? 'selected' : '' }}>Tutor / Pengajar</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                        <option value="kepala_sekolah" {{ request('role') === 'kepala_sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                    </select>
                </div>

                <div class="filterActionGroup grid-span-full mt-1 d-flex flex-wrap gap-2">
                    <button type="submit" class="profileBtnPrimary px-4 text-md rounded-lg flex-1">
                        Terapkan Filter
                    </button>
                    <a href="{{ route('admin.karyawan.index') }}" class="profileBtnDanger px-3 text-md rounded-lg w-auto text-no-decor flex-center">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Section Title ── --}}
    <div class="sectionRow">
        <h2 >Daftar Karyawan &amp; Tenaga Pendidik</h2>
        <span class="badgeCount">{{ $karyawan->total() }} Staf Terdaftar</span>
    </div>

    {{-- ── Data Table Container (Desktop) ── --}}
    <div class="table-responsive-desktop">
        <div class="tableContainer m-0 mb-4 rounded-xl border-base">
            <table class="laporanTable">
                <thead >
                    <tr >
                        <th class="table-col-num">NO</th>
                        <th >NAMA LENGKAP</th>
                        <th >NIK</th>
                        <th >ROLE / JABATAN</th>
                        <th >NO HP / WHATSAPP</th>
                        <th >EMAIL</th>
                        <th >STATUS</th>
                        <th class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody >
                    @forelse($karyawan as $index => $k)
                        @php
                            $displayName = (string) ($k->nama_lengkap ?? $k->name ?? '-');
                            $isActive = (bool) ($k->is_active ?? true);
                            $roleLabel = match($k->role) {
                                'admin' => 'Administrator',
                                'tutor' => 'Tutor / Pengajar',
                                'kepala_sekolah' => 'Kepala Sekolah',
                                default => ucfirst(str_replace('_', ' ', (string) $k->role)),
                            };
                            $roleBadgeClass = match($k->role) {
                                'admin' => 'badge-role-admin',
                                'tutor' => 'badge-role-tutor',
                                'kepala_sekolah' => 'badge-role-kepsek',
                                default => 'badge-role-magang',
                            };
                        @endphp
                        <tr >
                            <td class="p-3 font-bold text-muted">
                                {{ $karyawan->firstItem() + $index }}
                            </td>
                            <td class="font-extrabold text-dark">
                                {{ $displayName }}
                            </td>
                            <td class="font-bold text-muted">
                                {{ $k->nik ?? '-' }}
                            </td>
                            <td >
                                <span class="app-badge {{ $roleBadgeClass }}">
                                    {{ $roleLabel }}
                                </span>
                            </td>
                            <td class="font-bold">
                                @if($k->no_hp)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $k->no_hp) }}" target="_blank" class="text-primary text-no-decor">
                                        {{ $k->no_hp }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-muted text-sm">
                                {{ $k->email ?? '-' }}
                            </td>
                            <td >
                                @if($isActive)
                                    <span class="app-badge badge-status-aktif">Aktif</span>
                                @else
                                    <span class="app-badge badge-status-nonaktif">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 flex-center flex-wrap">
                                    <a class="smallBtn edit btn-table-action" href="{{ route('admin.karyawan.edit', $k->id) }}">
                                        Edit
                                    </a>

                                    <form method="POST" action="{{ route('admin.karyawan.toggleStatus', $k->id) }}" class="d-inline m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-status-toggle" title="Klik untuk ubah status aktif/nonaktif">
                                            {{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.karyawan.destroy', $k->id) }}" data-confirm="Apakah Anda yakin ingin menghapus staf {{ $displayName }}? Seluruh riwayat akun akan dihapus." data-confirm-title="Hapus Karyawan" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="smallBtn delete cursor-pointer btn-table-action" title="Hapus Staf">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr >
                            <td colspan="8" class="table-empty-cell">
                                <div class="font-bold text-md mb-1">Belum Ada Data Karyawan</div>
                                <div class="text-sm">Tidak ada data staf atau tutor pada filter yang dipilih.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Mobile Cards List (Layar HP / Tablet) ── --}}
    <div class="mobile-card-list">
        @forelse($karyawan as $index => $k)
            @php
                $displayName = (string) ($k->nama_lengkap ?? $k->name ?? '-');
                $isActive = (bool) ($k->is_active ?? true);
                $roleLabel = match($k->role) {
                    'admin' => 'Administrator',
                    'tutor' => 'Tutor / Pengajar',
                    'kepala_sekolah' => 'Kepala Sekolah',
                    default => ucfirst(str_replace('_', ' ', (string) $k->role)),
                };
                $roleBadgeClass = match($k->role) {
                    'admin' => 'badge-role-admin',
                    'tutor' => 'badge-role-tutor',
                    'kepala_sekolah' => 'badge-role-kepsek',
                    default => 'badge-role-magang',
                };
            @endphp
            <div class="data-mobile-card">
                <div class="dmc-header">
                    <div >
                        <h3 class="dmc-title">{{ $displayName }}</h3>
                        <div class="dmc-subtitle">NIK: {{ $k->nik ?? '-' }}</div>
                    </div>
                    <div class="flex-col gap-1 flex-items-end">
                        @if($isActive)
                            <span class="app-badge badge-status-aktif">Aktif</span>
                        @else
                            <span class="app-badge badge-status-nonaktif">Nonaktif</span>
                        @endif
                        <span class="app-badge {{ $roleBadgeClass }}">
                            {{ $roleLabel }}
                        </span>
                    </div>
                </div>

                <div class="dmc-grid">
                    <div class="dmc-field">
                        <div class="dmc-label">No WhatsApp</div>
                        <div class="dmc-value">
                            @if($k->no_hp)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $k->no_hp) }}" target="_blank" class="text-primary text-no-decor">
                                    {{ $k->no_hp }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Email</div>
                        <div class="dmc-value text-sm text-muted">
                            {{ $k->email ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="dmc-footer">
                    <div class="text-xs font-bold text-muted">
                        No. {{ $karyawan->firstItem() + $index }}
                    </div>
                    <div class="dmc-actions">
                        <a class="smallBtn edit btn-table-action" href="{{ route('admin.karyawan.edit', $k->id) }}">
                            Edit
                        </a>

                        <form method="POST" action="{{ route('admin.karyawan.toggleStatus', $k->id) }}" class="d-inline m-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-status-toggle">
                                {{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.karyawan.destroy', $k->id) }}" data-confirm="Apakah Anda yakin ingin menghapus staf {{ $displayName }}? Seluruh riwayat akun akan dihapus." data-confirm-title="Hapus Karyawan" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="smallBtn delete cursor-pointer btn-table-action">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="data-mobile-card table-empty-cell">
                <div class="font-bold text-md mb-1">Belum Ada Data Karyawan</div>
                <div class="text-sm">Tidak ada data staf atau tutor pada filter yang dipilih.</div>
            </div>
        @endforelse
    </div>

    @if(method_exists($karyawan, 'links'))
        <div class="paginatePad py-4">
            {{ $karyawan->links() }}
        </div>
    @endif
</div>

@endsection
