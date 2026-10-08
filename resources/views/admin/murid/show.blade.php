@extends('layouts.admin')

@section('content')
<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">MODUL MURID</div>
                <h1 class="laporanHeaderTitle">Detail Murid: {{ $murid->nama_murid }}</h1>
                <div class="laporanHeaderSub">Informasi lengkap data murid, wali murid, dan rombongan belajar</div>
            </div>
            <div class="laporanHeaderActions header-actions-group">
                <a href="{{ route('admin.murid.index') }}" class="btnOutline">
                    Kembali ke Daftar
                </a>
                <a href="{{ route('admin.murid.edit', $murid) }}" class="profileBtnPrimary w-auto px-4">
                    Edit Data Murid
                </a>
            </div>
        </div>
    </div>

    <div class="form-card-container p-4">
        <div class="d-flex justify-between items-center flex-wrap gap-3 border-b-base pb-4">
            <div>
                <span class="form-field-label mb-1">STATUS & KATEGORI</span>
                <div class="d-flex items-center gap-2 flex-wrap">
                    <span class="app-badge badge-lg badge-status-{{ $murid->status_murid }}">
                        {{ $murid->status_label }}
                    </span>
                    @if($murid->is_abk)
                        <span class="app-badge badge-lg badge-abk">
                            Murid Berkebutuhan Khusus (ABK)
                        </span>
                    @else
                        <span class="app-badge badge-lg badge-reguler">
                            Reguler
                        </span>
                    @endif
                </div>
            </div>
            <div class="text-right">
                <span class="form-field-label mb-1">NOMOR ABSEN / NIS</span>
                <span class="icon-sm font-extrabold text-dark">{{ $murid->no_absen ?? '-' }}</span>
            </div>
        </div>

        <div class="form-grid-responsive mt-4">
            <div class="card-sub-alt">
                <div class="form-field-label mb-1">NAMA LENGKAP MURID</div>
                <div class="text-md font-bold text-dark">{{ $murid->nama_murid }}</div>
            </div>

            <div class="card-sub-alt">
                <div class="form-field-label mb-1">KELAS / ROMBEL</div>
                <div class="text-md font-bold text-dark">{{ $murid->relKelas->nama_kelas ?? '-' }}</div>
            </div>

            <div class="card-sub-alt">
                <div class="form-field-label mb-1">JENJANG PAKET</div>
                <div class="text-md font-bold text-dark">{{ $murid->jenjang_paket_label ?? '-' }}</div>
            </div>

            <div class="card-sub-alt">
                <div class="form-field-label mb-1">TUTOR PEMBIMBING</div>
                <div class="text-md font-bold text-dark">{{ $murid->tutor->nama_lengkap ?? 'Belum Ditentukan' }}</div>
            </div>

            <div class="card-sub-alt">
                <div class="form-field-label mb-1">KONTAK NO HP MURID</div>
                <div class="text-md font-bold text-dark">{{ $murid->no_hp ?? '-' }}</div>
            </div>

            <div class="card-sub-alt">
                <div class="form-field-label mb-1">NAMA ORANG TUA / WALI</div>
                <div class="text-md font-bold text-dark">{{ $murid->nama_wali ?? '-' }}</div>
            </div>
        </div>

        {{-- ── Akun Login Portal Murid (Tabel Users) ── --}}
        <div class="mt-4 p-4 border border-dashed rounded-xl bg-slate-50">
            <div class="d-flex justify-between items-center flex-wrap gap-2 mb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 m-0">AKUN LOGIN MURID (PORTAL MANDIRI)</h3>
                    <p class="text-xs text-muted m-0">Kredensial akun untuk murid login presensi mandiri dan melihat riwayat kehadiran</p>
                </div>
                @if($murid->user)
                    <form method="POST" action="{{ route('admin.murid.resetPassword', $murid) }}" data-confirm="Reset password akun murid ini menjadi 'password123'?" data-confirm-title="Reset Password Murid" data-confirm-type="warning" data-confirm-btn="Ya, Reset Password">
                        @csrf
                        <button type="submit" class="btnOutline btn-sm text-xs font-semibold">
                            Reset Password Default
                        </button>
                    </form>
                @endif
            </div>

            @if($murid->user)
                <div class="form-grid-responsive">
                    <div class="card-sub-alt bg-white">
                        <div class="form-field-label mb-1">EMAIL LOGIN</div>
                        <div class="text-sm font-bold text-dark font-mono">{{ $murid->user->email }}</div>
                    </div>
                    <div class="card-sub-alt bg-white">
                        <div class="form-field-label mb-1">NIK / USERNAME ALTERNATIF</div>
                        <div class="text-sm font-bold text-dark font-mono">{{ $murid->user->nik ?? '-' }}</div>
                    </div>
                    <div class="card-sub-alt bg-white">
                        <div class="form-field-label mb-1">STATUS AKUN LOGIN</div>
                        <div>
                            @if($murid->user->is_active)
                                <span class="app-badge badge-sm badge-reguler">Aktif (Dapat Login)</span>
                            @else
                                <span class="app-badge badge-sm badge-status-nonaktif">Nonaktif (Akses Diblokir)</span>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-warning text-xs mb-0">
                    Murid ini belum memiliki akun pengguna yang terhubung. Akun akan otomatis terbuat saat data murid disimpan kembali.
                </div>
            @endif
        </div>

        <div class="form-action-footer">
            <a class="btnOutline" href="{{ route('admin.murid.edit', $murid) }}">
                Edit Data
            </a>

            <form method="POST" action="{{ route('admin.murid.destroy', $murid) }}" data-confirm="Arsipkan data murid ini (Soft Delete)? Seluruh riwayat presensi tetap aman." data-confirm-title="Arsipkan Murid" data-confirm-type="danger" data-confirm-btn="Ya, Arsipkan">
                @csrf
                @method('DELETE')
                <button type="submit" class="profileBtnDanger cursor-pointer w-auto px-4 text-md">
                    Arsipkan Murid
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
