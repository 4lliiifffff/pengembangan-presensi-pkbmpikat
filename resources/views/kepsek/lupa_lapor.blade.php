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
    <div class="filterBarKll" style="display:flex; flex-direction:column; gap:8px; margin-bottom:16px;">
        <div class="filterRowTop" style="display:flex; gap:8px; flex-wrap:wrap;">
            <select name="status" class="filterInput" style="flex:1; min-width:140px;" onchange="this.form.submit()">
                <option value="">— Semua Status —</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>🟡 Menunggu ({{ $totalPending ?? 0 }})</option>
                <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>🟢 Disetujui</option>
                <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>🔴 Ditolak</option>
            </select>
            <input type="date" name="tanggal" class="filterInput" style="flex:1; min-width:130px;" value="{{ request('tanggal') }}">
            <input type="text" name="cari" class="filterInput" style="flex:2; min-width:180px;" value="{{ request('cari') }}" placeholder="Nama tutor / siswa...">
            <button type="submit" class="filterBtn" style="padding: 8px 16px;">
                <ion-icon name="search-outline" style="font-size:15px;vertical-align:middle;"></ion-icon> Cari
            </button>
        </div>
    </div>
</form>

{{-- ── Summary Badges ── --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap:10px; margin-bottom:16px;">
    <div class="summaryBadge" style="padding:12px; background:#fff; border-radius:10px; border:1px solid #e2e8f0; display:flex; align-items:center; gap:10px;">
        <div class="summaryBadgeIcon" style="color:#0284c7;">
            <ion-icon name="document-text-outline" style="font-size:22px;"></ion-icon>
        </div>
        <div>
            <div class="summaryBadgeNum" style="font-weight:700; font-size:18px;">{{ $total }}</div>
            <div class="summaryBadgeLbl" style="font-size:11px; color:#64748b;">Total Pengajuan</div>
        </div>
    </div>
    <div class="summaryBadge" style="padding:12px; background:#fff; border-radius:10px; border:1px solid #e2e8f0; display:flex; align-items:center; gap:10px;">
        <div class="summaryBadgeIcon" style="color:#f59e0b;">
            <ion-icon name="time-outline" style="font-size:22px;"></ion-icon>
        </div>
        <div>
            <div class="summaryBadgeNum" style="font-weight:700; font-size:18px; color:#d97706;">{{ $totalPending ?? 0 }}</div>
            <div class="summaryBadgeLbl" style="font-size:11px; color:#64748b;">Menunggu Verifikasi</div>
        </div>
    </div>
</div>

{{-- ── List ── --}}
<div class="sectionRow" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
    <h2 style="font-size:16px; font-weight:600; margin:0;">Daftar Pengajuan Lupa Lapor</h2>
    @if(request('tanggal') || request('cari') || request('status'))
        <a href="{{ route('kepsek.lupa-lapor') }}" class="mutedLink" style="font-size:12px; color:#0284c7;">Reset Filter &rsaquo;</a>
    @endif
</div>

<div class="kllList" style="display:flex; flex-direction:column; gap:12px;">
    @forelse($items as $item)
    @php
        $tgl      = \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d F Y');
        $jMulai   = substr((string) $item->jam_mulai, 0, 5);
        $jSelesai = substr((string) $item->jam_selesai, 0, 5);
        $tutorNama = $item->tutor->nama_lengkap ?? 'Tutor #'.$item->tutor_id;
        $siswaNama = $item->siswa->nama_siswa  ?? 'Siswa #'.$item->siswa_id;
        $status   = $item->status ?? 'pending';
    @endphp
    <div class="kllCard" style="background:#fff; border-radius:12px; border:1px solid #e2e8f0; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        {{-- Strip header --}}
        <div class="kllCardStrip" style="padding:10px 14px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
            <div class="kllTutorInfo">
                <div class="kllTutorName" style="font-weight:600; font-size:14px; color:#0f172a;">{{ $tutorNama }}</div>
                <div class="kllTutorId" style="font-size:11px; color:#64748b;">ID Tutor: {{ $item->tutor_id }}</div>
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
                <div class="kllDate" style="font-size:12px; color:#475569; font-weight:500;">{{ $tgl }}</div>
            </div>
        </div>

        {{-- Body --}}
        <div class="kllCardBody" style="padding:14px;">
            <div class="kllRow" style="margin-bottom:10px;">
                <div class="kllLeft">
                    <div class="kllSiswaLabel" style="font-size:11px; color:#64748b;">Siswa yang Diajar:</div>
                    <div class="kllSiswaVal" style="font-weight:600; font-size:13px; color:#1e293b;">{{ $siswaNama }}</div>
                    <div class="kllJamBox" style="margin-top:6px; font-size:12px; color:#475569;">
                        Jam Mengajar: <strong>{{ $jMulai }}</strong> s/d <strong>{{ $jSelesai }}</strong>
                    </div>
                </div>
            </div>

            <div class="kllAlasanWrap" style="background:#f1f5f9; padding:10px; border-radius:8px; margin-bottom:12px;">
                <div class="kllAlasanLbl" style="font-size:11px; font-weight:600; color:#475569; margin-bottom:2px;">Alasan Lupa Lapor:</div>
                <div class="kllAlasanTxt" style="font-size:12px; color:#334155; line-height:1.4;">{{ $item->alasan }}</div>
                @if($item->catatan_kepsek)
                    <div style="margin-top:6px; border-top:1px dashed #cbd5e1; padding-top:6px; font-size:11px; color:#0284c7;">
                        <strong>Catatan Respon Kepsek:</strong> {{ $item->catatan_kepsek }}
                    </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="kllActions" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center; border-top:1px solid #f1f5f9; padding-top:10px;">
                @if($status === 'pending')
                    <form method="POST" action="{{ route('kepsek.lupa-lapor.setujui', $item->id) }}" style="flex:1;" onsubmit="return confirm('Setujui pengajuan ini? Data presensi akan otomatis dicatat ke rekap.');">
                        @csrf @method('PATCH')
                        <button type="submit" style="width:100%; background:#10b981; color:#fff; border:none; padding:8px 12px; border-radius:6px; font-weight:600; font-size:12px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:4px;">
                            <ion-icon name="checkmark-circle-outline" style="font-size:16px;"></ion-icon> Setujui
                        </button>
                    </form>

                    <form method="POST" action="{{ route('kepsek.lupa-lapor.tolak', $item->id) }}" style="flex:1;" onsubmit="return confirm('Tolak pengajuan ini?');">
                        @csrf @method('PATCH')
                        <button type="submit" style="width:100%; background:#f59e0b; color:#fff; border:none; padding:8px 12px; border-radius:6px; font-weight:600; font-size:12px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:4px;">
                            <ion-icon name="close-circle-outline" style="font-size:16px;"></ion-icon> Tolak
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('kepsek.lupa-lapor.destroy', $item->id) }}" onsubmit="return confirm('Hapus permanen pengajuan ini?');">
                    @csrf @method('DELETE')
                    <button type="submit" style="background:#fee2e2; color:#ef4444; border:1px solid #fca5a5; padding:8px 12px; border-radius:6px; font-weight:600; font-size:12px; cursor:pointer; display:flex; align-items:center; gap:4px;">
                        <ion-icon name="trash-outline" style="font-size:15px;"></ion-icon> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
        <div class="emptyKll" style="padding:40px 20px; text-align:center; background:#fff; border-radius:12px; border:1px solid #e2e8f0; color:#64748b;">
            <ion-icon name="checkmark-circle-outline" style="font-size:40px; color:#94a3b8;"></ion-icon>
            <p style="margin-top:8px; font-size:13px;">{{ request('tanggal') || request('cari') || request('status') ? 'Tidak ada pengajuan yang cocok dengan filter.' : 'Belum ada pengajuan lupa lapor dari tutor.' }}</p>
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
