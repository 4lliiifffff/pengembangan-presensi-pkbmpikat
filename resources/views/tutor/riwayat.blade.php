@extends('layouts.presensi')

@section('title', 'Riwayat Kehadiran')

@section('content')
@php
    use Carbon\Carbon;

    $user = auth()->user();
    $displayName = (string) (($user->nama_lengkap ?? $user->name) ?? 'Tutor');
    $initial = strtoupper(substr($displayName, 0, 1));
@endphp

@include('layouts.components.navigasi_atas', [
    'titleName' => $displayName,
    'subTitle' => 'Tutor'
])

<div class="header">
    <div class="title">Riwayat Kehadiran</div>
    <div class="sub">{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('F Y') }}</div>

    <!-- STAT -->
    <div class="stats">
        <div class="statCard">
            <div class="statIcon">📘</div>
            <div class="statTitle">Hadir</div>
            <div class="statValue">{{ $hadir }} Hari</div>
        </div>
        <div class="statCard">
            <div class="statIcon">📙</div>
            <div class="statTitle">Izin</div>
            <div class="statValue">{{ $izin }} Hari</div>
        </div>
        <div class="statCard">
            <div class="statIcon">📗</div>
            <div class="statTitle">Persentase</div>
            <div class="statValue">{{ $persentase }}%</div>
        </div>
    </div>

    <!-- FILTER -->
    <form action="{{ route('tutor.riwayat') }}" method="GET" class="filter">
        <input type="date" name="tanggal" value="{{ $selectedDate }}" onchange="this.form.submit()" class="input">
        <select name="status" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="hadir" {{ $statusFilter === 'hadir' ? 'selected' : '' }}>Hadir</option>
            <option value="proses" {{ $statusFilter === 'proses' ? 'selected' : '' }}>Proses</option>
            <option value="izin" {{ $statusFilter === 'izin' ? 'selected' : '' }}>Izin</option>
        </select>
    </form>
</div>

<!-- LIST -->
<div class="pad-list">
@forelse($items as $p)
    @php
        $tgl = Carbon::parse($p->tgl_presensi);
        $hari = $tgl->translatedFormat('l, d F Y');
        $masuk = $p->jam_mulai ? substr((string)$p->jam_mulai,0,5) : '—';
        $keluar = $p->jam_selesai ? substr((string)$p->jam_selesai,0,5) : '—';

        $status = strtolower($p->status);
        $statusLabel = ucfirst($status);
        $statusClass = $status;

        // Jika sudah masuk tapi belum pulang
        if ($p->foto_mulai && !$p->foto_selesai) {
            $statusLabel = 'Proses';
            $statusClass = 'proses';
        }
    @endphp

    <div class="card">
        <div class="cardHeader">
            <div class="date">{{ $hari }}</div>
            <div class="status {{ $statusClass }}">
                {{ $statusLabel }}
            </div>
        </div>

        <div class="row">
            <!-- Sesi Mulai -->
            <div class="session-info">
                @if($p->foto_mulai)
                    <img src="{{ asset($p->foto_mulai) }}" class="avatar" alt="Mulai">
                @else
                    <div class="avatar" style="border: 1px solid var(--border); background: var(--card); color: var(--text);"></div>
                @endif
                <div class="timeBox">
                    <div class="timeLabel">Mulai</div>
                    <div class="timeValue">{{ $masuk }}</div>
                </div>
            </div>

            <div class="separator">
                <ion-icon name="arrow-forward-outline"></ion-icon>
            </div>

            <!-- Sesi Selesai -->
            <div class="session-info session-info--end">
                <div class="timeBox">
                    <div class="timeLabel">Selesai</div>
                    <div class="timeValue">{{ $keluar }}</div>
                </div>
                @if($p->foto_selesai)
                    <img src="{{ asset($p->foto_selesai) }}" class="avatar" alt="Selesai">
                @else
                    <div class="avatar" style="border: 1px solid var(--border); background: var(--card); color: var(--text);"></div>
                @endif
            </div>
        </div>
    </div>

@empty
    <div class="emptyState">
        Tidak ada data
    </div>
@endforelse
</div>

<div class="pad-pagination">
    {{ $items->links() }}
</div>

@endsection
