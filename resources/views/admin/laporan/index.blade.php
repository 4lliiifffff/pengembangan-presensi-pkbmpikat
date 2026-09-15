@extends('layouts.admin')

@section('title', 'Laporan Presensi & KBM — Admin')

@section('content')

    {{-- Header --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">LAPORAN OPERASIONAL &amp; KBM</div>
                <div class="laporanHeaderTitle">Rekapitulasi Presensi Tutor</div>
                <div class="laporanHeaderSub">
                    Periode: {{ \Carbon\Carbon::parse($inputStartDate)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($inputEndDate)->translatedFormat('d M Y') }}
                </div>
                <p class="laporanHeaderDesc">
                    Monitoring log kehadiran, jam masuk/keluar, moda pembelajaran, dan bukti foto GPS tutor untuk evaluasi akademik &amp; akreditasi.
                </p>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.payroll.index') }}" class="btnPayrollShortcut">
                    <ion-icon name="wallet-outline"></ion-icon>
                    <span>Buka Rekap Payroll &amp; Honor</span>
                </a>
            </div>
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

            <button type="submit" class="filterBtn" style="background: #1a3a5c; width: 100%;">Filter Data Presensi</button>
            <div style="display: flex; gap: 8px; width: 100%; flex-wrap: wrap;">
                <button type="submit" formtarget="_blank" formaction="{{ route('admin.laporan.exportExcel') }}"
                    class="filterBtn" style="background: #16a34a; flex:1;">
                    <ion-icon name="document-outline" style="vertical-align:middle;margin-right:2px;"></ion-icon> Excel
                </button>
                <button type="submit" formtarget="_blank" formaction="{{ route('admin.laporan.exportPdf') }}"
                    class="filterBtn" style="background: #dc2626; flex:1;">
                    <ion-icon name="document-text-outline" style="vertical-align:middle;margin-right:2px;"></ion-icon> PDF
                </button>
                <button type="button" onclick="document.getElementById('importPresensiModal').style.display='flex'"
                    class="filterBtn" style="background: #0284c7; flex:1;">
                    <ion-icon name="cloud-upload-outline" style="vertical-align:middle;margin-right:2px;"></ion-icon> Import Log
                </button>
            </div>
        </div>
    </form>

    {{-- ── Modal Impor Presensi Retroaktif / Log Manual ── --}}
    <div id="importPresensiModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:var(--card,#fff);border-radius:18px;max-width:480px;width:100%;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.2);border:1px solid var(--border,#e2e8f0);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="margin:0;font-size:17px;font-weight:800;color:var(--text);">Impor Rekapan Presensi Manual</h3>
                <button type="button" onclick="document.getElementById('importPresensiModal').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);">&times;</button>
            </div>
            <p style="font-size:13px;color:var(--muted);margin-bottom:16px;line-height:1.4;">
                Unggah berkas spreadsheet Excel/CSV untuk menyinkronkan rekapan data presensi fisik atau kegiatan offline luar jaringan secara massal.
            </p>
            <div style="margin-bottom:18px;">
                <a href="{{ route('admin.laporan.downloadTemplate') }}" class="btnOutline" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;font-size:12px;font-weight:700;">
                    <ion-icon name="download-outline"></ion-icon> Download Template Presensi (.xlsx)
                </a>
            </div>
            <form method="POST" action="{{ route('admin.laporan.importExcel') }}" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:11.5px;font-weight:800;text-transform:uppercase;margin-bottom:6px;color:var(--muted);">Pilih Berkas Rekap Presensi:</label>
                    <div class="fileUploadBox">
                        <input type="file" name="file_excel" id="laporanFileInput" accept=".xlsx,.xls,.csv" required onchange="handleFileSelected(this, 'laporanFileFeedback')">
                        <div class="fileUploadIcon">
                            <ion-icon name="cloud-upload-outline"></ion-icon>
                        </div>
                        <div class="fileUploadText">Pilih atau seret berkas ke sini</div>
                        <div class="fileUploadSubtext">
                            <span class="fileUploadInfoPill">Format: .XLSX, .CSV</span>
                            <span class="fileUploadInfoPill">Maks: 5 MB</span>
                        </div>
                    </div>
                    <div id="laporanFileFeedback" class="fileUploadFeedback"></div>
                </div>
                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <button type="button" onclick="document.getElementById('importPresensiModal').style.display='none'" class="profileBtnDanger" style="height:38px;padding:0 14px;font-size:12px;border-radius:10px;width:auto;">Batal</button>
                    <button type="submit" class="profileBtnPrimary" style="height:38px;padding:0 16px;font-size:12px;border-radius:10px;width:auto;">Unggah &amp; Impor Presensi</button>
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
