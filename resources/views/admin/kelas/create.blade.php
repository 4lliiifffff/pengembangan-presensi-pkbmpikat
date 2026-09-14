@extends('layout.admin')

@section('content')
    <div class="pageHeaderRow">
        <h2>Tambah Kelas</h2>
        <a class="btnOutline" href="{{ route('admin.kelas.index') }}">
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="errorList">
            <div style="font-weight:1000;margin-bottom:6px;">Periksa input berikut:</div>
            <ul style="padding-left:18px;margin:0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="formCard">
        <form method="POST" action="{{ route('admin.kelas.store') }}">
            @csrf
            <div class="formRow">
                <div class="fieldLabel">Nama Kelas</div>
                <input class="input" type="text" name="nama_kelas" value="{{ old('nama_kelas') }}" required />
            </div>

            <button type="submit" class="btnPrimary" style="width:100%;justify-content:center;">
                <ion-icon name="save-outline"></ion-icon>
                Simpan
            </button>
        </form>
    </div>
@endsection
