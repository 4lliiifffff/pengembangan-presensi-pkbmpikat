@extends('layouts.admin')

@section('content')
    <div class="pageHeaderRow">
        <h2>Tambah Siswa</h2>
        <a class="btnOutline" href="{{ route('admin.siswa.index') }}">
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
        <form method="POST" action="{{ route('admin.siswa.store') }}">
            @csrf

            <div class="formRow">
                <div class="fieldLabel">No Absen</div>
                <input class="input" type="text" name="no_absen" value="{{ old('no_absen') }}" required />
            </div>

            <div class="formRow">
                <div class="fieldLabel">Nama Siswa</div>
                <input class="input" type="text" name="nama_siswa" value="{{ old('nama_siswa') }}" required />
            </div>



            <div class="formRow">
                <div class="fieldLabel">No HP</div>
                <input class="input" type="text" name="no_hp" value="{{ old('no_hp') }}" required />
            </div>

            <div class="formRow">
                <div class="fieldLabel">Nama Wali</div>
                <input class="input" type="text" name="nama_wali" value="{{ old('nama_wali') }}" required />
            </div>

            <div class="formRow">
                <div class="fieldLabel">Tarif Honor Per Jam (Rp)</div>
                <input class="input" type="number" step="1000" name="tarif_per_jam" value="{{ old('tarif_per_jam', 50000) }}" required placeholder="Contoh: 50000" />
            </div>

            <div class="formRow">
                <div class="fieldLabel">Kelas</div>
                @if ($kelas->isEmpty())
                    <div class="input" style="border: 1px solid #ccc; ">
                        Belum ada data kelas.
                        <a href="{{ route('admin.kelas.create') }}" style="font-weight:900;color:#1d4ed8;text-decoration:underline;">Tambah kelas sekarang</a>.
                    </div>
                @endif
                <select class="input" name="kelas_id" required onchange="if(this.value === 'tambah_kelas') { window.location.href = '{{ route('admin.kelas.create') }}'; }">
                    <option value="">-- Pilih Kelas --</option>
                    <option value="tambah_kelas" style="font-weight: bold; color: #1d4ed8;">+ Tambah Kelas</option>
                    @foreach ($kelas as $k)
                        <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="formRow">
                <div class="fieldLabel">Tutor (Opsional)</div>
                <select class="input" name="tutor_id">
                    <option value="">-- Tidak Ada / Belum Ditentukan --</option>
                    @if (isset($tutors))
                        @foreach ($tutors as $tutor)
                            <option value="{{ $tutor->id }}" {{ old('tutor_id') == $tutor->id ? 'selected' : '' }}>
                                {{ $tutor->nama_lengkap }}@if(!empty($tutor->jabatan) && stripos($tutor->jabatan, 'kepala') !== false) (Kepala Sekolah)@endif
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <button type="submit" class="btnPrimary" style="width:100%;justify-content:center;">
                <ion-icon name="save-outline"></ion-icon>
                Simpan
            </button>
        </form>
    </div>
@endsection

