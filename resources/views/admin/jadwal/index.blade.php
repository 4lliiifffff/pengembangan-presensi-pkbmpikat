@extends('layout.admin')

@section('title', 'Agenda — Admin')

<style>
/* (SEMUA CSS TETAP SAMA, TIDAK DIUBAH) */
.weekStrip {
    background: #1a3a5c;
    padding: 12px 16px 18px;
}
.weekStripDate {
    font-size: 12px;
    color: #cbd5e1;
    margin-bottom: 12px;
    font-weight: 500;
}
.weekRow {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 4px;
    scrollbar-width: none;
}
.weekRow::-webkit-scrollbar {
    display: none;
}
.dayCell {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    flex: 1;
    min-width: 50px;
    flex: 0 0 auto;

}
.dayLabel {
    font-size: 10px;
    font-weight: 600;
    color: #7a8fa6;
}
.dayNumber {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: #c8d6e5;
}
.dayNumber.selected {
    background: #2563eb;
    color: #fff;
}
.dayDot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #2563eb;
    position: absolute;
    bottom: -8px;
}

/* CARD */
.scheduleList {
    padding: 0 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding-bottom: 110px;
}
.scheduleCard {
    background: #fff;
    border-radius: 14px;
    padding: 16px;
    border-left: 4px solid #2563eb;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.scheduleCardTop {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}
.scheduleActions {
    display: flex;
    gap: 6px;
}
.scheduleActionBtn {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
.scheduleActionBtn.edit {
    background: rgba(37,99,235,0.1);
    color: #2563eb;
}
.scheduleActionBtn.delete {
    background: rgba(239,68,68,0.1);
    color: #ef4444;
}
.scheduleSubject {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 10px;
}
.scheduleMeta {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.scheduleMetaRow {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
}
.scheduleMetaRow ion-icon {
    font-size: 13px;
    color: #94a3b8;
}
.metaText {
    color: var(--muted);
}
.metaHighlight {
    color: #2563eb;
    font-weight: 500;
}
.emptySchedule {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 50px 20px;
    gap: 10px;
    color: #94a3b8;
}
</style>

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