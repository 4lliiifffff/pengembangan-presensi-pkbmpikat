@extends('layouts.admin')

@section('title', 'Data Peserta Magang & PKL')

@section('content')

<div class="pageHeaderRow header-actions-group flex-between">
    <div >
        <h2 class="mb-0 mt-0">Data Peserta Magang &amp; PKL</h2>
        <p class="mt-1 text-sm text-muted">Kelola data mahasiswa/siswa magang, masa periode, dan akun akses sistem</p>
    </div>
    <div class="header-actions-group">
        <a href="{{ route('admin.magang.presensi') }}" class="btnOutline p-2 text-sm gap-1">
            <ion-icon name="calendar-outline"></ion-icon> Monitoring Presensi
        </a>
        <a href="{{ route('admin.magang.create') }}" class="btnPrimary p-2 text-sm gap-1 bg-primary-gradient">
            <ion-icon name="add-outline"></ion-icon> Tambah Peserta Magang
        </a>
    </div>
</div>

<!-- Statistik -->
<div class="statsRow px-4">
    <div class="statBox dark">
        <ion-icon name="school-outline" class="icon-xl mb-1"></ion-icon>
        <h2 >{{ $total }}</h2>
        <div >TOTAL MAGANG</div>
    </div>
    <div class="statBox active">
        <ion-icon name="checkmark-circle-outline" class="icon-xl mb-1"></ion-icon>
        <h2 >{{ $aktif }}</h2>
        <div >AKTIF</div>
    </div>
    <div class="statBox inactive">
        <ion-icon name="pause-circle-outline" class="icon-xl mb-1"></ion-icon>
        <h2 >{{ $nonaktif }}</h2>
        <div >SELESAI / NONAKTIF</div>
    </div>
</div>

<!-- Filter Search -->
<div  class="searchRow mt-4 px-4">
    <form method="GET" action="{{ route('admin.magang.index') }}" class="d-flex gap-2 w-full flex-wrap">
        <div  class="flex-1 pos-relative min-w-220">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama, NIM, instansi, email..." class="profileInput w-full text-md pl-8 text-md">
            <ion-icon name="search-outline" class="pos-absolute icon-md text-muted left-2"></ion-icon>
        </div>
        <select name="status" onchange="this.form.submit()" class="profileInput w-auto text-md text-md">
            <option value="">Semua Status</option>
            <option value="1" {{ ($status === '1') ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ ($status === '0') ? 'selected' : '' }}>Nonaktif / Selesai</option>
        </select>
        <button type="submit" class="profileBtnPrimary text-md w-auto px-4 text-md">Filter</button>
        @if($search || $status !== null)
            <a href="{{ route('admin.magang.index') }}" class="btnOutline text-md d-inline-flex items-center px-3 text-md">Reset</a>
        @endif
    </form>
</div>

<!-- Tabel Magang -->
<div  class="tableCard overflow-hidden border-base m-4 rounded-xl bg-card">
    <div class="tableResponsive overflow-x-auto">
        <table  class="table w-full table-modern">
            <thead >
                <tr  class="text-left bg-card-alt table-head-row">
                    <th class="p-3 text-sm font-bold">Peserta Magang</th>
                    <th class="p-3 text-sm font-bold">Asal Instansi & Jurusan</th>
                    <th class="p-3 text-sm font-bold">Periode Magang</th>
                    <th class="p-3 text-sm font-bold">No. WhatsApp</th>
                    <th class="p-3 text-sm font-bold">Status</th>
                    <th class="p-3 text-sm font-bold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody >
                @forelse($magangs as $m)
                    @php
                        $detail = $m->magang;
                        $tglMulai = $detail?->tgl_mulai ? \Carbon\Carbon::parse($detail->tgl_mulai)->format('d/m/Y') : '-';
                        $tglSelesai = $detail?->tgl_selesai ? \Carbon\Carbon::parse($detail->tgl_selesai)->format('d/m/Y') : '-';
                        $avatarUrl = $m->foto ? (str_starts_with($m->foto, 'uploads/') ? asset($m->foto) : asset('storage/' . $m->foto)) : null;
                    @endphp
                    <tr class="table-body-row">
                        <td class="p-3">
                            <div class="d-flex items-center gap-3">
                                @if($avatarUrl)
                                    <img src="{{ $avatarUrl }}" class="rounded-full object-cover avatar-icon-36">
                                @else
                                    <div  class="rounded-full d-flex items-center justify-center font-bold text-md avatar-icon-36 bg-primary-light text-primary">
                                        {{ strtoupper(substr($m->nama_lengkap ?? $m->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div >
                                    <div class="font-bold text-md text-dark">{{ $m->nama_lengkap ?? $m->name }}</div>
                                    <div class="text-xs text-muted">NIK: {{ $m->nik }} • {{ $m->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3">
                            <div class="font-semibold text-md text-dark">{{ $detail?->asal_instansi ?: '-' }}</div>
                            <div class="text-xs text-muted">{{ $detail?->jurusan ?: '-' }} (NIM: {{ $detail?->nim_nisn ?: '-' }})</div>
                        </td>
                        <td class="p-3">
                            <div class="text-sm font-semibold text-dark">{{ $tglMulai }} s/d {{ $tglSelesai }}</div>
                        </td>
                        <td class="p-3 text-sm">
                            {{ $m->no_hp ?: '-' }}
                        </td>
                        <td class="p-3">
                            @if($m->is_active)
                                <span class="app-badge badge-status-aktif">Aktif</span>
                            @else
                                <span class="app-badge badge-status-nonaktif">Nonaktif</span>
                            @endif
                        </td>
                        <td class="p-3 text-right">
                            <div class="d-flex justify-end gap-1">
                                <a href="{{ route('admin.magang.edit', $m->id) }}" class="smallBtn edit btn-table-action" title="Edit Data Magang">
                                    <ion-icon name="create-outline"></ion-icon> Edit
                                </a>
                                <form action="{{ route('admin.magang.destroy', $m->id) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus data peserta magang ini beserta seluruh riwayat presensinya?" data-confirm-title="Hapus Data Magang" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
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
                    <tr >
                        <td colspan="6" class="text-center text-muted p-4">
                            <ion-icon name="school-outline" class="icon-2xl mb-2 opacity-50"></ion-icon>
                            <div >Belum ada data peserta magang/PKL.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($magangs->hasPages())
        <div  class="p-4 border-t-base">
            {{ $magangs->links() }}
        </div>
    @endif
</div>

@endsection
