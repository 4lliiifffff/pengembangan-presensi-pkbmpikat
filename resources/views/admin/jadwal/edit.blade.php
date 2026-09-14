@extends('layout.admin')

@section('title', 'Edit Agenda — Admin')

<style>
    .formPage {
        padding: 0 16px 120px;
    }

    .formPageHeader {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 16px 4px;
    }

    .backBtn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(37,99,235,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: #2563eb;
        flex-shrink: 0;
    }

    .backBtn ion-icon { font-size: 20px; }

    .formPageTitle {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }

    .formGroup {
        margin-bottom: 16px;
    }

    .formLabel {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--muted);
        letter-spacing: 0.5px;
        margin-bottom: 7px;
        text-transform: uppercase;
    }

    .formControl {
        width: 100%;
        padding: 13px 14px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
        font-size: 14px;
        color: #0f172a;
        box-sizing: border-box;
        appearance: none;
        -webkit-appearance: none;
        font-family: inherit;
    }

    .formControl:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }

    textarea.formControl {
        resize: vertical;
        min-height: 90px;
    }

    .errorMsg {
        font-size: 11px;
        color: #ef4444;
        margin-top: 5px;
        display: block;
    }

    .submitBtn {
        width: 100%;
        padding: 15px;
        border-radius: 14px;
        border: none;
        background: #1a3a5c;
        color: #fff;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        margin-top: 8px;
        font-family: inherit;
        box-shadow: 0 4px 14px rgba(26,58,92,0.3);
    }

    .submitBtn:active { opacity: 0.9; }

    .deleteBtn {
        width: 100%;
        padding: 14px;
        border-radius: 14px;
        border: 1px solid rgba(239,68,68,0.25);
        background: rgba(239,68,68,0.08);
        color: #dc2626;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        margin-top: 10px;
        font-family: inherit;
    }

    .errorList {
        margin: 0 0 16px;
        padding: 12px 14px;
        background: rgba(239,68,68,0.08);
        border: 1px solid rgba(239,68,68,0.22);
        border-radius: 12px;
        font-size: 13px;
        color: #dc2626;
    }

    .errorList ul { margin: 6px 0 0; padding-left: 16px; }
    .errorList ul li { margin-bottom: 3px; }
</style>

@section('content')

    <div class="formPageHeader">
        <a href="{{ route('admin.jadwal.index', ['tanggal' => $jadwal->tanggal]) }}" class="backBtn">
            <ion-icon name="arrow-back-outline"></ion-icon>
        </a>
        <div class="formPageTitle">Edit Agenda</div>
    </div>

    <div class="formPage">

        @if($errors->any())
            <div class="errorList">
                <strong>Terdapat kesalahan:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.jadwal.update', $jadwal) }}">
            @csrf
            @method('PUT')

            {{-- Judul --}}
            <div class="formGroup">
                <label class="formLabel">Judul Agenda</label>
                <input type="text" name="judul" class="formControl"
                       value="{{ old('judul', $jadwal->judul) }}" placeholder="Contoh: Rapat Wali Murid" required>
                @error('judul')
                    <span class="errorMsg">{{ $message }}</span>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="formGroup">
                <label class="formLabel">Deskripsi <span style="font-weight:400;text-transform:none;">(opsional)</span></label>
                <textarea name="deskripsi" class="formControl"
                          placeholder="Keterangan tambahan tentang agenda ini...">{{ old('deskripsi', $jadwal->deskripsi) }}</textarea>
                @error('deskripsi')
                    <span class="errorMsg">{{ $message }}</span>
                @enderror
            </div>

            {{-- Tanggal --}}
            <div class="formGroup">
                <label class="formLabel">Tanggal</label>
                <input type="date" name="tanggal" class="formControl"
                       value="{{ old('tanggal', $jadwal->tanggal) }}" required>
                @error('tanggal')
                    <span class="errorMsg">{{ $message }}</span>
                @enderror
            </div>

            {{-- Lokasi --}}
            <div class="formGroup">
                <label class="formLabel">Lokasi <span style="font-weight:400;text-transform:none;">(opsional)</span></label>
                <input type="text" name="lokasi" class="formControl"
                       value="{{ old('lokasi', $jadwal->lokasi) }}" placeholder="Contoh: Ruang Aula, Online, dll.">
                @error('lokasi')
                    <span class="errorMsg">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="submitBtn">Perbarui Agenda</button>
        </form>

        <form method="POST" action="{{ route('admin.jadwal.destroy', $jadwal) }}"
              onsubmit="return confirm('Yakin ingin menghapus agenda ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="deleteBtn">
                <ion-icon name="trash-outline" style="vertical-align:middle;margin-right:4px;"></ion-icon>
                Hapus Agenda
            </button>
        </form>
    </div>

@endsection
