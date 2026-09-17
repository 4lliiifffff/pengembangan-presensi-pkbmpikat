@extends('layouts.admin')

@section('title', 'Edit Kategori SK — Admin')

@section('content')
<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">KEBIJAKAN HONORARIUM &amp; SK</div>
                <h1 class="laporanHeaderTitle">Edit Kategori SK: {{ $kategoriTutorial->nama_kategori }}</h1>
                <div class="laporanHeaderSub">Perbarui parameter durasi acuan, status ABK / rombel gabungan, dan besaran tarif honor</div>
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
        <form method="POST" action="{{ route('admin.kategori-tutorial.update', $kategoriTutorial) }}">
            @csrf
            @method('PUT')

            {{-- Info Penggunaan Sesi --}}
            <div class="info-callout-box mt-0 mb-4">
                <div class="info-callout-title">
                    <div class="d-flex items-center gap-1">
                        <ion-icon name="time-outline" class="icon-sm"></ion-icon>
                        Total Sesi Presensi Menggunakan Kategori Ini: {{ $kategoriTutorial->presensis_count ?? 0 }} Sesi
                    </div>
                </div>
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Nama Kategori Pembelajaran <span class="text-danger">*</span></label>
                <input class="profileInput" type="text" name="nama_kategori" value="{{ old('nama_kategori', $kategoriTutorial->nama_kategori) }}" placeholder="Contoh: Tutorial Komunitas 2 Jam" required />
            </div>

            <div class="form-grid-responsive mb-4">
                <div>
                    <label class="form-field-label">Jenis Layanan <span class="text-danger">*</span></label>
                    <select name="jenis_layanan" class="profileInput" required>
                        <option value="komunitas" {{ old('jenis_layanan', $kategoriTutorial->jenis_layanan) === 'komunitas' ? 'selected' : '' }}>Tutorial Komunitas</option>
                        <option value="dl" {{ old('jenis_layanan', $kategoriTutorial->jenis_layanan) === 'dl' ? 'selected' : '' }}>Distance Learning (DL)</option>
                        <option value="lainnya" {{ old('jenis_layanan', $kategoriTutorial->jenis_layanan) === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="form-field-label">Durasi Sesi (Jam) <span class="text-danger">*</span></label>
                    <input type="number" step="0.25" min="0.5" max="12" name="durasi_jam" value="{{ old('durasi_jam', $kategoriTutorial->durasi_jam) }}" placeholder="2.0" class="profileInput" required />
                </div>
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Nominal Honor per Pertemuan (Rp) <span class="text-danger">*</span></label>
                <input type="number" step="1000" min="0" name="nominal_honor" value="{{ old('nominal_honor', $kategoriTutorial->nominal_honor) }}" placeholder="75000" class="profileInput" required />
            </div>

            <div class="form-grid-responsive mb-4">
                <div class="checkbox-toggle-card">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_abk" value="1" {{ old('is_abk', $kategoriTutorial->is_abk) ? 'checked' : '' }} class="w-auto" />
                        Khusus Siswa ABK
                    </label>
                </div>
                <div class="checkbox-toggle-card">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_gabungan" value="1" {{ old('is_gabungan', $kategoriTutorial->is_gabungan) ? 'checked' : '' }} class="w-auto" />
                        Rombel Gabungan
                    </label>
                </div>
            </div>

            <div class="form-grid-responsive mb-4">
                <div>
                    <label class="form-field-label">Urutan Tampilan</label>
                    <input type="number" name="urutan" min="0" value="{{ old('urutan', $kategoriTutorial->urutan) }}" class="profileInput" />
                </div>
                <div class="checkbox-toggle-card self-end">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_aktif" value="1" {{ old('is_aktif', $kategoriTutorial->is_aktif) ? 'checked' : '' }} class="w-auto" />
                        Status Aktif
                    </label>
                </div>
            </div>

            <button type="submit" class="profileBtnPrimary mt-3">
                <ion-icon name="save-outline"></ion-icon>
                Perbarui Kategori SK
            </button>
        </form>
    </div>
</div>
@endsection
