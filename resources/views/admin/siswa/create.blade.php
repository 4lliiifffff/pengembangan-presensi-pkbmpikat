@extends('layouts.admin')

@section('content')
<div class="laporanPageWrapper">
    <div class="laporanHeaderCard">
        <div class="laporanHeaderMain">
            <div class="laporanTitleBlock">
                <span class="laporanSubtitle">MODUL PESERTA DIDIK</span>
                <h1 class="laporanMainTitle">Tambah Siswa Baru</h1>
                <p class="laporanDesc">Daftarkan peserta didik baru ke dalam rombel dan paket belajar</p>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.siswa.index') }}" class="btnOutline">
                    Kembali ke Daftar
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
        <form method="POST" action="{{ route('admin.siswa.store') }}">
            @csrf

            <div class="form-grid-responsive">
                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">NOMOR ABSEN / NIS <span class="text-danger">*</span></label>
                    <input class="profileInput" type="text" name="no_absen" value="{{ old('no_absen') }}" placeholder="Contoh: 101" required />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">NAMA LENGKAP SISWA <span class="text-danger">*</span></label>
                    <input class="profileInput" type="text" name="nama_siswa" value="{{ old('nama_siswa') }}" placeholder="Contoh: Ahmad Dahlan" required />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">STATUS SIKLUS MURID <span class="text-danger">*</span></label>
                    <select class="filterSelect" name="status_siswa" required>
                        <option value="aktif" {{ old('status_siswa', 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif Belajar</option>
                        <option value="alumni" {{ old('status_siswa') === 'alumni' ? 'selected' : '' }}>Lulus / Alumni</option>
                        <option value="cuti" {{ old('status_siswa') === 'cuti' ? 'selected' : '' }}>Cuti Belajar</option>
                        <option value="nonaktif" {{ old('status_siswa') === 'nonaktif' ? 'selected' : '' }}>Nonaktif / Keluar</option>
                    </select>
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">NO HP / WHATSAPP SISWA <span class="text-danger">*</span></label>
                    <input class="profileInput" type="text" name="no_hp" value="{{ old('no_hp') }}" placeholder="Contoh: 081234567890" required />
                </div>

                <div class="form-field-wrapper form-grid-full">
                    <label class="filterFieldLabel">NAMA ORANG TUA / WALI <span class="text-danger">*</span></label>
                    <input class="profileInput" type="text" name="nama_wali" value="{{ old('nama_wali') }}" placeholder="Contoh: Bapak / Ibu Santoso" required />
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">KELAS / ROMBONGAN BELAJAR <span class="text-danger">*</span></label>
                    @if ($kelas->isEmpty())
                        <div class="profileInput text-sm text-muted bg-card-alt">
                            Belum ada data kelas.
                            <a href="{{ route('admin.kelas.create') }}" class="link-primary font-bold">Tambah kelas</a>.
                        </div>
                    @endif
                    <select class="filterSelect" name="kelas_id" required onchange="if(this.value === 'tambah_kelas') { window.location.href = '{{ route('admin.kelas.create') }}'; }">
                        <option value="">-- Pilih Kelas / Rombel --</option>
                        <option value="tambah_kelas" class="link-primary">+ Tambah Kelas Baru</option>
                        @php
                            $groupedKelas = $kelas->groupBy(fn($k) => $k->jenjang_paket_label);
                        @endphp
                        @foreach ($groupedKelas as $jenjangLabel => $kelasList)
                            <optgroup label="{{ $jenjangLabel }}">
                                @foreach ($kelasList as $k)
                                    <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">TUTOR PEMBIMBING (OPSIONAL)</label>
                    <select class="filterSelect" name="tutor_id">
                        <option value="">-- Tidak Ada / Belum Ditentukan --</option>
                        @if (isset($tutors))
                            @foreach ($tutors as $tutor)
                                <option value="{{ $tutor->id }}" {{ old('tutor_id') == $tutor->id ? 'selected' : '' }}>
                                    {{ $tutor->nama_lengkap }}@if(!empty($tutor->jabatan) && stripos($tutor->jabatan, 'kepala') !== false) (Kepala Sekolah)@endif
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label class="checkbox-toggle-card">
                    <input type="checkbox" name="is_abk" value="1" {{ old('is_abk') ? 'checked' : '' }}>
                    <span >Siswa Berkebutuhan Khusus (ABK)</span>
                </label>
                <div class="field-help-text">Centang jika siswa ini merupakan Anak Berkebutuhan Khusus (tarif honor tutor otomatis disesuaikan SK ABK).</div>
            </div>

            <div class="info-callout-box">
                <div class="info-callout-title">
                    <span >Skema Tarif Honor Tutor</span>
                    <a href="{{ route('admin.kategori-tutorial.index') }}" target="_blank" class="info-callout-link">
                        Kelola Master SK &rarr;
                    </a>
                </div>
                <div class="info-callout-desc">
                    Tarif honor sesi mengajar tutor dihitung secara otomatis dari Master Tarif SK berdasarkan status ABK / Reguler dan durasi tutorial.
                </div>
            </div>

            <div class="form-action-footer">
                <a href="{{ route('admin.siswa.index') }}" class="btnOutline">
                    Batal
                </a>
                <button type="submit" class="profileBtnPrimary">
                    Simpan Data Siswa
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

