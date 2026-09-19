@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@php
    $user = auth()->user();
    $displayName = (string) ($user->nama_lengkap ?? ($user->name ?? 'Administrator'));
@endphp

@section('content')
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">PANEL KONTROL ADMINISTRATOR</div>
                <h1 class="laporanHeaderTitle">Dashboard Admin</h1>
                <div class="laporanHeaderSub">Selamat datang kembali, {{ $displayName }}!</div>
                <p class="laporanHeaderDesc">Ringkasan aktivitas operasional lembaga, rekapitulasi presensi bimbingan &amp; tutor, serta verifikasi pengajuan izin.</p>
            </div>
            <div class="laporanHeaderActions">
                <div class="badgeDate">
                    <ion-icon name="calendar-outline"></ion-icon>
                    <span>{{ \Carbon\Carbon::parse($today)->translatedFormat('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Summary KPI Cards ── --}}
    <div class="summaryGrid">
        <div class="summaryCard">
            <div class="summaryTop">
                <div class="summaryIcon hadir">
                    <ion-icon name="checkmark-circle-outline"></ion-icon>
                </div>
                <div class="summaryLabel">HADIR HARI INI</div>
            </div>
            <div class="summaryCount">{{ $counts['hadir'] ?? 0 }}</div>
            <div class="summarySub">Sesi bimbingan selesai</div>
            <ion-icon class="summaryBigIcon" name="checkmark-circle"></ion-icon>
        </div>

        <div class="summaryCard">
            <div class="summaryTop">
                <div class="summaryIcon izin">
                    <ion-icon name="time-outline"></ion-icon>
                </div>
                <div class="summaryLabel">IZIN / SAKIT</div>
            </div>
            <div class="summaryCount">{{ $counts['izin'] ?? 0 }}</div>
            <div class="summarySub">Pengajuan hari ini</div>
            <ion-icon class="summaryBigIcon" name="document-text-outline"></ion-icon>
        </div>
    </div>

    {{-- ── Pintasan Menu Cepat Admin ── --}}
    <div class="adminQuickSection">
        <div class="sectionCaps">
            <span class="sectionCapTitle">Pintasan Menu Admin</span>
        </div>
        <div class="adminQuickGrid">
            <a href="{{ route('admin.presensi') }}" class="adminQuickItem">
                <div class="adminQuickIcon blue">
                    <ion-icon name="camera-outline"></ion-icon>
                </div>
                <div class="adminQuickText">
                    <span class="adminQuickTitle">Presensi Saya</span>
                    <span class="adminQuickSub">Bebas Radius</span>
                </div>
            </a>
            <a href="{{ route('admin.karyawan.index') }}" class="adminQuickItem">
                <div class="adminQuickIcon indigo">
                    <ion-icon name="people-outline"></ion-icon>
                </div>
                <div class="adminQuickText">
                    <span class="adminQuickTitle">Kelola Akun</span>
                    <span class="adminQuickSub">Tutor &amp; Siswa</span>
                </div>
            </a>
            <a href="{{ route('admin.jadwal-rutin.index') }}" class="adminQuickItem">
                <div class="adminQuickIcon amber">
                    <ion-icon name="calendar-outline"></ion-icon>
                </div>
                <div class="adminQuickText">
                    <span class="adminQuickTitle">Jadwal &amp; Sesi</span>
                    <span class="adminQuickSub">Pola Rutin KBM</span>
                </div>
            </a>
            <a href="{{ route('admin.laporan.index') }}" class="adminQuickItem">
                <div class="adminQuickIcon emerald">
                    <ion-icon name="document-text-outline"></ion-icon>
                </div>
                <div class="adminQuickText">
                    <span class="adminQuickTitle">Rekap Laporan</span>
                    <span class="adminQuickSub">Detail Presensi</span>
                </div>
            </a>
        </div>
    </div>

    {{-- ── Dashboard Grid ── --}}
    <div class="dashboardGrid">
        <div class="dashboardCol">
            {{-- Statistik Mingguan --}}
            <div class="cardBox">
                <div class="cardHeadRow">
                    <h2>
                        <ion-icon name="bar-chart-outline" class="text-primary"></ion-icon>
                        <span>Statistik Mingguan</span>
                    </h2>
                    <span class="badgeDate text-xs px-2 py-1">7 Hari Terakhir</span>
                </div>

                <div class="barChart">
                    @foreach ($weekly['days'] ?? [] as $day)
                        @php
                            $count = (int) ($day['count'] ?? 0);
                            $maxVal = (int) ($weekly['max'] ?? 1);

                            $calculatedHeight = $maxVal > 0 ? (int) round(($count / $maxVal) * 85) : 0;
                            $height = min(85, max(6, $calculatedHeight));

                            $todayISO = (int) \Carbon\Carbon::now()->format('N'); // 1=Mon…7=Sun
                            $dayLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                            $dayIndex = array_search($day['label'] ?? '', $dayLabels, true);
                            $isToday = $dayIndex !== false && (int) $dayIndex + 1 === $todayISO;
                        @endphp
                        <div class="barCol {{ $isToday ? 'today' : '' }}">
                            <span class="barCount">{{ $count }}</span>
                            <div class="bar {{ $isToday ? 'active' : '' }}" style="height: {{ $height }}px;" title="{{ $count }} Sesi"></div>
                            <div class="barDay">{{ $day['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="dashboardCol">
            {{-- Aktivitas Terbaru --}}
            <div class="cardBox">
                <div class="cardHeadRow">
                    <h2>
                        <ion-icon name="pulse-outline" class="text-primary"></ion-icon>
                        <span>Aktivitas Presensi Terbaru</span>
                    </h2>
                    <a class="mutedLink" href="{{ route('admin.laporan.index') }}">Lihat Semua &rsaquo;</a>
                </div>

                <div class="activityList p-0 mt-2">
                    @forelse($latest as $item)
                        @php
                            $tutorName = $item->tutor->nama_lengkap ?? 'Tutor';
                            $siswaName = $item->siswa->nama_siswa ?? ($item->siswa_id ?? 'Siswa');
                            $initial = strtoupper(substr((string) $tutorName, 0, 1));
                            $pillClass = $item->status_class ?? 'pending';
                            $statusLabel = $item->status_label ?? strtoupper((string) $item->status);
                            $tgl = \Carbon\Carbon::parse($item->tgl_presensi)->translatedFormat('d M Y');
                            $jam = (string) ($item->jam_mulai ?? '');
                        @endphp
                        <div class="activityRow mb-2">
                            <div class="activityLeft">
                                <div class="activityAvatar">
                                    @if ($item->tutor?->foto_url ?? null)
                                        <img src="{{ $item->tutor->foto_url }}" alt="Avatar"
                                            class="w-full h-full object-cover" />
                                    @else
                                        {{ $initial }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="activityName">{{ $tutorName }}</div>
                                    <div class="activityMeta">
                                        {{ $siswaName }}{{ $jam ? ' · ' . $jam . ' WIB' : '' }}
                                    </div>
                                </div>
                            </div>
                            <div class="activityRight">
                                <div class="pill {{ $pillClass }}">{{ $statusLabel }}</div>
                                <a class="detailBtn" href="{{ route('admin.laporan.index', ['tutor_id' => $item->tutor_id, 'start_date' => $item->tgl_presensi, 'siswa_id' => $item->siswa_id]) }}">DETAIL</a>
                            </div>
                        </div>
                    @empty
                        <div class="emptyState m-0 p-4">Belum ada data presensi terbaru.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
