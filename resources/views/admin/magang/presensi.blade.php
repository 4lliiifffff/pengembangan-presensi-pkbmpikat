@extends('layouts.admin')

@section('title', 'Monitoring Presensi Magang / PKL')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">LOG AKTIVITAS MAGANG</div>
                <h1 class="laporanHeaderTitle">Monitoring Presensi Magang &amp; PKL</h1>
                <div class="laporanHeaderSub">Rekapitulasi log absensi masuk, pulang, dan jam kerja peserta magang</div>
                <p class="laporanHeaderDesc">Pantau riwayat presensi harian, bukti foto kehadiran, dan unduh laporan resmi.</p>
            </div>
            <div class="laporanHeaderActions header-actions-group">
                <a href="{{ route('admin.magang.exportPdf', request()->all()) }}" class="profileBtnDanger">
                    <ion-icon name="document-text-outline" class="text-lg"></ion-icon> Export PDF
                </a>
                <a href="{{ route('admin.magang.index') }}" class="btnOutline">
                    <ion-icon name="people-outline" class="text-lg"></ion-icon> Data Magang
                </a>
            </div>
        </div>
    </div>

    <!-- Statistik -->
    <div  class="statsRow gap-3 px-4 pb-3 grid-stats-auto">
        <div class="statBox dark p-3 rounded-lg">
            <ion-icon name="calendar-outline" class="icon-xl mb-1"></ion-icon>
            <h2 class="icon-lg mt-1 mb-1">{{ $totalPresensi }}</h2>
            <div class="text-xs letter-spacing-sm opacity-85">TOTAL LOG</div>
        </div>
        <div class="statBox active p-3 rounded-lg">
            <ion-icon name="checkmark-done-circle-outline" class="icon-xl mb-1"></ion-icon>
            <h2 class="icon-lg mt-1 mb-1">{{ $totalHadirLengkap }}</h2>
            <div class="text-xs letter-spacing-sm opacity-85">HADIR LENGKAP</div>
        </div>
        <div  class="statBox p-3 rounded-lg stat-box-amber">
            <ion-icon name="time-outline" class="icon-xl mb-1 text-warning"></ion-icon>
            <h2  class="icon-lg mt-1 mb-1 text-warning">{{ $totalSedangProses }}</h2>
            <div class="text-xs text-warning letter-spacing-sm">SEDANG BERLANGSUNG</div>
        </div>
    </div>

    <!-- Filter Box -->
    <div class="laporanFilterCard p-3 px-4 mb-4">
        <form method="GET" action="{{ route('admin.magang.presensi') }}" class="gap-2 grid-stats-auto items-end">
            <div >
                <label class="form-field-label">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDateStr }}" class="profileInput text-sm">
            </div>
            <div >
                <label class="form-field-label">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDateStr }}" class="profileInput text-sm">
            </div>
            <div >
                <label class="form-field-label">Peserta Magang</label>
                <select name="user_id" class="profileInput text-sm">
                    <option value="">Semua Peserta</option>
                    @foreach($allMagangUsers as $u)
                        <option value="{{ $u->id }}" {{ $magangUserId == $u->id ? 'selected' : '' }}>
                            {{ $u->nama_lengkap ?? $u->name }} ({{ $u->magang?->asal_instansi ?: $u->nik }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div >
                <label class="form-field-label">Status Kehadiran</label>
                <select name="status" class="profileInput text-sm">
                    <option value="">Semua Status</option>
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
        </form>
    </div>

    <!-- Tabel Log Presensi (Desktop) -->
    <div class="table-responsive-desktop px-4">
        <div class="tableContainer rounded-xl border-base mb-4">
            <table class="laporanTable">
                <thead >
                    <tr >
                        <th class="p-3">TANGGAL</th>
                        <th >PESERTA MAGANG</th>
                        <th >ABSEN MASUK</th>
                        <th >ABSEN PULANG</th>
                        <th >DURASI</th>
                        <th >STATUS</th>
                    </tr>
                </thead>
                <tbody >
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
                        <tr >
                            <td class="white-space-nowrap">
                                <div class="font-bold text-md text-dark">{{ $tgl }}</div>
                                <div class="text-xs text-muted">{{ $hari }}</div>
                            </td>
                            <td >
                                <div class="font-bold text-md text-dark">{{ $u->nama_lengkap ?? ($u->name ?? 'Magang') }}</div>
                                <div class="text-xs text-muted">{{ $u->magang?->asal_instansi ?: '-' }} (NIM: {{ $u->magang?->nim_nisn ?: $u->nik }})</div>
                            </td>
                            <td >
                                <div class="font-bold text-md text-primary">{{ $masuk }} WIB</div>
                                @if($p->foto_mulai)
                                    <a href="javascript:void(0)" onclick="openPreviewModal('{{ asset($p->foto_mulai) }}', 'Foto Masuk: {{ $u->nama_lengkap ?? $u->name }}')" class="text-xs text-primary font-semibold text-no-decor d-inline-flex items-center gap-1 mt-1">
                                        <ion-icon name="image-outline"></ion-icon> Lihat Foto
                                    </a>
                                @endif
                            </td>
                            <td >
                                <div class="font-bold text-md {{ $p->jam_selesai ? 'text-success' : 'text-muted' }}">{{ $pulang }} {{ $p->jam_selesai ? 'WIB' : '' }}</div>
                                @if($p->foto_selesai)
                                    <a href="javascript:void(0)" onclick="openPreviewModal('{{ asset($p->foto_selesai) }}', 'Foto Pulang: {{ $u->nama_lengkap ?? $u->name }}')" class="text-xs text-success font-semibold text-no-decor d-inline-flex items-center gap-1 mt-1">
                                        <ion-icon name="image-outline"></ion-icon> Lihat Foto
                                    </a>
                                @endif
                            </td>
                            <td class="text-sm font-bold text-dark">
                                {{ $durasi }}
                            </td>
                            <td >
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
                        <tr >
                            <td colspan="6" class="table-empty-cell text-center text-muted">
                                <ion-icon name="calendar-outline" class="icon-2xl mb-1 d-block opacity-40 mx-auto"></ion-icon>
                                <div class="text-md font-semibold">Tidak ada riwayat presensi magang pada periode filter ini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Mobile Cards List (Layar HP / Tablet) ── --}}
    <div class="mobile-card-list px-4">
        @forelse($presensis as $index => $p)
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
            <div class="data-mobile-card">
                <div class="dmc-header">
                    <div>
                        <h3 class="dmc-title">{{ $u->nama_lengkap ?? ($u->name ?? 'Magang') }}</h3>
                        <div class="dmc-subtitle">{{ $hari }}, {{ $tgl }}</div>
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
                        <div class="dmc-value text-primary">{{ $masuk }} WIB</div>
                        @if($p->foto_mulai)
                            <a href="javascript:void(0)" onclick="openPreviewModal('{{ asset($p->foto_mulai) }}', 'Foto Masuk: {{ $u->nama_lengkap ?? $u->name }}')" class="text-xs text-primary font-semibold text-no-decor d-inline-flex items-center gap-1 mt-1">
                                <ion-icon name="image-outline"></ion-icon> Foto Masuk
                            </a>
                        @endif
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Jam Pulang</div>
                        <div class="dmc-value {{ $p->jam_selesai ? 'text-success' : 'text-muted' }}">
                            {{ $pulang }} {{ $p->jam_selesai ? 'WIB' : '' }}
                        </div>
                        @if($p->foto_selesai)
                            <a href="javascript:void(0)" onclick="openPreviewModal('{{ asset($p->foto_selesai) }}', 'Foto Pulang: {{ $u->nama_lengkap ?? $u->name }}')" class="text-xs text-success font-semibold text-no-decor d-inline-flex items-center gap-1 mt-1">
                                <ion-icon name="image-outline"></ion-icon> Foto Pulang
                            </a>
                        @endif
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Total Durasi</div>
                        <div class="dmc-value font-bold text-dark">{{ $durasi }}</div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Instansi</div>
                        <div class="dmc-value text-xs text-muted">{{ $u->magang?->asal_instansi ?: '-' }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="data-mobile-card table-empty-cell">
                <ion-icon name="calendar-outline" class="icon-2xl mb-1 d-block opacity-40 mx-auto"></ion-icon>
                <div class="font-bold text-md mb-1">Belum Ada Riwayat Presensi</div>
                <div class="text-sm">Tidak ada riwayat presensi magang pada periode filter ini.</div>
            </div>
        @endforelse
    </div>

    @if($presensis->hasPages())
        <div class="px-4 mb-4">
            {{ $presensis->links() }}
        </div>
    @endif

    {{-- Photo Preview Modal --}}
    <div id="photoPreviewModal" class="app-modal-backdrop">
        <div class="app-modal-card p-0 overflow-hidden max-w-md">
            <div class="app-modal-header p-3 mb-0 table-body-row">
                <h4 id="previewModalTitle" class="app-modal-title text-md">Foto Presensi</h4>
                <button type="button" onclick="closePreviewModal()" class="app-modal-close">&times;</button>
            </div>
            <div class="p-4 text-center bg-dark">
                <img id="previewModalImg" src="" alt="Foto Presensi" class="rounded-lg object-contain modal-preview-img">
            </div>
        </div>
    </div>

    <script >
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
