@extends('layouts.admin')

@section('content')

    <div class="pageHeaderRow header-actions-group" style="justify-content: space-between; margin-bottom: 18px;">
        <div>
            <h2 style="margin:0;font-size:20px;font-weight:800;color:var(--text);">Master Kategori &amp; Tarif SK</h2>
            <p style="margin:2px 0 0;font-size:12px;color:var(--muted);">Kelola kategori pembelajaran, durasi acuan, status ABK, dan besaran tarif honor per pertemuan sesuai SK Kepala PKBM</p>
        </div>
        <div class="header-actions-group">
            <button type="button" onclick="bukaModalTambah()" class="btnPrimary" style="padding:8px 14px;background:var(--blue-gradient);font-size:12px;display:inline-flex;align-items:center;gap:6px;">
                <ion-icon name="add-circle-outline" style="font-size:16px;"></ion-icon> Tambah Kategori SK
            </button>
        </div>
    </div>

    {{-- ── Grid / List Kategori Tutorial ── --}}
    <div style="background:var(--card,#fff);border-radius:18px;border:1px solid var(--border,#e2e8f0);overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.03);">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;text-align:left;">
                <thead>
                    <tr style="background:var(--card-alt,#f8fafc);border-bottom:1px solid var(--border,#e2e8f0);color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">
                        <th style="padding:14px 16px;width:50px;text-align:center;">#</th>
                        <th style="padding:14px 16px;">Nama Kategori SK</th>
                        <th style="padding:14px 16px;">Layanan &amp; Durasi</th>
                        <th style="padding:14px 16px;text-align:center;">Klasifikasi</th>
                        <th style="padding:14px 16px;text-align:right;">Nominal Honor</th>
                        <th style="padding:14px 16px;text-align:center;">Status</th>
                        <th style="padding:14px 16px;text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategoriList as $k)
                        <tr style="border-bottom:1px solid var(--border,#e2e8f0);">
                            <td style="padding:14px 16px;text-align:center;font-weight:700;color:var(--muted);">{{ $k->urutan ?: $loop->iteration }}</td>
                            <td style="padding:14px 16px;">
                                <div style="font-weight:700;color:var(--text);">{{ $k->nama_kategori }}</div>
                                <div style="font-size:11px;color:var(--muted);">{{ $k->presensis_count }} sesi presensi tercatat</div>
                            </td>
                            <td style="padding:14px 16px;">
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span class="app-badge {{ $k->jenis_layanan === 'dl' ? 'badge-layanan-dl' : 'badge-layanan-komunitas' }}">
                                        {{ $k->jenis_layanan === 'dl' ? 'Distance Learning (DL)' : 'Tutorial Komunitas' }}
                                    </span>
                                    <span style="font-weight:700;color:var(--text);font-size:12px;">{{ $k->durasi_jam }} Jam</span>
                                </div>
                            </td>
                            <td style="padding:14px 16px;text-align:center;">
                                <div style="display:flex;gap:4px;justify-content:center;flex-wrap:wrap;">
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
                            <td style="padding:14px 16px;text-align:right;">
                                <span style="font-weight:800;color:#16a34a;font-size:14px;">{{ $k->formatted_nominal_honor }}</span>
                                <span style="font-size:11px;color:var(--muted);display:block;">/ pertemuan</span>
                            </td>
                            <td style="padding:14px 16px;text-align:center;">
                                <form method="POST" action="{{ route('admin.kategori-tutorial.toggleStatus', $k) }}" style="display:inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="Klik untuk ubah status" style="border:none;background:none;cursor:pointer;padding:0;">
                                        @if($k->is_aktif)
                                            <span class="app-badge badge-status-aktif">Aktif</span>
                                        @else
                                            <span class="app-badge badge-status-nonaktif">Nonaktif</span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td style="padding:14px 16px;text-align:center;">
                                <div style="display:flex;gap:6px;justify-content:center;">
                                    <button type="button" onclick="bukaModalEdit({{ json_encode($k) }})" class="btnOutline" style="padding:6px 10px;font-size:11px;border-radius:8px;" title="Edit Kategori">
                                        <ion-icon name="create-outline"></ion-icon> Edit
                                    </button>
                                    @if($k->presensis_count === 0)
                                        <form method="POST" action="{{ route('admin.kategori-tutorial.destroy', $k) }}" data-confirm="Apakah Anda yakin ingin menghapus kategori tutorial ini?" data-confirm-title="Hapus Kategori" data-confirm-type="danger" data-confirm-btn="Ya, Hapus" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btnOutline" style="padding:6px 10px;font-size:11px;border-radius:8px;color:#dc2626;border-color:#fca5a5;" title="Hapus Kategori">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:32px;text-align:center;color:var(--muted);">
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
                    <label class="form-field-label">Nama Kategori Pembelajaran: <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="nama_kategori" id="inputNamaKategori" required placeholder="Contoh: Tutorial Komunitas 2 Jam" class="profileInput">
                </div>

                <div class="form-grid-responsive" style="margin-bottom:14px;">
                    <div>
                        <label class="form-field-label">Jenis Layanan: <span style="color:#ef4444;">*</span></label>
                        <select name="jenis_layanan" id="inputJenisLayanan" required class="profileInput">
                            <option value="komunitas">Tutorial Komunitas</option>
                            <option value="dl">Distance Learning (DL)</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-field-label">Durasi Sesi (Jam): <span style="color:#ef4444;">*</span></label>
                        <input type="number" step="0.25" min="0.5" max="12" name="durasi_jam" id="inputDurasiJam" required placeholder="2.0" class="profileInput">
                    </div>
                </div>

                <div class="form-field-wrapper">
                    <label class="form-field-label">Nominal Honor per Pertemuan (Rp): <span style="color:#ef4444;">*</span></label>
                    <input type="number" step="1000" min="0" name="nominal_honor" id="inputNominalHonor" required placeholder="75000" class="profileInput">
                </div>

                <div class="form-grid-responsive" style="margin-bottom:16px;">
                    <div class="checkbox-toggle-card">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_abk" id="inputIsAbk" value="1" style="width:16px;height:16px;">
                            Khusus Siswa ABK
                        </label>
                    </div>
                    <div class="checkbox-toggle-card">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_gabungan" id="inputIsGabungan" value="1" style="width:16px;height:16px;">
                            Rombel Gabungan
                        </label>
                    </div>
                </div>

                <div class="form-grid-responsive" style="margin-bottom:20px;">
                    <div>
                        <label class="form-field-label">Urutan Tampilan:</label>
                        <input type="number" name="urutan" id="inputUrutan" min="0" value="0" class="profileInput">
                    </div>
                    <div class="checkbox-toggle-card" style="align-self: end;">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_aktif" id="inputIsAktif" value="1" checked style="width:16px;height:16px;">
                            Status Aktif
                        </label>
                    </div>
                </div>

                <div class="app-modal-footer">
                    <button type="button" onclick="tutupModal()" class="btnOutline" style="padding:9px 16px;">Batal</button>
                    <button type="submit" id="btnSubmitModal" class="profileBtnPrimary" style="padding:9px 20px; width:auto;">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>

    <script>
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
