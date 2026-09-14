@extends('layouts.admin')

@section('title', 'Agenda — Admin')



@section('content')

@php
    $dayLabels = ['SEN','SEL','RAB','KAM','JUM','SAB'];
@endphp

<!-- HEADER -->
<div style="background: var(--blue); padding:14px 16px 8px;">
    <div style="font-size:11px;color: #f1f5f9;font-weight:600;">
        AGENDA SEKOLAH
    </div>
    <div style="margin-top:4px;">
        <div style="font-size:15px;font-weight:700;color:#fff;">
            {{ $selectedDate->translatedFormat('l') }}
        </div>
        <div style="font-size:12px;color: #cbd5e1;">
            {{ $selectedDate->translatedFormat('d F Y') }}
        </div>
    </div>
</div>

<!-- WEEK STRIP -->
<div class="weekStrip">
    <div class="weekRow">
        @foreach($monthDays as $i => $day)
            @php
                $isSelected = $day->toDateString() === $selectedDate->toDateString();
                $hasAgenda  = isset($monthCounts[$day->toDateString()]);
                $label      = strtoupper($day->format('D'));
            @endphp
            <a href="{{ route('admin.jadwal.index', ['tanggal' => $day->toDateString()]) }}"
               class="dayCell"
               {{ $isSelected ? 'id=selectedDay' : '' }}>
                <span class="dayLabel">{{ $label }}</span>
                <div class="dayNumber {{ $isSelected ? 'selected' : '' }}">
                    {{ $day->day }}
                    @if($hasAgenda)
                        <span class="dayDot"></span>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectedDay = document.getElementById('selectedDay');
        if (selectedDay) {
            selectedDay.scrollIntoView({ behavior: 'auto', inline: 'center', block: 'nearest' });
        }
    });
</script>

<!-- LIST -->
<div class="scheduleList">
@forelse($jadwals as $jadwal)
    @php
        $judul = $jadwal->judul ?? 'Agenda';
        $deskripsi = $jadwal->deskripsi ?? null;
        $tanggal = \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('d F Y');
        $lokasi = $jadwal->lokasi ?? '-';
    @endphp

    <div class="scheduleCard">

        <div class="scheduleCardTop">
            <div style="font-size:12px;color: var(--muted);">
                {{ $tanggal }}
            </div>

            <div class="scheduleActions">
                <a href="{{ route('admin.jadwal.edit', $jadwal) }}" class="scheduleActionBtn edit">
                    <ion-icon name="create-outline"></ion-icon>
                </a>

                <form method="POST"
                      action="{{ route('admin.jadwal.destroy', $jadwal) }}"
                      onsubmit="return confirm('Yakin hapus agenda ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="scheduleActionBtn delete">
                        <ion-icon name="trash-outline"></ion-icon>
                    </button>
                </form>
            </div>
        </div>

        <div class="scheduleSubject">
            {{ $judul }}
        </div>

        <div class="scheduleMeta">

            @if($deskripsi)
            <div class="scheduleMetaRow">
                <ion-icon name="document-text-outline"></ion-icon>
                <span class="metaText">{{ $deskripsi }}</span>
            </div>
            @endif

            <div class="scheduleMetaRow">
                <ion-icon name="location-outline"></ion-icon>
                <span class="metaHighlight">{{ $lokasi }}</span>
            </div>

        </div>

    </div>

@empty
    <div class="emptySchedule">
        <ion-icon name="calendar-outline"></ion-icon>
        <div>Tidak ada agenda</div>
    </div>
@endforelse
</div>

<!-- FAB -->
<a href="{{ route('admin.jadwal.create') }}" class="fabAdd">
    <ion-icon name="add-outline"></ion-icon>
</a>

@endsection