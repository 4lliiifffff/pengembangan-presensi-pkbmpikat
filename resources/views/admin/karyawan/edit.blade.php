@extends('layouts.admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">PERBARUI DATA</div>
                <h1 class="laporanHeaderTitle">Edit Karyawan &amp; Tutor</h1>
                <div class="laporanHeaderSub">Perbarui profil, hak akses, dan informasi kontak akun</div>
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
        <form method="POST" action="{{ route('admin.karyawan.update', $karyawan->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-grid-responsive">
                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Nama Lengkap <span class="text-danger">*</span></label>
                    <input class="profileInput" type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $karyawan->nama_lengkap) }}" required />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">NIK (Nomor Induk Karyawan) <span class="text-danger">*</span></label>
                    <input class="profileInput" type="text" name="nik" value="{{ old('nik', $karyawan->nik) }}" required />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Role &amp; Hak Akses <span class="text-danger">*</span></label>
                    <select class="filterSelect" name="role" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="tutor" {{ old('role', $karyawan->role) == 'tutor' ? 'selected' : '' }}>Tutor / Pengajar</option>
                        <option value="admin" {{ old('role', $karyawan->role) == 'admin' ? 'selected' : '' }}>Administrator</option>
                        <option value="kepala_sekolah" {{ old('role', $karyawan->role) == 'kepala_sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                    </select>
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">No HP / WhatsApp <span class="text-danger">*</span></label>
                    <input class="profileInput" type="text" name="no_hp" value="{{ old('no_hp', $karyawan->no_hp) }}" required />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Password Baru (Opsional)</label>
                    <input class="profileInput" type="password" name="password" placeholder="Kosongkan jika tidak diubah" />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Konfirmasi Password Baru</label>
                    <input class="profileInput" type="password" name="password_confirmation" placeholder="Ulangi password baru" />
                </div>
            </div>

            <div class="form-field-wrapper mt-4">
                <label class="filterFieldLabel">Foto Profil</label>
                @if ($karyawan->foto)
                    @php
                        $fotoEditUrl = $karyawan->foto_url;
                    @endphp
                    <div class="d-flex items-center gap-3 mb-3">
                        <img src="{{ $fotoEditUrl }}" alt="Foto {{ $karyawan->nama_lengkap }}"
                            class="avatar-thumb-52" />
                        <span class="text-sm text-muted font-semibold">Foto saat ini tersimpan</span>
                    </div>
                @endif
                <div class="fileUploadBox">
                    <input type="file" name="foto" id="fotoKaryawanEditInput" accept="image/png,image/jpeg,image/jpg" onchange="handleFileSelected(this, 'karyawanEditFotoFeedback')">
                    <div class="fileUploadText">Pilih foto baru untuk mengganti</div>
                    <div class="fileUploadSubtext">
                        <span class="fileUploadInfoPill">Format: JPG, PNG</span>
                        <span class="fileUploadInfoPill">Maks: 2 MB</span>
                    </div>
                </div>
                <div id="karyawanEditFotoFeedback" class="fileUploadFeedback"></div>
            </div>

            <div class="form-action-footer">
                <a href="{{ route('admin.karyawan.index') }}" class="btnOutline">
                    Batal
                </a>
                <button type="submit" class="profileBtnPrimary">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script >
    function handleFileSelected(input, feedbackId) {
        const feedback = document.getElementById(feedbackId);
        if (!feedback) return;
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const sizeKb = Math.round(file.size / 1024);
            feedback.innerHTML = '<span >' + file.name + ' (' + sizeKb + ' KB)</span>';
            feedback.style.display = 'flex';
        } else {
            feedback.style.display = 'none';
        }
    }
</script>
@endsection
