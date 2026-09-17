@extends('layouts.admin')

@section('title', 'Kelola Izin — Admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">MANAJEMEN PERIZINAN</div>
                <h1 class="laporanHeaderTitle">Kelola Izin Tutor</h1>
                <div class="laporanHeaderSub">Pencatatan & verifikasi izin KBM tutor</div>
                <p class="laporanHeaderDesc">Pencatatan izin resmi tutor setelah konfirmasi untuk sinkronisasi otomatis kalender KBM dan rekapitulasi kehadiran.</p>
            </div>
            <div class="laporanHeaderActions">
                <div class="badgeCount">
                    <ion-icon name="document-text-outline"></ion-icon>
                    <span>{{ $riwayatIzin->count() }} Data Tercatat</span>
                </div>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="errorList">
            @foreach($errors->all() as $err)
                <div>• {{ $err }}</div>
            @endforeach
        </div>
    @endif

    {{-- Panel Beri Izin --}}
    <div class="panelCard">
        <div class="panelCardHead">
            <div class="panelCardHeadIcon">
                <ion-icon name="shield-checkmark-outline"></ion-icon>
            </div>
            <div>
                <div class="panelCardHeadTitle">Formulir Beri Izin</div>
                <div class="panelCardHeadSub">Pilih tutor, tanggal, dan siswa bimbingan yang izin</div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.izin.store') }}" id="izinForm">
            @csrf
            <div class="panelCardBody">
                <div class="formGrid2">
                    {{-- Pilih Tutor --}}
                    <div class="fieldGroup">
                        <label class="fieldLabel" for="tutorSelect">Pilih Tutor</label>
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
                        <label class="fieldLabel" for="tanggalInput">Tanggal Izin</label>
                        <input type="date" name="tanggal" id="tanggalInput" class="dateInput" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                    </div>
                </div>

                {{-- Daftar Siswa (muncul via AJAX) --}}
                <div class="fieldGroup">
                    <div class="fieldLabel">Pilih Siswa <span class="fieldLabelHint">(bisa pilih lebih dari 1)</span></div>
                    <div class="siswaCheckList" id="siswaList">
                        <div class="siswaCheckEmpty" id="siswaPlaceholder">
                            <ion-icon name="person-outline"></ion-icon>
                            <span>Pilih tutor terlebih dahulu untuk memuat daftar siswa bimbingan</span>
                        </div>
                    </div>
                </div>

                {{-- Jenis Izin: fixed izin --}}
                <input type="hidden" name="jenis" value="izin">

                <button type="submit" class="profileBtnPrimary btnSubmitIzinFull" id="btnSubmit" disabled>
                    <ion-icon name="checkmark-circle-outline"></ion-icon> Simpan Izin Tutor
                </button>
            </div>
        </form>
    </div>

    {{-- Riwayat Izin --}}
    <div class="sectionRow">
        <div class="sectionTitleWrap">
            <div class="sectionTitleLabel">RIWAYAT PERIZINAN</div>
            <h2 class="sectionTitle">Daftar Izin Tercatat</h2>
        </div>
        <div class="sectionBadge">
            <ion-icon name="time-outline"></ion-icon>
            <span>{{ $riwayatIzin->count() }} Riwayat</span>
        </div>
    </div>

    <div class="riwayatList">
        @forelse($riwayatIzin as $item)
            @php
                $tutorName  = $item->tutor->nama_lengkap ?? 'Tutor';
                $siswaName  = $item->siswa->nama_siswa   ?? '-';
                $initial    = strtoupper(substr($tutorName, 0, 1));
                $tgl        = \Carbon\Carbon::parse($item->tgl_presensi)->translatedFormat('d M Y');
            @endphp
            <div class="riwayatRow">
                <div class="riwayatLeft">
                    <div class="riwayatAvatar">{{ $initial }}</div>
                    <div class="riwayatInfo">
                        <div class="riwayatName">{{ $tutorName }}</div>
                        <div class="riwayatMeta">
                            <span class="riwayatMetaItem"><ion-icon name="person-outline"></ion-icon> {{ $siswaName }}</span>
                            <span class="riwayatMetaDivider">&bull;</span>
                            <span class="riwayatMetaItem"><ion-icon name="calendar-outline"></ion-icon> {{ $tgl }}</span>
                        </div>
                    </div>
                </div>
                <div class="riwayatRight">
                    <span class="badgeIzinPill">
                        <ion-icon name="time-outline"></ion-icon> Izin
                    </span>
                    <form method="POST" action="{{ route('admin.izin.destroy', $item->id) }}"
                          data-confirm="Apakah Anda yakin ingin membatalkan izin ini?"
                          data-confirm-title="Batalkan Izin"
                          data-confirm-type="danger"
                          data-confirm-btn="Ya, Batalkan">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btnBatal" title="Batalkan Izin">
                            <ion-icon name="trash-outline"></ion-icon>
                            <span>Batalkan</span>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="emptyStateCard">
                <div class="emptyStateIcon">
                    <ion-icon name="document-text-outline"></ion-icon>
                </div>
                <div class="emptyStateTitle">Belum Ada Riwayat Izin</div>
                <div class="emptyStateDesc">Daftar izin tutor yang telah dicatat akan tampil di sini secara terstruktur.</div>
            </div>
        @endforelse
    </div>
</div>

@endsection

@push('scripts')
<script>
    const tutorSelect  = document.getElementById('tutorSelect');
    const siswaList    = document.getElementById('siswaList');
    const placeholder  = document.getElementById('siswaPlaceholder');
    const btnSubmit    = document.getElementById('btnSubmit');
    const ajaxBase     = '{{ url("/admin/izin/siswa") }}';

    // Load siswa saat tutor berubah
    tutorSelect.addEventListener('change', function () {
        const tutorId = this.value;
        if (!tutorId) {
            siswaList.innerHTML = '<div class="siswaCheckEmpty" id="siswaPlaceholder"><ion-icon name="person-outline"></ion-icon><span>Pilih tutor terlebih dahulu untuk memuat daftar siswa bimbingan</span></div>';
            btnSubmit.disabled = true;
            return;
        }

        siswaList.innerHTML = '<div class="siswaCheckEmpty"><ion-icon name="sync-outline" class="spinIcon"></ion-icon><span>Memuat daftar siswa bimbingan...</span></div>';

        fetch(`${ajaxBase}/${tutorId}`)
            .then(r => r.json())
            .then(data => {
                if (!data.length) {
                    siswaList.innerHTML = '<div class="siswaCheckEmpty"><ion-icon name="alert-circle-outline"></ion-icon><span>Tidak ada siswa bimbingan aktif untuk tutor ini.</span></div>';
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
                siswaList.innerHTML = '<div class="siswaCheckEmpty"><ion-icon name="close-circle-outline"></ion-icon><span>Gagal memuat data siswa bimbingan.</span></div>';
                btnSubmit.disabled = true;
            });
    });

    function checkSubmit() {
        const any = document.querySelector('input[name="siswa_ids[]"]:checked');
        btnSubmit.disabled = !any;
    }

    // Aktifkan jika ada old value
    document.addEventListener('DOMContentLoaded', () => {
        if (tutorSelect.value) tutorSelect.dispatchEvent(new Event('change'));
    });
</script>
@endpush
