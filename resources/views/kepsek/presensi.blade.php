@extends('layout.kepsek')

@section('title', 'Data Presensi — Kepala Sekolah')

@section('content')
    <style>
        .monthFilter {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 14px 16px 20px;
        }

        .monthInput {
            flex: 1;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--input-bg);
            font-size: 13px;
            color: var(--text);
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
        }

        .tableContainer {
            background: var(--card);
            border-radius: 12px;
            overflow-x: auto;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
            margin: 0 16px 20px;
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
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .laporanTable td {
            padding: 12px 10px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            color: var(--text);
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
            border: 1px solid var(--border);
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
            cursor: pointer;
            border: none;
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
            max-width: 480px;
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
            font-size: 22px;
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
            min-height: 200px;
        }

        .modal-body img {
            max-width: 100%;
            border-radius: 10px;
        }

        .modal-body iframe {
            width: 100%;
            height: 320px;
            border: 0;
            border-radius: 10px;
        }
    </style>

    <div class="sectionTitleRow">
        <h2 style="font-size: 13px; font-weight: 700; color: var(--muted); letter-spacing: 1px; text-transform: uppercase;">
            Monitoring Presensi Tutor</h2>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('kepsek.presensi-tutor') }}">
        <div class="monthFilter" style="flex-wrap: wrap;">
            <input type="date" name="start_date" class="monthInput" value="{{ $startDateStr }}"
                style="flex:1; min-width: 140px; color-scheme: light dark;">
            <span style="font-size:12px; font-weight:bold; color: var(--muted); padding-top: 10px;">s/d</span>
            <input type="date" name="end_date" class="monthInput" value="{{ $endDateStr }}"
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

            <button type="submit" class="filterBtn" style="width: 100%;">Filter</button>
        </div>
    </form>

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
                @forelse($presensi as $index => $item)
                    @php
                        $tutorName = $item->tutor->nama_lengkap ?? 'Tutor';
                        $siswaName = $item->siswa->nama_siswa ?? '-';
                        $tgl = \Carbon\Carbon::parse($item->tgl_presensi)->format('d/m/y');
                        $jamMasuk = $item->jam_mulai ? \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') : '-';
                        $jamSelesai = $item->jam_selesai
                            ? \Carbon\Carbon::parse($item->jam_selesai)->format('H:i')
                            : '-';

                        $lokasi = $item->lokasi_mulai ?? '-';
                        $urlPeta =
                            $lokasi !== '-'
                                ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($lokasi)
                                : '#';
                    @endphp
                    <tr>
                        <td>{{ $presensi->firstItem() + $index }}</td>
                        <td>{{ $tgl }}</td>
                        <td style="font-weight: 600; color: var(--text);">{{ $tutorName }}</td>
                        <td>{{ $siswaName }}</td>
                        <td style="font-weight: 700; color: var(--success);">{{ $jamMasuk }}</td>
                        <td style="font-weight: 700; color: var(--blue2);">{{ $jamSelesai }}</td>
                        <td>
                            <div class="fotoStack">
                                @if ($item->foto_mulai)
                                    <img src="{{ asset($item->foto_mulai) }}" class="fotoThumbnail" title="Foto Mulai"
                                        onclick="openPhotoModal('{{ asset($item->foto_mulai) }}', 'Foto Masuk')"
                                        style="cursor:pointer;">
                                @else
                                    <div class="fotoPlaceholder">M -</div>
                                @endif

                                @if ($item->foto_selesai)
                                    <img src="{{ asset($item->foto_selesai) }}" class="fotoThumbnail" title="Foto Selesai"
                                        onclick="openPhotoModal('{{ asset($item->foto_selesai) }}', 'Foto Pulang')"
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
                        <td colspan="8" style="text-align: center; padding: 30px; color: #94a3b8;">Belum ada data
                            presensi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if (isset($karyawanPresensi) && $karyawanPresensi->isNotEmpty())
        <div class="sectionTitleRow">
            <h2
                style="font-size: 13px; font-weight: 700; color: var(--muted); letter-spacing: 1px; text-transform: uppercase;">
                Monitoring Presensi Karyawan (Admin/Kepsek)</h2>
        </div>
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
                            $kpJamMasuk = $kp->jam_mulai ? \Carbon\Carbon::parse($kp->jam_mulai)->format('H:i') : '-';
                            $kpJamSelesai = $kp->jam_selesai
                                ? \Carbon\Carbon::parse($kp->jam_selesai)->format('H:i')
                                : '-';
                            $kpName = $kp->user->nama_lengkap;
                            $kpRole = ucfirst($kp->user->role);
                            $lokasi = $kp->lokasi_mulai;
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
                                        <img src="{{ asset($kp->foto_mulai) }}" class="fotoThumbnail" title="Foto Mulai"
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
    @endif

    @if ($presensi->hasPages())
        <div class="contentPad" style="padding-bottom: 20px;">
            {{ $presensi->links('pagination::bootstrap-5') }}
        </div>
    @endif

    {{-- ── Modal Foto ── --}}
    <div class="modal-overlay" id="photoModal" onclick="if(event.target===this)closeModal('photoModal')">
        <div class="modal-box">
            <div class="modal-header">
                <span id="photoModalTitle">Foto</span>
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
