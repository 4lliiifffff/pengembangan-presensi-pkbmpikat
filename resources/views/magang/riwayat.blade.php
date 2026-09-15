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
        'titleName' => $displayName,
        'subTitle'  => 'Mahasiswa/Siswa Magang',
    ])
@endpush

@section('content')

<div class="header">
    <div class="title">Riwayat Presensi Magang</div>
    <div class="sub">{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('F Y') }}</div>

    <!-- STAT -->
    <div class="stats">
        <div class="statCard">
            <div class="statIcon">📘</div>
            <div class="statTitle">Total Hadir</div>
            <div class="statValue">{{ $hadir }} Hari</div>
        </div>
        <div class="statCard">
            <div class="statIcon">📗</div>
            <div class="statTitle">Persentase</div>
            <div class="statValue">{{ $persentase }}%</div>
        </div>
    </div>

    <!-- FILTER -->
    <form action="{{ route('magang.riwayat') }}" method="GET" class="filter">
        <input type="month" name="tanggal" value="{{ substr($selectedDate, 0, 7) }}" onchange="this.form.submit()" class="input">
        <select name="status" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="hadir" {{ $statusFilter === 'hadir' ? 'selected' : '' }}>Hadir Lengkap</option>
            <option value="proses" {{ $statusFilter === 'proses' ? 'selected' : '' }}>Sedang Berjalan</option>
        </select>
    </form>
</div>

<!-- LIST -->
<div class="pad-list">
@forelse($items as $p)
    @php
        $tgl = Carbon::parse($p->tgl_presensi);
        $hari = $tgl->translatedFormat('l, d F Y');
        $masuk = $p->jam_mulai ? substr((string)$p->jam_mulai, 0, 5) : '—';
        $pulang = $p->jam_selesai ? substr((string)$p->jam_selesai, 0, 5) : '—';
        
        $statusText = $p->jam_selesai ? 'Hadir' : 'Proses';
        $statusColor = $p->jam_selesai ? '#16a34a' : '#d97706';
        $statusBg = $p->jam_selesai ? 'rgba(22,163,74,0.1)' : 'rgba(217,119,6,0.1)';
        
        $durasi = '—';
        if ($p->jam_mulai && $p->jam_selesai) {
            try {
                $dtMulai = Carbon::parse($p->tgl_presensi . ' ' . $p->jam_mulai);
                $dtSelesai = Carbon::parse($p->tgl_presensi . ' ' . $p->jam_selesai);
                $diffMin = $dtMulai->diffInMinutes($dtSelesai);
                $durasi = floor($diffMin / 60) . ' jam ' . ($diffMin % 60) . ' mnt';
            } catch (\Throwable) {}
        }
    @endphp

    <div class="card item" style="padding: 16px; margin-bottom: 12px; border-radius: 16px; background: var(--bg-card, #fff); border: 1px solid var(--border-color, #e2e8f0);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
            <div>
                <div style="font-size: 14px; font-weight: 700; color: var(--text-primary, #0f172a);">{{ $hari }}</div>
                <div style="font-size: 12px; color: var(--text-secondary, #64748b); margin-top: 2px;">Durasi: <strong>{{ $durasi }}</strong></div>
            </div>
            <span style="font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; background: {{ $statusBg }}; color: {{ $statusColor }};">
                {{ $statusText }}
            </span>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding: 12px; background: var(--bg-surface, #f8fafc); border-radius: 12px;">
            <div>
                <div style="font-size: 11px; color: var(--text-secondary, #64748b); margin-bottom: 4px;">MASUK</div>
                <div style="font-size: 15px; font-weight: 800; color: #0b5ed7;">{{ $masuk }} WIB</div>
                @if ($p->foto_mulai)
                    <div style="margin-top: 6px;">
                        <a href="{{ asset($p->foto_mulai) }}" target="_blank" style="font-size: 11px; color: #0b5ed7; text-decoration: none; display: inline-flex; align-items: center; gap: 3px;">
                            <ion-icon name="image-outline"></ion-icon> Foto Masuk
                        </a>
                    </div>
                @endif
            </div>
            <div>
                <div style="font-size: 11px; color: var(--text-secondary, #64748b); margin-bottom: 4px;">PULANG</div>
                <div style="font-size: 15px; font-weight: 800; color: {{ $p->jam_selesai ? '#16a34a' : '#64748b' }};">{{ $pulang }} WIB</div>
                @if ($p->foto_selesai)
                    <div style="margin-top: 6px;">
                        <a href="{{ asset($p->foto_selesai) }}" target="_blank" style="font-size: 11px; color: #16a34a; text-decoration: none; display: inline-flex; align-items: center; gap: 3px;">
                            <ion-icon name="image-outline"></ion-icon> Foto Pulang
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@empty
    <div style="text-align: center; padding: 40px 20px; color: var(--text-secondary, #64748b);">
        <ion-icon name="file-tray-outline" style="font-size: 40px; opacity: 0.5; margin-bottom: 8px;"></ion-icon>
        <div style="font-size: 14px; font-weight: 600;">Tidak ada catatan presensi pada periode ini.</div>
    </div>
@endforelse

<div style="margin-top: 20px;">
    {{ $items->links() }}
</div>
</div>
@endsection
