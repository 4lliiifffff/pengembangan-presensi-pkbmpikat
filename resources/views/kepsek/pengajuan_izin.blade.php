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
<form method="GET" action="{{ route('kepsek.pengajuan-izin') }}">
    <div class="filterBarKll" style="display:flex; flex-direction:column; gap:8px; margin-bottom:16px;">
        <div class="filterRowTop" style="display:flex; gap:8px; flex-wrap:wrap;">
            <select name="status" class="filterInput" style="flex:1; min-width:140px;" onchange="this.form.submit()">
                <option value="">— Semua Status —</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>🟡 Menunggu ({{ $totalPending ?? 0 }})</option>
                <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>🟢 Disetujui</option>
                <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>🔴 Ditolak</option>
            </select>
            <select name="jenis" class="filterInput" style="flex:1; min-width:120px;" onchange="this.form.submit()">
                <option value="">— Semua Jenis —</option>
                <option value="izin" {{ request('jenis') === 'izin' ? 'selected' : '' }}>Izin</option>
                <option value="sakit" {{ request('jenis') === 'sakit' ? 'selected' : '' }}>Sakit</option>
            </select>
            <input type="text" name="cari" class="filterInput" style="flex:2; min-width:180px;" value="{{ request('cari') }}" placeholder="Nama tutor...">
            <button type="submit" class="filterBtn" style="padding: 8px 16px;">
                <ion-icon name="search-outline" style="font-size:15px;vertical-align:middle;"></ion-icon> Cari
            </button>
        </div>
    </div>
</form>

{{-- ── Daftar Items ── --}}
@if($items->isEmpty())
    <div style="text-align:center; padding:48px 16px; background:#fff; border-radius:12px; border:1px solid #e2e8f0;">
        <ion-icon name="folder-open-outline" style="font-size:48px; color:#cbd5e1;"></ion-icon>
        <p style="margin-top:12px; color:#64748b; font-weight:500;">Tidak ada pengajuan izin/sakit ditemukan.</p>
    </div>
@else
    <div style="display:flex; flex-direction:column; gap:12px;">
        @foreach($items as $item)
            <div class="kllCard" style="background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:8px;">
                    <div>
                        <span class="badgeBadge {{ $item->jenis === 'sakit' ? 'sakit' : 'izin' }}">
                            {{ strtoupper($item->jenis_label) }}
                        </span>
                        <h3 style="margin:4px 0 2px; font-size:16px; font-weight:600; color:#1e293b;">
                            {{ $item->tutor->nama_lengkap ?? 'Tutor Tidak Ditemukan' }}
                        </h3>
                        <div style="font-size:13px; color:#64748b;">
                            📅 {{ \Carbon\Carbon::parse($item->tgl_mulai)->translatedFormat('d M Y') }}
                            @if($item->tgl_mulai !== $item->tgl_selesai)
                                s/d {{ \Carbon\Carbon::parse($item->tgl_selesai)->translatedFormat('d M Y') }}
                            @endif
                        </div>
                    </div>

                    <div>
                        @if($item->status === 'pending')
                            <span style="background:#fef3c7; color:#b45309; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:700;">🟡 MENUNGGU</span>
                        @elseif($item->status === 'disetujui')
                            <span style="background:#dcfce7; color:#15803d; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:700;">🟢 DISETUJUI</span>
                        @else
                            <span style="background:#fee2e2; color:#b91c1c; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:700;">🔴 DITOLAK</span>
                        @endif
                    </div>
                </div>

                <div style="margin-top:12px; font-size:14px; color:#334155; line-height:1.5;">
                    <strong>Alasan:</strong> {{ $item->alasan }}
                </div>

                @if($item->dokumen_url)
                    <div style="margin-top:8px;">
                        <a href="{{ $item->dokumen_url }}" target="_blank" style="font-size:13px; color:#0284c7; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                            <ion-icon name="document-attach-outline" style="font-size:16px;"></ion-icon> Lihat Surat Keterangan / Lampiran Bukti
                        </a>
                    </div>
                @endif

                @if($item->status === 'pending')
                    <div style="margin-top:16px; padding-top:12px; border-top:1px solid #f1f5f9; display:flex; gap:8px; flex-wrap:wrap;">
                        <form action="{{ route('kepsek.pengajuan-izin.setujui', $item->id) }}" method="POST" style="flex:1;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" onclick="return confirm('Setujui pengajuan ini? Data presensi akan disinkronkan secara otomatis.')" style="width:100%; padding:8px 16px; background:#10b981; color:#fff; border:none; border-radius:8px; font-weight:600; font-size:13px; cursor:pointer;">
                                ✔️ Setujui
                            </button>
                        </form>

                        <form action="{{ route('kepsek.pengajuan-izin.tolak', $item->id) }}" method="POST" style="flex:1;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" onclick="return confirm('Tolak pengajuan ini?')" style="width:100%; padding:8px 16px; background:#ef4444; color:#fff; border:none; border-radius:8px; font-weight:600; font-size:13px; cursor:pointer;">
                                ✖️ Tolak
                            </button>
                        </form>
                    </div>
                @else
                    <div style="margin-top:12px; font-size:12px; color:#64748b; background:#f8fafc; padding:8px 12px; border-radius:6px;">
                        Verifikasi oleh {{ $item->verifikator->name ?? 'Kepala Sekolah' }} pada {{ $item->updated_at->translatedFormat('d M Y H:i') }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div style="margin-top:16px;">
        {{ $items->links() }}
    </div>
@endif

<style>
.badgeBadge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
}
.badgeBadge.sakit { background: #ffeef0; color: #dc3545; }
.badgeBadge.izin { background: #e8f4fd; color: #0d6efd; }
.filterInput {
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13px;
}
.filterBtn {
    background: #0284c7;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
}
</style>

@endsection
