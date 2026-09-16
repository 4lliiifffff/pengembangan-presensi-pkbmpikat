@extends('layouts.kepsek')

@section('title', 'Persetujuan Pengajuan Lupa Lapor — Kepala Sekolah')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header ── --}}
    <div class="laporanHeader" style="padding-left: 0; padding-right: 0; margin-bottom: 16px;">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">VERIFIKASI &amp; PERSETUJUAN</div>
                <h1 class="laporanHeaderTitle">Persetujuan Lupa Lapor Presensi</h1>
                <div class="laporanHeaderSub">Tinjau dan verifikasi permohonan presensi retroaktif dari Tutor PKBM</div>
                <p class="laporanHeaderDesc">Persetujuan otomatis menyinkronkan data jam masuk, jam selesai, dan rekap kehadiran.</p>
            </div>
        </div>
    </div>


    {{-- ── Summary Stats ── --}}
    <div class="statRow">
        <div class="statCard">
            <div class="statNum blue">{{ $total }}</div>
            <div class="statLabel">Total Pengajuan</div>
        </div>
        <div class="statCard">
            <div class="statNum" style="color: #f59e0b;">{{ $totalPending ?? 0 }}</div>
            <div class="statLabel">Menunggu Verifikasi</div>
        </div>
        <div class="statCard">
            <div class="statNum green">{{ max(0, $total - ($totalPending ?? 0)) }}</div>
            <div class="statLabel">Selesai Diproses</div>
        </div>
    </div>

    {{-- ── Filter Card ── --}}
    <div class="laporanFilterCard">
        <form method="GET" action="{{ route('kepsek.lupa-lapor') }}">
            <div class="laporanFilterGrid">
                <div class="filterField">
                    <label class="filterFieldLabel">Status</label>
                    <select name="status" class="filterSelect" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>🟡 Menunggu ({{ $totalPending ?? 0 }})</option>
                        <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>🟢 Disetujui</option>
                        <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>🔴 Ditolak</option>
                    </select>
                </div>

                <div class="filterField">
                    <label class="filterFieldLabel">Tanggal</label>
                    <input type="date" name="tanggal" class="profileInput" style="height: 42px; font-size: 13px;" value="{{ request('tanggal') }}">
                </div>

                <div class="filterField" style="grid-column: span 2;">
                    <label class="filterFieldLabel">Cari Tutor / Siswa</label>
                    <input type="text" name="cari" class="profileInput" style="height: 42px; font-size: 13px;" value="{{ request('cari') }}" placeholder="Nama tutor atau siswa...">
                </div>

                <div class="filterActionGroup" style="grid-column: 1 / -1; margin-top: 4px; display: flex; flex-wrap: wrap; gap: 8px;">
                    <button type="submit" class="profileBtnPrimary" style="height: 42px; padding: 0 18px; font-size: 13px; border-radius: 12px; flex: 1; min-width: 140px;">
                        <ion-icon name="search-outline"></ion-icon> Cari Data
                    </button>
                    @if(request('tanggal') || request('cari') || request('status'))
                        <a href="{{ route('kepsek.lupa-lapor') }}" class="profileBtnDanger" style="height: 42px; padding: 0 14px; font-size: 13px; border-radius: 12px; width: auto; text-decoration: none;">
                            Reset Filter
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- ── Section Title ── --}}
    <div class="sectionRow">
        <h2>Daftar Permohonan</h2>
        <span class="badgeCount">{{ $items->total() }} Data</span>
    </div>

    {{-- ── List of Cards ── --}}
    <div class="kllList">
        @forelse($items as $item)
            @php
                $tgl       = \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y');
                $jMulai    = substr((string) $item->jam_mulai, 0, 5);
                $jSelesai  = substr((string) $item->jam_selesai, 0, 5);
                $tutorNama = $item->tutor->nama_lengkap ?? 'Tutor #'.$item->tutor_id;
                $siswaNama = $item->siswa->nama_siswa  ?? 'Siswa #'.$item->siswa_id;
                $status    = $item->status ?? 'pending';
                $initials  = strtoupper(substr($tutorNama, 0, 2));
            @endphp
            <div class="kllCard">
                {{-- Header Card --}}
                <div class="kllCardStrip">
                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1;">
                        <div class="tutorAvatar" style="width: 36px; height: 36px; font-size: 12.5px;">{{ $initials }}</div>
                        <div class="kllTutorInfo">
                            <div class="kllTutorName">{{ $tutorNama }}</div>
                            <div class="kllTutorId">
                                <ion-icon name="calendar-outline"></ion-icon> {{ $tgl }}
                            </div>
                        </div>
                    </div>
                    <div>
                        @if($status === 'disetujui')
                            <span class="badgeStatus disetujui">
                                <ion-icon name="checkmark-circle-outline"></ion-icon> DISETUJUI
                            </span>
                        @elseif($status === 'ditolak')
                            <span class="badgeStatus ditolak">
                                <ion-icon name="close-circle-outline"></ion-icon> DITOLAK
                            </span>
                        @else
                            <span class="badgeStatus pending">
                                <ion-icon name="time-outline"></ion-icon> MENUNGGU
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Body Card --}}
                <div class="kllCardBody">
                    <div class="kllRow">
                        <div class="kllLeft">
                            <div class="kllSiswaLabel">Siswa yang Diajar:</div>
                            <div class="kllSiswaVal">{{ $siswaNama }}</div>
                            <div class="kllJamBox">
                                <span>Jam KBM:</span>
                                <span class="kllJamChip">{{ $jMulai }} - {{ $jSelesai }} WIB</span>
                            </div>
                        </div>
                    </div>

                    <div class="kllAlasanWrap">
                        <div class="kllAlasanLbl">Alasan Lupa Lapor:</div>
                        <div class="kllAlasanTxt">{{ $item->alasan }}</div>
                        @if($item->catatan_kepsek)
                            <div style="margin-top:8px; border-top:1px dashed var(--border); padding-top:6px; font-size:11.5px; color:var(--blue2);">
                                <strong>Catatan Respon:</strong> {{ $item->catatan_kepsek }}
                            </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="kllActions">
                        @if($status === 'pending')
                            <form method="POST" action="{{ route('kepsek.lupa-lapor.setujui', $item->id) }}" style="flex:1;" data-confirm="Setujui permohonan lupa lapor ini? Presensi mengajar tutor akan otomatis dicatat." data-confirm-title="Setujui Lupa Lapor" data-confirm-type="success" data-confirm-btn="Ya, Setujui">
                                @csrf @method('PATCH')
                                <button type="submit" class="profileBtnPrimary" style="width:100%; height:38px; font-size:12px; border-radius:10px; background:#16a34a;">
                                    <ion-icon name="checkmark-circle-outline"></ion-icon> Setujui
                                </button>
                            </form>

                            <form method="POST" action="{{ route('kepsek.lupa-lapor.tolak', $item->id) }}" style="flex:1;" data-confirm="Apakah Anda yakin ingin menolak permohonan lupa lapor ini?" data-confirm-title="Tolak Lupa Lapor" data-confirm-type="danger" data-confirm-btn="Ya, Tolak">
                                @csrf @method('PATCH')
                                <button type="submit" class="profileBtnDanger" style="width:100%; height:38px; font-size:12px; border-radius:10px; background:#ef4444; color:#fff;">
                                    <ion-icon name="close-circle-outline"></ion-icon> Tolak
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('kepsek.lupa-lapor.destroy', $item->id) }}" data-confirm="Hapus permanen arsip pengajuan ini?" data-confirm-title="Hapus Arsip Pengajuan" data-confirm-type="danger" data-confirm-btn="Ya, Hapus">
                            @csrf @method('DELETE')
                            <button type="submit" class="profileBtnDanger" style="height:38px; padding:0 12px; font-size:12px; border-radius:10px; width:auto;" title="Hapus Data">
                                <ion-icon name="trash-outline"></ion-icon>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="emptyLaporan">
                <ion-icon name="document-text-outline"></ion-icon>
                <div style="font-weight: 800; font-size: 14px; margin-bottom: 4px; color: var(--text);">Tidak Ada Data Pengajuan</div>
                <div>{{ request('tanggal') || request('cari') || request('status') ? 'Tidak ada pengajuan yang cocok dengan filter pencarian.' : 'Belum ada pengajuan lupa lapor dari tutor saat ini.' }}</div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($items->hasPages())
        <div class="paginatePad">
            {{ $items->withQueryString()->links() }}
        </div>
    @endif
</div>

@endsection
