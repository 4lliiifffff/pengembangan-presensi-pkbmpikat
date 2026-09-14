@extends('layouts.admin')

@section('title', 'Agenda — Admin')



@section('content')

@php
    $dayLabels = ['SEN','SEL','RAB','KAM','JUM','SAB'];
@endphp

<!-- HEADER -->
<div style="background: var(--blue); padding:14px 16px 10px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
        <div>
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
        <div style="display:flex;gap:6px;">
            <a href="{{ route('admin.jadwal.exportExcel') }}" class="btnPrimary" style="padding:6px 10px;background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);font-size:11px;font-weight:700;">
                <ion-icon name="download-outline"></ion-icon> Export
            </a>
            <button type="button" onclick="document.getElementById('importJadwalModal').style.display='flex'" class="btnPrimary" style="padding:6px 10px;background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);font-size:11px;font-weight:700;">
                <ion-icon name="cloud-upload-outline"></ion-icon> Import
            </button>
        </div>
    </div>
</div>

{{-- ── Modal Impor Jadwal / Agenda ── --}}
<div id="importJadwalModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:16px;">
    <div style="background:var(--card,#fff);border-radius:18px;max-width:480px;width:100%;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.2);border:1px solid var(--border,#e2e8f0);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="margin:0;font-size:17px;font-weight:800;color:var(--text);">Impor Kalender Jadwal Massal</h3>
            <button type="button" onclick="document.getElementById('importJadwalModal').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);">&times;</button>
        </div>
        <p style="font-size:13px;color:var(--muted);margin-bottom:16px;line-height:1.4;">
            Unggah berkas spreadsheet Excel/CSV untuk menambahkan agenda kegiatan belajar dan kalender akademik secara massal.
        </p>
        <div style="margin-bottom:18px;">
            <a href="{{ route('admin.jadwal.downloadTemplate') }}" class="btnOutline" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;font-size:12px;font-weight:700;">
                <ion-icon name="download-outline"></ion-icon> Download Template Jadwal (.xlsx)
            </a>
        </div>
        <form method="POST" action="{{ route('admin.jadwal.importExcel') }}" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:12px;font-weight:700;margin-bottom:6px;color:var(--text);">Pilih Berkas Spreadsheet (.xlsx / .csv):</label>
                <input type="file" name="file_excel" accept=".xlsx,.xls,.csv" required style="width:100%;padding:10px;border:1px dashed var(--border,#cbd5e1);border-radius:10px;font-size:13px;background:var(--card-alt,#f8fafc);">
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('importJadwalModal').style.display='none'" class="btnOutline" style="padding:9px 14px;">Batal</button>
                <button type="submit" class="btnPrimary" style="padding:9px 18px;background:var(--blue-gradient);">Unggah &amp; Impor Jadwal</button>
            </div>
        </form>
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