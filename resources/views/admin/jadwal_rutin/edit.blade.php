@extends('layouts.admin')

@section('title', 'Edit Master Jadwal Rutin Siswa — Admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">PENJADWALAN KBM SISWA</div>
                <h1 class="laporanHeaderTitle">Edit Master Jadwal Rutin Siswa</h1>
                <div class="laporanHeaderSub">Ubah konfigurasi hari, jam belajar, atau tutor pengampu untuk siswa</div>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.jadwal-rutin.index') }}" class="profileBtnSecondary">
                    <ion-icon name="arrow-back-outline"></ion-icon> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

    {{-- ── Form Card ── --}}
    <div class="laporanTableCard" style="max-width: 800px; margin: 0 auto; padding: 2rem;">
        <form action="{{ route('admin.jadwal-rutin.update', $jadwalRutin) }}" method="POST">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.88rem;">
                    <strong>Terdapat kesalahan input data:</strong>
                    <ul style="margin: 0.5rem 0 0 1rem; padding: 0;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <div>
                    <label style="display: block; font-size: 0.88rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Pilih Siswa Bimbingan <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="siswa_id" required class="filterSelect" style="width: 100%;">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach ($siswas as $s)
                            <option value="{{ $s->id }}" {{ old('siswa_id', $jadwalRutin->siswa_id) == $s->id ? 'selected' : '' }}>
                                {{ $s->nama_siswa }} (No. {{ $s->no_absen ?? '-' }}) - {{ $s->nama_kelas_lengkap ?? 'Reguler' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 0.88rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Tutor Pengampu <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="tutor_id" required class="filterSelect" style="width: 100%;">
                        <option value="">-- Pilih Tutor --</option>
                        @foreach ($tutors as $t)
                            <option value="{{ $t->id }}" {{ old('tutor_id', $jadwalRutin->tutor_id) == $t->id ? 'selected' : '' }}>
                                {{ $t->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <div>
                    <label style="display: block; font-size: 0.88rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Kategori Tutorial / Modul
                    </label>
                    <select name="kategori_tutorial_id" id="kategori_tutorial_id" class="filterSelect" style="width: 100%;">
                        <option value="">-- Default / Mengikuti Jadwal Kerja --</option>
                        @foreach ($kategoriTutorials as $k)
                            <option value="{{ $k->id }}" {{ old('kategori_tutorial_id', $jadwalRutin->kategori_tutorial_id) == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kategori }} (Durasi: {{ number_format((float) $k->durasi_jam, 1) }} Jam)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 0.88rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Hari Belajar Mingguan <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="hari" required class="filterSelect" style="width: 100%;">
                        <option value="">-- Pilih Hari --</option>
                        @foreach ($hariList as $val => $label)
                            <option value="{{ $val }}" {{ old('hari', $jadwalRutin->hari) == $val ? 'selected' : '' }}>
                                Setiap Hari {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                <div>
                    <label style="display: block; font-size: 0.88rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Jam Mulai KBM <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="time" name="jam_masuk" id="jam_masuk" value="{{ old('jam_masuk', substr((string) $jadwalRutin->jam_masuk, 0, 5)) }}" required class="filterInput" style="width: 100%;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.88rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Jam Selesai KBM <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="time" name="jam_pulang" id="jam_pulang" value="{{ old('jam_pulang', substr((string) $jadwalRutin->jam_pulang, 0, 5)) }}" required class="filterInput" style="width: 100%;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.88rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Durasi (Jam)
                    </label>
                    <input type="number" step="0.25" min="0.5" max="24" name="durasi_jam" id="durasi_jam" value="{{ old('durasi_jam', $jadwalRutin->durasi_jam) }}" class="filterInput" style="width: 100%;">
                </div>
            </div>

            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1.25rem; font-size: 0.82rem; color: #166534; display: flex; align-items: center; gap: 8px;">
                <ion-icon name="information-circle-outline" style="font-size: 1.25rem;"></ion-icon>
                <span>Siswa akan dapat melakukan presensi mandiri mulai <strong>30 menit sebelum jam mulai</strong> (<span id="previewEarlyTime">08:30</span> WIB).</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <div>
                    <label style="display: block; font-size: 0.88rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Berlaku Mulai Tanggal
                    </label>
                    <input type="date" name="berlaku_mulai" value="{{ old('berlaku_mulai', $jadwalRutin->berlaku_mulai?->format('Y-m-d')) }}" class="filterInput" style="width: 100%;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.88rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Berlaku Sampai Tanggal (Opsional)
                    </label>
                    <input type="date" name="berlaku_sampai" value="{{ old('berlaku_sampai', $jadwalRutin->berlaku_sampai?->format('Y-m-d')) }}" class="filterInput" style="width: 100%;">
                </div>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.88rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                    Catatan / Keterangan Tambahan
                </label>
                <textarea name="keterangan" rows="2" placeholder="Catatan khusus kesepakatan belajar..." class="filterInput" style="width: 100%; height: auto;">{{ old('keterangan', $jadwalRutin->keterangan) }}</textarea>
            </div>

            <div style="margin-bottom: 1.25rem; display: flex; flex-direction: column; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $jadwalRutin->is_active) ? 'checked' : '' }} style="width: 16px; height: 16px;">
                    <label for="is_active" style="font-size: 0.88rem; font-weight: 600; color: #374151; cursor: pointer;">
                        Jadwal Rutin Berstatus Aktif
                    </label>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" id="sync_future" name="sync_future" value="1" {{ old('sync_future', true) ? 'checked' : '' }} style="width: 16px; height: 16px;">
                    <label for="sync_future" style="font-size: 0.85rem; color: #047857; font-weight: 600; cursor: pointer;">
                        ⚡ Sinkronkan perubahan jam/tutor ke seluruh sesi kalender yang belum terlaksana di masa depan
                    </label>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 2rem; border-top: 1px solid #f3f4f6; padding-top: 1.25rem;">
                <a href="{{ route('admin.jadwal-rutin.index') }}" class="profileBtnSecondary">Batal</a>
                <button type="submit" class="profileBtnPrimary">
                    <ion-icon name="save-outline"></ion-icon> Perbarui Master Jadwal Rutin
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
        if (jamMasuk.value) {
            const [h, m] = jamMasuk.value.split(':').map(Number);
            let earlyH = h;
            let earlyM = m - 30;
            if (earlyM < 0) {
                earlyM += 60;
                earlyH = (earlyH - 1 + 24) % 24;
            }
            previewEarly.textContent = String(earlyH).padStart(2, '0') + ':' + String(earlyM).padStart(2, '0');
        }

        if (jamMasuk.value && jamPulang.value) {
            const [h1, m1] = jamMasuk.value.split(':').map(Number);
            const [h2, m2] = jamPulang.value.split(':').map(Number);
            let diffMin = (h2 * 60 + m2) - (h1 * 60 + m1);
            if (diffMin > 0) {
                durasiJam.value = (diffMin / 60).toFixed(2);
            }
        }
    }

    jamMasuk.addEventListener('input', updateCalculations);
    jamPulang.addEventListener('input', updateCalculations);
    updateCalculations();
</script>

@endsection
