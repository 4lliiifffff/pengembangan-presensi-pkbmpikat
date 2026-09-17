@extends('layouts.admin')

@section('title', 'Tambah Kategori SK — Admin')

@section('content')
<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">KEBIJAKAN HONORARIUM &amp; SK</div>
                <h1 class="laporanHeaderTitle">Tambah Kategori &amp; Tarif SK</h1>
                <div class="laporanHeaderSub">Daftarkan kategori pembelajaran, jenis layanan, durasi acuan, dan tarif honor</div>
            </div>
            <div class="laporanHeaderActions">
                <a class="btnOutline" href="{{ route('admin.kategori-tutorial.index') }}">
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
        <form method="POST" action="{{ route('admin.kategori-tutorial.store') }}">
            @csrf

            <div class="form-field-wrapper">
                <label class="form-field-label">Nama Kategori Pembelajaran <span class="text-danger">*</span></label>
                <input class="profileInput" type="text" name="nama_kategori" value="{{ old('nama_kategori') }}" placeholder="Contoh: Tutorial Komunitas 2 Jam" required />
            </div>

            <div class="form-grid-responsive mb-4">
                <div>
                    <label class="form-field-label">Jenis Layanan <span class="text-danger">*</span></label>
                    <select name="jenis_layanan" class="profileInput" required>
                        <option value="komunitas" {{ old('jenis_layanan') === 'komunitas' ? 'selected' : '' }}>Tutorial Komunitas</option>
                        <option value="dl" {{ old('jenis_layanan') === 'dl' ? 'selected' : '' }}>Distance Learning (DL)</option>
                        <option value="lainnya" {{ old('jenis_layanan') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="form-field-label">Durasi Sesi (Jam) <span class="text-danger">*</span></label>
                    <input type="number" step="0.25" min="0.5" max="12" name="durasi_jam" value="{{ old('durasi_jam', '2.0') }}" placeholder="2.0" class="profileInput" required />
                </div>
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Nominal Honor per Pertemuan (Rp) <span class="text-danger">*</span></label>
                <input type="number" step="1000" min="0" name="nominal_honor" value="{{ old('nominal_honor', '75000') }}" placeholder="75000" class="profileInput" required />
            </div>

            <div class="form-grid-responsive mb-4">
                <div class="checkbox-toggle-card">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_abk" value="1" {{ old('is_abk') ? 'checked' : '' }} class="w-auto" />
                        Khusus Siswa ABK
                    </label>
                </div>
                <div class="checkbox-toggle-card">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_gabungan" value="1" {{ old('is_gabungan') ? 'checked' : '' }} class="w-auto" />
                        Rombel Gabungan
                    </label>
                </div>
            </div>

            <div class="form-grid-responsive mb-4">
                <div>
                    <label class="form-field-label">Urutan Tampilan</label>
                    <input type="number" name="urutan" min="0" value="{{ old('urutan', $nextUrutan ?? 1) }}" class="profileInput" />
                </div>
                <div class="checkbox-toggle-card self-end">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_aktif" value="1" {{ old('is_aktif', true) ? 'checked' : '' }} class="w-auto" />
                        Status Aktif
                    </label>
                </div>
            </div>

            <button type="submit" class="profileBtnPrimary mt-3">
                <ion-icon name="save-outline"></ion-icon>
                Simpan Kategori SK
            </button>
        </form>
    </div>
</div>
@endsection
