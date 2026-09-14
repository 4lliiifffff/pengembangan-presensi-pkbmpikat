@extends('layout.admin')

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
                <div class="fieldLabel">Foto</div>
                @if ($karyawan->foto)
                    @php
                        $fotoEditUrl = str_starts_with($karyawan->foto, 'uploads/')
                            ? asset($karyawan->foto)
                            : asset('storage/' . $karyawan->foto);
                    @endphp
                    <img src="{{ $fotoEditUrl }}" alt="Foto {{ $karyawan->nama_lengkap }}"
                        style="width:80px;height:80px;object-fit:cover;border-radius:8px;margin-bottom:8px;display:block;" />
                @endif
                <input class="input" type="file" name="foto" />
                <small style="color: var(--muted);">Kosongkan jika tidak ingin mengubah foto.</small>
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
@endsection
