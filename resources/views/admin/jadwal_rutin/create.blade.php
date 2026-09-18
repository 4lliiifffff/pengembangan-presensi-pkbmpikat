@extends('layouts.admin')

@section('title', 'Tambah Master Jadwal Rutin Siswa — Admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">PENJADWALAN KBM SISWA</div>
                <h1 class="laporanHeaderTitle">Tambah Jadwal Rutin Siswa</h1>
                <div class="laporanHeaderSub">Tentukan siswa mana yang belajar, tutor pengampu, hari, dan jam belajar berulang</div>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.jadwal-rutin.index') }}" class="btnOutline">
                    <ion-icon name="arrow-back-outline"></ion-icon> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="error-list-container mb-4">
            <div class="error-title">Periksa input berikut:</div>
            <ul style="margin: 0.5rem 0 0 1rem; padding: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Form Card ── --}}
    <div class="form-card-container">
        <form action="{{ route('admin.jadwal-rutin.store') }}" method="POST">
            @csrf

            <div class="form-grid-responsive mb-4">
                <div class="form-field-wrapper">
                    <label class="form-field-label">
                        Pilih Siswa Bimbingan <span class="text-danger">*</span>
                    </label>
                    <select name="siswa_id" required class="filterSelect">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach ($siswas as $s)
                            <option value="{{ $s->id }}" {{ old('siswa_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->nama_siswa }} (No. {{ $s->no_absen ?? '-' }}) - {{ $s->nama_kelas_lengkap ?? 'Reguler' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field-wrapper">
                    <label class="form-field-label">
                        Tutor Pengampu <span class="text-danger">*</span>
                    </label>
                    <select name="tutor_id" required class="filterSelect">
                        <option value="">-- Pilih Tutor --</option>
                        @foreach ($tutors as $t)
                            <option value="{{ $t->id }}" {{ old('tutor_id') == $t->id ? 'selected' : '' }}>
                                {{ $t->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-grid-responsive mb-4">
                <div class="form-field-wrapper">
                    <label class="form-field-label">
                        Kategori Tutorial / Modul
                    </label>
                    <select name="kategori_tutorial_id" id="kategori_tutorial_id" class="filterSelect">
                        <option value="">-- Default / Mengikuti Jadwal Kerja --</option>
                        @foreach ($kategoriTutorials as $k)
                            <option value="{{ $k->id }}" data-durasi="{{ $k->durasi_jam }}" {{ old('kategori_tutorial_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kategori }} (Durasi: {{ number_format((float) $k->durasi_jam, 1) }} Jam)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field-wrapper">
                    <label class="form-field-label">
                        Hari Belajar Mingguan <span class="text-danger">*</span>
                    </label>
                    <select name="hari" required class="filterSelect">
                        <option value="">-- Pilih Hari --</option>
                        @foreach ($hariList as $val => $label)
                            <option value="{{ $val }}" {{ old('hari') == $val ? 'selected' : '' }}>
                                Setiap Hari {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-grid-responsive mb-4">
                <div class="form-field-wrapper">
                    <label class="form-field-label">
                        Jam Mulai KBM <span class="text-danger">*</span>
                    </label>
                    <input type="time" name="jam_masuk" id="jam_masuk" value="{{ old('jam_masuk', '09:00') }}" required class="profileInput">
                </div>

                <div class="form-field-wrapper">
                    <label class="form-field-label">
                        Jam Selesai KBM <span class="text-danger">*</span>
                    </label>
                    <input type="time" name="jam_pulang" id="jam_pulang" value="{{ old('jam_pulang', '11:00') }}" required class="profileInput">
                </div>

                <div class="form-field-wrapper">
                    <label class="form-field-label">
                        Durasi (Jam)
                    </label>
                    <input type="number" step="0.25" min="0.5" max="24" name="durasi_jam" id="durasi_jam" value="{{ old('durasi_jam', '2.00') }}" class="profileInput">
                </div>
            </div>

            <div class="info-callout-box mb-4">
                <div class="info-callout-title">
                    <span><ion-icon name="time-outline" class="text-primary"></ion-icon> Jendela Waktu Presensi Siswa</span>
                    <span class="app-badge badge-status-aktif">Auto Time-Gating</span>
                </div>
                <div class="info-callout-desc">
                    Siswa bimbingan dapat mulai melakukan presensi masuk mandiri <strong>30 menit sebelum jam mulai</strong> (pukul <strong id="previewEarlyTime">08:30</strong> WIB).
                </div>
            </div>

            <div class="form-grid-responsive mb-4">
                <div class="form-field-wrapper">
                    <label class="form-field-label">
                        Berlaku Mulai Tanggal
                    </label>
                    <input type="date" name="berlaku_mulai" value="{{ old('berlaku_mulai', date('Y-m-d')) }}" class="profileInput">
                </div>

                <div class="form-field-wrapper">
                    <label class="form-field-label">
                        Berlaku Sampai Tanggal (Opsional)
                    </label>
                    <input type="date" name="berlaku_sampai" value="{{ old('berlaku_sampai') }}" class="profileInput">
                </div>
            </div>

            <div class="form-field-wrapper mb-4">
                <label class="form-field-label">
                    Catatan / Keterangan Tambahan
                </label>
                <textarea name="keterangan" rows="2" placeholder="Catatan khusus kesepakatan belajar..." class="profileInput" style="height: auto; resize: vertical;">{{ old('keterangan') }}</textarea>
            </div>

            <div class="form-grid-responsive mb-4">
                <div class="checkbox-toggle-card">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label for="is_active" class="cursor-pointer" style="margin: 0;">
                        Jadwal Rutin Berstatus Aktif
                    </label>
                </div>

                <div class="checkbox-toggle-card">
                    <input type="checkbox" id="auto_generate" name="auto_generate" value="1" {{ old('auto_generate', true) ? 'checked' : '' }}>
                    <label for="auto_generate" class="cursor-pointer text-success" style="margin: 0;">
                        ⚡ Langsung generate sesi KBM untuk 4 minggu ke depan
                    </label>
                </div>
            </div>

            <div class="form-action-footer">
                <a href="{{ route('admin.jadwal-rutin.index') }}" class="btnOutline">Batal</a>
                <button type="submit" class="profileBtnPrimary">
                    <ion-icon name="save-outline"></ion-icon> Simpan Master Jadwal Rutin
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const jamMasuk = document.getElementById('jam_masuk');
    const jamPulang = document.getElementById('jam_pulang');
    const durasiJam = document.getElementById('durasi_jam');
    const previewEarly = document.getElementById('previewEarlyTime');

    function updateCalculations() {
        if (jamMasuk && jamMasuk.value) {
            const [h, m] = jamMasuk.value.split(':').map(Number);
            let earlyH = h;
            let earlyM = m - 30;
            if (earlyM < 0) {
                earlyM += 60;
                earlyH = (earlyH - 1 + 24) % 24;
            }
            if (previewEarly) {
                previewEarly.textContent = String(earlyH).padStart(2, '0') + ':' + String(earlyM).padStart(2, '0');
            }
        }

        if (jamMasuk && jamPulang && jamMasuk.value && jamPulang.value) {
            const [h1, m1] = jamMasuk.value.split(':').map(Number);
            const [h2, m2] = jamPulang.value.split(':').map(Number);
            let diffMin = (h2 * 60 + m2) - (h1 * 60 + m1);
            if (diffMin > 0 && durasiJam) {
                durasiJam.value = (diffMin / 60).toFixed(2);
            }
        }
    }

    if (jamMasuk) jamMasuk.addEventListener('input', updateCalculations);
    if (jamPulang) jamPulang.addEventListener('input', updateCalculations);
    updateCalculations();
</script>

@endsection
