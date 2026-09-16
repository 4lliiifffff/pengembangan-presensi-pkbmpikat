@extends('layouts.admin')

@section('content')

    <div class="pageHeaderRow header-actions-group justify-between mb-4">
        <div >
            <h2 class="m-0 text-xl font-extrabold text-dark">Master Kategori &amp; Tarif SK</h2>
            <p class="mt-1 text-sm text-muted">Kelola kategori pembelajaran, durasi acuan, status ABK, dan besaran tarif honor per pertemuan sesuai SK Kepala PKBM</p>
        </div>
        <div class="header-actions-group">
            <button type="button" onclick="bukaModalTambah()" class="btnPrimary px-3 py-2 text-sm d-inline-flex items-center gap-1">
                <ion-icon name="add-circle-outline" class="icon-sm"></ion-icon> Tambah Kategori SK
            </button>
        </div>
    </div>

    {{-- ── Grid / List Kategori Tutorial ── --}}
    <div class="table-wrapper-card">
        <div class="overflow-x-auto">
            <table class="table-modern">
                <thead >
                    <tr class="table-head-row">
                        <th class="table-col-num">#</th>
                        <th class="p-3">Nama Kategori SK</th>
                        <th class="p-3">Layanan &amp; Durasi</th>
                        <th class="p-3 text-center">Klasifikasi</th>
                        <th class="table-col-action p-3">Nominal Honor</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody >
                    @forelse($kategoriList as $k)
                        <tr class="table-body-row">
                            <td class="p-3 text-center font-bold text-muted">{{ $k->urutan ?: $loop->iteration }}</td>
                            <td class="p-3">
                                <div class="font-bold text-dark">{{ $k->nama_kategori }}</div>
                                <div class="text-xs text-muted">{{ $k->presensis_count }} sesi presensi tercatat</div>
                            </td>
                            <td class="p-3">
                                <div class="d-flex items-center gap-1">
                                    <span class="app-badge {{ $k->jenis_layanan === 'dl' ? 'badge-layanan-dl' : 'badge-layanan-komunitas' }}">
                                        {{ $k->jenis_layanan === 'dl' ? 'Distance Learning (DL)' : 'Tutorial Komunitas' }}
                                    </span>
                                    <span class="font-bold text-dark text-sm">{{ $k->durasi_jam }} Jam</span>
                                </div>
                            </td>
                            <td class="p-3 text-center">
                                <div class="d-flex gap-1 justify-center flex-wrap">
                                    @if($k->is_abk)
                                        <span class="app-badge badge-abk">ABK</span>
                                    @else
                                        <span class="app-badge badge-reguler">Reguler</span>
                                    @endif

                                    @if($k->is_gabungan)
                                        <span class="app-badge badge-layanan-gabungan">Rombel Gabungan</span>
                                    @endif
                                </div>
                            </td>
                            <td class="table-col-action p-3">
                                <span class="font-extrabold text-success text-md">{{ $k->formatted_nominal_honor }}</span>
                                <span class="text-xs text-muted d-block">/ pertemuan</span>
                            </td>
                            <td class="p-3 text-center">
                                <form method="POST" action="{{ route('admin.kategori-tutorial.toggleStatus', $k) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="Klik untuk ubah status" class="border-none bg-none cursor-pointer p-0">
                                        @if($k->is_aktif)
                                            <span class="app-badge badge-status-aktif">Aktif</span>
                                        @else
                                            <span class="app-badge badge-status-nonaktif">Nonaktif</span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td class="p-3 text-center">
                                <div class="d-flex gap-1 justify-center">
                                    <button type="button" onclick="bukaModalEdit({{ json_encode($k) }})" title="Edit Kategori" class="btnOutline text-xs rounded-md p-1">
                                        <ion-icon name="create-outline"></ion-icon> Edit
                                    </button>
                                    @if($k->presensis_count === 0)
                                        <form method="POST" action="{{ route('admin.kategori-tutorial.destroy', $k) }}" data-confirm="Apakah Anda yakin ingin menghapus kategori tutorial ini?" data-confirm-title="Hapus Kategori" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus Kategori" class="btnOutline text-xs rounded-md text-danger p-1 border-danger-light">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr >
                            <td colspan="7" class="text-center text-muted p-4">
                                Belum ada data kategori tutorial. Silakan klik tombol "Tambah Kategori SK" di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Modal Tambah / Edit Kategori ── --}}
    <div id="modalKategori" class="app-modal-backdrop">
        <div class="app-modal-card">
            <div class="app-modal-header">
                <h3 id="modalKategoriTitle" class="app-modal-title">Tambah Kategori SK</h3>
                <button type="button" onclick="tutupModal()" class="app-modal-close">&times;</button>
            </div>

            <form id="formKategori" method="POST" action="{{ route('admin.kategori-tutorial.store') }}">
                @csrf
                <div id="methodContainer"></div>

                <div class="form-field-wrapper">
                    <label class="form-field-label">Nama Kategori Pembelajaran: <span class="text-danger">*</span></label>
                    <input type="text" name="nama_kategori" id="inputNamaKategori" required placeholder="Contoh: Tutorial Komunitas 2 Jam" class="profileInput">
                </div>

                <div class="form-grid-responsive mb-4">
                    <div >
                        <label class="form-field-label">Jenis Layanan: <span class="text-danger">*</span></label>
                        <select name="jenis_layanan" id="inputJenisLayanan" required class="profileInput">
                            <option value="komunitas">Tutorial Komunitas</option>
                            <option value="dl">Distance Learning (DL)</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div >
                        <label class="form-field-label">Durasi Sesi (Jam): <span class="text-danger">*</span></label>
                        <input type="number" step="0.25" min="0.5" max="12" name="durasi_jam" id="inputDurasiJam" required placeholder="2.0" class="profileInput">
                    </div>
                </div>

                <div class="form-field-wrapper">
                    <label class="form-field-label">Nominal Honor per Pertemuan (Rp): <span class="text-danger">*</span></label>
                    <input type="number" step="1000" min="0" name="nominal_honor" id="inputNominalHonor" required placeholder="75000" class="profileInput">
                </div>

                <div class="form-grid-responsive mb-4">
                    <div class="checkbox-toggle-card">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_abk" id="inputIsAbk" value="1" class="w-auto">
                            Khusus Siswa ABK
                        </label>
                    </div>
                    <div class="checkbox-toggle-card">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_gabungan" id="inputIsGabungan" value="1" class="w-auto">
                            Rombel Gabungan
                        </label>
                    </div>
                </div>

                <div class="form-grid-responsive mb-4">
                    <div >
                        <label class="form-field-label">Urutan Tampilan:</label>
                        <input type="number" name="urutan" id="inputUrutan" min="0" value="0" class="profileInput">
                    </div>
                    <div class="checkbox-toggle-card self-end">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_aktif" id="inputIsAktif" value="1" checked class="w-auto">
                            Status Aktif
                        </label>
                    </div>
                </div>

                <div class="app-modal-footer">
                    <button type="button" onclick="tutupModal()" class="btnOutline px-3 py-2">Batal</button>
                    <button type="submit" id="btnSubmitModal" class="profileBtnPrimary w-auto px-4 py-2">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>

    <script >
        function bukaModalTambah() {
            document.getElementById('modalKategoriTitle').innerText = 'Tambah Kategori SK Baru';
            document.getElementById('formKategori').action = '{{ route("admin.kategori-tutorial.store") }}';
            document.getElementById('methodContainer').innerHTML = '';
            document.getElementById('btnSubmitModal').innerText = 'Simpan Kategori';

            document.getElementById('inputNamaKategori').value = '';
            document.getElementById('inputJenisLayanan').value = 'komunitas';
            document.getElementById('inputDurasiJam').value = '2.0';
            document.getElementById('inputNominalHonor').value = '75000';
            document.getElementById('inputIsAbk').checked = false;
            document.getElementById('inputIsGabungan').checked = false;
            document.getElementById('inputIsAktif').checked = true;
            document.getElementById('inputUrutan').value = '0';

            document.getElementById('modalKategori').style.display = 'flex';
        }

        function bukaModalEdit(data) {
            document.getElementById('modalKategoriTitle').innerText = 'Edit Kategori SK: ' + data.nama_kategori;
            document.getElementById('formKategori').action = '/admin/kategori-tutorial/' + data.id;
            document.getElementById('methodContainer').innerHTML = '@method("PUT")';
            document.getElementById('btnSubmitModal').innerText = 'Perbarui Kategori';

            document.getElementById('inputNamaKategori').value = data.nama_kategori;
            document.getElementById('inputJenisLayanan').value = data.jenis_layanan;
            document.getElementById('inputDurasiJam').value = data.durasi_jam;
            document.getElementById('inputNominalHonor').value = data.nominal_honor;
            document.getElementById('inputIsAbk').checked = Boolean(data.is_abk);
            document.getElementById('inputIsGabungan').checked = Boolean(data.is_gabungan);
            document.getElementById('inputIsAktif').checked = Boolean(data.is_aktif);
            document.getElementById('inputUrutan').value = data.urutan || 0;

            document.getElementById('modalKategori').style.display = 'flex';
        }

        function tutupModal() {
            document.getElementById('modalKategori').style.display = 'none';
        }
    </script>

@endsection
