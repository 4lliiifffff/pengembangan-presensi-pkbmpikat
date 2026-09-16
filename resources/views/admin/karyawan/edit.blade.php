@extends('layouts.admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader" style="padding-left: 0; padding-right: 0; margin-bottom: 16px;">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">PERBARUI DATA</div>
                <h1 class="laporanHeaderTitle">Edit Karyawan &amp; Tutor</h1>
                <div class="laporanHeaderSub">Perbarui profil, hak akses, dan informasi kontak akun</div>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.karyawan.index') }}" class="btnOutline" style="padding:9px 16px;font-size:12.5px;font-weight:700;">
                    Kembali
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="errorList" style="margin-bottom: 16px;">
            <div style="font-weight:800;margin-bottom:8px;">Periksa input berikut:</div>
            <ul style="padding-left:20px;margin:0;line-height:1.5;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="laporanFilterCard" style="padding: 24px;">
        <form method="POST" action="{{ route('admin.karyawan.update', $karyawan->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px;">
                <div class="filterField">
                    <label class="filterFieldLabel">Nama Lengkap <span style="color:#ef4444;">*</span></label>
                    <input class="profileInput" type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $karyawan->nama_lengkap) }}" required style="height: 42px; font-size: 13px;" />
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">NIK (Nomor Induk Karyawan) <span style="color:#ef4444;">*</span></label>
                    <input class="profileInput" type="text" name="nik" value="{{ old('nik', $karyawan->nik) }}" required style="height: 42px; font-size: 13px;" />
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Role &amp; Hak Akses <span style="color:#ef4444;">*</span></label>
                    <select class="filterSelect" name="role" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="tutor" {{ old('role', $karyawan->role) == 'tutor' ? 'selected' : '' }}>Tutor / Pengajar</option>
                        <option value="admin" {{ old('role', $karyawan->role) == 'admin' ? 'selected' : '' }}>Administrator</option>
                        <option value="kepala_sekolah" {{ old('role', $karyawan->role) == 'kepala_sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                    </select>
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">No HP / WhatsApp <span style="color:#ef4444;">*</span></label>
                    <input class="profileInput" type="text" name="no_hp" value="{{ old('no_hp', $karyawan->no_hp) }}" required style="height: 42px; font-size: 13px;" />
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Password Baru (Opsional)</label>
                    <input class="profileInput" type="password" name="password" placeholder="Kosongkan jika tidak diubah" style="height: 42px; font-size: 13px;" />
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Konfirmasi Password Baru</label>
                    <input class="profileInput" type="password" name="password_confirmation" placeholder="Ulangi password baru" style="height: 42px; font-size: 13px;" />
                </div>
            </div>

            <div class="filterField" style="margin-top:18px;">
                <label class="filterFieldLabel">Foto Profil</label>
                @if ($karyawan->foto)
                    @php
                        $fotoEditUrl = str_starts_with($karyawan->foto, 'uploads/')
                            ? asset($karyawan->foto)
                            : asset('storage/' . $karyawan->foto);
                    @endphp
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:12px;">
                        <img src="{{ $fotoEditUrl }}" alt="Foto {{ $karyawan->nama_lengkap }}"
                            style="width:52px;height:52px;object-fit:cover;border-radius:12px;border:1px solid var(--border);display:block;" />
                        <span style="font-size:12.5px;color:var(--muted);font-weight:600;">Foto saat ini tersimpan</span>
                    </div>
                @endif
                <div class="fileUploadBox">
                    <input type="file" name="foto" id="fotoKaryawanEditInput" accept="image/png,image/jpeg,image/jpg" onchange="handleFileSelected(this, 'karyawanEditFotoFeedback')">
                    <div class="fileUploadText" style="margin-top:0;font-size:13px;font-weight:700;">Pilih foto baru untuk mengganti</div>
                    <div class="fileUploadSubtext">
                        <span class="fileUploadInfoPill">Format: JPG, PNG</span>
                        <span class="fileUploadInfoPill">Maks: 2 MB</span>
                    </div>
                </div>
                <div id="karyawanEditFotoFeedback" class="fileUploadFeedback"></div>
            </div>

            <div style="margin-top:24px;padding-top:18px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                <a href="{{ route('admin.karyawan.index') }}" class="profileBtnDanger" style="height:42px;padding:0 20px;font-size:13px;border-radius:12px;width:auto;text-decoration:none;display:inline-flex;align-items:center;">
                    Batal
                </a>
                <button type="submit" class="profileBtnPrimary" style="height:42px;padding:0 24px;font-size:13px;border-radius:12px;border:none;cursor:pointer;">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function handleFileSelected(input, feedbackId) {
        const feedback = document.getElementById(feedbackId);
        if (!feedback) return;
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const sizeKb = Math.round(file.size / 1024);
            feedback.innerHTML = '<span>' + file.name + ' (' + sizeKb + ' KB)</span>';
            feedback.style.display = 'flex';
        } else {
            feedback.style.display = 'none';
        }
    }
</script>
@endsection
