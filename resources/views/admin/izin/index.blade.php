@extends('layouts.admin')

@section('title', 'Kelola & Verifikasi Izin Tutor - Admin')

@section('content')

    <div class="laporanPageWrapper">
        {{-- ── Header Card ── --}}
        <div class="laporanHeader">
            <div class="laporanHeaderCard">
                <div class="laporanHeaderInfo">
                    <div class="d-flex items-center gap-2 flex-wrap mb-1">
                        <div class="laporanHeaderLabel m-0">MANAJEMEN PERIZINAN TUTOR</div>
                        @if($totalPending > 0)
                            <span class="badgeCount danger text-xs">
                                <ion-icon name="alert-circle-outline"></ion-icon>
                                <span>{{ $totalPending }} Menunggu Tindakan</span>
                            </span>
                        @else
                            <span class="badgeCount success text-xs">
                                <ion-icon name="checkmark-done-circle-outline"></ion-icon>
                                <span>Semua Terverifikasi</span>
                            </span>
                        @endif
                    </div>
                    <h1 class="laporanHeaderTitle">Kelola &amp; Verifikasi Izin Tutor</h1>
                    <div class="laporanHeaderSub">Pusat persetujuan izin/sakit mandiri &amp; pencatatan izin KBM tutor</div>
                    <p class="laporanHeaderDesc">Verifikasi permohonan izin/sakit tutor secara langsung atau berikan izin manual untuk sinkronisasi otomatis kalender KBM dan rekapitulasi kehadiran.</p>
                </div>
                <div class="laporanHeaderActions">
                    <a href="{{ route('admin.izin.index', ['tab' => 'manual']) }}" class="profileBtnPrimary btn-header-action">
                        <ion-icon name="create-outline"></ion-icon>
                        <span>Beri Izin Manual</span>
                    </a>
                    <a href="{{ route('admin.laporan.index', ['tab' => 'perizinan']) }}" class="profileBtnPrimary btnOutline btn-header-action">
                        <ion-icon name="stats-chart-outline"></ion-icon>
                        <span>Audit &amp; Rekap</span>
                    </a>
                </div>
            </div>
        </div>


        {{-- ── Tab Navigasi Terpadu ── --}}
        <div class="laporanNavTabsWrapper">
            <div class="laporanNavTabs">
                <a href="{{ route('admin.izin.index', ['tab' => 'pengajuan']) }}" class="laporanTabBtn {{ $activeTab === 'pengajuan' ? 'active' : '' }}">
                    <ion-icon name="mail-unread-outline"></ion-icon>
                    <span>Permohonan Masuk Tutor</span>
                    @if($totalPending > 0)
                        <span class="tabBadge danger">{{ $totalPending }}</span>
                    @else
                        <span class="tabBadge">{{ $totalPengajuan }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.izin.index', ['tab' => 'manual']) }}" class="laporanTabBtn {{ $activeTab === 'manual' ? 'active' : '' }}">
                    <ion-icon name="create-outline"></ion-icon>
                    <span>Input Izin Manual</span>
                </a>
                <a href="{{ route('admin.izin.index', ['tab' => 'log']) }}" class="laporanTabBtn {{ $activeTab === 'log' ? 'active' : '' }}">
                    <ion-icon name="calendar-outline"></ion-icon>
                    <span>Log Presensi Izin</span>
                    <span class="tabBadge">{{ $riwayatIzin->count() }}</span>
                </a>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════
             TAB 1: PERMOHONAN MASUK TUTOR (PENGAJUAN IZIN & SAKIT)
             ══════════════════════════════════════════════════════════════ --}}
        @if($activeTab === 'pengajuan')
            {{-- Summary Stats Row --}}
            <div class="statRow">
                <div class="statCard">
                    <div class="statNum blue">{{ $totalPengajuan }}</div>
                    <div class="statLabel">Total Pengajuan</div>
                </div>
                <div class="statCard {{ $totalPending > 0 ? 'border-warning' : '' }}">
                    <div class="statNum {{ $totalPending > 0 ? 'text-danger' : 'text-warning' }}">{{ $totalPending }}</div>
                    <div class="statLabel">Menunggu</div>
                </div>
                <div class="statCard">
                    <div class="statNum green">{{ $totalDisetujui }}</div>
                    <div class="statLabel">Disetujui</div>
                </div>
                <div class="statCard">
                    <div class="statNum text-muted">{{ $totalDitolak }}</div>
                    <div class="statLabel">Ditolak</div>
                </div>
            </div>

            {{-- Filter Bar --}}
            <div class="laporanFilterCard mb-4">
                <div class="laporanFilterHeaderMobile">
                    <div class="filterMobileSummary">
                        <span class="filterMobileIcon"><ion-icon name="filter-outline"></ion-icon></span>
                        <div class="filterMobileTexts">
                            <span class="filterMobileTitle">Parameter &amp; Filter Pengajuan</span>
                            <span class="filterMobileSub">{{ request('status') ? 'Status: ' . ucfirst(request('status')) : 'Semua Status' }}</span>
                        </div>
                    </div>
                    <button type="button" class="btnToggleFilterMobile" aria-expanded="false">
                        <span class="filterToggleText">Buka Filter</span>
                        <ion-icon name="chevron-down-outline" class="filterToggleIcon"></ion-icon>
                    </button>
                </div>
                <form method="GET" action="{{ route('admin.izin.index') }}" class="laporanFilterFormBody">
                    <input type="hidden" name="tab" value="pengajuan">
                    <div class="laporanFilterGrid">
                        <div class="filterField">
                            <label class="filterFieldLabel">Status Verifikasi</label>
                            <select name="status" class="filterSelect" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>
                                    Menunggu Verifikasi ({{ $totalPending }})
                                </option>
                                <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>
                                    Disetujui ({{ $totalDisetujui }})
                                </option>
                                <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>
                                    Ditolak ({{ $totalDitolak }})
                                </option>
                            </select>
                        </div>

                        <div class="filterField">
                            <label class="filterFieldLabel">Jenis Pengajuan</label>
                            <select name="jenis" class="filterSelect" onchange="this.form.submit()">
                                <option value="">Semua Jenis</option>
                                <option value="izin" {{ request('jenis') === 'izin' ? 'selected' : '' }}>Izin Keperluan</option>
                                <option value="sakit" {{ request('jenis') === 'sakit' ? 'selected' : '' }}>Sakit / Rawat</option>
                            </select>
                        </div>

                        <div class="filterField">
                            <label class="filterFieldLabel">Cari Nama Tutor</label>
                            <input type="text" name="cari" class="profileInput text-md" value="{{ request('cari') }}"
                                placeholder="Ketik nama tutor...">
                        </div>

                        <div class="filter-actions-full">
                            <button type="submit" class="btn-filter-primary">
                                <ion-icon name="filter-outline"></ion-icon> Terapkan Filter
                            </button>
                            <a href="{{ route('admin.izin.index', ['tab' => 'pengajuan']) }}" class="btn-filter-reset">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- List Permohonan Izin / Sakit --}}
            <div class="sectionRow">
                <div class="sectionTitleWrap">
                    <div class="sectionTitleLabel">DAFTAR PERMOHONAN</div>
                    <h2 class="sectionTitle">Permohonan Izin &amp; Sakit Masuk</h2>
                </div>
                <div class="sectionBadge">
                    <ion-icon name="document-text-outline"></ion-icon>
                    <span>{{ $pengajuanItems->total() }} Data</span>
                </div>
            </div>

            <div class="kllList">
                @forelse($pengajuanItems as $item)
                    @php
                        $tutorNama = $item->tutor->nama_lengkap ?? 'Tutor Tidak Ditemukan';
                        $initials = strtoupper(substr($tutorNama, 0, 2));
                        $tglMulai = \Carbon\Carbon::parse($item->tgl_mulai)->translatedFormat('d M Y');
                        $tglSelesai = \Carbon\Carbon::parse($item->tgl_selesai)->translatedFormat('d M Y');
                    @endphp
                    <div class="kllCard {{ $item->status === 'pending' ? 'border-pending-highlight' : '' }}">
                        {{-- Header Card --}}
                        <div class="kllCardStrip">
                            <div class="d-flex items-center gap-2 min-w-0 flex-1">
                                <div class="tutorAvatar text-sm avatar-icon-36">{{ $initials }}</div>
                                <div class="kllTutorInfo">
                                    <div class="kllTutorName">{{ $tutorNama }}</div>
                                    <div class="kllTutorId">
                                        <ion-icon name="calendar-outline"></ion-icon>
                                        {{ $tglMulai }}
                                        @if($item->tgl_mulai !== $item->tgl_selesai)
                                            &mdash; {{ $tglSelesai }}
                                        @endif
                                        @if($item->tutor?->nik)
                                            <span class="text-xs text-muted">({{ $item->tutor->nik }})</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-col items-end gap-1">
                                <span class="badgeBadge {{ $item->jenis === 'sakit' ? 'sakit' : 'izin' }}">
                                    {{ strtoupper($item->jenis_label) }}
                                </span>
                                @if($item->status === 'pending')
                                    <span class="badgeStatus pending">
                                        <ion-icon name="time-outline"></ion-icon> MENUNGGU VERIFIKASI
                                    </span>
                                @elseif($item->status === 'disetujui')
                                    <span class="badgeStatus disetujui">
                                        <ion-icon name="checkmark-circle-outline"></ion-icon> DISETUJUI
                                    </span>
                                @else
                                    <span class="badgeStatus ditolak">
                                        <ion-icon name="close-circle-outline"></ion-icon> DITOLAK
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Body Card --}}
                        <div class="kllCardBody">
                            <div class="kllAlasanWrap">
                                <div class="kllAlasanLbl">Alasan / Keterangan Tutor:</div>
                                <div class="kllAlasanTxt">{{ $item->alasan }}</div>
                            </div>

                            @if($item->dokumen_url)
                                <div class="mt-2">
                                    <a href="{{ $item->dokumen_url }}" target="_blank" class="btn-dokumen-lampiran">
                                        <ion-icon name="document-attach-outline"></ion-icon>
                                        <span>Lihat Dokumen Lampiran</span>
                                    </a>
                                </div>
                            @endif

                            @if($item->verifikator || $item->catatan_verifikasi)
                                <div class="mt-2 pt-2 border-top text-xs text-muted d-flex items-center gap-1">
                                    <ion-icon name="shield-checkmark-outline"></ion-icon>
                                    <span>
                                        Diverifikasi oleh: <strong>{{ $item->verifikator->name ?? 'Admin / Kepsek' }}</strong>
                                        @if($item->catatan_verifikasi)
                                            &bull; Catatan: <em>"{{ $item->catatan_verifikasi }}"</em>
                                        @endif
                                    </span>
                                </div>
                            @endif

                            {{-- Actions --}}
                            @if($item->status === 'pending')
                                <div class="kllActions">
                                    <form action="{{ route('admin.pengajuan-izin.setujui', $item->id) }}" method="POST"
                                        class="kllActionForm"
                                        data-confirm="Setujui permohonan izin/sakit tutor ini? Catatan presensi otomatis disinkronkan ke kalender KBM."
                                        data-confirm-title="Setujui Permohonan Izin" data-confirm-type="success"
                                        data-confirm-btn="Ya, Setujui">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-kll-success">
                                            <ion-icon name="checkmark-circle-outline"></ion-icon> <span>Setujui</span>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.pengajuan-izin.tolak', $item->id) }}" method="POST"
                                        class="kllActionForm"
                                        data-confirm="Tolak permohonan izin tutor ini?"
                                        data-confirm-title="Tolak Permohonan Izin" data-confirm-type="danger"
                                        data-confirm-btn="Ya, Tolak">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-kll-danger">
                                            <ion-icon name="close-circle-outline"></ion-icon> <span>Tolak</span>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.pengajuan-izin.destroy', $item->id) }}" method="POST"
                                        class="kllDeleteForm"
                                        data-confirm="Hapus data arsip pengajuan izin ini?"
                                        data-confirm-title="Hapus Pengajuan" data-confirm-type="danger"
                                        data-confirm-btn="Hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-kll-delete" title="Hapus Pengajuan">
                                            <ion-icon name="trash-outline"></ion-icon>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="mt-3 pt-2 text-sm text-muted d-flex items-center justify-between border-t-dashed">
                                    <div class="d-flex items-center gap-1 text-xs">
                                        <ion-icon name="shield-checkmark-outline" class="text-success text-base"></ion-icon>
                                        <span>Diverifikasi: <strong>{{ $item->verifikator->name ?? 'Admin / Kepsek' }}</strong></span>
                                    </div>
                                    <form action="{{ route('admin.pengajuan-izin.destroy', $item->id) }}" method="POST"
                                        data-confirm="Hapus data arsip pengajuan izin ini?"
                                        data-confirm-title="Hapus Pengajuan" data-confirm-type="danger"
                                        data-confirm-btn="Hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btnBatal text-xs" title="Hapus Pengajuan">
                                            <ion-icon name="trash-outline"></ion-icon> <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state-standard">
                        <ion-icon name="document-text-outline" class="empty-icon"></ion-icon>
                        <div class="empty-title">Tidak Ada Data Izin/Sakit</div>
                        <div class="empty-desc">
                            {{ request('cari') || request('status') || request('jenis') ? 'Tidak ada permohonan yang cocok dengan filter pencarian.' : 'Belum ada permohonan izin atau surat sakit dari tutor saat ini.' }}
                        </div>
                    </div>
                @endforelse
            </div>

            @if($pengajuanItems->hasPages())
                <div class="paginatePad">
                    {{ $pengajuanItems->withQueryString()->links() }}
                </div>
            @endif

        {{-- ══════════════════════════════════════════════════════════════
             TAB 2: FORMULIR INPUT IZIN MANUAL
             ══════════════════════════════════════════════════════════════ --}}
        @elseif($activeTab === 'manual')
            <div class="panelCard">
                <div class="panelCardHead">
                    <div class="panelCardHeadIcon">
                        <ion-icon name="shield-checkmark-outline"></ion-icon>
                    </div>
                    <div>
                        <div class="panelCardHeadTitle">Formulir Beri Izin Langsung</div>
                        <div class="panelCardHeadSub">Pilih tutor, tanggal, dan siswa bimbingan untuk mencatatkan presensi izin secara manual</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.izin.store') }}" id="izinForm">
                    @csrf
                    <div class="panelCardBody">
                        <div class="formGrid2">
                            {{-- Pilih Tutor --}}
                            <div class="fieldGroup">
                                <label class="fieldLabel" for="tutorSelect">Pilih Tutor <span class="text-danger">*</span></label>
                                <select name="tutor_id" id="tutorSelect" class="selectInput searchable-select" data-searchable="true" data-placeholder="Cari & Pilih Tutor..." required>
                                    <option value="">-- Pilih Tutor --</option>
                                    @foreach($tutors as $tutor)
                                        <option value="{{ $tutor->id }}" {{ old('tutor_id') == $tutor->id ? 'selected' : '' }}>
                                            {{ $tutor->nama_lengkap }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Pilih Tanggal --}}
                            <div class="fieldGroup">
                                <label class="fieldLabel" for="tanggalInput">Tanggal Izin <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" id="tanggalInput" class="dateInput"
                                    value="{{ old('tanggal', date('Y-m-d')) }}" required>
                            </div>
                        </div>

                        {{-- Daftar Siswa (muncul via AJAX) --}}
                        <div class="fieldGroup">
                            <div class="fieldLabel">Pilih Siswa / Murid <span class="fieldLabelHint">(bisa pilih lebih dari 1)</span>
                            </div>
                            <div class="siswaCheckList" id="siswaList">
                                <div class="siswaCheckEmpty" id="siswaPlaceholder">
                                    <ion-icon name="person-outline"></ion-icon>
                                    <span>Pilih tutor terlebih dahulu untuk memuat daftar siswa bimbingan</span>
                                </div>
                            </div>
                        </div>

                        {{-- Jenis Izin: fixed izin --}}
                        <input type="hidden" name="jenis" value="izin">

                        <button type="submit" class="profileBtnPrimary btnSubmitIzinFull" id="btnSubmit" disabled>
                            <ion-icon name="checkmark-circle-outline"></ion-icon> Simpan Izin Tutor Manual
                        </button>
                    </div>
                </form>
            </div>

        {{-- ══════════════════════════════════════════════════════════════
             TAB 3: LOG PRESENSI IZIN TERCATAT
             ══════════════════════════════════════════════════════════════ --}}
        @elseif($activeTab === 'log')
            <div class="sectionRow">
                <div class="sectionTitleWrap">
                    <div class="sectionTitleLabel">RIWAYAT PRESENSI</div>
                    <h2 class="sectionTitle">Daftar Sesi Berstatus Izin Tercatat</h2>
                </div>
                <div class="sectionBadge">
                    <ion-icon name="time-outline"></ion-icon>
                    <span>{{ $riwayatIzin->count() }} Data Sesi</span>
                </div>
            </div>

            <div class="riwayatList">
                @forelse($riwayatIzin as $item)
                    @php
                        $tutorName = $item->tutor->nama_lengkap ?? 'Tutor';
                        $siswaName = $item->murid->nama_murid ?? ($item->siswa->nama_siswa ?? '-');
                        $initial = strtoupper(substr($tutorName, 0, 1));
                        $tgl = \Carbon\Carbon::parse($item->tgl_presensi)->translatedFormat('d M Y');
                    @endphp
                    <div class="riwayatRow">
                        <div class="riwayatLeft">
                            <div class="riwayatAvatar">{{ $initial }}</div>
                            <div class="riwayatInfo">
                                <div class="riwayatName">{{ $tutorName }}</div>
                                <div class="riwayatMeta">
                                    <span class="riwayatMetaItem"><ion-icon name="person-outline"></ion-icon>
                                        {{ $siswaName }}</span>
                                    <span class="riwayatMetaDivider">&bull;</span>
                                    <span class="riwayatMetaItem"><ion-icon name="calendar-outline"></ion-icon> {{ $tgl }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="riwayatRight">
                            <span class="badgeIzinPill">
                                <ion-icon name="time-outline"></ion-icon> Izin
                            </span>
                            <form method="POST" action="{{ route('admin.izin.destroy', $item->id) }}"
                                data-confirm="Apakah Anda yakin ingin membatalkan izin presensi ini?" data-confirm-title="Batalkan Izin"
                                data-confirm-type="danger" data-confirm-btn="Ya, Batalkan">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btnBatal" title="Batalkan Izin">
                                    <ion-icon name="trash-outline"></ion-icon>
                                    <span>Batalkan</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="empty-state-standard">
                        <ion-icon name="calendar-outline" class="empty-icon"></ion-icon>
                        <div class="empty-title">Belum Ada Presensi Berstatus Izin</div>
                        <div class="empty-desc">Log presensi tutor yang berstatus izin akan tampil di sini.</div>
                    </div>
                @endforelse
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    <script>
        const tutorSelect = document.getElementById('tutorSelect');
        const siswaList = document.getElementById('siswaList');
        const placeholder = document.getElementById('siswaPlaceholder');
        const btnSubmit = document.getElementById('btnSubmit');
        const ajaxBase = '{{ url("/admin/izin/siswa") }}';

        if (tutorSelect && siswaList && btnSubmit) {
            // Load siswa saat tutor berubah
            tutorSelect.addEventListener('change', function () {
                const tutorId = this.value;
                if (!tutorId) {
                    siswaList.innerHTML = '<div class="siswaCheckEmpty" id="siswaPlaceholder"><ion-icon name="person-outline"></ion-icon><span>Pilih tutor terlebih dahulu untuk memuat daftar siswa bimbingan</span></div>';
                    btnSubmit.disabled = true;
                    return;
                }

                siswaList.innerHTML = '<div class="siswaCheckEmpty"><ion-icon name="sync-outline" class="spinIcon"></ion-icon><span>Memuat daftar siswa bimbingan...</span></div>';

                fetch(`${ajaxBase}/${tutorId}`)
                    .then(r => r.json())
                    .then(data => {
                        if (!data.length) {
                            siswaList.innerHTML = '<div class="siswaCheckEmpty"><ion-icon name="alert-circle-outline"></ion-icon><span>Tidak ada siswa bimbingan aktif untuk tutor ini.</span></div>';
                            btnSubmit.disabled = true;
                            return;
                        }

                        siswaList.innerHTML = data.map(s => `
                            <label class="siswaCheckItem">
                                <input type="checkbox" name="siswa_ids[]" value="${s.id}" onchange="checkSubmit()">
                                <span class="siswaCheckName">${s.nama_murid || s.nama_siswa}</span>
                            </label>
                        `).join('');

                        checkSubmit();
                    })
                    .catch(() => {
                        siswaList.innerHTML = '<div class="siswaCheckEmpty"><ion-icon name="close-circle-outline"></ion-icon><span>Gagal memuat data siswa bimbingan.</span></div>';
                        btnSubmit.disabled = true;
                    });
            });

            function checkSubmit() {
                const any = document.querySelector('input[name="siswa_ids[]"]:checked');
                btnSubmit.disabled = !any;
            }

            // Aktifkan jika ada old value
            document.addEventListener('DOMContentLoaded', () => {
                if (tutorSelect.value) tutorSelect.dispatchEvent(new Event('change'));
            });
        }
    </script>
@endpush