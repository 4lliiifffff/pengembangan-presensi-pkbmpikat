@extends('layouts.admin')

@section('title', 'Monitoring Presensi Magang / PKL')

@section('content')

    <div class="pageHeaderRow header-actions-group" style="padding: 16px 16px 6px; justify-content: space-between;">
        <div>
            <h2 style="margin: 0; font-size: 18px; font-weight: 800; color: var(--text);">Monitoring Presensi Magang &amp; PKL</h2>
            <p style="margin: 2px 0 0; font-size: 12px; color: var(--muted);">Rekapitulasi log absensi masuk dan pulang mahasiswa/siswa magang</p>
        </div>
        <div class="header-actions-group">
            <a href="{{ route('admin.magang.exportPdf', request()->all()) }}" class="profileBtnDanger" style="padding: 0 14px; height: 38px; font-size: 12px; border-radius: 10px; width: auto; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <ion-icon name="document-text-outline" style="font-size: 15px;"></ion-icon> Export PDF
            </a>
            <a href="{{ route('admin.magang.index') }}" class="btnOutline" style="padding: 0 14px; height: 38px; font-size: 12px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <ion-icon name="people-outline" style="font-size: 15px;"></ion-icon> Data Magang
            </a>
        </div>
    </div>

    <!-- Statistik -->
    <div class="statsRow" style="padding: 0 16px 14px; display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px;">
        <div class="statBox dark" style="padding: 14px; border-radius: 14px;">
            <ion-icon name="calendar-outline" style="font-size: 22px; margin-bottom: 4px;"></ion-icon>
            <h2 style="font-size: 20px; margin: 4px 0 2px;">{{ $totalPresensi }}</h2>
            <div style="font-size: 10px; letter-spacing: 0.5px; opacity: 0.85;">TOTAL LOG</div>
        </div>
        <div class="statBox active" style="padding: 14px; border-radius: 14px;">
            <ion-icon name="checkmark-done-circle-outline" style="font-size: 22px; margin-bottom: 4px;"></ion-icon>
            <h2 style="font-size: 20px; margin: 4px 0 2px;">{{ $totalHadirLengkap }}</h2>
            <div style="font-size: 10px; letter-spacing: 0.5px; opacity: 0.85;">HADIR LENGKAP</div>
        </div>
        <div class="statBox" style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 14px; border-radius: 14px;">
            <ion-icon name="time-outline" style="font-size: 22px; margin-bottom: 4px; color: #d97706;"></ion-icon>
            <h2 style="font-size: 20px; margin: 4px 0 2px; color: #92400e;">{{ $totalSedangProses }}</h2>
            <div style="font-size: 10px; letter-spacing: 0.5px; color: #b45309;">SEDANG BERLANGSUNG</div>
        </div>
    </div>

    <!-- Filter Box -->
    <div class="laporanFilterCard" style="margin: 0 16px 16px; padding: 14px;">
        <form method="GET" action="{{ route('admin.magang.presensi') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; align-items: end;">
            <div>
                <label class="form-field-label">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDateStr }}" class="profileInput" style="height: 40px; font-size: 12.5px;">
            </div>
            <div>
                <label class="form-field-label">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDateStr }}" class="profileInput" style="height: 40px; font-size: 12.5px;">
            </div>
            <div>
                <label class="form-field-label">Peserta Magang</label>
                <select name="user_id" class="profileInput" style="height: 40px; font-size: 12.5px;">
                    <option value="">Semua Peserta</option>
                    @foreach($allMagangUsers as $u)
                        <option value="{{ $u->id }}" {{ $magangUserId == $u->id ? 'selected' : '' }}>
                            {{ $u->nama_lengkap ?? $u->name }} ({{ $u->magang?->asal_instansi ?: $u->nik }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-field-label">Status Kehadiran</label>
                <select name="status" class="profileInput" style="height: 40px; font-size: 12.5px;">
                    <option value="">Semua Status</option>
                    <option value="hadir" {{ $statusFilter === 'hadir' ? 'selected' : '' }}>Hadir Lengkap</option>
                    <option value="proses" {{ $statusFilter === 'proses' ? 'selected' : '' }}>Sedang Berlangsung</option>
                </select>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="profileBtnPrimary" style="height: 40px; padding: 0 16px; font-size: 12.5px; border-radius: 12px; flex: 1;">
                    <ion-icon name="filter-outline"></ion-icon> Filter
                </button>
                <a href="{{ route('admin.magang.presensi') }}" class="profileBtnDanger" style="height: 40px; padding: 0 12px; font-size: 12.5px; border-radius: 12px; width: auto; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Log Presensi -->
    <div class="tableContainer" style="margin: 0 16px 20px; border-radius: 18px; border: 1px solid var(--border);">
        <table class="laporanTable">
            <thead>
                <tr>
                    <th style="padding: 12px 14px;">TANGGAL</th>
                    <th>PESERTA MAGANG</th>
                    <th>ABSEN MASUK</th>
                    <th>ABSEN PULANG</th>
                    <th>DURASI</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($presensis as $p)
                    @php
                        $u = $p->user;
                        $tgl = \Carbon\Carbon::parse($p->tgl_presensi)->translatedFormat('d M Y');
                        $hari = \Carbon\Carbon::parse($p->tgl_presensi)->translatedFormat('l');
                        $masuk = $p->jam_mulai ? substr((string)$p->jam_mulai, 0, 5) : '—';
                        $pulang = $p->jam_selesai ? substr((string)$p->jam_selesai, 0, 5) : '—';
                        
                        $durasi = '—';
                        if ($p->jam_mulai && $p->jam_selesai) {
                            try {
                                $dtMulai = \Carbon\Carbon::parse($p->tgl_presensi . ' ' . $p->jam_mulai);
                                $dtSelesai = \Carbon\Carbon::parse($p->tgl_presensi . ' ' . $p->jam_selesai);
                                $diffMin = $dtMulai->diffInMinutes($dtSelesai);
                                $durasi = floor($diffMin / 60) . 'j ' . ($diffMin % 60) . 'm';
                            } catch (\Throwable) {}
                        }
                    @endphp
                    <tr>
                        <td style="white-space: nowrap;">
                            <div style="font-weight: 700; font-size: 13px; color: var(--text);">{{ $tgl }}</div>
                            <div style="font-size: 11px; color: var(--muted);">{{ $hari }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 700; font-size: 13px; color: var(--text);">{{ $u->nama_lengkap ?? ($u->name ?? 'Magang') }}</div>
                            <div style="font-size: 11px; color: var(--muted);">{{ $u->magang?->asal_instansi ?: '-' }} (NIM: {{ $u->magang?->nim_nisn ?: $u->nik }})</div>
                        </td>
                        <td>
                            <div style="font-weight: 700; font-size: 13px; color: #0284c7;">{{ $masuk }} WIB</div>
                            @if($p->foto_mulai)
                                <a href="javascript:void(0)" onclick="openPreviewModal('{{ asset($p->foto_mulai) }}', 'Foto Masuk: {{ $u->nama_lengkap ?? $u->name }}')" style="font-size: 11px; color: #0284c7; display: inline-flex; align-items: center; gap: 3px; margin-top: 3px; text-decoration: none; font-weight: 600;">
                                    <ion-icon name="image-outline"></ion-icon> Lihat Foto
                                </a>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 700; font-size: 13px; color: {{ $p->jam_selesai ? '#16a34a' : 'var(--muted)' }};">{{ $pulang }} {{ $p->jam_selesai ? 'WIB' : '' }}</div>
                            @if($p->foto_selesai)
                                <a href="javascript:void(0)" onclick="openPreviewModal('{{ asset($p->foto_selesai) }}', 'Foto Pulang: {{ $u->nama_lengkap ?? $u->name }}')" style="font-size: 11px; color: #16a34a; display: inline-flex; align-items: center; gap: 3px; margin-top: 3px; text-decoration: none; font-weight: 600;">
                                    <ion-icon name="image-outline"></ion-icon> Lihat Foto
                                </a>
                            @endif
                        </td>
                        <td style="font-size: 12px; font-weight: 700; color: var(--text);">
                            {{ $durasi }}
                        </td>
                        <td>
                            @if($p->jam_selesai)
                                <span class="app-badge badge-status-aktif">
                                    <ion-icon name="checkmark-circle-outline"></ion-icon> Hadir Lengkap
                                </span>
                            @else
                                <span class="app-badge badge-status-cuti">
                                    <ion-icon name="time-outline"></ion-icon> Berlangsung
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 36px 16px; text-align: center; color: var(--muted);">
                            <ion-icon name="calendar-outline" style="font-size: 36px; opacity: 0.4; margin-bottom: 6px; display: block; margin-inline: auto;"></ion-icon>
                            <div style="font-size: 13px; font-weight: 600;">Tidak ada riwayat presensi magang pada periode filter ini.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($presensis->hasPages())
        <div style="margin: 0 16px 20px;">
            {{ $presensis->links() }}
        </div>
    @endif

    {{-- Photo Preview Modal --}}
    <div id="photoPreviewModal" class="app-modal-backdrop">
        <div class="app-modal-card" style="max-width: 480px; padding: 0; overflow: hidden;">
            <div class="app-modal-header" style="padding: 14px 18px; margin-bottom: 0; border-bottom: 1px solid var(--border);">
                <h4 id="previewModalTitle" class="app-modal-title" style="font-size: 14px;">Foto Presensi</h4>
                <button type="button" onclick="closePreviewModal()" class="app-modal-close">&times;</button>
            </div>
            <div style="padding: 16px; text-align: center; background: #0f172a;">
                <img id="previewModalImg" src="" alt="Foto Presensi" style="max-width: 100%; max-height: 65vh; border-radius: 12px; object-fit: contain; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
            </div>
        </div>
    </div>

    <script>
        function openPreviewModal(imgUrl, title) {
            document.getElementById('previewModalImg').src = imgUrl;
            document.getElementById('previewModalTitle').innerText = title || 'Foto Presensi';
            const modal = document.getElementById('photoPreviewModal');
            modal.style.display = 'flex';
        }

        function closePreviewModal() {
            const modal = document.getElementById('photoPreviewModal');
            modal.style.display = 'none';
        }

        document.getElementById('photoPreviewModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closePreviewModal();
            }
        });
    </script>

@endsection
