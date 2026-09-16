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

    {{-- Header & Statistik (Tanpa Icon Dekoratif) --}}
    <div class="riwayatHeaderCard">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; flex-wrap: wrap;">
            <div>
                <h2 style="margin: 0; font-size: 17px; font-weight: 800; color: var(--text);">Riwayat Presensi</h2>
                <p style="margin: 2px 0 0; font-size: 12px; color: var(--muted);">Periode {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('F Y') }}</p>
            </div>
        </div>

        <!-- STATISTIK RINGKAS -->
        <div class="riwayatStatsGrid">
            <div class="riwayatStatCard">
                <div class="riwayatStatLabel">Total Hadir</div>
                <div class="riwayatStatValue">{{ $hadir }} <span style="font-size: 12px; font-weight: 600; color: var(--muted);">Hari</span></div>
            </div>
            <div class="riwayatStatCard">
                <div class="riwayatStatLabel">Total Izin</div>
                <div class="riwayatStatValue">{{ $izin }} <span style="font-size: 12px; font-weight: 600; color: var(--muted);">Hari</span></div>
            </div>
            <div class="riwayatStatCard">
                <div class="riwayatStatLabel">Persentase</div>
                <div class="riwayatStatValue" style="color: #16a34a;">{{ $persentase }}%</div>
            </div>
        </div>

        <!-- FILTER TANGGAL & STATUS -->
        <form action="{{ route('tutor.riwayat') }}" method="GET" class="riwayatFilterForm">
            <div>
                <input type="date" name="tanggal" value="{{ $selectedDate }}" onchange="this.form.submit()" class="profileInput" style="height: 40px; font-size: 12.5px;">
            </div>
            <div>
                <select name="status" onchange="this.form.submit()" class="profileInput" style="height: 40px; font-size: 12.5px;">
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
                    <div>
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
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="riwayatTimeVal" style="color: #0284c7;">{{ $masuk }} WIB</span>
                            @if($p->foto_mulai)
                                <img src="{{ asset($p->foto_mulai) }}" onclick="openRiwayatModal('{{ asset($p->foto_mulai) }}', 'Foto Masuk: {{ $hari }}')" class="riwayatPhotoThumb" alt="Foto Masuk" title="Klik untuk perbesar">
                            @endif
                        </div>
                    </div>

                    <div class="riwayatTimeBox">
                        <div class="riwayatTimeLabel">Jam Pulang</div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="riwayatTimeVal" style="color: {{ $p->jam_selesai ? '#16a34a' : 'var(--muted)' }};">{{ $keluar }} {{ $p->jam_selesai ? 'WIB' : '' }}</span>
                            @if($p->foto_selesai)
                                <img src="{{ asset($p->foto_selesai) }}" onclick="openRiwayatModal('{{ asset($p->foto_selesai) }}', 'Foto Pulang: {{ $hari }}')" class="riwayatPhotoThumb" alt="Foto Pulang" title="Klik untuk perbesar">
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
        <div style="margin-top: 16px;">
            {{ $items->links() }}
        </div>
    @endif

</div>

{{-- Modal Preview Foto --}}
<div id="riwayatPhotoModal" class="app-modal-backdrop">
    <div class="app-modal-card" style="max-width: 460px; padding: 0; overflow: hidden;">
        <div class="app-modal-header" style="padding: 14px 18px; margin-bottom: 0; border-bottom: 1px solid var(--border);">
            <h4 id="riwayatModalTitle" class="app-modal-title" style="font-size: 14px;">Foto Presensi</h4>
            <button type="button" onclick="closeRiwayatModal()" class="app-modal-close">
                <ion-icon name="close-circle-outline"></ion-icon>
            </button>
        </div>
        <div style="padding: 14px; text-align: center; background: #0f172a;">
            <img id="riwayatModalImg" src="" alt="Foto" style="max-width: 100%; max-height: 65vh; border-radius: 12px; object-fit: contain;">
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

