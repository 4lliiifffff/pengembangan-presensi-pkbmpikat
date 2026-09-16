@extends('layouts.admin')

@section('title', 'Edit Agenda — Admin')

@section('content')

    <div class="pageHeaderRow">
        <div >
            <h2 class="mb-0 mt-0">Edit Agenda</h2>
            <p class="mt-1 text-sm text-muted">Perbarui informasi agenda atau kalender kegiatan</p>
        </div>
        <a class="btnOutline" href="{{ route('admin.jadwal.index', ['tanggal' => $jadwal->tanggal]) }}">
            Kembali
        </a>
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
        <form method="POST" action="{{ route('admin.jadwal.update', $jadwal) }}">
            @csrf
            @method('PUT')

            {{-- Judul --}}
            <div class="form-field-wrapper">
                <label class="form-field-label">Judul Agenda <span class="text-danger">*</span></label>
                <input type="text" name="judul" class="profileInput" value="{{ old('judul', $jadwal->judul) }}" placeholder="Contoh: Rapat Wali Murid" required>
                @error('judul')
                    <div class="field-help-text text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="form-field-wrapper">
                <label class="form-field-label">Deskripsi (Opsional)</label>
                <textarea name="deskripsi" rows="3" placeholder="Keterangan tambahan tentang agenda ini..." class="profileInput h-auto p-2">{{ old('deskripsi', $jadwal->deskripsi) }}</textarea>
                @error('deskripsi')
                    <div class="field-help-text text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tanggal --}}
            <div class="form-field-wrapper">
                <label class="form-field-label">Tanggal <span class="text-danger">*</span></label>
                <input type="date" name="tanggal" class="profileInput" value="{{ old('tanggal', $jadwal->tanggal) }}" required>
                @error('tanggal')
                    <div class="field-help-text text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Lokasi --}}
            <div class="form-field-wrapper">
                <label class="form-field-label">Lokasi (Opsional)</label>
                <input type="text" name="lokasi" class="profileInput" value="{{ old('lokasi', $jadwal->lokasi) }}" placeholder="Contoh: Ruang Aula, Online, dll.">
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
            <form method="POST" action="{{ route('admin.jadwal.destroy', $jadwal) }}" data-confirm="Apakah Anda yakin ingin menghapus agenda ini?" data-confirm-title="Hapus Agenda" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline m-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="profileBtnDanger cursor-pointer w-auto px-4">
                    <ion-icon name="trash-outline" class="align-middle mr-1"></ion-icon>
                    Hapus Agenda
                </button>
            </form>
        </div>
    </div>

@endsection
