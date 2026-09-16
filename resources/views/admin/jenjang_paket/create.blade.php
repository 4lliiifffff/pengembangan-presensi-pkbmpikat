@extends('layouts.admin')

@section('content')
    <div class="pageHeaderRow">
        <div>
            <h2 style="margin:0;">Tambah Jenjang &amp; Program Paket</h2>
            <p style="margin:2px 0 0;font-size:12px;color:var(--muted);">Daftarkan jenjang pendidikan kesetaraan atau program kejuruan baru</p>
        </div>
        <a class="btnOutline" href="{{ route('admin.jenjang-paket.index') }}">
            Kembali
        </a>
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
        <form method="POST" action="{{ route('admin.jenjang-paket.store') }}">
            @csrf

            <div class="form-field-wrapper">
                <label class="form-field-label">Nama Jenjang / Program <span style="color:#ef4444;">*</span></label>
                <input class="profileInput" type="text" name="nama_jenjang" id="nama_jenjang" value="{{ old('nama_jenjang') }}" placeholder="Contoh: Paket A (Setara SD) atau Vokasi Tata Boga" required oninput="autoGenerateKode()" />
            </div>

            <div class="form-field-wrapper">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                    <label class="form-field-label" style="margin:0;">Kode Sistem (Unique ID) <span style="color:#ef4444;">*</span></label>
                    <span style="font-size:10.5px;color:var(--muted);">Format: huruf_kecil_dan_underscore</span>
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
                <textarea class="profileInput" name="keterangan" rows="2" style="height:auto;padding:8px 12px;" placeholder="Deskripsi singkat tentang kurikulum atau sasaran program kesetaraan/vokasi">{{ old('keterangan') }}</textarea>
            </div>

            <div class="form-field-wrapper">
                <div class="checkbox-toggle-card">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_aktif" value="1" {{ old('is_aktif', true) ? 'checked' : '' }} style="width:16px;height:16px;" />
                        Status Aktif (dapat dipilih di form kelas)
                    </label>
                </div>
            </div>

            <button type="submit" class="profileBtnPrimary" style="margin-top:12px;">
                <ion-icon name="save-outline"></ion-icon>
                Simpan Master Jenjang
            </button>
        </form>
    </div>

    <script>
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
@endsection
