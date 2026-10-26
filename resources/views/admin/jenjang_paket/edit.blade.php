@extends('layouts.admin')

@section('title', 'Edit Master Jenjang: ' . $jenjangPaket->nama_jenjang . ' - Admin')

@section('content')
    <div class="laporanPageWrapper">
        {{-- ── Header Card ── --}}
        <div class="laporanHeader">
            <div class="laporanHeaderCard">
                <div class="laporanHeaderInfo">
                    <div class="laporanHeaderLabel">PERBARUI MASTER PENDIDIKAN</div>
                    <h1 class="laporanHeaderTitle">Edit Master Jenjang: {{ $jenjangPaket->nama_jenjang }}</h1>
                    <div class="laporanHeaderSub">Perbarui klasifikasi jenjang paket, kode sistem, dan format tingkatan kelas</div>
                </div>
                <div class="laporanHeaderActions">
                    <a class="btnOutline" href="{{ route('admin.jenjang-paket.index') }}">
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
        <div class="form-card-container form-card-wide formResponsiveWrapper">
            {{-- Info Rombel / Kelas Terkait --}}
            <div class="mb-4 p-3 sm:p-4 rounded-xl border-base bg-card-alt flex items-start gap-2.5 sm:gap-3">
                <div class="text-primary text-xl flex-shrink-0 mt-0.5">
                    <ion-icon name="school-outline"></ion-icon>
                </div>
                <div class="text-xs sm:text-sm w-full">
                    <div class="font-bold text-dark flex flex-wrap items-center justify-between gap-1.5">
                        <span>Total Rombel Terkait: <strong>{{ $jenjangPaket->kelas_count }} Kelas</strong></span>
                        <code class="text-xs font-bold font-monospace bg-slate-200 text-slate-800 px-2 py-0.5 rounded">
                            Kode: {{ $jenjangPaket->kode }}
                        </code>
                    </div>
                    <div class="text-muted mt-1 leading-relaxed">
                        Perubahan nama jenjang akan langsung terefleksi pada seluruh rombel yang bernaung di bawah jenjang ini.
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.jenjang-paket.update', $jenjangPaket) }}">
                @csrf
                @method('PUT')

                <div class="form-grid-responsive">
                    {{-- Nama Jenjang --}}
                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel" for="nama_jenjang">
                            Nama Jenjang / Program <span class="text-danger">*</span>
                        </label>
                        <input class="profileInput text-md w-full" type="text" name="nama_jenjang" id="nama_jenjang"
                            value="{{ old('nama_jenjang', $jenjangPaket->nama_jenjang) }}"
                            placeholder="Contoh: Paket A (Setara SD)" required />
                        <div class="text-xs text-muted mt-1">Nama resmi jenjang yang tampil di seluruh kartu kelas &amp; presensi.</div>
                    </div>

                    {{-- Kode Sistem --}}
                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel" for="kode">
                            Kode Sistem (ID Unik) <span class="text-danger">*</span>
                        </label>
                        <input class="profileInput text-md font-monospace w-full" type="text" name="kode" id="kode"
                            value="{{ old('kode', $jenjangPaket->kode) }}" required />
                        <div class="text-xs text-amber font-medium mt-1">Harap berhati-hati jika mengubah kode yang telah digunakan oleh rombel kelas.</div>
                    </div>

                    {{-- Format / Rentang Tingkat --}}
                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel" for="tingkat_label">
                            Format / Rentang Tingkatan
                        </label>
                        <input class="profileInput text-md w-full" type="text" name="tingkat_label" id="tingkat_label"
                            value="{{ old('tingkat_label', $jenjangPaket->tingkat_label) }}"
                            placeholder="Contoh: Kelas 1 - 6, Tingkat 10 - 12, atau Dasar / Terampil" />
                        <div class="text-xs text-muted mt-1">Panduan penomoran tingkatan bagi pengelola saat membuat rombel belajar.</div>
                    </div>

                    {{-- Urutan Tampilan --}}
                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel" for="urutan">
                            Urutan Tampilan
                        </label>
                        <input class="profileInput text-md w-full" type="number" name="urutan" id="urutan" min="1"
                            value="{{ old('urutan', $jenjangPaket->urutan) }}" />
                        <div class="text-xs text-muted mt-1">Nomor posisi urut pada tab filter dan menu pilihan.</div>
                    </div>

                    {{-- Keterangan / Deskripsi Program --}}
                    <div class="form-field-wrapper form-grid-full">
                        <label class="filterFieldLabel" for="keterangan">
                            Keterangan / Deskripsi Kurikulum
                        </label>
                        <textarea name="keterangan" id="keterangan" rows="3"
                            placeholder="Deskripsi singkat kurikulum, sasaran murid, atau kompetensi program..."
                            class="profileInput text-md h-auto p-3 w-full">{{ old('keterangan', $jenjangPaket->keterangan) }}</textarea>
                    </div>

                    {{-- Checkbox Status Aktif --}}
                    <div class="form-field-wrapper form-grid-full">
                        <label class="checkbox-toggle-card p-3 sm:p-3.5">
                            <input type="checkbox" name="is_aktif" value="1" {{ old('is_aktif', $jenjangPaket->is_aktif) ? 'checked' : '' }} />
                            <div class="flex-1">
                                <div class="font-bold text-dark text-sm sm:text-base">Status Aktif</div>
                                <div class="text-xs text-muted font-normal mt-0.5">Jika aktif, jenjang ini dapat dipilih saat membuat rombel/kelas baru.</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-action-footer">
                    <a href="{{ route('admin.jenjang-paket.index') }}" class="btnOutline">
                        <ion-icon name="arrow-back-outline"></ion-icon> Batal
                    </a>
                    <button type="submit" class="profileBtnPrimary">
                        <ion-icon name="checkmark-circle-outline"></ion-icon> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        @media (max-width: 640px) {
            .formResponsiveWrapper {
                padding: 16px 14px !important;
                margin-left: -8px;
                margin-right: -8px;
                border-radius: 14px !important;
            }
            .form-action-footer {
                flex-direction: column-reverse !important;
                gap: 10px !important;
                width: 100% !important;
            }
            .form-action-footer > button,
            .form-action-footer > a {
                width: 100% !important;
                min-height: 44px !important;
                justify-content: center !important;
            }
            .profileInput, .filterSelect {
                font-size: 14px !important;
            }
        }
    </style>
@endsection
