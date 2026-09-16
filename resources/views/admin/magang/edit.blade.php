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
    <div class="error-list-container">
        <div class="error-title">Periksa input berikut:</div>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-card-container">
    <form method="POST" action="{{ route('admin.magang.update', $magang->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <h3 style="font-size:15px; font-weight:800; color:var(--text); margin-top:0; margin-bottom:16px; border-bottom:1px solid var(--border); padding-bottom:8px;">
            1. Informasi Akun &amp; Pribadi
        </h3>

        <div class="form-grid-responsive" style="margin-bottom:20px;">
            <div class="form-field-wrapper">
                <label class="form-field-label">Nama Lengkap <span style="color:#dc2626;">*</span></label>
                <input class="profileInput" type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $magang->nama_lengkap ?? $magang->name) }}" required />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">NIK / No. Identitas Sistem <span style="color:#dc2626;">*</span></label>
                <input class="profileInput" type="text" name="nik" value="{{ old('nik', $magang->nik) }}" required />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Email Login <span style="color:#dc2626;">*</span></label>
                <input class="profileInput" type="email" name="email" value="{{ old('email', $magang->email) }}" required />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Password Baru (Kosongkan jika tidak diubah)</label>
                <input class="profileInput" type="password" name="password" placeholder="Minimal 6 karakter" />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">No. WhatsApp / HP</label>
                <input class="profileInput" type="text" name="no_hp" value="{{ old('no_hp', $magang->no_hp) }}" />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Status Akun <span style="color:#dc2626;">*</span></label>
                <select name="is_active" class="profileInput">
                    <option value="1" {{ old('is_active', $magang->is_active) == 1 ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('is_active', $magang->is_active) == 0 ? 'selected' : '' }}>Nonaktif / Selesai</option>
                </select>
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Ganti Foto Profil (Opsional)</label>
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

        <h3 style="font-size:15px; font-weight:800; color:var(--text); margin-top:24px; margin-bottom:16px; border-bottom:1px solid var(--border); padding-bottom:8px;">
            2. Informasi Instansi &amp; Periode Magang
        </h3>

        <div class="form-grid-responsive" style="margin-bottom:24px;">
            <div class="form-field-wrapper">
                <label class="form-field-label">Asal Universitas / Sekolah <span style="color:#dc2626;">*</span></label>
                <input class="profileInput" type="text" name="asal_instansi" value="{{ old('asal_instansi', $detail?->asal_instansi) }}" required />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">NIM / NISN</label>
                <input class="profileInput" type="text" name="nim_nisn" value="{{ old('nim_nisn', $detail?->nim_nisn) }}" />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Program Studi / Jurusan</label>
                <input class="profileInput" type="text" name="jurusan" value="{{ old('jurusan', $detail?->jurusan) }}" />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Dosen / Guru Pembimbing</label>
                <input class="profileInput" type="text" name="pembimbing_lapangan" value="{{ old('pembimbing_lapangan', $detail?->pembimbing_lapangan) }}" />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Tanggal Mulai Magang <span style="color:#dc2626;">*</span></label>
                <input class="profileInput" type="date" name="tgl_mulai" value="{{ old('tgl_mulai', $detail?->tgl_mulai) }}" required />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Tanggal Selesai Magang <span style="color:#dc2626;">*</span></label>
                <input class="profileInput" type="date" name="tgl_selesai" value="{{ old('tgl_selesai', $detail?->tgl_selesai) }}" required />
            </div>
        </div>

        <div class="form-action-footer">
            <a href="{{ route('admin.magang.index') }}" class="btnOutline">Batal</a>
            <button type="submit" class="profileBtnPrimary" style="width: auto; padding: 0 24px;">Simpan Perubahan</button>
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
