@extends('layouts.admin')

@section('content')
    <div class="pageHeaderRow header-actions-group" style="justify-content: space-between;">
        <div>
            <h2 style="margin:0;">Data Kelas &amp; Rombel</h2>
            <p style="margin:2px 0 0;font-size:12px;color:var(--muted);">Kelola master rombongan belajar, jenjang paket kesetaraan, dan program vokasi</p>
        </div>
        <div class="header-actions-group">
            <a href="{{ route('admin.jenjang-paket.index') }}" class="btnOutline" style="padding:8px 12px;font-size:12px;gap:6px;">
                <ion-icon name="layers-outline" style="font-size:16px;"></ion-icon> Kelola Master Jenjang &rarr;
            </a>
            <a href="{{ route('admin.kelas.create') }}" class="btnPrimary" style="padding:8px 14px;background:var(--blue-gradient);font-size:12px;gap:6px;">
                <ion-icon name="add-outline" style="font-size:16px;"></ion-icon> Tambah Kelas Baru
            </a>
        </div>
    </div>

    {{-- Filter Quick Tabs & Search Dinamis --}}
    <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <a href="{{ route('admin.kelas.index') }}" class="{{ !request('jenjang') ? 'profileBtnPrimary' : 'btnOutline' }}" style="height:32px;padding:0 12px;font-size:11.5px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;">
                Semua
            </a>
            @foreach($jenjangPakets as $jp)
                <a href="{{ route('admin.kelas.index', ['jenjang' => $jp->kode]) }}" class="{{ request('jenjang') === $jp->kode ? 'profileBtnPrimary' : 'btnOutline' }}" style="height:32px;padding:0 12px;font-size:11.5px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;">
                    {{ $jp->nama_jenjang }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.kelas.index') }}" style="display:flex;gap:6px;width:100%;max-width:280px;">
            @if(request('jenjang'))
                <input type="hidden" name="jenjang" value="{{ request('jenjang') }}">
            @endif
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama kelas..." class="input" style="height:34px;padding:4px 10px;font-size:12px;" />
            <button type="submit" class="btnOutline" style="padding:0 10px;height:34px;font-size:12px;">
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
                    <div style="display:flex;align-items:flex-start;gap:12px;width:100%;">
                        <div class="activityAvatar" style="background:var(--blue-gradient);color:#fff;flex-shrink:0;">
                            {{ strtoupper(substr((string) $k->nama_kelas, 0, 1)) }}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div class="siswaName" style="margin-bottom:4px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                <span>{{ $k->nama_kelas }}</span>
                                <span class="app-badge {{ $jenjangClass }}">
                                    {{ $k->jenjang_paket_label }}
                                </span>
                            </div>
                            <div class="siswaMeta" style="line-height:1.5;">
                                @if($k->tingkat)
                                    <span>Tingkat: <b>{{ $k->tingkat }}</b></span> •
                                @endif
                                <span>Total Murid: <b>{{ $k->siswas_count }} Siswa</b></span>
                                @if($k->keterangan)
                                    <div style="font-size:11px;color:var(--muted);margin-top:2px;font-style:italic;">
                                        {{ $k->keterangan }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="siswaActions">
                    <a class="smallBtn edit" href="{{ route('admin.kelas.edit', $k) }}">
                        <ion-icon name="create-outline"></ion-icon>
                        Edit
                    </a>

                    <form method="POST" action="{{ route('admin.kelas.destroy', $k) }}" data-confirm="Apakah Anda yakin ingin menghapus kelas ini?" data-confirm-title="Hapus Kelas" data-confirm-type="danger" data-confirm-btn="Ya, Hapus">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="smallBtn delete" style="cursor:pointer;">
                            <ion-icon name="trash-outline"></ion-icon>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="emptyState" style="grid-column:1/-1;">Belum ada data kelas yang sesuai kriteria.</div>
        @endforelse
    </div>

    <div style="padding:0 16px 30px;">
        {{ $kelas->links() }}
    </div>

    <a href="{{ route('admin.kelas.create') }}" class="fabAdd" aria-label="Tambah Kelas">
        <ion-icon name="add-outline"></ion-icon>
    </a>
@endsection
