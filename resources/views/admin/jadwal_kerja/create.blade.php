@extends('layouts.admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">TAMBAH MASTER DATA</div>
                <h1 class="laporanHeaderTitle">Tambah Jadwal &amp; Shift Baru</h1>
                <div class="laporanHeaderSub">Tentukan jam masuk, jam pulang, toleransi, dan integrasi KBM</div>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.jadwal-kerja.index') }}" class="profileBtnSecondary">
                    <ion-icon name="arrow-back-outline"></ion-icon> Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- ── Form Card ── --}}
    <div class="card p-4 rounded-xl border-base max-w-4xl mx-auto">
        <form method="POST" action="{{ route('admin.jadwal-kerja.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                {{-- Nama Shift --}}
                <div>
                    <label class="formLabel font-bold">Nama Shift <span class="text-danger">*</span></label>
                    <input type="text" name="nama_shift" id="inputNamaShift" value="{{ old('nama_shift') }}" placeholder="Contoh: Shift Pagi Operasional" class="filterInput w-full @error('nama_shift') border-danger @enderror" required>
                    @error('nama_shift')
                        <div class="text-xs text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Kode Shift --}}
                <div>
                    <label class="formLabel font-bold">Kode Shift Unik <span class="text-danger">*</span></label>
                    <input type="text" name="kode_shift" id="inputKodeShift" value="{{ old('kode_shift') }}" placeholder="Contoh: shift_pagi_reguler" class="filterInput w-full @error('kode_shift') border-danger @enderror" required>
                    <div class="text-xs text-muted mt-1">Hanya huruf, angka, dash (-), dan underscore (_).</div>
                    @error('kode_shift')
                        <div class="text-xs text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                {{-- Jenis Shift --}}
                <div>
                    <label class="formLabel font-bold">Jenis Peruntukan Shift <span class="text-danger">*</span></label>
                    <select name="jenis_shift" id="selectJenisShift" onchange="handleJenisShiftChange()" class="filterInput w-full @error('jenis_shift') border-danger @enderror" required>
                        <option value="umum" {{ old('jenis_shift', 'umum') === 'umum' ? 'selected' : '' }}>Umum (Karyawan, Admin, &amp; Magang)</option>
                        <option value="kbm" {{ old('jenis_shift') === 'kbm' ? 'selected' : '' }}>KBM Tutor (Pembelajaran &amp; Mengajar)</option>
                    </select>
                    @error('jenis_shift')
                        <div class="text-xs text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Relasi Kategori Tutorial SK --}}
                <div id="boxKategoriTutorial" class="{{ old('jenis_shift', 'umum') === 'kbm' ? '' : 'd-none' }}">
                    <label class="formLabel font-bold">Integrasi Kategori SK Tutorial</label>
                    <select name="kategori_tutorial_id" id="selectKategoriTutorial" onchange="handleKategoriTutorialChange()" class="filterInput w-full">
                        <option value="">-- Tanpa Kategori Khusus (Manual) --</option>
                        @foreach($kategoriTutorials as $kat)
                            <option value="{{ $kat->id }}" data-durasi="{{ $kat->durasi_jam }}" {{ old('kategori_tutorial_id') == $kat->id ? 'selected' : '' }}>
                                {{ $kat->nama_kategori }} ({{ $kat->durasi_jam }} Jam &bull; {{ $kat->formatted_nominal_honor }})
                            </option>
                        @endforeach
                    </select>
                    <div class="text-xs text-muted mt-1">Memilih kategori akan otomatis mengunci durasi jam acuan SK.</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-3 bg-card-alt rounded-xl border-base">
                {{-- Jam Masuk --}}
                <div>
                    <label class="formLabel font-bold">Jam Masuk (WIB) <span class="text-danger">*</span></label>
                    <input type="time" name="jam_masuk" id="inputJamMasuk" value="{{ old('jam_masuk', '08:00') }}" onchange="calculateDuration()" class="filterInput w-full @error('jam_masuk') border-danger @enderror" required>
                    @error('jam_masuk')
                        <div class="text-xs text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Jam Pulang --}}
                <div>
                    <label class="formLabel font-bold">Jam Pulang (WIB) <span class="text-danger">*</span></label>
                    <input type="time" name="jam_pulang" id="inputJamPulang" value="{{ old('jam_pulang', '16:00') }}" onchange="calculateDuration()" class="filterInput w-full @error('jam_pulang') border-danger @enderror" required>
                    @error('jam_pulang')
                        <div class="text-xs text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Durasi Jam --}}
                <div>
                    <label class="formLabel font-bold">Durasi Kerja (Jam)</label>
                    <input type="number" step="0.25" min="0.5" max="24" name="durasi_jam" id="inputDurasiJam" value="{{ old('durasi_jam', '8.00') }}" class="filterInput w-full">
                    <div class="text-xs text-muted mt-1" id="durasiHint">Otomatis dihitung dari selisih waktu.</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                {{-- Batas Awal --}}
                <div>
                    <label class="formLabel font-bold">Batas Boleh Absen Awal (Menit) <span class="text-danger">*</span></label>
                    <input type="number" min="0" max="180" name="earliest_minutes" value="{{ old('earliest_minutes', 30) }}" class="filterInput w-full" required>
                    <div class="text-xs text-muted mt-1">Misal 30 menit: Jam 08:00 dapat mulai diabsen dari pukul 07:30.</div>
                    @error('earliest_minutes')
                        <div class="text-xs text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Toleransi Keterlambatan --}}
                <div>
                    <label class="formLabel font-bold">Toleransi Keterlambatan (Menit) <span class="text-danger">*</span></label>
                    <input type="number" min="0" max="180" name="tolerance_minutes" value="{{ old('tolerance_minutes', 30) }}" class="filterInput w-full" required>
                    <div class="text-xs text-muted mt-1">Misal 30 menit: Absen s.d. jam 08:30 tetap dihitung Tepat Waktu.</div>
                    @error('tolerance_minutes')
                        <div class="text-xs text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                {{-- Urutan --}}
                <div>
                    <label class="formLabel font-bold">Urutan Tampilan</label>
                    <input type="number" min="0" name="urutan" value="{{ old('urutan', 0) }}" class="filterInput w-full">
                </div>

                {{-- Status Aktif --}}
                <div class="d-flex items-center gap-2 pt-4">
                    <input type="checkbox" name="is_aktif" id="inputIsAktif" value="1" {{ old('is_aktif', 1) ? 'checked' : '' }} class="w-5 h-5 rounded text-primary">
                    <label for="inputIsAktif" class="font-bold text-dark cursor-pointer text-sm">Status Shift Aktif (Bisa digunakan untuk presensi)</label>
                </div>
            </div>

            {{-- Keterangan --}}
            <div class="mb-4">
                <label class="formLabel font-bold">Keterangan / Catatan Shift</label>
                <textarea name="keterangan" rows="3" placeholder="Tambahkan catatan aturan kebijakan shift jika diperlukan..." class="filterInput w-full">{{ old('keterangan') }}</textarea>
            </div>

            <div class="d-flex justify-end gap-2 border-t-base pt-4">
                <a href="{{ route('admin.jadwal-kerja.index') }}" class="profileBtnSecondary">Batal</a>
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
            document.getElementById('durasiHint').textContent = 'Otomatis dihitung dari selisih jam masuk dan pulang (' + hours + ' jam).';
        }
    }
</script>

@endsection
