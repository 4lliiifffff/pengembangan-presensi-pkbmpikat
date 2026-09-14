@extends('layouts.presensi')

@section('title', 'Lupa Lapor')

@section('content')

@php
    $user        = auth()->user();
    $displayName = (string) (($user->nama_lengkap ?? $user->name) ?? 'Tutor');
    $initial     = strtoupper(substr($displayName, 0, 1));
@endphp

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
                        @if(($item->status ?? 'pending') === 'disetujui')
                            <span class="badge" style="background:#10b981;color:#fff;font-size:11px;padding:3px 8px;border-radius:12px;">🟢 Disetujui</span>
                        @elseif(($item->status ?? 'pending') === 'ditolak')
                            <span class="badge" style="background:#ef4444;color:#fff;font-size:11px;padding:3px 8px;border-radius:12px;">🔴 Ditolak</span>
                        @else
                            <span class="badge" style="background:#f59e0b;color:#fff;font-size:11px;padding:3px 8px;border-radius:12px;">🟡 Menunggu</span>
                        @endif
                        <div class="llJam">{{ $jMulai }} – {{ $jSelesai }}</div>
                        @if(($item->status ?? 'pending') === 'pending')
                            <form method="POST"
                                  action="{{ route('tutor.lupa-lapor.destroy', $item->id) }}"
                                  onsubmit="return confirm('Hapus pengajuan ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="deleteBtn">Hapus</button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="llAlasanLabel">Alasan / Keterangan</div>
                <div class="llAlasan">{{ $item->alasan }}</div>
                @if($item->catatan_kepsek)
                    <div class="llAlasanLabel" style="margin-top:6px;color:#0284c7;">Catatan Kepala Sekolah</div>
                    <div class="llAlasan" style="font-style:italic;color:#334155;">{{ $item->catatan_kepsek }}</div>
                @endif
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
