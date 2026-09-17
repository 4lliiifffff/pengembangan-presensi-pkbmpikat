@extends('layouts.presensi')

@section('title', 'Jadwal Sesi Belajar & Pengganti — Tutor')

@php
    use Carbon\Carbon;
    $user = auth()->user();
    $displayName = (string) (($user->nama_lengkap ?? $user->name) ?? 'Tutor');

    $tz = 'Asia/Jakarta';
    $todayDate = Carbon::today($tz);
    $selectedDate = $selectedDate ?? Carbon::parse(request('tanggal', $todayDate->toDateString()))->startOfDay();

    $prevMonthDate = $selectedDate->copy()->subMonth()->toDateString();
    $nextMonthDate = $selectedDate->copy()->addMonth()->toDateString();

    $firstDayOfMonth = $selectedDate->copy()->startOfMonth();
    $startDayOfWeek = $firstDayOfMonth->dayOfWeekIso;
    $emptyCellsCount = $startDayOfWeek - 1;

    $dayNames = [
        ['code' => 'SEN', 'sunday' => false],
        ['code' => 'SEL', 'sunday' => false],
        ['code' => 'RAB', 'sunday' => false],
        ['code' => 'KAM', 'sunday' => false],
        ['code' => 'JUM', 'sunday' => false],
        ['code' => 'SAB', 'sunday' => true],
        ['code' => 'MIN', 'sunday' => true],
    ];
@endphp

@push('topbar')
    @include('layouts.components.navigasi_atas', [
        'titleName' => 'Jadwal Sesi & Pengganti',
        'subTitle' => $displayName . ' • Perencanaan KBM',
        'dashRoute' => route('tutor.dashboard'),
        'backRoute' => route('tutor.dashboard'),
    ])
@endpush

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">PERENCANAAN KBM &amp; RESCHEDULE</div>
                <h1 class="laporanHeaderTitle">Jadwal Sesi &amp; Pengganti</h1>
                <div class="laporanHeaderSub">Atur jam belajar murid, siapkan sesi pengganti (*make-up class*), dan sinkronisasi presensi</div>
                <p class="laporanHeaderDesc">Sesi yang dijadwalkan di sini akan otomatis menjadi acuan jam masuk presensi foto tutor tanpa false flag keterlambatan.</p>
            </div>
            <div class="laporanHeaderActions header-actions-group">
                <button type="button" onclick="bukaModalJadwalPengganti()" class="profileBtnPrimary">
                    <ion-icon name="add-circle-outline" class="icon-sm"></ion-icon> + Buat Jadwal Pengganti
                </button>
            </div>
        </div>
    </div>

    {{-- ── Tab Switcher Navigasi ── --}}
    <div class="d-flex gap-2 mb-4">
        <a href="{{ route('tutor.jadwal-sesi.index') }}" class="profileBtnPrimary text-xs py-2 px-3">
            <ion-icon name="calendar"></ion-icon> Sesi Belajar Murid
        </a>
        <a href="{{ route('tutor.jadwal') }}" class="profileBtnSecondary text-xs py-2 px-3">
            <ion-icon name="megaphone-outline"></ion-icon> Agenda &amp; Pengumuman PKBM
        </a>
    </div>

    <div class="max-w-4xl mx-auto pb-6">
        {{-- ── Kalender Sesi Bulanan ── --}}
        <div class="agendaHeaderCard mb-4">
            <div class="agendaMonthNav">
                <a href="{{ route('tutor.jadwal-sesi.index', ['tanggal' => $prevMonthDate]) }}" class="agendaNavBtn" title="Bulan Sebelumnya">
                    <ion-icon name="chevron-back-outline"></ion-icon>
                </a>
                <div class="text-center">
                    <h3 class="agendaMonthTitle">{{ $selectedDate->translatedFormat('F Y') }}</h3>
                    <span class="text-xs font-bold text-muted">Jadwal Sesi Mengajar Tutor</span>
                </div>
                <a href="{{ route('tutor.jadwal-sesi.index', ['tanggal' => $nextMonthDate]) }}" class="agendaNavBtn" title="Bulan Berikutnya">
                    <ion-icon name="chevron-forward-outline"></ion-icon>
                </a>
            </div>

            <div class="agendaMonthGrid mb-1">
                @foreach($dayNames as $dName)
                    <div class="agendaDayNameHeader {{ $dName['sunday'] ? 'sunday' : '' }}">
                        {{ $dName['code'] }}
                    </div>
                @endforeach
            </div>

            <div class="agendaMonthGrid">
                @for($i = 0; $i < $emptyCellsCount; $i++)
                    <div class="agendaDayCell empty"></div>
                @endfor

                @foreach($monthDays as $d)
                    @php
                        $dStr = $d->format('Y-m-d');
                        $isActive = $d->isSameDay($selectedDate);
                        $isCellToday = $d->isToday();
                        $sesiCount = (int) ($monthCounts[$dStr] ?? 0);
                        $hasEvent = $sesiCount > 0;
                    @endphp
                    <a href="{{ route('tutor.jadwal-sesi.index', ['tanggal' => $dStr]) }}"
                        class="agendaDayCell {{ $isActive ? 'active' : '' }} {{ $isCellToday ? 'today' : '' }} {{ $hasEvent ? 'hasEvent' : '' }}"
                        title="{{ $d->translatedFormat('d F Y') }} ({{ $sesiCount }} Sesi)">
                        <span class="agendaDayNum">{{ $d->day }}</span>
                        @if($hasEvent)
                            <span class="agendaEventIndicatorWrap">
                                <span class="agendaEventDot"></span>
                                @if($sesiCount > 1)
                                    <span class="agendaEventCountText">{{ $sesiCount }}</span>
                                @endif
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        {{-- ── Daftar Sesi Pada Tanggal Terpilih ── --}}
        <div class="mb-4">
            <div class="d-flex items-center justify-between mb-3">
                <h2 class="text-md font-extrabold text-dark">
                    <ion-icon name="time-outline" class="text-primary"></ion-icon>
                    Sesi: {{ $selectedDate->translatedFormat('l, d F Y') }}
                </h2>
                <span class="badgeCount">{{ $sesiHariIni->count() }} Sesi</span>
            </div>

            <div class="mobile-card-list">
                @forelse($sesiHariIni as $sesi)
                    <div class="data-mobile-card">
                        <div class="dmc-header">
                            <div>
                                <h3 class="dmc-title">{{ $sesi->siswa->nama_siswa ?? 'Peserta Didik' }}</h3>
                                <div class="dmc-subtitle">
                                    <span class="app-badge {{ $sesi->jenis_sesi === 'pengganti' ? 'badge-layanan-dl' : 'badge-layanan-komunitas' }}">
                                        {{ $sesi->jenis_label }}
                                    </span>
                                    @if($sesi->kategoriTutorial)
                                        <span class="text-xs text-muted ml-1">&bull; {{ $sesi->kategoriTutorial->nama_kategori }}</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                @if($sesi->status === 'selesai')
                                    <span class="app-badge badge-status-aktif">Selesai</span>
                                @elseif($sesi->status === 'dibatalkan')
                                    <span class="app-badge badge-status-nonaktif">Dibatalkan</span>
                                @else
                                    <span class="app-badge badge-reguler">Terjadwal</span>
                                @endif
                            </div>
                        </div>

                        <div class="dmc-grid">
                            <div class="dmc-field">
                                <div class="dmc-label">Jam Rencana</div>
                                <div class="dmc-value">
                                    <span class="font-bold text-dark text-sm">{{ $sesi->jam_masuk_formatted }} - {{ $sesi->jam_pulang_formatted }} WIB</span>
                                    <span class="text-xs text-muted">({{ $sesi->durasi_jam }} Jam)</span>
                                </div>
                            </div>

                            @if($sesi->tanggal_asli)
                                <div class="dmc-field">
                                    <div class="dmc-label">Menggantikan Sesi</div>
                                    <div class="dmc-value">
                                        <span class="font-bold text-primary text-xs">{{ $sesi->tanggal_asli->translatedFormat('d M Y') }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($sesi->alasan_penggantian)
                            <div class="text-xs text-muted mt-2 pt-2 border-t-base">
                                <span class="font-semibold text-dark">Alasan Reschedule:</span> {{ $sesi->alasan_penggantian }}
                            </div>
                        @endif

                        @if($sesi->catatan)
                            <div class="text-xs text-muted mt-1">
                                <span class="font-semibold text-dark">Catatan:</span> {{ $sesi->catatan }}
                            </div>
                        @endif

                        <div class="dmc-actions mt-3">
                            @if($sesi->status === 'terjadwal')
                                <a href="{{ route('tutor.presensi') }}" class="profileBtnPrimary text-xs py-1 px-3">
                                    <ion-icon name="camera-outline"></ion-icon> Absen Sesi Ini
                                </a>
                                <form method="POST" action="{{ route('tutor.jadwal-sesi.destroy', $sesi) }}" data-confirm="Batalkan sesi mengajar ini?" data-confirm-title="Batalkan Sesi" data-confirm-type="danger" data-confirm-btn="Ya, Batalkan" class="d-inline m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="smallBtn delete cursor-pointer btn-table-action" title="Batalkan Sesi">
                                        <ion-icon name="close-circle-outline"></ion-icon> Batalkan
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-success font-bold d-flex items-center gap-1">
                                    <ion-icon name="checkmark-done-circle"></ion-icon> Presensi Sesi Tersimpan
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="tableEmptyState p-4 bg-card rounded-xl border-base text-center">
                        <ion-icon name="calendar-outline" class="tableEmptyIcon"></ion-icon>
                        <div class="tableEmptyTitle">Belum Ada Sesi Terjadwal</div>
                        <div class="tableEmptyDesc">Tidak ada sesi mengajar terdaftar pada tanggal ini. Klik tombol "+ Buat Jadwal Pengganti" untuk merencanakan sesi.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Form Tambah Jadwal Sesi / Pengganti ── --}}
<div id="modalJadwalPengganti" class="navDrawerBackdrop" onclick="if(event.target === this) tutupModalJadwalPengganti()">
    <div class="navDrawerSheet max-w-lg mx-auto" onclick="event.stopPropagation()">
        <div class="navDrawerHandle"></div>

        <div class="navDrawerHeader">
            <div class="navDrawerTitleWrap">
                <div class="navDrawerSub">Perencanaan KBM &bull; Make-up Class</div>
                <h3 class="navDrawerTitle">Buat Jadwal Sesi / Pengganti</h3>
            </div>
            <button type="button" class="navDrawerCloseBtn" onclick="tutupModalJadwalPengganti()" aria-label="Tutup">
                <ion-icon name="close"></ion-icon>
            </button>
        </div>

        <div class="navDrawerBody">
            <form method="POST" action="{{ route('tutor.jadwal-sesi.store') }}">
                @csrf

                <div class="form-field-wrapper mb-3">
                    <label class="filterFieldLabel">Pilih Murid / Siswa <span class="text-danger">*</span></label>
                    <select name="siswa_id" class="filterSelect" required>
                        <option value="">-- Pilih Siswa Bimbingan --</option>
                        @foreach($siswas as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_siswa }} ({{ $s->kelas->nama_kelas ?? 'Umum' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field-wrapper mb-3">
                    <label class="filterFieldLabel">Jenis Sesi <span class="text-danger">*</span></label>
                    <select name="jenis_sesi" id="modalJenisSesi" onchange="toggleTanggalAsli()" class="filterSelect" required>
                        <option value="pengganti" selected>Sesi Pengganti (Make-up Class / Reschedule)</option>
                        <option value="reguler">Sesi Reguler Terjadwal</option>
                        <option value="tambahan">Sesi Tambahan / Pengayaan</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Tanggal Rencana <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_rencana" value="{{ $selectedDate->format('Y-m-d') }}" class="profileInput" required>
                    </div>

                    <div class="form-field-wrapper" id="boxTanggalAsli">
                        <label class="filterFieldLabel">Menggantikan Sesi Tanggal</label>
                        <input type="date" name="tanggal_asli" class="profileInput">
                        <span class="field-help-text">Tanggal sesi semula yang dilewati.</span>
                    </div>
                </div>

                <div class="p-3 bg-card-alt rounded-xl border-base mb-3">
                    <div class="text-xs font-bold uppercase tracking-wider text-muted mb-2">
                        <ion-icon name="time-outline"></ion-icon> Jam Belajar &amp; Durasi
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div class="form-field-wrapper">
                            <label class="filterFieldLabel">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_masuk_rencana" id="modalJamMasuk" value="14:00" onchange="modalHitungDurasi()" class="profileInput" required>
                        </div>
                        <div class="form-field-wrapper">
                            <label class="filterFieldLabel">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_pulang_rencana" id="modalJamPulang" value="16:00" onchange="modalHitungDurasi()" class="profileInput" required>
                        </div>
                        <div class="form-field-wrapper">
                            <label class="filterFieldLabel">Durasi (Jam)</label>
                            <input type="number" step="0.25" min="0.5" max="12" name="durasi_jam" id="modalDurasiJam" value="2.00" class="profileInput">
                        </div>
                    </div>
                </div>

                <div class="form-field-wrapper mb-3">
                    <label class="filterFieldLabel">Kategori Tutorial SK (Opsional)</label>
                    <select name="kategori_tutorial_id" id="modalKategoriTutorial" onchange="modalKategoriChange()" class="filterSelect">
                        <option value="">-- Sesuaikan Durasi SK Otomatis --</option>
                        @foreach($kategoriTutorials as $kat)
                            <option value="{{ $kat->id }}" data-durasi="{{ $kat->durasi_jam }}">
                                {{ $kat->nama_kategori }} ({{ $kat->durasi_jam }}j &bull; {{ $kat->formatted_nominal_honor }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field-wrapper mb-3">
                    <label class="filterFieldLabel">Alasan Reschedule / Penggantian</label>
                    <input type="text" name="alasan_penggantian" placeholder="Misal: Permintaan orang tua, siswa ujian formal, dsb." class="profileInput">
                </div>

                <div class="form-field-wrapper mb-4">
                    <label class="filterFieldLabel">Catatan Tambahan (Opsional)</label>
                    <textarea name="catatan" rows="2" placeholder="Catatan materi atau lokasi..." class="profileInput"></textarea>
                </div>

                <div class="d-flex justify-end gap-2 border-t-base pt-3">
                    <button type="button" onclick="tutupModalJadwalPengganti()" class="btnOutline">Batal</button>
                    <button type="submit" class="profileBtnPrimary">
                        <ion-icon name="save-outline"></ion-icon> Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function bukaModalJadwalPengganti() {
        const modal = document.getElementById('modalJadwalPengganti');
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function tutupModalJadwalPengganti() {
        const modal = document.getElementById('modalJadwalPengganti');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function toggleTanggalAsli() {
        const jenis = document.getElementById('modalJenisSesi').value;
        const box = document.getElementById('boxTanggalAsli');
        if (jenis === 'pengganti') {
            box.classList.remove('d-none');
        } else {
            box.classList.add('d-none');
        }
    }

    function modalKategoriChange() {
        const sel = document.getElementById('modalKategoriTutorial');
        const durasiInput = document.getElementById('modalDurasiJam');
        const opt = sel.selectedOptions[0];
        if (opt && opt.dataset.durasi) {
            durasiInput.value = parseFloat(opt.dataset.durasi).toFixed(2);
        } else {
            modalHitungDurasi();
        }
    }

    function modalHitungDurasi() {
        const m = document.getElementById('modalJamMasuk').value;
        const p = document.getElementById('modalJamPulang').value;
        const d = document.getElementById('modalDurasiJam');
        const selKat = document.getElementById('modalKategoriTutorial');

        if (selKat && selKat.value) return;

        if (m && p) {
            const [h1, m1] = m.split(':').map(Number);
            const [h2, m2] = p.split(':').map(Number);
            let diff = (h2 * 60 + m2) - (h1 * 60 + m1);
            if (diff < 0) diff += 24 * 60;
            d.value = (diff / 60).toFixed(2);
        }
    }
</script>

@endsection
