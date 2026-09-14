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
            <ion-icon name="arrow-back-outline" class="icon-md"></ion-icon>
        </a>
        <div style="flex: 1;">
            <div class="llPageTitle">Pengajuan Izin &amp; Sakit</div>
            <div class="llPageSub">Formulir pengajuan izin atau sakit digital</div>
        </div>
        <button class="llBackBtn" type="button" aria-label="Tema" id="themeToggleBtn">
            <ion-icon name="moon-outline" class="icon-md" id="themeToggleIcon"></ion-icon>
        </button>
    </div>
</div>

{{-- ── Flash Messages ── --}}
@if(session('success'))
    <div class="flashAlert success">{{ session('success') }}</div>
@endif
@if(session('warning'))
    <div class="flashAlert warning">{{ session('warning') }}</div>
@endif

{{-- ── Tabs ── --}}
<div class="tabBar">
    <button class="tabBtn active" id="tabForm" onclick="switchTab('form', this)">
        <ion-icon name="add-circle-outline" class="icon-md" style="vertical-align:middle;"></ion-icon>
        Ajukan
    </button>
    <button class="tabBtn" id="tabRiwayat" onclick="switchTab('riwayat', this)">
        <ion-icon name="time-outline" class="icon-md" style="vertical-align:middle;"></ion-icon>
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
                <label class="formLabel" for="jenis">Jenis Pengajuan <span class="errorMsg">*</span></label>
                <select name="jenis" id="jenis" class="formControl" required>
                    <option value="izin" {{ old('jenis') == 'izin' ? 'selected' : '' }}>Izin (Keperluan Pribadi/Mendesak)</option>
                    <option value="sakit" {{ old('jenis') == 'sakit' ? 'selected' : '' }}>Sakit (Kondisi Kesehatan / Dokter)</option>
                </select>
            </div>

            <div class="inputRow">
                <div class="formGroup">
                    <label class="formLabel" for="tgl_mulai">Tanggal Mulai <span class="errorMsg">*</span></label>
                    <input type="date" name="tgl_mulai" id="tgl_mulai" class="formControl" value="{{ old('tgl_mulai', date('Y-m-d')) }}" required>
                </div>

                <div class="formGroup">
                    <label class="formLabel" for="tgl_selesai">Tanggal Selesai <span class="errorMsg">*</span></label>
                    <input type="date" name="tgl_selesai" id="tgl_selesai" class="formControl" value="{{ old('tgl_selesai', date('Y-m-d')) }}" required>
                </div>
            </div>

            <div class="formGroup">
                <label class="formLabel" for="alasan">Alasan / Keterangan Lengkap <span class="errorMsg">*</span></label>
                <textarea name="alasan" id="alasan" class="formControl input--no-resize" rows="3" placeholder="Tuliskan keterangan lengkap alasan izin/sakit..." required>{{ old('alasan') }}</textarea>
            </div>

            <div class="formGroup">
                <label class="formLabel" for="dokumen_surat">Unggah Surat Keterangan / Bukti (Opsional)</label>
                <input type="file" name="dokumen_surat" id="dokumen_surat" class="formControl" accept=".pdf,.jpg,.jpeg,.png">
                <small class="td-muted" style="display:block;margin-top:4px;">Format: PDF, JPG, PNG. Maksimal 2MB.</small>
            </div>

            <button type="submit" class="submitBtn">
                <ion-icon name="send-outline" class="icon-md" style="vertical-align:middle;"></ion-icon>
                Kirim Pengajuan
            </button>
        </form>
    </div>

</div>

{{-- ═══════════════════ PANEL RIWAYAT ═══════════════════ --}}
<div class="tabPanel" id="panelRiwayat">

    @if($riwayat->isEmpty())
    <div class="emptyLL">
        <ion-icon name="folder-open-outline"></ion-icon>
        <p>Belum ada riwayat pengajuan izin/sakit.</p>
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
                    <p class="td-muted" style="font-size:13px;margin:0 0 8px;">{{ $r->alasan }}</p>

                    @if($r->dokumen_url)
                        <a href="{{ $r->dokumen_url }}" target="_blank" class="text-primary" style="font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
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
                <div class="llAlasan" style="margin-top:10px;border-left:3px solid var(--blue2);">
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

@endsection
