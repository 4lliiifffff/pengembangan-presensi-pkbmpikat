@extends('layouts.kepsek')

@section('title', 'Persetujuan Pengajuan Izin & Sakit')

@section('content')

{{-- ── Header ── --}}
<div class="kllHeader">
    <h1 class="kllTitle">Persetujuan Pengajuan Izin & Sakit Tutor</h1>
    <p class="kllSub">Verifikasi surat dan setujui permohonan izin/sakit secara digital</p>
</div>

{{-- ── Flash Alerts ── --}}
@if(session('success'))
    <div class="flashAlert success">{{ session('success') }}</div>
@endif
@if(session('warning'))
    <div class="flashAlert warning">{{ session('warning') }}</div>
@endif

{{-- ── Filter ── --}}
<form method="GET" action="{{ route('kepsek.pengajuan-izin') }}">
    <div class="filterBarKll">
        <div class="filterRowTop">
            <select name="status" class="filterInput filterInput--flex" onchange="this.form.submit()">
                <option value="">— Semua Status —</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>🟡 Menunggu ({{ $totalPending ?? 0 }})</option>
                <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>🟢 Disetujui</option>
                <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>🔴 Ditolak</option>
            </select>
            <select name="jenis" class="filterInput filterInput--flex" onchange="this.form.submit()">
                <option value="">— Semua Jenis —</option>
                <option value="izin" {{ request('jenis') === 'izin' ? 'selected' : '' }}>Izin</option>
                <option value="sakit" {{ request('jenis') === 'sakit' ? 'selected' : '' }}>Sakit</option>
            </select>
            <input type="text" name="cari" class="filterInput filterInput--search" value="{{ request('cari') }}" placeholder="Nama tutor...">
            <button type="submit" class="filterBtn">
                <ion-icon name="search-outline" class="icon-md" style="vertical-align:middle;"></ion-icon> Cari
            </button>
        </div>
    </div>
</form>

{{-- ── Daftar Items ── --}}
@if($items->isEmpty())
    <div class="emptyState" style="padding:48px 16px;">
        <ion-icon name="folder-open-outline" class="icon-lg" style="font-size:48px; opacity:0.4; display:block; margin:0 auto 12px;"></ion-icon>
        <p class="td-muted" style="margin:0; font-weight:500;">Tidak ada pengajuan izin/sakit ditemukan.</p>
    </div>
@else
    <div class="kllList">
        @foreach($items as $item)
            <div class="kllCard">
                <div class="kllCardStrip">
                    <div class="kllTutorInfo">
                        <span class="badgeBadge {{ $item->jenis === 'sakit' ? 'sakit' : 'izin' }}">
                            {{ strtoupper($item->jenis_label) }}
                        </span>
                        <div class="kllTutorName" style="margin-top:4px;">
                            {{ $item->tutor->nama_lengkap ?? 'Tutor Tidak Ditemukan' }}
                        </div>
                        <div class="kllTutorId">
                            <ion-icon name="calendar-outline" style="vertical-align:middle; font-size:12px;"></ion-icon>
                            {{ \Carbon\Carbon::parse($item->tgl_mulai)->translatedFormat('d M Y') }}
                            @if($item->tgl_mulai !== $item->tgl_selesai)
                                s/d {{ \Carbon\Carbon::parse($item->tgl_selesai)->translatedFormat('d M Y') }}
                            @endif
                        </div>
                    </div>
                    <div>
                        @if($item->status === 'pending')
                            <span class="badgeStatus pending">
                                <ion-icon name="time-outline" style="vertical-align:middle;"></ion-icon> MENUNGGU
                            </span>
                        @elseif($item->status === 'disetujui')
                            <span class="badgeStatus disetujui">
                                <ion-icon name="checkmark-circle-outline" style="vertical-align:middle;"></ion-icon> DISETUJUI
                            </span>
                        @else
                            <span class="badgeStatus ditolak">
                                <ion-icon name="close-circle-outline" style="vertical-align:middle;"></ion-icon> DITOLAK
                            </span>
                        @endif
                    </div>
                </div>

                <div class="kllCardBody">
                    <div class="kllAlasanWrap">
                        <div class="kllAlasanLbl">Alasan</div>
                        <div class="kllAlasanTxt">{{ $item->alasan }}</div>
                    </div>

                    @if($item->dokumen_url)
                        <div style="margin-top:8px;">
                            <a href="{{ $item->dokumen_url }}" target="_blank" class="detailBtn" style="display:inline-flex;align-items:center;gap:4px;">
                                <ion-icon name="document-attach-outline"></ion-icon> Lihat Lampiran
                            </a>
                        </div>
                    @endif

                    @if($item->status === 'pending')
                        <div class="kllActions" style="flex-wrap:wrap;">
                            <form action="{{ route('kepsek.pengajuan-izin.setujui', $item->id) }}" method="POST" style="flex:1;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" onclick="return confirm('Setujui pengajuan ini? Data presensi akan disinkronkan secara otomatis.')" class="drawerActionBtn primary" style="margin:0;">
                                    <ion-icon name="checkmark-outline" style="vertical-align:middle;"></ion-icon> Setujui
                                </button>
                            </form>
                            <form action="{{ route('kepsek.pengajuan-izin.tolak', $item->id) }}" method="POST" style="flex:1;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" onclick="return confirm('Tolak pengajuan ini?')" class="drawerActionBtn danger" style="margin:0;">
                                    <ion-icon name="close-outline" style="vertical-align:middle;"></ion-icon> Tolak
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="llAlasan" style="margin-top:10px; font-size:12px;">
                            Verifikasi oleh {{ $item->verifikator->name ?? 'Kepala Sekolah' }} pada {{ $item->updated_at->translatedFormat('d M Y H:i') }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="paginatePad">
        {{ $items->links() }}
    </div>
@endif

@endsection
