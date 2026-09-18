@extends('layouts.presensi')

@section('title', 'Riwayat Kehadiran')

@php
    use Carbon\Carbon;
    $user = auth()->user();
    $displayName = (string) (($user->nama_lengkap ?? $user->name) ?? 'Tutor');
    $initial = strtoupper(substr($displayName, 0, 1));
@endphp

@push('topbar')
    @include('layouts.components.navigasi_atas', [
        'titleName' => 'Riwayat Kehadiran',
        'subTitle'  => $displayName . ' • Presensi',
        'dashRoute' => route('tutor.dashboard'),
        'backRoute' => route('tutor.dashboard'),
    ])
@endpush

@section('content')

<div class="riwayatPage">

    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">LOG PRESENSI &amp; SESI MENGAJAR</div>
                <h1 class="laporanHeaderTitle">Riwayat Presensi Tutor</h1>
                <div class="laporanHeaderSub">Periode: {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('F Y') }}</div>
                <p class="laporanHeaderDesc">Catatan riwayat kehadiran KBM, jam masuk/selesai, foto presensi, dan status verifikasi.</p>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('tutor.presensi') }}" class="profileBtnPrimary">
                    <ion-icon name="camera-outline"></ion-icon> Presensi Hari Ini
                </a>
            </div>
        </div>
    </div>

    {{-- Statistik & Filter --}}
    <div class="riwayatHeaderCard">
        <!-- STATISTIK RINGKAS -->
        <div class="riwayatStatsGrid">
            <div class="riwayatStatCard">
                <div class="riwayatStatLabel">Total Hadir</div>
                <div class="riwayatStatValue">{{ $hadir }} <span class="text-sm font-semibold text-muted">Hari</span></div>
            </div>
            <div class="riwayatStatCard">
                <div class="riwayatStatLabel">Total Izin</div>
                <div class="riwayatStatValue">{{ $izin }} <span class="text-sm font-semibold text-muted">Hari</span></div>
            </div>
            <div class="riwayatStatCard">
                <div class="riwayatStatLabel">Persentase</div>
                <div class="riwayatStatValue text-success">{{ $persentase }}%</div>
            </div>
        </div>

        <!-- FILTER TANGGAL & STATUS -->
        <form action="{{ route('tutor.riwayat') }}" method="GET" class="riwayatFilterForm">
            <div >
                <input type="date" name="tanggal" value="{{ $selectedDate }}" onchange="this.form.submit()" class="profileInput text-sm">
            </div>
            <div >
                <select name="status" onchange="this.form.submit()" class="profileInput text-sm">
                    <option value="">Semua Status</option>
                    <option value="hadir" {{ $statusFilter === 'hadir' ? 'selected' : '' }}>Hadir Lengkap</option>
                    <option value="proses" {{ $statusFilter === 'proses' ? 'selected' : '' }}>Sedang Berjalan</option>
                    <option value="izin" {{ $statusFilter === 'izin' ? 'selected' : '' }}>Izin / Sakit</option>
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
                $masuk = $p->jam_mulai ? substr((string)$p->jam_mulai, 0, 5) : '—';
                $keluar = $p->jam_selesai ? substr((string)$p->jam_selesai, 0, 5) : '—';

                $status = strtolower($p->status ?? 'hadir');
                $statusLabel = 'Hadir';
                $statusClass = 'hadir';

                if ($p->foto_mulai && !$p->foto_selesai && $status !== 'izin') {
                    $statusLabel = 'Sedang Berjalan';
                    $statusClass = 'proses';
                } elseif ($status === 'izin' || $status === 'sakit') {
                    $statusLabel = 'Izin';
                    $statusClass = 'izin';
                }
            @endphp

            <div class="riwayatCard">
                <div class="riwayatCardHeader">
                    <div >
                        <div class="riwayatDateTitle">{{ $hari }}</div>
                        <div class="riwayatDateSub">Sesi Mengajar • {{ $p->siswa?->nama_siswa ?? 'Siswa' }}</div>
                    </div>
                    <span class="riwayatStatusBadge {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="riwayatTimeGrid">
                    <div class="riwayatTimeBox">
                        <div class="riwayatTimeLabel">Jam Masuk</div>
                        <div class="flex-items-center gap-2">
                            <span class="riwayatTimeVal text-primary">{{ $masuk }} WIB</span>
                            @if($p->foto_mulai)
                                <img src="{{ $p->foto_mulai_url }}" onclick="openRiwayatModal('{{ $p->foto_mulai_url }}', 'Foto Masuk: {{ $hari }}')" class="riwayatPhotoThumb" alt="Foto Masuk" title="Klik untuk perbesar">
                            @endif
                        </div>
                    </div>

                    <div class="riwayatTimeBox">
                        <div class="riwayatTimeLabel">Jam Pulang</div>
                        <div class="flex-items-center gap-2">
                            <span  class="riwayatTimeVal {{ $p->jam_selesai ? 'text-success' : 'text-muted' }}">{{ $keluar }} {{ $p->jam_selesai ? 'WIB' : '' }}</span>
                            @if($p->foto_selesai)
                                <img src="{{ $p->foto_selesai_url }}" onclick="openRiwayatModal('{{ $p->foto_selesai_url }}', 'Foto Pulang: {{ $hari }}')" class="riwayatPhotoThumb" alt="Foto Pulang" title="Klik untuk perbesar">
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        @empty
            <div class="empty-state-standard">
                <div class="empty-title">Tidak ada catatan presensi pada filter ini.</div>
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

<script >
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

