@extends('layouts.admin')

@section('title', 'Edit Data Kelas: ' . $kelas->nama_kelas . ' - Admin')

@section('content')
    <div class="laporanPageWrapper">
        {{-- ── Header Card ── --}}
        <div class="laporanHeader">
            <div class="laporanHeaderCard">
                <div class="laporanHeaderInfo">
                    <div class="laporanHeaderLabel">PERBARUI KELAS &amp; ROMBEL</div>
                    <h1 class="laporanHeaderTitle">Edit Data Kelas: {{ $kelas->nama_kelas }}</h1>
                    <div class="laporanHeaderSub">Perbarui alokasi jenjang paket, tingkatan belajar, dan nama rombel</div>
                </div>
                <div class="laporanHeaderActions">
                    <a class="btnOutline" href="{{ route('admin.kelas.index') }}">
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
            {{-- Info Siswa Terkait --}}
            <div class="mb-4 p-3 sm:p-4 rounded-xl border-base bg-card-alt flex items-start gap-2.5 sm:gap-3">
                <div class="text-primary text-xl flex-shrink-0 mt-0.5">
                    <ion-icon name="people-outline"></ion-icon>
                </div>
                <div class="text-xs sm:text-sm w-full">
                    <div class="font-bold text-dark flex flex-wrap items-center justify-between gap-1.5">
                        <span>Total Siswa Terdaftar: <strong>{{ $kelas->siswas()->count() }} Murid</strong></span>
                        <span class="app-badge badge-jenjang-{{ $kelas->jenjang_paket }}">
                            {{ $kelas->jenjang_paket_label }}
                        </span>
                    </div>
                    <div class="text-muted mt-1 leading-relaxed">
                        Perubahan pada <strong>Jenjang Paket</strong> akan otomatis menyelaraskan pengelompokan jadwal sesi tutorial dan validasi presensi murid.
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.kelas.update', $kelas) }}" id="formKelas">
                @csrf
                @method('PUT')

                <div class="form-grid-responsive">
                    {{-- Jenjang / Kategori Paket --}}
                    <div class="form-field-wrapper">
                        <div class="flex-between flex-wrap gap-1 mb-1">
                            <label class="filterFieldLabel mb-0" for="jenjang_paket_id">
                                Jenjang / Program Paket <span class="text-danger">*</span>
                            </label>
                            <a href="{{ route('admin.jenjang-paket.index') }}" target="_blank"
                                class="text-xs font-bold text-primary text-no-decor d-inline-flex items-center gap-1 hover:underline">
                                <ion-icon name="layers-outline"></ion-icon> Kelola Jenjang
                            </a>
                        </div>
                        <select class="filterSelect text-md w-full" name="jenjang_paket_id" id="jenjang_paket_id" required>
                            <option value="">-- Pilih Jenjang / Program Paket --</option>
                            @foreach ($jenjangPakets as $jp)
                                <option value="{{ $jp->id }}" data-kode="{{ $jp->kode }}" data-label="{{ $jp->nama_jenjang }}"
                                    data-tingkat="{{ $jp->tingkat_label }}"
                                    {{ (string) old('jenjang_paket_id', $kelas->jenjang_paket_id) === (string) $jp->id ? 'selected' : '' }}>
                                    {{ $jp->nama_jenjang }} @if ($jp->tingkat_label) ({{ $jp->tingkat_label }}) @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="text-xs text-muted mt-1">Kurikulum kesetaraan induk rombel.</div>
                    </div>

                    {{-- Tingkat / Nomor Kelas --}}
                    <div class="form-field-wrapper">
                        <label class="filterFieldLabel" for="tingkat">
                            Tingkat / Nomor Kelas
                        </label>
                        <input class="profileInput text-md w-full" type="text" name="tingkat" id="tingkat"
                            value="{{ old('tingkat', $kelas->tingkat) }}"
                            placeholder="Contoh: 1, 7, 10, atau Dasar" />
                        <div class="text-xs text-muted mt-1">Nomor tingkatan kelas atau level pembelajaran.</div>
                    </div>

                    {{-- Nama Kelas --}}
                    <div class="form-field-wrapper form-grid-full">
                        <div class="flex-between flex-wrap gap-1 mb-1">
                            <label class="filterFieldLabel mb-0" for="nama_kelas">
                                Nama Resmi Kelas / Rombel <span class="text-danger">*</span>
                            </label>
                            <button type="button" onclick="generateAutoNama()"
                                class="bg-none border-none text-primary text-xs font-bold cursor-pointer d-flex items-center gap-1 hover:underline">
                                <ion-icon name="flash-outline"></ion-icon> Format Otomatis
                            </button>
                        </div>
                        <input class="profileInput text-md w-full" type="text" name="nama_kelas" id="nama_kelas"
                            value="{{ old('nama_kelas', $kelas->nama_kelas) }}"
                            placeholder="Contoh: Paket B - Kelas 7" required />
                        <div class="text-xs text-muted mt-1">Nama kelas yang tampil pada daftar presensi, kartu belajar, dan raport.</div>
                    </div>

                    {{-- Keterangan / Catatan Rombel --}}
                    <div class="form-field-wrapper form-grid-full">
                        <label class="filterFieldLabel" for="keterangan">
                            Keterangan / Catatan Rombel
                        </label>
                        <textarea name="keterangan" id="keterangan" rows="2"
                            placeholder="Catatan tambahan rombongan belajar (opsional)..."
                            class="profileInput text-md h-auto p-3 w-full">{{ old('keterangan', $kelas->keterangan) }}</textarea>
                    </div>
                </div>

                <div class="form-action-footer">
                    <a href="{{ route('admin.kelas.index') }}" class="btnOutline">
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

    <script>
        function generateAutoNama() {
            const select = document.getElementById('jenjang_paket_id');
            const selectedOpt = select.options[select.selectedIndex];
            const tingkatVal = document.getElementById('tingkat').value.trim();
            const namaInput = document.getElementById('nama_kelas');

            if (!selectedOpt || !selectedOpt.value) return;

            let jenjangLabel = selectedOpt.dataset.label || selectedOpt.text;
            jenjangLabel = jenjangLabel.replace(/\s*\(.*?\)\s*/g, '').trim();

            if (tingkatVal) {
                if (tingkatVal.toLowerCase().startsWith('kelas') || tingkatVal.toLowerCase().startsWith('tingkat')) {
                    namaInput.value = jenjangLabel + ' - ' + tingkatVal;
                } else {
                    namaInput.value = jenjangLabel + ' - Kelas ' + tingkatVal;
                }
            } else {
                namaInput.value = jenjangLabel;
            }
        }
    </script>
@endsection
