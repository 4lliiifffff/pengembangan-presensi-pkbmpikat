@extends('layouts.admin')

@section('title', 'Tambah Peserta Magang / PKL')

@section('content')
<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">FORMULIR PESERTA MAGANG</div>
                <h1 class="laporanHeaderTitle">Tambah Peserta Magang / PKL</h1>
                <div class="laporanHeaderSub">Daftarkan mahasiswa magang atau siswa PKL beserta informasi instansi</div>
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
    <form method="POST" action="{{ route('admin.magang.store') }}" enctype="multipart/form-data">
        @csrf
        
        <h3 class="card-section-title">
            1. Informasi Akun &amp; Pribadi
        </h3>

        <div class="form-grid-responsive mb-4">
            <div class="form-field-wrapper">
                <label class="form-field-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input class="profileInput" type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required placeholder="cth: Ahmad Fauzi" />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">NIK / No. Identitas Sistem <span class="text-danger">*</span></label>
                <input class="profileInput" type="text" name="nik" value="{{ old('nik') }}" required placeholder="cth: MG202601" />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Email Login <span class="text-danger">*</span></label>
                <input class="profileInput" type="email" name="email" value="{{ old('email') }}" required placeholder="cth: ahmad@kampus.ac.id" />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Password Login <span class="text-danger">*</span></label>
                <input class="profileInput" type="password" name="password" required placeholder="Minimal 6 karakter" />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">No. WhatsApp / HP</label>
                <input class="profileInput" type="text" name="no_hp" value="{{ old('no_hp') }}" placeholder="cth: 08123456789" />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Foto Profil (Opsional)</label>
                <div class="fileUploadBox p-3">
                    <input type="file" name="foto" id="fotoMagangInput" accept="image/png,image/jpeg,image/jpg" onchange="handleFileSelected(this, 'magangFotoFeedback')">
                    <div class="fileUploadIcon avatar-icon-36">
                        <ion-icon name="image-outline"></ion-icon>
                    </div>
                    <div class="fileUploadText text-sm">Pilih foto profil</div>
                    <div class="fileUploadSubtext">
                        <span class="fileUploadInfoPill">Format: JPG, PNG</span>
                        <span class="fileUploadInfoPill">Maks: 2 MB</span>
                    </div>
                </div>
                <div id="magangFotoFeedback" class="fileUploadFeedback"></div>
            </div>
        </div>

        <h3 class="text-lg font-extrabold text-dark mb-4 py-2 mt-4 border-b-base">
            2. Informasi Instansi &amp; Periode Magang
        </h3>

        <div class="form-grid-responsive mb-4">
            <div class="form-field-wrapper">
                <label class="form-field-label">Asal Universitas / Sekolah <span class="text-danger">*</span></label>
                <input class="profileInput" type="text" name="asal_instansi" value="{{ old('asal_instansi') }}" required placeholder="cth: Universitas Negeri Yogyakarta" />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">NIM / NISN</label>
                <input class="profileInput" type="text" name="nim_nisn" value="{{ old('nim_nisn') }}" placeholder="cth: 210101002" />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Program Studi / Jurusan</label>
                <input class="profileInput" type="text" name="jurusan" value="{{ old('jurusan') }}" placeholder="cth: Pendidikan Luar Sekolah / Teknik Informatika" />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Dosen / Guru Pembimbing</label>
                <input class="profileInput" type="text" name="pembimbing_lapangan" value="{{ old('pembimbing_lapangan') }}" placeholder="cth: Dr. Hendra, M.Pd" />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Tanggal Mulai Magang <span class="text-danger">*</span></label>
                <input class="profileInput" type="date" name="tgl_mulai" value="{{ old('tgl_mulai', date('Y-m-d')) }}" required />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Tanggal Selesai Magang <span class="text-danger">*</span></label>
                <input class="profileInput" type="date" name="tgl_selesai" value="{{ old('tgl_selesai', date('Y-m-d', strtotime('+3 months'))) }}" required />
            </div>
        </div>

        <div class="form-action-footer">
            <a href="{{ route('admin.magang.index') }}" class="btnOutline">Batal</a>
            <button type="submit" class="profileBtnPrimary w-auto px-4">Simpan Data Magang</button>
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
