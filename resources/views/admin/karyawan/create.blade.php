@extends('layout.admin')

@section('content')
    <div class="pageHeaderRow">
        <h2>Tambah Karyawan</h2>
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
        <form method="POST" action="{{ route('admin.karyawan.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="formRow">
                <div class="fieldLabel">Nama Lengkap</div>
                <input class="input" type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required />
            </div>

            <div class="formRow">
                <div class="fieldLabel">NIK</div>
                <input class="input" type="text" name="nik" value="{{ old('nik') }}" required />
            </div>

            <div class="formRow">
                <div class="fieldLabel">Role</div>
                <select class="input" name="role">
                    <option value="">-- Pilih Role --</option>
                    <option value="admin">Admin</option>
                    <option value="tutor">Tutor</option>
                    <option value="kepala_sekolah">Kepala Sekolah</option>
                </select>
            </div>

            <div class="formRow">
                <div class="fieldLabel">No HP</div>
                <input class="input" type="text" name="no_hp" value="{{ old('no_hp') }}" required />
            </div>

            <div class="formRow">
                <div class="fieldLabel">Foto</div>
                <input class="input" type="file" name="foto" value="{{ old('foto') }}" />
            </div>

            <div class="formRow">
                <div class="fieldLabel">Password</div>
                <input class="input" type="password" name="password" value="{{ old('password') }}" required />
            </div>

            <div class="formRow">
                <div class="fieldLabel">Konfirmasi Password</div>
                <input class="input" type="password" name="password_confirmation" value="{{ old('password_confirmation') }}" required />
            </div>

            <button type="submit" class="btnPrimary" style="width:100%;justify-content:center;">
                <ion-icon name="save-outline"></ion-icon>
                Simpan
            </button>
        </form>
    </div>
@endsection
