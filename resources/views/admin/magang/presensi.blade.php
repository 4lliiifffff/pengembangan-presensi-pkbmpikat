@extends('layouts.admin')

@section('title', 'Monitoring Presensi Magang & PKL')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">LOG AKTIVITAS MAGANG</div>
                <h1 class="laporanHeaderTitle">Monitoring Presensi Magang &amp; PKL</h1>
                <div class="laporanHeaderSub">Rekapitulasi log absensi masuk, pulang, dan jam kerja peserta magang</div>
                <p class="laporanHeaderDesc">Pantau riwayat presensi harian, verifikasi radius lokasi GPS &amp; bukti foto selfie, serta unduh rekap PDF resmi.</p>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.magang.exportPdf', request()->all()) }}" class="profileBtnPrimary btn-action-danger">
                    <ion-icon name="document-text-outline"></ion-icon> Export PDF
                </a>
                <a href="{{ route('admin.magang.index') }}" class="btnOutline">
                    <ion-icon name="people-outline"></ion-icon> Data Magang
                </a>
            </div>
        </div>
    </div>

    {{-- ── Quick Stats Grid ── --}}
    <div class="account-stats-grid">
        <div class="account-stat-card">
            <div class="account-stat-icon blue">
                <ion-icon name="calendar-outline"></ion-icon>
            </div>
            <div class="account-stat-content">
                <div class="account-stat-label">Total Log Presensi</div>
                <div class="account-stat-value">{{ $totalPresensi }}</div>
                <div class="account-stat-sub">Sesi Presensi Tercatat</div>
            </div>
        </div>

        <div class="account-stat-card">
            <div class="account-stat-icon emerald">
                <ion-icon name="checkmark-done-circle-outline"></ion-icon>
            </div>
            <div class="account-stat-content">
                <div class="account-stat-label">Hadir Lengkap</div>
                <div class="account-stat-value">{{ $totalHadirLengkap }}</div>
                <div class="account-stat-sub">Absen Masuk &amp; Pulang Selesai</div>
            </div>
        </div>

        <div class="account-stat-card">
            <div class="account-stat-icon orange">
                <ion-icon name="time-outline"></ion-icon>
            </div>
            <div class="account-stat-content">
                <div class="account-stat-label">Sedang Berlangsung</div>
                <div class="account-stat-value">{{ $totalSedangProses }}</div>
                <div class="account-stat-sub">Belum Absen Pulang</div>
            </div>
        </div>
    </div>

    {{-- ── Filter Card ── --}}
    <div class="laporanFilterCard">
        <form method="GET" action="{{ route('admin.magang.presensi') }}">
            <div class="laporanFilterGrid">
                <div class="filterField">
                    <label class="filterFieldLabel">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDateStr }}" class="profileInput text-md">
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDateStr }}" class="profileInput text-md">
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Peserta Magang</label>
                    <select name="user_id" class="filterSelect">
                        <option value="">Semua Peserta Magang</option>
                        @foreach($allMagangUsers as $u)
                            <option value="{{ $u->id }}" {{ $magangUserId == $u->id ? 'selected' : '' }}>
                                {{ $u->nama_lengkap ?? $u->name }} ({{ $u->magang?->asal_instansi ?: $u->nik }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Status Kehadiran</label>
                    <select name="status" class="filterSelect">
                        <option value="">Semua Status Log</option>
                        <option value="hadir" {{ $statusFilter === 'hadir' ? 'selected' : '' }}>Hadir Lengkap</option>
                        <option value="proses" {{ $statusFilter === 'proses' ? 'selected' : '' }}>Sedang Berlangsung</option>
                    </select>
                </div>

                <div class="filter-actions-full">
                    <button type="submit" class="btn-filter-primary">
                        <ion-icon name="filter-outline"></ion-icon> Terapkan Filter
                    </button>
                    <a href="{{ route('admin.magang.presensi') }}" class="btn-filter-reset">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Section Title & Filter Chips ── --}}
    <div class="sectionRow">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h2 class="m-0 font-bold">Log Presensi Harian</h2>
            <span class="app-badge badge-role-magang">
                Periode: {{ \Carbon\Carbon::parse($startDateStr)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($endDateStr)->translatedFormat('d M Y') }}
            </span>
            @if(!empty($statusFilter))
                <span class="app-badge {{ $statusFilter === 'hadir' ? 'badge-status-aktif' : 'badge-status-cuti' }}">
                    Status: {{ $statusFilter === 'hadir' ? 'Hadir Lengkap' : 'Sedang Berlangsung' }}
                </span>
            @endif
        </div>
        <span class="badgeCount">{{ $presensis->total() }} Log Ditemukan</span>
    </div>

    {{-- ── Data Table Container (Desktop) ── --}}
    <div class="table-responsive-desktop">
        <div class="tableContainer m-0 mb-4 rounded-xl border-base">
            <table class="laporanTable">
                <thead>
                    <tr>
                        <th class="table-col-num">NO</th>
                        <th>TANGGAL &amp; HARI</th>
                        <th>PESERTA MAGANG</th>
                        <th>ABSEN MASUK</th>
                        <th>ABSEN PULANG</th>
                        <th>DURASI KERJA</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($presensis as $index => $p)
                        @php
                            $u = $p->user;
                            $tgl = \Carbon\Carbon::parse($p->tgl_presensi)->translatedFormat('d M Y');
                            $hari = \Carbon\Carbon::parse($p->tgl_presensi)->translatedFormat('l');
                            $masuk = $p->jam_mulai ? substr((string)$p->jam_mulai, 0, 5) : '—';
                            $pulang = $p->jam_selesai ? substr((string)$p->jam_selesai, 0, 5) : '—';
                            $displayName = $u->nama_lengkap ?? ($u->name ?? 'Magang');
                            $avatarUrl = $u->foto ? (str_starts_with($u->foto, 'uploads/') ? asset($u->foto) : asset('storage/' . $u->foto)) : null;
                            
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
                            <td class="table-col-num">
                                {{ $presensis->firstItem() + $index }}
                            </td>
                            <td>
                                <div class="font-bold text-sm text-dark">{{ $tgl }}</div>
                                <div class="text-xs text-muted">{{ $hari }}</div>
                            </td>
                            <td>
                                <div class="table-user-cell">
                                    @if($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="{{ $displayName }}" class="table-user-avatar">
                                    @else
                                        <div class="table-user-avatar-placeholder">
                                            {{ strtoupper(substr($displayName, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="table-user-info">
                                        <div class="table-user-name">{{ $displayName }}</div>
                                        <div class="text-xs text-muted">{{ $u->magang?->asal_instansi ?: '-' }}</div>
                                        @if($p->lokasiPresensi)
                                            <div class="text-xs text-primary font-bold mt-1 d-flex items-center gap-1">
                                                <ion-icon name="location-sharp"></ion-icon> {{ $p->lokasiPresensi->nama_lokasi }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="font-bold text-sm text-primary">{{ $masuk }} WIB</div>
                                @if($p->foto_mulai)
                                    <a href="javascript:void(0)" onclick="openPreviewModal('{{ $p->foto_mulai_url }}', 'Foto Masuk: {{ $displayName }}')" class="table-phone-link mt-1">
                                        <ion-icon name="image-outline"></ion-icon>
                                        <span>Lihat Foto</span>
                                    </a>
                                @endif
                            </td>
                            <td>
                                <div class="font-bold text-sm {{ $p->jam_selesai ? 'text-success' : 'text-muted' }}">
                                    {{ $pulang }} {{ $p->jam_selesai ? 'WIB' : '' }}
                                </div>
                                @if($p->foto_selesai)
                                    <a href="javascript:void(0)" onclick="openPreviewModal('{{ $p->foto_selesai_url }}', 'Foto Pulang: {{ $displayName }}')" class="table-phone-link text-success mt-1">
                                        <ion-icon name="image-outline"></ion-icon>
                                        <span>Lihat Foto</span>
                                    </a>
                                @endif
                            </td>
                            <td>
                                <div class="font-bold text-sm text-dark">{{ $durasi }}</div>
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
                            <td colspan="7" class="table-empty-cell">
                                <ion-icon name="calendar-outline" class="table-empty-icon"></ion-icon>
                                <div class="table-empty-title">Belum Ada Riwayat Presensi</div>
                                <div class="table-empty-desc">Tidak ada riwayat presensi magang pada periode filter ini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Mobile Cards List (Layar HP / Tablet) ── --}}
    <div class="mobile-card-list">
        @forelse($presensis as $p)
            @php
                $u = $p->user;
                $tgl = \Carbon\Carbon::parse($p->tgl_presensi)->translatedFormat('d M Y');
                $hari = \Carbon\Carbon::parse($p->tgl_presensi)->translatedFormat('l');
                $masuk = $p->jam_mulai ? substr((string)$p->jam_mulai, 0, 5) : '—';
                $pulang = $p->jam_selesai ? substr((string)$p->jam_selesai, 0, 5) : '—';
                $displayName = $u->nama_lengkap ?? ($u->name ?? 'Magang');
                $avatarUrl = $u->foto ? (str_starts_with($u->foto, 'uploads/') ? asset($u->foto) : asset('storage/' . $u->foto)) : null;
                
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
            <div class="data-mobile-card">
                <div class="dmc-header">
                    <div class="dmc-user-info">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="{{ $displayName }}" class="dmc-avatar">
                        @else
                            <div class="dmc-avatar-placeholder">
                                {{ strtoupper(substr($displayName, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="dmc-title">{{ $displayName }}</h3>
                            <div class="dmc-subtitle">{{ $hari }}, {{ $tgl }}</div>
                            @if($p->lokasiPresensi)
                                <div class="text-xs text-primary font-bold mt-1 d-flex items-center gap-1">
                                    <ion-icon name="location-sharp"></ion-icon> {{ $p->lokasiPresensi->nama_lokasi }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div>
                        @if($p->jam_selesai)
                            <span class="app-badge badge-status-aktif">Hadir Lengkap</span>
                        @else
                            <span class="app-badge badge-status-cuti">Berlangsung</span>
                        @endif
                    </div>
                </div>

                <div class="dmc-grid">
                    <div class="dmc-field">
                        <div class="dmc-label">Jam Masuk</div>
                        <div class="dmc-value text-primary font-bold">{{ $masuk }} WIB</div>
                        @if($p->foto_mulai)
                            <a href="javascript:void(0)" onclick="openPreviewModal('{{ $p->foto_mulai_url }}', 'Foto Masuk: {{ $displayName }}')" class="table-phone-link mt-1">
                                <ion-icon name="image-outline"></ion-icon>
                                <span>Foto Masuk</span>
                            </a>
                        @endif
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Jam Pulang</div>
                        <div class="dmc-value font-bold {{ $p->jam_selesai ? 'text-success' : 'text-muted' }}">
                            {{ $pulang }} {{ $p->jam_selesai ? 'WIB' : '' }}
                        </div>
                        @if($p->foto_selesai)
                            <a href="javascript:void(0)" onclick="openPreviewModal('{{ $p->foto_selesai_url }}', 'Foto Pulang: {{ $displayName }}')" class="table-phone-link text-success mt-1">
                                <ion-icon name="image-outline"></ion-icon>
                                <span>Foto Pulang</span>
                            </a>
                        @endif
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Total Durasi</div>
                        <div class="dmc-value font-bold text-dark">{{ $durasi }}</div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Asal Instansi</div>
                        <div class="dmc-value text-xs text-muted">{{ $u->magang?->asal_instansi ?: '-' }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="data-mobile-card table-empty-cell">
                <ion-icon name="calendar-outline" class="table-empty-icon"></ion-icon>
                <div class="font-bold text-md mb-1">Belum Ada Riwayat Presensi</div>
                <div class="text-sm">Tidak ada riwayat presensi magang pada periode filter ini.</div>
            </div>
        @endforelse
    </div>

    @if(method_exists($presensis, 'links'))
        <div class="paginatePad py-4">
            {{ $presensis->links() }}
        </div>
    @endif

    {{-- Photo Preview Modal --}}
    <div id="photoPreviewModal" class="app-modal-backdrop">
        <div class="app-modal-card p-0 overflow-hidden max-w-md">
            <div class="app-modal-header p-3 mb-0">
                <h4 id="previewModalTitle" class="app-modal-title text-md">Foto Presensi</h4>
                <button type="button" onclick="closePreviewModal()" class="app-modal-close">&times;</button>
            </div>
            <div class="p-4 text-center bg-dark">
                <img id="previewModalImg" src="" alt="Foto Presensi" class="rounded-lg object-contain modal-preview-img">
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
</div>

@endsection

