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
            <div class="laporanHeaderActions header-actions-group">
                <button type="button" class="profileBtnSecondary" onclick="document.getElementById('modalGenerate').style.display='flex'">
                    <ion-icon name="sync-outline" class="icon-sm"></ion-icon> Generate Sesi
                </button>
                <a href="{{ route('admin.jadwal-rutin.create') }}" class="profileBtnPrimary">
                    <ion-icon name="add-circle-outline" class="icon-sm"></ion-icon> Tambah Jadwal Siswa
                </a>
            </div>
        </div>
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
    <div class="filterCard mb-4">
        <form action="{{ route('admin.jadwal-rutin.index') }}" method="GET" class="filterGrid">
            <div class="filterItem">
                <label class="filterLabel">Cari Siswa / Tutor / Catatan</label>
                <div class="searchWrap">
                    <ion-icon name="search-outline" class="searchIcon"></ion-icon>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama siswa, tutor..." class="filterInput">
                </div>
            </div>

            <div class="filterItem">
                <label class="filterLabel">Filter Siswa</label>
                <select name="siswa_id" class="filterSelect">
                    <option value="">-- Semua Siswa --</option>
                    @foreach ($siswas as $s)
                        <option value="{{ $s->id }}" {{ request('siswa_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->nama_siswa }} (No. {{ $s->no_absen ?? '-' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filterItem">
                <label class="filterLabel">Filter Tutor</label>
                <select name="tutor_id" class="filterSelect">
                    <option value="">-- Semua Tutor --</option>
                    @foreach ($tutors as $t)
                        <option value="{{ $t->id }}" {{ request('tutor_id') == $t->id ? 'selected' : '' }}>
                            {{ $t->nama_lengkap }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filterItem">
                <label class="filterLabel">Hari Belajar</label>
                <select name="hari" class="filterSelect">
                    <option value="">-- Semua Hari --</option>
                    @foreach (['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu', 'minggu' => 'Minggu'] as $val => $label)
                        <option value="{{ $val }}" {{ request('hari') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filterItem">
                <label class="filterLabel">Status</label>
                <select name="status" class="filterSelect">
                    <option value="">-- Semua Status --</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div class="filterActions">
                <button type="submit" class="profileBtnPrimary">
                    <ion-icon name="filter-outline"></ion-icon> Filter
                </button>
                <a href="{{ route('admin.jadwal-rutin.index') }}" class="profileBtnSecondary">
                    <ion-icon name="refresh-outline"></ion-icon> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ── Table Card ── --}}
    <div class="laporanTableCard">
        <div class="tableResponsive">
            <table class="laporanTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Siswa Bimbingan</th>
                        <th>Tutor Pengampu</th>
                        <th>Hari &amp; Jam Belajar</th>
                        <th>Kategori &amp; Durasi</th>
                        <th>Status</th>
                        <th>Sesi Aktif</th>
                        <th style="width: 140px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jadwalRutins as $index => $item)
                        @php
                            $hariColors = [
                                'senin' => 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;',
                                'selasa' => 'background: #fdf4ff; color: #a21caf; border: 1px solid #f0abfc;',
                                'rabu' => 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;',
                                'kamis' => 'background: #fffbeb; color: #b45309; border: 1px solid #fde68a;',
                                'jumat' => 'background: #f0fdfa; color: #0f766e; border: 1px solid #99f6e4;',
                                'sabtu' => 'background: #fff1f2; color: #be123c; border: 1px solid #fecdd3;',
                                'minggu' => 'background: #fef2f2; color: #b91c1c; border: 1px solid #fca5a5;',
                            ];
                            $badgeStyle = $hariColors[strtolower((string) $item->hari)] ?? 'background: #f3f4f6; color: #374151;';
                        @endphp
                        <tr>
                            <td>{{ $jadwalRutins->firstItem() + $index }}</td>
                            <td>
                                <div style="font-weight: 600; color: #111827;">{{ $item->siswa->nama_siswa ?? '-' }}</div>
                                <div style="font-size: 0.78rem; color: #6b7280;">
                                    No. Absen: {{ $item->siswa->no_absen ?? '-' }} · {{ $item->siswa->nama_kelas_lengkap ?? 'Reguler' }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #1f2937;">{{ $item->tutor->nama_lengkap ?? '-' }}</div>
                                <div style="font-size: 0.78rem; color: #6b7280;">
                                    {{ $item->tutor->kontak ?? ($item->tutor->email ?? 'Tutor Terdaftar') }}
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 3px;">
                                    <span style="font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; text-transform: uppercase; {{ $badgeStyle }}">
                                        {{ $item->nama_hari_label }}
                                    </span>
                                    <span style="font-weight: 700; color: #111827; font-size: 0.88rem;">
                                        {{ $item->jam_masuk_formatted }} - {{ $item->jam_pulang_formatted }} WIB
                                    </span>
                                </div>
                                <div style="font-size: 0.75rem; color: #059669; font-weight: 500;">
                                    <ion-icon name="time-outline" style="vertical-align: middle;"></ion-icon> Absen buka mulai: {{ \Carbon\Carbon::createFromFormat('H:i:s', $item->jam_masuk)->subMinutes(30)->format('H:i') }} WIB
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-info" style="font-size: 0.75rem;">
                                    {{ $item->kategoriTutorial->nama_kategori ?? 'Tutorial KBM' }}
                                </span>
                                <div style="font-size: 0.75rem; color: #6b7280; margin-top: 2px;">
                                    Durasi: <strong>{{ number_format((float) $item->durasi_jam, 1) }} Jam</strong>
                                </div>
                            </td>
                            <td>
                                @if ($item->is_active)
                                    <span class="badge badge-success" style="font-size: 0.75rem;">Aktif Rutin</span>
                                @else
                                    <span class="badge badge-secondary" style="font-size: 0.75rem;">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-primary" style="font-size: 0.75rem;">
                                    {{ $item->jadwal_sesis_count }} Sesi
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; align-items: center; gap: 4px;">
                                    <form action="{{ route('admin.jadwal-rutin.toggleStatus', $item) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="actionBtn" title="{{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}" style="color: {{ $item->is_active ? '#d97706' : '#059669' }};">
                                            <ion-icon name="{{ $item->is_active ? 'pause-circle-outline' : 'play-circle-outline' }}"></ion-icon>
                                        </button>
                                    </form>

                                    <a href="{{ route('admin.jadwal-rutin.edit', $item) }}" class="actionBtn text-blue-600" title="Edit Jadwal">
                                        <ion-icon name="create-outline"></ion-icon>
                                    </a>

                                    <form action="{{ route('admin.jadwal-rutin.destroy', $item) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal rutin ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="actionBtn text-red-600" title="Hapus">
                                            <ion-icon name="trash-outline"></ion-icon>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #9ca3af;">
                                <ion-icon name="calendar-clear-outline" style="font-size: 3rem; margin-bottom: 0.5rem; color: #d1d5db; display: block; margin-left: auto; margin-right: auto;"></ion-icon>
                                <strong>Belum ada master jadwal rutin KBM siswa yang terdaftar.</strong>
                                <p style="font-size: 0.85rem; margin-top: 4px;">Klik tombol "Tambah Jadwal Siswa" di atas untuk menetapkan jadwal mingguan siswa tertentu.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($jadwalRutins->hasPages())
            <div style="padding: 1rem;">
                {{ $jadwalRutins->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ── Modal Manual Generate Sesi ── --}}
<div id="modalGenerate" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: white; border-radius: 16px; max-width: 480px; width: 100%; padding: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="background: #eff6ff; color: #2563eb; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <ion-icon name="sync-outline"></ion-icon>
                </div>
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: #111827; margin: 0;">Generate Sesi KBM</h3>
                    <p style="font-size: 0.78rem; color: #6b7280; margin: 0;">Sinkronkan kalender sesi dari master jadwal rutin</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modalGenerate').style.display='none'" style="border: none; background: none; font-size: 1.5rem; color: #9ca3af; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('admin.jadwal-rutin.generate-manual') }}" method="POST">
            @csrf
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Periode Generate ke Depan</label>
                <select name="weeks" class="filterSelect" style="width: 100%;">
                    <option value="2">2 Minggu ke Depan</option>
                    <option value="4" selected>4 Minggu ke Depan (1 Bulan)</option>
                    <option value="8">8 Minggu ke Depan (2 Bulan)</option>
                </select>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Filter Siswa Khusus (Opsional)</label>
                <select name="siswa_id" class="filterSelect" style="width: 100%;">
                    <option value="">-- Semua Siswa Aktif --</option>
                    @foreach ($siswas as $s)
                        <option value="{{ $s->id }}">{{ $s->nama_siswa }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Filter Tutor Khusus (Opsional)</label>
                <select name="tutor_id" class="filterSelect" style="width: 100%;">
                    <option value="">-- Semua Tutor --</option>
                    @foreach ($tutors as $t)
                        <option value="{{ $t->id }}">{{ $t->nama_lengkap }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 1.25rem; display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" id="skip_holidays" name="skip_holidays" value="1" style="width: 16px; height: 16px;">
                <label for="skip_holidays" style="font-size: 0.82rem; color: #4b5563; cursor: pointer;">
                    Lewati tanggal merah / hari libur (jangan buat sesi pada tanggal libur)
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="profileBtnSecondary" onclick="document.getElementById('modalGenerate').style.display='none'">Batal</button>
                <button type="submit" class="profileBtnPrimary">
                    <ion-icon name="flash-outline"></ion-icon> Jalankan Generator
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
