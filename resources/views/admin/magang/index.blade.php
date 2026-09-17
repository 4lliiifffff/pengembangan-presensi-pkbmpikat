@extends('layouts.admin')

@section('title', 'Data Peserta Magang & PKL')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">KEMITRAAN &amp; PRAKTIK KERJA</div>
                <h1 class="laporanHeaderTitle">Data Peserta Magang &amp; PKL</h1>
                <div class="laporanHeaderSub">Kelola data mahasiswa/siswa magang, masa periode, dan akun akses sistem</div>
                <p class="laporanHeaderDesc">Pendaftaran peserta PKL, verifikasi instansi asal, monitoring log kehadiran, dan status keaktifan penugasan.</p>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.magang.presensi') }}" class="profileBtnPrimary btn-action-info">
                    <ion-icon name="calendar-outline"></ion-icon> Monitoring Presensi
                </a>
                <a href="{{ route('admin.magang.create') }}" class="profileBtnPrimary">
                    <ion-icon name="add-circle-outline"></ion-icon> Tambah Peserta Magang
                </a>
            </div>
        </div>
    </div>

    {{-- ── Quick Stats Grid ── --}}
    <div class="account-stats-grid">
        <div class="account-stat-card">
            <div class="account-stat-icon teal">
                <ion-icon name="briefcase-outline"></ion-icon>
            </div>
            <div class="account-stat-content">
                <div class="account-stat-label">Total Peserta Magang</div>
                <div class="account-stat-value">{{ $total }}</div>
                <div class="account-stat-sub">Mahasiswa &amp; Siswa PKL</div>
            </div>
        </div>

        <div class="account-stat-card">
            <div class="account-stat-icon emerald">
                <ion-icon name="checkmark-circle-outline"></ion-icon>
            </div>
            <div class="account-stat-content">
                <div class="account-stat-label">Sedang Aktif</div>
                <div class="account-stat-value">{{ $aktif }}</div>
                <div class="account-stat-sub">Penugasan Berjalan</div>
            </div>
        </div>

        <div class="account-stat-card">
            <div class="account-stat-icon orange">
                <ion-icon name="pause-circle-outline"></ion-icon>
            </div>
            <div class="account-stat-content">
                <div class="account-stat-label">Selesai / Nonaktif</div>
                <div class="account-stat-value">{{ $nonaktif }}</div>
                <div class="account-stat-sub">Masa Magang Berakhir</div>
            </div>
        </div>
    </div>

    {{-- ── Filter Card ── --}}
    <div class="laporanFilterCard">
        <form method="GET" action="{{ route('admin.magang.index') }}">
            <div class="laporanFilterGrid">
                <div class="filterField">
                    <label class="filterFieldLabel">Pencarian Peserta Magang</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari Nama, NIK, NIM, Asal Instansi, Jurusan..." class="profileInput text-md">
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Status Penugasan</label>
                    <select name="status" class="filterSelect" onchange="this.form.submit()">
                        <option value="" {{ $status === null || $status === '' ? 'selected' : '' }}>Semua Status ({{ $total }})</option>
                        <option value="1" {{ $status === '1' ? 'selected' : '' }}>Hanya Aktif ({{ $aktif }})</option>
                        <option value="0" {{ $status === '0' ? 'selected' : '' }}>Selesai / Nonaktif ({{ $nonaktif }})</option>
                    </select>
                </div>

                <div class="filter-actions-full">
                    <button type="submit" class="btn-filter-primary">
                        <ion-icon name="filter-outline"></ion-icon> Terapkan Filter
                    </button>
                    <a href="{{ route('admin.magang.index') }}" class="btn-filter-reset">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Section Title & Filter Chips ── --}}
    <div class="sectionRow">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h2 class="m-0 font-bold">Daftar Mahasiswa &amp; Siswa Magang</h2>
            @if($status !== null && $status !== '')
                <span class="app-badge {{ $status === '1' ? 'badge-status-aktif' : 'badge-status-nonaktif' }}">
                    Status: {{ $status === '1' ? 'Aktif' : 'Nonaktif' }}
                </span>
            @endif
            @if(!empty($search))
                <span class="app-badge badge-role-magang">
                    Kata Kunci: "{{ $search }}"
                </span>
            @endif
        </div>
        <span class="badgeCount">{{ $magangs->total() }} Peserta Ditampilkan</span>
    </div>

    {{-- ── Data Table Container (Desktop) ── --}}
    <div class="table-responsive-desktop">
        <div class="tableContainer m-0 mb-4 rounded-xl border-base">
            <table class="laporanTable">
                <thead>
                    <tr>
                        <th class="table-col-num">NO</th>
                        <th>PESERTA &amp; EMAIL</th>
                        <th>NIK / NIM</th>
                        <th>ASAL INSTANSI &amp; JURUSAN</th>
                        <th>PERIODE MAGANG</th>
                        <th>NO WHATSAPP</th>
                        <th>STATUS</th>
                        <th class="text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($magangs as $index => $m)
                        @php
                            $detail = $m->magang;
                            $tglMulai = $detail?->tgl_mulai ? \Carbon\Carbon::parse($detail->tgl_mulai)->translatedFormat('d M Y') : '-';
                            $tglSelesai = $detail?->tgl_selesai ? \Carbon\Carbon::parse($detail->tgl_selesai)->translatedFormat('d M Y') : '-';
                            $avatarUrl = $m->foto ? (str_starts_with($m->foto, 'uploads/') ? asset($m->foto) : asset('storage/' . $m->foto)) : null;
                            $displayName = $m->nama_lengkap ?? $m->name;
                        @endphp
                        <tr>
                            <td class="table-col-num">
                                {{ $magangs->firstItem() + $index }}
                            </td>
                            <td>
                                <div class="table-user-cell">
                                    @if($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="{{ $displayName }}" class="table-user-avatar">
                                    @else
                                        <div class="table-user-avatar-placeholder">
                                            {{ strtoupper(substr($displayName, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="table-user-info">
                                        <div class="table-user-name">{{ $displayName }}</div>
                                        <div class="table-user-email">{{ $m->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="font-mono text-xs font-bold text-dark">{{ $m->nik }}</div>
                                @if($detail?->nim_nisn && $detail->nim_nisn !== $m->nik)
                                    <div class="text-xs text-muted">NIM: {{ $detail->nim_nisn }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="font-bold text-sm text-dark">{{ $detail?->asal_instansi ?: '-' }}</div>
                                <div class="text-xs text-muted">{{ $detail?->jurusan ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="text-xs font-semibold text-dark">{{ $tglMulai }}</div>
                                <div class="text-xs text-muted">s/d {{ $tglSelesai }}</div>
                            </td>
                            <td>
                                @if($m->no_hp)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $m->no_hp) }}" target="_blank" class="table-phone-link">
                                        <ion-icon name="logo-whatsapp"></ion-icon>
                                        <span>{{ $m->no_hp }}</span>
                                    </a>
                                @else
                                    <span class="text-muted text-xs">-</span>
                                @endif
                            </td>
                            <td>
                                @if($m->is_active)
                                    <span class="app-badge badge-status-aktif">
                                        <ion-icon name="checkmark-circle-outline"></ion-icon> Aktif
                                    </span>
                                @else
                                    <span class="app-badge badge-status-nonaktif">
                                        <ion-icon name="pause-circle-outline"></ion-icon> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="table-actions-group">
                                    <a href="{{ route('admin.magang.edit', $m->id) }}" class="smallBtn edit btn-table-action" title="Edit Data Magang">
                                        <ion-icon name="create-outline"></ion-icon> Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.magang.destroy', $m->id) }}" data-confirm="Apakah Anda yakin ingin menghapus data peserta magang {{ $displayName }} beserta seluruh riwayat presensinya?" data-confirm-title="Hapus Data Magang" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="smallBtn delete cursor-pointer btn-table-action" title="Hapus Data Magang">
                                            <ion-icon name="trash-outline"></ion-icon> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="table-empty-cell">
                                <ion-icon name="briefcase-outline" class="table-empty-icon"></ion-icon>
                                <div class="table-empty-title">Belum Ada Data Peserta Magang</div>
                                <div class="table-empty-desc">Tidak ada data peserta magang/PKL pada filter yang dipilih.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Mobile Cards List (Layar HP / Tablet) ── --}}
    <div class="mobile-card-list">
        @forelse($magangs as $m)
            @php
                $detail = $m->magang;
                $tglMulai = $detail?->tgl_mulai ? \Carbon\Carbon::parse($detail->tgl_mulai)->translatedFormat('d M Y') : '-';
                $tglSelesai = $detail?->tgl_selesai ? \Carbon\Carbon::parse($detail->tgl_selesai)->translatedFormat('d M Y') : '-';
                $avatarUrl = $m->foto ? (str_starts_with($m->foto, 'uploads/') ? asset($m->foto) : asset('storage/' . $m->foto)) : null;
                $displayName = $m->nama_lengkap ?? $m->name;
            @endphp
            <div class="data-mobile-card">
                <div class="dmc-header">
                    <div class="dmc-user-info">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="{{ $displayName }}" class="dmc-avatar">
                        @else
                            <div class="dmc-avatar-placeholder">
                                {{ strtoupper(substr($displayName, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="dmc-title">{{ $displayName }}</h3>
                            <div class="dmc-subtitle">NIK: {{ $m->nik }}</div>
                        </div>
                    </div>
                    <div class="dmc-badges">
                        <span class="app-badge badge-role-magang">Magang</span>
                        @if($m->is_active)
                            <span class="app-badge badge-status-aktif">Aktif</span>
                        @else
                            <span class="app-badge badge-status-nonaktif">Nonaktif</span>
                        @endif
                    </div>
                </div>

                <div class="dmc-grid">
                    <div class="dmc-field" style="grid-column: span 2;">
                        <div class="dmc-label">Instansi &amp; Jurusan</div>
                        <div class="dmc-value text-sm font-bold text-dark">
                            {{ $detail?->asal_instansi ?: '-' }}
                            @if($detail?->jurusan)
                                <span class="text-xs text-muted d-block font-normal">{{ $detail->jurusan }} (NIM: {{ $detail->nim_nisn ?: '-' }})</span>
                            @endif
                        </div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">Periode Magang</div>
                        <div class="dmc-value text-xs font-semibold">
                            {{ $tglMulai }} s/d {{ $tglSelesai }}
                        </div>
                    </div>

                    <div class="dmc-field">
                        <div class="dmc-label">No WhatsApp</div>
                        <div class="dmc-value">
                            @if($m->no_hp)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $m->no_hp) }}" target="_blank" class="text-primary text-no-decor text-xs font-bold">
                                    {{ $m->no_hp }}
                                </a>
                            @else
                                <span class="text-muted text-xs">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="dmc-field" style="grid-column: span 2;">
                        <div class="dmc-label">Email Login</div>
                        <div class="dmc-value text-xs font-mono text-muted">{{ $m->email }}</div>
                    </div>
                </div>

                <div class="dmc-footer">
                    <div class="dmc-actions">
                        <a href="{{ route('admin.magang.edit', $m->id) }}" class="smallBtn edit btn-table-action" title="Edit Data Magang">
                            <ion-icon name="create-outline"></ion-icon> Edit
                        </a>
                        <form method="POST" action="{{ route('admin.magang.destroy', $m->id) }}" data-confirm="Apakah Anda yakin ingin menghapus data peserta magang {{ $displayName }} beserta seluruh riwayat presensinya?" data-confirm-title="Hapus Data Magang" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="smallBtn delete cursor-pointer btn-table-action" title="Hapus Data Magang">
                                <ion-icon name="trash-outline"></ion-icon> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="data-mobile-card table-empty-cell">
                <ion-icon name="briefcase-outline" class="table-empty-icon"></ion-icon>
                <div class="font-bold text-md mb-1">Belum Ada Peserta Magang</div>
                <div class="text-sm">Tidak ada data peserta magang/PKL pada filter yang dipilih.</div>
            </div>
        @endforelse
    </div>

    @if(method_exists($magangs, 'links'))
        <div class="paginatePad py-4">
            {{ $magangs->links() }}
        </div>
    @endif
</div>

@endsection

