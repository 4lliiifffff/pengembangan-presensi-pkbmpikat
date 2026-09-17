@extends('layouts.presensi')

@section('title', 'Lupa Lapor')

@php
    $user        = auth()->user();
    $displayName = (string) (($user->nama_lengkap ?? $user->name) ?? 'Tutor');
    $initial     = strtoupper(substr($displayName, 0, 1));
@endphp

@push('topbar')
    @include('layouts.components.navigasi_atas', [
        'titleName' => 'Pengajuan Lupa Lapor',
        'subTitle' => $displayName . ' • Presensi',
        'dashRoute' => route('tutor.dashboard'),
        'backRoute' => route('tutor.dashboard'),
    ])
@endpush

@section('content')

<div class="pengajuanPage">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">KOREKSI ABSENSI SESI</div>
                <h1 class="laporanHeaderTitle">Pengajuan Lupa Lapor</h1>
                <div class="laporanHeaderSub">Koreksi data jam masuk atau jam selesai sesi mengajar</div>
                <p class="laporanHeaderDesc">Ajukan permohonan lupa absen dengan menyertakan alasan dan bukti foto untuk disetujui Kepala Sekolah.</p>
            </div>
            <div class="laporanHeaderActions">
                <div class="badgeDate">
                    <ion-icon name="calendar-outline"></ion-icon>
                    <span>{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

{{-- ── Tabs ── --}}
<div class="tabBar">
    <button class="tabBtn active" id="tabForm" onclick="switchTab('form', this)">
        <ion-icon name="add-circle-outline" class="text-lg align-middle"></ion-icon>
        Ajukan
    </button>
    <button class="tabBtn" id="tabRiwayat" onclick="switchTab('riwayat', this)">
        <ion-icon name="time-outline" class="text-lg align-middle"></ion-icon>
        Riwayat ({{ $riwayat->count() }})
    </button>
</div>

{{-- ═══════════════════ PANEL FORM ═══════════════════ --}}
<div class="tabPanel active" id="panelForm">

    @if($errors->any())
    <div class="errorBox mt-3">
        <ul >
            @foreach($errors->all() as $e)
                <li >{{ $e }}</li>
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
                <div >
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
                <div >
                    <div class="fieldLabel">Tanggal <span class="req">*</span></div>
                    <input type="date" name="tanggal" class="input" value="{{ old('tanggal', now()->toDateString()) }}"
                           max="{{ now()->toDateString() }}" required>
                </div>

                {{-- Jam --}}
                <div class="inputRow">
                    <div >
                        <div class="fieldLabel">Jam Mulai <span class="req">*</span></div>
                        <input type="time" name="jam_mulai" class="input" value="{{ old('jam_mulai') }}" required>
                    </div>
                    <div >
                        <div class="fieldLabel">Jam Selesai <span class="req">*</span></div>
                        <input type="time" name="jam_selesai" class="input" value="{{ old('jam_selesai') }}" required>
                    </div>
                </div>

                {{-- Alasan --}}
                <div >
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

    <div class="h-spacer-110"></div>
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
                    <div >
                        <div class="llDate">{{ $tgl }}</div>
                        <div class="llSiswa">
                            <ion-icon name="person-outline" class="text-xs align-middle"></ion-icon>
                            {{ $item->siswa->nama_siswa ?? 'Siswa #'.$item->siswa_id }}
                        </div>
                    </div>
                    <div class="d-flex flex-col items-end gap-1">
                        @if(($item->status ?? 'pending') === 'disetujui')
                            <span class="badgeStatus disetujui">
                                <ion-icon name="checkmark-circle-outline" class="align-middle"></ion-icon> Disetujui
                            </span>
                        @elseif(($item->status ?? 'pending') === 'ditolak')
                            <span class="badgeStatus ditolak">
                                <ion-icon name="close-circle-outline" class="align-middle"></ion-icon> Ditolak
                            </span>
                        @else
                            <span class="badgeStatus pending">
                                <ion-icon name="time-outline" class="align-middle"></ion-icon> Menunggu
                            </span>
                        @endif
                        <div class="llJam">{{ $jMulai }} – {{ $jSelesai }}</div>
                        @if(($item->status ?? 'pending') === 'pending')
                            <form method="POST" action="{{ route('tutor.lupa-lapor.destroy', $item->id) }}"
                                  data-confirm="Apakah Anda yakin ingin menghapus pengajuan lupa lapor ini?"
                                  data-confirm-title="Hapus Pengajuan"
                                  data-confirm-type="danger"
                                  data-confirm-btn="Ya, Hapus">
                                @csrf @method('DELETE')
                                <button type="submit" class="deleteBtn">Hapus</button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="llAlasanLabel">Alasan / Keterangan</div>
                <div class="llAlasan">{{ $item->alasan }}</div>
                @if($item->catatan_kepsek)
                    <div class="llAlasanLabel mt-1 text-primary">Catatan Kepala Sekolah</div>
                    <div  class="llAlasan font-italic text-dark">{{ $item->catatan_kepsek }}</div>
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

    <div class="h-spacer-110"></div>
</div>
</div>

<script >
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
