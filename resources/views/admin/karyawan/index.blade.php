@extends('layouts.admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">PUSAT KONTROL AKSES &amp; IDENTITAS</div>
                <h1 class="laporanHeaderTitle">Pusat Pengelolaan Akun Pengguna</h1>
                <div class="laporanHeaderSub">Manajemen akun terintegrasi untuk Tutor, Siswa, Magang, Admin, dan Kepala Sekolah</div>
                <p class="laporanHeaderDesc">Kelola hak akses sistem, reset kata sandi, status keaktifan akun pengguna, serta integrasi data antar modul dalam satu pintu.</p>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.karyawan.exportExcel') }}" class="profileBtnPrimary btn-action-success">
                    <ion-icon name="document-outline"></ion-icon> Export Excel
                </a>
                <button type="button" onclick="document.getElementById('importKaryawanModal').style.display='flex'" class="profileBtnPrimary btn-action-info">
                    <ion-icon name="cloud-upload-outline"></ion-icon> Import Akun
                </button>
                <a href="{{ route('admin.karyawan.create') }}" class="profileBtnPrimary">
                    <ion-icon name="add-circle-outline"></ion-icon> Tambah Akun
                </a>
            </div>
        </div>
    </div>

    {{-- ── Modal Impor Tutor / Karyawan ── --}}
    <div id="importKaryawanModal" class="app-modal-backdrop" onclick="if(event.target===this) this.style.display='none'">
        <div class="app-modal-card">
            <div class="app-modal-header">
                <div class="app-modal-header-left">
                    <div class="app-modal-badge-icon">
                        <ion-icon name="people-outline"></ion-icon>
                    </div>
                    <div>
                        <h3 class="app-modal-title m-0">Impor Data Akun &amp; Tutor</h3>
                        <div class="text-xs text-muted">Format Spreadsheet Excel / CSV</div>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('importKaryawanModal').style.display='none'" class="app-modal-close" title="Tutup">&times;</button>
            </div>
            <p class="app-modal-desc">
                Unggah berkas spreadsheet Excel/CSV untuk mendaftarkan akun secara massal. Akun login akan otomatis dibuat dengan password default berbasis NIK / <code>password123</code>.
            </p>
            <div class="import-template-banner">
                <div class="import-template-info">
                    <div class="import-template-icon">
                        <ion-icon name="cloud-download-outline"></ion-icon>
                    </div>
                    <div class="import-template-texts">
                        <div class="import-template-title">Belum memiliki template?</div>
                        <div class="import-template-desc">Gunakan format kolom resmi agar data akun terbaca otomatis.</div>
                    </div>
                </div>
                <a href="{{ route('admin.karyawan.downloadTemplate') }}" class="btn-download-template" title="Download Template Akun & Tutor">
                    <ion-icon name="download-outline"></ion-icon> Unduh Template (.xlsx)
                </a>
            </div>
            <form method="POST" action="{{ route('admin.karyawan.importExcel') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-field-wrapper mb-3">
                    <label class="form-field-label">Pilih Berkas Spreadsheet:</label>
                    <div class="fileUploadBox">
                        <input type="file" name="file_excel" id="karyawanFileInput" accept=".xlsx,.xls,.csv" required onchange="handleExcelFileSelected(this, 'karyawanFileFeedback')">
                        <div class="fileUploadIcon">
                            <ion-icon name="cloud-upload-outline"></ion-icon>
                        </div>
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

    {{-- ── Quick Stats Grid ── --}}
    <div class="account-stats-grid">
        <div class="account-stat-card">
            <div class="account-stat-icon blue">
                <ion-icon name="people-outline"></ion-icon>
            </div>
            <div class="account-stat-content">
                <div class="account-stat-label">Total Pengguna</div>
                <div class="account-stat-value">{{ $total }}</div>
                <div class="account-stat-sub">{{ $aktif }} aktif · {{ $nonaktif }} nonaktif</div>
            </div>
        </div>

        <div class="account-stat-card">
            <div class="account-stat-icon emerald">
                <ion-icon name="id-card-outline"></ion-icon>
            </div>
            <div class="account-stat-content">
                <div class="account-stat-label">Tutor &amp; Pengajar</div>
                <div class="account-stat-value">{{ $tutorCount }}</div>
                <div class="account-stat-sub">Tenaga Pendidik Terdaftar</div>
            </div>
        </div>

        <div class="account-stat-card">
            <div class="account-stat-icon indigo">
                <ion-icon name="school-outline"></ion-icon>
            </div>
            <div class="account-stat-content">
                <div class="account-stat-label">Peserta Didik (Siswa)</div>
                <div class="account-stat-value">{{ $siswaCount }}</div>
                <div class="account-stat-sub">Presensi Mandiri Siswa</div>
            </div>
        </div>

        <div class="account-stat-card">
            <div class="account-stat-icon teal">
                <ion-icon name="briefcase-outline"></ion-icon>
            </div>
            <div class="account-stat-content">
                <div class="account-stat-label">Magang, Staf &amp; Admin</div>
                <div class="account-stat-value">{{ $magangCount + $adminCount + $kepsekCount }}</div>
                <div class="account-stat-sub">{{ $magangCount }} Magang · {{ $adminCount + $kepsekCount }} Manajemen</div>
            </div>
        </div>
    </div>

    {{-- ── Filter Card (Responsive Role & Status Filter) ── --}}
    @php
        $currentRole = request('role', 'semua');
        $currentStatus = request('status', 'semua');
        $currentSearch = request('search') ?? request('q');
    @endphp

    <div class="laporanFilterCard">
        <form method="GET" action="{{ route('admin.karyawan.index') }}">
            <div class="laporanFilterGrid">
                <div class="filterField">
                    <label class="filterFieldLabel">Pencarian Pengguna</label>
                    <input type="text" name="search" value="{{ $currentSearch }}" placeholder="Cari Nama, NIK, Username, Email, No HP..." class="profileInput text-md">
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Peran / Role Pengguna</label>
                    <select name="role" class="filterSelect" onchange="this.form.submit()">
                        <option value="semua" {{ $currentRole === 'semua' || empty($currentRole) ? 'selected' : '' }}>
                            Semua Peran / Role ({{ $total }})
                        </option>
                        <option value="tutor" {{ $currentRole === 'tutor' ? 'selected' : '' }}>
                            Pendidik / Tutor ({{ $tutorCount }})
                        </option>
                        <option value="siswa" {{ $currentRole === 'siswa' ? 'selected' : '' }}>
                            Peserta Didik / Siswa ({{ $siswaCount }})
                        </option>
                        <option value="magang" {{ $currentRole === 'magang' ? 'selected' : '' }}>
                            Mahasiswa Magang ({{ $magangCount }})
                        </option>
                        <option value="admin" {{ $currentRole === 'admin' ? 'selected' : '' }}>
                            Administrator ({{ $adminCount }})
                        </option>
                        <option value="kepala_sekolah" {{ $currentRole === 'kepala_sekolah' ? 'selected' : '' }}>
                            Kepala Sekolah ({{ $kepsekCount }})
                        </option>
                    </select>
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Status Akun Login</label>
                    <select name="status" class="filterSelect" onchange="this.form.submit()">
                        <option value="semua" {{ $currentStatus === 'semua' ? 'selected' : '' }}>Semua Status ({{ $total }})</option>
                        <option value="aktif" {{ $currentStatus === 'aktif' ? 'selected' : '' }}>Hanya Aktif ({{ $aktif }})</option>
                        <option value="nonaktif" {{ $currentStatus === 'nonaktif' ? 'selected' : '' }}>Hanya Nonaktif ({{ $nonaktif }})</option>
                    </select>
                </div>

                <div class="filter-actions-full">
                    <button type="submit" class="btn-filter-primary">
                        <ion-icon name="filter-outline"></ion-icon> Terapkan Filter
                    </button>
                    <a href="{{ route('admin.karyawan.index') }}" class="btn-filter-reset">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Section Title & Filter Chips ── --}}
    <div class="sectionRow">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h2 class="m-0 font-bold">Daftar Akun Pengguna</h2>
            @if($currentRole !== 'semua' && !empty($currentRole))
                <span class="app-badge badge-role-{{ $currentRole }}">
                    Peran: {{ ucfirst(str_replace('_', ' ', $currentRole)) }}
                </span>
            @endif
            @if($currentStatus !== 'semua')
                <span class="app-badge {{ $currentStatus === 'aktif' ? 'badge-status-aktif' : 'badge-status-nonaktif' }}">
                    Status: {{ ucfirst($currentStatus) }}
                </span>
            @endif
            @if(!empty($currentSearch))
                <span class="app-badge badge-role-admin">
                    Kata Kunci: "{{ $currentSearch }}"
                </span>
            @endif
        </div>
        <span class="badgeCount">{{ $karyawan->total() }} Akun Ditemukan</span>
    </div>

    {{-- ── Data Table Container (Desktop) ── --}}
    <div class="table-responsive-desktop">
        <div class="tableContainer m-0 mb-4 rounded-xl border-base">
            <table class="laporanTable">
                <thead>
                    <tr>
                        <th class="table-col-num">NO</th>
                        <th>PENGGUNA &amp; EMAIL</th>
                        <th>NIK / USERNAME</th>
                        <th>PERAN / ROLE</th>
                        <th>INFORMASI MODUL</th>
                        <th>NO WHATSAPP</th>
                        <th>STATUS</th>
                        <th class="text-center">AKSI PENGELOLAAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($karyawan as $index => $k)
                        @php
                            $displayName = (string) ($k->nama_lengkap ?? $k->name ?? '-');
                            $isActive = (bool) ($k->is_active ?? true);
                            $roleLabel = match($k->role) {
                                'admin' => 'Administrator',
                                'tutor' => 'Tutor / Pengajar',
                                'kepala_sekolah' => 'Kepala Sekolah',
                                'siswa' => 'Peserta Didik (Siswa)',
                                'magang' => 'Mahasiswa Magang',
                                default => ucfirst(str_replace('_', ' ', (string) $k->role)),
                            };
                            $roleBadgeClass = match($k->role) {
                                'admin' => 'badge-role-admin',
                                'tutor' => 'badge-role-tutor',
                                'kepala_sekolah' => 'badge-role-kepsek',
                                'siswa' => 'badge-role-siswa',
                                'magang' => 'badge-role-magang',
                                default => 'badge-role-admin',
                            };
                            $initial = strtoupper(substr($displayName, 0, 1));
                        @endphp
                        <tr>
                            <td class="p-3 font-bold text-muted">
                                {{ $karyawan->firstItem() + $index }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar-initial {{ $k->role }}">
                                        {{ $initial }}
                                    </div>
                                    <div>
                                        <div class="font-extrabold text-dark">{{ $displayName }}</div>
                                        <div class="text-xs text-muted font-mono">{{ $k->email ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="font-bold font-mono text-dark">
                                {{ $k->nik ?? '-' }}
                                @if($k->role === 'siswa' && $k->siswa)
                                    <span class="text-xs text-muted d-block font-sans">No Absen: {{ $k->siswa->no_absen }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="app-badge {{ $roleBadgeClass }}">
                                    {{ $roleLabel }}
                                </span>
                            </td>
                            <td>
                                @if($k->role === 'siswa' && $k->siswa)
                                    <div class="text-sm font-semibold text-dark">
                                        {{ $k->siswa->relKelas?->nama_kelas ?? 'Tanpa Kelas' }}
                                    </div>
                                    <a href="{{ route('admin.siswa.index', ['search' => $k->siswa->nama_siswa]) }}" class="text-xs text-primary font-bold">
                                        Lihat Data Siswa &rarr;
                                    </a>
                                @elseif($k->role === 'tutor')
                                    <div class="text-sm text-dark font-semibold">Tenaga Pendidik</div>
                                    <a href="{{ route('admin.payroll.show', $k->id) }}" class="text-xs text-primary font-bold">
                                        Slip Honor &amp; KBM &rarr;
                                    </a>
                                @elseif($k->role === 'magang')
                                    <div class="text-sm text-dark font-semibold">Peserta PKL</div>
                                    <a href="{{ route('admin.magang.presensi') }}" class="text-xs text-primary font-bold">
                                        Presensi Magang &rarr;
                                    </a>
                                @else
                                    <span class="text-xs text-muted">Akses Manajemen</span>
                                @endif
                            </td>
                            <td class="font-bold">
                                @if($k->no_hp)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $k->no_hp) }}" target="_blank" class="text-primary text-no-decor d-inline-flex align-items-center gap-1">
                                        <ion-icon name="logo-whatsapp"></ion-icon>
                                        {{ $k->no_hp }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($isActive)
                                    <span class="app-badge badge-status-aktif">Aktif</span>
                                @else
                                    <span class="app-badge badge-status-nonaktif">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 flex-center flex-wrap">
                                    {{-- Reset Password Button --}}
                                    <form method="POST" action="{{ route('admin.karyawan.resetPassword', $k->id) }}" data-confirm="Reset password akun {{ $displayName }} menjadi 'password123'?" data-confirm-title="Reset Password Akun" data-confirm-type="warning" data-confirm-btn="Ya, Reset Password" class="d-inline m-0">
                                        @csrf
                                        <button type="submit" class="smallBtn btn-action-reset-pw btn-table-action" title="Reset Password Default (password123)">
                                            <ion-icon name="key-outline"></ion-icon> Reset PW
                                        </button>
                                    </form>

                                    {{-- Edit Button --}}
                                    <a class="smallBtn edit btn-table-action" href="{{ route('admin.karyawan.edit', $k->id) }}" title="Edit Data Akun">
                                        Edit
                                    </a>

                                    {{-- Toggle Status Button --}}
                                    <form method="POST" action="{{ route('admin.karyawan.toggleStatus', $k->id) }}" class="d-inline m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="smallBtn btn-status-toggle btn-table-action" title="Klik untuk ubah status aktif/nonaktif">
                                            {{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    {{-- Delete Button (khusus non-current admin) --}}
                                    @if(auth()->id() !== $k->id)
                                        <form method="POST" action="{{ route('admin.karyawan.destroy', $k->id) }}" data-confirm="Apakah Anda yakin ingin menghapus akun {{ $displayName }}? Seluruh hak akses login akan dihapus." data-confirm-title="Hapus Akun Pengguna" data-confirm-type="danger" data-confirm-btn="Ya, Hapus Akun" class="d-inline m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="smallBtn delete cursor-pointer btn-table-action" title="Hapus Akun">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="table-empty-cell">
                                <div class="tableEmptyState">
                                    <div class="tableEmptyIconWrap indigo">
                                        <ion-icon name="people-outline" class="tableEmptyIcon"></ion-icon>
                                    </div>
                                    <div class="tableEmptyTitle">Belum Ada Akun Pengguna</div>
                                    <div class="tableEmptyDesc">Tidak ada data akun pada peran atau kriteria pencarian yang dipilih.</div>
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
        @forelse($karyawan as $index => $k)
            @php
                $displayName = (string) ($k->nama_lengkap ?? $k->name ?? '-');
                $isActive = (bool) ($k->is_active ?? true);
                $roleLabel = match($k->role) {
                    'admin' => 'Administrator',
                    'tutor' => 'Tutor / Pengajar',
                    'kepala_sekolah' => 'Kepala Sekolah',
                    'siswa' => 'Peserta Didik (Siswa)',
                    'magang' => 'Mahasiswa Magang',
                    default => ucfirst(str_replace('_', ' ', (string) $k->role)),
                };
                $roleBadgeClass = match($k->role) {
                    'admin' => 'badge-role-admin',
                    'tutor' => 'badge-role-tutor',
                    'kepala_sekolah' => 'badge-role-kepsek',
                    'siswa' => 'badge-role-siswa',
                    'magang' => 'badge-role-magang',
                    default => 'badge-role-admin',
                };
                $initial = strtoupper(substr($displayName, 0, 1));
            @endphp
            <div class="data-mobile-card">
                <div class="dmc-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar-initial {{ $k->role }}">
                            {{ $initial }}
                        </div>
                        <div>
                            <h3 class="dmc-title">{{ $displayName }}</h3>
                            <div class="dmc-subtitle font-mono">NIK: {{ $k->nik ?? '-' }}</div>
                        </div>
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
                        <div class="dmc-label">Email Login</div>
                        <div class="dmc-value text-xs font-mono text-muted">
                            {{ $k->email ?? '-' }}
                        </div>
                    </div>

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

                    @if($k->role === 'siswa' && $k->siswa)
                        <div class="dmc-field" style="grid-column: span 2;">
                            <div class="dmc-label">Kelas &amp; Program</div>
                            <div class="dmc-value text-sm font-bold text-dark">
                                {{ $k->siswa->relKelas?->nama_kelas ?? 'Tanpa Kelas' }} (No Absen: {{ $k->siswa->no_absen }})
                            </div>
                        </div>
                    @endif
                </div>

                <div class="dmc-footer">
                    <div class="text-xs font-bold text-muted">
                        No. {{ $karyawan->firstItem() + $index }}
                    </div>
                    <div class="dmc-actions">
                        {{-- Reset Password --}}
                        <form method="POST" action="{{ route('admin.karyawan.resetPassword', $k->id) }}" data-confirm="Reset password akun {{ $displayName }} menjadi 'password123'?" data-confirm-title="Reset Password Akun" data-confirm-type="warning" data-confirm-btn="Ya, Reset" class="d-inline m-0">
                            @csrf
                            <button type="submit" class="smallBtn btn-action-reset-pw btn-table-action" title="Reset Password">
                                <ion-icon name="key-outline"></ion-icon> Reset PW
                            </button>
                        </form>

                        {{-- Edit --}}
                        <a class="smallBtn edit btn-table-action" href="{{ route('admin.karyawan.edit', $k->id) }}" title="Edit Akun">
                            Edit
                        </a>

                        {{-- Toggle Status --}}
                        <form method="POST" action="{{ route('admin.karyawan.toggleStatus', $k->id) }}" class="d-inline m-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="smallBtn btn-status-toggle btn-table-action" title="Ubah Status">
                                {{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>

                        {{-- Delete --}}
                        @if(auth()->id() !== $k->id)
                            <form method="POST" action="{{ route('admin.karyawan.destroy', $k->id) }}" data-confirm="Apakah Anda yakin ingin menghapus akun {{ $displayName }}?" data-confirm-title="Hapus Akun" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="smallBtn delete cursor-pointer btn-table-action" title="Hapus Akun">
                                    Hapus
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="tableEmptyCard">
                <div class="tableEmptyIconWrap indigo">
                    <ion-icon name="people-outline" class="tableEmptyIcon"></ion-icon>
                </div>
                <div class="tableEmptyTitle">Belum Ada Akun Pengguna</div>
                <div class="tableEmptyDesc">Tidak ada data akun pada peran atau kriteria pencarian yang dipilih.</div>
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
