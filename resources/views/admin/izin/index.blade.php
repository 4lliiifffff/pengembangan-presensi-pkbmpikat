@extends('layouts.admin')

@section('title', 'Kelola Izin — Admin')



@section('content')

    {{-- Header --}}
    <div class="izinHeader">
        <div class="izinHeaderLabel">Admin</div>
        <div class="izinHeaderTitle">Kelola Izin Tutor</div>
        <div class="izinHeaderSub">Berikan izin setelah konfirmasi WhatsApp dari tutor</div>
    </div>


    @if($errors->any())
        <div class="errorList" style="margin: 14px 16px 0;">
            @foreach($errors->all() as $err)
                <div>• {{ $err }}</div>
            @endforeach
        </div>
    @endif

    {{-- Panel Beri Izin --}}
    <div class="panelCard">
        <div class="panelCardHead">
            <ion-icon name="shield-checkmark-outline"></ion-icon>
            <div class="panelCardHeadTitle">Beri Izin</div>
        </div>
        <form method="POST" action="{{ route('admin.izin.store') }}" id="izinForm">
            @csrf
            <div class="panelCardBody">

                {{-- Pilih Tutor --}}
                <div class="fieldGroup">
                    <div class="fieldLabel">Tutor</div>
                    <select name="tutor_id" id="tutorSelect" class="selectInput" required>
                        <option value="">-- Pilih Tutor --</option>
                        @foreach($tutors as $tutor)
                            <option value="{{ $tutor->id }}" {{ old('tutor_id') == $tutor->id ? 'selected' : '' }}>
                                {{ $tutor->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Pilih Tanggal --}}
                <div class="fieldGroup">
                    <div class="fieldLabel">Tanggal Izin</div>
                    <input type="date" name="tanggal" id="tanggalInput" class="dateInput"
                           value="{{ old('tanggal', date('Y-m-d')) }}" required>
                </div>

                {{-- Daftar Siswa (muncul via AJAX) --}}
                <div class="fieldGroup">
                    <div class="fieldLabel">Siswa <span style="font-weight:600;color:var(--muted);">(bisa lebih dari 1)</span></div>
                    <div class="siswaCheckList" id="siswaList">
                        <div class="siswaCheckEmpty" id="siswaPlaceholder">
                            Pilih tutor terlebih dahulu
                        </div>
                    </div>
                </div>

                {{-- Jenis Izin: hanya Izin --}}
                <input type="hidden" name="jenis" value="izin">

                <button type="submit" class="btnSubmitIzin" id="btnSubmit" disabled>
                    Beri Izin
                </button>
            </div>
        </form>
    </div>

    {{-- Riwayat Izin --}}
    <div class="sectionTitle">RIWAYAT IZIN</div>

    <div class="riwayatList">
        @forelse($riwayatIzin as $item)
            @php
                $tutorName  = $item->tutor->nama_lengkap ?? 'Tutor';
                $siswaName  = $item->siswa->nama_siswa   ?? '-';
                $initial    = strtoupper(substr($tutorName, 0, 1));
                $tgl        = \Carbon\Carbon::parse($item->tgl_presensi)->translatedFormat('d M Y');
                $pillClass  = 'izin';
                $pillLabel  = 'Izin';
            @endphp
            <div class="riwayatRow">
                <div class="riwayatAvatar">{{ $initial }}</div>
                <div class="riwayatInfo">
                    <div class="riwayatName">{{ $tutorName }}</div>
                    <div class="riwayatMeta">{{ $siswaName }} &bull; {{ $tgl }}</div>
                </div>
                <div class="riwayatRight">
                    <span class="pill {{ $pillClass }}">{{ $pillLabel }}</span>
                    <form method="POST" action="{{ route('admin.izin.destroy', $item->id) }}"
                          data-confirm="Apakah Anda yakin ingin membatalkan izin ini?"
                          data-confirm-title="Batalkan Izin"
                          data-confirm-type="danger"
                          data-confirm-btn="Ya, Batalkan">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btnBatal">Batalkan</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="emptyState">Belum ada riwayat izin.</div>
        @endforelse
    </div>

@endsection

@push('scripts')
<script>
    const tutorSelect  = document.getElementById('tutorSelect');
    const siswaList    = document.getElementById('siswaList');
    const placeholder  = document.getElementById('siswaPlaceholder');
    const btnSubmit    = document.getElementById('btnSubmit');
    const ajaxBase     = '{{ url("/admin/izin/siswa") }}';

    // Jenis sudah fixed = izin, tidak perlu toggle

    // Load siswa saat tutor berubah
    tutorSelect.addEventListener('change', function () {
        const tutorId = this.value;
        if (!tutorId) {
            siswaList.innerHTML = '<div class="siswaCheckEmpty" id="siswaPlaceholder">Pilih tutor terlebih dahulu</div>';
            btnSubmit.disabled = true;
            return;
        }

        siswaList.innerHTML = '<div class="siswaCheckEmpty">Memuat daftar siswa...</div>';

        fetch(`${ajaxBase}/${tutorId}`)
            .then(r => r.json())
            .then(data => {
                if (!data.length) {
                    siswaList.innerHTML = '<div class="siswaCheckEmpty">Tidak ada siswa ditemukan untuk tutor ini.</div>';
                    btnSubmit.disabled = true;
                    return;
                }

                siswaList.innerHTML = data.map(s => `
                    <label class="siswaCheckItem">
                        <input type="checkbox" name="siswa_ids[]" value="${s.id}" onchange="checkSubmit()">
                        <span class="siswaCheckName">${s.nama_siswa}</span>
                    </label>
                `).join('');

                checkSubmit();
            })
            .catch(() => {
                siswaList.innerHTML = '<div class="siswaCheckEmpty">Gagal memuat data siswa.</div>';
                btnSubmit.disabled = true;
            });
    });

    function checkSubmit() {
        const any = document.querySelector('input[name="siswa_ids[]"]:checked');
        btnSubmit.disabled = !any;
    }

    // Aktifkan tombol jika halaman diload ulang dengan old() dan ada siswa
    document.addEventListener('DOMContentLoaded', () => {
        if (tutorSelect.value) tutorSelect.dispatchEvent(new Event('change'));
    });
</script>
@endpush
