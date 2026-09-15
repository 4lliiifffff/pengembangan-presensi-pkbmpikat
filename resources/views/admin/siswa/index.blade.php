@extends('layouts.admin')

@section('content')

    <div class="pageHeaderRow" style="flex-wrap:wrap;gap:10px;align-items:center;">
        <div>
            <h2 style="margin:0;">Data Siswa</h2>
            <p style="margin:2px 0 0;font-size:12px;color:var(--muted);">Kelola data peserta didik, wali murid, dan tarif mengajar</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('admin.siswa.exportExcel') }}" class="btnPrimary" style="padding:8px 12px;background:#16a34a;font-size:12px;">
                <ion-icon name="download-outline"></ion-icon> Export Excel
            </a>
            <button type="button" onclick="document.getElementById('importSiswaModal').style.display='flex'" class="btnPrimary" style="padding:8px 12px;background:#0284c7;font-size:12px;">
                <ion-icon name="cloud-upload-outline"></ion-icon> Import Siswa
            </button>
            <a href="{{ route('admin.siswa.create') }}" class="btnPrimary" style="padding:8px 12px;background:var(--blue-gradient);font-size:12px;">
                <ion-icon name="add-outline"></ion-icon> Tambah Siswa
            </a>
        </div>
    </div>

    {{-- ── Modal Impor Siswa ── --}}
    <div id="importSiswaModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:var(--card,#fff);border-radius:18px;max-width:480px;width:100%;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.2);border:1px solid var(--border,#e2e8f0);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="margin:0;font-size:17px;font-weight:800;color:var(--text);">Impor Data Siswa Massal</h3>
                <button type="button" onclick="document.getElementById('importSiswaModal').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);">&times;</button>
            </div>
            <p style="font-size:13px;color:var(--muted);margin-bottom:16px;line-height:1.4;">
                Unggah berkas spreadsheet Excel/CSV untuk mendaftarkan siswa baru secara massal atau memperbarui data siswa berdasarkan nomor absen (NIS).
            </p>
            <div style="margin-bottom:18px;">
                <a href="{{ route('admin.siswa.downloadTemplate') }}" class="btnOutline" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;font-size:12px;font-weight:700;">
                    <ion-icon name="download-outline"></ion-icon> Download Template Siswa (.xlsx)
                </a>
            </div>
            <form method="POST" action="{{ route('admin.siswa.importExcel') }}" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:11.5px;font-weight:800;text-transform:uppercase;margin-bottom:6px;color:var(--muted);">Pilih Berkas Data Siswa:</label>
                    <div class="fileUploadBox">
                        <input type="file" name="file_excel" id="siswaFileInput" accept=".xlsx,.xls,.csv" required onchange="handleFileSelected(this, 'siswaFileFeedback')">
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
                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <button type="button" onclick="document.getElementById('importSiswaModal').style.display='none'" class="profileBtnDanger" style="height:38px;padding:0 14px;font-size:12px;border-radius:10px;width:auto;">Batal</button>
                    <button type="submit" class="profileBtnPrimary" style="height:38px;padding:0 16px;font-size:12px;border-radius:10px;width:auto;">Unggah &amp; Impor</button>
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
                feedback.innerHTML = '<ion-icon name="document-text-outline" style="font-size:16px;"></ion-icon> <span>' + file.name + ' (' + sizeKb + ' KB)</span>';
                feedback.style.display = 'flex';
            } else {
                feedback.style.display = 'none';
            }
        }
    </script>



    <div class="siswaGrid">
        @forelse($siswas as $siswa)
            <div class="siswaCard">
                <div class="siswaTop">
                    <div style="display:flex;align-items:center;gap:12px;min-width:0;">
                        @php
                            $initial = strtoupper(substr((string) ($siswa->nama_siswa ?? ''), 0, 1));
                        @endphp
                        <div class="activityAvatar">
                            {{ $initial }}
                        </div>
                        <div style="min-width:0;">
                            <div class="siswaName" style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                <span>{{ $siswa->nama_siswa }}</span>
                                @if($siswa->is_abk)
                                    <span style="display:inline-block;padding:2px 6px;border-radius:6px;font-size:10px;font-weight:800;background:#fef3c7;color:#b45309;">ABK</span>
                                @endif
                            </div>
                            <div class="siswaMeta">
                                No Absen: {{ $siswa->no_absen }} • Kelas: {{ $siswa->relKelas->nama_kelas ?? '-' }}
                                <br>
                                Tutor: <span class="td-bold">{{ $siswa->tutor->nama_lengkap ?? 'Belum Ditentukan' }}</span>
                                • @if($siswa->is_abk)
                                    <span style="display:inline-block;padding:1px 6px;border-radius:6px;font-size:11px;font-weight:800;background:#fef3c7;color:#b45309;">SK: ABK (Rp 100rb-130rb)</span>
                                @else
                                    <span style="display:inline-block;padding:1px 6px;border-radius:6px;font-size:11px;font-weight:800;background:#f0fdf4;color:#15803d;">SK: Reguler (Rp 50rb-100rb)</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="siswaActions">
                    <a class="smallBtn edit" href="{{ route('admin.siswa.edit', $siswa) }}">
                        <ion-icon name="create-outline"></ion-icon>
                        Edit
                    </a>

                    <form method="POST" action="{{ route('admin.siswa.destroy', $siswa) }}" onsubmit="return confirm('Yakin hapus siswa ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="smallBtn delete cursor-pointer">
                            <ion-icon name="trash-outline"></ion-icon>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="emptyState">Belum ada data siswa.</div>
        @endforelse
    </div>

    <div class="pad-bottom-actions">
        {{ $siswas->links() }}
    </div>

    <a href="{{ route('admin.siswa.create') }}" class="fabAdd" aria-label="Tambah Siswa">
        <ion-icon name="add-outline"></ion-icon>
    </a>
@endsection

