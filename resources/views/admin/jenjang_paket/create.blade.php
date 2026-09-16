@extends('layouts.admin')

@section('content')
<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">FORMULIR MASTER JENJANG</div>
                <h1 class="laporanHeaderTitle">Tambah Jenjang &amp; Program Paket</h1>
                <div class="laporanHeaderSub">Daftarkan jenjang pendidikan kesetaraan atau program kejuruan baru</div>
            </div>
            <div class="laporanHeaderActions">
                <a class="btnOutline" href="{{ route('admin.jenjang-paket.index') }}">
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
        <form method="POST" action="{{ route('admin.jenjang-paket.store') }}">
            @csrf

            <div class="form-field-wrapper">
                <label class="form-field-label">Nama Jenjang / Program <span class="text-danger">*</span></label>
                <input class="profileInput" type="text" name="nama_jenjang" id="nama_jenjang" value="{{ old('nama_jenjang') }}" placeholder="Contoh: Paket A (Setara SD) atau Vokasi Tata Boga" required oninput="autoGenerateKode()" />
            </div>

            <div class="form-field-wrapper">
                <div class="flex-between mb-1">
                    <label class="form-field-label mb-0 mt-0">Kode Sistem (Unique ID) <span class="text-danger">*</span></label>
                    <span class="text-xs text-muted">Format: huruf_kecil_dan_underscore</span>
                </div>
                <input class="profileInput" type="text" name="kode" id="kode" value="{{ old('kode') }}" placeholder="Contoh: paket_a atau vokasi_tata_boga" required />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Format / Rentang Tingkat</label>
                <input class="profileInput" type="text" name="tingkat_label" value="{{ old('tingkat_label') }}" placeholder="Contoh: Kelas 1 - 6, Tingkat 7 - 9, atau Dasar / Terampil" />
                <div class="field-help-text">Panduan tingkatan nomor kelas bagi admin saat membuat rombel.</div>
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Urutan Tampilan</label>
                <input class="profileInput" type="number" name="urutan" value="{{ old('urutan', $nextUrutan ?? 1) }}" />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Keterangan / Deskripsi Program</label>
                <textarea name="keterangan" rows="2" placeholder="Deskripsi singkat tentang kurikulum atau sasaran program kesetaraan/vokasi" class="profileInput h-auto p-2">{{ old('keterangan') }}</textarea>
            </div>

            <div class="form-field-wrapper">
                <div class="checkbox-toggle-card">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_aktif" value="1" {{ old('is_aktif', true) ? 'checked' : '' }} class="w-auto" />
                        Status Aktif (dapat dipilih di form kelas)
                    </label>
                </div>
            </div>

            <button type="submit" class="profileBtnPrimary mt-3">
                <ion-icon name="save-outline"></ion-icon>
                Simpan Master Jenjang
            </button>
        </form>
    </div>

    <script >
        function autoGenerateKode() {
            const nama = document.getElementById('nama_jenjang').value;
            const kodeInput = document.getElementById('kode');
            if (!kodeInput.dataset.manualEdit) {
                let slug = nama.toLowerCase()
                    .replace(/\s+/g, '_')
                    .replace(/[^\w]/g, '');
                kodeInput.value = slug;
            }
        }

        document.getElementById('kode').addEventListener('input', function() {
            this.dataset.manualEdit = 'true';
        });
    </script>
</div>
@endsection
