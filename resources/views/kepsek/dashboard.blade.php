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

            {{-- Statistik Mingguan --}}
            <div class="cardBox" style="margin-bottom: 20px;">
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

            {{-- Visualisasi Grafik Interaktif Tren Kehadiran & Jam Mengajar (Chart.js) --}}
            <div class="cardBox" style="margin-bottom: 24px; padding: 18px; border-radius: 16px; background: var(--bg-card, #ffffff); border: 1px solid var(--border-color, #e2e8f0); box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                <div class="cardHeadRow" style="display:flex; justify-between:space-between; align-items:center; margin-bottom: 14px;">
                    <div>
                        <h2 style="font-size: 1.1rem; font-weight: 700; color: var(--text-color, #1e293b); margin:0;">📊 Analytics Tren Kehadiran & Jam Mengajar</h2>
                        <span style="font-size: 0.8rem; color: #64748b;">Histori kinerja 6 bulan terakhir</span>
                    </div>
                    <span class="badgeDate" style="background: rgba(11, 94, 215, 0.1); color: #0B5ED7; font-weight: 600; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem;">Chart.js Live</span>
                </div>

                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>

            {{-- Pemeringkatan Indikator Kinerja Utama (KPI Tutor) --}}
            <div class="cardBox" style="margin-bottom: 24px; padding: 18px; border-radius: 16px; background: var(--bg-card, #ffffff); border: 1px solid var(--border-color, #e2e8f0); box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                <div class="cardHeadRow" style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px;">
                    <div>
                        <h2 style="font-size: 1.1rem; font-weight: 700; color: var(--text-color, #1e293b); margin:0;">🏆 Pemeringkatan KPI Tutor</h2>
                        <span style="font-size: 0.8rem; color: #64748b;">Evaluasi kedisiplinan & akumulasi jam mengajar bulan ini</span>
                    </div>
                    <a href="{{ route('kepsek.laporan') }}" class="mutedLink" style="font-size: 0.8rem; font-weight: 600; color: #0B5ED7; text-decoration: none;">Rekap Detail &rsaquo;</a>
                </div>

                <div class="kpiLeaderboardList" style="display: flex; flex-direction: column; gap: 12px;">
                    @forelse($kpiRanking ?? [] as $item)
                        @php
                            $tutor = $item['tutor'];
                            $rank = $item['rank'];
                            $rankBadge = $item['rank_badge'];
                            $kpiScore = $item['kpi_score'];
                            $pctDisiplin = $item['pct_disiplin'];
                            $totalJam = $item['total_jam'];
                            $totalHadir = $item['total_hadir'];
                            $kategori = $item['kategori_kinerja'];
                            $badgeColor = $item['badge_color'];
                            $initial = strtoupper(substr((string) $tutor->nama_lengkap, 0, 1));
                        @endphp
                        <div class="kpiCard" style="display:flex; align-items:center; justify-content:space-between; padding: 12px 14px; background: var(--bg-body, #f8fafc); border-radius: 12px; border: 1px solid var(--border-color, #f1f5f9);">
                            <div style="display:flex; align-items:center; gap: 12px; min-width: 0;">
                                <div style="font-weight: 800; font-size: 0.9rem; min-width: 42px; text-align: center; padding: 4px 6px; border-radius: 8px; background: {{ $rank == 1 ? '#fffbe6' : ($rank == 2 ? '#f1f5f9' : ($rank == 3 ? '#fff7ed' : '#ffffff')) }}; color: {{ $rank == 1 ? '#d97706' : ($rank == 2 ? '#475569' : ($rank == 3 ? '#c2410c' : '#64748b')) }}; border: 1px solid {{ $rank <= 3 ? 'currentColor' : '#cbd5e1' }};">
                                    {{ $rank == 1 ? '🥇 #1' : ($rank == 2 ? '🥈 #2' : ($rank == 3 ? '🥉 #3' : '#'.$rank)) }}
                                </div>
                                
                                <div class="activityAvatar" style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background: #0B5ED7; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">
                                    @if ($tutor->foto ?? null)
                                        <img src="{{ asset($tutor->foto) }}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;" />
                                    @else
                                        {{ $initial }}
                                    @endif
                                </div>

                                <div style="min-width: 0;">
                                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-color, #1e293b); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $tutor->nama_lengkap }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b; display: flex; gap: 8px; align-items: center; margin-top: 2px;">
                                        <span>⏱️ {{ $totalJam }} Jam</span>
                                        <span>•</span>
                                        <span>✅ {{ $totalHadir }} Sesi</span>
                                    </div>
                                </div>
                            </div>

                            <div style="text-align: right; flex-shrink: 0; padding-left: 10px;">
                                <div style="font-weight: 800; font-size: 1rem; color: #0B5ED7;">
                                    {{ $kpiScore }} <span style="font-size: 0.7rem; font-weight: 500; color: #64748b;">/ 100</span>
                                </div>
                                <div style="margin-top: 2px;">
                                    <span class="pill" style="font-size: 0.65rem; padding: 2px 8px; border-radius: 12px; font-weight: 600; text-transform: uppercase;
                                        background: {{ $badgeColor == 'success' ? '#dcfce7' : ($badgeColor == 'info' ? '#e0f2fe' : ($badgeColor == 'warning' ? '#fef3c7' : '#fee2e2')) }};
                                        color: {{ $badgeColor == 'success' ? '#15803d' : ($badgeColor == 'info' ? '#0369a1' : ($badgeColor == 'warning' ? '#b45309' : '#b91c1c')) }};">
                                        {{ $kategori }} ({{ $pctDisiplin }}%)
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="emptyState" style="text-align: center; color: #94a3b8; padding: 20px;">Belum ada data evaluasi KPI Tutor.</div>
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
