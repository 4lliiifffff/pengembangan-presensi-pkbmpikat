@extends('layouts.presensi')

@section('title', 'Pengajuan Izin & Sakit')

@php
    $user        = auth()->user();
    $displayName = (string) (($user->nama_lengkap ?? $user->name) ?? 'Tutor');
@endphp

@push('topbar')
    @include('layouts.components.navigasi_atas', [
        'titleName' => 'Pengajuan Izin & Sakit',
        'subTitle' => $displayName . ' • Izin Digital',
        'dashRoute' => route('tutor.dashboard'),
        'backRoute' => route('tutor.dashboard'),
    ])
@endpush

@section('content')

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
                <div class="fileUploadBox">
                    <input type="file" name="dokumen_surat" id="dokumen_surat" accept=".pdf,.jpg,.jpeg,.png" onchange="handleFileSelected(this, 'izinDocFeedback')">
                    <div class="fileUploadIcon">
                        <ion-icon name="document-attach-outline"></ion-icon>
                    </div>
                    <div class="fileUploadText">Pilih atau seret berkas surat bukti</div>
                    <div class="fileUploadSubtext">
                        <span class="fileUploadInfoPill">Format: PDF, JPG, PNG</span>
                        <span class="fileUploadInfoPill">Maks: 2 MB</span>
                    </div>
                </div>
                <div id="izinDocFeedback" class="fileUploadFeedback"></div>
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
                        <form action="{{ route('tutor.pengajuan-izin.destroy', $r->id) }}" method="POST" style="margin-top:8px;" data-confirm="Apakah Anda yakin ingin membatalkan pengajuan ini?" data-confirm-title="Batalkan Pengajuan" data-confirm-type="danger" data-confirm-btn="Ya, Batalkan">
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

function handleFileSelected(input, feedbackId) {
    const feedback = document.getElementById(feedbackId);
    if (!feedback) return;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const sizeKb = Math.round(file.size / 1024);
        feedback.innerHTML = '<ion-icon name="document-text-outline" style="font-size:16px;"></ion-icon> <span>' + file.name + ' (' + sizeKb + ' KB)</span>';
        feedback.style.display = 'flex';
    } else {
        feedback.style.display = 'none';
    }
}
</script>

@endsection
