@extends('layout.presensi')

@section('title', 'Lupa Lapor')

@section('content')

@php
    $user        = auth()->user();
    $displayName = (string) (($user->nama_lengkap ?? $user->name) ?? 'Tutor');
    $initial     = strtoupper(substr($displayName, 0, 1));
@endphp

<style>
    /* ── Top Bar ── */
    .llTopBar {
        background: var(--topbar-bg);
        padding: 14px 16px 18px;
        border-bottom-left-radius: 22px;
        border-bottom-right-radius: 22px;
    }
    .llTopRow { display: flex; align-items: center; gap: 12px; }
    .llBackBtn {
        width: 36px; height: 36px; border-radius: 12px;
        background: rgba(255,255,255,0.18);
        border: 1px solid rgba(255,255,255,0.22);
        display: grid; place-items: center;
        color: #fff; text-decoration: none; flex-shrink: 0;
    }
    .llPageTitle  { font-size: 15px; font-weight: 900; color: #fff; }
    .llPageSub    { font-size: 11px; color: rgba(255,255,255,0.75); font-weight: 600; margin-top: 2px; }

    /* ── Tab Switcher ── */
    .tabBar {
        display: flex;
        gap: 8px;
        padding: 14px 16px 0;
    }
    .tabBtn {
        flex: 1;
        padding: 10px 0;
        border-radius: 14px;
        border: 1px solid var(--border);
        background: var(--card-alt);
        font-size: 12px;
        font-weight: 900;
        color: var(--muted);
        cursor: pointer;
        transition: all 0.15s;
        text-align: center;
    }
    .tabBtn.active {
        background: var(--blue2);
        border-color: var(--blue2);
        color: #fff;
    }

    /* ── Tab panels ── */
    .tabPanel { display: none; }
    .tabPanel.active { display: block; }

    /* ── Form ── */
    .llFormSection { padding: 16px 16px 0; }
    .llFormTitle {
        font-size: 12px; font-weight: 900; letter-spacing: 0.5px;
        text-transform: uppercase; color: var(--muted);
        margin-bottom: 10px;
    }
    .llFormCard {
        border: 1px solid var(--border);
        border-radius: 18px;
        background: var(--card-alt);
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 14px;
    }
    .fieldLabel {
        font-size: 12px; font-weight: 900; margin-bottom: 5px; color: var(--text);
        display: flex; align-items: center; gap: 5px;
    }
    .fieldLabel .req { color: var(--danger); }
    .input {
        width: 100%; padding: 11px 12px;
        border-radius: 14px; border: 1px solid var(--border);
        background: var(--input-bg); outline: none;
        font-size: 13px; color: var(--text);
        font-family: inherit;
    }
    .input:focus {
        border-color: rgba(11,94,215,0.45);
        box-shadow: 0 0 0 3px rgba(11,94,215,0.12);
    }
    textarea.input { resize: vertical; min-height: 80px; }
    .inputRow { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .submitBtn {
        width: 100%; padding: 15px;
        border-radius: 16px;
        background: linear-gradient(135deg, #0B5ED7 0%, #1A73E8 100%);
        color: #fff; border: none;
        font-size: 13px; font-weight: 900;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(11,94,215,0.25);
        transition: opacity 0.15s, transform 0.15s;
    }
    .submitBtn:active { opacity: 0.88; transform: scale(0.98); }
    .submitBtn ion-icon { font-size: 18px; }

    /* ── Error list ── */
    .errorBox {
        margin: 0 16px 10px;
        padding: 10px 12px;
        border-radius: 14px;
        border: 1px solid rgba(239,68,68,0.25);
        background: rgba(239,68,68,0.08);
        color: #dc2626; font-weight: 900; font-size: 12px;
    }
    .errorBox ul { margin: 4px 0 0 14px; padding: 0; }

    /* ── Riwayat List ── */
    .llList { padding: 14px 16px 0; display: flex; flex-direction: column; gap: 10px; }
    .llCard {
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 14px;
        background: var(--card-alt2);
    }
    .llCardHead {
        display: flex; align-items: flex-start;
        justify-content: space-between; gap: 10px; margin-bottom: 8px;
    }
    .llDate  { font-size: 13px; font-weight: 900; color: var(--text); }
    .llSiswa { font-size: 11px; color: var(--muted); font-weight: 700; margin-top: 2px; }
    .llJam   { font-size: 11px; font-weight: 900; color: var(--blue2); white-space: nowrap; }

    .llAlasan {
        font-size: 12px; color: var(--muted);
        background: var(--card-alt);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 9px 11px;
        line-height: 1.5;
    }
    .llAlasanLabel {
        font-size: 10px; font-weight: 900; letter-spacing: 0.3px;
        text-transform: uppercase; color: var(--muted); margin-bottom: 4px;
    }

    .emptyLL {
        padding: 30px 16px; text-align: center;
        color: var(--muted); font-weight: 800; font-size: 13px;
    }
    .emptyLL ion-icon {
        font-size: 42px; display: block; margin: 0 auto 10px; opacity: 0.3;
    }

    .deleteBtn {
        font-size: 10px; font-weight: 900; padding: 5px 10px;
        border-radius: 10px;
        background: rgba(239,68,68,0.08);
        color: #dc2626;
        border: 1px solid rgba(239,68,68,0.2);
        cursor: pointer; text-decoration: none;
        flex-shrink: 0;
    }
</style>

{{-- ── Top Bar ── --}}
<div class="llTopBar">
    <div class="llTopRow">
        <a href="{{ route('tutor.dashboard') }}" class="llBackBtn" aria-label="Kembali">
            <ion-icon name="arrow-back-outline" style="font-size:20px;"></ion-icon>
        </a>
        <div style="flex: 1;">
            <div class="llPageTitle">Lupa Lapor</div>
            <div class="llPageSub">Ajukan jika lupa absen atau izin</div>
        </div>
        <button class="llBackBtn" type="button" aria-label="Tema" id="themeToggleBtn">
            <ion-icon name="moon-outline" style="font-size:20px;" id="themeToggleIcon"></ion-icon>
        </button>
    </div>
</div>

{{-- ── Flash Messages ── --}}
@if(session('success'))
    <div class="flashAlert success" style="margin:12px 16px 0;">{{ session('success') }}</div>
@endif
@if(session('warning'))
    <div class="flashAlert warning" style="margin:12px 16px 0;">{{ session('warning') }}</div>
@endif

{{-- ── Tabs ── --}}
<div class="tabBar">
    <button class="tabBtn active" id="tabForm" onclick="switchTab('form', this)">
        <ion-icon name="add-circle-outline" style="font-size:15px;vertical-align:middle;"></ion-icon>
        Ajukan
    </button>
    <button class="tabBtn" id="tabRiwayat" onclick="switchTab('riwayat', this)">
        <ion-icon name="time-outline" style="font-size:15px;vertical-align:middle;"></ion-icon>
        Riwayat ({{ $riwayat->count() }})
    </button>
</div>

{{-- ═══════════════════ PANEL FORM ═══════════════════ --}}
<div class="tabPanel active" id="panelForm">

    @if($errors->any())
    <div class="errorBox" style="margin-top:12px;">
        <ul>
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('tutor.lupa-lapor.store') }}">
        @csrf

        <div class="llFormSection">
            <div class="llFormTitle">Data Pengajuan</div>

            <div class="llFormCard">
                {{-- Siswa --}}
                <div>
                    <div class="fieldLabel">Siswa <span class="req">*</span></div>
                    <select name="siswa_id" class="input" required>
                        <option value="">— Pilih Siswa —</option>
                        @foreach($siswaList as $siswa)
                            <option value="{{ $siswa->id }}" {{ old('siswa_id') == $siswa->id ? 'selected' : '' }}>
                                {{ $siswa->nama_siswa }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tanggal --}}
                <div>
                    <div class="fieldLabel">Tanggal <span class="req">*</span></div>
                    <input type="date" name="tanggal" class="input"
                           value="{{ old('tanggal', now()->toDateString()) }}"
                           max="{{ now()->toDateString() }}" required>
                </div>

                {{-- Jam --}}
                <div class="inputRow">
                    <div>
                        <div class="fieldLabel">Jam Mulai <span class="req">*</span></div>
                        <input type="time" name="jam_mulai" class="input"
                               value="{{ old('jam_mulai') }}" required>
                    </div>
                    <div>
                        <div class="fieldLabel">Jam Selesai <span class="req">*</span></div>
                        <input type="time" name="jam_selesai" class="input"
                               value="{{ old('jam_selesai') }}" required>
                    </div>
                </div>

                {{-- Alasan --}}
                <div>
                    <div class="fieldLabel">Alasan / Keterangan <span class="req">*</span></div>
                    <textarea name="alasan" class="input" placeholder="Jelaskan alasan lupa lapor..." required>{{ old('alasan') }}</textarea>
                </div>
            </div>

            <button type="submit" class="submitBtn" id="btnSubmitLupa">
                <ion-icon name="send-outline"></ion-icon>
                Kirim Pengajuan
            </button>
        </div>
    </form>

    <div style="height: 110px;"></div>
</div>

{{-- ═══════════════════ PANEL RIWAYAT ═══════════════════ --}}
<div class="tabPanel" id="panelRiwayat">

    @if($riwayat->count() > 0)
        <div class="llList">
            @foreach($riwayat as $item)
            @php
                $tgl    = \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('l, d F Y');
                $jMulai = substr((string) $item->jam_mulai, 0, 5);
                $jSelesai = substr((string) $item->jam_selesai, 0, 5);
            @endphp
            <div class="llCard">
                <div class="llCardHead">
                    <div>
                        <div class="llDate">{{ $tgl }}</div>
                        <div class="llSiswa">
                            <ion-icon name="person-outline" style="font-size:11px;vertical-align:middle;"></ion-icon>
                            {{ $item->siswa->nama_siswa ?? 'Siswa #'.$item->siswa_id }}
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
                        <div class="llJam">{{ $jMulai }} – {{ $jSelesai }}</div>
                        <form method="POST"
                              action="{{ route('tutor.lupa-lapor.destroy', $item->id) }}"
                              onsubmit="return confirm('Hapus pengajuan ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="deleteBtn">Hapus</button>
                        </form>
                    </div>
                </div>
                <div class="llAlasanLabel">Alasan</div>
                <div class="llAlasan">{{ $item->alasan }}</div>
            </div>
            @endforeach
        </div>
    @else
        <div class="emptyLL">
            <ion-icon name="document-text-outline"></ion-icon>
            Belum ada riwayat pengajuan lupa lapor.
        </div>
    @endif

    <div style="height: 110px;"></div>
</div>

<script>
    function switchTab(panel, btn) {
        document.querySelectorAll('.tabPanel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tabBtn').forEach(b => b.classList.remove('active'));
        document.getElementById('panel' + panel.charAt(0).toUpperCase() + panel.slice(1)).classList.add('active');
        btn.classList.add('active');
    }

    // Jika ada error validasi, otomatis open tab Form
    @if($errors->any())
        document.getElementById('tabForm').click();
    @endif
</script>

@endsection
