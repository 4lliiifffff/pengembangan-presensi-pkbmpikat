@extends('layouts.admin')

@section('title', 'Edit Peserta Magang / PKL')

@section('content')
@php
    $detail = $magang->magang;
@endphp

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">PERBARUI DATA MAGANG</div>
                <h1 class="laporanHeaderTitle">Edit Peserta Magang: {{ $magang->nama_lengkap ?? $magang->name }}</h1>
                <div class="laporanHeaderSub">Perbarui data informasi instansi, akun, atau masa berlaku magang</div>
            </div>
            <div class="laporanHeaderActions">
                <a class="btnOutline" href="{{ route('admin.magang.index') }}">
                    Kembali
                </a>
            </div>
        </div>
    </div>

@if ($errors->any())
    <div class="error-list-container">
        <div class="error-title">Periksa input berikut:</div>
        <ul >
            @foreach ($errors->all() as $error)
                <li >{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-card-container">
    <form method="POST" action="{{ route('admin.magang.update', $magang->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <h3 class="card-section-title">
            1. Informasi Akun &amp; Pribadi
        </h3>

        <div class="form-grid-responsive mb-4">
            <div class="form-field-wrapper">
                <label class="form-field-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input class="profileInput" type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $magang->nama_lengkap ?? $magang->name) }}" required />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">NIK / No. Identitas Sistem <span class="text-danger">*</span></label>
                <input class="profileInput" type="text" name="nik" value="{{ old('nik', $magang->nik) }}" required />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Email Login <span class="text-danger">*</span></label>
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
                <label class="form-field-label">Status Akun <span class="text-danger">*</span></label>
                <select name="is_active" class="profileInput">
                    <option value="1" {{ old('is_active', $magang->is_active) == 1 ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('is_active', $magang->is_active) == 0 ? 'selected' : '' }}>Nonaktif / Selesai</option>
                </select>
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Ganti Foto Profil (Opsional)</label>
                <div class="fileUploadBox p-3">
                    <input type="file" name="foto" id="fotoMagangEditInput" accept="image/png,image/jpeg,image/jpg" onchange="handleFileSelected(this, 'magangEditFotoFeedback')">
                    <div class="fileUploadIcon avatar-icon-36">
                        <ion-icon name="image-outline"></ion-icon>
                    </div>
                    <div class="fileUploadText text-sm">Pilih foto profil baru</div>
                    <div class="fileUploadSubtext">
                        <span class="fileUploadInfoPill">Format: JPG, PNG</span>
                        <span class="fileUploadInfoPill">Maks: 2 MB</span>
                    </div>
                </div>
                <div id="magangEditFotoFeedback" class="fileUploadFeedback"></div>
            </div>
        </div>

        <h3 class="text-lg font-extrabold text-dark mb-4 py-2 mt-4 border-b-base">
            2. Informasi Instansi &amp; Periode Magang
        </h3>

        <div class="form-grid-responsive mb-4">
            <div class="form-field-wrapper">
                <label class="form-field-label">Asal Universitas / Sekolah <span class="text-danger">*</span></label>
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
                <label class="form-field-label">Tanggal Mulai Magang <span class="text-danger">*</span></label>
                <input class="profileInput" type="date" name="tgl_mulai" value="{{ old('tgl_mulai', $detail?->tgl_mulai) }}" required />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Tanggal Selesai Magang <span class="text-danger">*</span></label>
                <input class="profileInput" type="date" name="tgl_selesai" value="{{ old('tgl_selesai', $detail?->tgl_selesai) }}" required />
            </div>
        </div>

        <div class="form-action-footer">
            <a href="{{ route('admin.magang.index') }}" class="btnOutline">Batal</a>
            <button type="submit" class="profileBtnPrimary w-auto px-4">Simpan Perubahan</button>
        </div>
    </form>
</div>

<script >
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
</div>
@endsection
