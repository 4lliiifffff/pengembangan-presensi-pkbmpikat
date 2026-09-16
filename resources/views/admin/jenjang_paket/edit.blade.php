@extends('layouts.admin')

@section('content')
    <div class="pageHeaderRow">
        <div>
            <h2 style="margin:0;">Edit Master Jenjang: {{ $jenjangPaket->nama_jenjang }}</h2>
            <p style="margin:2px 0 0;font-size:12px;color:var(--muted);">Perbarui informasi jenjang paket, kode sistem, dan format tingkatan</p>
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
        <form method="POST" action="{{ route('admin.jenjang-paket.update', $jenjangPaket) }}">
            @csrf
            @method('PUT')

            {{-- Info Rombel / Kelas Terkait --}}
            <div class="info-callout-box" style="margin-top: 0; margin-bottom: 20px;">
                <div class="info-callout-title">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <ion-icon name="layers-outline" style="font-size:16px;"></ion-icon>
                        Total Rombel / Kelas Menggunakan Jenjang Ini: {{ $jenjangPaket->kelas_count }} Kelas
                    </div>
                    <code style="font-size:11px;font-weight:800;background:#e0e7ff;color:#3730a3;padding:2px 8px;border-radius:6px;">
                        Kode: {{ $jenjangPaket->kode }}
                    </code>
                </div>
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Nama Jenjang / Program <span style="color:#ef4444;">*</span></label>
                <input class="profileInput" type="text" name="nama_jenjang" value="{{ old('nama_jenjang', $jenjangPaket->nama_jenjang) }}" placeholder="Contoh: Paket A (Setara SD)" required />
            </div>

            <div class="form-field-wrapper">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                    <label class="form-field-label" style="margin:0;">Kode Sistem (Unique ID) <span style="color:#ef4444;">*</span></label>
                    <span style="font-size:10.5px;color:var(--muted);">Harap berhati-hati jika mengubah kode yang sudah dipakai oleh data kelas</span>
                </div>
                <input class="profileInput" type="text" name="kode" value="{{ old('kode', $jenjangPaket->kode) }}" required />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Format / Rentang Tingkat</label>
                <input class="profileInput" type="text" name="tingkat_label" value="{{ old('tingkat_label', $jenjangPaket->tingkat_label) }}" placeholder="Contoh: Kelas 1 - 6, Tingkat 7 - 9, atau Dasar / Terampil" />
                <div class="field-help-text">Panduan tingkatan nomor kelas bagi admin saat membuat rombel.</div>
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Urutan Tampilan</label>
                <input class="profileInput" type="number" name="urutan" value="{{ old('urutan', $jenjangPaket->urutan) }}" />
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Keterangan / Deskripsi Program</label>
                <textarea class="profileInput" name="keterangan" rows="2" style="height:auto;padding:8px 12px;" placeholder="Deskripsi singkat tentang kurikulum atau sasaran program kesetaraan/vokasi">{{ old('keterangan', $jenjangPaket->keterangan) }}</textarea>
            </div>

            <div class="form-field-wrapper">
                <div class="checkbox-toggle-card">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_aktif" value="1" {{ old('is_aktif', $jenjangPaket->is_aktif) ? 'checked' : '' }} style="width:16px;height:16px;" />
                        Status Aktif (dapat dipilih di form kelas)
                    </label>
                </div>
            </div>

            <button type="submit" class="profileBtnPrimary" style="margin-top:12px;">
                <ion-icon name="save-outline"></ion-icon>
                Simpan Perubahan
            </button>
        </form>
    </div>
@endsection
