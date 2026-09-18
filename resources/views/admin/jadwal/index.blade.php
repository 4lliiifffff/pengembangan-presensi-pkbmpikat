@extends('layouts.admin')

@section('title', 'Agenda — Admin')

@section('content')

@php
    $dayLabels = ['SEN','SEL','RAB','KAM','JUM','SAB','MIN'];
@endphp

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">AKADEMIK &amp; KALENDER</div>
                <h1 class="laporanHeaderTitle">Agenda &amp; Jadwal Sekolah</h1>
                <div class="laporanHeaderSub">{{ $selectedDate->translatedFormat('l, d F Y') }}</div>
                <p class="laporanHeaderDesc">Jadwal kegiatan pembelajaran, kalender akademik, dan agenda resmi PKBM PIKAT.</p>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.jadwal.exportExcel') }}" class="profileBtnPrimary btn-action-success">
                    <ion-icon name="document-outline"></ion-icon> Export Excel
                </a>
                <button type="button" onclick="document.getElementById('importJadwalModal').style.display='flex'" class="profileBtnPrimary btn-action-info">
                    <ion-icon name="cloud-upload-outline"></ion-icon> Import Agenda
                </button>
                <a href="{{ route('admin.jadwal.create', ['tanggal' => $selectedDate->toDateString()]) }}" class="profileBtnPrimary">
                    <ion-icon name="add-circle-outline"></ion-icon> Tambah Agenda
                </a>
            </div>
        </div>
    </div>

    {{-- ── Tab Switcher Navigasi Admin ── --}}
    <div class="calendarNavTabsContainer">
        <a href="{{ route('admin.jadwal.index') }}" class="calendarNavTab active">
            <ion-icon name="megaphone-outline"></ion-icon>
            <span>Kalender Agenda Sekolah</span>
        </a>
        <a href="{{ route('admin.jadwal-rutin.index') }}" class="calendarNavTab">
            <ion-icon name="repeat-outline"></ion-icon>
            <span>Master Jadwal Rutin Siswa</span>
        </a>
    </div>

{{-- ── Modal Impor Jadwal / Agenda ── --}}
<div id="importJadwalModal" class="app-modal-backdrop" onclick="if(event.target===this) this.style.display='none'">
    <div class="app-modal-card">
        <div class="app-modal-header">
            <div class="app-modal-header-left">
                <div class="app-modal-badge-icon">
                    <ion-icon name="calendar-outline"></ion-icon>
                </div>
                <div>
                    <h3 class="app-modal-title m-0">Impor Kalender Jadwal Massal</h3>
                    <div class="text-xs text-muted">Format Spreadsheet Excel / CSV</div>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('importJadwalModal').style.display='none'" class="app-modal-close" title="Tutup">&times;</button>
        </div>
        <p class="app-modal-desc">
            Unggah berkas spreadsheet Excel/CSV untuk menambahkan agenda kegiatan belajar dan kalender akademik secara massal.
        </p>
        <div class="import-template-banner">
            <div class="import-template-info">
                <div class="import-template-icon">
                    <ion-icon name="cloud-download-outline"></ion-icon>
                </div>
                <div class="import-template-texts">
                    <div class="import-template-title">Belum memiliki template?</div>
                    <div class="import-template-desc">Gunakan format resmi agar agenda tersinkronisasi otomatis.</div>
                </div>
            </div>
            <a href="{{ route('admin.jadwal.downloadTemplate') }}" class="btn-download-template" title="Download Template Jadwal">
                <ion-icon name="download-outline"></ion-icon> Unduh Template (.xlsx)
            </a>
        </div>
        <form method="POST" action="{{ route('admin.jadwal.importExcel') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-field-wrapper mb-3">
                <label class="form-field-label">Pilih Berkas Spreadsheet:</label>
                <div class="fileUploadBox">
                    <input type="file" name="file_excel" id="jadwalFileInput" accept=".xlsx,.xls,.csv" required onchange="handleExcelFileSelected(this, 'jadwalFileFeedback')">
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
                <button type="button" onclick="document.getElementById('importJadwalModal').style.display='none'" class="btnOutline w-auto">Batal</button>
                <button type="submit" class="profileBtnPrimary w-auto">
                    <ion-icon name="cloud-upload-outline"></ion-icon> Unggah &amp; Impor
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function handleExcelFileSelected(input, feedbackId) {
        const feedback = document.getElementById(feedbackId);
        if (!feedback) return;
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const sizeKb = Math.round(file.size / 1024);
            const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
            const sizeText = sizeKb > 1024 ? `${sizeMb} MB` : `${sizeKb} KB`;

            if (file.size > 5 * 1024 * 1024) {
                feedback.style.display = 'block';
                feedback.innerHTML = `
                    <div class="file-selected-card" style="border-color: #fca5a5; background: rgba(239, 68, 68, 0.1);">
                        <div class="fsc-info">
                            <ion-icon name="alert-circle-outline" class="fsc-icon" style="color: #dc2626;"></ion-icon>
                            <div class="fsc-details">
                                <div class="fsc-name">${file.name}</div>
                                <div class="text-xs font-bold" style="color: #dc2626;">Ukuran (${sizeText}) melebihi batas maksimal 5 MB!</div>
                            </div>
                        </div>
                        <button type="button" class="fsc-remove-btn" onclick="clearSelectedExcel('${input.id}', '${feedbackId}')" title="Hapus">
                            <ion-icon name="close-circle-outline"></ion-icon>
                        </button>
                    </div>`;
                input.value = '';
                return;
            }

            feedback.style.display = 'block';
            feedback.innerHTML = `
                <div class="file-selected-card">
                    <div class="fsc-info">
                        <ion-icon name="document-attach-outline" class="fsc-icon"></ion-icon>
                        <div class="fsc-details">
                            <div class="fsc-name">${file.name}</div>
                            <div class="fsc-meta">${sizeText} &bull; Berkas siap diunggah</div>
                        </div>
                    </div>
                    <button type="button" class="fsc-remove-btn" onclick="clearSelectedExcel('${input.id}', '${feedbackId}')" title="Ganti berkas">
                        <ion-icon name="close-circle-outline"></ion-icon>
                    </button>
                </div>`;
        } else {
            feedback.style.display = 'none';
            feedback.innerHTML = '';
        }
    }

    function clearSelectedExcel(inputId, feedbackId) {
        const input = document.getElementById(inputId);
        const feedback = document.getElementById(feedbackId);
        if (input) input.value = '';
        if (feedback) {
            feedback.style.display = 'none';
            feedback.innerHTML = '';
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

<div class="max-w-2xl px-0 mx-auto pb-6">

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
                    $dStr = $d->format('Y-m-d');
                    $isActive = $d->isSameDay($selectedDate);
                    $isCellToday = $d->isToday();
                    $agendaCount = (int) ($monthCounts[$dStr] ?? 0);
                    $hasEvent = $agendaCount > 0;
                @endphp
                <a href="{{ route('admin.jadwal.index', ['tanggal' => $dStr]) }}"
                   class="agendaDayCell {{ $isActive ? 'active' : '' }} {{ $isCellToday ? 'today' : '' }} {{ $hasEvent ? 'hasEvent' : '' }}"
                   title="{{ $d->translatedFormat('d F Y') }} ({{ $agendaCount }} Agenda Kegiatan)">
                    <span class="agendaDayNum">{{ $d->day }}</span>
                    @if($hasEvent)
                        <span class="agendaEventIndicatorWrap">
                            <span class="agendaEventDot"></span>
                            @if($agendaCount > 1)
                                <span class="agendaEventCountText">{{ $agendaCount }}</span>
                            @endif
                        </span>
                    @endif
                </a>
            @endforeach
        </div>

        <!-- Legenda Kalender -->
        <div class="agendaCalendarLegend">
            <div class="agendaLegendItem">
                <span class="agendaLegendIndicator todayIndicator"></span>
                <span>Hari Ini</span>
            </div>
            <div class="agendaLegendItem">
                <span class="agendaLegendIndicator eventIndicator"></span>
                <span>Ada Kegiatan</span>
            </div>
            <div class="agendaLegendItem">
                <span class="agendaLegendIndicator activeIndicator"></span>
                <span>Tanggal Terpilih</span>
            </div>
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
                        {{ $jadwals->count() }} Kegiatan
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
                            <span><strong class="text-dark">Lokasi:</strong> {{ $lokasi }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="emptyAgendaBox">
            <div class="emptyAgendaIcon">
                <ion-icon name="calendar-clear-outline"></ion-icon>
            </div>
            <div class="emptyAgendaTitle">Tidak Ada Agenda Kegiatan</div>
            <div class="emptyAgendaDesc">Belum ada agenda atau kegiatan terjadwal pada <strong>{{ $selectedDate->translatedFormat('l, d F Y') }}</strong>.</div>
            <a href="{{ route('admin.jadwal.create', ['tanggal' => $selectedDate->toDateString()]) }}" class="profileBtnPrimary btn-sm mt-3">
                <ion-icon name="add-circle-outline"></ion-icon> Tambah Agenda Pada Tanggal Ini
            </a>
        </div>
    @endif

</div>

<!-- FAB Tambah Agenda Mobile -->
<a href="{{ route('admin.jadwal.create', ['tanggal' => $selectedDate->toDateString()]) }}" class="fabAdd" title="Tambah Agenda Baru">
    <ion-icon name="add-outline"></ion-icon>
</a>

@endsection