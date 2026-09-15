@extends('layouts.admin')

@section('content')
    <div class="pageHeaderRow">
        <h2>Edit Karyawan</h2>
        <a class="btnOutline" href="{{ route('admin.karyawan.index') }}">
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="errorList">
            <div style="font-weight:1000;margin-bottom:6px;">Periksa input berikut:</div>
            <ul style="padding-left:18px;margin:0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="formCard">
        <form method="POST" action="{{ route('admin.karyawan.update', $karyawan->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="formRow">
                <div class="fieldLabel">Nama Lengkap</div>
                <input class="input" type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $karyawan->nama_lengkap) }}" required />
            </div>

            <div class="formRow">
                <div class="fieldLabel">NIK</div>
                <input class="input" type="text" name="nik" value="{{ old('nik', $karyawan->nik) }}" required />
            </div>

            <div class="formRow">
                <div class="fieldLabel">Role</div>
                <select class="input" name="role">
                    <option value="">-- Pilih Role --</option>
                    <option value="admin" {{ old('role', $karyawan->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="tutor" {{ old('role', $karyawan->role) == 'tutor' ? 'selected' : '' }}>Tutor</option>
                    <option value="kepala_sekolah" {{ old('role', $karyawan->role) == 'kepala_sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                </select>
            </div>

            <div class="formRow">
                <div class="fieldLabel">No HP</div>
                <input class="input" type="text" name="no_hp" value="{{ old('no_hp', $karyawan->no_hp) }}" required />
            </div>

            <div class="formRow">
                <div class="fieldLabel">Foto Profil</div>
                @if ($karyawan->foto)
                    @php
                        $fotoEditUrl = str_starts_with($karyawan->foto, 'uploads/')
                            ? asset($karyawan->foto)
                            : asset('storage/' . $karyawan->foto);
                    @endphp
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                        <img src="{{ $fotoEditUrl }}" alt="Foto {{ $karyawan->nama_lengkap }}"
                            style="width:54px;height:54px;object-fit:cover;border-radius:12px;border:1px solid var(--border);display:block;" />
                        <span style="font-size:12px;color:var(--muted);">Foto saat ini</span>
                    </div>
                @endif
                <div class="fileUploadBox" style="padding:14px;">
                    <input type="file" name="foto" id="fotoKaryawanEditInput" accept="image/png,image/jpeg,image/jpg" onchange="handleFileSelected(this, 'karyawanEditFotoFeedback')">
                    <div class="fileUploadIcon" style="width:36px;height:36px;font-size:18px;">
                        <ion-icon name="image-outline"></ion-icon>
                    </div>
                    <div class="fileUploadText" style="font-size:12px;">Pilih foto baru</div>
                    <div class="fileUploadSubtext">
                        <span class="fileUploadInfoPill">Format: JPG, PNG</span>
                        <span class="fileUploadInfoPill">Maks: 2 MB</span>
                    </div>
                </div>
                <div id="karyawanEditFotoFeedback" class="fileUploadFeedback"></div>
            </div>

            <div class="formRow">
                <div class="fieldLabel">Password Baru</div>
                <input class="input" type="password" name="password" />
                <small style="color: var(--muted);">Kosongkan jika tidak ingin mengubah password.</small>
            </div>

            <div class="formRow">
                <div class="fieldLabel">Konfirmasi Password Baru</div>
                <input class="input" type="password" name="password_confirmation" />
            </div>

            <button type="submit" class="btnPrimary" style="width:100%;justify-content:center;">
                <ion-icon name="save-outline"></ion-icon>
                Simpan Perubahan
            </button>
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
