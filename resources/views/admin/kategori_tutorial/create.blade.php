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

            <div class="form-field-wrapper mb-4">
                <label class="filterFieldLabel">NAMA KATEGORI PEMBELAJARAN <span class="text-danger">*</span></label>
                <input class="profileInput" type="text" name="nama_kategori" value="{{ old('nama_kategori') }}" placeholder="Contoh: Tutorial Komunitas 2 Jam" required />
                <span class="field-help-text">Nama resmi kategori sesi belajar yang akan muncul pada jadwal dan laporan presensi.</span>
            </div>

            <div class="form-grid-responsive mb-4">
                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">JENIS LAYANAN <span class="text-danger">*</span></label>
                    <input type="text" name="jenis_layanan" id="inputJenisLayanan" list="listJenisLayanan"
                        value="{{ old('jenis_layanan', 'komunitas') }}" placeholder="Ketik atau pilih saran di bawah..."
                        class="profileInput" required autocomplete="off" />
                    <datalist id="listJenisLayanan">
                        <option value="komunitas">Tutorial Komunitas</option>
                        <option value="dl">Distance Learning (DL)</option>
                        <option value="vokasi">Kursus / Vokasi</option>
                        <option value="homeschooling">Homeschooling</option>
                        <option value="intensif">Bimbingan Intensif</option>
                        @foreach($existingJenisLayanan ?? [] as $layanan)
                            @if(!in_array($layanan, ['komunitas', 'dl', 'vokasi', 'homeschooling', 'intensif']))
                                <option value="{{ $layanan }}">{{ ucwords(str_replace(['_', '-'], ' ', $layanan)) }}</option>
                            @endif
                        @endforeach
                    </datalist>
                    <div class="d-flex gap-1 flex-wrap mt-2">
                        <span class="text-xs text-muted font-semibold mr-1">Saran Cepat:</span>
                        <button type="button" onclick="setJenisLayanan('komunitas')" class="app-badge badge-layanan-komunitas cursor-pointer border-none py-1 px-2">Komunitas</button>
                        <button type="button" onclick="setJenisLayanan('dl')" class="app-badge badge-layanan-dl cursor-pointer border-none py-1 px-2">DL (Daring)</button>
                        <button type="button" onclick="setJenisLayanan('vokasi')" class="app-badge badge-layanan-custom cursor-pointer border-none py-1 px-2">Vokasi</button>
                        <button type="button" onclick="setJenisLayanan('homeschooling')" class="app-badge badge-layanan-custom cursor-pointer border-none py-1 px-2">Homeschooling</button>
                    </div>
                </div>

                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">DURASI SESI (JAM) <span class="text-danger">*</span></label>
                    <input type="number" step="0.25" min="0.5" max="12" name="durasi_jam" value="{{ old('durasi_jam', '2.0') }}" placeholder="2.0" class="profileInput" required />
                    <span class="field-help-text">Durasi acuan standar per sesi pertemuan (contoh: 1.5, 2.0, 3.0 jam).</span>
                </div>
            </div>

            <div class="form-field-wrapper mb-4">
                <label class="filterFieldLabel">NOMINAL HONOR PER PERTEMUAN (RP) <span class="text-danger">*</span></label>
                <input type="number" step="1000" min="0" name="nominal_honor" value="{{ old('nominal_honor', '75000') }}" placeholder="Contoh: 75000" class="profileInput font-bold text-dark" required />
                <span class="field-help-text">Besaran tarif honorarium per sesi mengajar yang diterima tutor berdasarkan SK resmi.</span>
            </div>

            <div class="form-grid-responsive mb-4">
                <div class="checkbox-toggle-card">
                    <input type="checkbox" id="check_abk" name="is_abk" value="1" {{ old('is_abk') ? 'checked' : '' }} class="w-auto" />
                    <label for="check_abk" class="cursor-pointer m-0">
                        Khusus Siswa Berkebutuhan Khusus (ABK)
                    </label>
                </div>

                <div class="checkbox-toggle-card">
                    <input type="checkbox" id="check_gabungan" name="is_gabungan" value="1" {{ old('is_gabungan') ? 'checked' : '' }} class="w-auto" />
                    <label for="check_gabungan" class="cursor-pointer m-0">
                        Skema Sesi Rombel Gabungan
                    </label>
                </div>
            </div>

            <div class="form-grid-responsive mb-4">
                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">URUTAN TAMPILAN</label>
                    <input type="number" name="urutan" min="0" value="{{ old('urutan', $nextUrutan ?? 1) }}" class="profileInput" />
                    <span class="field-help-text">Nomor urut kategori di dalam daftar dan dropdown pilihan jadwal.</span>
                </div>

                <div class="checkbox-toggle-card self-end">
                    <input type="checkbox" id="check_aktif" name="is_aktif" value="1" {{ old('is_aktif', true) ? 'checked' : '' }} class="w-auto" />
                    <label for="check_aktif" class="cursor-pointer m-0">
                        Status Kategori Aktif
                    </label>
                </div>
            </div>

            <div class="info-callout-box mb-4">
                <div class="info-callout-title">
                    <div class="d-flex items-center gap-1">
                        <ion-icon name="shield-checkmark-outline" class="text-primary"></ion-icon>
                        <span>Proteksi Finansial &amp; Otomatisasi SK</span>
                    </div>
                </div>
                <div class="info-callout-desc">
                    Kategori SK ini akan langsung tersedia di modal <em>Buat Jadwal Belajar</em> tutor. Setiap sesi mengajar yang menggunakan kategori ini akan mengunci nilai honornya saat presensi tercatat untuk menjaga keabsahan audit payroll.
                </div>
            </div>

            <div class="form-action-footer">
                <a href="{{ route('admin.kategori-tutorial.index') }}" class="btnOutline">
                    Batal
                </a>
                <button type="submit" class="profileBtnPrimary">
                    <ion-icon name="save-outline"></ion-icon> Simpan Kategori SK
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function setJenisLayanan(val) {
        var input = document.getElementById('inputJenisLayanan');
        if (input) {
            input.value = val;
            input.focus();
        }
    }
</script>
@endsection
