@extends('layouts.presensi')

@section('title', 'Pengajuan Izin & Sakit')

@section('content')

@php
    $user        = auth()->user();
    $displayName = (string) (($user->nama_lengkap ?? $user->name) ?? 'Tutor');
@endphp

{{-- ── Top Bar ── --}}
<div class="llTopBar">
    <div class="llTopRow">
        <a href="{{ route('tutor.dashboard') }}" class="llBackBtn" aria-label="Kembali">
            <ion-icon name="arrow-back-outline" style="font-size:20px;"></ion-icon>
        </a>
        <div style="flex: 1;">
            <div class="llPageTitle">Pengajuan Izin & Sakit</div>
            <div class="llPageSub">Formulir pengajuan izin atau sakit digital</div>
        </div>
        <button class="llBackBtn" type="button" aria-label="Tema" id="themeToggleBtn">
            <ion-icon name="moon-outline" style="font-size:20px;" id="themeToggleIcon"></ion-icon>
        </button>
    </div>
</div>

{{-- ── Flash Messages ── --}}
@if(session('success'))
    <div class="flashAlert success" style="margin:12px 16px 0;">{{ session('success') }}</div>
@endif
@if(session('warning'))
    <div class="flashAlert warning" style="margin:12px 16px 0;">{{ session('warning') }}</div>
@endif

{{-- ── Tabs ── --}}
<div class="tabBar">
    <button class="tabBtn active" id="tabForm" onclick="switchTab('form', this)">
        <ion-icon name="add-circle-outline" style="font-size:15px;vertical-align:middle;"></ion-icon>
        Ajukan
    </button>
    <button class="tabBtn" id="tabRiwayat" onclick="switchTab('riwayat', this)">
        <ion-icon name="time-outline" style="font-size:15px;vertical-align:middle;"></ion-icon>
        Riwayat ({{ $riwayat->count() }})
    </button>
</div>

{{-- ═══════════════════ PANEL FORM ═══════════════════ --}}
<div class="tabPanel active" id="panelForm">

    @if($errors->any())
    <div class="errorBox" style="margin-top:12px;">
        <ul>
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="formCard">
        <form action="{{ route('tutor.pengajuan-izin.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="formGroup">
                <label class="formLabel" for="jenis">Jenis Pengajuan <span style="color:var(--danger,#dc3545);">*</span></label>
                <select name="jenis" id="jenis" class="formControl" required>
                    <option value="izin" {{ old('jenis') == 'izin' ? 'selected' : '' }}>Izin (Keperluan Pribadi/Mendesak)</option>
                    <option value="sakit" {{ old('jenis') == 'sakit' ? 'selected' : '' }}>Sakit (Kondisi Kesehatan / Dokter)</option>
                </select>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="formGroup">
                    <label class="formLabel" for="tgl_mulai">Tanggal Mulai <span style="color:var(--danger,#dc3545);">*</span></label>
                    <input type="date" name="tgl_mulai" id="tgl_mulai" class="formControl" value="{{ old('tgl_mulai', date('Y-m-d')) }}" required>
                </div>

                <div class="formGroup">
                    <label class="formLabel" for="tgl_selesai">Tanggal Selesai <span style="color:var(--danger,#dc3545);">*</span></label>
                    <input type="date" name="tgl_selesai" id="tgl_selesai" class="formControl" value="{{ old('tgl_selesai', date('Y-m-d')) }}" required>
                </div>
            </div>

            <div class="formGroup">
                <label class="formLabel" for="alasan">Alasan / Keterangan Lengkap <span style="color:var(--danger,#dc3545);">*</span></label>
                <textarea name="alasan" id="alasan" class="formControl" rows="3" placeholder="Tuliskan keterangan lengkap alasan izin/sakit..." required>{{ old('alasan') }}</textarea>
            </div>

            <div class="formGroup">
                <label class="formLabel" for="dokumen_surat">Unggah Surat Keterangan / Bukti (Opsional)</label>
                <input type="file" name="dokumen_surat" id="dokumen_surat" class="formControl" accept=".pdf,.jpg,.jpeg,.png">
                <small style="color:var(--sub,#6c757d);display:block;margin-top:4px;">Format: PDF, JPG, PNG. Maksimal 2MB.</small>
            </div>

            <button type="submit" class="submitBtn">
                <ion-icon name="send-outline" style="font-size:17px;vertical-align:middle;"></ion-icon>
                Kirim Pengajuan
            </button>
        </form>
    </div>

</div>

{{-- ═══════════════════ PANEL RIWAYAT ═══════════════════ --}}
<div class="tabPanel" id="panelRiwayat">

    @if($riwayat->isEmpty())
    <div style="text-align:center;padding:48px 16px;color:var(--sub,#6c757d);">
        <ion-icon name="folder-open-outline" style="font-size:48px;opacity:0.4;"></ion-icon>
        <p style="margin-top:12px;font-weight:500;">Belum ada riwayat pengajuan izin/sakit.</p>
    </div>
    @else
    <div style="padding:12px 16px 80px;">
        @foreach($riwayat as $r)
        <div class="riwayatCard">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <span class="badgeBadge {{ $r->jenis === 'sakit' ? 'sakit' : 'izin' }}">
                        {{ strtoupper($r->jenis_label) }}
                    </span>
                    <h4 style="margin:6px 0 2px;font-size:15px;font-weight:600;">
                        {{ \Carbon\Carbon::parse($r->tgl_mulai)->translatedFormat('d M Y') }}
                        @if($r->tgl_mulai !== $r->tgl_selesai)
                            s/d {{ \Carbon\Carbon::parse($r->tgl_selesai)->translatedFormat('d M Y') }}
                        @endif
                    </h4>
                    <p style="font-size:13px;color:var(--sub,#6c757d);margin:0 0 8px;">{{ $r->alasan }}</p>

                    @if($r->dokumen_url)
                        <a href="{{ $r->dokumen_url }}" target="_blank" style="font-size:12px;color:var(--primary,#0B5ED7);text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                            <ion-icon name="document-attach-outline"></ion-icon> Lihat Dokumen Surat
                        </a>
                    @endif
                </div>

                <div style="text-align:right;">
                    @if($r->status === 'pending')
                        <span class="badgeStatus pending">MENUNGGU</span>
                        <form action="{{ route('tutor.pengajuan-izin.destroy', $r->id) }}" method="POST" style="margin-top:8px;" onsubmit="return confirm('Batalkan pengajuan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btnDelete" title="Batalkan Pengajuan">
                                <ion-icon name="trash-outline"></ion-icon>
                            </button>
                        </form>
                    @elseif($r->status === 'disetujui')
                        <span class="badgeStatus disetujui">DISETUJUI</span>
                    @else
                        <span class="badgeStatus ditolak">DITOLAK</span>
                    @endif
                </div>
            </div>

            @if($r->catatan_verifikasi)
                <div style="margin-top:10px;padding:8px 12px;background:var(--bg-sub,#f8f9fa);border-left:3px solid var(--primary,#0B5ED7);border-radius:4px;font-size:12px;">
                    <strong>Catatan:</strong> {{ $r->catatan_verifikasi }}
                </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

</div>

<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.tabBtn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tabPanel').forEach(p => p.classList.remove('active'));

    btn.classList.add('active');
    if (tab === 'form') {
        document.getElementById('panelForm').classList.add('active');
    } else {
        document.getElementById('panelRiwayat').classList.add('active');
    }
}
</script>

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

.badgeStatus {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
}
.badgeStatus.pending { background: #fff3cd; color: #856404; }
.badgeStatus.disetujui { background: #d4edda; color: #155724; }
.badgeStatus.ditolak { background: #f8d7da; color: #721c24; }

.riwayatCard {
    background: var(--bg-card, #fff);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border: 1px solid var(--border-color, #e9ecef);
}

.btnDelete {
    background: #fff0f1;
    color: #dc3545;
    border: none;
    border-radius: 6px;
    padding: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
</style>

@endsection
