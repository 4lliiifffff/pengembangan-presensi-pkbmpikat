@extends('layouts.admin')

@section('title', 'Edit Peserta Magang / PKL')

@section('content')
    @php
        $detail = $magang->magang;
        $displayName = $magang->nama_lengkap ?? $magang->name;
        $avatarUrl = $magang->foto_url;
    @endphp

    <div class="laporanPageWrapper">
        {{-- ── Header Card ── --}}
        <div class="laporanHeader">
            <div class="laporanHeaderCard">
                <div class="laporanHeaderInfo">
                    <div class="laporanHeaderLabel">PERBARUI DATA</div>
                    <h1 class="laporanHeaderTitle">Edit Peserta: {{ $displayName }}</h1>
                    <div class="laporanHeaderSub">Perbarui profil, hak akses akun, dan data penugasan magang/PKL</div>
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
            <form method="POST" action="{{ route('admin.magang.update', $magang->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <h3 class="card-section-title">
                    1. Informasi Akun &amp; Identitas Login
                </h3>

                <div class="form-grid-responsive mb-4">
                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Nama Lengkap <span class="text-danger">*</span></label>
                        <input class="profileInput" type="text" name="nama_lengkap"
                            value="{{ old('nama_lengkap', $displayName) }}" required
                            placeholder="Contoh: Ahmad Fauzi Pratama" />
                    </div>

                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">NIK / No. Identitas Sistem <span
                                class="text-danger">*</span></label>
                        <input class="profileInput" type="text" name="nik" value="{{ old('nik', $magang->nik) }}" required
                            placeholder="Contoh: MG202601" />
                    </div>

                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Email Login <span class="text-danger">*</span></label>
                        <input class="profileInput" type="email" name="email" value="{{ old('email', $magang->email) }}"
                            required placeholder="Contoh: ahmad@kampus.ac.id" />
                    </div>

                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Password Baru (Kosongkan jika tidak diubah)</label>
                        <input class="profileInput" type="password" name="password" placeholder="Minimal 6 karakter" />
                    </div>

                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">No. WhatsApp / HP</label>
                        <input class="profileInput" type="text" name="no_hp" value="{{ old('no_hp', $magang->no_hp) }}"
                            placeholder="Contoh: 081234567890" />
                    </div>

                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Status Akun &amp; Penugasan <span
                                class="text-danger">*</span></label>
                        <select name="is_active" class="filterSelect">
                            <option value="1" {{ old('is_active', $magang->is_active) == 1 ? 'selected' : '' }}>Aktif Berjalan
                            </option>
                            <option value="0" {{ old('is_active', $magang->is_active) == 0 ? 'selected' : '' }}>Selesai /
                                Nonaktif</option>
                        </select>
                    </div>
                </div>

                <h3 class="card-section-title mt-4">
                    2. Informasi Instansi &amp; Periode Penugasan
                </h3>

                <div class="form-grid-responsive mb-4">
                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Asal Universitas / Sekolah <span
                                class="text-danger">*</span></label>
                        <input class="profileInput" type="text" name="asal_instansi"
                            value="{{ old('asal_instansi', $detail?->asal_instansi) }}" required
                            placeholder="Contoh: Universitas Negeri Yogyakarta" />
                    </div>

                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">NIM / NISN</label>
                        <input class="profileInput" type="text" name="nim_nisn"
                            value="{{ old('nim_nisn', $detail?->nim_nisn) }}" placeholder="Contoh: 210101002" />
                    </div>

                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Program Studi / Jurusan</label>
                        <input class="profileInput" type="text" name="jurusan"
                            value="{{ old('jurusan', $detail?->jurusan) }}" placeholder="Contoh: Pendidikan Luar Sekolah" />
                    </div>

                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Dosen / Guru Pembimbing</label>
                        <input class="profileInput" type="text" name="pembimbing_lapangan"
                            value="{{ old('pembimbing_lapangan', $detail?->pembimbing_lapangan) }}"
                            placeholder="Contoh: Muhamad Alif Nur Rohman, M.Kom" />
                    </div>

                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Tanggal Mulai Magang <span class="text-danger">*</span></label>
                        <input class="profileInput" type="date" name="tgl_mulai"
                            value="{{ old('tgl_mulai', $detail?->tgl_mulai) }}" required />
                    </div>

                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Tanggal Selesai Magang <span class="text-danger">*</span></label>
                        <input class="profileInput" type="date" name="tgl_selesai"
                            value="{{ old('tgl_selesai', $detail?->tgl_selesai) }}" required />
                    </div>
                </div>

                <div class="form-field-wrapper mt-4">
                    <label class="filterFieldLabel">Foto Profil</label>
                    @if ($avatarUrl)
                        <div class="d-flex items-center gap-3 mb-3">
                            <img src="{{ $avatarUrl }}" alt="Foto {{ $displayName }}" class="avatar-thumb-52" />
                            <span class="text-sm text-muted font-semibold">Foto saat ini tersimpan</span>
                        </div>
                    @endif
                    <div class="fileUploadBox">
                        <input type="file" name="foto" id="fotoMagangEditInput" accept="image/png,image/jpeg,image/jpg"
                            onchange="handleFileSelected(this, 'magangEditFotoFeedback')">
                        <div class="fileUploadText">Pilih foto baru untuk mengganti</div>
                        <div class="fileUploadSubtext">
                            <span class="fileUploadInfoPill">Format: JPG, PNG</span>
                            <span class="fileUploadInfoPill">Maks: 2 MB</span>
                        </div>
                    </div>
                    <div id="magangEditFotoFeedback" class="fileUploadFeedback"></div>
                </div>

                <div class="form-action-footer">
                    <a href="{{ route('admin.magang.index') }}" class="btnOutline">Batal</a>
                    <button type="submit" class="profileBtnPrimary">
                        <ion-icon name="save-outline"></ion-icon> Simpan Perubahan
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