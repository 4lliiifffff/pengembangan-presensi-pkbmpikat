@extends('layout.admin')

@section('title', 'Laporan — Admin')

<style>
    /* ── Page Header ── */
    .laporanHeader {
        background: #1a3a5c;
        padding: 16px 16px 20px;
    }

    .laporanHeaderLabel {
        font-size: 11px;
        color: #f1f5f9;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .laporanHeaderTitle {
        font-size: 18px;
        font-weight: 700;
        color: #fff;
    }

    .laporanHeaderSub {
        font-size: 12px;
        color: #cbd5e1;
        margin-top: 2px;
    }

    /* ── Month Filter ── */
    .monthFilter {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 14px 16px 0;
    }

    .monthInput {
        flex: 1;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: var(--input-bg);
        font-size: 13px;
        color: var(--text);
        font-family: inherit;
    }

    .monthInput:focus {
        outline: none;
        border-color: #2563eb;
    }

    .filterBtn {
        padding: 10px 16px;
        border-radius: 10px;
        border: none;
        background: var(--blue2);
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        white-space: nowrap;
    }

    /* ── Summary Cards ── */
    .summaryRow {
        display: flex;
        gap: 10px;
        padding: 14px 16px 0;
    }

    .statCard {
        flex: 1;
        background: var(--card);
        border-radius: 14px;
        padding: 14px 10px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border);
    }

    .statCardValue {
        font-size: 24px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }

    .statCardLabel {
        font-size: 10px;
        font-weight: 600;
        color: #94a3b8;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .stat-hadir .statCardValue {
        color: #16a34a;
    }

    .stat-izin .statCardValue {
        color: #f59e0b;
    }

    /* ── Section Title ── */
    .sectionTitle {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1.5px;
        color: var(--muted);
        padding: 16px 16px 8px;
    }

    /* ── Chart Container ── */
    .chartContainer {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
        padding: 0 16px;
    }

    @media (min-width: 768px) {
        .chartContainer {
            grid-template-columns: 1fr;
        }
    }

    .chartCard {
        background: var(--card);
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border);
    }

    .chartTitle {
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 16px;
    }

    /* ── Tutor List ── */
    .tutorList {
        padding: 0 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .tutorCard {
        background: var(--card);
        border-radius: 12px;
        padding: 14px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        border: 1px solid var(--border);
    }

    .tutorCardTop {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .tutorCardName {
        font-size: 14px;
        font-weight: 600;
        color: var(--text);
    }

    .tutorCardCount {
        font-size: 13px;
        font-weight: 700;
        color: var(--blue2);
    }

    .progressBg {
        height: 6px;
        background: #e2e8f0;
        border-radius: 3px;
    }

    .progressFill {
        height: 6px;
        border-radius: 3px;
        background: #2563eb;
    }

    /* ── Recent Presensi (Table Layout) ── */
    .recentList {
        padding: 0 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding-bottom: 110px;
    }

    .tableContainer {
        background: var(--card);
        border-radius: 12px;
        overflow-x: auto;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        margin-bottom: 110px;
        border: 1px solid var(--border);
    }

    .laporanTable {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .laporanTable th {
        background: var(--card-alt);
        padding: 12px 10px;
        text-align: left;
        color: var(--muted);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    .laporanTable td {
        padding: 12px 10px;
        border-bottom: 1px solid var(--border);
        color: var(--text);
        vertical-align: middle;
    }

    .fotoStack {
        display: flex;
        gap: 4px;
    }

    .fotoThumbnail {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        object-fit: cover;
        cursor: pointer;
        transition: transform 0.2s;
        border: 1px solid var(--border);
    }

    .fotoThumbnail:hover {
        transform: scale(1.1);
    }

    .fotoPlaceholder {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        border: 1px dashed var(--muted);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: var(--muted);
        background: var(--card-alt);
    }

    .mapBtn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: #2563eb;
        color: #fff;
        border-radius: 8px;
        text-decoration: none;
        transition: background 0.2s;
    }

    .mapBtn:hover {
        background: #1d4ed8;
    }

    /* ── Modal Popup ── */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(0, 0, 0, 0.75);
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-box {
        background: var(--card);
        border-radius: 18px;
        overflow: hidden;
        max-width: 520px;
        width: 100%;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        border: 1px solid var(--border);
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: var(--muted);
        line-height: 1;
    }

    .modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 240px;
    }

    .modal-body img {
        max-width: 100%;
        border-radius: 10px;
    }

    .modal-body iframe {
        width: 100%;
        height: 340px;
        border: 0;
        border-radius: 10px;
    }

    .pill {
        font-size: 10px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        letter-spacing: 0.3px;
        flex-shrink: 0;
    }

    .pill.hadir {
        background: #dcfce7;
        color: #15803d;
    }

    .pill.izin {
        background: #fef9c3;
        color: #92400e;
    }

    .pill.alpha {
        background: #fee2e2;
        color: #dc2626;
    }

    .pill.proses {
        background: #e0e7ff;
        color: #4f46e5;
    }

    .emptyState {
        text-align: center;
        padding: 30px;
        color: #94a3b8;
        font-size: 13px;
    }
</style>

@section('content')

    {{-- Header --}}
    <div class="laporanHeader">
        <div class="laporanHeaderLabel">LAPORAN</div>
        <div class="laporanHeaderTitle">Rekap Presensi</div>
        <div class="laporanHeaderSub">
            {{ \Carbon\Carbon::parse($inputStartDate)->translatedFormat('d M Y') }} -
            {{ \Carbon\Carbon::parse($inputEndDate)->translatedFormat('d M Y') }}
        </div>
    </div>

    {{-- Date & Advanced Filter --}}
    <form method="GET" action="{{ route('admin.laporan.index') }}">
        <div class="monthFilter" style="flex-wrap: wrap; margin-bottom: 20px;">
            <input type="date" name="start_date" class="monthInput" value="{{ $inputStartDate }}"
                style="flex:1; min-width: 140px; color-scheme: light dark;">
            <span style="font-size:12px; font-weight:bold; color: var(--muted); padding-top: 10px;">s/d</span>
            <input type="date" name="end_date" class="monthInput" value="{{ $inputEndDate }}"
                style="flex:1; min-width: 140px; color-scheme: light dark;">

            <select name="tutor_id" class="monthInput" style="flex:1; min-width: 140px;">
                <option value="">Semua Tutor</option>
                @foreach ($tutors as $tutor)
                    <option value="{{ $tutor->id }}" {{ $tutorId == $tutor->id ? 'selected' : '' }}>
                        {{ $tutor->nama_lengkap }}</option>
                @endforeach
            </select>

            <select name="siswa_id" class="monthInput" style="flex:1; min-width: 140px;">
                <option value="">Semua Siswa</option>
                @foreach ($siswas as $siswa)
                    <option value="{{ $siswa->id }}" {{ $siswaId == $siswa->id ? 'selected' : '' }}>
                        {{ $siswa->nama_siswa }}</option>
                @endforeach
            </select>

            <select name="status" class="monthInput" style="flex:1; min-width: 140px;">
                <option value="">Semua Status</option>
                <option value="hadir" {{ $statusFilter == 'hadir' ? 'selected' : '' }}>Hadir (Selesai)</option>
                <option value="proses" {{ $statusFilter == 'proses' ? 'selected' : '' }}>Sedang Berjalan</option>
                <option value="izin" {{ $statusFilter == 'izin' ? 'selected' : '' }}>Izin/Sakit</option>
            </select>

            <button type="submit" class="filterBtn" style="background: #1a3a5c; width: 100%;">Filter</button>
            <div style="display: flex; gap: 8px; width: 100%;">
                <button type="submit" formtarget="_blank" formaction="{{ route('admin.laporan.exportExcel') }}"
                    class="filterBtn" style="background: #16a34a; flex:1;">Excel</button>
                <button type="submit" formtarget="_blank" formaction="{{ route('admin.laporan.exportPdf') }}"
                    class="filterBtn" style="background: #dc2626; flex:1;">PDF</button>
            </div>
        </div>
    </form>

    {{-- Summary Stats --}}
    <div class="summaryRow">
        <div class="statCard stat-hadir">
            <div class="statCardValue">{{ $totalHadir }}</div>
            <div class="statCardLabel">Total Hadir</div>
        </div>
        <div class="statCard stat-izin">
            <div class="statCardValue">{{ $totalIzin }}</div>
            <div class="statCardLabel">Total Izin</div>
        </div>
    </div>

    {{-- Chart Section --}}
    <div class="sectionTitle">ANALISIS VISUAL</div>
    <div class="chartContainer">
        <div class="chartCard">
            <div class="chartTitle">Tren Kehadiran Harian</div>
            <canvas id="trendChart" style="max-height: 250px;"></canvas>
        </div>
    </div>

    {{-- Jadwal per Tutor --}}
    @if ($perTutor->isNotEmpty())
        <div class="sectionTitle">JADWAL PER TUTOR</div>
        @php $maxJadwal = $perTutor->max('total_jadwal') ?: 1; @endphp
        <div class="tutorList">
            @foreach ($perTutor as $tutor)
                @php
                    $pct = (int) round(($tutor->total_jadwal / $maxJadwal) * 100);
                @endphp
                <div class="tutorCard">
                    <div class="tutorCardTop">
                        <div class="tutorCardName">{{ $tutor->nama_lengkap }}</div>
                        <div class="tutorCardCount">{{ $tutor->total_jadwal }} kali</div>
                    </div>
                    <div class="progressBg">
                        <div class="progressFill" style="width: {{ $pct }}%;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Recent Presensi (Table View) --}}
    <div class="sectionTitle">MONITORING PRESENSI TUTOR</div>
    <div style="padding: 0 16px;">
        <div class="tableContainer">
            <table class="laporanTable">
                <thead>
                    <tr>
                        <th>NO.</th>
                        <th>TANGGAL</th>
                        <th>NAMA TUTOR</th>
                        <th>NAMA SISWA</th>
                        <th>MASUK</th>
                        <th>SELESAI</th>
                        <th>FOTO (M/S)</th>
                        <th>LOKASI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPresensi as $index => $presensi)
                        @php
                            $tutor = $presensi->tutor;
                            $tutorName = $tutor->nama_lengkap ?? 'Tutor';
                            $siswaName = $presensi->siswa->nama_siswa ?? '-';
                            $tgl = \Carbon\Carbon::parse($presensi->tgl_presensi)->format('d/m/y');
                            $jamMasuk = $presensi->jam_mulai
                                ? \Carbon\Carbon::parse($presensi->jam_mulai)->format('H:i')
                                : '-';
                            $jamSelesai = $presensi->jam_selesai
                                ? \Carbon\Carbon::parse($presensi->jam_selesai)->format('H:i')
                                : '-';

                            $lokasi = $presensi->lokasi_mulai ?? '-';
                            $urlPeta =
                                $lokasi !== '-'
                                    ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($lokasi)
                                    : '#';
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $tgl }}</td>
                            <td style="font-weight: 600; color: var(--text);">{{ $tutorName }}</td>
                            <td>{{ $siswaName }}</td>
                            <td style="font-weight: 700; color: var(--success);">{{ $jamMasuk }}</td>
                            <td style="font-weight: 700; color: var(--blue2);">{{ $jamSelesai }}</td>
                            <td>
                                <div class="fotoStack">
                                    @if ($presensi->foto_mulai)
                                        <img src="{{ asset($presensi->foto_mulai) }}" class="fotoThumbnail"
                                            title="Foto Mulai"
                                            onclick="openPhotoModal('{{ asset($presensi->foto_mulai) }}', 'Foto Masuk — ' + '{{ $tutorName }}')"
                                            style="cursor:pointer;">
                                    @else
                                        <div class="fotoPlaceholder">M -</div>
                                    @endif

                                    @if ($presensi->foto_selesai)
                                        <img src="{{ asset($presensi->foto_selesai) }}" class="fotoThumbnail"
                                            title="Foto Selesai"
                                            onclick="openPhotoModal('{{ asset($presensi->foto_selesai) }}', 'Foto Pulang — ' + '{{ $tutorName }}')"
                                            style="cursor:pointer;">
                                    @else
                                        <div class="fotoPlaceholder">S -</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if ($lokasi !== '-')
                                    <button type="button" class="mapBtn" title="Lihat Peta"
                                        onclick="openMapModal('{{ $lokasi }}')">
                                        <ion-icon name="map-outline"></ion-icon>
                                    </button>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="emptyState">Belum ada data presensi periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Karyawan Presensi (Table View) --}}
    @if ($karyawanPresensi->isNotEmpty())
        <div class="sectionTitle">MONITORING PRESENSI KARYAWAN (ADMIN/KEPSEK)</div>
        <div style="padding: 0 16px;">
            <div class="tableContainer">
                <table class="laporanTable">
                    <thead>
                        <tr>
                            <th>NO.</th>
                            <th>TANGGAL</th>
                            <th>NAMA KARYAWAN</th>
                            <th>ROLE</th>
                            <th>MASUK</th>
                            <th>SELESAI</th>
                            <th>FOTO (M/S)</th>
                            <th>LOKASI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($karyawanPresensi as $index => $kp)
                            @php
                                $kpTgl = \Carbon\Carbon::parse($kp->tgl_presensi)->format('d/m/y');
                                $kpJamMasuk = $kp->jam_mulai
                                    ? \Carbon\Carbon::parse($kp->jam_mulai)->format('H:i')
                                    : '-';
                                $kpJamSelesai = $kp->jam_selesai
                                    ? \Carbon\Carbon::parse($kp->jam_selesai)->format('H:i')
                                    : '-';
                                $kpName = $kp->user->nama_lengkap;
                                $kpRole = ucfirst($kp->user->role);
                                $lokasi = $kp->lokasi_mulai;
                                $urlPeta = $lokasi
                                    ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($lokasi)
                                    : '#';
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $kpTgl }}</td>
                                <td style="font-weight: 600; color: var(--text);">{{ $kpName }}</td>
                                <td><span
                                        style="background: rgba(100,116,139,0.1); padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: bold; color: var(--muted);">{{ $kpRole }}</span>
                                </td>
                                <td style="font-weight: 700; color: var(--success);">{{ $kpJamMasuk }}</td>
                                <td style="font-weight: 700; color: var(--blue2);">{{ $kpJamSelesai }}</td>
                                <td>
                                    <div class="fotoStack">
                                        @if ($kp->foto_mulai)
                                            <img src="{{ asset($kp->foto_mulai) }}" class="fotoThumbnail"
                                                title="Foto Mulai"
                                                onclick="openPhotoModal('{{ asset($kp->foto_mulai) }}', 'Foto Masuk — ' + '{{ $kpName }}')"
                                                style="cursor:pointer;">
                                        @else
                                            <div class="fotoPlaceholder">M -</div>
                                        @endif

                                        @if ($kp->foto_selesai)
                                            <img src="{{ asset($kp->foto_selesai) }}" class="fotoThumbnail"
                                                title="Foto Selesai"
                                                onclick="openPhotoModal('{{ asset($kp->foto_selesai) }}', 'Foto Pulang — ' + '{{ $kpName }}')"
                                                style="cursor:pointer;">
                                        @else
                                            <div class="fotoPlaceholder">S -</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if ($lokasi && $lokasi !== '-')
                                        <button type="button" class="mapBtn" title="Lihat Peta"
                                            onclick="openMapModal('{{ $lokasi }}')">
                                            <ion-icon name="map-outline"></ion-icon>
                                        </button>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const trendCtx = document.getElementById('trendChart').getContext('2d');
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartLabels) !!},
                    datasets: [{
                            label: 'Hadir',
                            data: {!! json_encode($chartDataHadir) !!},
                            borderColor: '#16a34a',
                            backgroundColor: 'rgba(22, 163, 74, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Izin',
                            data: {!! json_encode($chartDataIzin) !!},
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    size: 10
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        });
    </script>

    {{-- ── Modal Foto ── --}}
    <div class="modal-overlay" id="photoModal" onclick="if(event.target===this)closeModal('photoModal')">
        <div class="modal-box">
            <div class="modal-header">
                <span id="photoModalTitle">Foto Presensi</span>
                <button class="modal-close" onclick="closeModal('photoModal')">&times;</button>
            </div>
            <div class="modal-body">
                <img id="photoModalImg" src="" alt="Foto presensi">
            </div>
        </div>
    </div>

    {{-- ── Modal Maps ── --}}
    <div class="modal-overlay" id="mapModal" onclick="if(event.target===this)closeModal('mapModal')">
        <div class="modal-box">
            <div class="modal-header">
                <span>Lokasi Presensi</span>
                <button class="modal-close" onclick="closeModal('mapModal')">&times;</button>
            </div>
            <div class="modal-body">
                <iframe id="mapModalFrame" src="" allowfullscreen loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>

    <script>
        function openPhotoModal(src, title) {
            document.getElementById('photoModalImg').src = src;
            document.getElementById('photoModalTitle').textContent = title;
            document.getElementById('photoModal').classList.add('active');
        }

        function openMapModal(lokasi) {
            var q = encodeURIComponent(lokasi);
            document.getElementById('mapModalFrame').src = 'https://www.google.com/maps?q=' + q +
                '&z=17&hl=id&output=embed';
            document.getElementById('mapModal').classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
            if (id === 'mapModal') document.getElementById('mapModalFrame').src = '';
            if (id === 'photoModal') document.getElementById('photoModalImg').src = '';
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('photoModal');
                closeModal('mapModal');
            }
        });
    </script>
@endsection
