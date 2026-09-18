@extends('layouts.admin')

@section('title', 'Master Jadwal Rutin KBM Siswa — Admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">PENJADWALAN MINGGUAN KBM</div>
                <h1 class="laporanHeaderTitle">Master Jadwal Rutin KBM Siswa</h1>
                <div class="laporanHeaderSub">Jadwal belajar mingguan berulang khusus siswa tertentu bersama tutor pengampu</div>
                <p class="laporanHeaderDesc">Pola hari dan jam belajar akan terus berulang secara otomatis setiap minggu, terhubung dengan smart time-gating presensi masuk siswa.</p>
            </div>
            <div class="laporanHeaderActions">
                <button type="button" class="profileBtnSecondary" onclick="document.getElementById('modalGenerate').style.display='flex'">
                    <ion-icon name="sync-outline"></ion-icon> Generate Sesi
                </button>
                <a href="{{ route('admin.jadwal-rutin.create') }}" class="profileBtnPrimary">
                    <ion-icon name="add-circle-outline"></ion-icon> Tambah Jadwal Siswa
                </a>
            </div>
        </div>
    </div>

    {{-- ── Tab Switcher Navigasi Admin ── --}}
    <div class="calendarNavTabsContainer">
        <a href="{{ route('admin.jadwal.index') }}" class="calendarNavTab">
            <ion-icon name="megaphone-outline"></ion-icon>
            <span>Kalender Agenda Sekolah</span>
        </a>
        <a href="{{ route('admin.jadwal-rutin.index') }}" class="calendarNavTab active">
            <ion-icon name="repeat-outline"></ion-icon>
            <span>Master Jadwal Rutin Siswa</span>
            <span class="calendarNavBadge">{{ $stats['total'] ?? 0 }}</span>
        </a>
    </div>

    {{-- ── KPI Summary Cards ── --}}
    <div class="kpi-grid mb-4">
        <div class="kpi-card emerald">
            <div class="kpi-top">
                <div class="kpi-label">Total Jadwal Rutin</div>
                <div class="kpi-icon-wrap">
                    <ion-icon name="calendar-outline"></ion-icon>
                </div>
            </div>
            <div>
                <div class="kpi-val">{{ $stats['total'] }} Pola</div>
                <div class="kpi-sub">Master KBM mingguan aktif &amp; arsip</div>
            </div>
        </div>

        <div class="kpi-card blue">
            <div class="kpi-top">
                <div class="kpi-label">Pola KBM Aktif</div>
                <div class="kpi-icon-wrap">
                    <ion-icon name="checkmark-circle-outline"></ion-icon>
                </div>
            </div>
            <div>
                <div class="kpi-val">{{ $stats['active'] }} Pola</div>
                <div class="kpi-sub">Berjalan otomatis tiap minggu</div>
            </div>
        </div>

        <div class="kpi-card purple">
            <div class="kpi-top">
                <div class="kpi-label">Siswa Terjadwal</div>
                <div class="kpi-icon-wrap">
                    <ion-icon name="people-outline"></ion-icon>
                </div>
            </div>
            <div>
                <div class="kpi-val">{{ $stats['siswa_count'] }} Siswa</div>
                <div class="kpi-sub">Memiliki jadwal KBM rutin</div>
            </div>
        </div>

        <div class="kpi-card cyan">
            <div class="kpi-top">
                <div class="kpi-label">Tutor Pengampu</div>
                <div class="kpi-icon-wrap">
                    <ion-icon name="school-outline"></ion-icon>
                </div>
            </div>
            <div>
                <div class="kpi-val">{{ $stats['tutor_count'] }} Tutor</div>
                <div class="kpi-sub">Membimbing sesi rutin siswa</div>
            </div>
        </div>
    </div>

    {{-- ── Filter Card ── --}}
    <div class="laporanFilterCard mb-4">
        <form action="{{ route('admin.jadwal-rutin.index') }}" method="GET">
            <div class="laporanFilterGrid">
                <div class="filterField">
                    <label class="filterFieldLabel">Cari Jadwal</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari siswa, tutor, catatan..." class="profileInput text-md">
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Filter Siswa</label>
                    <select name="siswa_id" class="filterSelect" onchange="this.form.submit()">
                        <option value="">Semua Siswa</option>
                        @foreach ($siswas as $s)
                            <option value="{{ $s->id }}" {{ request('siswa_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->nama_siswa }} (No. {{ $s->no_absen ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Filter Tutor</label>
                    <select name="tutor_id" class="filterSelect" onchange="this.form.submit()">
                        <option value="">Semua Tutor</option>
                        @foreach ($tutors as $t)
                            <option value="{{ $t->id }}" {{ request('tutor_id') == $t->id ? 'selected' : '' }}>
                                {{ $t->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Hari Belajar</label>
                    <select name="hari" class="filterSelect" onchange="this.form.submit()">
                        <option value="">Semua Hari</option>
                        @foreach (['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu', 'minggu' => 'Minggu'] as $val => $label)
                            <option value="{{ $val }}" {{ request('hari') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Status</label>
                    <select name="status" class="filterSelect" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="filter-actions-full">
                    <button type="submit" class="btn-filter-primary">
                        <ion-icon name="filter-outline"></ion-icon> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'siswa_id', 'tutor_id', 'hari', 'status']))
                        <a href="{{ route('admin.jadwal-rutin.index') }}" class="btn-filter-reset" title="Reset Filter">
                            <ion-icon name="refresh-outline"></ion-icon> Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- ── Section Title ── --}}
    <div class="sectionRow">
        <h2>Daftar Pola Jadwal KBM</h2>
        <span class="badgeCount">{{ $jadwalRutins->total() }} Pola Ditemukan</span>
    </div>

    {{-- ── Desktop Table ── --}}
    <div class="table-responsive-desktop">
        <div class="tableContainer m-0 mb-4 rounded-xl border-base">
            <table class="laporanTable">
                <thead>
                    <tr>
                        <th class="table-col-num">NO</th>
                        <th>SISWA BIMBINGAN</th>
                        <th>TUTOR PENGAMPU</th>
                        <th>HARI &amp; JAM BELAJAR</th>
                        <th>KATEGORI &amp; DURASI</th>
                        <th class="text-center">STATUS</th>
                        <th class="text-center">SESI AKTIF</th>
                        <th class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jadwalRutins as $index => $item)
                        @php
                            $hariSlug = strtolower((string) $item->hari);
                        @endphp
                        <tr>
                            <td class="text-center font-bold text-muted">{{ $jadwalRutins->firstItem() + $index }}</td>
                            <td>
                                <div class="font-bold text-base" style="color: var(--text);">{{ $item->siswa->nama_siswa ?? '-' }}</div>
                                <div class="text-xs text-muted mt-1">
                                    No. Absen: <strong>{{ $item->siswa->no_absen ?? '-' }}</strong> · {{ $item->siswa->nama_kelas_lengkap ?? 'Reguler' }}
                                </div>
                            </td>
                            <td>
                                <div class="font-bold" style="color: var(--text);">{{ $item->tutor->nama_lengkap ?? '-' }}</div>
                                <div class="text-xs text-muted mt-1">
                                    {{ $item->tutor->kontak ?? ($item->tutor->email ?? 'Tutor PKBM') }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex items-center gap-1 mb-1 flex-wrap">
                                    <span class="app-badge badge-hari-{{ $hariSlug }}">
                                        {{ strtoupper((string) $item->nama_hari_label) }}
                                    </span>
                                    <span class="font-bold text-sm" style="color: var(--text);">
                                        {{ $item->jam_masuk_formatted }} - {{ $item->jam_pulang_formatted }} WIB
                                    </span>
                                </div>
                                <div class="text-xs font-semibold" style="color: #059669;">
                                    <ion-icon name="time-outline" style="vertical-align: middle;"></ion-icon>
                                    Absen buka: {{ \Carbon\Carbon::createFromFormat('H:i:s', $item->jam_masuk)->subMinutes(30)->format('H:i') }} WIB
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-info text-xs">
                                    {{ $item->kategoriTutorial->nama_kategori ?? 'Tutorial KBM' }}
                                </span>
                                <div class="text-xs text-muted mt-1">
                                    Durasi: <strong>{{ number_format((float) $item->durasi_jam, 1) }} Jam</strong>
                                </div>
                            </td>
                            <td class="text-center">
                                @if ($item->is_active)
                                    <span class="app-badge badge-status-aktif">Aktif</span>
                                @else
                                    <span class="app-badge badge-status-nonaktif">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="app-badge badge-role-tutor">
                                    {{ $item->jadwal_sesis_count }} Sesi
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 flex-center flex-wrap">
                                    <form action="{{ route('admin.jadwal-rutin.toggleStatus', $item) }}" method="POST" class="d-inline m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="smallBtn btn-table-action" title="{{ $item->is_active ? 'Nonaktifkan Jadwal' : 'Aktifkan Jadwal' }}" style="background: var(--card-alt); border: 1px solid var(--border); color: {{ $item->is_active ? '#d97706' : '#059669' }};">
                                            <ion-icon name="{{ $item->is_active ? 'pause-circle-outline' : 'play-circle-outline' }}" style="font-size: 15px;"></ion-icon>
                                            {{ $item->is_active ? 'Pause' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    <a href="{{ route('admin.jadwal-rutin.edit', $item) }}" class="smallBtn edit btn-table-action" title="Edit Jadwal">
                                        <ion-icon name="create-outline" style="font-size: 14px;"></ion-icon> Edit
                                    </a>

                                    <form action="{{ route('admin.jadwal-rutin.destroy', $item) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal rutin ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="smallBtn delete cursor-pointer btn-table-action" title="Hapus Jadwal">
                                            <ion-icon name="trash-outline" style="font-size: 14px;"></ion-icon> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="table-empty-cell">
                                <div class="font-bold text-md mb-1">Belum Ada Jadwal Rutin KBM</div>
                                <div class="text-sm text-muted">Belum ada master jadwal rutin siswa yang terdaftar sesuai filter saat ini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($jadwalRutins->hasPages())
            <div class="mt-3">
                {{ $jadwalRutins->links() }}
            </div>
        @endif
    </div>

    {{-- ── Mobile Cards List (Layar HP / Tablet) ── --}}
    <div class="mobile-card-list">
        @forelse ($jadwalRutins as $index => $item)
            @php
                $hariSlug = strtolower((string) $item->hari);
            @endphp
            <div class="data-mobile-card">
                <div class="dmc-header">
                    <div>
                        <h3 class="dmc-title">{{ $item->siswa->nama_siswa ?? '-' }}</h3>
                        <div class="dmc-subtitle">
                            No. Absen: <strong>{{ $item->siswa->no_absen ?? '-' }}</strong> · {{ $item->siswa->nama_kelas_lengkap ?? 'Reguler' }}
                        </div>
                    </div>
                    <div class="flex-col gap-1 flex-items-end">
                        <span class="app-badge badge-hari-{{ $hariSlug }}">
                            {{ strtoupper((string) $item->nama_hari_label) }}
                        </span>
                        @if ($item->is_active)
                            <span class="app-badge badge-status-aktif">Aktif</span>
                        @else
                            <span class="app-badge badge-status-nonaktif">Nonaktif</span>
                        @endif
                    </div>
                </div>

                <div class="dmc-grid">
                    <div class="dmc-field">
                        <div class="dmc-label">Tutor Pengampu</div>
                        <div class="dmc-value font-bold">{{ $item->tutor->nama_lengkap ?? '-' }}</div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Jam Belajar KBM</div>
                        <div class="dmc-value text-primary font-bold">{{ $item->jam_masuk_formatted }} - {{ $item->jam_pulang_formatted }} WIB</div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Kategori / Modul</div>
                        <div class="dmc-value">{{ $item->kategoriTutorial->nama_kategori ?? 'Tutorial KBM' }}</div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Durasi / Sesi Kalender</div>
                        <div class="dmc-value font-bold">{{ number_format((float) $item->durasi_jam, 1) }} Jam ({{ $item->jadwal_sesis_count }} Sesi)</div>
                    </div>

                    <div class="dmc-field full">
                        <div class="dmc-label">Jendela Presensi Masuk Siswa</div>
                        <div class="dmc-value text-xs font-semibold" style="color: #059669;">
                            <ion-icon name="time-outline" style="vertical-align: middle;"></ion-icon>
                            Absen dibuka 30 menit sebelum ({{ \Carbon\Carbon::createFromFormat('H:i:s', $item->jam_masuk)->subMinutes(30)->format('H:i') }} WIB)
                        </div>
                        @if($item->keterangan)
                            <div class="text-xs text-muted mt-1" style="line-height: 1.4;">
                                <em>Catatan: {{ $item->keterangan }}</em>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="dmc-footer">
                    <div class="text-xs font-bold text-muted">
                        No. {{ $jadwalRutins->firstItem() + $index }}
                    </div>
                    <div class="d-flex gap-1 flex-items-center">
                        <form action="{{ route('admin.jadwal-rutin.toggleStatus', $item) }}" method="POST" class="d-inline m-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="smallBtn btn-table-action" title="{{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}" style="background: var(--card-alt); border: 1px solid var(--border); color: {{ $item->is_active ? '#d97706' : '#059669' }};">
                                <ion-icon name="{{ $item->is_active ? 'pause-circle-outline' : 'play-circle-outline' }}"></ion-icon>
                            </button>
                        </form>

                        <a href="{{ route('admin.jadwal-rutin.edit', $item) }}" class="smallBtn edit btn-table-action" title="Edit Jadwal">
                            <ion-icon name="create-outline"></ion-icon> Edit
                        </a>

                        <form action="{{ route('admin.jadwal-rutin.destroy', $item) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal rutin ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="smallBtn delete cursor-pointer btn-table-action" title="Hapus Jadwal">
                                <ion-icon name="trash-outline"></ion-icon>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="data-mobile-card text-center p-4">
                <div class="font-bold text-md mb-1">Belum Ada Jadwal Rutin KBM</div>
                <div class="text-sm text-muted">Belum ada master jadwal rutin siswa yang terdaftar sesuai filter saat ini.</div>
            </div>
        @endforelse

        @if ($jadwalRutins->hasPages())
            <div class="mt-3">
                {{ $jadwalRutins->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ── Modal Manual Generate Sesi ── --}}
<div id="modalGenerate" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 16px;">
    <div class="app-modal-card">
        <div class="app-modal-header">
            <div class="d-flex items-center gap-2">
                <div style="background: rgba(37, 99, 235, 0.12); color: #2563eb; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <ion-icon name="sync-outline"></ion-icon>
                </div>
                <div>
                    <h3 class="app-modal-title">Generate Sesi KBM</h3>
                    <p class="text-xs text-muted m-0">Sinkronkan kalender sesi KBM dari master jadwal rutin</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modalGenerate').style.display='none'" style="border: none; background: transparent; font-size: 1.75rem; color: var(--muted); cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <form action="{{ route('admin.jadwal-rutin.generate-manual') }}" method="POST" class="mt-3">
            @csrf
            <div class="form-field-wrapper mb-3">
                <label class="form-field-label">Periode Generate ke Depan</label>
                <select name="weeks" class="filterSelect">
                    <option value="2">2 Minggu ke Depan</option>
                    <option value="4" selected>4 Minggu ke Depan (1 Bulan)</option>
                    <option value="8">8 Minggu ke Depan (2 Bulan)</option>
                </select>
            </div>

            <div class="form-field-wrapper mb-3">
                <label class="form-field-label">Filter Siswa Khusus (Opsional)</label>
                <select name="siswa_id" class="filterSelect">
                    <option value="">Semua Siswa Aktif</option>
                    @foreach ($siswas as $s)
                        <option value="{{ $s->id }}">{{ $s->nama_siswa }} (No. {{ $s->no_absen ?? '-' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-field-wrapper mb-3">
                <label class="form-field-label">Filter Tutor Khusus (Opsional)</label>
                <select name="tutor_id" class="filterSelect">
                    <option value="">Semua Tutor</option>
                    @foreach ($tutors as $t)
                        <option value="{{ $t->id }}">{{ $t->nama_lengkap }}</option>
                    @endforeach
                </select>
            </div>

            <div class="checkbox-toggle-card mb-4">
                <input type="checkbox" id="skip_holidays" name="skip_holidays" value="1" checked>
                <label for="skip_holidays" class="cursor-pointer text-xs" style="margin: 0; font-weight: 600;">
                    Lewati tanggal merah / libur nasional (jangan buat sesi pada tanggal libur)
                </label>
            </div>

            <div class="form-action-footer mt-4">
                <button type="button" class="btnOutline" onclick="document.getElementById('modalGenerate').style.display='none'">Batal</button>
                <button type="submit" class="profileBtnPrimary">
                    <ion-icon name="flash-outline"></ion-icon> Jalankan Generator
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
