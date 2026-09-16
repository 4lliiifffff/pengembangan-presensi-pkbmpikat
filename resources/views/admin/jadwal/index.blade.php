@extends('layouts.admin')

@section('title', 'Agenda — Admin')



@section('content')

@php
    $dayLabels = ['SEN','SEL','RAB','KAM','JUM','SAB'];
@endphp

<!-- HEADER -->
<div  class="pageHeaderRow header-actions-group justify-between px-4 pt-4 pb-2">
    <div >
        <h2 class="m-0 text-xl font-extrabold text-dark">Agenda &amp; Jadwal Sekolah</h2>
        <p class="mt-1 text-sm text-muted">{{ $selectedDate->translatedFormat('l, d F Y') }}</p>
    </div>
    <div class="header-actions-group">
        <a href="{{ route('admin.jadwal.exportExcel') }}" class="btnOutline btn-action-pill">
            <ion-icon name="download-outline" class="text-lg"></ion-icon> Export
        </a>
        <button type="button" onclick="document.getElementById('importJadwalModal').style.display='flex'" class="profileBtnPrimary text-sm rounded-md w-auto d-inline-flex items-center gap-1 cursor-pointer px-3">
            <ion-icon name="cloud-upload-outline" class="text-lg"></ion-icon> Import
        </button>
    </div>
</div>

{{-- ── Modal Impor Jadwal / Agenda ── --}}
<div id="importJadwalModal" class="app-modal-backdrop">
    <div class="app-modal-card">
        <div class="app-modal-header">
            <h3 class="app-modal-title">Impor Kalender Jadwal Massal</h3>
            <button type="button" onclick="document.getElementById('importJadwalModal').style.display='none'" class="app-modal-close">&times;</button>
        </div>
        <p class="app-modal-desc">
            Unggah berkas spreadsheet Excel/CSV untuk menambahkan agenda kegiatan belajar dan kalender akademik secara massal.
        </p>
        <div class="mb-4">
            <a href="{{ route('admin.jadwal.downloadTemplate') }}" class="btnOutline">
                <ion-icon name="download-outline"></ion-icon> Download Template (.xlsx)
            </a>
        </div>
        <form method="POST" action="{{ route('admin.jadwal.importExcel') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-field-wrapper">
                <label class="form-field-label">Pilih Berkas Jadwal:</label>
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
            <div class="app-modal-footer">
                <button type="button" onclick="document.getElementById('importJadwalModal').style.display='none'" class="profileBtnDanger px-3 text-sm rounded-md w-auto">Batal</button>
                <button type="submit" class="profileBtnPrimary px-4 text-sm rounded-md w-auto">Unggah &amp; Impor</button>
            </div>
        </form>
    </div>
</div>

<script >
    function handleFileSelected(input, feedbackId) {
        const feedback = document.getElementById(feedbackId);
        if (!feedback) return;
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const sizeKb = Math.round(file.size / 1024);
            feedback.innerHTML = '<ion-icon name="document-text-outline" class="icon-sm"></ion-icon> <span >' + file.name + ' (' + sizeKb + ' KB)</span>';
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
        ['code' => 'SAB', 'sunday' => true],
        ['code' => 'MIN', 'sunday' => true],
    ];

    $hasAgendasToday = $jadwals->count() > 0;
    $isToday = $selectedDate->isToday();
@endphp

<div  class="max-w-2xl px-4 mx-auto pb-6">

    {{-- ── 1. KALENDER BULANAN (MONTH GRID VIEW) ── --}}
    <div class="agendaHeaderCard">
        <!-- Header Navigasi Bulan -->
        <div class="agendaMonthNav">
            <a href="{{ route('admin.jadwal.index', ['tanggal' => $prevMonthDate]) }}" class="agendaNavBtn" title="Bulan Sebelumnya">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
            <div class="text-center">
                <h3 class="agendaMonthTitle">{{ $selectedDate->translatedFormat('F Y') }}</h3>
                <span class="text-xs font-bold text-muted">Kalender Akademik &amp; Kegiatan</span>
            </div>
            <a href="{{ route('admin.jadwal.index', ['tanggal' => $nextMonthDate]) }}" class="agendaNavBtn" title="Bulan Berikutnya">
                <ion-icon name="chevron-forward-outline"></ion-icon>
            </a>
        </div>

        <!-- 7 Kolom Header Hari -->
        <div class="agendaMonthGrid mb-1">
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
                <div class="flex-items-center gap-2">
                    <span class="agendaBannerBadge {{ $isToday ? 'today' : '' }}">
                        {{ $isToday ? 'Kegiatan Hari Ini' : 'Agenda Terjadwal' }}
                    </span>
                    <span class="text-xs font-bold text-primary">
                        {{ $jadwals->count() }} Agenda
                    </span>
                </div>
                <div class="agendaBannerDate">
                    {{ $selectedDate->translatedFormat('l, d F Y') }}
                </div>
            </div>

            <!-- List Card Detail Agenda -->
            <div >
                @foreach($jadwals as $jadwal)
                    @php
                        $judul = $jadwal->judul ?? 'Agenda';
                        $deskripsi = $jadwal->deskripsi ?? null;
                        $lokasi = $jadwal->lokasi ?? '-';
                    @endphp

                    <div class="agendaCardItem {{ $isToday ? 'today' : '' }}">
                        <div class="d-flex justify-between items-start gap-2 mb-1">
                            <h4 class="m-0 text-lg font-extrabold text-dark">
                                {{ $judul }}
                            </h4>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <a href="{{ route('admin.jadwal.edit', $jadwal) }}" title="Edit Agenda" class="scheduleActionBtn edit avatar-icon-28">
                                    <ion-icon name="create-outline"></ion-icon>
                                </a>
                                <form method="POST" action="{{ route('admin.jadwal.destroy', $jadwal) }}" data-confirm="Apakah Anda yakin ingin menghapus agenda ini?" data-confirm-title="Hapus Agenda" data-confirm-type="danger" data-confirm-btn="Ya, Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus Agenda" class="scheduleActionBtn delete avatar-icon-28">
                                        <ion-icon name="trash-outline"></ion-icon>
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if($deskripsi)
                            <div class="text-sm text-muted mb-2">
                                {{ $deskripsi }}
                            </div>
                        @endif

                        <div class="agendaMetaRow">
                            <ion-icon name="location-outline" class="text-primary"></ion-icon>
                            <span ><strong class="text-dark">Lokasi:</strong> {{ $lokasi }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="text-center table-empty-cell border-base rounded-xl text-muted mb-4 bg-card">
            <div class="text-md font-bold text-dark">Tidak ada agenda kegiatan</div>
            <div class="text-sm mt-1">Pada tanggal {{ $selectedDate->translatedFormat('l, d F Y') }}</div>
        </div>
    @endif

</div>

<!-- FAB Tambah Agenda -->
<a href="{{ route('admin.jadwal.create') }}" class="fabAdd" title="Tambah Agenda Baru">
    <ion-icon name="add-outline"></ion-icon>
</a>

@endsection