@extends('layouts.admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">FORMULIR PENDAFTARAN</div>
                <h1 class="laporanHeaderTitle">Tambah Karyawan &amp; Tutor</h1>
                <div class="laporanHeaderSub">Daftarkan staf pengajar atau administrator baru ke dalam sistem</div>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.karyawan.index') }}" class="btnOutline">
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
        <form method="POST" action="{{ route('admin.karyawan.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-grid-responsive">
                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Nama Lengkap <span class="text-danger">*</span></label>
                    <input class="profileInput" type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" placeholder="Masukkan nama lengkap" required />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">NIK (Nomor Induk Karyawan) <span class="text-danger">*</span></label>
                    <input class="profileInput" type="text" name="nik" value="{{ old('nik') }}" placeholder="Contoh: 3201234567890001" required />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Role &amp; Hak Akses <span class="text-danger">*</span></label>
                    <select class="filterSelect" name="role" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="tutor" {{ old('role') === 'tutor' ? 'selected' : '' }}>Tutor / Pengajar</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                        <option value="kepala_sekolah" {{ old('role') === 'kepala_sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                    </select>
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">No HP / WhatsApp <span class="text-danger">*</span></label>
                    <input class="profileInput" type="text" name="no_hp" value="{{ old('no_hp') }}" placeholder="Contoh: 081234567890" required />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Password Akun <span class="text-danger">*</span></label>
                    <input class="profileInput" type="password" name="password" placeholder="Minimal 6 karakter" required />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Konfirmasi Password <span class="text-danger">*</span></label>
                    <input class="profileInput" type="password" name="password_confirmation" placeholder="Ulangi password" required />
                </div>
            </div>

            <div class="form-field-wrapper mt-4">
                <label class="filterFieldLabel">Foto Profil (Opsional)</label>
                <div class="fileUploadBox">
                    <input type="file" name="foto" id="fotoKaryawanCreateInput" accept="image/png,image/jpeg,image/jpg" onchange="handleFileSelected(this, 'karyawanCreateFotoFeedback')">
                    <div class="fileUploadIcon">
                        <ion-icon name="camera-outline"></ion-icon>
                    </div>
                    <div class="fileUploadText">Pilih atau seret foto profil</div>
                    <div class="fileUploadSubtext">
                        <span class="fileUploadInfoPill"><ion-icon name="image-outline"></ion-icon> JPG, PNG, WEBP</span>
                        <span class="fileUploadInfoPill"><ion-icon name="hardware-chip-outline"></ion-icon> Maks. 2 MB</span>
                    </div>
                </div>
                <div id="karyawanCreateFotoFeedback" class="fileUploadFeedback"></div>
            </div>

            <div class="form-action-footer">
                <a href="{{ route('admin.karyawan.index') }}" class="btnOutline">
                    Batal
                </a>
                <button type="submit" class="profileBtnPrimary">
                    <ion-icon name="save-outline"></ion-icon> Simpan Data Karyawan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
