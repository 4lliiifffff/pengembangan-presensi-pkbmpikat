@extends('layouts.kepsek')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="page-wrapper">

        {{-- ===== MAIN CONTENT ===== --}}
        <div class="content">

            {{-- Ringkasan Hari Ini --}}
            <div class="sectionTitleRow">
                <h2>Ringkasan Hari ini</h2>
                <div class="badgeDate">{{ \Carbon\Carbon::parse($today)->translatedFormat('d M Y') }}</div>
            </div>

            <div class="summaryGrid">
                <div class="summaryCard">
                    <div class="summaryTop">
                        <div class="summaryIcon hadir">
                            <ion-icon name="checkmark-circle" style="font-size:20px;"></ion-icon>
                        </div>
                        <div class="summaryLabel">HADIR</div>
                    </div>
                    <div class="summaryCount">{{ $counts['hadir'] ?? 0 }}</div>
                    <ion-icon class="summaryBigIcon" name="checkmark-circle"></ion-icon>
                </div>

                <div class="summaryCard">
                    <div class="summaryTop">
                        <div class="summaryIcon izin">
                            <ion-icon name="time-outline" style="font-size:20px;"></ion-icon>
                        </div>
                        <div class="summaryLabel">IZIN / SAKIT</div>
                    </div>
                    <div class="summaryCount">{{ $counts['izin'] ?? 0 }}</div>
                    <ion-icon class="summaryBigIcon" name="document-text-outline"></ion-icon>
                </div>
            </div>

            <div class="dashboardGrid">
                <div class="dashboardCol">
                    {{-- Visualisasi Grafik Interaktif Tren Kehadiran & Jam Mengajar (Chart.js) --}}
                    <div class="cardBox">
                        <div class="cardHeadRow">
                            <div>
                                <h2>
                                    <ion-icon name="bar-chart-outline" style="color:var(--blue2);"></ion-icon>
                                    Analytics Tren Kehadiran &amp; Jam Mengajar
                                </h2>
                                <span style="font-size: 11px; color: var(--muted);">Histori kinerja 6 bulan terakhir</span>
                            </div>
                            <span class="badgeDate" style="background: rgba(11, 94, 215, 0.1); color: var(--blue2); font-size: 11px; padding: 4px 8px;">Chart.js Live</span>
                        </div>

                        <div style="position: relative; height: 240px; width: 100%;">
                            <canvas id="monthlyTrendChart"></canvas>
                        </div>
                    </div>

                    {{-- Statistik Mingguan --}}
                    <div class="cardBox">
                        <div class="cardHeadRow">
                            <h2>Statistik Mingguan</h2>
                        </div>

                        <div class="barChart">
                            @foreach ($weekly['days'] ?? [] as $day)
                                @php
                                    $count = (int) ($day['count'] ?? 0);
                                    $maxVal = (int) ($weekly['max'] ?? 1);

                                    // Hitung tinggi dan batasi maksimal agar tidak offside
                                    $calculatedHeight = $maxVal > 0 ? (int) round(($count / $maxVal) * 85) : 0;
                                    $height = min(85, max(6, $calculatedHeight));

                                    $todayISO = (int) \Carbon\Carbon::now()->format('N'); // 1=Mon…7=Sun
                                    $dayLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                                    $dayIndex = array_search($day['label'] ?? '', $dayLabels, true);
                                    $isToday = $dayIndex !== false && (int) $dayIndex + 1 === $todayISO;
                                @endphp
                                <div class="barCol">
                                    <div class="bar {{ $isToday ? 'active' : '' }}" style="height: {{ $height }}px;"></div>
                                    <div class="barDay">{{ $day['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="dashboardCol">
                    {{-- Pemeringkatan Indikator Kinerja Utama (KPI Tutor) --}}
                    <div class="cardBox">
                        <div class="cardHeadRow">
                            <div>
                                <h2><ion-icon name="trophy-outline" style="color:#d97706;"></ion-icon> Pemeringkatan KPI Tutor</h2>
                                <span style="font-size: 11px; color: var(--muted);">Evaluasi kedisiplinan & akumulasi jam mengajar</span>
                            </div>
                            <a href="{{ route('kepsek.laporan') }}" class="mutedLink">Rekap &rsaquo;</a>
                        </div>

                        <div class="kpiLeaderboardList">
                            @forelse($kpiRanking ?? [] as $item)
                                @php
                                    $tutor = $item['tutor'];
                                    $rank = $item['rank'];
                                    $rankClass = match($rank) {
                                        1 => 'gold',
                                        2 => 'silver',
                                        3 => 'bronze',
                                        default => 'normal',
                                    };
                                    $rankText = match($rank) {
                                        1 => '🥇 #1',
                                        2 => '🥈 #2',
                                        3 => '🥉 #3',
                                        default => '#'.$rank,
                                    };
                                    $kpiScore = $item['kpi_score'];
                                    $pctDisiplin = $item['pct_disiplin'];
                                    $totalJam = $item['total_jam'];
                                    $totalHadir = $item['total_hadir'];
                                    $kategori = $item['kategori_kinerja'];
                                    $badgeColor = $item['badge_color'];
                                    $initial = strtoupper(substr((string) $tutor->nama_lengkap, 0, 1));
                                @endphp
                                <div class="kpiCard">
                                    <div class="kpiLeft">
                                        <div class="kpiRank {{ $rankClass }}">
                                            {{ $rankText }}
                                        </div>
                                        
                                        <div class="kpiAvatar">
                                            @if ($tutor->foto ?? null)
                                                <img src="{{ asset($tutor->foto) }}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;" />
                                            @else
                                                {{ $initial }}
                                            @endif
                                        </div>

                                        <div class="kpiInfo">
                                            <div class="kpiName">
                                                {{ $tutor->nama_lengkap }}
                                            </div>
                                            <div class="kpiMeta">
                                                <span><ion-icon name="time-outline" style="vertical-align:middle;font-size:12px;"></ion-icon> {{ $totalJam }} Jam</span>
                                                <span>•</span>
                                                <span><ion-icon name="checkmark-done-outline" style="vertical-align:middle;font-size:12px;"></ion-icon> {{ $totalHadir }} Sesi</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="kpiRight">
                                        <div class="kpiScore">
                                            {{ $kpiScore }} <span class="kpiScoreSub">/ 100</span>
                                        </div>
                                        <span class="pill {{ $badgeColor == 'success' ? 'hadir' : ($badgeColor == 'warning' ? 'izin' : 'alpha') }}" style="font-size: 10px; padding: 2px 8px;">
                                            {{ $kategori }} ({{ $pctDisiplin }}%)
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="emptyState" style="text-align: center; color: var(--muted); padding: 20px;">Belum ada data evaluasi KPI Tutor.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Aktivitas Terbaru --}}
                    <div class="activityHeaderRow">
                        <h2>Aktivitas Presensi Terbaru</h2>
                        <a class="mutedLink" href="{{ route('kepsek.presensi-tutor') }}">Lihat Semua &rsaquo;</a>
                    </div>

                    <div class="activityList">
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
                            <div class="activityRow">
                                <div class="activityLeft">
                                    <div class="activityAvatar">
                                        @if ($item->tutor->foto ?? null)
                                            <img src="{{ asset($item->tutor->foto) }}" alt="Avatar"
                                                style="width:100%;height:100%;object-fit:cover;" />
                                        @else
                                            {{ $initial }}
                                        @endif
                                    </div>
                                    <div style="min-width:0;">
                                        <div class="activityName">{{ $tutorName }}</div>
                                        <div class="activityMeta">
                                            {{ $siswaName }}{{ $jam ? ' · ' . $jam : '' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="activityRight">
                                    <div class="pill {{ $pillClass }}">{{ $statusLabel }}</div>
                                    <a class="detailBtn" href="{{ route('kepsek.presensi-tutor', ['tutor_id' => $item->tutor_id, 'siswa_id' => $item->siswa_id, 'tgl_presensi' => $item->tgl_presensi]) }}">DETAIL</a>
                                </div>
                            </div>
                        @empty
                            <div class="emptyState">Belum ada data presensi.</div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>{{-- end .content --}}

    </div>{{-- end .page-wrapper --}}

    {{-- Script Inisialisasi Grafik Chart.js --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('monthlyTrendChart');
            if (!ctx) return;

            const labels = {!! json_encode($monthlyTrend['months'] ?? []) !!};
            const totalSesi = {!! json_encode($monthlyTrend['total_sesi'] ?? []) !!};
            const totalHadir = {!! json_encode($monthlyTrend['total_hadir'] ?? []) !!};
            const totalJam = {!! json_encode($monthlyTrend['total_jam'] ?? []) !!};

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Jam Mengajar (Jam)',
                            data: totalJam,
                            type: 'line',
                            borderColor: '#0B5ED7',
                            backgroundColor: 'rgba(11, 94, 215, 0.1)',
                            borderWidth: 3,
                            pointBackgroundColor: '#0B5ED7',
                            pointRadius: 4,
                            fill: true,
                            tension: 0.35,
                            yAxisID: 'y1'
                        },
                        {
                            label: 'Presensi Hadir (Sesi)',
                            data: totalHadir,
                            backgroundColor: 'rgba(16, 185, 129, 0.85)',
                            borderRadius: 6,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Total Sesi Bimbingan',
                            data: totalSesi,
                            backgroundColor: 'rgba(203, 213, 225, 0.5)',
                            borderRadius: 6,
                            yAxisID: 'y'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }
                            }
                        },
                        tooltip: {
                            padding: 10,
                            borderRadius: 8,
                            usePointStyle: true
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: { display: true, text: 'Sesi', font: { size: 10 } },
                            grid: { color: '#f1f5f9' },
                            beginAtZero: true
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: { display: true, text: 'Jam', font: { size: 10 } },
                            grid: { drawOnChartArea: false },
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
@endsection
