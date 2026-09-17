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

<div class="pengajuanPage">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">PERIZINAN DIGITAL TUTOR</div>
                <h1 class="laporanHeaderTitle">Pengajuan Izin &amp; Sakit</h1>
                <div class="laporanHeaderSub">Formulir izin ketidakhadiran &amp; riwayat persetujuan</div>
                <p class="laporanHeaderDesc">Kirim permohonan izin/sakit dengan lampiran surat keterangan untuk ditinjau oleh Kepala Sekolah.</p>
            </div>
            <div class="laporanHeaderActions">
                <div class="badgeDate">
                    <ion-icon name="calendar-outline"></ion-icon>
                    <span>{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>
    {{-- ── Tabs ── --}}
    <div class="tabBar">
        <button class="tabBtn active" id="tabForm" onclick="switchTab('form', this)">
            <ion-icon name="add-circle-outline"></ion-icon>
            <span >Ajukan Izin</span>
        </button>
        <button class="tabBtn" id="tabRiwayat" onclick="switchTab('riwayat', this)">
            <ion-icon name="time-outline"></ion-icon>
            <span >Riwayat ({{ $riwayat->count() }})</span>
        </button>
    </div>

    {{-- ═══════════════════ PANEL FORM ═══════════════════ --}}
    <div class="tabPanel active" id="panelForm">

        @if($errors->any())
        <div class="errorBox">
            <div class="d-flex items-center gap-1 mb-1">
                <ion-icon name="alert-circle-outline" class="icon-md"></ion-icon>
                <strong >Terdapat kesalahan pada input Anda:</strong>
            </div>
            <ul >
                @foreach($errors->all() as $e)
                    <li >{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="izinFormCard">
            <form action="{{ route('tutor.pengajuan-izin.store') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-col gap-4">
                @csrf

                <div class="formGroup">
                    <label class="formLabel" for="jenis">
                        Jenis Pengajuan <span class="req">*</span>
                    </label>
                    <select name="jenis" id="jenis" class="formControl" required>
                        <option value="izin" {{ old('jenis') == 'izin' ? 'selected' : '' }}>Izin (Keperluan Pribadi / Mendesak)</option>
                        <option value="sakit" {{ old('jenis') == 'sakit' ? 'selected' : '' }}>Sakit (Kondisi Kesehatan / Surat Dokter)</option>
                    </select>
                </div>

                <div class="dateInputRow">
                    <div class="formGroup">
                        <label class="formLabel" for="tgl_mulai">
                            Tanggal Mulai <span class="req">*</span>
                        </label>
                        <input type="date" name="tgl_mulai" id="tgl_mulai" class="formControl" value="{{ old('tgl_mulai', date('Y-m-d')) }}" required>
                    </div>

                    <div class="formGroup">
                        <label class="formLabel" for="tgl_selesai">
                            Tanggal Selesai <span class="req">*</span>
                        </label>
                        <input type="date" name="tgl_selesai" id="tgl_selesai" class="formControl" value="{{ old('tgl_selesai', date('Y-m-d')) }}" required>
                    </div>
                </div>

                <div class="formGroup">
                    <label class="formLabel" for="alasan">
                        Alasan / Keterangan Lengkap <span class="req">*</span>
                    </label>
                    <textarea name="alasan" id="alasan" class="formControl input--no-resize" rows="3" placeholder="Tuliskan alasan izin atau kondisi sakit secara jelas..." required>{{ old('alasan') }}</textarea>
                </div>

                <div class="formGroup">
                    <label class="formLabel" for="dokumen_surat">
                        Unggah Dokumen / Surat Bukti <span class="text-xs text-muted font-semibold">(Opsional)</span>
                    </label>
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

                <button type="submit" class="submitBtn mt-1">
                    <ion-icon name="send-outline"></ion-icon>
                    <span >Kirim Pengajuan Izin</span>
                </button>
            </form>
        </div>

    </div>

    {{-- ═══════════════════ PANEL RIWAYAT ═══════════════════ --}}
    <div class="tabPanel" id="panelRiwayat">

        @if($riwayat->isEmpty())
        <div class="emptyLL">
            <ion-icon name="folder-open-outline"></ion-icon>
            <p class="m-0 font-bold">Belum ada riwayat pengajuan izin/sakit.</p>
            <button type="button" onclick="switchTab('form', document.getElementById('tabForm'))" class="btnOutline mt-2">
                <span >Buat Pengajuan Baru</span>
            </button>
        </div>
        @else
        <div class="izinList">
            @foreach($riwayat as $r)
            <div class="izinCard">
                <div class="izinCardHeader">
                    <div >
                        <span class="pill {{ $r->jenis === 'sakit' ? 'sakit' : 'izin' }}">
                            {{ strtoupper($r->jenis_label) }}
                        </span>
                        <div class="izinDateTitle">
                            <ion-icon name="calendar-outline" class="text-primary"></ion-icon>
                            <span >
                                {{ \Carbon\Carbon::parse($r->tgl_mulai)->translatedFormat('d M Y') }}
                                @if($r->tgl_mulai !== $r->tgl_selesai)
                                    — {{ \Carbon\Carbon::parse($r->tgl_selesai)->translatedFormat('d M Y') }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="text-right d-flex flex-col items-end gap-1">
                        @if($r->status === 'pending')
                            <span class="pill pending">MENUNGGU</span>
                            <form action="{{ route('tutor.pengajuan-izin.destroy', $r->id) }}" method="POST"
                                data-confirm="Apakah Anda yakin ingin membatalkan pengajuan ini?"
                                data-confirm-title="Batalkan Pengajuan"
                                data-confirm-type="danger"
                                data-confirm-btn="Ya, Batalkan">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="deleteBtn" title="Batalkan Pengajuan">
                                    <ion-icon name="trash-outline"></ion-icon>
                                    <span >Batalkan</span>
                                </button>
                            </form>
                        @elseif($r->status === 'disetujui')
                            <span class="pill hadir">DISETUJUI</span>
                        @else
                            <span class="pill alpha">DITOLAK</span>
                        @endif
                    </div>
                </div>

                <div class="izinAlasan">
                    <div  class="text-xs font-extrabold text-uppercase text-muted mb-1 letter-spacing-sm">
                        Alasan / Keterangan:
                    </div>
                    {{ $r->alasan }}
                </div>

                @if($r->dokumen_url)
                    <div class="mt-2">
                        <a href="{{ $r->dokumen_url }}" target="_blank" class="izinDocBtn">
                            <ion-icon name="document-attach-outline"></ion-icon>
                            <span >Lihat Dokumen Surat Bukti</span>
                        </a>
                    </div>
                @endif

                @if($r->catatan_verifikasi)
                    <div class="izinCatatan">
                        <strong class="text-primary">Catatan Verifikasi:</strong> {{ $r->catatan_verifikasi }}
                    </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

    </div>
</div>

<script >
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
        feedback.innerHTML = '<ion-icon name="document-text-outline" class="icon-sm"></ion-icon> <span >' + file.name + ' (' + sizeKb + ' KB)</span>';
        feedback.style.display = 'flex';
    } else {
        feedback.style.display = 'none';
    }
}
</script>

@endsection
