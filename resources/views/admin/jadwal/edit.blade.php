@extends('layouts.admin')

@section('title', 'Edit Agenda — Admin')

@section('content')

    <div class="pageHeaderRow">
        <div>
            <h2 style="margin:0;">Edit Agenda</h2>
            <p style="margin:2px 0 0;font-size:12px;color:var(--muted);">Perbarui informasi agenda atau kalender kegiatan</p>
        </div>
        <a class="btnOutline" href="{{ route('admin.jadwal.index', ['tanggal' => $jadwal->tanggal]) }}">
            Kembali
        </a>
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
                <label class="form-field-label">Judul Agenda <span style="color:#ef4444;">*</span></label>
                <input type="text" name="judul" class="profileInput"
                       value="{{ old('judul', $jadwal->judul) }}" placeholder="Contoh: Rapat Wali Murid" required>
                @error('judul')
                    <div class="field-help-text" style="color:#dc2626;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="form-field-wrapper">
                <label class="form-field-label">Deskripsi (Opsional)</label>
                <textarea name="deskripsi" class="profileInput" rows="3" style="height:auto;padding:10px 14px;"
                          placeholder="Keterangan tambahan tentang agenda ini...">{{ old('deskripsi', $jadwal->deskripsi) }}</textarea>
                @error('deskripsi')
                    <div class="field-help-text" style="color:#dc2626;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tanggal --}}
            <div class="form-field-wrapper">
                <label class="form-field-label">Tanggal <span style="color:#ef4444;">*</span></label>
                <input type="date" name="tanggal" class="profileInput"
                       value="{{ old('tanggal', $jadwal->tanggal) }}" required>
                @error('tanggal')
                    <div class="field-help-text" style="color:#dc2626;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Lokasi --}}
            <div class="form-field-wrapper">
                <label class="form-field-label">Lokasi (Opsional)</label>
                <input type="text" name="lokasi" class="profileInput"
                       value="{{ old('lokasi', $jadwal->lokasi) }}" placeholder="Contoh: Ruang Aula, Online, dll.">
                @error('lokasi')
                    <div class="field-help-text" style="color:#dc2626;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-action-footer">
                <button type="submit" class="profileBtnPrimary" style="width: auto; padding: 0 20px;">
                    <ion-icon name="save-outline"></ion-icon>
                    Perbarui Agenda
                </button>
            </div>
        </form>

        <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end;">
            <form method="POST" action="{{ route('admin.jadwal.destroy', $jadwal) }}"
                  data-confirm="Apakah Anda yakin ingin menghapus agenda ini?"
                  data-confirm-title="Hapus Agenda"
                  data-confirm-type="danger"
                  data-confirm-btn="Ya, Hapus" style="display:inline; margin:0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="profileBtnDanger cursor-pointer" style="width: auto; padding: 0 16px; height: 38px;">
                    <ion-icon name="trash-outline" style="vertical-align:middle;margin-right:4px;"></ion-icon>
                    Hapus Agenda
                </button>
            </form>
        </div>
    </div>

@endsection
