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
            <div class="laporanHeaderActions header-actions-group">
                <a href="{{ route('admin.siswa.index') }}" class="btnOutline">
                    Kembali ke Daftar
                </a>
                <a href="{{ route('admin.siswa.edit', $siswa) }}" class="profileBtnPrimary" style="width: auto; padding: 0 20px;">
                    Edit Data Siswa
                </a>
            </div>
        </div>
    </div>

    <div class="form-card-container" style="padding: 24px 28px;">
        <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:18px; border-bottom:1px solid var(--border); flex-wrap:wrap; gap:12px;">
            <div>
                <span class="form-field-label" style="margin-bottom:4px;">STATUS & KATEGORI</span>
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <span class="app-badge badge-lg badge-status-{{ $siswa->status_siswa }}">
                        {{ $siswa->status_label }}
                    </span>
                    @if($siswa->is_abk)
                        <span class="app-badge badge-lg badge-abk">
                            Anak Berkebutuhan Khusus (ABK)
                        </span>
                    @else
                        <span class="app-badge badge-lg badge-reguler">
                            Reguler
                        </span>
                    @endif
                </div>
            </div>
            <div style="text-align:right;">
                <span class="form-field-label" style="margin-bottom:4px;">NOMOR ABSEN / NIS</span>
                <span style="font-size:16px; font-weight:800; color:var(--text);">{{ $siswa->no_absen ?? '-' }}</span>
            </div>
        </div>

        <div class="form-grid-responsive" style="margin-top: 20px;">
            <div style="background:var(--card-alt, #f8fafc); padding:14px 16px; border-radius:10px; border:1px solid var(--border);">
                <div class="form-field-label" style="margin-bottom:4px;">NAMA LENGKAP SISWA</div>
                <div style="font-size:14px; font-weight:700; color:var(--text);">{{ $siswa->nama_siswa }}</div>
            </div>

            <div style="background:var(--card-alt, #f8fafc); padding:14px 16px; border-radius:10px; border:1px solid var(--border);">
                <div class="form-field-label" style="margin-bottom:4px;">KELAS / ROMBEL</div>
                <div style="font-size:14px; font-weight:700; color:var(--text);">{{ $siswa->relKelas->nama_kelas ?? '-' }}</div>
            </div>

            <div style="background:var(--card-alt, #f8fafc); padding:14px 16px; border-radius:10px; border:1px solid var(--border);">
                <div class="form-field-label" style="margin-bottom:4px;">JENJANG PAKET</div>
                <div style="font-size:14px; font-weight:700; color:var(--text);">{{ $siswa->jenjang_paket_label ?? '-' }}</div>
            </div>

            <div style="background:var(--card-alt, #f8fafc); padding:14px 16px; border-radius:10px; border:1px solid var(--border);">
                <div class="form-field-label" style="margin-bottom:4px;">TUTOR PEMBIMBING</div>
                <div style="font-size:14px; font-weight:700; color:var(--text);">{{ $siswa->tutor->nama_lengkap ?? 'Belum Ditentukan' }}</div>
            </div>

            <div style="background:var(--card-alt, #f8fafc); padding:14px 16px; border-radius:10px; border:1px solid var(--border);">
                <div class="form-field-label" style="margin-bottom:4px;">KONTAK NO HP SISWA</div>
                <div style="font-size:14px; font-weight:700; color:var(--text);">{{ $siswa->no_hp ?? '-' }}</div>
            </div>

            <div style="background:var(--card-alt, #f8fafc); padding:14px 16px; border-radius:10px; border:1px solid var(--border);">
                <div class="form-field-label" style="margin-bottom:4px;">NAMA ORANG TUA / WALI</div>
                <div style="font-size:14px; font-weight:700; color:var(--text);">{{ $siswa->nama_wali ?? '-' }}</div>
            </div>
        </div>

        <div class="form-action-footer">
            <a class="btnOutline" href="{{ route('admin.siswa.edit', $siswa) }}">
                Edit Data
            </a>

            <form method="POST" action="{{ route('admin.siswa.destroy', $siswa) }}" data-confirm="Arsipkan data siswa ini (Soft Delete)? Seluruh riwayat presensi tetap aman." data-confirm-title="Arsipkan Siswa" data-confirm-type="danger" data-confirm-btn="Ya, Arsipkan">
                @csrf
                @method('DELETE')
                <button type="submit" class="profileBtnDanger cursor-pointer" style="width: auto; padding: 0 18px; height: 42px;">
                    Arsipkan Siswa
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

