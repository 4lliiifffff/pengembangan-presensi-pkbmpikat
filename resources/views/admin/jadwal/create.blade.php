@extends('layout.admin')

@section('title', 'Tambah Agenda — Admin')



@section('content')

    <div class="formPageHeader">
        <a href="{{ route('admin.jadwal.index') }}" class="backBtn">
            <ion-icon name="arrow-back-outline"></ion-icon>
        </a>
        <div class="formPageTitle">Tambah Agenda</div>
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

        <form method="POST" action="{{ route('admin.jadwal.store') }}">
            @csrf

            {{-- Judul --}}
            <div class="formGroup">
                <label class="formLabel">Judul Agenda</label>
                <input type="text" name="judul" class="formControl"
                       value="{{ old('judul') }}" placeholder="Contoh: Rapat Wali Murid" required>
                @error('judul')
                    <span class="errorMsg">{{ $message }}</span>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="formGroup">
                <label class="formLabel">Deskripsi <span style="font-weight:400;text-transform:none;">(opsional)</span></label>
                <textarea name="deskripsi" class="formControl"
                          placeholder="Keterangan tambahan tentang agenda ini...">{{ old('deskripsi') }}</textarea>
                @error('deskripsi')
                    <span class="errorMsg">{{ $message }}</span>
                @enderror
            </div>

            {{-- Tanggal --}}
            <div class="formGroup">
                <label class="formLabel">Tanggal</label>
                <input type="date" name="tanggal" class="formControl"
                       value="{{ old('tanggal', date('Y-m-d')) }}" required>
                @error('tanggal')
                    <span class="errorMsg">{{ $message }}</span>
                @enderror
            </div>

            {{-- Lokasi --}}
            <div class="formGroup">
                <label class="formLabel">Lokasi <span style="font-weight:400;text-transform:none;">(opsional)</span></label>
                <input type="text" name="lokasi" class="formControl"
                       value="{{ old('lokasi') }}" placeholder="Contoh: Ruang Aula, Online, dll.">
                @error('lokasi')
                    <span class="errorMsg">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="submitBtn">Simpan Agenda</button>
        </form>
    </div>

@endsection
