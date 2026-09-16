@extends('layouts.admin')

@section('content')
    <div class="pageHeaderRow header-actions-group flex-between">
        <div >
            <h2 class="mb-0 mt-0">Data Kelas &amp; Rombel</h2>
            <p class="mt-1 text-sm text-muted">Kelola master rombongan belajar, jenjang paket kesetaraan, dan program vokasi</p>
        </div>
        <div class="header-actions-group">
            <a href="{{ route('admin.jenjang-paket.index') }}" class="btnOutline p-2 text-sm gap-1">
                <ion-icon name="layers-outline" class="icon-sm"></ion-icon> Kelola Master Jenjang &rarr;
            </a>
            <a href="{{ route('admin.kelas.create') }}" class="btnPrimary text-sm gap-1 bg-primary-gradient px-3 py-2">
                <ion-icon name="add-outline" class="icon-sm"></ion-icon> Tambah Kelas Baru
            </a>
        </div>
    </div>

    {{-- Filter Quick Tabs & Search Dinamis --}}
    <div class="d-flex flex-wrap gap-2 justify-between items-center mb-4">
        <div class="d-flex gap-1 flex-wrap">
            <a href="{{ route('admin.kelas.index') }}" class="{{ !request('jenjang') ? 'profileBtnPrimary' : 'btnOutline' }} px-3 text-sm rounded-md text-no-decor d-inline-flex items-center">
                Semua
            </a>
            @foreach($jenjangPakets as $jp)
                <a href="{{ route('admin.kelas.index', ['jenjang' => $jp->kode]) }}" class="{{ request('jenjang') === $jp->kode ? 'profileBtnPrimary' : 'btnOutline' }}" class="px-3 text-sm rounded-md text-no-decor d-inline-flex items-center">
                    {{ $jp->nama_jenjang }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.kelas.index') }}" class="d-flex gap-1 w-full max-w-xs">
            @if(request('jenjang'))
                <input type="hidden" name="jenjang" value="{{ request('jenjang') }}">
            @endif
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama kelas..." / class="input text-sm px-2 py-1 text-sm">
            <button type="submit" class="btnOutline text-sm px-2 text-sm">
                <ion-icon name="search-outline"></ion-icon>
            </button>
        </form>
    </div>

    <div class="siswaGrid">
        @forelse($kelas as $k)
            @php
                $jenjangClass = match($k->jenjang_paket) {
                    'paket_a' => 'badge-jenjang-paket_a',
                    'paket_b' => 'badge-jenjang-paket_b',
                    'paket_c' => 'badge-jenjang-paket_c',
                    'vokasi'  => 'badge-jenjang-vokasi',
                    'kursus'  => 'badge-jenjang-kursus',
                    default   => 'badge-jenjang-default',
                };
            @endphp
            <div class="siswaCard">
                <div class="siswaTop">
                    <div class="d-flex items-start gap-3 w-full">
                        <div class="activityAvatar text-white flex-shrink-0 bg-primary-gradient">
                            {{ strtoupper(substr((string) $k->nama_kelas, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="siswaName mb-1 d-flex items-center gap-1 flex-wrap">
                                <span >{{ $k->nama_kelas }}</span>
                                <span class="app-badge {{ $jenjangClass }}">
                                    {{ $k->jenjang_paket_label }}
                                </span>
                            </div>
                            <div class="siswaMeta line-height-relaxed">
                                @if($k->tingkat)
                                    <span >Tingkat: <b >{{ $k->tingkat }}</b></span> •
                                @endif
                                <span >Total Murid: <b >{{ $k->siswas_count }} Siswa</b></span>
                                @if($k->keterangan)
                                    <div class="text-xs text-muted mt-1 font-italic">
                                        {{ $k->keterangan }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="siswaActions">
                    <a class="smallBtn edit btn-table-action" href="{{ route('admin.kelas.edit', $k) }}">
                        <ion-icon name="create-outline"></ion-icon>
                        Edit
                    </a>

                    <form method="POST" action="{{ route('admin.kelas.destroy', $k) }}" data-confirm="Apakah Anda yakin ingin menghapus kelas ini?" data-confirm-title="Hapus Kelas" data-confirm-type="danger" data-confirm-btn="Ya, Hapus">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="smallBtn delete cursor-pointer btn-table-action">
                            <ion-icon name="trash-outline"></ion-icon>
                            Hapus
                        </button>
                    </form>
                </div>

            </div>
        @empty
            <div class="emptyState grid-span-full">Belum ada data kelas yang sesuai kriteria.</div>
        @endforelse
    </div>

    <div class="px-4 pb-4">
        {{ $kelas->links() }}
    </div>

    <a href="{{ route('admin.kelas.create') }}" class="fabAdd" aria-label="Tambah Kelas">
        <ion-icon name="add-outline"></ion-icon>
    </a>
@endsection
