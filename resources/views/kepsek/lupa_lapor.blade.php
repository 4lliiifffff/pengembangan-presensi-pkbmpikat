@extends('layout.kepsek')

@section('title', 'Kelola Lupa Presensi')

@section('content')

<style>
    /* ── Page Header ── */
    .kllHeader { padding: 18px 16px 10px; }
    .kllTitle  { font-size: 20px; font-weight: 900; color: var(--text); margin: 0 0 2px; }
    .kllSub    { font-size: 12px; color: var(--muted); font-weight: 600; margin: 0; }

    /* ── Filter ── */
    .filterBarKll {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 0 16px 14px;
    }
    .filterRowTop {
        display: flex;
        gap: 8px;
        width: 100%;
    }
    .filterInput {
        flex: 1;
        min-width: 0;
        padding: 11px 12px;
        border-radius: 14px;
        border: 1px solid var(--border);
        background: var(--input-bg);
        color: var(--text);
        font-size: 14px;
        font-weight: 700;
        outline: none;
        -webkit-appearance: none;
        appearance: none;
        width: 100%;
    }
    input[type="date"].filterInput {
        /* iOS date inputs need explicit height and proper padding */
        height: 44px;
        padding: 0 12px;
        line-height: 44px;
    }
    .filterInput:focus {
        border-color: rgba(11,94,215,0.45);
        box-shadow: 0 0 0 3px rgba(11,94,215,0.12);
    }
    .filterBtn {
        padding: 11px 18px;
        border-radius: 14px;
        background: var(--blue2);
        color: #fff;
        border: none;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        white-space: nowrap;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        height: 44px;
        -webkit-tap-highlight-color: transparent;
    }

    /* ── Summary badge ── */
    .summaryBadge {
        margin: 0 16px 14px;
        padding: 12px 14px;
        border-radius: 16px;
        border: 1px solid var(--border);
        background: var(--card-alt);
        display: flex; align-items: center; gap: 12px;
    }
    .summaryBadgeIcon {
        width: 42px; height: 42px; border-radius: 14px;
        background: rgba(245,158,11,0.15); color: var(--warn);
        display: grid; place-items: center; flex-shrink: 0;
    }
    .summaryBadgeNum  { font-size: 22px; font-weight: 900; color: var(--text); line-height: 1; }
    .summaryBadgeLbl  { font-size: 11px; font-weight: 800; color: var(--muted); margin-top: 2px; }

    /* ── Section title ── */
    .sectionRow {
        display: flex; align-items: center; justify-content: space-between;
        padding: 4px 16px 10px;
    }
    .sectionRow h2 { margin: 0; font-size: 14px; font-weight: 900; }

    /* ── Card list ── */
    .kllList { padding: 0 16px; display: flex; flex-direction: column; gap: 12px; margin-bottom: 16px; }

    .kllCard {
        border: 1px solid var(--border);
        border-radius: 18px;
        background: var(--card-alt2);
        overflow: hidden;
    }
    /* Header strip */
    .kllCardStrip {
        background: var(--card-alt);
        border-bottom: 1px solid var(--border);
        padding: 11px 14px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
    }
    .kllTutorInfo { min-width: 0; flex: 1; }
    .kllTutorName  { font-size: 13px; font-weight: 900; color: var(--text); word-break: break-word; }
    .kllTutorId    { font-size: 10px; font-weight: 700; color: var(--muted); margin-top: 2px; }
    .kllDate {
        font-size: 11px; font-weight: 900;
        padding: 5px 10px; border-radius: 10px;
        background: rgba(239,68,68,0.10); color: #dc2626;
        border: 1px solid rgba(239,68,68,0.18);
        white-space: nowrap;
        flex-shrink: 0;
        align-self: flex-start;
    }

    /* Body */
    .kllCardBody { padding: 12px 14px; }
    .kllRow { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }

    /* Left: siswa + jam */
    .kllLeft { min-width: 0; flex: 1; }
    .kllSiswaLabel {
        font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;
        font-weight: 900; color: var(--muted); margin-bottom: 2px;
    }
    .kllSiswaVal { font-size: 13px; font-weight: 900; color: var(--text); word-break: break-word; }

    .kllJamBox {
        margin-top: 8px;
        display: flex; gap: 6px; align-items: center; flex-wrap: wrap;
    }
    .kllJamChip {
        font-size: 11px; font-weight: 900; padding: 5px 10px;
        border-radius: 10px;
        background: rgba(11,94,215,0.08); color: var(--blue2);
        border: 1px solid rgba(11,94,215,0.15);
    }
    .kllJamSep { color: var(--muted); font-size: 12px; }

    /* Alasan */
    .kllAlasanWrap { margin-top: 10px; }
    .kllAlasanLbl  { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 900; color: var(--muted); margin-bottom: 4px; }
    .kllAlasanTxt  {
        font-size: 12px; color: var(--text);
        background: var(--card-alt); border: 1px solid var(--border);
        border-radius: 12px; padding: 9px 11px; line-height: 1.55;
        word-break: break-word;
    }

    /* Action buttons */
    .kllActions { margin-top: 12px; display: flex; gap: 8px; }
    .kllDelBtn {
        flex: 1;
        padding: 12px;
        border-radius: 14px;
        background: rgba(239,68,68,0.08); color: #dc2626;
        border: 1px solid rgba(239,68,68,0.2);
        font-size: 13px; font-weight: 900; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 6px;
        min-height: 44px;
        -webkit-tap-highlight-color: transparent;
    }

    /* Empty state */
    .emptyKll {
        padding: 40px 20px; text-align: center;
        color: var(--muted); font-weight: 800; font-size: 13px;
    }
    .emptyKll ion-icon {
        font-size: 48px; display: block; margin: 0 auto 12px; opacity: 0.3;
    }

    /* Pagination */
    .paginatePad {
        padding: 0 16px 8px;
        padding-bottom: calc(8px + env(safe-area-inset-bottom, 0px));
    }

    /* ── iOS Safe-area spacing ── */
    @supports (padding: env(safe-area-inset-bottom)) {
        .kllHeader {
            padding-top: max(18px, env(safe-area-inset-top, 0px));
        }
    }
</style>

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
