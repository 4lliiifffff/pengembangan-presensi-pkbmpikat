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
            <div class="errorList-title">Periksa input berikut:</div>
            <ul>
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
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;font-weight:700;color:var(--text);padding:10px 14px;background:var(--card-alt,#f8fafc);border-radius:10px;border:1px solid var(--border,#e2e8f0);">
                    <input type="checkbox" name="is_abk" value="1" {{ old('is_abk') ? 'checked' : '' }} style="width:18px;height:18px;">
                    <span>Siswa Berkebutuhan Khusus (ABK)</span>
                </label>
                <div style="font-size:11px;color:var(--muted);margin-top:4px;">Centang jika siswa ini merupakan Anak Berkebutuhan Khusus (tarif honor tutor otomatis disesuaikan SK ABK).</div>
            </div>



            <div class="formRow">
                <div class="fieldLabel">No HP</div>
                <input class="input" type="text" name="no_hp" value="{{ old('no_hp') }}" required />
            </div>

            <div class="formRow">
                <div class="fieldLabel">Nama Wali</div>
                <input class="input" type="text" name="nama_wali" value="{{ old('nama_wali') }}" required />
            </div>

            <div class="formRow" style="background:rgba(31,59,138,0.04);border:1px solid rgba(31,59,138,0.15);border-radius:12px;padding:12px 14px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <div style="font-size:12px;font-weight:800;color:#1f3b8a;display:flex;align-items:center;gap:6px;">
                        <ion-icon name="cash-outline" style="font-size:16px;"></ion-icon>
                        Skema Tarif Honor Tutor
                    </div>
                    <a href="{{ route('admin.kategori-tutorial.index') }}" target="_blank" style="font-size:11px;font-weight:700;color:#1f3b8a;text-decoration:underline;">
                        Kelola Master SK &rarr;
                    </a>
                </div>
                <div style="font-size:11px;color:var(--muted);line-height:1.4;">
                    Tarif honor sesi mengajar tutor dihitung <b>secara otomatis dari Master Tarif SK</b> berdasarkan status <b>ABK / Reguler</b> dan durasi sesi tutorial yang dipilih saat presensi.
                </div>
            </div>

            <div class="formRow">
                <div class="fieldLabel">Kelas</div>
                @if ($kelas->isEmpty())
                    <div class="input">
                        Belum ada data kelas.
                        <a href="{{ route('admin.kelas.create') }}" class="link-primary">Tambah kelas sekarang</a>.
                    </div>
                @endif
                <select class="input" name="kelas_id" required onchange="if(this.value === 'tambah_kelas') { window.location.href = '{{ route('admin.kelas.create') }}'; }">
                    <option value="">-- Pilih Kelas --</option>
                    <option value="tambah_kelas" class="link-primary">+ Tambah Kelas</option>
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

            <button type="submit" class="btnPrimary btnPrimary--full">
                <ion-icon name="save-outline"></ion-icon>
                Simpan
            </button>
        </form>
    </div>
@endsection

