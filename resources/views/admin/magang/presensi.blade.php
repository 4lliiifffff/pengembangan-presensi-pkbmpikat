@extends('layouts.admin')

@section('title', 'Monitoring Presensi Magang / PKL')

@section('content')

<div class="pageHeaderRow" style="flex-wrap:wrap;gap:10px;align-items:center;">
    <div>
        <h2 style="margin:0;">Monitoring Presensi Magang &amp; PKL</h2>
        <p style="margin:2px 0 0;font-size:12px;color:var(--muted);">Rekapitulasi log absensi masuk dan pulang mahasiswa/siswa magang</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('admin.magang.exportPdf', request()->all()) }}" class="btnPrimary" style="padding:8px 12px;background:#dc2626;font-size:12px;">
            <ion-icon name="document-text-outline"></ion-icon> Export PDF
        </a>
        <a href="{{ route('admin.magang.index') }}" class="btnOutline" style="padding:8px 12px;font-size:12px;">
            <ion-icon name="people-outline"></ion-icon> Data Magang
        </a>
    </div>
</div>

<!-- Statistik -->
<div class="statsRow" style="padding: 0 16px;">
    <div class="statBox dark">
        <ion-icon name="calendar-outline" style="font-size:24px;margin-bottom:4px;"></ion-icon>
        <h2>{{ $totalPresensi }}</h2>
        <div>TOTAL LOG</div>
    </div>
    <div class="statBox active">
        <ion-icon name="checkmark-done-circle-outline" style="font-size:24px;margin-bottom:4px;"></ion-icon>
        <h2>{{ $totalHadirLengkap }}</h2>
        <div>HADIR LENGKAP (PULANG)</div>
    </div>
    <div class="statBox" style="background:#fffbeb; border:1px solid #fde68a; color:#92400e;">
        <ion-icon name="time-outline" style="font-size:24px;margin-bottom:4px; color:#d97706;"></ion-icon>
        <h2 style="color:#92400e;">{{ $totalSedangProses }}</h2>
        <div style="color:#b45309;">SEDANG BERLANGSUNG</div>
    </div>
</div>

<!-- Filter Box -->
<div class="filterBox" style="margin:16px; padding:16px; background:var(--card,#fff); border-radius:16px; border:1px solid var(--border,#e2e8f0);">
    <form method="GET" action="{{ route('admin.magang.presensi') }}" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; align-items:end;">
        <div>
            <label style="font-size:12px; font-weight:700; color:var(--muted); display:block; margin-bottom:4px;">Dari Tanggal:</label>
            <input type="date" name="start_date" value="{{ $startDateStr }}" class="input" style="width:100%;">
        </div>
        <div>
            <label style="font-size:12px; font-weight:700; color:var(--muted); display:block; margin-bottom:4px;">Sampai Tanggal:</label>
            <input type="date" name="end_date" value="{{ $endDateStr }}" class="input" style="width:100%;">
        </div>
        <div>
            <label style="font-size:12px; font-weight:700; color:var(--muted); display:block; margin-bottom:4px;">Peserta Magang:</label>
            <select name="user_id" class="input" style="width:100%;">
                <option value="">-- Semua Peserta --</option>
                @foreach($allMagangUsers as $u)
                    <option value="{{ $u->id }}" {{ $magangUserId == $u->id ? 'selected' : '' }}>
                        {{ $u->nama_lengkap ?? $u->name }} ({{ $u->magang?->asal_instansi ?: $u->nik }})
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="font-size:12px; font-weight:700; color:var(--muted); display:block; margin-bottom:4px;">Status Kehadiran:</label>
            <select name="status" class="input" style="width:100%;">
                <option value="">-- Semua Status --</option>
                <option value="hadir" {{ $statusFilter === 'hadir' ? 'selected' : '' }}>Hadir Lengkap</option>
                <option value="proses" {{ $statusFilter === 'proses' ? 'selected' : '' }}>Sedang Berlangsung</option>
            </select>
        </div>
        <div style="display:flex; gap:6px;">
            <button type="submit" class="btnPrimary" style="flex:1; padding:9px;">Terapkan</button>
            <a href="{{ route('admin.magang.presensi') }}" class="btnOutline" style="padding:9px;">Reset</a>
        </div>
    </form>
</div>

<!-- Tabel Log Presensi -->
<div class="tableCard" style="margin:16px; border-radius:16px; overflow:hidden; background:var(--card,#fff); border:1px solid var(--border,#e2e8f0);">
    <div class="tableResponsive" style="overflow-x:auto;">
        <table class="table" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--border,#e2e8f0); background:var(--card-alt,#f8fafc); text-align:left;">
                    <th style="padding:12px 16px; font-size:12px; font-weight:700;">Tanggal</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700;">Peserta Magang</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700;">Absen Masuk</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700;">Absen Pulang</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700;">Durasi</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($presensis as $p)
                    @php
                        $u = $p->user;
                        $tgl = \Carbon\Carbon::parse($p->tgl_presensi)->translatedFormat('d M Y');
                        $hari = \Carbon\Carbon::parse($p->tgl_presensi)->translatedFormat('l');
                        $masuk = $p->jam_mulai ? substr((string)$p->jam_mulai, 0, 5) : '—';
                        $pulang = $p->jam_selesai ? substr((string)$p->jam_selesai, 0, 5) : '—';
                        
                        $durasi = '—';
                        if ($p->jam_mulai && $p->jam_selesai) {
                            try {
                                $dtMulai = \Carbon\Carbon::parse($p->tgl_presensi . ' ' . $p->jam_mulai);
                                $dtSelesai = \Carbon\Carbon::parse($p->tgl_presensi . ' ' . $p->jam_selesai);
                                $diffMin = $dtMulai->diffInMinutes($dtSelesai);
                                $durasi = floor($diffMin / 60) . 'j ' . ($diffMin % 60) . 'm';
                            } catch (\Throwable) {}
                        }
                    @endphp
                    <tr style="border-bottom:1px solid var(--border,#f1f5f9);">
                        <td style="padding:12px 16px; white-space:nowrap;">
                            <div style="font-weight:700; font-size:13px; color:var(--text,#0f172a);">{{ $tgl }}</div>
                            <div style="font-size:11px; color:var(--muted,#64748b);">{{ $hari }}</div>
                        </td>
                        <td style="padding:12px 16px;">
                            <div style="font-weight:700; font-size:13px; color:var(--text,#0f172a);">{{ $u->nama_lengkap ?? ($u->name ?? 'Magang') }}</div>
                            <div style="font-size:11px; color:var(--muted,#64748b);">{{ $u->magang?->asal_instansi ?: '-' }} (NIM: {{ $u->magang?->nim_nisn ?: $u->nik }})</div>
                        </td>
                        <td style="padding:12px 16px;">
                            <div style="font-weight:700; font-size:13px; color:#0b5ed7;">{{ $masuk }} WIB</div>
                            @if($p->foto_mulai)
                                <a href="{{ asset($p->foto_mulai) }}" target="_blank" style="font-size:11px; color:#0b5ed7; display:inline-flex; align-items:center; gap:2px; margin-top:2px;">
                                    <ion-icon name="image-outline"></ion-icon> Foto
                                </a>
                            @endif
                        </td>
                        <td style="padding:12px 16px;">
                            <div style="font-weight:700; font-size:13px; color:{{ $p->jam_selesai ? '#16a34a' : '#64748b' }};">{{ $pulang }} {{ $p->jam_selesai ? 'WIB' : '' }}</div>
                            @if($p->foto_selesai)
                                <a href="{{ asset($p->foto_selesai) }}" target="_blank" style="font-size:11px; color:#16a34a; display:inline-flex; align-items:center; gap:2px; margin-top:2px;">
                                    <ion-icon name="image-outline"></ion-icon> Foto
                                </a>
                            @endif
                        </td>
                        <td style="padding:12px 16px; font-size:12px; font-weight:600;">
                            {{ $durasi }}
                        </td>
                        <td style="padding:12px 16px;">
                            @if($p->jam_selesai)
                                <span class="badge" style="background:#dcfce7; color:#15803d; padding:4px 8px; border-radius:6px; font-size:11px; font-weight:700;">Hadir Lengkap</span>
                            @else
                                <span class="badge" style="background:#fef3c7; color:#92400e; padding:4px 8px; border-radius:6px; font-size:11px; font-weight:700;">Sedang Berjalan</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:32px; text-align:center; color:var(--muted,#64748b);">
                            <ion-icon name="calendar-outline" style="font-size:36px; opacity:0.5; margin-bottom:8px;"></ion-icon>
                            <div>Tidak ada riwayat presensi magang pada periode filter ini.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($presensis->hasPages())
        <div style="padding:16px; border-top:1px solid var(--border,#e2e8f0);">
            {{ $presensis->links() }}
        </div>
    @endif
</div>

@endsection
