@extends('layouts.presensi')

@section('title', 'Riwayat Absensi Siswa')

@php
    use Carbon\Carbon;
    $user = auth()->user();
    $siswa = $user->siswa;
    $displayName = (string) ($siswa->nama_siswa ?? ($siswa->nama_lengkap ?? ($user->nama_lengkap ?? ($user->name ?? 'Siswa PKBM'))));
    $initial = strtoupper(substr($displayName, 0, 1));
@endphp

@push('topbar')
    @include('layouts.components.navigasi_atas', [
        'titleName' => 'Riwayat Absensi',
        'subTitle'  => $displayName . ' • Siswa',
        'dashRoute' => route('siswa.dashboard'),
        'backRoute' => route('siswa.dashboard'),
    ])
@endpush

@section('content')

<div class="riwayatPage">

    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">LOG KEHADIRAN MANDIRI SISWA</div>
                <h1 class="laporanHeaderTitle">Riwayat Presensi Mandiri</h1>
                <div class="laporanHeaderSub">Periode: {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('F Y') }}</div>
                <p class="laporanHeaderDesc">Catatan riwayat kehadiran mandiri Anda di PKBM Pikat, termasuk jam masuk, jam pulang, dan verifikasi foto.</p>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('siswa.presensi.foto') }}" class="profileBtnPrimary">
                    <ion-icon name="camera-outline"></ion-icon> Absen Hari Ini
                </a>
            </div>
        </div>
    </div>

    {{-- Statistik & Filter --}}
    <div class="riwayatHeaderCard">
        <!-- STATISTIK RINGKAS -->
        <div class="riwayatStatsGrid grid-stats-auto">
            <div class="riwayatStatCard">
                <div class="riwayatStatLabel">Total Hadir Mandiri</div>
                <div class="riwayatStatValue">{{ $hadir }} <span class="text-sm font-semibold text-muted">Hari</span></div>
            </div>
        </div>

        <!-- FILTER BULAN & STATUS -->
        <form action="{{ route('siswa.riwayat') }}" method="GET" class="riwayatFilterForm">
            <div>
                <input type="month" name="tanggal" value="{{ substr($selectedDate, 0, 7) }}" onchange="this.form.submit()" class="profileInput text-sm">
            </div>
            <div>
                <select name="status" onchange="this.form.submit()" class="profileInput text-sm">
                    <option value="">Semua Status</option>
                    <option value="hadir" {{ $statusFilter === 'hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="izin" {{ $statusFilter === 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ $statusFilter === 'sakit' ? 'selected' : '' }}>Sakit</option>
                </select>
            </div>
        </form>
    </div>

    <!-- DAFTAR LOG PRESENSI -->
    <div class="riwayatList">
        @forelse($items as $p)
            @php
                $tgl = Carbon::parse($p->tgl_presensi);
                $hari = $tgl->translatedFormat('l, d F Y');
                $masuk = $p->jam_masuk ? substr((string)$p->jam_masuk, 0, 5) : '—';
                $lokasiNama = $p->lokasiPresensi?->nama_lokasi ?? 'PKBM Pikat';
                $statusLabel = $p->status_label ?? ucfirst($p->status ?? 'Hadir');
            @endphp

            <div class="riwayatCard">
                <div class="riwayatCardHeader">
                    <div>
                        <div class="riwayatDateTitle">{{ $hari }}</div>
                        <div class="riwayatDateSub">Lokasi Belajar: <strong>{{ $lokasiNama }}</strong></div>
                    </div>
                    <div>
                        <span class="riwayatStatusBadge hadir">
                            {{ $statusLabel }}
                        </span>
                        @if($p->isTerlambat())
                            <span class="badge bg-warning-light text-warning font-bold text-xs py-1 px-2 rounded-full ml-1" style="font-size: 0.72rem;">
                                Terlambat (+{{ $p->menit_keterlambatan }} mnt)
                            </span>
                        @elseif($p->status_kehadiran === 'lebih_awal')
                            <span class="badge bg-info-light text-info font-bold text-xs py-1 px-2 rounded-full ml-1" style="font-size: 0.72rem;">
                                Lebih Awal
                            </span>
                        @endif
                    </div>
                </div>

                <div class="riwayatTimeGrid">
                    <div class="riwayatTimeBox">
                        <div class="riwayatTimeLabel">Jam Kedatangan (Masuk)</div>
                        <div class="flex-items-center gap-2">
                            <span class="riwayatTimeVal text-primary">{{ $masuk }} WIB</span>
                            @if($p->foto_masuk_url)
                                <img src="{{ $p->foto_masuk_url }}" onclick="openRiwayatModal('{{ $p->foto_masuk_url }}', 'Foto Masuk: {{ $hari }}')" class="riwayatPhotoThumb" alt="Foto Masuk" title="Klik untuk perbesar">
                            @endif
                        </div>
                    </div>

                    @if($p->foto_pulang || $p->jam_pulang)
                        <div class="riwayatTimeBox">
                            <div class="riwayatTimeLabel">Absen Pulang (Arsip)</div>
                            <div class="flex-items-center gap-2">
                                <span class="riwayatTimeVal text-muted">{{ substr((string)$p->jam_pulang, 0, 5) }} WIB</span>
                                @if($p->foto_pulang_url)
                                    <img src="{{ $p->foto_pulang_url }}" onclick="openRiwayatModal('{{ $p->foto_pulang_url }}', 'Foto Pulang: {{ $hari }}')" class="riwayatPhotoThumb" alt="Foto Pulang" title="Klik untuk perbesar">
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        @empty
            <div class="tableEmptyCard">
                <div class="tableEmptyIconWrap">
                    <ion-icon name="calendar-outline" class="tableEmptyIcon"></ion-icon>
                </div>
                <div class="tableEmptyTitle">Belum Ada Catatan Presensi</div>
                <div class="tableEmptyDesc">Tidak ada catatan presensi pada periode filter yang dipilih.</div>
            </div>
        @endforelse
    </div>

    @if($items->hasPages())
        <div class="mt-4">
            {{ $items->links() }}
        </div>
    @endif

</div>

{{-- Modal Preview Foto --}}
<div id="riwayatPhotoModal" class="app-modal-backdrop">
    <div class="app-modal-card p-0 overflow-hidden max-w-md">
        <div class="app-modal-header p-3 mb-0 table-body-row">
            <h4 id="riwayatModalTitle" class="app-modal-title text-md">Foto Presensi</h4>
            <button type="button" onclick="closeRiwayatModal()" class="app-modal-close">
                <ion-icon name="close-circle-outline"></ion-icon>
            </button>
        </div>
        <div class="p-3 text-center bg-dark">
            <img id="riwayatModalImg" src="" alt="Foto" class="rounded-lg object-contain modal-preview-img">
        </div>
    </div>
</div>

<script>
    function openRiwayatModal(url, title) {
        document.getElementById('riwayatModalImg').src = url;
        document.getElementById('riwayatModalTitle').innerText = title || 'Foto Presensi';
        document.getElementById('riwayatPhotoModal').style.display = 'flex';
    }

    function closeRiwayatModal() {
        document.getElementById('riwayatPhotoModal').style.display = 'none';
    }

    document.getElementById('riwayatPhotoModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeRiwayatModal();
    });
</script>

@endsection
