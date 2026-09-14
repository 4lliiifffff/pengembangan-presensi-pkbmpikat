@extends('layout.presensi')

@section('title', 'Agenda')

@section('content')
@php
    $user = auth()->user();
    $displayName = (string) (($user->nama_lengkap ?? $user->name) ?? 'Tutor');
    $initial = strtoupper(substr($displayName, 0, 1));

    use Carbon\Carbon;
    $todayDate = Carbon::today('Asia/Jakarta');
    $selectedDate = $selectedDate ?? Carbon::parse(request('tanggal', $todayDate->toDateString()))->startOfDay();

    // generate 7 hari (calendar horizontal) mulai dari hari ini
    $dates = [];
    for ($i = 0; $i <= 6; $i++) {
        $dates[] = $todayDate->copy()->addDays($i);
    }
@endphp

<style>
body {
    background: var(--bg);
}

/* HEADER */
.tutorTop {
        background: var(--card-alt);
        padding: 12px 14px;
        border-bottom: 1px solid var(--border);
    }
    .tutorTopRow { display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .tutorLeft { display:flex; align-items:center; gap:10px; min-width:0; }
    .tutorAvatar {
        width: 42px; height: 42px; border-radius: 50%;
        background: var(--text); color: var(--card);
        display:grid; place-items:center; font-weight: 900;
        overflow: hidden;
    }
    .tutorMeta { min-width:0; }
    .tutorName { font-size: 13px; font-weight: 900; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .tutorSub { font-size: 11px; color:var(--muted); font-weight: 700; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .tutorIconBtn {
        width: 36px; height: 36px;
        border-radius: 999px;
        border: none;
        background: var(--icon-btn-bg);
        display:grid; place-items:center;
        color:var(--text);
        cursor:pointer;
    }

/* TITLE */
.agendaTitle {
    margin-top: 14px;
    font-size: 18px;
    font-weight: 900;
}

.agendaSub {
    font-size: 12px;
    color: #6b7280;
}

/* CALENDAR */
.calendar {
    display: flex;
    overflow-x: auto;
    gap: 10px;
    margin-top: 12px;
    padding-bottom: 5px;
}

.calItem {
    min-width: 60px;
    text-align: center;
    font-size: 11px;
    font-weight: 700;
    color: #6b7280;
}

.calItem .date {
    margin-top: 6px;
    padding: 8px;
    border-radius: 10px;
}

.calItem.active .date {
    background: #1e3a8a;
    color: #fff;
}

/* CARD */
.agendaCard {
    background: #fff;
    border-radius: 14px;
    padding: 12px;
    margin-top: 12px;
    border-left: 5px solid #2563eb;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.agendaCard.red {
    border-left: 5px solid #ef4444;
}

.label {
    font-size: 10px;
    font-weight: 900;
    color: #2563eb;
    letter-spacing: 1px;
}

.label.red {
    color: #ef4444;
}

.title {
    font-size: 13px;
    font-weight: 800;
    margin-top: 4px;
}

.time {
    font-size: 10px;
    float: right;
    color: #6b7280;
}

.location {
    font-size: 11px;
    color: #6b7280;
    margin-top: 4px;
}
</style>

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

<div class="agendaTitle">Agenda Kegiatan</div>
<div class="agendaSub">{{ $selectedDate->translatedFormat('F Y') }}</div>

<!-- CALENDAR HORIZONTAL -->
<div class="calendar">
    @foreach($dates as $d)
        <a href="{{ route('tutor.jadwal', ['tanggal' => $d->toDateString()]) }}" style="text-decoration:none; color:inherit;">
            <div class="calItem {{ $d->isSameDay($selectedDate) ? 'active' : '' }}">
                <div>{{ strtoupper($d->translatedFormat('D')) }}</div>
                <div class="date">{{ $d->format('d') }}</div>
            </div>
        </a>
    @endforeach
</div>

<!-- LIST AGENDA -->
@forelse($items as $j)
    @php
        $tgl = $selectedDate->translatedFormat('d F Y');
        $judul = $j->judul ?? 'Agenda';
        $deskripsi = $j->deskripsi ?? null;
        $lokasi = $j->lokasi ?? null;
        $isToday = $selectedDate->isToday();
    @endphp

    <div class="agendaCard" style="{{ $isToday ? 'border-left-color:#16a34a;' : '' }}">

        <div>
            <span class="label" style="{{ $isToday ? 'color:#16a34a;' : '' }}">
                {{ $isToday ? 'HARI INI' : 'AGENDA' }}
            </span>
            <span class="time">{{ $tgl }}</span>
        </div>

        <div class="title">{{ $judul }}</div>

        @if($deskripsi)
            <div class="location" style="margin-top:6px;">{{ $deskripsi }}</div>
        @endif

        @if($lokasi)
            <div class="location">
                <strong style="color:#374151;">Lokasi:</strong> {{ $lokasi }}
            </div>
        @endif

    </div>

@empty
    <div style="text-align:center; margin-top:20px; color:#6b7280;">
        Tidak ada agenda
    </div>
@endforelse

@endsection
