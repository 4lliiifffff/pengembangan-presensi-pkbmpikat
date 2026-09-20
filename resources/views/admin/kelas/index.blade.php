@extends('layouts.admin')

@section('content')
<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">ROMBONGAN BELAJAR &amp; KELAS</div>
                <h1 class="laporanHeaderTitle">Data Kelas &amp; Rombel</h1>
                <div class="laporanHeaderSub">Kelola rombongan belajar, jenjang paket kesetaraan, dan program vokasi</div>
                <p class="laporanHeaderDesc">Daftar kelas aktif, alokasi jenjang kesetaraan, dan pemetaan tingkatan siswa.</p>
            </div>
            <div class="laporanHeaderActions header-actions-group">
                <a href="{{ route('admin.jenjang-paket.index') }}" class="btnOutline">
                    <ion-icon name="layers-outline" class="icon-sm"></ion-icon> Kelola Master Jenjang
                </a>
                <a href="{{ route('admin.kelas.create') }}" class="profileBtnPrimary">
                    <ion-icon name="add-outline" class="icon-sm"></ion-icon> Tambah Kelas Baru
                </a>
            </div>
        </div>
    </div>

    @php
        $selectedJenjang = request('jenjang');
        $currentJenjangObj = $selectedJenjang
            ? ($jenjangPakets->firstWhere('kode', $selectedJenjang) ?? $jenjangPakets->firstWhere('id', $selectedJenjang))
            : null;
    @endphp

    {{-- Modern Responsive Filter & Search Toolbar --}}
    <div class="kelas-toolbar-card">
        <div class="kelas-tabs-wrapper">
            <a href="{{ route('admin.kelas.index', request('q') ? ['q' => request('q')] : []) }}"
               class="kelas-tab-pill {{ !$selectedJenjang ? 'active' : '' }}">
                <ion-icon name="grid-outline"></ion-icon>
                <span>Semua Jenjang</span>
            </a>
            @foreach($jenjangPakets as $jp)
                <a href="{{ route('admin.kelas.index', array_merge(['jenjang' => $jp->kode], request('q') ? ['q' => request('q')] : [])) }}"
                   class="kelas-tab-pill {{ $selectedJenjang === $jp->kode || $selectedJenjang == $jp->id ? 'active' : '' }}">
                    <ion-icon name="layers-outline"></ion-icon>
                    <span>{{ $jp->nama_jenjang }}</span>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.kelas.index') }}" class="kelas-search-box">
            @if($selectedJenjang)
                <input type="hidden" name="jenjang" value="{{ $selectedJenjang }}">
            @endif
            <div class="kelas-search-input-wrap">
                <ion-icon name="search-outline" class="search-icon"></ion-icon>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama kelas atau tingkat..." class="kelas-search-input">
                @if(request('q'))
                    <a href="{{ route('admin.kelas.index', $selectedJenjang ? ['jenjang' => $selectedJenjang] : []) }}" class="kelas-search-clear" title="Hapus pencarian">
                        <ion-icon name="close-circle"></ion-icon>
                    </a>
                @endif
            </div>
            <button type="submit" class="kelas-search-btn">
                <span>Cari</span>
            </button>
        </form>
    </div>

    {{-- Active Filter Indicator Banner --}}
    @if($selectedJenjang || request('q'))
        <div class="kelas-filter-active-bar">
            <div class="kelas-filter-active-text">
                <ion-icon name="filter-circle-outline" class="icon-md text-primary"></ion-icon>
                <span>
                    Menampilkan hasil untuk:
                    @if($currentJenjangObj)
                        <strong>Jenjang {{ $currentJenjangObj->nama_jenjang }}</strong>
                    @elseif($selectedJenjang)
                        <strong>Jenjang {{ strtoupper(str_replace('_', ' ', $selectedJenjang)) }}</strong>
                    @endif
                    @if($selectedJenjang && request('q'))
                        &bull;
                    @endif
                    @if(request('q'))
                        kata kunci <strong>"{{ request('q') }}"</strong>
                    @endif
                    <span class="text-muted">({{ $kelas->total() }} kelas terdata)</span>
                </span>
            </div>
            <a href="{{ route('admin.kelas.index') }}" class="kelas-filter-reset-link">
                <ion-icon name="close-circle-outline"></ion-icon> Reset Filter
            </a>
        </div>
    @endif

    {{-- Modern Responsive Card Grid --}}
    <div class="kelasGridModern">
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
            <div class="kelasCardModern siswaCard">
                <div>
                    {{-- Header Kartu --}}
                    <div class="kelasCardHeader siswaTop">
                        <div class="kelasCardAvatar">
                            {{ strtoupper(substr((string) $k->nama_kelas, 0, 1)) }}
                        </div>
                        <div class="kelasCardHeaderContent">
                            <h2 class="kelasCardTitle siswaName">{{ $k->nama_kelas }}</h2>
                            <div class="kelasCardBadgeRow">
                                <span class="app-badge {{ $jenjangClass }}">
                                    {{ $k->jenjang_paket_label }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Info Micro-Chips --}}
                    <div class="kelasChipsGrid mt-3">
                        <div class="kelasChipItem">
                            <ion-icon name="ribbon-outline"></ion-icon>
                            <span>Tingkat: <b>{{ $k->tingkat ?: '-' }}</b></span>
                        </div>
                        <div class="kelasChipItem">
                            <ion-icon name="people-outline"></ion-icon>
                            <span>Murid: <b>{{ $k->siswas_count }} Siswa</b></span>
                        </div>
                    </div>

                    {{-- Keterangan / Deskripsi Rombel --}}
                    @if($k->keterangan)
                        <div class="kelasNoteBox mt-3">
                            <ion-icon name="information-circle-outline"></ion-icon>
                            <span>{{ $k->keterangan }}</span>
                        </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="kelasCardActions siswaActions">
                    <a class="kelasBtnEdit smallBtn edit btn-table-action" href="{{ route('admin.kelas.edit', $k) }}">
                        <ion-icon name="create-outline"></ion-icon>
                        Edit
                    </a>

                    <form method="POST" action="{{ route('admin.kelas.destroy', $k) }}" data-confirm="Apakah Anda yakin ingin menghapus kelas ini?" data-confirm-title="Hapus Kelas" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="kelasFormDelete">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="kelasBtnDelete smallBtn delete cursor-pointer btn-table-action">
                            <ion-icon name="trash-outline"></ion-icon>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="tableEmptyCard grid-span-full">
                <div class="tableEmptyIconWrap indigo">
                    <ion-icon name="easel-outline" class="tableEmptyIcon"></ion-icon>
                </div>
                <div class="tableEmptyTitle">Belum Ada Data Kelas</div>
                <div class="tableEmptyDesc">Belum ada data rombel/kelas yang terdaftar atau sesuai dengan kriteria filter jenjang &amp; pencarian saat ini.</div>
                @if($selectedJenjang || request('q'))
                    <div class="mt-3">
                        <a href="{{ route('admin.kelas.index') }}" class="btnOutline text-sm">
                            <ion-icon name="refresh-outline"></ion-icon> Tampilkan Semua Kelas
                        </a>
                    </div>
                @endif
            </div>
        @endforelse
    </div>

    <div class="px-2 pb-4">
        {{ $kelas->links() }}
    </div>
</div>
@endsection
