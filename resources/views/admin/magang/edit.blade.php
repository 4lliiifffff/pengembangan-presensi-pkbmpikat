@extends('layouts.admin')

@section('title', 'Edit Peserta Magang / PKL')

@section('content')
@php
    $detail = $magang->magang;
@endphp

<div class="pageHeaderRow">
    <div>
        <h2 style="margin:0;">Edit Peserta Magang: {{ $magang->nama_lengkap ?? $magang->name }}</h2>
        <p style="margin:2px 0 0;font-size:12px;color:var(--muted);">Perbarui data informasi instansi, akun, atau masa berlaku magang</p>
    </div>
    <a class="btnOutline" href="{{ route('admin.magang.index') }}">
        <ion-icon name="arrow-back-outline"></ion-icon> Kembali
    </a>
</div>

@if ($errors->any())
    <div class="errorList" style="background:#fef2f2; border:1px solid #fecaca; padding:12px 16px; border-radius:12px; color:#991b1b; margin:16px;">
        <div style="font-weight:700;margin-bottom:6px;">Periksa input berikut:</div>
        <ul style="padding-left:18px;margin:0;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="formCard" style="margin:16px; background:var(--card,#fff); border-radius:16px; padding:24px; border:1px solid var(--border,#e2e8f0);">
    <form method="POST" action="{{ route('admin.magang.update', $magang->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <h3 style="font-size:15px; font-weight:700; color:var(--primary); margin-top:0; margin-bottom:16px; border-bottom:1px solid var(--border,#e2e8f0); padding-bottom:8px;">
            1. Informasi Akun &amp; Pribadi
        </h3>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-bottom:20px;">
            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Nama Lengkap <span style="color:#dc2626;">*</span></label>
                <input class="input" type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $magang->nama_lengkap ?? $magang->name) }}" required style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">NIK / No. Identitas Sistem <span style="color:#dc2626;">*</span></label>
                <input class="input" type="text" name="nik" value="{{ old('nik', $magang->nik) }}" required style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Email Login <span style="color:#dc2626;">*</span></label>
                <input class="input" type="email" name="email" value="{{ old('email', $magang->email) }}" required style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Password Baru (Kosongkan jika tidak diubah)</label>
                <input class="input" type="password" name="password" placeholder="Minimal 6 karakter" style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">No. WhatsApp / HP</label>
                <input class="input" type="text" name="no_hp" value="{{ old('no_hp', $magang->no_hp) }}" style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Status Akun <span style="color:#dc2626;">*</span></label>
                <select name="is_active" class="input" style="width:100%;">
                    <option value="1" {{ old('is_active', $magang->is_active) == 1 ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('is_active', $magang->is_active) == 0 ? 'selected' : '' }}>Nonaktif / Selesai</option>
                </select>
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Ganti Foto Profil (Opsional)</label>
                <div class="fileUploadBox" style="padding:14px;">
                    <input type="file" name="foto" id="fotoMagangEditInput" accept="image/png,image/jpeg,image/jpg" onchange="handleFileSelected(this, 'magangEditFotoFeedback')">
                    <div class="fileUploadIcon" style="width:36px;height:36px;font-size:18px;">
                        <ion-icon name="image-outline"></ion-icon>
                    </div>
                    <div class="fileUploadText" style="font-size:12px;">Pilih foto profil baru</div>
                    <div class="fileUploadSubtext">
                        <span class="fileUploadInfoPill">Format: JPG, PNG</span>
                        <span class="fileUploadInfoPill">Maks: 2 MB</span>
                    </div>
                </div>
                <div id="magangEditFotoFeedback" class="fileUploadFeedback"></div>
            </div>
        </div>

        <h3 style="font-size:15px; font-weight:700; color:var(--primary); margin-top:24px; margin-bottom:16px; border-bottom:1px solid var(--border,#e2e8f0); padding-bottom:8px;">
            2. Informasi Instansi &amp; Periode Magang
        </h3>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-bottom:24px;">
            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Asal Universitas / Sekolah <span style="color:#dc2626;">*</span></label>
                <input class="input" type="text" name="asal_instansi" value="{{ old('asal_instansi', $detail?->asal_instansi) }}" required style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">NIM / NISN</label>
                <input class="input" type="text" name="nim_nisn" value="{{ old('nim_nisn', $detail?->nim_nisn) }}" style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Program Studi / Jurusan</label>
                <input class="input" type="text" name="jurusan" value="{{ old('jurusan', $detail?->jurusan) }}" style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Dosen / Guru Pembimbing</label>
                <input class="input" type="text" name="pembimbing_lapangan" value="{{ old('pembimbing_lapangan', $detail?->pembimbing_lapangan) }}" style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Tanggal Mulai Magang <span style="color:#dc2626;">*</span></label>
                <input class="input" type="date" name="tgl_mulai" value="{{ old('tgl_mulai', $detail?->tgl_mulai) }}" required style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Tanggal Selesai Magang <span style="color:#dc2626;">*</span></label>
                <input class="input" type="date" name="tgl_selesai" value="{{ old('tgl_selesai', $detail?->tgl_selesai) }}" required style="width:100%;" />
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <a href="{{ route('admin.magang.index') }}" class="btnOutline" style="padding:10px 20px;">Batal</a>
            <button type="submit" class="btnPrimary" style="padding:10px 24px; background:var(--blue-gradient);">Simpan Perubahan</button>
        </div>
    </form>
</div>

<script>
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
