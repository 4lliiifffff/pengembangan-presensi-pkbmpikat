@extends('layout.admin')

@section('content')
    <div class="pageHeaderRow">
        <h2>Detail Siswa</h2>
        <a class="btnOutline" href="{{ route('admin.siswa.index') }}">Kembali</a>
    </div>

    <div class="formCard">
        <div style="display:flex;align-items:center;gap:12px;">
            @php
                $initial = strtoupper(substr((string) ($siswa->nama_siswa ?? ''), 0, 1));
            @endphp
            <div class="activityAvatar" style="width:54px;height:54px;font-size:16px;">{{ $initial }}</div>
            <div>
                <div style="font-weight:1000;font-size:16px;">{{ $siswa->nama_siswa }}</div>
                <div style="color: var(--muted);font-weight:900;font-size:12px;margin-top:4px;">
                    No Absen: {{ $siswa->no_absen }}
                </div>
            </div>
        </div>

        <div style="margin-top:14px;">
            <div class="fieldLabel">Kelas</div>
            <div style="font-weight:900;">{{ $siswa->relKelas->nama_kelas ?? '-' }}</div>

            <div class="fieldLabel" style="margin-top:12px;">Tutor Pembimbing</div>
            <div style="color: var(--text);font-weight:900;">{{ $siswa->tutor->nama_lengkap ?? 'Belum Ditentukan' }}</div>

            <div class="fieldLabel" style="margin-top:12px;">No HP</div>
            <div style="color: var(--text);font-weight:900;">{{ $siswa->no_hp }}</div>

            <div class="fieldLabel" style="margin-top:12px;">Nama Wali</div>
            <div style="color: var(--text);font-weight:900;">{{ $siswa->nama_wali }}</div>
        </div>

        <div style="display:flex;gap:10px;margin-top:16px;flex-wrap:wrap;">
            <a class="smallBtn edit" href="{{ route('admin.siswa.edit', $siswa) }}">
                <ion-icon name="create-outline"></ion-icon>
                Edit
            </a>
            <form method="POST" action="{{ route('admin.siswa.destroy', $siswa) }}" onsubmit="return confirm('Yakin hapus siswa ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="smallBtn delete" style="cursor:pointer;">
                    <ion-icon name="trash-outline"></ion-icon>
                    Hapus
                </button>
            </form>
        </div>
    </div>
@endsection

