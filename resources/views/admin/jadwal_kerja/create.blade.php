@extends('layouts.admin')

@section('title', 'Tambah Jadwal & Shift Kerja — Admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">TAMBAH MASTER DATA</div>
                <h1 class="laporanHeaderTitle">Tambah Jadwal &amp; Shift Baru</h1>
                <div class="laporanHeaderSub">Tentukan jam masuk, jam pulang, toleransi keterlambatan, dan integrasi KBM</div>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.jadwal-kerja.index') }}" class="btnOutline">
                    <ion-icon name="arrow-back-outline"></ion-icon> Kembali
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="error-list-container mb-4">
            <div class="error-title">Periksa input berikut:</div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Form Card ── --}}
    <div class="form-card-container form-card-wide">
        <form method="POST" action="{{ route('admin.jadwal-kerja.store') }}">
            @csrf

            <div class="form-grid-responsive">
                {{-- Nama Shift --}}
                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Nama Shift <span class="text-danger">*</span></label>
                    <input type="text" name="nama_shift" id="inputNamaShift" value="{{ old('nama_shift') }}" placeholder="Contoh: Shift Pagi Operasional" class="profileInput @error('nama_shift') border-danger @enderror" required>
                    <span class="field-help-text">Nama shift yang akan tampil di pilihan jadwal.</span>
                </div>

                {{-- Kode Shift --}}
                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Kode Shift Unik <span class="text-danger">*</span></label>
                    <input type="text" name="kode_shift" id="inputKodeShift" value="{{ old('kode_shift') }}" placeholder="Contoh: shift_pagi_reguler" class="profileInput @error('kode_shift') border-danger @enderror" required>
                    <span class="field-help-text">Hanya huruf, angka, dash (-), dan underscore (_).</span>
                </div>

                {{-- Jenis Shift --}}
                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Jenis Peruntukan Shift <span class="text-danger">*</span></label>
                    <select name="jenis_shift" id="selectJenisShift" onchange="handleJenisShiftChange()" class="filterSelect @error('jenis_shift') border-danger @enderror" required>
                        <option value="umum" {{ old('jenis_shift', 'umum') === 'umum' ? 'selected' : '' }}>Umum (Karyawan, Admin, &amp; Magang)</option>
                        <option value="kbm" {{ old('jenis_shift') === 'kbm' ? 'selected' : '' }}>KBM Tutor (Pembelajaran &amp; Mengajar)</option>
                    </select>
                </div>

                {{-- Relasi Kategori Tutorial SK --}}
                <div class="form-field-wrapper {{ old('jenis_shift', 'umum') === 'kbm' ? '' : 'd-none' }}" id="boxKategoriTutorial">
                    <label class="filterFieldLabel">Integrasi Kategori SK Tutorial</label>
                    <select name="kategori_tutorial_id" id="selectKategoriTutorial" onchange="handleKategoriTutorialChange()" class="filterSelect">
                        <option value="">-- Tanpa Kategori Khusus (Manual) --</option>
                        @foreach($kategoriTutorials as $kat)
                            <option value="{{ $kat->id }}" data-durasi="{{ $kat->durasi_jam }}" {{ old('kategori_tutorial_id') == $kat->id ? 'selected' : '' }}>
                                {{ $kat->nama_kategori }} ({{ $kat->durasi_jam }} Jam &bull; {{ $kat->formatted_nominal_honor }})
                            </option>
                        @endforeach
                    </select>
                    <span class="field-help-text">Memilih kategori akan otomatis mengunci durasi jam acuan SK.</span>
                </div>
            </div>

            {{-- Jam Kerja & Durasi Card --}}
            <div class="p-3 bg-card-alt rounded-xl border-base my-4">
                <div class="text-xs font-bold uppercase tracking-wider text-muted mb-3">
                    <ion-icon name="time-outline"></ion-icon> Waktu Operasional &amp; Durasi
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Jam Masuk (WIB) <span class="text-danger">*</span></label>
                        <input type="time" name="jam_masuk" id="inputJamMasuk" value="{{ old('jam_masuk', '08:00') }}" onchange="calculateDuration()" class="profileInput @error('jam_masuk') border-danger @enderror" required>
                    </div>

                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Jam Pulang (WIB) <span class="text-danger">*</span></label>
                        <input type="time" name="jam_pulang" id="inputJamPulang" value="{{ old('jam_pulang', '16:00') }}" onchange="calculateDuration()" class="profileInput @error('jam_pulang') border-danger @enderror" required>
                    </div>

                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Durasi Kerja (Jam)</label>
                        <input type="number" step="0.25" min="0.5" max="24" name="durasi_jam" id="inputDurasiJam" value="{{ old('durasi_jam', '8.00') }}" class="profileInput">
                        <span class="field-help-text" id="durasiHint">Dihitung otomatis dari selisih waktu.</span>
                    </div>
                </div>
            </div>

            <div class="form-grid-responsive">
                {{-- Batas Awal --}}
                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Batas Awal Absen (Menit) <span class="text-danger">*</span></label>
                    <input type="number" min="0" max="180" name="earliest_minutes" value="{{ old('earliest_minutes', 30) }}" class="profileInput" required>
                    <span class="field-help-text">Misal 30 menit: Jam 08:00 dapat mulai diabsen dari pukul 07:30 WIB.</span>
                </div>

                {{-- Toleransi Keterlambatan --}}
                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Toleransi Keterlambatan (Menit) <span class="text-danger">*</span></label>
                    <input type="number" min="0" max="180" name="tolerance_minutes" value="{{ old('tolerance_minutes', 30) }}" class="profileInput" required>
                    <span class="field-help-text">Misal 30 menit: Absen s.d. jam 08:30 tetap dihitung Tepat Waktu.</span>
                </div>

                {{-- Urutan --}}
                <div class="form-field-wrapper">
                    <label class="filterFieldLabel">Urutan Tampilan</label>
                    <input type="number" min="0" name="urutan" value="{{ old('urutan', 0) }}" class="profileInput" placeholder="0">
                    <span class="field-help-text">Urutan prioritas saat ditampilkan di daftar.</span>
                </div>

                {{-- Status Aktif Switch --}}
                <div class="form-field-wrapper flex-col justify-end">
                    <label class="checkbox-toggle-card">
                        <input type="checkbox" name="is_aktif" value="1" {{ old('is_aktif', 1) ? 'checked' : '' }}>
                        <div>
                            <div class="font-bold text-dark text-sm">Status Shift Aktif</div>
                            <div class="field-help-text m-0">Shift ini dapat langsung dipilih saat presensi masuk.</div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Keterangan --}}
            <div class="form-field-wrapper mt-3">
                <label class="filterFieldLabel">Keterangan / Catatan Kebijakan (Opsional)</label>
                <textarea name="keterangan" rows="3" placeholder="Tambahkan catatan aturan kebijakan shift jika diperlukan..." class="profileInput">{{ old('keterangan') }}</textarea>
            </div>

            <div class="d-flex justify-end gap-2 border-t-base pt-4 mt-4">
                <a href="{{ route('admin.jadwal-kerja.index') }}" class="btnOutline">Batal</a>
                <button type="submit" class="profileBtnPrimary">
                    <ion-icon name="save-outline"></ion-icon> Simpan Jadwal Shift
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function handleJenisShiftChange() {
        const jenis = document.getElementById('selectJenisShift').value;
        const boxKat = document.getElementById('boxKategoriTutorial');
        if (jenis === 'kbm') {
            boxKat.classList.remove('d-none');
        } else {
            boxKat.classList.add('d-none');
            document.getElementById('selectKategoriTutorial').value = '';
        }
    }

    function handleKategoriTutorialChange() {
        const sel = document.getElementById('selectKategoriTutorial');
        const durasiInput = document.getElementById('inputDurasiJam');
        const opt = sel.selectedOptions[0];
        if (opt && opt.dataset.durasi) {
            durasiInput.value = parseFloat(opt.dataset.durasi).toFixed(2);
            document.getElementById('durasiHint').textContent = 'Durasi dikunci sesuai standar SK Kategori Tutorial.';
        } else {
            calculateDuration();
        }
    }

    function calculateDuration() {
        const masuk = document.getElementById('inputJamMasuk').value;
        const pulang = document.getElementById('inputJamPulang').value;
        const durasiInput = document.getElementById('inputDurasiJam');
        const selKat = document.getElementById('selectKategoriTutorial');

        if (selKat && selKat.value) return;

        if (masuk && pulang) {
            const [h1, m1] = masuk.split(':').map(Number);
            const [h2, m2] = pulang.split(':').map(Number);
            let diff = (h2 * 60 + m2) - (h1 * 60 + m1);
            if (diff < 0) diff += 24 * 60;
            const hours = (diff / 60).toFixed(2);
            durasiInput.value = hours;
            document.getElementById('durasiHint').textContent = 'Dihitung otomatis: ' + hours + ' jam kerja.';
        }
    }
</script>

@endsection
