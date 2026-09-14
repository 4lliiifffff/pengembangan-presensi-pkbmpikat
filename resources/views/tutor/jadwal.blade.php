@extends('layouts.presensi')

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
