@extends('layouts.presensi')

@section('title', 'Riwayat Presensi Magang')

@php
    use Carbon\Carbon;
    $user = auth()->user();
    $displayName = (string) (($user->nama_lengkap ?? $user->name) ?? 'Mahasiswa Magang');
    $initial = strtoupper(substr($displayName, 0, 1));
@endphp

@push('topbar')
    @include('layouts.components.navigasi_atas', [
        'titleName' => 'Riwayat Presensi',
        'subTitle'  => $displayName . ' • Magang',
        'dashRoute' => route('magang.dashboard'),
        'backRoute' => route('magang.dashboard'),
    ])
@endpush

@section('content')

<div class="riwayatPage">

    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">LOG KEHADIRAN PRAKTIK KERJA</div>
                <h1 class="laporanHeaderTitle">Riwayat Presensi Magang</h1>
                <div class="laporanHeaderSub">Periode: {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('F Y') }}</div>
                <p class="laporanHeaderDesc">Catatan kehadiran harian mahasiswa magang/PKL, jam masuk &amp; pulang, dan persentase kehadiran.</p>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('magang.presensi.foto') }}" class="profileBtnPrimary">
                    <ion-icon name="camera-outline"></ion-icon> Presensi Hari Ini
                </a>
            </div>
        </div>
    </div>

    {{-- Statistik & Filter --}}
    <div class="riwayatHeaderCard">
        <!-- STATISTIK RINGKAS -->
        <div  class="riwayatStatsGrid grid-stats-auto">
            <div class="riwayatStatCard">
                <div class="riwayatStatLabel">Total Hadir</div>
                <div class="riwayatStatValue">{{ $hadir }} <span class="text-sm font-semibold text-muted">Hari</span></div>
            </div>
            <div class="riwayatStatCard">
                <div class="riwayatStatLabel">Persentase Kehadiran</div>
                <div class="riwayatStatValue text-success">{{ $persentase }}%</div>
            </div>
        </div>

        <!-- FILTER BULAN & STATUS -->
        <form action="{{ route('magang.riwayat') }}" method="GET" class="riwayatFilterForm">
            <div >
                <input type="month" name="tanggal" value="{{ substr($selectedDate, 0, 7) }}" onchange="this.form.submit()" class="profileInput text-sm">
            </div>
            <div >
                <select name="status" onchange="this.form.submit()" class="profileInput text-sm">
                    <option value="">Semua Status</option>
                    <option value="hadir" {{ $statusFilter === 'hadir' ? 'selected' : '' }}>Hadir Lengkap</option>
                    <option value="proses" {{ $statusFilter === 'proses' ? 'selected' : '' }}>Sedang Berjalan</option>
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
                $pulang = $p->jam_selesai ? substr((string)$p->jam_selesai, 0, 5) : '—';
                
                $statusText = $p->jam_selesai ? 'Hadir Lengkap' : 'Sedang Berjalan';
                $statusClass = $p->jam_selesai ? 'hadir' : 'proses';
                
                $durasi = '—';
                if ($p->jam_mulai && $p->jam_selesai) {
                    try {
                        $dtMulai = Carbon::parse($p->tgl_presensi . ' ' . $p->jam_mulai);
                        $dtSelesai = Carbon::parse($p->tgl_presensi . ' ' . $p->jam_selesai);
                        $diffMin = $dtMulai->diffInMinutes($dtSelesai);
                        $durasi = floor($diffMin / 60) . 'j ' . ($diffMin % 60) . 'm';
                    } catch (\Throwable) {}
                }
            @endphp

            <div class="riwayatCard">
                <div class="riwayatCardHeader">
                    <div >
                        <div class="riwayatDateTitle">{{ $hari }}</div>
                        <div class="riwayatDateSub">Durasi: <strong >{{ $durasi }}</strong></div>
                    </div>
                    <span class="riwayatStatusBadge {{ $statusClass }}">
                        {{ $statusText }}
                    </span>
                </div>

                <div class="riwayatTimeGrid">
                    <div class="riwayatTimeBox">
                        <div class="riwayatTimeLabel">Absen Masuk</div>
                        <div class="flex-items-center gap-2">
                            <span class="riwayatTimeVal text-primary">{{ $masuk }} WIB</span>
                            @if($p->foto_mulai)
                                <img src="{{ $p->foto_mulai_url }}" onclick="openRiwayatModal('{{ $p->foto_mulai_url }}', 'Foto Masuk: {{ $hari }}')" class="riwayatPhotoThumb" alt="Foto Masuk" title="Klik untuk perbesar">
                            @endif
                        </div>
                    </div>

                    <div class="riwayatTimeBox">
                        <div class="riwayatTimeLabel">Absen Pulang</div>
                        <div class="flex-items-center gap-2">
                            <span  class="riwayatTimeVal {{ $p->jam_selesai ? 'text-success' : 'text-muted' }}">{{ $pulang }} {{ $p->jam_selesai ? 'WIB' : '' }}</span>
                            @if($p->foto_selesai)
                                <img src="{{ $p->foto_selesai_url }}" onclick="openRiwayatModal('{{ $p->foto_selesai_url }}', 'Foto Pulang: {{ $hari }}')" class="riwayatPhotoThumb" alt="Foto Pulang" title="Klik untuk perbesar">
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        @empty
            <div class="empty-state-standard">
                <div class="empty-title">Tidak ada catatan presensi pada periode ini.</div>
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

