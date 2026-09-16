@extends('layouts.kepsek')

@section('title', 'Persetujuan Pengajuan Izin & Sakit — Kepala Sekolah')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">PERIZINAN &amp; KETIDAKHADIRAN</div>
                <h1 class="laporanHeaderTitle">Persetujuan Izin &amp; Sakit Tutor</h1>
                <div class="laporanHeaderSub">Verifikasi surat keterangan dan berikan persetujuan permohonan izin/sakit</div>
                <p class="laporanHeaderDesc">Pengajuan yang disetujui otomatis menyinkronkan status presensi kehadiran tutor.</p>
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
            <div class="statLabel">Menunggu Persetujuan</div>
        </div>
        <div class="statCard">
            <div class="statNum green">{{ max(0, $total - ($totalPending ?? 0)) }}</div>
            <div class="statLabel">Telah Diverifikasi</div>
        </div>
    </div>

    {{-- ── Filter Card ── --}}
    <div class="laporanFilterCard">
        <form method="GET" action="{{ route('kepsek.pengajuan-izin') }}">
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
                    <label class="filterFieldLabel">Jenis Izin</label>
                    <select name="jenis" class="filterSelect" onchange="this.form.submit()">
                        <option value="">Semua Jenis</option>
                        <option value="izin" {{ request('jenis') === 'izin' ? 'selected' : '' }}>Izin Keperluan</option>
                        <option value="sakit" {{ request('jenis') === 'sakit' ? 'selected' : '' }}>Sakit / Rawat</option>
                    </select>
                </div>

                <div class="filterField" style="grid-column: span 2;">
                    <label class="filterFieldLabel">Cari Tutor</label>
                    <input type="text" name="cari" class="profileInput" value="{{ request('cari') }}" placeholder="Ketik nama tutor...">
                </div>

                <div class="filter-actions-full">
                    <button type="submit" class="btn-filter-primary">
                        <ion-icon name="search-outline"></ion-icon> Cari Data
                    </button>
                    @if(request('cari') || request('status') || request('jenis'))
                        <a href="{{ route('kepsek.pengajuan-izin') }}" class="btn-filter-reset">
                            Reset Filter
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- ── Section Title ── --}}
    <div class="sectionRow">
        <h2>Daftar Permohonan Izin / Sakit</h2>
        <span class="badgeCount">{{ $items->total() }} Data</span>
    </div>

    {{-- ── List of Cards ── --}}
    <div class="kllList">
        @forelse($items as $item)
            @php
                $tutorNama = $item->tutor->nama_lengkap ?? 'Tutor Tidak Ditemukan';
                $initials  = strtoupper(substr($tutorNama, 0, 2));
                $tglMulai  = \Carbon\Carbon::parse($item->tgl_mulai)->translatedFormat('d M Y');
                $tglSelesai = \Carbon\Carbon::parse($item->tgl_selesai)->translatedFormat('d M Y');
            @endphp
            <div class="kllCard">
                {{-- Header Card --}}
                <div class="kllCardStrip">
                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1;">
                        <div class="tutorAvatar" style="width: 36px; height: 36px; font-size: 12.5px;">{{ $initials }}</div>
                        <div class="kllTutorInfo">
                            <div class="kllTutorName">{{ $tutorNama }}</div>
                            <div class="kllTutorId">
                                <ion-icon name="calendar-outline"></ion-icon>
                                {{ $tglMulai }}
                                @if($item->tgl_mulai !== $item->tgl_selesai)
                                    — {{ $tglSelesai }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                        <span class="badgeBadge {{ $item->jenis === 'sakit' ? 'sakit' : 'izin' }}">
                            {{ strtoupper($item->jenis_label) }}
                        </span>
                        @if($item->status === 'pending')
                            <span class="badgeStatus pending">
                                <ion-icon name="time-outline"></ion-icon> MENUNGGU
                            </span>
                        @elseif($item->status === 'disetujui')
                            <span class="badgeStatus disetujui">
                                <ion-icon name="checkmark-circle-outline"></ion-icon> DISETUJUI
                            </span>
                        @else
                            <span class="badgeStatus pending">
                                <ion-icon name="close-circle-outline"></ion-icon> DITOLAK
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Body Card --}}
                <div class="kllCardBody">
                    <div class="kllAlasanWrap">
                        <div class="kllAlasanLbl">Alasan / Keterangan:</div>
                        <div class="kllAlasanTxt">{{ $item->alasan }}</div>
                    </div>

                    @if($item->dokumen_url)
                        <div style="margin-top: 4px;">
                            <a href="{{ $item->dokumen_url }}" target="_blank" class="btnOutline" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; font-size: 12px; border-radius: 10px; text-decoration: none;">
                                <ion-icon name="document-attach-outline" style="font-size: 16px; color: var(--blue2);"></ion-icon>
                                <span>Lihat Surat Lampiran</span>
                            </a>
                        </div>
                    @endif

                    {{-- Actions --}}
                    @if($item->status === 'pending')
                        <div class="kllActions">
                            <form action="{{ route('kepsek.pengajuan-izin.setujui', $item->id) }}" method="POST" style="flex:1;" data-confirm="Setujui permohonan izin/sakit ini? Log presensi kehadiran tutor akan disinkronkan secara otomatis." data-confirm-title="Setujui Pengajuan Izin" data-confirm-type="success" data-confirm-btn="Ya, Setujui">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="profileBtnPrimary" style="width:100%; height:38px; font-size:12px; border-radius:10px; background:#16a34a;">
                                    <ion-icon name="checkmark-circle-outline"></ion-icon> Setujui
                                </button>
                            </form>
                            <form action="{{ route('kepsek.pengajuan-izin.tolak', $item->id) }}" method="POST" style="flex:1;" data-confirm="Apakah Anda yakin ingin menolak permohonan izin ini?" data-confirm-title="Tolak Pengajuan Izin" data-confirm-type="danger" data-confirm-btn="Ya, Tolak">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="profileBtnDanger" style="width:100%; height:38px; font-size:12px; border-radius:10px; background:#ef4444; color:#fff;">
                                    <ion-icon name="close-circle-outline"></ion-icon> Tolak
                                </button>
                            </form>
                        </div>
                    @else
                        <div style="margin-top: 8px; border-top: 1px dashed var(--border); padding-top: 8px; font-size: 11.5px; color: var(--muted); display: flex; align-items: center; gap: 6px;">
                            <ion-icon name="shield-checkmark-outline" style="color: #16a34a; font-size: 15px;"></ion-icon>
                            <span>Diverifikasi oleh <strong>{{ $item->verifikator->name ?? 'Kepala Sekolah' }}</strong> pada {{ $item->updated_at->translatedFormat('d M Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state-standard">
                <ion-icon name="document-text-outline" class="empty-icon"></ion-icon>
                <div class="empty-title">Tidak Ada Data Izin/Sakit</div>
                <div class="empty-desc">{{ request('cari') || request('status') || request('jenis') ? 'Tidak ada pengajuan yang cocok dengan filter pencarian.' : 'Belum ada pengajuan izin atau surat sakit dari tutor saat ini.' }}</div>
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
