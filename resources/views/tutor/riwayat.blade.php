@extends('layout.presensi')

@section('title', 'Riwayat Kehadiran')

@section('content')
@php
    use Carbon\Carbon;

    $user = auth()->user();
    $displayName = (string) (($user->nama_lengkap ?? $user->name) ?? 'Tutor');
    $initial = strtoupper(substr($displayName, 0, 1));
@endphp

<div class="tutorTop">
    <div class="tutorTopRow">
        <div class="tutorLeft">
            <a href="{{ route('profil.index') }}" style="text-decoration: none;">
                @if(auth()->user()->foto)
                    <img src="{{ str_starts_with(auth()->user()->foto, 'uploads/') ? asset(auth()->user()->foto) : asset('storage/' . auth()->user()->foto) }}" class="tutorAvatar" alt="Avatar" style="object-fit:cover;" />
                @else
                    <div class="tutorAvatar" aria-label="Avatar">{{ $initial }}</div>
                @endif
            </a>
            <div class="tutorMeta">
                <div class="tutorName">{{ $displayName }}</div>
                <div class="tutorSub">Tutor</div>
            </div>
        </div>
        <button class="tutorIconBtn" type="button" aria-label="Tema" id="themeToggleBtn">
            <ion-icon name="moon-outline" style="font-size:20px;" id="themeToggleIcon"></ion-icon>
        </button>
    </div>
</div>

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
        <input type="date" name="tanggal" value="{{ $selectedDate }}" onchange="this.form.submit()" style="flex: 1; padding: 8px; border-radius: 10px; border: 1px solid #e5e7eb; font-size: 12px;">
        <select name="status" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="hadir" {{ $statusFilter === 'hadir' ? 'selected' : '' }}>Hadir</option>
            <option value="proses" {{ $statusFilter === 'proses' ? 'selected' : '' }}>Proses</option>
            <option value="izin" {{ $statusFilter === 'izin' ? 'selected' : '' }}>Izin</option>
        </select>
    </form>
</div>

<!-- LIST -->
<div style="padding: 0 14px 20px;">
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
                <ion-icon name="arrow-forward-outline" style="color: var(--text);"></ion-icon>
            </div>

            <!-- Sesi Selesai -->
            <div class="session-info" style="justify-content: flex-end; text-align: right;">
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
    <div style="text-align:center; margin-top:20px; color:#6b7280;">
        Tidak ada data
    </div>
@endforelse
</div>

<div style="padding: 0 14px 100px;">
    {{ $items->links() }}
</div>

@endsection
