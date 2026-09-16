@extends('layouts.admin')

@section('content')
<div class="laporanPageWrapper">
    <div class="laporanHeaderCard">
        <div class="laporanHeaderMain">
            <div class="laporanTitleBlock">
                <span class="laporanSubtitle">MODUL PESERTA DIDIK</span>
                <h1 class="laporanMainTitle">Detail Siswa: {{ $siswa->nama_siswa }}</h1>
                <p class="laporanDesc">Informasi lengkap data peserta didik, wali murid, dan rombongan belajar</p>
            </div>
            <div class="laporanHeaderActions">
                <a href="{{ route('admin.siswa.index') }}" class="btnOutline">
                    Kembali ke Daftar
                </a>
                <a href="{{ route('admin.siswa.edit', $siswa) }}" class="profileBtnPrimary">
                    Edit Data Siswa
                </a>
            </div>
        </div>
    </div>

    @php
        $statusBadgeStyle = match($siswa->status_siswa) {
            'alumni' => 'background:#e0e7ff;color:#3730a3;border:1px solid #c7d2fe;',
            'cuti' => 'background:#fef3c7;color:#b45309;border:1px solid #fde68a;',
            'nonaktif' => 'background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;',
            default => 'background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;',
        };
    @endphp

    <div class="laporanFilterCard" style="max-width:900px;margin:0 auto 32px;padding:24px 28px;">
        <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:18px;border-bottom:1px solid var(--border);flex-wrap:wrap;gap:12px;">
            <div>
                <span class="filterFieldLabel" style="display:block;margin-bottom:4px;">STATUS & KATEGORI</span>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span style="display:inline-block;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:700;{{ $statusBadgeStyle }}">
                        {{ $siswa->status_label }}
                    </span>
                    @if($siswa->is_abk)
                        <span style="display:inline-block;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:700;background:#fef3c7;color:#b45309;border:1px solid #fde68a;">
                            Anak Berkebutuhan Khusus (ABK)
                        </span>
                    @else
                        <span style="display:inline-block;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:700;background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;">
                            Reguler
                        </span>
                    @endif
                </div>
            </div>
            <div style="text-align:right;">
                <span class="filterFieldLabel" style="display:block;margin-bottom:4px;">NOMOR ABSEN / NIS</span>
                <span style="font-size:16px;font-weight:800;color:var(--text);">{{ $siswa->no_absen ?? '-' }}</span>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:16px;margin-top:20px;">
            <div style="background:var(--card-alt,#f8fafc);padding:14px 16px;border-radius:10px;border:1px solid var(--border);">
                <div class="filterFieldLabel" style="margin-bottom:4px;">NAMA LENGKAP SISWA</div>
                <div style="font-size:14px;font-weight:700;color:var(--text);">{{ $siswa->nama_siswa }}</div>
            </div>

            <div style="background:var(--card-alt,#f8fafc);padding:14px 16px;border-radius:10px;border:1px solid var(--border);">
                <div class="filterFieldLabel" style="margin-bottom:4px;">KELAS / ROMBEL</div>
                <div style="font-size:14px;font-weight:700;color:var(--text);">{{ $siswa->relKelas->nama_kelas ?? '-' }}</div>
            </div>

            <div style="background:var(--card-alt,#f8fafc);padding:14px 16px;border-radius:10px;border:1px solid var(--border);">
                <div class="filterFieldLabel" style="margin-bottom:4px;">JENJANG PAKET</div>
                <div style="font-size:14px;font-weight:700;color:var(--text);">{{ $siswa->jenjang_paket_label ?? '-' }}</div>
            </div>

            <div style="background:var(--card-alt,#f8fafc);padding:14px 16px;border-radius:10px;border:1px solid var(--border);">
                <div class="filterFieldLabel" style="margin-bottom:4px;">TUTOR PEMBIMBING</div>
                <div style="font-size:14px;font-weight:700;color:var(--text);">{{ $siswa->tutor->nama_lengkap ?? 'Belum Ditentukan' }}</div>
            </div>

            <div style="background:var(--card-alt,#f8fafc);padding:14px 16px;border-radius:10px;border:1px solid var(--border);">
                <div class="filterFieldLabel" style="margin-bottom:4px;">KONTAK NO HP SISWA</div>
                <div style="font-size:14px;font-weight:700;color:var(--text);">{{ $siswa->no_hp ?? '-' }}</div>
            </div>

            <div style="background:var(--card-alt,#f8fafc);padding:14px 16px;border-radius:10px;border:1px solid var(--border);">
                <div class="filterFieldLabel" style="margin-bottom:4px;">NAMA ORANG TUA / WALI</div>
                <div style="font-size:14px;font-weight:700;color:var(--text);">{{ $siswa->nama_wali ?? '-' }}</div>
            </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:24px;padding-top:18px;border-top:1px solid var(--border);justify-content:space-between;flex-wrap:wrap;align-items:center;">
            <a class="btnOutline" href="{{ route('admin.siswa.edit', $siswa) }}">
                Edit Data
            </a>

            <form method="POST" action="{{ route('admin.siswa.destroy', $siswa) }}" data-confirm="Arsipkan data siswa ini (Soft Delete)? Seluruh riwayat presensi tetap aman." data-confirm-title="Arsipkan Siswa" data-confirm-type="danger" data-confirm-btn="Ya, Arsipkan">
                @csrf
                @method('DELETE')
                <button type="submit" class="profileBtnDanger cursor-pointer">
                    Arsipkan Siswa
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

