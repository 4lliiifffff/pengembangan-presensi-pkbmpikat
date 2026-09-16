@extends('layouts.admin')

@section('content')
    <div class="pageHeaderRow">
        <h2>Data Kelas</h2>
    </div>

    <div class="siswaGrid">
        @forelse($kelas as $k)
            <div class="siswaCard">
                <div class="siswaTop">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="activityAvatar" class="activityAvatar">
                            {{ strtoupper(substr((string) $k->nama_kelas, 0, 1)) }}
                        </div>
                        <div>
                            <div class="siswaName">{{ $k->nama_kelas }}</div>
                            <div class="siswaMeta">Total siswa: {{ $k->siswas_count }}</div>
                        </div>
                    </div>
                </div>

                <div class="siswaActions">
                    <a class="smallBtn edit" href="{{ route('admin.kelas.edit', $k) }}">
                        <ion-icon name="create-outline"></ion-icon>
                        Edit
                    </a>

                    <form method="POST" action="{{ route('admin.kelas.destroy', $k) }}" data-confirm="Apakah Anda yakin ingin menghapus kelas ini?" data-confirm-title="Hapus Kelas" data-confirm-type="danger" data-confirm-btn="Ya, Hapus">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="smallBtn delete" style="cursor:pointer;">
                            <ion-icon name="trash-outline"></ion-icon>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="emptyState">Belum ada data kelas.</div>
        @endforelse
    </div>

    <div style="padding:0 16px 30px;">
        {{ $kelas->links() }}
    </div>

    <a href="{{ route('admin.kelas.create') }}" class="fabAdd" aria-label="Tambah Kelas">
        <ion-icon name="add-outline"></ion-icon>
    </a>
@endsection
