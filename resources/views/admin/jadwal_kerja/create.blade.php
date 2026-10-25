@extends('layouts.admin')

@section('title', 'Tambah Shift Kerja Magang - Admin')

@section('content')

    <div class="laporanPageWrapper">
        {{-- ── Header Card ── --}}
        <div class="laporanHeader">
            <div class="laporanHeaderCard">
                <div class="laporanHeaderInfo">
                    <div class="laporanHeaderLabel">TAMBAH MASTER DATA</div>
                    <h1 class="laporanHeaderTitle">Tambah Jadwal &amp; Shift Kerja Magang</h1>
                    <div class="laporanHeaderSub">Tentukan jam masuk, jam pulang, dan toleransi keterlambatan piket mahasiswa magang/PKL</div>
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
                        <label class="filterFieldLabel">Nama Shift Magang <span class="text-danger">*</span></label>
                        <input type="text" name="nama_shift" id="inputNamaShift" value="{{ old('nama_shift') }}"
                            placeholder="Contoh: Shift Reguler Magang (09:00 - 15:00)"
                            class="profileInput @error('nama_shift') border-danger @enderror" required>
                        <span class="field-help-text">Nama shift yang akan tampil pada sistem presensi magang.</span>
                    </div>

                    {{-- Kode Shift --}}
                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Kode Shift Unik <span class="text-danger">*</span></label>
                        <input type="text" name="kode_shift" id="inputKodeShift" value="{{ old('kode_shift') }}"
                            placeholder="Contoh: magang_pagi"
                            class="profileInput @error('kode_shift') border-danger @enderror" required>
                        <span class="field-help-text">Hanya huruf kecil, angka, dash (-), dan underscore (_).</span>
                    </div>
                </div>

                {{-- Jam Kerja & Durasi Card --}}
                <div class="p-3 bg-card-alt rounded-xl border-base my-4">
                    <div class="text-xs font-bold uppercase tracking-wider text-muted mb-3">
                        <ion-icon name="time-outline"></ion-icon> Waktu Operasional &amp; Durasi Piket
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="form-field-wrapper">
                            <label class="filterFieldLabel">Jam Masuk (WIB) <span class="text-danger">*</span></label>
                            <input type="time" name="jam_masuk" id="inputJamMasuk" value="{{ old('jam_masuk', '09:00') }}"
                                onchange="calculateDuration()"
                                class="profileInput @error('jam_masuk') border-danger @enderror" required>
                        </div>

                        <div class="form-field-wrapper">
                            <label class="filterFieldLabel">Jam Pulang (WIB) <span class="text-danger">*</span></label>
                            <input type="time" name="jam_pulang" id="inputJamPulang"
                                value="{{ old('jam_pulang', '15:00') }}" onchange="calculateDuration()"
                                class="profileInput @error('jam_pulang') border-danger @enderror" required>
                        </div>

                        <div class="form-field-wrapper">
                            <label class="filterFieldLabel">Durasi Piket (Jam)</label>
                            <input type="number" step="0.25" min="0.5" max="24" name="durasi_jam" id="inputDurasiJam"
                                value="{{ old('durasi_jam', '6.00') }}" class="profileInput">
                            <span class="field-help-text" id="durasiHint">Dihitung otomatis dari selisih waktu.</span>
                        </div>
                    </div>
                </div>

                <div class="form-grid-responsive">
                    {{-- Batas Awal --}}
                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Batas Awal Absen (Menit) <span class="text-danger">*</span></label>
                        <input type="number" min="0" max="180" name="earliest_minutes"
                            value="{{ old('earliest_minutes', 30) }}" class="profileInput" required>
                        <span class="field-help-text">Misal 30 menit: Jam 09:00 dapat mulai diabsen dari pukul 08:30 WIB.</span>
                    </div>

                    {{-- Toleransi Keterlambatan --}}
                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Toleransi Keterlambatan (Menit) <span class="text-danger">*</span></label>
                        <input type="number" min="0" max="180" name="tolerance_minutes"
                            value="{{ old('tolerance_minutes', 30) }}" class="profileInput" required>
                        <span class="field-help-text">Misal 30 menit: Absen s.d. jam 09:30 tetap dihitung Tepat Waktu.</span>
                    </div>

                    {{-- Urutan --}}
                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel">Urutan Tampilan</label>
                        <input type="number" min="0" name="urutan" value="{{ old('urutan', 0) }}" class="profileInput"
                            placeholder="0">
                        <span class="field-help-text">Urutan prioritas saat ditampilkan di daftar.</span>
                    </div>

                    {{-- Status Aktif Switch --}}
                    <div class="form-field-wrapper flex-col justify-end">
                        <label class="checkbox-toggle-card">
                            <input type="checkbox" name="is_aktif" value="1" {{ old('is_aktif', 1) ? 'checked' : '' }}>
                            <div>
                                <div class="font-bold text-dark text-sm">Status Shift Aktif</div>
                                <div class="field-help-text m-0">Shift ini dapat langsung dipilih saat presensi masuk magang.</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Keterangan --}}
                <div class="form-field-wrapper mt-3">
                    <label class="filterFieldLabel">Keterangan / Catatan Kebijakan (Opsional)</label>
                    <textarea name="keterangan" rows="3"
                        placeholder="Tambahkan catatan aturan kebijakan shift magang jika diperlukan..."
                        class="profileInput">{{ old('keterangan') }}</textarea>
                </div>

                <div class="form-action-footer">
                    <a href="{{ route('admin.jadwal-kerja.index') }}" class="btnOutline">
                        <ion-icon name="arrow-back-outline"></ion-icon> Batal
                    </a>
                    <button type="submit" class="profileBtnPrimary">
                        <ion-icon name="save-outline"></ion-icon> Simpan Shift Magang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function calculateDuration() {
            const masuk = document.getElementById('inputJamMasuk').value;
            const pulang = document.getElementById('inputJamPulang').value;
            const durasiInput = document.getElementById('inputDurasiJam');

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

        document.addEventListener('DOMContentLoaded', function () {
            calculateDuration();
        });
    </script>
@endsection