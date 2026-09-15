@extends('layouts.admin')

@section('title', 'Agenda — Admin')



@section('content')

@php
    $dayLabels = ['SEN','SEL','RAB','KAM','JUM','SAB'];
@endphp

<!-- HEADER -->
<div class="pageHeaderRow" style="padding: 16px 16px 8px; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between;">
    <div>
        <h2 style="margin: 0; font-size: 18px; font-weight: 800; color: var(--text);">Agenda &amp; Jadwal Sekolah</h2>
        <p style="margin: 2px 0 0; font-size: 12px; color: var(--muted);">{{ $selectedDate->translatedFormat('l, d F Y') }}</p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('admin.jadwal.exportExcel') }}" class="profileBtnOutline" style="padding: 0 12px; height: 38px; font-size: 12px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <ion-icon name="download-outline" style="font-size: 15px;"></ion-icon> Export
        </a>
        <button type="button" onclick="document.getElementById('importJadwalModal').style.display='flex'" class="profileBtnPrimary" style="padding: 0 12px; height: 38px; font-size: 12px; border-radius: 10px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
            <ion-icon name="cloud-upload-outline" style="font-size: 15px;"></ion-icon> Import
        </button>
    </div>
</div>

{{-- ── Modal Impor Jadwal / Agenda ── --}}
<div id="importJadwalModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);z-index:9999;align-items:center;justify-content:center;padding:16px;">
    <div style="background:var(--card,#fff);border-radius:20px;max-width:480px;width:100%;padding:20px;box-shadow:0 20px 40px rgba(0,0,0,0.25);border:1px solid var(--border,#e2e8f0);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--border);">
            <h3 style="margin:0;font-size:16px;font-weight:800;color:var(--text);">Impor Kalender Jadwal Massal</h3>
            <button type="button" onclick="document.getElementById('importJadwalModal').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);display:flex;align-items:center;">&times;</button>
        </div>
        <p style="font-size:12.5px;color:var(--muted);margin-bottom:14px;line-height:1.5;">
            Unggah berkas spreadsheet Excel/CSV untuk menambahkan agenda kegiatan belajar dan kalender akademik secara massal.
        </p>
        <div style="margin-bottom:16px;">
            <a href="{{ route('admin.jadwal.downloadTemplate') }}" class="profileBtnOutline" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;font-size:12px;font-weight:700;text-decoration:none;border-radius:10px;">
                <ion-icon name="download-outline"></ion-icon> Download Template (.xlsx)
            </a>
        </div>
        <form method="POST" action="{{ route('admin.jadwal.importExcel') }}" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:11.5px;font-weight:800;text-transform:uppercase;margin-bottom:6px;color:var(--muted);">Pilih Berkas Jadwal:</label>
                <div class="fileUploadBox">
                    <input type="file" name="file_excel" id="jadwalFileInput" accept=".xlsx,.xls,.csv" required onchange="handleFileSelected(this, 'jadwalFileFeedback')">
                    <div class="fileUploadIcon">
                        <ion-icon name="cloud-upload-outline"></ion-icon>
                    </div>
                    <div class="fileUploadText">Pilih atau seret berkas ke sini</div>
                    <div class="fileUploadSubtext">
                        <span class="fileUploadInfoPill">Format: .XLSX, .CSV</span>
                        <span class="fileUploadInfoPill">Maks: 5 MB</span>
                    </div>
                </div>
                <div id="jadwalFileFeedback" class="fileUploadFeedback"></div>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('importJadwalModal').style.display='none'" class="profileBtnDanger" style="height:38px;padding:0 14px;font-size:12px;border-radius:10px;width:auto;">Batal</button>
                <button type="submit" class="profileBtnPrimary" style="height:38px;padding:0 16px;font-size:12px;border-radius:10px;width:auto;">Unggah &amp; Impor</button>
            </div>
        </form>
    </div>
</div>

<script>
    function handleFileSelected(input, feedbackId) {
        const feedback = document.getElementById(feedbackId);
        if (!feedback) return;
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const sizeKb = Math.round(file.size / 1024);
            feedback.innerHTML = '<ion-icon name="document-text-outline" style="font-size:16px;"></ion-icon> <span>' + file.name + ' (' + sizeKb + ' KB)</span>';
            feedback.style.display = 'flex';
        } else {
            feedback.style.display = 'none';
        }
    }
</script>

@php
    $prevMonthDate = $selectedDate->copy()->subMonth()->toDateString();
    $nextMonthDate = $selectedDate->copy()->addMonth()->toDateString();

    $firstDayOfMonth = $selectedDate->copy()->startOfMonth();
    $startDayOfWeek = $firstDayOfMonth->dayOfWeekIso; // 1 (Mon) to 7 (Sun)
    $emptyCellsCount = $startDayOfWeek - 1;

    $dayNames = [
        ['code' => 'SEN', 'sunday' => false],
        ['code' => 'SEL', 'sunday' => false],
        ['code' => 'RAB', 'sunday' => false],
        ['code' => 'KAM', 'sunday' => false],
        ['code' => 'JUM', 'sunday' => false],
        ['code' => 'SAB', 'sunday' => false],
        ['code' => 'MIN', 'sunday' => true],
    ];

    $hasAgendasToday = $jadwals->count() > 0;
    $isToday = $selectedDate->isToday();
@endphp

<div style="padding: 0 16px 96px; max-width: 900px; margin: 0 auto;">

    {{-- ── 1. KALENDER BULANAN (MONTH GRID VIEW) ── --}}
    <div class="agendaHeaderCard">
        <!-- Header Navigasi Bulan -->
        <div class="agendaMonthNav">
            <a href="{{ route('admin.jadwal.index', ['tanggal' => $prevMonthDate]) }}" class="agendaNavBtn" title="Bulan Sebelumnya">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
            <div style="text-align: center;">
                <h3 class="agendaMonthTitle">{{ $selectedDate->translatedFormat('F Y') }}</h3>
                <span style="font-size: 11px; font-weight: 700; color: var(--muted);">Kalender Akademik &amp; Kegiatan</span>
            </div>
            <a href="{{ route('admin.jadwal.index', ['tanggal' => $nextMonthDate]) }}" class="agendaNavBtn" title="Bulan Berikutnya">
                <ion-icon name="chevron-forward-outline"></ion-icon>
            </a>
        </div>

        <!-- 7 Kolom Header Hari -->
        <div class="agendaMonthGrid" style="margin-bottom: 4px;">
            @foreach($dayNames as $dName)
                <div class="agendaDayNameHeader {{ $dName['sunday'] ? 'sunday' : '' }}">
                    {{ $dName['code'] }}
                </div>
            @endforeach
        </div>

        <!-- Grid Sel Tanggal Bulanan -->
        <div class="agendaMonthGrid">
            {{-- Sel kosong sebelum awal bulan --}}
            @for($i = 0; $i < $emptyCellsCount; $i++)
                <div class="agendaDayCell empty"></div>
            @endfor

            {{-- Sel tanggal dalam bulan --}}
            @foreach($monthDays as $d)
                @php
                    $dStr = $d->toDateString();
                    $isActive = $d->isSameDay($selectedDate);
                    $isCellToday = $d->isToday();
                    $agendaCount = $monthCounts[$dStr] ?? 0;
                @endphp
                <a href="{{ route('admin.jadwal.index', ['tanggal' => $dStr]) }}"
                   class="agendaDayCell {{ $isActive ? 'active' : '' }} {{ $isCellToday ? 'today' : '' }}"
                   title="{{ $d->translatedFormat('d F Y') }} ({{ $agendaCount }} Agenda)">
                    <span class="agendaDayNum">{{ $d->day }}</span>
                    @if($agendaCount > 0)
                        @if($agendaCount > 1)
                            <span class="agendaEventBadge">{{ $agendaCount }}</span>
                        @else
                            <span class="agendaEventDot"></span>
                        @endif
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    {{-- ── 2. BANNER HIGHLIGHT AGENDA TERPILIH ── --}}
    @if($hasAgendasToday)
        <div class="agendaBannerBox">
            <div class="agendaBannerHeader">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="agendaBannerBadge {{ $isToday ? 'today' : '' }}">
                        {{ $isToday ? 'Kegiatan Hari Ini' : 'Agenda Terjadwal' }}
                    </span>
                    <span style="font-size: 11px; font-weight: 700; color: var(--blue);">
                        {{ $jadwals->count() }} Agenda
                    </span>
                </div>
                <div class="agendaBannerDate">
                    {{ $selectedDate->translatedFormat('l, d F Y') }}
                </div>
            </div>

            <!-- List Card Detail Agenda -->
            <div>
                @foreach($jadwals as $jadwal)
                    @php
                        $judul = $jadwal->judul ?? 'Agenda';
                        $deskripsi = $jadwal->deskripsi ?? null;
                        $lokasi = $jadwal->lokasi ?? '-';
                    @endphp

                    <div class="agendaCardItem {{ $isToday ? 'today' : '' }}">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 6px;">
                            <h4 style="margin: 0; font-size: 15px; font-weight: 800; color: var(--text);">
                                {{ $judul }}
                            </h4>
                            <div style="display: flex; gap: 4px; flex-shrink: 0;">
                                <a href="{{ route('admin.jadwal.edit', $jadwal) }}" class="scheduleActionBtn edit" title="Edit Agenda" style="width: 28px; height: 28px; font-size: 13px;">
                                    <ion-icon name="create-outline"></ion-icon>
                                </a>
                                <form method="POST" action="{{ route('admin.jadwal.destroy', $jadwal) }}" onsubmit="return confirm('Yakin hapus agenda ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="scheduleActionBtn delete" title="Hapus Agenda" style="width: 28px; height: 28px; font-size: 13px;">
                                        <ion-icon name="trash-outline"></ion-icon>
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if($deskripsi)
                            <div style="font-size: 12.5px; color: var(--muted); margin-bottom: 8px; line-height: 1.4;">
                                {{ $deskripsi }}
                            </div>
                        @endif

                        <div class="agendaMetaRow">
                            <ion-icon name="location-outline" style="color: var(--blue);"></ion-icon>
                            <span><strong style="color: var(--text);">Lokasi:</strong> {{ $lokasi }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div style="text-align: center; padding: 36px 16px; background: var(--card); border: 1px solid var(--border); border-radius: 18px; color: var(--muted); margin-bottom: 16px;">
            <div style="font-size: 14px; font-weight: 700; color: var(--text);">Tidak ada agenda kegiatan</div>
            <div style="font-size: 12px; margin-top: 4px;">Pada tanggal {{ $selectedDate->translatedFormat('l, d F Y') }}</div>
        </div>
    @endif

</div>

<!-- FAB Tambah Agenda -->
<a href="{{ route('admin.jadwal.create') }}" class="fabAdd" title="Tambah Agenda Baru">
    <ion-icon name="add-outline"></ion-icon>
</a>

@endsection