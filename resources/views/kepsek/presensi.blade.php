@extends('layouts.kepsek')

@section('title', 'Data Presensi — Kepala Sekolah')

@section('content')


    <div class="pageHeaderRow" style="padding: 16px 16px 6px;">
        <div>
            <h2 style="margin: 0; font-size: 18px; font-weight: 800; color: var(--text);">Monitoring Presensi Tutor</h2>
            <p style="margin: 2px 0 0; font-size: 12px; color: var(--muted);">Log presensi mengajar tutor dan siswa PKBM</p>
        </div>
    </div>

    {{-- Filter Card --}}
    <div style="margin: 0 16px 16px; background: var(--card); border: 1px solid var(--border); border-radius: 18px; padding: 14px;">
        <form method="GET" action="{{ route('kepsek.presensi-tutor') }}">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; align-items: end;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--muted); margin-bottom: 4px;">Dari Tanggal</label>
                    <input type="date" name="start_date" class="profileInput" value="{{ $startDateStr }}" style="height: 40px; font-size: 12.5px;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--muted); margin-bottom: 4px;">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="profileInput" value="{{ $endDateStr }}" style="height: 40px; font-size: 12.5px;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--muted); margin-bottom: 4px;">Tutor</label>
                    <select name="tutor_id" class="profileInput" style="height: 40px; font-size: 12.5px;">
                        <option value="">Semua Tutor</option>
                        @foreach ($tutors as $tutor)
                            <option value="{{ $tutor->id }}" {{ $tutorId == $tutor->id ? 'selected' : '' }}>
                                {{ $tutor->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--muted); margin-bottom: 4px;">Siswa</label>
                    <select name="siswa_id" class="profileInput" style="height: 40px; font-size: 12.5px;">
                        <option value="">Semua Siswa</option>
                        @foreach ($siswas as $siswa)
                            <option value="{{ $siswa->id }}" {{ $siswaId == $siswa->id ? 'selected' : '' }}>
                                {{ $siswa->nama_siswa }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--muted); margin-bottom: 4px;">Status</label>
                    <select name="status" class="profileInput" style="height: 40px; font-size: 12.5px;">
                        <option value="">Semua Status</option>
                        <option value="hadir" {{ $statusFilter == 'hadir' ? 'selected' : '' }}>Hadir (Selesai)</option>
                        <option value="proses" {{ $statusFilter == 'proses' ? 'selected' : '' }}>Sedang Berjalan</option>
                        <option value="izin" {{ $statusFilter == 'izin' ? 'selected' : '' }}>Izin/Sakit</option>
                    </select>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="profileBtnPrimary" style="height: 40px; padding: 0 16px; font-size: 12.5px; border-radius: 12px; flex: 1;">
                        <ion-icon name="filter-outline"></ion-icon> Filter
                    </button>
                    <a href="{{ route('kepsek.presensi-tutor') }}" class="profileBtnDanger" style="height: 40px; padding: 0 12px; font-size: 12.5px; border-radius: 12px; width: auto; text-decoration: none;">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="tableContainer" style="margin: 0 16px 20px; border-radius: 18px; border: 1px solid var(--border);">
        <table class="laporanTable">
            <thead>
                <tr>
                    <th style="padding: 12px 14px;">NO</th>
                    <th>TANGGAL</th>
                    <th>TUTOR</th>
                    <th>SISWA</th>
                    <th>MASUK</th>
                    <th>SELESAI</th>
                    <th>FOTO</th>
                    <th style="text-align: center;">LOKASI</th>
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
                        <td style="padding: 12px 14px; font-weight: 700; color: var(--muted);">{{ $presensi->firstItem() + $index }}</td>
                        <td style="font-weight: 700; white-space: nowrap;">{{ $tgl }}</td>
                        <td style="font-weight: 800; color: var(--text);">{{ $tutorName }}</td>
                        <td style="font-weight: 600;">{{ $siswaName }}</td>
                        <td style="font-weight: 800; color: #15803d;">{{ $jamMasuk }}</td>
                        <td style="font-weight: 800; color: var(--blue2);">{{ $jamSelesai }}</td>
                        <td>
                            <div class="fotoStack">
                                @if ($item->foto_mulai)
                                    <img src="{{ asset($item->foto_mulai) }}" class="fotoThumbnail cursor-pointer" title="Foto Mulai"
                                        onclick="openPhotoModal('{{ asset($item->foto_mulai) }}', 'Foto Masuk — {{ $tutorName }}')">
                                @else
                                    <div class="fotoPlaceholder">M -</div>
                                @endif

                                @if ($item->foto_selesai)
                                    <img src="{{ asset($item->foto_selesai) }}" class="fotoThumbnail cursor-pointer" title="Foto Selesai"
                                        onclick="openPhotoModal('{{ asset($item->foto_selesai) }}', 'Foto Pulang — {{ $tutorName }}')">
                                @else
                                    <div class="fotoPlaceholder">S -</div>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: center;">
                            @if ($lokasi !== '-')
                                <button type="button" class="mapBtn" title="Lihat Peta"
                                    onclick="openMapModal('{{ $lokasi }}')">
                                    <ion-icon name="map-outline"></ion-icon>
                                </button>
                            @else
                                <span style="color: var(--muted);">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--muted); padding: 32px 16px;">
                            <ion-icon name="calendar-outline" style="font-size: 32px; opacity: 0.4; display: block; margin: 0 auto 6px;"></ion-icon>
                            Belum ada data presensi pada periode filter ini.
                        </td>
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
