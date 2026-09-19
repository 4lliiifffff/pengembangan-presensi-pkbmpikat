@extends('layouts.admin')

@section('title', 'Tambah Peserta Magang / PKL')

@section('content')
<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">FORMULIR PENDAFTARAN</div>
                <h1 class="laporanHeaderTitle">Tambah Peserta Magang &amp; PKL</h1>
                <div class="laporanHeaderSub">Daftarkan mahasiswa magang atau siswa PKL beserta instansi dan hak akses</div>
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
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-card-container">
        <form method="POST" action="{{ route('admin.magang.store') }}" enctype="multipart/form-data">
            @csrf
            
            <h3 class="card-section-title">
                1. Informasi Akun &amp; Identitas Login
            </h3>

            <div class="form-grid-responsive mb-4">
                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Nama Lengkap <span class="text-danger">*</span></label>
                    <input class="profileInput" type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required placeholder="Contoh: Alif" />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">NIK / No. Identitas Sistem <span class="text-danger">*</span></label>
                    <input class="profileInput" type="text" name="nik" value="{{ old('nik') }}" required placeholder="Contoh: MG202601 atau NIK KTP" />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Email Login <span class="text-danger">*</span></label>
                    <input class="profileInput" type="email" name="email" value="{{ old('email') }}" required placeholder="Contoh: ahmad@kampus.ac.id" />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Password Login <span class="text-danger">*</span></label>
                    <input class="profileInput" type="password" name="password" required placeholder="Minimal 6 karakter (cth: password123)" />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">No. WhatsApp / HP</label>
                    <input class="profileInput" type="text" name="no_hp" value="{{ old('no_hp') }}" placeholder="Contoh: 081234567890" />
                </div>
            </div>

            <h3 class="card-section-title mt-4">
                2. Informasi Instansi &amp; Periode Penugasan
            </h3>

            <div class="form-grid-responsive mb-4">
                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Asal Universitas / Sekolah <span class="text-danger">*</span></label>
                    <input class="profileInput" type="text" name="asal_instansi" value="{{ old('asal_instansi') }}" required placeholder="Contoh: Universitas Negeri Yogyakarta" />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">NIM / NISN</label>
                    <input class="profileInput" type="text" name="nim_nisn" value="{{ old('nim_nisn') }}" placeholder="Contoh: 210101002" />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Program Studi / Jurusan</label>
                    <input class="profileInput" type="text" name="jurusan" value="{{ old('jurusan') }}" placeholder="Contoh: Pendidikan Luar Sekolah / Teknik Informatika" />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Dosen / Guru Pembimbing</label>
                    <input class="profileInput" type="text" name="pembimbing_lapangan" value="{{ old('pembimbing_lapangan') }}" placeholder="Contoh: Muhamad Alif Nur Rohman, M.Kom" />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Tanggal Mulai Magang <span class="text-danger">*</span></label>
                    <input class="profileInput" type="date" name="tgl_mulai" value="{{ old('tgl_mulai', date('Y-m-d')) }}" required />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Tanggal Selesai Magang <span class="text-danger">*</span></label>
                    <input class="profileInput" type="date" name="tgl_selesai" value="{{ old('tgl_selesai', date('Y-m-d', strtotime('+3 months'))) }}" required />
                </div>
            </div>

            <div class="form-field-wrapper mt-4">
                <label class="filterFieldLabel">Foto Profil (Opsional)</label>
                <div class="fileUploadBox">
                    <input type="file" name="foto" id="fotoMagangInput" accept="image/png,image/jpeg,image/jpg" onchange="handleFileSelected(this, 'magangFotoFeedback')">
                    <div class="fileUploadText">Pilih atau seret foto profil</div>
                    <div class="fileUploadSubtext">
                        <span class="fileUploadInfoPill">Format: JPG, PNG</span>
                        <span class="fileUploadInfoPill">Maks: 2 MB</span>
                    </div>
                </div>
                <div id="magangFotoFeedback" class="fileUploadFeedback"></div>
            </div>

            <div class="form-action-footer">
                <a href="{{ route('admin.magang.index') }}" class="btnOutline">Batal</a>
                <button type="submit" class="profileBtnPrimary">
                    <ion-icon name="save-outline"></ion-icon> Simpan Data Magang
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

