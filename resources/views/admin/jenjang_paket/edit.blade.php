@extends('layouts.admin')

@section('content')
<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">PERBARUI MASTER JENJANG</div>
                <h1 class="laporanHeaderTitle">Edit Master Jenjang: {{ $jenjangPaket->nama_jenjang }}</h1>
                <div class="laporanHeaderSub">Perbarui informasi jenjang paket, kode sistem, dan format tingkatan</div>
            </div>
            <div class="laporanHeaderActions">
                <a class="btnOutline" href="{{ route('admin.jenjang-paket.index') }}">
                    <ion-icon name="arrow-back-outline"></ion-icon> Kembali
                </a>
            </div>
        </div>
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
        {{-- Info Rombel / Kelas Terkait --}}
        <div class="info-callout-box mt-0 mb-4">
            <div class="info-callout-title">
                <div class="d-flex items-center gap-2">
                    <ion-icon name="layers-outline" class="icon-md text-primary"></ion-icon>
                    <span>Total Rombel / Kelas Terkait: <strong>{{ $jenjangPaket->kelas_count }} Kelas</strong></span>
                </div>
                <code class="text-xs font-extrabold rounded-sm badge-auto-gabungan">
                    Kode: {{ $jenjangPaket->kode }}
                </code>
            </div>
            <div class="info-callout-desc">
                Perubahan nama jenjang akan langsung terlihat pada seluruh rombel yang terdaftar di bawah jenjang ini.
            </div>
        </div>

        <form method="POST" action="{{ route('admin.jenjang-paket.update', $jenjangPaket) }}">
            @csrf
            @method('PUT')

            <div class="form-grid-responsive">
                {{-- Nama Jenjang --}}
                <div class="form-field-wrapper">
                    <label class="form-field-label" for="nama_jenjang">Nama Jenjang / Program <span class="text-danger">*</span></label>
                    <input class="profileInput" type="text" name="nama_jenjang" id="nama_jenjang" value="{{ old('nama_jenjang', $jenjangPaket->nama_jenjang) }}" placeholder="Contoh: Paket A (Setara SD)" required />
                    <div class="field-help-text">Nama resmi jenjang yang akan tampil di seluruh kartu kelas &amp; presensi.</div>
                </div>

                {{-- Kode Sistem --}}
                <div class="form-field-wrapper">
                    <label class="form-field-label" for="kode">
                        Kode Sistem (Unique ID) <span class="text-danger">*</span>
                    </label>
                    <input class="profileInput font-monospace" type="text" name="kode" id="kode" value="{{ old('kode', $jenjangPaket->kode) }}" required />
                    <div class="field-help-text text-amber font-medium">Perhatikan kehati-hatian jika mengubah kode yang telah digunakan oleh rombel.</div>
                </div>

                {{-- Format / Rentang Tingkat --}}
                <div class="form-field-wrapper">
                    <label class="form-field-label" for="tingkat_label">Format / Rentang Tingkat</label>
                    <input class="profileInput" type="text" name="tingkat_label" id="tingkat_label" value="{{ old('tingkat_label', $jenjangPaket->tingkat_label) }}" placeholder="Contoh: Kelas 1 - 6, Tingkat 10 - 12, atau Dasar / Terampil" />
                    <div class="field-help-text">Panduan penomoran tingkatan kelas bagi admin saat membuat rombel belajar.</div>
                </div>

                {{-- Urutan Tampilan --}}
                <div class="form-field-wrapper">
                    <label class="form-field-label" for="urutan">Urutan Tampilan</label>
                    <input class="profileInput" type="number" name="urutan" id="urutan" min="1" value="{{ old('urutan', $jenjangPaket->urutan) }}" />
                    <div class="field-help-text">Nomor urut posisi jenjang pada tab filter dan menu pilihan.</div>
                </div>

                {{-- Keterangan / Deskripsi Program --}}
                <div class="form-field-wrapper form-grid-full">
                    <label class="form-field-label" for="keterangan">Keterangan / Deskripsi Program</label>
                    <textarea name="keterangan" id="keterangan" rows="3" placeholder="Deskripsi singkat kurikulum atau sasaran program..." class="profileInput h-auto p-3">{{ old('keterangan', $jenjangPaket->keterangan) }}</textarea>
                </div>

                {{-- Checkbox Status Aktif --}}
                <div class="form-field-wrapper form-grid-full">
                    <label class="checkbox-toggle-card">
                        <input type="checkbox" name="is_aktif" value="1" {{ old('is_aktif', $jenjangPaket->is_aktif) ? 'checked' : '' }} />
                        <div class="flex-1">
                            <div class="font-bold text-dark">Status Aktif</div>
                            <div class="text-xs text-muted font-normal mt-1">Jika aktif, jenjang ini dapat dipilih saat membuat rombel/kelas baru.</div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Form Action Footer --}}
            <div class="form-action-footer">
                <a href="{{ route('admin.jenjang-paket.index') }}" class="btnOutline">
                    <ion-icon name="close-outline"></ion-icon> Batal
                </a>
                <button type="submit" class="profileBtnPrimary">
                    <ion-icon name="save-outline"></ion-icon>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
