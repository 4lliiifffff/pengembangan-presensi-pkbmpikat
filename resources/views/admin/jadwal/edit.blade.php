@extends('layouts.admin')

@section('title', 'Edit Agenda - Admin')

@section('content')

    <div class="laporanPageWrapper">
        {{-- ── Header Card ── --}}
        <div class="laporanHeader">
            <div class="laporanHeaderCard">
                <div class="laporanHeaderInfo">
                    <div class="laporanHeaderLabel">PERBARUI AGENDA</div>
                    <h1 class="laporanHeaderTitle">Edit Agenda</h1>
                    <div class="laporanHeaderSub">Perbarui informasi agenda atau kalender kegiatan</div>
                </div>
                <div class="laporanHeaderActions">
                    <a class="btnOutline" href="{{ route('admin.jadwal.index', ['tanggal' => $jadwal->tanggal]) }}">
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="error-list-container">
                <div class="error-title">Periksa input berikut:</div>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-card-container">
            <form method="POST" action="{{ route('admin.jadwal.update', $jadwal) }}">
                @csrf
                @method('PUT')

                {{-- Judul --}}
                <div class="form-field-wrapper">
                    <label class="form-field-label">Judul Agenda <span class="text-danger">*</span></label>
                    <input type="text" name="judul" class="profileInput" value="{{ old('judul', $jadwal->judul) }}"
                        placeholder="Contoh: Rapat Wali Murid" required>
                    @error('judul')
                        <div class="field-help-text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div class="form-field-wrapper">
                    <label class="form-field-label">Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" rows="3" placeholder="Keterangan tambahan tentang agenda ini..."
                        class="profileInput h-auto p-2">{{ old('deskripsi', $jadwal->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="field-help-text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tanggal --}}
                <div class="form-field-wrapper">
                    <label class="form-field-label">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal" class="profileInput" value="{{ old('tanggal', $jadwal->tanggal) }}"
                        required>
                    @error('tanggal')
                        <div class="field-help-text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Titik Lokasi Resmi (Opsional) --}}
                <div class="form-field-wrapper">
                    <label class="form-field-label">Titik Lokasi Resmi PKBM (Opsional)</label>
                    <select name="lokasi_presensi_id" class="filterSelect">
                        <option value="">-- Tanpa Titik Resmi / Lokasi Bebas Luar Sekolah --</option>
                        @foreach($lokasiPresensis as $lp)
                            <option value="{{ $lp->id }}" {{ old('lokasi_presensi_id', $jadwal->lokasi_presensi_id) == $lp->id ? 'selected' : '' }}>
                                {{ $lp->nama_lokasi }} ({{ ucfirst($lp->tipe) }} &bull; Radius {{ $lp->radius_meter }}m)
                            </option>
                        @endforeach
                    </select>
                    <div class="field-help-text">Jika dipilih, koordinat GPS resmi akan digunakan untuk panduan peta & rute
                        navigasi Google Maps.</div>
                    @error('lokasi_presensi_id')
                        <div class="field-help-text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Lokasi / Detail Ruangan --}}
                <div class="form-field-wrapper">
                    <label class="form-field-label">Keterangan Ruangan / Lokasi Kustom (Opsional)</label>
                    <input type="text" name="lokasi" class="profileInput" value="{{ old('lokasi', $jadwal->lokasi) }}"
                        placeholder="Contoh: Ruang Aula Lt. 2, Lapangan Utama, atau Museum Vredeburg">
                    <div class="field-help-text">Tuliskan spesifik ruangan jika memilih titik resmi, atau nama tempat/link
                        jika agenda di luar sekolah.</div>
                    @error('lokasi')
                        <div class="field-help-text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-action-footer">
                    <button type="submit" class="profileBtnPrimary w-auto px-4">
                        <ion-icon name="save-outline"></ion-icon>
                        Perbarui Agenda
                    </button>
                </div>
            </form>

            <div class="mt-4 d-flex justify-end pt-4 border-t-base">
                <form method="POST" action="{{ route('admin.jadwal.destroy', $jadwal) }}"
                    data-confirm="Apakah Anda yakin ingin menghapus agenda ini?" data-confirm-title="Hapus Agenda"
                    data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="profileBtnDanger cursor-pointer w-auto px-4">
                        <ion-icon name="trash-outline" class="align-middle mr-1"></ion-icon>
                        Hapus Agenda
                    </button>
                </form>
            </div>
        </div>
    </div>

@endsection