@extends('layouts.admin')

@section('content')
<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">FORMULIR KELAS &amp; ROMBEL</div>
                <h1 class="laporanHeaderTitle">Tambah Kelas Baru</h1>
                <div class="laporanHeaderSub">Daftarkan rombongan belajar atau kelas program kesetaraan/vokasi</div>
            </div>
            <div class="laporanHeaderActions">
                <a class="btnOutline" href="{{ route('admin.kelas.index') }}">
                    Kembali
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
        <form method="POST" action="{{ route('admin.kelas.store') }}" id="formKelas">
            @csrf

            {{-- Banner Edukasi Arsitektur Rombel & Paket --}}
            <div class="info-callout-box mt-0 mb-4">
                <div class="info-callout-title">
                    <div class="d-flex items-center gap-1">
                        <ion-icon name="layers-outline" class="icon-sm"></ion-icon>
                        Struktur Rombel &amp; Validasi Presensi Otomatis
                    </div>
                    <a href="{{ route('admin.jenjang-paket.index') }}" target="_blank" class="info-callout-link">
                        Kelola Master Jenjang &rarr;
                    </a>
                </div>
                <div class="info-callout-desc">
                    Pemilihan <b >Jenjang Paket</b> digunakan oleh sistem untuk <b >Universal Package Guard</b> (mencegah absen campur paket) serta <b >Deteksi Otomatis Sesi Gabungan Komunitas</b> ketika tutor mengajar murid dari beberapa kelas dalam jenjang yang sama.
                </div>
            </div>

            <div class="form-field-wrapper">
                <div class="flex-between mb-1">
                    <label class="form-field-label mb-0 mt-0">Jenjang / Kategori Paket <span class="text-danger">*</span></label>
                    <a href="{{ route('admin.jenjang-paket.index') }}" target="_blank" class="text-xs font-bold text-primary text-no-decor d-inline-flex items-center gap-1">
                        <ion-icon name="add-circle-outline"></ion-icon> Tambah Jenjang Baru
                    </a>
                </div>
                <select class="profileInput" name="jenjang_paket_id" id="jenjang_paket_id" required onchange="handleJenjangChange(this.value)">
                    <option value="">-- Pilih Jenjang / Program Paket --</option>
                    @foreach ($jenjangPakets as $jp)
                        <option value="{{ $jp->id }}" data-kode="{{ $jp->kode }}" data-label="{{ $jp->nama_jenjang }}" data-tingkat="{{ $jp->tingkat_label }}" {{ (string) old('jenjang_paket_id') === (string) $jp->id ? 'selected' : '' }}>
                            {{ $jp->nama_jenjang }} @if($jp->tingkat_label) ({{ $jp->tingkat_label }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Tingkat / Nomor Kelas</label>
                <input class="profileInput" type="text" name="tingkat" id="tingkat" value="{{ old('tingkat') }}" placeholder="Contoh: 1-6 (SD), 7-9 (SMP), 10-12 (SMA), atau Dasar/Terampil" oninput="autoSuggestNamaKelas()" />
                <div id="tingkatHelper" class="field-help-text">Nomor tingkatan kelas atau level pembelajaran.</div>
            </div>

            <div class="form-field-wrapper">
                <div class="flex-between mb-1">
                    <label class="form-field-label mb-0 mt-0">Nama Kelas <span class="text-danger">*</span></label>
                    <button type="button" onclick="generateAutoNama()" class="bg-none border-none text-primary text-xs font-bold cursor-pointer d-flex items-center gap-1">
                        <ion-icon name="flash-outline"></ion-icon> Format Otomatis
                    </button>
                </div>
                <input class="profileInput" type="text" name="nama_kelas" id="nama_kelas" value="{{ old('nama_kelas') }}" placeholder="Contoh: Paket B - Kelas 7" required />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Keterangan / Catatan Rombel</label>
                <textarea name="keterangan" rows="2" placeholder="Catatan tambahan rombongan belajar (opsional)" class="profileInput h-auto p-2">{{ old('keterangan') }}</textarea>
            </div>

            <button type="submit" class="profileBtnPrimary mt-3">
                <ion-icon name="save-outline"></ion-icon>
                Simpan Kelas
            </button>
        </form>
    </div>

    <script >
        function handleJenjangChange(val) {
            const select = document.getElementById('jenjang_paket_id');
            const selectedOpt = select.options[select.selectedIndex];
            const tingkatInfo = selectedOpt.getAttribute('data-tingkat');
            const helper = document.getElementById('tingkatHelper');
            if (tingkatInfo && helper) {
                helper.innerHTML = 'Rekomendasi format tingkatan: <b >' + tingkatInfo + '</b>';
            }
            autoSuggestNamaKelas();
        }

        function generateAutoNama() {
            const select = document.getElementById('jenjang_paket_id');
            const tingkat = document.getElementById('tingkat').value.trim();
            const namaField = document.getElementById('nama_kelas');

            if (!select.value) return;

            const selectedOpt = select.options[select.selectedIndex];
            let rawLabel = selectedOpt.getAttribute('data-label') || select.value;
            // Clean up parenthesis e.g. "Paket A (Setara SD)" -> "Paket A"
            let prefix = rawLabel.split('(')[0].trim();

            if (tingkat) {
                if (/^\d+$/.test(tingkat)) {
                    namaField.value = prefix + ' - Kelas ' + tingkat;
                } else {
                    namaField.value = prefix + ' - ' + tingkat;
                }
            } else {
                namaField.value = prefix;
            }
        }

        function autoSuggestNamaKelas() {
            const namaField = document.getElementById('nama_kelas');
            if (!namaField.value || namaField.dataset.autofilled === 'true') {
                generateAutoNama();
                namaField.dataset.autofilled = 'true';
            }
        }

        document.getElementById('nama_kelas').addEventListener('input', function() {
            this.dataset.autofilled = 'false';
        });
    </script>
</div>
@endsection
