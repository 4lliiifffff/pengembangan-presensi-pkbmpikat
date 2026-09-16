@extends('layouts.admin')

@section('content')
<div class="laporanPageWrapper">
    <div class="laporanHeaderCard">
        <div class="laporanHeaderMain">
            <div class="laporanTitleBlock">
                <span class="laporanSubtitle">MODUL PESERTA DIDIK</span>
                <h1 class="laporanMainTitle">Tambah Siswa Baru</h1>
                <p class="laporanDesc">Daftarkan peserta didik baru ke dalam rombel dan paket belajar</p>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.siswa.index') }}" class="btnOutline">
                    Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="errorList" style="max-width:900px;margin:0 auto 18px;">
            <div style="font-weight:800;margin-bottom:8px;">Periksa input berikut:</div>
            <ul style="padding-left:20px;margin:0;line-height:1.5;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="laporanFilterCard" style="max-width:900px;margin:0 auto 32px;padding:24px 28px;">
        <form method="POST" action="{{ route('admin.siswa.store') }}">
            @csrf

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:18px;">
                <div>
                    <label class="filterFieldLabel" style="margin-bottom:6px;display:block;">NOMOR ABSEN / NIS <span style="color:#ef4444;">*</span></label>
                    <input class="profileInput" type="text" name="no_absen" value="{{ old('no_absen') }}" placeholder="Contoh: 101" required />
                </div>

                <div>
                    <label class="filterFieldLabel" style="margin-bottom:6px;display:block;">NAMA LENGKAP SISWA <span style="color:#ef4444;">*</span></label>
                    <input class="profileInput" type="text" name="nama_siswa" value="{{ old('nama_siswa') }}" placeholder="Contoh: Ahmad Dahlan" required />
                </div>

                <div>
                    <label class="filterFieldLabel" style="margin-bottom:6px;display:block;">STATUS SIKLUS MURID <span style="color:#ef4444;">*</span></label>
                    <select class="filterSelect" name="status_siswa" required>
                        <option value="aktif" {{ old('status_siswa', 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif Belajar</option>
                        <option value="alumni" {{ old('status_siswa') === 'alumni' ? 'selected' : '' }}>Lulus / Alumni</option>
                        <option value="cuti" {{ old('status_siswa') === 'cuti' ? 'selected' : '' }}>Cuti Belajar</option>
                        <option value="nonaktif" {{ old('status_siswa') === 'nonaktif' ? 'selected' : '' }}>Nonaktif / Keluar</option>
                    </select>
                </div>

                <div>
                    <label class="filterFieldLabel" style="margin-bottom:6px;display:block;">NO HP / WHATSAPP SISWA <span style="color:#ef4444;">*</span></label>
                    <input class="profileInput" type="text" name="no_hp" value="{{ old('no_hp') }}" placeholder="Contoh: 081234567890" required />
                </div>

                <div style="grid-column:1/-1;">
                    <label class="filterFieldLabel" style="margin-bottom:6px;display:block;">NAMA ORANG TUA / WALI <span style="color:#ef4444;">*</span></label>
                    <input class="profileInput" type="text" name="nama_wali" value="{{ old('nama_wali') }}" placeholder="Contoh: Bapak / Ibu Santoso" required />
                </div>

                <div>
                    <label class="filterFieldLabel" style="margin-bottom:6px;display:block;">KELAS / ROMBONGAN BELAJAR <span style="color:#ef4444;">*</span></label>
                    @if ($kelas->isEmpty())
                        <div class="profileInput" style="font-size:12px;color:var(--muted);background:#f8fafc;">
                            Belum ada data kelas.
                            <a href="{{ route('admin.kelas.create') }}" class="link-primary" style="font-weight:700;">Tambah kelas</a>.
                        </div>
                    @endif
                    <select class="filterSelect" name="kelas_id" required onchange="if(this.value === 'tambah_kelas') { window.location.href = '{{ route('admin.kelas.create') }}'; }">
                        <option value="">-- Pilih Kelas / Rombel --</option>
                        <option value="tambah_kelas" class="link-primary">+ Tambah Kelas Baru</option>
                        @php
                            $groupedKelas = $kelas->groupBy(fn($k) => $k->jenjang_paket_label);
                        @endphp
                        @foreach ($groupedKelas as $jenjangLabel => $kelasList)
                            <optgroup label="{{ $jenjangLabel }}">
                                @foreach ($kelasList as $k)
                                    <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="filterFieldLabel" style="margin-bottom:6px;display:block;">TUTOR PEMBIMBING (OPSIONAL)</label>
                    <select class="filterSelect" name="tutor_id">
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
            </div>

            <div style="margin-top:20px;">
                <label style="display:flex;align-items:center;gap:12px;cursor:pointer;font-size:13.5px;font-weight:700;color:var(--text);padding:12px 16px;background:var(--card-alt,#f8fafc);border-radius:10px;border:1px solid var(--border,#e2e8f0);">
                    <input type="checkbox" name="is_abk" value="1" {{ old('is_abk') ? 'checked' : '' }} style="width:18px;height:18px;">
                    <span>Siswa Berkebutuhan Khusus (ABK)</span>
                </label>
                <div style="font-size:11.5px;color:var(--muted);margin-top:6px;line-height:1.4;">Centang jika siswa ini merupakan Anak Berkebutuhan Khusus (tarif honor tutor otomatis disesuaikan SK ABK).</div>
            </div>

            <div style="background:rgba(31,59,138,0.04);border:1px solid rgba(31,59,138,0.15);border-radius:10px;padding:14px 16px;margin-top:18px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;flex-wrap:wrap;gap:8px;">
                    <div style="font-size:12.5px;font-weight:800;color:#1f3b8a;">
                        Skema Tarif Honor Tutor
                    </div>
                    <a href="{{ route('admin.kategori-tutorial.index') }}" target="_blank" style="font-size:12px;font-weight:700;color:#1f3b8a;text-decoration:underline;">
                        Kelola Master SK &rarr;
                    </a>
                </div>
                <div style="font-size:12px;color:var(--muted);line-height:1.5;">
                    Tarif honor sesi mengajar tutor dihitung secara otomatis dari Master Tarif SK berdasarkan status ABK / Reguler dan durasi tutorial.
                </div>
            </div>

            <div style="margin-top:24px;padding-top:18px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                <a href="{{ route('admin.siswa.index') }}" class="btnOutline">
                    Batal
                </a>
                <button type="submit" class="profileBtnPrimary">
                    Simpan Data Siswa
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

