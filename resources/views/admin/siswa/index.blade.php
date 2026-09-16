@extends('layouts.admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader" style="padding-left: 0; padding-right: 0; margin-bottom: 16px;">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">MANAJEMEN PESERTA DIDIK</div>
                <h1 class="laporanHeaderTitle">Data Siswa</h1>
                <div class="laporanHeaderSub">Kelola data peserta didik, wali murid, status siklus, dan rombongan belajar</div>
                <p class="laporanHeaderDesc">Daftar siswa, pemetaan paket kesetaraan, data ABK, dan histori rombel.</p>
            </div>
            <div class="laporanHeaderActions" style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="{{ route('admin.siswa.exportExcel') }}" class="profileBtnPrimary" style="padding:9px 14px;background:#16a34a;font-size:12.5px;border-radius:12px;text-decoration:none;display:inline-flex;align-items:center;">
                    Export Excel
                </a>
                <button type="button" onclick="document.getElementById('importSiswaModal').style.display='flex'" class="profileBtnPrimary" style="padding:9px 14px;background:#0284c7;font-size:12.5px;border-radius:12px;border:none;cursor:pointer;">
                    Import Siswa
                </button>
                <a href="{{ route('admin.siswa.create') }}" class="profileBtnPrimary" style="padding:9px 16px;font-size:12.5px;border-radius:12px;text-decoration:none;display:inline-flex;align-items:center;">
                    Tambah Siswa
                </a>
            </div>
        </div>
    </div>

    {{-- ── Modal Impor Siswa ── --}}
    <div id="importSiswaModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:var(--card,#fff);border-radius:20px;max-width:500px;width:100%;padding:28px;box-shadow:0 24px 48px rgba(0,0,0,0.25);border:1px solid var(--border,#e2e8f0);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="margin:0;font-size:18px;font-weight:800;color:var(--text);">Impor Data Siswa Massal</h3>
                <button type="button" onclick="document.getElementById('importSiswaModal').style.display='none'" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);">&times;</button>
            </div>
            <p style="font-size:13px;color:var(--muted);margin-bottom:18px;line-height:1.5;">
                Unggah berkas spreadsheet Excel/CSV untuk mendaftarkan siswa baru secara massal atau memperbarui data siswa berdasarkan nomor absen (NIS).
            </p>
            <div style="margin-bottom:20px;">
                <a href="{{ route('admin.siswa.downloadTemplate') }}" class="btnOutline" style="display:inline-flex;align-items:center;gap:8px;padding:9px 16px;font-size:12.5px;font-weight:700;">
                    Download Template Siswa (.xlsx)
                </a>
            </div>
            <form method="POST" action="{{ route('admin.siswa.importExcel') }}" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:20px;">
                    <label style="display:block;font-size:12px;font-weight:800;text-transform:uppercase;margin-bottom:8px;color:var(--muted);">Pilih Berkas Data Siswa:</label>
                    <div class="fileUploadBox">
                        <input type="file" name="file_excel" id="siswaFileInput" accept=".xlsx,.xls,.csv" required onchange="handleFileSelected(this, 'siswaFileFeedback')">
                        <div class="fileUploadText" style="margin-top:0;font-size:13px;font-weight:700;">Pilih atau seret berkas ke sini</div>
                        <div class="fileUploadSubtext">
                            <span class="fileUploadInfoPill">Format: .XLSX, .CSV</span>
                            <span class="fileUploadInfoPill">Maks: 5 MB</span>
                        </div>
                    </div>
                    <div id="siswaFileFeedback" class="fileUploadFeedback"></div>
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" onclick="document.getElementById('importSiswaModal').style.display='none'" class="profileBtnDanger" style="height:40px;padding:0 18px;font-size:12.5px;border-radius:12px;width:auto;">Batal</button>
                    <button type="submit" class="profileBtnPrimary" style="height:40px;padding:0 20px;font-size:12.5px;border-radius:12px;width:auto;">Unggah &amp; Impor</button>
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
        <form method="GET" action="{{ route('admin.siswa.index') }}">
            <div class="laporanFilterGrid">
                <div class="filterField">
                    <label class="filterFieldLabel">Pencarian</label>
                    <input type="text" name="q" class="profileInput" value="{{ request('q') }}" placeholder="Nama, Absen/NIS, Wali..." style="height: 42px; font-size: 13px;">
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

                <div class="filterActionGroup" style="grid-column: 1 / -1; margin-top: 4px; display: flex; flex-wrap: wrap; gap: 8px;">
                    <button type="submit" class="profileBtnPrimary" style="height: 42px; padding: 0 18px; font-size: 13px; border-radius: 12px; flex: 1; min-width: 140px;">
                        Terapkan Filter
                    </button>
                    <a href="{{ route('admin.siswa.index') }}" class="profileBtnDanger" style="height: 42px; padding: 0 14px; font-size: 13px; border-radius: 12px; width: auto; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Section Title ── --}}
    <div class="sectionRow">
        <h2>Daftar Peserta Didik</h2>
        <span class="badgeCount">{{ $siswas->total() }} Siswa Terdata</span>
    </div>

    {{-- ── Data Table Container (Desktop) ── --}}
    <div class="table-responsive-desktop">
        <div class="tableContainer" style="margin: 0 0 20px; border-radius: 18px; border: 1px solid var(--border);">
            <table class="laporanTable">
                <thead>
                    <tr>
                        <th style="padding: 12px 14px; width: 50px;">NO</th>
                        <th>ABSEN / NIS</th>
                        <th>NAMA LENGKAP</th>
                        <th>KELAS / ROMBEL</th>
                        <th>JENJANG</th>
                        <th>STATUS SIKLUS</th>
                        <th>NAMA WALI</th>
                        <th>TUTOR</th>
                        <th style="text-align: center; width: 180px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswas as $index => $siswa)
                        @php
                            $statusBadgeStyle = match($siswa->status_siswa) {
                                'alumni' => 'background:#e0e7ff;color:#3730a3;border:1px solid #c7d2fe;',
                                'cuti' => 'background:#fef3c7;color:#b45309;border:1px solid #fde68a;',
                                'nonaktif' => 'background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;',
                                default => 'background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;',
                            };
                        @endphp
                        <tr>
                            <td style="padding: 12px 14px; font-weight: 700; color: var(--muted);">
                                {{ $siswas->firstItem() + $index }}
                            </td>
                            <td style="font-weight: 700; color: var(--text);">
                                {{ $siswa->no_absen ?? '-' }}
                            </td>
                            <td style="font-weight: 800; color: var(--text);">
                                {{ $siswa->nama_siswa }}
                            </td>
                            <td style="font-weight: 600;">
                                {{ $siswa->relKelas->nama_kelas ?? '-' }}
                            </td>
                            <td style="font-weight: 700; color: var(--blue2);">
                                {{ $siswa->jenjang_paket_label }}
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;flex-wrap:wrap;align-items:center;">
                                    <span style="display:inline-block;padding:3px 8px;border-radius:8px;font-size:11px;font-weight:800;{{ $statusBadgeStyle }}">
                                        {{ $siswa->status_label }}
                                    </span>
                                    @if($siswa->is_abk)
                                        <span style="display:inline-block;padding:3px 7px;border-radius:8px;font-size:10.5px;font-weight:800;background:#fef3c7;color:#b45309;border:1px solid #fde68a;">
                                            ABK
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td style="color:var(--muted);font-size:12px;">
                                {{ $siswa->nama_wali ?? '-' }}
                            </td>
                            <td style="font-size:12px;font-weight:600;">
                                {{ $siswa->tutor->nama_lengkap ?? '-' }}
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center; align-items: center; flex-wrap: wrap;">
                                    <a class="smallBtn" style="padding: 6px 10px; min-height: 32px; font-size: 11.5px; border:1px solid var(--border);color:var(--text);background:var(--card-alt,#f8fafc);" href="{{ route('admin.siswa.show', $siswa) }}" title="Lihat Detail Siswa">
                                        Detail
                                    </a>

                                    <a class="smallBtn edit" href="{{ route('admin.siswa.edit', $siswa) }}" style="padding: 6px 10px; min-height: 32px; font-size: 11.5px;">
                                        Edit
                                    </a>

                                    <form method="POST" action="{{ route('admin.siswa.destroy', $siswa) }}" data-confirm="Arsipkan data siswa ini (Soft Delete)? Seluruh data riwayat presensi dan honor mengajar tutor akan tetap aman tersimpan." data-confirm-title="Arsipkan Siswa" data-confirm-type="danger" data-confirm-btn="Ya, Arsipkan" style="display:inline; margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="smallBtn delete cursor-pointer" title="Arsipkan Siswa" style="padding: 6px 10px; min-height: 32px; font-size: 11.5px;">
                                            Arsipkan
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; color: var(--muted); padding: 36px 16px;">
                                <div style="font-weight: 700; font-size: 13.5px; margin-bottom: 2px;">Belum Ada Data Siswa</div>
                                <div style="font-size: 12px;">Tidak ada data peserta didik pada filter yang dipilih.</div>
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
            @php
                $statusBadgeStyle = match($siswa->status_siswa) {
                    'alumni' => 'background:#e0e7ff;color:#3730a3;border:1px solid #c7d2fe;',
                    'cuti' => 'background:#fef3c7;color:#b45309;border:1px solid #fde68a;',
                    'nonaktif' => 'background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;',
                    default => 'background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;',
                };
            @endphp
            <div class="data-mobile-card">
                <div class="dmc-header">
                    <div>
                        <h3 class="dmc-title">{{ $siswa->nama_siswa }}</h3>
                        <div class="dmc-subtitle">Absen / NIS: {{ $siswa->no_absen ?? '-' }}</div>
                    </div>
                    <div style="display:flex;gap:4px;flex-direction:column;align-items:flex-end;">
                        <span style="display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:800;{{ $statusBadgeStyle }}">
                            {{ $siswa->status_label }}
                        </span>
                        @if($siswa->is_abk)
                            <span style="display:inline-block;padding:2px 7px;border-radius:6px;font-size:10.5px;font-weight:800;background:#fef3c7;color:#b45309;border:1px solid #fde68a;">
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
                        <div class="dmc-value" style="color:var(--blue2);">{{ $siswa->jenjang_paket_label }}</div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Tutor Pembimbing</div>
                        <div class="dmc-value" style="font-size:12px;">{{ $siswa->tutor->nama_lengkap ?? '-' }}</div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Wali Murid</div>
                        <div class="dmc-value" style="font-size:12px;">{{ $siswa->nama_wali ?? '-' }}</div>
                    </div>
                </div>

                <div class="dmc-footer">
                    <div style="font-size:11px;font-weight:700;color:var(--muted);">
                        No. {{ $siswas->firstItem() + $index }}
                    </div>
                    <div class="dmc-actions">
                        <a class="smallBtn" style="padding: 7px 12px; font-size: 12px; border:1px solid var(--border);color:var(--text);background:var(--card-alt,#f8fafc);" href="{{ route('admin.siswa.show', $siswa) }}" title="Lihat Detail Siswa">
                            Detail
                        </a>

                        <a class="smallBtn edit" href="{{ route('admin.siswa.edit', $siswa) }}" style="padding: 7px 14px; font-size: 12px;">
                            Edit
                        </a>

                        <form method="POST" action="{{ route('admin.siswa.destroy', $siswa) }}" data-confirm="Arsipkan data siswa ini (Soft Delete)? Seluruh data riwayat presensi dan honor mengajar tutor akan tetap aman tersimpan." data-confirm-title="Arsipkan Siswa" data-confirm-type="danger" data-confirm-btn="Ya, Arsipkan" style="display:inline; margin:0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="smallBtn delete cursor-pointer" style="padding: 7px 12px; font-size: 12px;">
                                Arsipkan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="data-mobile-card" style="text-align:center;padding:32px 16px;color:var(--muted);">
                <div style="font-weight: 700; font-size: 13.5px; margin-bottom: 2px;">Belum Ada Data Siswa</div>
                <div style="font-size: 12px;">Tidak ada data peserta didik pada filter yang dipilih.</div>
            </div>
        @endforelse
    </div>

    @if(method_exists($siswas, 'links'))
        <div class="paginatePad" style="padding: 0 0 16px;">
            {{ $siswas->links() }}
        </div>
    @endif
</div>

@endsection


