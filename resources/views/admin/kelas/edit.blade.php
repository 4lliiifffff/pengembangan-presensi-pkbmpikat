@extends('layouts.admin')

@section('content')
    <div class="pageHeaderRow">
        <div>
            <h2 style="margin:0;">Edit Data Kelas</h2>
            <p style="margin:2px 0 0;font-size:12px;color:var(--muted);">Perbarui informasi jenjang paket, tingkatan, dan nama kelas</p>
        </div>
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
        <form method="POST" action="{{ route('admin.kelas.update', $kelas) }}" id="formKelas">
            @csrf
            @method('PUT')

            {{-- Info Siswa Terkait --}}
            <div class="formRow" style="background:rgba(31,59,138,0.04);border:1px solid rgba(31,59,138,0.15);border-radius:12px;padding:12px 14px;margin-bottom:18px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <div style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:800;color:#1f3b8a;">
                        <ion-icon name="people-outline" style="font-size:16px;"></ion-icon>
                        Total Siswa Terdaftar: {{ $kelas->siswas()->count() }} Murid
                    </div>
                    <span style="font-size:11px;font-weight:700;color:#1f3b8a;background:#e0e7ff;padding:2px 8px;border-radius:6px;">
                        {{ $kelas->jenjang_paket_label }}
                    </span>
                </div>
                <div style="font-size:11.5px;color:var(--muted);line-height:1.4;">
                    Perubahan pada <b>Jenjang Paket</b> akan langsung memengaruhi pengelompokan presensi tutor dan deteksi multi-rombel sesi gabungan komunitas.
                </div>
            </div>

            <div class="formRow">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <div class="fieldLabel" style="margin:0;">Jenjang / Kategori Paket <span style="color:#ef4444;">*</span></div>
                    <a href="{{ route('admin.jenjang-paket.index') }}" target="_blank" style="font-size:11px;font-weight:700;color:#2563eb;text-decoration:none;display:inline-flex;align-items:center;gap:2px;">
                        <ion-icon name="add-circle-outline"></ion-icon> Kelola Jenjang
                    </a>
                </div>
                <select class="input" name="jenjang_paket_id" id="jenjang_paket_id" required>
                    <option value="">-- Pilih Jenjang / Program Paket --</option>
                    @foreach ($jenjangPakets as $jp)
                        <option value="{{ $jp->id }}" data-kode="{{ $jp->kode }}" data-label="{{ $jp->nama_jenjang }}" data-tingkat="{{ $jp->tingkat_label }}" {{ (string) old('jenjang_paket_id', $kelas->jenjang_paket_id) === (string) $jp->id ? 'selected' : '' }}>
                            {{ $jp->nama_jenjang }} @if($jp->tingkat_label) ({{ $jp->tingkat_label }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="formRow">
                <div class="fieldLabel">Tingkat / Nomor Kelas</div>
                <input class="input" type="text" name="tingkat" id="tingkat" value="{{ old('tingkat', $kelas->tingkat) }}" placeholder="Contoh: 1-6 (SD), 7-9 (SMP), 10-12 (SMA), atau Dasar/Terampil" />
                <div style="font-size:11px;color:var(--muted);margin-top:4px;">Nomor tingkatan kelas atau level pembelajaran.</div>
            </div>

            <div class="formRow">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <div class="fieldLabel" style="margin:0;">Nama Kelas <span style="color:#ef4444;">*</span></div>
                    <button type="button" onclick="generateAutoNama()" style="background:none;border:none;color:#2563eb;font-size:11px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:4px;">
                        <ion-icon name="flash-outline"></ion-icon> Format Otomatis
                    </button>
                </div>
                <input class="input" type="text" name="nama_kelas" id="nama_kelas" value="{{ old('nama_kelas', $kelas->nama_kelas) }}" placeholder="Contoh: Paket B - Kelas 7" required />
            </div>

            <div class="formRow">
                <div class="fieldLabel">Keterangan / Catatan Rombel</div>
                <textarea class="input" name="keterangan" rows="2" style="height:auto;padding:8px 12px;" placeholder="Catatan tambahan rombongan belajar (opsional)">{{ old('keterangan', $kelas->keterangan) }}</textarea>
            </div>

            <button type="submit" class="btnPrimary" style="width:100%;justify-content:center;margin-top:8px;">
                <ion-icon name="save-outline"></ion-icon>
                Simpan Perubahan
            </button>
        </form>
    </div>

    <script>
        function generateAutoNama() {
            const select = document.getElementById('jenjang_paket_id');
            const tingkat = document.getElementById('tingkat').value.trim();
            const namaField = document.getElementById('nama_kelas');

            if (!select.value) return;

            const selectedOpt = select.options[select.selectedIndex];
            let rawLabel = selectedOpt.getAttribute('data-label') || select.value;
            let prefix = rawLabel.split('(')[0].trim();

            if (tingkat) {
                if (/^\d+$/.test(tingkat)) {
                    namaField.value = prefix + ' - Kelas ' + tingkat;
                } else {
                    namaField.value = prefix + ' - ' + tingkat;
                }
            } else {
                namaField.value = prefix;
            }
        }
    </script>
@endsection
