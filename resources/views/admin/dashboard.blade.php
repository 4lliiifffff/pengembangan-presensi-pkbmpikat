@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@php
    $user = auth()->user();
    $displayName = (string) ($user->nama_lengkap ?? ($user->name ?? 'Administrator'));
@endphp

@push('styles')
    <style>
        .presensiPersonalRow {
            display: grid;
            grid-template-columns: minmax(280px, 1fr) 2fr;
            gap: 16px;
            padding: 0 16px 16px;
            width: 100%;
            box-sizing: border-box;
        }

        @media (min-width: 992px) {
            .presensiPersonalRow {
                padding: 0 24px 20px;
                gap: 20px;
            }
        }

        .todayCard {
            position: relative;
        }

        .todayAction {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
            flex-shrink: 0;
        }

        .btnPresensiCepat {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 15px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .btnPresensiCepat.masuk {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
        }

        .btnPresensiCepat.masuk:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
            color: #ffffff;
        }

        .btnPresensiCepat.pulang {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
        }

        .btnPresensiCepat.pulang:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
            color: #ffffff;
        }

        .badgeDone {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
            font-size: 12.5px;
            font-weight: 700;
            border-radius: 9999px;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }

        @media (max-width: 900px) {
            .presensiPersonalRow {
                grid-template-columns: 1fr;
                gap: 12px;
                padding: 0 16px 14px;
            }

            .todayCard {
                flex-wrap: wrap;
                padding: 14px 16px;
            }

            .todayAction {
                width: 100%;
                margin-top: 10px;
            }

            .btnPresensiCepat {
                width: 100%;
                justify-content: center;
                padding: 10px 16px;
            }
        }
    </style>
@endpush

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

    {{-- ── WAKTU SAAT INI & STATUS PRESENSI HARI INI ── --}}
    @php
        $todayStatus = $todayStatus ?? 'belum';
        $statusClass = match ($todayStatus) {
            'proses' => 'proses',
            'selesai' => 'selesai',
            default => 'belum',
        };
        $statusIcon = match ($todayStatus) {
            'proses' => 'time-outline',
            'selesai' => 'checkmark-circle-outline',
            default => 'radio-button-off-outline',
        };
        $statusText = match ($todayStatus) {
            'proses' => 'Sedang Berlangsung (Sesi Kerja Aktif)',
            'selesai' => 'Selesai Hari Ini (Sudah Pulang)',
            default => 'Belum Mulai Sesi',
        };
        $jamMasukToday = $todayPresensi?->jam_mulai ? substr((string) $todayPresensi->jam_mulai, 0, 5) : '-';
        $jamPulangToday = $todayPresensi?->jam_selesai ? substr((string) $todayPresensi->jam_selesai, 0, 5) : '-';
    @endphp

    <div class="presensiPersonalRow">
        {{-- JAM REALTIME --}}
        <div class="clockCard">
            <div class="clockLabel">WAKTU SAAT INI</div>
            <div class="clockTime" id="clockTime">--:--:--</div>
            <div class="clockTz">Waktu Indonesia Barat (WIB)</div>
        </div>

        {{-- STATUS PRESENSI HARI INI --}}
        <div class="todayCard {{ $statusClass }}">
            <div class="todayIcon {{ $statusClass }}">
                <ion-icon name="{{ $statusIcon }}"></ion-icon>
            </div>
            <div class="todayBody">
                <div class="todayStatusLabel {{ $statusClass }}">{{ $statusText }}</div>
                <div class="todayTimeRow">
                    <div class="todayTimeChip">
                        <span class="todayTimeVal">{{ $jamMasukToday }}</span>
                        <span class="todayTimeLbl">Jam Mulai</span>
                    </div>
                    <div class="todayTimeChip">
                        <span class="todayTimeVal">{{ $jamPulangToday }}</span>
                        <span class="todayTimeLbl">Jam Selesai</span>
                    </div>
                </div>
            </div>
            <div class="todayAction">
                @if ($todayStatus === 'belum')
                    <a href="{{ route('admin.presensi') }}" class="btnPresensiCepat masuk">
                        <ion-icon name="camera-outline"></ion-icon>
                        <span>Presensi Masuk</span>
                    </a>
                @elseif ($todayStatus === 'proses')
                    <a href="{{ route('admin.presensi') }}" class="btnPresensiCepat pulang">
                        <ion-icon name="log-out-outline"></ion-icon>
                        <span>Presensi Pulang</span>
                    </a>
                @else
                    <div class="badgeDone">
                        <ion-icon name="checkmark-done-circle-outline"></ion-icon>
                        <span>Presensi Selesai</span>
                    </div>
                @endif
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
            <a href="{{ route('admin.jadwal-sesi-kbm.index', ['tab' => 'rutin']) }}" class="adminQuickItem">
                <div class="adminQuickIcon amber">
                    <ion-icon name="repeat-outline"></ion-icon>
                </div>
                <div class="adminQuickText">
                    <span class="adminQuickTitle">Pola Rutin KBM</span>
                    <span class="adminQuickSub">Master Bimbingan Murid</span>
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

    @push('scripts')
        <script>
            function updateLiveClock() {
                const clockEl = document.getElementById('clockTime');
                if (!clockEl) return;
                const now = new Date();
                const pad = (n) => String(n).padStart(2, '0');
                clockEl.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
            }
            setInterval(updateLiveClock, 1000);
            updateLiveClock();
        </script>
    @endpush
@endsection
