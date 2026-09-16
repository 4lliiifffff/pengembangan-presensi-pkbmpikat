@extends('layouts.admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader" style="padding-left: 0; padding-right: 0; margin-bottom: 16px;">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">MANAJEMEN PENDIDIK &amp; STAF</div>
                <h1 class="laporanHeaderTitle">Data Karyawan &amp; Tutor</h1>
                <div class="laporanHeaderSub">Kelola tenaga pendidik, staf pengajar, dan hak akses akun sistem</div>
                <p class="laporanHeaderDesc">Daftar staf, pembaruan data login, status keaktifan, dan impor/ekspor data.</p>
            </div>
            <div class="laporanHeaderActions" style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="{{ route('admin.magang.index') }}" class="btnOutline" style="padding:9px 14px;border-color:var(--primary);color:var(--primary);font-size:12.5px;font-weight:700;">
                    Peserta Magang
                </a>
                <a href="{{ route('admin.karyawan.exportExcel') }}" class="profileBtnPrimary" style="padding:9px 14px;background:#16a34a;font-size:12.5px;border-radius:12px;text-decoration:none;display:inline-flex;align-items:center;">
                    Export Excel
                </a>
                <button type="button" onclick="document.getElementById('importKaryawanModal').style.display='flex'" class="profileBtnPrimary" style="padding:9px 14px;background:#0284c7;font-size:12.5px;border-radius:12px;border:none;cursor:pointer;">
                    Import Tutor
                </button>
                <a href="{{ route('admin.karyawan.create') }}" class="profileBtnPrimary" style="padding:9px 16px;font-size:12.5px;border-radius:12px;text-decoration:none;display:inline-flex;align-items:center;">
                    Tambah Staf
                </a>
            </div>
        </div>
    </div>

    {{-- ── Modal Impor Tutor / Karyawan ── --}}
    <div id="importKaryawanModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:var(--card,#fff);border-radius:20px;max-width:500px;width:100%;padding:28px;box-shadow:0 24px 48px rgba(0,0,0,0.25);border:1px solid var(--border,#e2e8f0);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="margin:0;font-size:18px;font-weight:800;color:var(--text);">Impor Data Tutor &amp; Karyawan</h3>
                <button type="button" onclick="document.getElementById('importKaryawanModal').style.display='none'" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);">&times;</button>
            </div>
            <p style="font-size:13px;color:var(--muted);margin-bottom:18px;line-height:1.5;">
                Unggah berkas spreadsheet Excel/CSV untuk mendaftarkan tutor dan staf baru secara massal. Akun login akan otomatis digenerate dengan password default berbasis NIK.
            </p>
            <div style="margin-bottom:20px;">
                <a href="{{ route('admin.karyawan.downloadTemplate') }}" class="btnOutline" style="display:inline-flex;align-items:center;gap:8px;padding:9px 16px;font-size:12.5px;font-weight:700;">
                    Download Template Tutor (.xlsx)
                </a>
            </div>
            <form method="POST" action="{{ route('admin.karyawan.importExcel') }}" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:20px;">
                    <label style="display:block;font-size:12px;font-weight:800;text-transform:uppercase;margin-bottom:8px;color:var(--muted);">Pilih Berkas Data Tutor/Staf:</label>
                    <div class="fileUploadBox">
                        <input type="file" name="file_excel" id="karyawanFileInput" accept=".xlsx,.xls,.csv" required onchange="handleFileSelected(this, 'karyawanFileFeedback')">
                        <div class="fileUploadText" style="margin-top:0;font-size:13px;font-weight:700;">Pilih atau seret berkas ke sini</div>
                        <div class="fileUploadSubtext">
                            <span class="fileUploadInfoPill">Format: .XLSX, .CSV</span>
                            <span class="fileUploadInfoPill">Maks: 5 MB</span>
                        </div>
                    </div>
                    <div id="karyawanFileFeedback" class="fileUploadFeedback"></div>
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" onclick="document.getElementById('importKaryawanModal').style.display='none'" class="profileBtnDanger" style="height:40px;padding:0 18px;font-size:12.5px;border-radius:12px;width:auto;">Batal</button>
                    <button type="submit" class="profileBtnPrimary" style="height:40px;padding:0 20px;font-size:12.5px;border-radius:12px;width:auto;">Unggah &amp; Impor Tutor</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function handleFileSelected(input, feedbackId) {
            const feedback = document.getElementById(feedbackId);
            if (!feedback) return;
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const sizeKb = Math.round(file.size / 1024);
                feedback.innerHTML = '<span>' + file.name + ' (' + sizeKb + ' KB)</span>';
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
                    <input type="text" name="search" class="profileInput" value="{{ request('search') ?? request('q') }}" placeholder="Nama, NIK, No HP..." style="height: 42px; font-size: 13px;">
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

                <div class="filterActionGroup" style="grid-column: 1 / -1; margin-top: 4px; display: flex; flex-wrap: wrap; gap: 8px;">
                    <button type="submit" class="profileBtnPrimary" style="height: 42px; padding: 0 18px; font-size: 13px; border-radius: 12px; flex: 1; min-width: 140px;">
                        Terapkan Filter
                    </button>
                    <a href="{{ route('admin.karyawan.index') }}" class="profileBtnDanger" style="height: 42px; padding: 0 14px; font-size: 13px; border-radius: 12px; width: auto; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Section Title ── --}}
    <div class="sectionRow">
        <h2>Daftar Karyawan &amp; Tenaga Pendidik</h2>
        <span class="badgeCount">{{ $karyawan->total() }} Staf Terdaftar</span>
    </div>

    {{-- ── Data Table Container (Desktop) ── --}}
    <div class="table-responsive-desktop">
        <div class="tableContainer" style="margin: 0 0 20px; border-radius: 18px; border: 1px solid var(--border);">
            <table class="laporanTable">
                <thead>
                    <tr>
                        <th style="padding: 12px 14px; width: 50px;">NO</th>
                        <th>NAMA LENGKAP</th>
                        <th>NIK</th>
                        <th>ROLE / JABATAN</th>
                        <th>NO HP / WHATSAPP</th>
                        <th>EMAIL</th>
                        <th>STATUS</th>
                        <th style="text-align: center; width: 180px;">AKSI</th>
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
                                default => ucfirst(str_replace('_', ' ', (string) $k->role)),
                            };
                            $roleBadgeStyle = match($k->role) {
                                'admin' => 'background:#ede9fe;color:#6d28d9;border:1px solid #ddd6fe;',
                                'tutor' => 'background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;',
                                'kepala_sekolah' => 'background:#fef3c7;color:#b45309;border:1px solid #fde68a;',
                                default => 'background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;',
                            };
                        @endphp
                        <tr>
                            <td style="padding: 12px 14px; font-weight: 700; color: var(--muted);">
                                {{ $karyawan->firstItem() + $index }}
                            </td>
                            <td style="font-weight: 800; color: var(--text);">
                                {{ $displayName }}
                            </td>
                            <td style="font-weight: 700; color: var(--muted);">
                                {{ $k->nik ?? '-' }}
                            </td>
                            <td>
                                <span style="display:inline-block;padding:3px 9px;border-radius:8px;font-size:11px;font-weight:800;{{ $roleBadgeStyle }}">
                                    {{ $roleLabel }}
                                </span>
                            </td>
                            <td style="font-weight: 700;">
                                @if($k->no_hp)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $k->no_hp) }}" target="_blank" style="color:var(--blue2);text-decoration:none;">
                                        {{ $k->no_hp }}
                                    </a>
                                @else
                                    <span style="color:var(--muted);">-</span>
                                @endif
                            </td>
                            <td style="color:var(--muted);font-size:12px;">
                                {{ $k->email ?? '-' }}
                            </td>
                            <td>
                                @if($isActive)
                                    <span style="display:inline-block;padding:3px 8px;border-radius:8px;font-size:11px;font-weight:800;background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;">Aktif</span>
                                @else
                                    <span style="display:inline-block;padding:3px 8px;border-radius:8px;font-size:11px;font-weight:800;background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;">Nonaktif</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center; align-items: center; flex-wrap: wrap;">
                                    <a class="smallBtn edit" href="{{ route('admin.karyawan.edit', $k->id) }}" style="padding: 6px 12px; min-height: 32px; font-size: 11.5px;">
                                        Edit
                                    </a>

                                    <form method="POST" action="{{ route('admin.karyawan.toggleStatus', $k->id) }}" style="display:inline; margin:0;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="smallBtn" style="padding: 6px 10px; min-height: 32px; font-size: 11.5px; border:1px solid var(--border);color:var(--text);background:var(--card-alt,#f8fafc);cursor:pointer;" title="Klik untuk ubah status aktif/nonaktif">
                                            {{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.karyawan.destroy', $k->id) }}" data-confirm="Apakah Anda yakin ingin menghapus staf {{ $displayName }}? Seluruh riwayat akun akan dihapus." data-confirm-title="Hapus Karyawan" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" style="display:inline; margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="smallBtn delete cursor-pointer" title="Hapus Staf" style="padding: 6px 10px; min-height: 32px; font-size: 11.5px;">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--muted); padding: 36px 16px;">
                                <div style="font-weight: 700; font-size: 13.5px; margin-bottom: 2px;">Belum Ada Data Karyawan</div>
                                <div style="font-size: 12px;">Tidak ada data staf atau tutor pada filter yang dipilih.</div>
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
                $roleBadgeStyle = match($k->role) {
                    'admin' => 'background:#ede9fe;color:#6d28d9;border:1px solid #ddd6fe;',
                    'tutor' => 'background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;',
                    'kepala_sekolah' => 'background:#fef3c7;color:#b45309;border:1px solid #fde68a;',
                    default => 'background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;',
                };
            @endphp
            <div class="data-mobile-card">
                <div class="dmc-header">
                    <div>
                        <h3 class="dmc-title">{{ $displayName }}</h3>
                        <div class="dmc-subtitle">NIK: {{ $k->nik ?? '-' }}</div>
                    </div>
                    <div style="display:flex;gap:4px;flex-direction:column;align-items:flex-end;">
                        @if($isActive)
                            <span style="display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:800;background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;">Aktif</span>
                        @else
                            <span style="display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:800;background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;">Nonaktif</span>
                        @endif
                        <span style="display:inline-block;padding:2px 7px;border-radius:6px;font-size:10.5px;font-weight:800;{{ $roleBadgeStyle }}">
                            {{ $roleLabel }}
                        </span>
                    </div>
                </div>

                <div class="dmc-grid">
                    <div class="dmc-field">
                        <div class="dmc-label">No WhatsApp</div>
                        <div class="dmc-value">
                            @if($k->no_hp)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $k->no_hp) }}" target="_blank" style="color:var(--blue2);text-decoration:none;">
                                    {{ $k->no_hp }}
                                </a>
                            @else
                                <span style="color:var(--muted);">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Email</div>
                        <div class="dmc-value" style="font-size:11.5px;color:var(--muted);">
                            {{ $k->email ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="dmc-footer">
                    <div style="font-size:11px;font-weight:700;color:var(--muted);">
                        No. {{ $karyawan->firstItem() + $index }}
                    </div>
                    <div class="dmc-actions">
                        <a class="smallBtn edit" href="{{ route('admin.karyawan.edit', $k->id) }}" style="padding: 7px 14px; font-size: 12px;">
                            Edit
                        </a>

                        <form method="POST" action="{{ route('admin.karyawan.toggleStatus', $k->id) }}" style="display:inline; margin:0;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="smallBtn" style="padding: 7px 12px; font-size: 12px; border:1px solid var(--border);color:var(--text);background:var(--card-alt,#f8fafc);cursor:pointer;">
                                {{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.karyawan.destroy', $k->id) }}" data-confirm="Apakah Anda yakin ingin menghapus staf {{ $displayName }}? Seluruh riwayat akun akan dihapus." data-confirm-title="Hapus Karyawan" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" style="display:inline; margin:0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="smallBtn delete cursor-pointer" style="padding: 7px 12px; font-size: 12px;">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="data-mobile-card" style="text-align:center;padding:32px 16px;color:var(--muted);">
                <div style="font-weight: 700; font-size: 13.5px; margin-bottom: 2px;">Belum Ada Data Karyawan</div>
                <div style="font-size: 12px;">Tidak ada data staf atau tutor pada filter yang dipilih.</div>
            </div>
        @endforelse
    </div>

    @if(method_exists($karyawan, 'links'))
        <div class="paginatePad" style="padding: 0 0 16px;">
            {{ $karyawan->links() }}
        </div>
    @endif
</div>

@endsection
