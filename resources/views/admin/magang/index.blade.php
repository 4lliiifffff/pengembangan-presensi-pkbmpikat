@extends('layouts.admin')

@section('title', 'Data Peserta Magang & PKL')

@section('content')

<div class="pageHeaderRow" style="flex-wrap:wrap;gap:10px;align-items:center;">
    <div>
        <h2 style="margin:0;">Data Peserta Magang &amp; PKL</h2>
        <p style="margin:2px 0 0;font-size:12px;color:var(--muted);">Kelola data mahasiswa/siswa magang, masa periode, dan akun akses sistem</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('admin.magang.presensi') }}" class="btnOutline" style="padding:8px 12px;font-size:12px;border-color:var(--primary);color:var(--primary);">
            <ion-icon name="calendar-outline"></ion-icon> Monitoring Presensi
        </a>
        <a href="{{ route('admin.magang.create') }}" class="btnPrimary" style="padding:8px 12px;background:var(--blue-gradient);font-size:12px;">
            <ion-icon name="add-outline"></ion-icon> Tambah Peserta Magang
        </a>
    </div>
</div>

<!-- Statistik -->
<div class="statsRow" style="padding: 0 16px;">
    <div class="statBox dark">
        <ion-icon name="school-outline" style="font-size:24px;margin-bottom:4px;"></ion-icon>
        <h2>{{ $total }}</h2>
        <div>TOTAL MAGANG</div>
    </div>
    <div class="statBox active">
        <ion-icon name="checkmark-circle-outline" style="font-size:24px;margin-bottom:4px;"></ion-icon>
        <h2>{{ $aktif }}</h2>
        <div>AKTIF</div>
    </div>
    <div class="statBox inactive">
        <ion-icon name="pause-circle-outline" style="font-size:24px;margin-bottom:4px;"></ion-icon>
        <h2>{{ $nonaktif }}</h2>
        <div>SELESAI / NONAKTIF</div>
    </div>
</div>

<!-- Filter Search -->
<div class="searchRow" style="padding: 0 16px; margin-top: 16px;">
    <form method="GET" action="{{ route('admin.magang.index') }}" style="display:flex; gap:10px; width:100%; flex-wrap:wrap;">
        <div style="flex:1; min-width:220px; position:relative;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama, NIM, instansi, email..." class="input" style="width:100%; padding-left:36px;">
            <ion-icon name="search-outline" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:18px; color:var(--muted);"></ion-icon>
        </div>
        <select name="status" class="input" style="width:auto;" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="1" {{ ($status === '1') ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ ($status === '0') ? 'selected' : '' }}>Nonaktif / Selesai</option>
        </select>
        <button type="submit" class="btnPrimary" style="padding:9px 16px;">Filter</button>
        @if($search || $status !== null)
            <a href="{{ route('admin.magang.index') }}" class="btnOutline" style="padding:9px 12px;">Reset</a>
        @endif
    </form>
</div>

<!-- Tabel Magang -->
<div class="tableCard" style="margin: 16px; border-radius:16px; overflow:hidden; background:var(--card,#fff); border:1px solid var(--border,#e2e8f0);">
    <div class="tableResponsive" style="overflow-x:auto;">
        <table class="table" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--border,#e2e8f0); background:var(--card-alt,#f8fafc); text-align:left;">
                    <th style="padding:12px 16px; font-size:12px; font-weight:700;">Peserta Magang</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700;">Asal Instansi & Jurusan</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700;">Periode Magang</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700;">No. WhatsApp</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700;">Status</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($magangs as $m)
                    @php
                        $detail = $m->magang;
                        $tglMulai = $detail?->tgl_mulai ? \Carbon\Carbon::parse($detail->tgl_mulai)->format('d/m/Y') : '-';
                        $tglSelesai = $detail?->tgl_selesai ? \Carbon\Carbon::parse($detail->tgl_selesai)->format('d/m/Y') : '-';
                        $avatarUrl = $m->foto ? (str_starts_with($m->foto, 'uploads/') ? asset($m->foto) : asset('storage/' . $m->foto)) : null;
                    @endphp
                    <tr style="border-bottom:1px solid var(--border,#f1f5f9);">
                        <td style="padding:12px 16px;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                @if($avatarUrl)
                                    <img src="{{ $avatarUrl }}" style="width:36px; height:36px; border-radius:50%; object-fit:cover;">
                                @else
                                    <div style="width:36px; height:36px; border-radius:50%; background:rgba(11,94,215,0.1); color:#0B5ED7; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px;">
                                        {{ strtoupper(substr($m->nama_lengkap ?? $m->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div style="font-weight:700; font-size:13px; color:var(--text,#0f172a);">{{ $m->nama_lengkap ?? $m->name }}</div>
                                    <div style="font-size:11px; color:var(--muted,#64748b);">NIK: {{ $m->nik }} • {{ $m->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:12px 16px;">
                            <div style="font-weight:600; font-size:13px; color:var(--text,#0f172a);">{{ $detail?->asal_instansi ?: '-' }}</div>
                            <div style="font-size:11px; color:var(--muted,#64748b);">{{ $detail?->jurusan ?: '-' }} (NIM: {{ $detail?->nim_nisn ?: '-' }})</div>
                        </td>
                        <td style="padding:12px 16px;">
                            <div style="font-size:12px; font-weight:600; color:var(--text,#0f172a);">{{ $tglMulai }} s/d {{ $tglSelesai }}</div>
                        </td>
                        <td style="padding:12px 16px; font-size:12px;">
                            {{ $m->no_hp ?: '-' }}
                        </td>
                        <td style="padding:12px 16px;">
                            @if($m->is_active)
                                <span class="badge" style="background:#dcfce7; color:#15803d; padding:4px 8px; border-radius:6px; font-size:11px; font-weight:700;">Aktif</span>
                            @else
                                <span class="badge" style="background:#f1f5f9; color:#64748b; padding:4px 8px; border-radius:6px; font-size:11px; font-weight:700;">Nonaktif</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px; text-align:right;">
                            <div style="display:flex; justify-content:flex-end; gap:6px;">
                                <a href="{{ route('admin.magang.edit', $m->id) }}" class="btnOutline" style="padding:6px 10px; font-size:12px;" title="Edit">
                                    <ion-icon name="create-outline"></ion-icon>
                                </a>
                                <form action="{{ route('admin.magang.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data peserta magang ini beserta seluruh riwayat presensinya?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btnOutline" style="padding:6px 10px; font-size:12px; color:#dc2626; border-color:#fca5a5;" title="Hapus">
                                        <ion-icon name="trash-outline"></ion-icon>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:32px; text-align:center; color:var(--muted,#64748b);">
                            <ion-icon name="school-outline" style="font-size:36px; opacity:0.5; margin-bottom:8px;"></ion-icon>
                            <div>Belum ada data peserta magang/PKL.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($magangs->hasPages())
        <div style="padding:16px; border-top:1px solid var(--border,#e2e8f0);">
            {{ $magangs->links() }}
        </div>
    @endif
</div>

@endsection
