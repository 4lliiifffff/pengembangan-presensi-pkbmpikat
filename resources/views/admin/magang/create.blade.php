@extends('layouts.admin')

@section('title', 'Tambah Peserta Magang / PKL')

@section('content')
<div class="pageHeaderRow">
    <div>
        <h2 style="margin:0;">Tambah Peserta Magang / PKL</h2>
        <p style="margin:2px 0 0;font-size:12px;color:var(--muted);">Daftarkan mahasiswa magang atau siswa PKL beserta informasi instansi</p>
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
    <form method="POST" action="{{ route('admin.magang.store') }}" enctype="multipart/form-data">
        @csrf
        
        <h3 style="font-size:15px; font-weight:700; color:var(--primary); margin-top:0; margin-bottom:16px; border-bottom:1px solid var(--border,#e2e8f0); padding-bottom:8px;">
            1. Informasi Akun &amp; Pribadi
        </h3>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-bottom:20px;">
            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Nama Lengkap <span style="color:#dc2626;">*</span></label>
                <input class="input" type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required placeholder="cth: Ahmad Fauzi" style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">NIK / No. Identitas Sistem <span style="color:#dc2626;">*</span></label>
                <input class="input" type="text" name="nik" value="{{ old('nik') }}" required placeholder="cth: MG202601" style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Email Login <span style="color:#dc2626;">*</span></label>
                <input class="input" type="email" name="email" value="{{ old('email') }}" required placeholder="cth: ahmad@kampus.ac.id" style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Password Login <span style="color:#dc2626;">*</span></label>
                <input class="input" type="password" name="password" required placeholder="Minimal 6 karakter" style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">No. WhatsApp / HP</label>
                <input class="input" type="text" name="no_hp" value="{{ old('no_hp') }}" placeholder="cth: 08123456789" style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Foto Profil</label>
                <input class="input" type="file" name="foto" accept="image/*" style="width:100%;" />
            </div>
        </div>

        <h3 style="font-size:15px; font-weight:700; color:var(--primary); margin-top:24px; margin-bottom:16px; border-bottom:1px solid var(--border,#e2e8f0); padding-bottom:8px;">
            2. Informasi Instansi &amp; Periode Magang
        </h3>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-bottom:24px;">
            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Asal Universitas / Sekolah <span style="color:#dc2626;">*</span></label>
                <input class="input" type="text" name="asal_instansi" value="{{ old('asal_instansi') }}" required placeholder="cth: Universitas Negeri Yogyakarta" style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">NIM / NISN</label>
                <input class="input" type="text" name="nim_nisn" value="{{ old('nim_nisn') }}" placeholder="cth: 210101002" style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Program Studi / Jurusan</label>
                <input class="input" type="text" name="jurusan" value="{{ old('jurusan') }}" placeholder="cth: Pendidikan Luar Sekolah / Teknik Informatika" style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Dosen / Guru Pembimbing</label>
                <input class="input" type="text" name="pembimbing_lapangan" value="{{ old('pembimbing_lapangan') }}" placeholder="cth: Dr. Hendra, M.Pd" style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Tanggal Mulai Magang <span style="color:#dc2626;">*</span></label>
                <input class="input" type="date" name="tgl_mulai" value="{{ old('tgl_mulai', date('Y-m-d')) }}" required style="width:100%;" />
            </div>

            <div class="formRow">
                <label class="fieldLabel" style="font-size:12px; font-weight:700; display:block; margin-bottom:6px;">Tanggal Selesai Magang <span style="color:#dc2626;">*</span></label>
                <input class="input" type="date" name="tgl_selesai" value="{{ old('tgl_selesai', date('Y-m-d', strtotime('+3 months'))) }}" required style="width:100%;" />
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <a href="{{ route('admin.magang.index') }}" class="btnOutline" style="padding:10px 20px;">Batal</a>
            <button type="submit" class="btnPrimary" style="padding:10px 24px; background:var(--blue-gradient);">Simpan Data Magang</button>
        </div>
    </form>
</div>
@endsection
