@extends('layouts.kepsek')

@section('title', 'Kelola Lupa Presensi')

@section('content')


{{-- ── Header ── --}}
<div class="kllHeader">
    <h1 class="kllTitle">Kelola Lupa Presensi</h1>
    <p class="kllSub">Pengajuan tutor yang lupa absen / izin</p>
</div>

{{-- ── Filter ── --}}
<form method="GET" action="{{ route('kepsek.lupa-lapor') }}">
    <div class="filterBarKll">
        <div class="filterRowTop">
            <input type="date" name="tanggal" class="filterInput"
                   value="{{ request('tanggal') }}">
            <input type="text" name="cari" class="filterInput"
                   value="{{ request('cari') }}"
                   placeholder="Nama tutor / siswa">
        </div>
        <button type="submit" class="filterBtn">
            <ion-icon name="search-outline" style="font-size:15px;"></ion-icon>
            Cari
        </button>
    </div>
</form>

{{-- ── Summary ── --}}
<div class="summaryBadge">
    <div class="summaryBadgeIcon">
        <ion-icon name="document-text-outline" style="font-size:22px;"></ion-icon>
    </div>
    <div>
        <div class="summaryBadgeNum">{{ $total }}</div>
        <div class="summaryBadgeLbl">Total Pengajuan Lupa Lapor</div>
    </div>
</div>

{{-- ── List ── --}}
<div class="sectionRow">
    <h2>Daftar Pengajuan</h2>
    @if(request('tanggal') || request('cari'))
        <a href="{{ route('kepsek.lupa-lapor') }}" class="mutedLink">Reset &rsaquo;</a>
    @endif
</div>

<div class="kllList">
    @forelse($items as $item)
    @php
        $tgl      = \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d F Y');
        $jMulai   = substr((string) $item->jam_mulai, 0, 5);
        $jSelesai = substr((string) $item->jam_selesai, 0, 5);
        $tutorNama = $item->tutor->nama_lengkap ?? 'Tutor #'.$item->tutor_id;
        $siswaNama = $item->siswa->nama_siswa  ?? 'Siswa #'.$item->siswa_id;
    @endphp
    <div class="kllCard">
        {{-- Strip header --}}
        <div class="kllCardStrip">
            <div class="kllTutorInfo">
                <div class="kllTutorName">{{ $tutorNama }}</div>
                <div class="kllTutorId">ID Tutor: {{ $item->tutor_id }}</div>
            </div>
            <div class="kllDate">{{ $tgl }}</div>
        </div>

        {{-- Body --}}
        <div class="kllCardBody">
            <div class="kllRow">
                <div class="kllLeft">
                    <div class="kllSiswaLabel">Siswa</div>
                    <div class="kllSiswaVal">{{ $siswaNama }}</div>
                    <div class="kllJamBox">
                        <div class="kllJamChip">{{ $jMulai }}</div>
                        <span class="kllJamSep">→</span>
                        <div class="kllJamChip">{{ $jSelesai }}</div>
                    </div>
                </div>
            </div>

            <div class="kllAlasanWrap">
                <div class="kllAlasanLbl">Alasan / Keterangan</div>
                <div class="kllAlasanTxt">{{ $item->alasan }}</div>
            </div>

            <div class="kllActions">
                <form method="POST"
                      action="{{ route('kepsek.lupa-lapor.destroy', $item->id) }}"
                      onsubmit="return confirm('Hapus pengajuan ini?');"
                      style="flex:1;">
                    @csrf @method('DELETE')
                    <button type="submit" class="kllDelBtn">
                        <ion-icon name="trash-outline" style="font-size:15px;"></ion-icon>
                        Hapus Pengajuan
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
        <div class="emptyKll">
            <ion-icon name="checkmark-circle-outline"></ion-icon>
            {{ request('tanggal') || request('cari') ? 'Tidak ada hasil yang cocok.' : 'Belum ada pengajuan lupa lapor.' }}
        </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($items->hasPages())
<div class="paginatePad">
    {{ $items->withQueryString()->links() }}
</div>
@endif

<div style="height: 20px;"></div>

@endsection
