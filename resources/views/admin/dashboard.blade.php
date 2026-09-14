@extends('layout.admin')

@section('content')
    {{-- Sisipkan CSS di sini agar langsung terbaca di dalam halaman --}}
    <style>
        .barChart {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            height: 110px;
            padding-top: 20px;
        }

        .barCol {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 12%;
        }

        .bar {
            width: 28px;
            background-color: #e0e0e0;
            border-radius: 6px 6px 0 0;
            transition: height 0.3s ease;
            max-height: 85px;
            /* Kunci pembatas tinggi agar bar tidak offside */
        }

        .bar.active {
            background-color: #248bf5;
        }

        .barDay {
            margin-top: 8px;
            font-size: 12px;
            color: #4a5568;
            font-weight: 600;
        }
    </style>

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
                        <div class="summaryLabel">IZIN</div>
                    </div>
                    <div class="summaryCount">{{ $counts['izin'] ?? 0 }}</div>
                    <ion-icon class="summaryBigIcon" name="document-text-outline"></ion-icon>
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

                            // Hitung tinggi dan batasi maksimal 85px (sesuai max-height pada CSS) agar tidak offside
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

            {{-- Aktivitas Terbaru --}}
            <div class="activityHeaderRow">
                <h2>Aktivitas Terbaru</h2>
                <a class="mutedLink" href="{{ route('admin.laporan.index') }}">Lihat Semua &rsaquo;</a>
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
                                    {{ $siswaName }} {{ $jam ? ' · ' . $jam : '' }}
                                </div>
                            </div>
                        </div>
                        <div class="activityRight">
                            <div class="pill {{ $pillClass }}">{{ $statusLabel }}</div>
                            <a class="detailBtn" href="{{ route('admin.laporan.index', ['tutor_id' => $item->tutor_id, 'start_date' => $item->tgl_presensi, 'siswa_id' => $item->siswa_id]) }}">DETAIL</a>
                        </div>
                    </div>
                @empty
                    <div class="emptyState">Belum ada data presensi.</div>
                @endforelse
            </div>

        </div>{{-- end .content --}}

    </div>{{-- end .page-wrapper --}}
@endsection
