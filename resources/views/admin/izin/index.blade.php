@extends('layout.admin')

@section('title', 'Kelola Izin — Admin')

<style>
    /* ── Page Header ── */
    .izinHeader {
        background: linear-gradient(135deg, #1a3a5c 0%, #0b5ed7 100%);
        padding: 20px 16px 24px;
    }

    .izinHeaderLabel {
        font-size: 11px;
        color: rgba(255,255,255,0.7);
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .izinHeaderTitle {
        font-size: 20px;
        font-weight: 800;
        color: #fff;
    }

    .izinHeaderSub {
        font-size: 12px;
        color: rgba(255,255,255,0.65);
        margin-top: 4px;
    }

    /* ── Panel Beri Izin ── */
    .panelCard {
        margin: 16px 16px 0;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }

    .panelCardHead {
        background: rgba(11,94,215,0.07);
        border-bottom: 1px solid var(--border);
        padding: 12px 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .panelCardHead ion-icon {
        font-size: 18px;
        color: var(--blue2);
    }

    .panelCardHeadTitle {
        font-size: 13px;
        font-weight: 800;
        color: var(--text);
    }

    .panelCardBody {
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .fieldGroup {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .fieldLabel {
        font-size: 11px;
        font-weight: 800;
        color: var(--muted);
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .selectInput, .dateInput {
        width: 100%;
        padding: 11px 12px;
        border-radius: 12px;
        border: 1.5px solid var(--border);
        background: var(--input-bg);
        color: var(--text);
        font-size: 13px;
        font-weight: 600;
        font-family: inherit;
        outline: none;
        appearance: none;
        -webkit-appearance: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .selectInput:focus, .dateInput:focus {
        border-color: rgba(11,94,215,0.5);
        box-shadow: 0 0 0 3px rgba(11,94,215,0.12);
    }

    /* Siswa Checkbox List */
    .siswaCheckList {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .siswaCheckItem {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 12px;
        border: 1.5px solid var(--border);
        background: var(--card-alt);
        cursor: pointer;
        transition: border-color 0.15s, background 0.15s;
    }

    .siswaCheckItem:has(input:checked) {
        border-color: rgba(11,94,215,0.45);
        background: rgba(11,94,215,0.06);
    }

    .siswaCheckItem input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: var(--blue2);
        flex-shrink: 0;
        cursor: pointer;
    }

    .siswaCheckName {
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
        flex: 1;
    }

    .siswaCheckEmpty {
        padding: 14px 12px;
        text-align: center;
        font-size: 12px;
        color: var(--muted);
        font-weight: 700;
        border: 1.5px dashed var(--border);
        border-radius: 12px;
    }

    /* Jenis Toggle */
    .jenisToggle {
        display: flex;
        gap: 8px;
    }

    .jenisBtn {
        flex: 1;
        padding: 10px 0;
        border-radius: 12px;
        border: 1.5px solid var(--border);
        background: var(--card-alt);
        color: var(--muted);
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        text-align: center;
        transition: all 0.15s;
        font-family: inherit;
    }

    .jenisBtn.izin.selected {
        background: rgba(245,158,11,0.12);
        border-color: rgba(245,158,11,0.45);
        color: #d97706;
    }

    input[type="radio"].jenisRadio {
        display: none;
    }

    /* Submit Button */
    .btnSubmitIzin {
        width: 100%;
        padding: 13px;
        border-radius: 14px;
        border: none;
        background: linear-gradient(135deg, #1a3a5c, #0b5ed7);
        color: #fff;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        font-family: inherit;
        letter-spacing: 0.3px;
        transition: opacity 0.15s, transform 0.1s;
    }

    .btnSubmitIzin:active {
        opacity: 0.85;
        transform: scale(0.99);
    }

    /* ── Riwayat Izin ── */
    .sectionTitle {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1.5px;
        color: var(--muted);
        padding: 18px 16px 8px;
        text-transform: uppercase;
    }

    .riwayatList {
        padding: 0 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding-bottom: 110px;
    }

    .riwayatRow {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }

    .riwayatAvatar {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: var(--avatar-bg);
        display: grid;
        place-items: center;
        font-weight: 900;
        font-size: 15px;
        color: var(--avatar-text);
        flex-shrink: 0;
    }

    .riwayatInfo {
        flex: 1;
        min-width: 0;
    }

    .riwayatName {
        font-size: 13px;
        font-weight: 800;
        color: var(--text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .riwayatMeta {
        font-size: 11px;
        color: var(--muted);
        margin-top: 2px;
    }

    .riwayatRight {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 6px;
        flex-shrink: 0;
    }

    .btnBatal {
        font-size: 11px;
        font-weight: 800;
        padding: 5px 10px;
        border-radius: 10px;
        border: 1px solid rgba(239,68,68,0.3);
        background: rgba(239,68,68,0.08);
        color: #dc2626;
        cursor: pointer;
        font-family: inherit;
        transition: background 0.15s;
    }

    .btnBatal:hover {
        background: rgba(239,68,68,0.15);
    }

    .emptyState {
        padding: 30px 20px;
        text-align: center;
        color: var(--muted);
        font-weight: 800;
        font-size: 13px;
        border: 1.5px dashed var(--border);
        border-radius: 16px;
    }
</style>

@section('content')

    {{-- Header --}}
    <div class="izinHeader">
        <div class="izinHeaderLabel">Admin</div>
        <div class="izinHeaderTitle">Kelola Izin Tutor</div>
        <div class="izinHeaderSub">Berikan izin setelah konfirmasi WhatsApp dari tutor</div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="flashAlert success" style="margin: 14px 16px 0;">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="flashAlert warning" style="margin: 14px 16px 0;">{{ session('warning') }}</div>
    @endif

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
                          onsubmit="return confirm('Batalkan izin ini?')">
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
