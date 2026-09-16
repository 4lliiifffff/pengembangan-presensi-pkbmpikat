@extends('layouts.admin')

@section('title', 'Tambah Agenda — Admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">FORMULIR AGENDA</div>
                <h1 class="laporanHeaderTitle">Tambah Agenda Baru</h1>
                <div class="laporanHeaderSub">Tambahkan kalender akademik, ujian, atau kegiatan sekolah</div>
            </div>
            <div class="laporanHeaderActions">
                <a class="btnOutline" href="{{ route('admin.jadwal.index') }}">
                    Kembali
                </a>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="error-list-container">
            <div class="error-title">Periksa input berikut:</div>
            <ul >
                @foreach($errors->all() as $error)
                    <li >{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-card-container">
        <form method="POST" action="{{ route('admin.jadwal.store') }}">
            @csrf

            {{-- Judul --}}
            <div class="form-field-wrapper">
                <label class="form-field-label">Judul Agenda <span class="text-danger">*</span></label>
                <input type="text" name="judul" class="profileInput" value="{{ old('judul') }}" placeholder="Contoh: Rapat Wali Murid" required>
                @error('judul')
                    <div class="field-help-text text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="form-field-wrapper">
                <label class="form-field-label">Deskripsi (Opsional)</label>
                <textarea name="deskripsi" rows="3" placeholder="Keterangan tambahan tentang agenda ini..." class="profileInput h-auto p-2">{{ old('deskripsi') }}</textarea>
                @error('deskripsi')
                    <div class="field-help-text text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tanggal --}}
            <div class="form-field-wrapper">
                <label class="form-field-label">Tanggal <span class="text-danger">*</span></label>
                <input type="date" name="tanggal" class="profileInput" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                @error('tanggal')
                    <div class="field-help-text text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Lokasi --}}
            <div class="form-field-wrapper">
                <label class="form-field-label">Lokasi (Opsional)</label>
                <input type="text" name="lokasi" class="profileInput" value="{{ old('lokasi') }}" placeholder="Contoh: Ruang Aula, Online, dll.">
                @error('lokasi')
                    <div class="field-help-text text-danger">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="profileBtnPrimary mt-3">
                <ion-icon name="save-outline"></ion-icon>
                Simpan Agenda
            </button>
        </form>
    </div>
</div>

@endsection
