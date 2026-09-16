@extends('layouts.admin')

@section('content')
    <div class="pageHeaderRow">
        <div>
            <h2 style="margin:0;">Edit Master Jenjang: {{ $jenjangPaket->nama_jenjang }}</h2>
            <p style="margin:2px 0 0;font-size:12px;color:var(--muted);">Perbarui informasi jenjang paket, kode sistem, dan format tingkatan</p>
        </div>
        <a class="btnOutline" href="{{ route('admin.jenjang-paket.index') }}">
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
        <form method="POST" action="{{ route('admin.jenjang-paket.update', $jenjangPaket) }}">
            @csrf
            @method('PUT')

            {{-- Info Rombel / Kelas Terkait --}}
            <div class="formRow" style="background:rgba(31,59,138,0.04);border:1px solid rgba(31,59,138,0.15);border-radius:12px;padding:12px 14px;margin-bottom:18px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:800;color:#1f3b8a;">
                        <ion-icon name="layers-outline" style="font-size:16px;"></ion-icon>
                        Total Rombel / Kelas Menggunakan Jenjang Ini: {{ $jenjangPaket->kelas_count }} Kelas
                    </div>
                    <code style="font-size:11px;font-weight:800;background:#e0e7ff;color:#3730a3;padding:2px 8px;border-radius:6px;">
                        Kode: {{ $jenjangPaket->kode }}
                    </code>
                </div>
            </div>

            <div class="formRow">
                <div class="fieldLabel">Nama Jenjang / Program <span style="color:#ef4444;">*</span></div>
                <input class="input" type="text" name="nama_jenjang" value="{{ old('nama_jenjang', $jenjangPaket->nama_jenjang) }}" placeholder="Contoh: Paket A (Setara SD)" required />
            </div>

            <div class="formRow">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div class="fieldLabel" style="margin:0;">Kode Sistem (Unique ID) <span style="color:#ef4444;">*</span></div>
                    <span style="font-size:10.5px;color:var(--muted);">Harap berhati-hati jika mengubah kode yang sudah dipakai oleh data kelas</span>
                </div>
                <input class="input" type="text" name="kode" value="{{ old('kode', $jenjangPaket->kode) }}" required />
            </div>

            <div class="formRow">
                <div class="fieldLabel">Format / Rentang Tingkat</div>
                <input class="input" type="text" name="tingkat_label" value="{{ old('tingkat_label', $jenjangPaket->tingkat_label) }}" placeholder="Contoh: Kelas 1 - 6, Tingkat 7 - 9, atau Dasar / Terampil" />
                <div style="font-size:11px;color:var(--muted);margin-top:4px;">Panduan tingkatan nomor kelas bagi admin saat membuat rombel.</div>
            </div>

            <div class="formRow">
                <div class="fieldLabel">Urutan Tampilan</div>
                <input class="input" type="number" name="urutan" value="{{ old('urutan', $jenjangPaket->urutan) }}" />
            </div>

            <div class="formRow">
                <div class="fieldLabel">Keterangan / Deskripsi Program</div>
                <textarea class="input" name="keterangan" rows="2" style="height:auto;padding:8px 12px;" placeholder="Deskripsi singkat tentang kurikulum atau sasaran program kesetaraan/vokasi">{{ old('keterangan', $jenjangPaket->keterangan) }}</textarea>
            </div>

            <div class="formRow">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;font-weight:700;color:var(--text);">
                    <input type="checkbox" name="is_aktif" value="1" {{ old('is_aktif', $jenjangPaket->is_aktif) ? 'checked' : '' }} style="width:16px;height:16px;" />
                    Status Aktif (dapat dipilih di form kelas)
                </label>
            </div>

            <button type="submit" class="btnPrimary" style="width:100%;justify-content:center;margin-top:8px;">
                <ion-icon name="save-outline"></ion-icon>
                Simpan Perubahan
            </button>
        </form>
    </div>
@endsection
