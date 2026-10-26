@extends('layouts.admin')

@section('title', 'Data Kelas & Rombel Belajar - Admin')

@section('content')
    <div class="laporanPageWrapper">
        {{-- ── Header Card ── --}}
        <div class="laporanHeader">
            <div class="laporanHeaderCard">
                <div class="laporanHeaderInfo">
                    <div class="laporanHeaderLabel">ROMBONGAN BELAJAR &amp; KELAS</div>
                    <h1 class="laporanHeaderTitle">Data Kelas &amp; Rombel Belajar</h1>
                    <div class="laporanHeaderSub">Kelola rombongan belajar, alokasi jenjang paket kesetaraan, dan program vokasi</div>
                    <p class="laporanHeaderDesc">Daftar kelas aktif, pemetaan kurikulum kesetaraan, dan alokasi murid belajar PKBM PIKAT.</p>
                </div>
                <div class="laporanHeaderActions header-actions-group">
                    <a href="{{ route('admin.jenjang-paket.index') }}" class="btnOutline">
                        <ion-icon name="layers-outline" class="icon-sm"></ion-icon> Kelola Master Jenjang
                    </a>
                    <a href="{{ route('admin.kelas.create') }}" class="profileBtnPrimary">
                        <ion-icon name="add-circle-outline" class="icon-sm"></ion-icon> Tambah Kelas Baru
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Banner Panduan Rombel ── --}}
        <div class="mb-4 p-4 rounded-xl border-base bg-card-alt flex items-start gap-3">
            <div class="text-primary text-xl mt-0.5">
                <ion-icon name="information-circle"></ion-icon>
            </div>
            <div class="text-sm">
                <div class="font-bold text-dark">Pedoman Rombongan Belajar (Rombel):</div>
                <div class="text-muted mt-1 leading-relaxed">
                    Setiap kelas menaungi sekelompok murid yang belajar pada jenjang paket tertentu. Sistem menggunakan jenjang kelas untuk mengatur jadwal sesi tutorial KBM, mencegah absensi lintas jenjang yang tidak sesuai, serta mendukung sesi gabungan komunitas.
                </div>
            </div>
        </div>

        {{-- ── KPI Summary Cards ── --}}
        <div class="kpi-grid mb-4">
            <div class="kpi-card teal">
                <div class="kpi-top">
                    <div class="kpi-label">Total Rombel Kelas</div>
                    <div class="kpi-icon-wrap">
                        <ion-icon name="easel-outline"></ion-icon>
                    </div>
                </div>
                <div>
                    <div class="kpi-val">{{ $stats['total_kelas'] }} Kelas</div>
                    <div class="kpi-sub">Rombongan belajar terdaftar</div>
                </div>
            </div>

            <div class="kpi-card blue">
                <div class="kpi-top">
                    <div class="kpi-label">Murid Terdaftar</div>
                    <div class="kpi-icon-wrap">
                        <ion-icon name="people-outline"></ion-icon>
                    </div>
                </div>
                <div>
                    <div class="kpi-val">{{ $stats['total_siswa'] }} Murid</div>
                    <div class="kpi-sub">Siswa aktif memiliki rombel</div>
                </div>
            </div>

            <div class="kpi-card indigo">
                <div class="kpi-top">
                    <div class="kpi-label">Jenjang Terintegrasi</div>
                    <div class="kpi-icon-wrap">
                        <ion-icon name="layers-outline"></ion-icon>
                    </div>
                </div>
                <div>
                    <div class="kpi-val">{{ $stats['total_jenjang'] }} Jenjang</div>
                    <div class="kpi-sub">Program paket yang memiliki kelas</div>
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
        <div class="kelas-toolbar-card mb-4">
            <div class="kelas-tabs-wrapper">
                <a href="{{ route('admin.kelas.index', request('q') ? ['q' => request('q')] : []) }}"
                    class="kelas-tab-pill {{ !$selectedJenjang ? 'active' : '' }}">
                    <ion-icon name="grid-outline"></ion-icon>
                    <span>Semua Jenjang</span>
                </a>
                @foreach ($jenjangPakets as $jp)
                    <a href="{{ route('admin.kelas.index', array_merge(['jenjang' => $jp->kode], request('q') ? ['q' => request('q')] : [])) }}"
                        class="kelas-tab-pill {{ $selectedJenjang === $jp->kode || (string) $selectedJenjang === (string) $jp->id ? 'active' : '' }}">
                        <ion-icon name="layers-outline"></ion-icon>
                        <span>{{ $jp->nama_jenjang }}</span>
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('admin.kelas.index') }}" class="kelas-search-box">
                @if ($selectedJenjang)
                    <input type="hidden" name="jenjang" value="{{ $selectedJenjang }}">
                @endif
                <div class="kelas-search-input-wrap">
                    <ion-icon name="search-outline" class="search-icon"></ion-icon>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama kelas atau tingkat..."
                        class="kelas-search-input">
                    @if (request('q'))
                        <a href="{{ route('admin.kelas.index', $selectedJenjang ? ['jenjang' => $selectedJenjang] : []) }}"
                            class="kelas-search-clear" title="Hapus pencarian">
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
        @if ($selectedJenjang || request('q'))
            <div class="kelas-filter-active-bar mb-4">
                <div class="kelas-filter-active-text">
                    <ion-icon name="filter-circle-outline" class="icon-md text-primary"></ion-icon>
                    <span>
                        Menampilkan hasil untuk:
                        @if ($currentJenjangObj)
                            <strong>Jenjang {{ $currentJenjangObj->nama_jenjang }}</strong>
                        @elseif($selectedJenjang)
                            <strong>Jenjang {{ strtoupper(str_replace('_', ' ', $selectedJenjang)) }}</strong>
                        @endif
                        @if ($selectedJenjang && request('q'))
                            &bull;
                        @endif
                        @if (request('q'))
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
                    $jenjangClass = match ($k->jenjang_paket) {
                        'paket_a' => 'badge-jenjang-paket_a',
                        'paket_b' => 'badge-jenjang-paket_b',
                        'paket_c' => 'badge-jenjang-paket_c',
                        'vokasi' => 'badge-jenjang-vokasi',
                        'kursus' => 'badge-jenjang-kursus',
                        default => 'badge-jenjang-default',
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
                                <span>Murid:
                                    <a href="{{ route('admin.murid.index', ['kelas_id' => $k->id]) }}"
                                        class="text-primary font-bold hover:underline"
                                        title="Lihat daftar murid pada kelas ini">
                                        <b>{{ $k->siswas_count }} Siswa</b>
                                    </a>
                                </span>
                            </div>
                        </div>

                        {{-- Keterangan / Deskripsi Rombel --}}
                        @if ($k->keterangan)
                            <div class="kelasNoteBox mt-3">
                                <ion-icon name="information-circle-outline"></ion-icon>
                                <span>{{ $k->keterangan }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Action Buttons --}}
                    <div class="kelasCardActions siswaActions">
                        <a class="kelasBtnEdit smallBtn edit btn-table-action" href="{{ route('admin.kelas.edit', $k) }}" title="Edit Data Kelas">
                            <ion-icon name="create-outline"></ion-icon> Edit
                        </a>

                        <form method="POST" action="{{ route('admin.kelas.destroy', $k) }}"
                            data-confirm="Apakah Anda yakin ingin menghapus kelas {{ $k->nama_kelas }}? {{ $k->siswas_count > 0 ? 'Peringatan: masih terdapat ' . $k->siswas_count . ' murid terdaftar.' : '' }}"
                            data-confirm-title="Hapus Kelas" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="kelasFormDelete">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="kelasBtnDelete smallBtn delete cursor-pointer btn-table-action" title="Hapus Kelas">
                                <ion-icon name="trash-outline"></ion-icon> Hapus
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
                    @if ($selectedJenjang || request('q'))
                        <div class="mt-3">
                            <a href="{{ route('admin.kelas.index') }}" class="btnOutline text-sm">
                                <ion-icon name="refresh-outline"></ion-icon> Tampilkan Semua Kelas
                            </a>
                        </div>
                    @endif
                </div>
            @endforelse
        </div>

        @if ($kelas->hasPages())
            <div class="px-2 pt-4 pb-4">
                {{ $kelas->links() }}
            </div>
        @endif
    </div>
@endsection
