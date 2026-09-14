@extends('layouts.kepsek')

@section('title', 'Data Presensi — Kepala Sekolah')

@section('content')


    <div class="sectionTitleRow">
        <h2 class="sectionTitle">Monitoring Presensi Tutor</h2>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('kepsek.presensi-tutor') }}">
        <div class="monthFilter" style="flex-wrap: wrap;">
            <input type="date" name="start_date" class="monthInput filterInput--flex" value="{{ $startDateStr }}"
                style="color-scheme: light dark;">
            <span class="td-muted" style="padding-top: 10px; font-size:12px; font-weight:bold;">s/d</span>
            <input type="date" name="end_date" class="monthInput filterInput--flex" value="{{ $endDateStr }}"
                style="color-scheme: light dark;">

            <select name="tutor_id" class="monthInput filterInput--flex">
                <option value="">Semua Tutor</option>
                @foreach ($tutors as $tutor)
                    <option value="{{ $tutor->id }}" {{ $tutorId == $tutor->id ? 'selected' : '' }}>
                        {{ $tutor->nama_lengkap }}</option>
                @endforeach
            </select>

            <select name="siswa_id" class="monthInput filterInput--flex">
                <option value="">Semua Siswa</option>
                @foreach ($siswas as $siswa)
                    <option value="{{ $siswa->id }}" {{ $siswaId == $siswa->id ? 'selected' : '' }}>
                        {{ $siswa->nama_siswa }}</option>
                @endforeach
            </select>

            <select name="status" class="monthInput filterInput--flex">
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
                        <td class="td-bold">{{ $tutorName }}</td>
                        <td>{{ $siswaName }}</td>
                        <td class="td-success">{{ $jamMasuk }}</td>
                        <td class="td-primary">{{ $jamSelesai }}</td>
                        <td>
                            <div class="fotoStack">
                                @if ($item->foto_mulai)
                                    <img src="{{ asset($item->foto_mulai) }}" class="fotoThumbnail cursor-pointer" title="Foto Mulai"
                                        onclick="openPhotoModal('{{ asset($item->foto_mulai) }}', 'Foto Masuk')">
                                @else
                                    <div class="fotoPlaceholder">M -</div>
                                @endif

                                @if ($item->foto_selesai)
                                    <img src="{{ asset($item->foto_selesai) }}" class="fotoThumbnail cursor-pointer" title="Foto Selesai"
                                        onclick="openPhotoModal('{{ asset($item->foto_selesai) }}', 'Foto Pulang')">
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
                        <td colspan="8" class="td-center td-muted" style="padding: 30px;">Belum ada data presensi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if (isset($karyawanPresensi) && $karyawanPresensi->isNotEmpty())
        <div class="sectionTitleRow">
            <h2 class="sectionTitle">Monitoring Presensi Karyawan (Admin/Kepsek)</h2>
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
