@extends('layouts.kepsek')

@section('title', 'Kelola Pengajuan Lupa Lapor')

@section('content')

{{-- ── Header ── --}}
<div class="kllHeader">
    <h1 class="kllTitle">Persetujuan Pengajuan Lupa Lapor</h1>
    <p class="kllSub">Verifikasi dan setujui laporan presensi retroaktif dari Tutor</p>
</div>

{{-- ── Flash Alerts ── --}}
@if(session('success'))
    <div class="flashAlert success" style="margin-bottom: 12px; padding: 10px 14px; background: #ecfdf5; border: 1px solid #10b981; color: #065f46; border-radius: 8px; font-size: 13px;">
        {{ session('success') }}
    </div>
@endif
@if(session('warning'))
    <div class="flashAlert warning" style="margin-bottom: 12px; padding: 10px 14px; background: #fffbebfb; border: 1px solid #f59e0b; color: #92400e; border-radius: 8px; font-size: 13px;">
        {{ session('warning') }}
    </div>
@endif

{{-- ── Filter ── --}}
<form method="GET" action="{{ route('kepsek.lupa-lapor') }}">
    <div class="filterBarKll">
        <div class="filterRowTop">
            <select name="status" class="filterInput" style="flex:1; min-width:140px;" onchange="this.form.submit()">
                <option value="">— Semua Status —</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>🟡 Menunggu ({{ $totalPending ?? 0 }})</option>
                <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>🟢 Disetujui</option>
                <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>🔴 Ditolak</option>
            </select>
            <input type="date" name="tanggal" class="filterInput" style="flex:1; min-width:130px;" value="{{ request('tanggal') }}">
            <input type="text" name="cari" class="filterInput" style="flex:2; min-width:180px;" value="{{ request('cari') }}" placeholder="Nama tutor / siswa...">
            <button type="submit" class="filterBtn">
                <ion-icon name="search-outline" style="font-size:15px;vertical-align:middle;"></ion-icon> Cari
            </button>
        </div>
    </div>
</form>

{{-- ── Summary Badges ── --}}
<div class="summaryBadgeGrid">
    <div class="summaryBadge">
        <div class="summaryBadgeIcon" style="color:#0284c7;">
            <ion-icon name="document-text-outline"></ion-icon>
        </div>
        <div>
            <div class="summaryBadgeNum">{{ $total }}</div>
            <div class="summaryBadgeLbl">Total Pengajuan</div>
        </div>
    </div>
    <div class="summaryBadge">
        <div class="summaryBadgeIcon" style="color:#f59e0b;">
            <ion-icon name="time-outline"></ion-icon>
        </div>
        <div>
            <div class="summaryBadgeNum" style="color:#d97706;">{{ $totalPending ?? 0 }}</div>
            <div class="summaryBadgeLbl">Menunggu Verifikasi</div>
        </div>
    </div>
</div>

{{-- ── List ── --}}
<div class="sectionTitleRow" style="padding-top:4px; padding-bottom:8px;">
    <h2>Daftar Pengajuan Lupa Lapor</h2>
    @if(request('tanggal') || request('cari') || request('status'))
        <a href="{{ route('kepsek.lupa-lapor') }}" class="mutedLink">Reset Filter &rsaquo;</a>
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
        $status   = $item->status ?? 'pending';
    @endphp
    <div class="kllCard">
        {{-- Strip header --}}
        <div class="kllCardStrip">
            <div class="kllTutorInfo">
                <div class="kllTutorName">{{ $tutorNama }}</div>
                <div class="kllTutorId">ID Tutor: {{ $item->tutor_id }}</div>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                @if($status === 'disetujui')
                    <span class="badgeStatus disetujui">
                        <ion-icon name="checkmark-circle-outline" style="vertical-align:middle;"></ion-icon> Disetujui
                    </span>
                @elseif($status === 'ditolak')
                    <span class="badgeStatus ditolak">
                        <ion-icon name="close-circle-outline" style="vertical-align:middle;"></ion-icon> Ditolak
                    </span>
                @else
                    <span class="badgeStatus pending">
                        <ion-icon name="time-outline" style="vertical-align:middle;"></ion-icon> Menunggu
                    </span>
                @endif
                <div class="kllDate" style="font-size:12px; color:var(--muted); font-weight:500;">{{ $tgl }}</div>
            </div>
        </div>

        {{-- Body --}}
        <div class="kllCardBody">
            <div class="kllRow" style="margin-bottom:10px;">
                <div class="kllLeft">
                    <div class="kllSiswaLabel">Siswa yang Diajar:</div>
                    <div class="kllSiswaVal">{{ $siswaNama }}</div>
                    <div class="kllJamBox">
                        Jam Mengajar: <strong>{{ $jMulai }}</strong> s/d <strong>{{ $jSelesai }}</strong>
                    </div>
                </div>
            </div>

            <div class="kllAlasanWrap">
                <div class="kllAlasanLbl">Alasan Lupa Lapor:</div>
                <div class="kllAlasanTxt">{{ $item->alasan }}</div>
                @if($item->catatan_kepsek)
                    <div style="margin-top:6px; border-top:1px dashed var(--border); padding-top:6px; font-size:11px; color:#0284c7;">
                        <strong>Catatan Respon Kepsek:</strong> {{ $item->catatan_kepsek }}
                    </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="kllActions">
                @if($status === 'pending')
                    <form method="POST" action="{{ route('kepsek.lupa-lapor.setujui', $item->id) }}" style="flex:1;" onsubmit="return confirm('Setujui pengajuan ini? Data presensi akan otomatis dicatat ke rekap.');">
                        @csrf @method('PATCH')
                        <button type="submit" style="width:100%; background:#10b981; color:#fff; border:none; padding:8px 12px; border-radius:8px; font-weight:700; font-size:12px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:4px;">
                            <ion-icon name="checkmark-circle-outline" style="font-size:16px;"></ion-icon> Setujui
                        </button>
                    </form>

                    <form method="POST" action="{{ route('kepsek.lupa-lapor.tolak', $item->id) }}" style="flex:1;" onsubmit="return confirm('Tolak pengajuan ini?');">
                        @csrf @method('PATCH')
                        <button type="submit" style="width:100%; background:#f59e0b; color:#fff; border:none; padding:8px 12px; border-radius:8px; font-weight:700; font-size:12px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:4px;">
                            <ion-icon name="close-circle-outline" style="font-size:16px;"></ion-icon> Tolak
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('kepsek.lupa-lapor.destroy', $item->id) }}" onsubmit="return confirm('Hapus permanen pengajuan ini?');">
                    @csrf @method('DELETE')
                    <button type="submit" style="background:rgba(239, 68, 68, 0.12); color:#ef4444; border:1px solid rgba(239, 68, 68, 0.25); padding:8px 12px; border-radius:8px; font-weight:700; font-size:12px; cursor:pointer; display:flex; align-items:center; gap:4px;">
                        <ion-icon name="trash-outline" style="font-size:15px;"></ion-icon> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
        <div class="emptyState" style="padding:40px 20px;">
            <ion-icon name="checkmark-circle-outline" style="font-size:40px; color:var(--muted); opacity:0.6; display:block; margin:0 auto 8px;"></ion-icon>
            <p style="margin:0; font-size:13px; color:var(--muted); text-align:center;">{{ request('tanggal') || request('cari') || request('status') ? 'Tidak ada pengajuan yang cocok dengan filter.' : 'Belum ada pengajuan lupa lapor dari tutor.' }}</p>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($items->hasPages())
<div class="paginatePad" style="margin-top:16px;">
    {{ $items->withQueryString()->links() }}
</div>
@endif

<div style="height: 20px;"></div>

@endsection
