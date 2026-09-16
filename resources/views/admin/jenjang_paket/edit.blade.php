@extends('layouts.admin')

@section('content')
    <div class="pageHeaderRow">
        <div >
            <h2 class="mb-0 mt-0">Edit Master Jenjang: {{ $jenjangPaket->nama_jenjang }}</h2>
            <p class="mt-1 text-sm text-muted">Perbarui informasi jenjang paket, kode sistem, dan format tingkatan</p>
        </div>
        <a class="btnOutline" href="{{ route('admin.jenjang-paket.index') }}">
            Kembali
        </a>
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
        <form method="POST" action="{{ route('admin.jenjang-paket.update', $jenjangPaket) }}">
            @csrf
            @method('PUT')

            {{-- Info Rombel / Kelas Terkait --}}
            <div class="info-callout-box mt-0 mb-4">
                <div class="info-callout-title">
                    <div class="d-flex items-center gap-1">
                        <ion-icon name="layers-outline" class="icon-sm"></ion-icon>
                        Total Rombel / Kelas Menggunakan Jenjang Ini: {{ $jenjangPaket->kelas_count }} Kelas
                    </div>
                    <code  class="text-xs font-extrabold rounded-sm badge-auto-gabungan">
                        Kode: {{ $jenjangPaket->kode }}
                    </code>
                </div>
            </div>

            <div class="form-field-wrapper">
                <label class="form-field-label">Nama Jenjang / Program <span class="text-danger">*</span></label>
                <input class="profileInput" type="text" name="nama_jenjang" value="{{ old('nama_jenjang', $jenjangPaket->nama_jenjang) }}" placeholder="Contoh: Paket A (Setara SD)" required />
            </div>

            <div class="form-field-wrapper">
                <div class="flex-between mb-1">
                    <label class="form-field-label mb-0 mt-0">Kode Sistem (Unique ID) <span class="text-danger">*</span></label>
                    <span class="text-xs text-muted">Harap berhati-hati jika mengubah kode yang sudah dipakai oleh data kelas</span>
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
                <textarea name="keterangan" rows="2" placeholder="Deskripsi singkat tentang kurikulum atau sasaran program kesetaraan/vokasi" class="profileInput h-auto p-2">{{ old('keterangan', $jenjangPaket->keterangan) }}</textarea>
            </div>

            <div class="form-field-wrapper">
                <div class="checkbox-toggle-card">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_aktif" value="1" {{ old('is_aktif', $jenjangPaket->is_aktif) ? 'checked' : '' }} class="w-auto" />
                        Status Aktif (dapat dipilih di form kelas)
                    </label>
                </div>
            </div>

            <button type="submit" class="profileBtnPrimary mt-3">
                <ion-icon name="save-outline"></ion-icon>
                Simpan Perubahan
            </button>
        </form>
    </div>
@endsection
