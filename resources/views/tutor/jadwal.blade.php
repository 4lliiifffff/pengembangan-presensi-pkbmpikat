@extends('layouts.presensi')

@section('title', 'Agenda Mengajar')

@php
    use Carbon\Carbon;
    $user = auth()->user();
    $displayName = (string) (($user->nama_lengkap ?? $user->name) ?? 'Tutor');
    $initial = strtoupper(substr($displayName, 0, 1));

    $tz = 'Asia/Jakarta';
    $todayDate = Carbon::today($tz);
    $selectedDate = $selectedDate ?? Carbon::parse(request('tanggal', $todayDate->toDateString()))->startOfDay();

    // Hitung tanggal navigasi bulan sebelumnya dan berikutnya
    $prevMonthDate = $selectedDate->copy()->subMonth()->toDateString();
    $nextMonthDate = $selectedDate->copy()->addMonth()->toDateString();

    // Hitung offset hari awal bulan (1 = Senin, 7 = Minggu)
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

    $hasAgendasToday = $items->count() > 0;
    $isToday = $selectedDate->isToday();
@endphp

@push('topbar')
    @include('layouts.components.navigasi_atas', [
        'titleName' => 'Agenda Mengajar',
        'subTitle' => $displayName . ' • Jadwal KBM',
        'dashRoute' => route('tutor.dashboard'),
        'backRoute' => route('tutor.dashboard'),
    ])
@endpush

@section('content')

    <div class="agendaPage">

        {{-- ── 1. KALENDER BULANAN (MONTH GRID VIEW) ── --}}
        <div class="agendaHeaderCard">
            <!-- Header Navigasi Bulan -->
            <div class="agendaMonthNav">
                <a href="{{ route('tutor.jadwal', ['tanggal' => $prevMonthDate]) }}" class="agendaNavBtn"
                    title="Bulan Sebelumnya">
                    <ion-icon name="chevron-back-outline"></ion-icon>
                </a>
                <div style="text-align: center;">
                    <h3 class="agendaMonthTitle">{{ $selectedDate->translatedFormat('F Y') }}</h3>
                    <span style="font-size: 11px; font-weight: 700; color: var(--muted);">Kalender Agenda PKBM</span>
                </div>
                <a href="{{ route('tutor.jadwal', ['tanggal' => $nextMonthDate]) }}" class="agendaNavBtn"
                    title="Bulan Berikutnya">
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
                    <a href="{{ route('tutor.jadwal', ['tanggal' => $dStr]) }}"
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
                            {{ $items->count() }} Agenda
                        </span>
                    </div>
                    <div class="agendaBannerDate">
                        {{ $selectedDate->translatedFormat('l, d F Y') }}
                    </div>
                </div>

                <!-- List Card Detail Agenda -->
                <div>
                    @foreach($items as $j)
                        @php
                            $judul = $j->judul ?? 'Agenda Kegiatan';
                            $deskripsi = $j->deskripsi ?? null;
                            $lokasi = $j->lokasi ?? null;
                        @endphp

                        <div class="agendaCardItem {{ $isToday ? 'today' : '' }}">
                            <h4 style="margin: 0 0 6px; font-size: 15px; font-weight: 800; color: var(--text);">
                                {{ $judul }}
                            </h4>

                            @if($deskripsi)
                                <div style="font-size: 12.5px; color: var(--muted); margin-bottom: 8px; line-height: 1.4;">
                                    {{ $deskripsi }}
                                </div>
                            @endif

                            @if($lokasi)
                                <div class="agendaMetaRow">
                                    <ion-icon name="location-outline" style="color: var(--blue);"></ion-icon>
                                    <span><strong style="color: var(--text);">Lokasi:</strong> {{ $lokasi }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div
                style="text-align: center; padding: 36px 16px; background: var(--card); border: 1px solid var(--border); border-radius: 18px; color: var(--muted); margin-bottom: 16px;">
                <div style="font-size: 14px; font-weight: 700; color: var(--text);">Tidak ada agenda kegiatan</div>
                <div style="font-size: 12px; margin-top: 4px;">Pada tanggal {{ $selectedDate->translatedFormat('l, d F Y') }}
                </div>
            </div>
        @endif

    </div>

@endsection