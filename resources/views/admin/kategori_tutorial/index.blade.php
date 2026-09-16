@extends('layouts.admin')

@section('content')

    <div class="pageHeaderRow" style="flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:18px;">
        <div>
            <h2 style="margin:0;font-size:20px;font-weight:800;color:var(--text);">Master Kategori &amp; Tarif SK</h2>
            <p style="margin:2px 0 0;font-size:12px;color:var(--muted);">Kelola kategori pembelajaran, durasi acuan, status ABK, dan besaran tarif honor per pertemuan sesuai SK Kepala PKBM</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
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
                        <tr style="border-bottom:1px solid var(--border,#e2e8f0);transition:background 0.2s;" onmouseover="this.style.background='var(--card-alt,#f8fafc)'" onmouseout="this.style.background='transparent'">
                            <td style="padding:14px 16px;text-align:center;font-weight:700;color:var(--muted);">{{ $k->urutan ?: $loop->iteration }}</td>
                            <td style="padding:14px 16px;">
                                <div style="font-weight:700;color:var(--text);">{{ $k->nama_kategori }}</div>
                                <div style="font-size:11px;color:var(--muted);">{{ $k->presensis_count }} sesi presensi tercatat</div>
                            </td>
                            <td style="padding:14px 16px;">
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span style="display:inline-block;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;background:{{ $k->jenis_layanan === 'dl' ? '#e0f2fe;color:#0369a1' : '#f0fdf4;color:#15803d' }};">
                                        {{ $k->jenis_layanan === 'dl' ? 'Distance Learning (DL)' : 'Tutorial Komunitas' }}
                                    </span>
                                    <span style="font-weight:700;color:var(--text);font-size:12px;">{{ $k->durasi_jam }} Jam</span>
                                </div>
                            </td>
                            <td style="padding:14px 16px;text-align:center;">
                                <div style="display:flex;gap:4px;justify-content:center;flex-wrap:wrap;">
                                    @if($k->is_abk)
                                        <span style="display:inline-block;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;background:#fef3c7;color:#b45309;">ABK</span>
                                    @else
                                        <span style="display:inline-block;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;background:#f1f5f9;color:#475569;">Reguler</span>
                                    @endif

                                    @if($k->is_gabungan)
                                        <span style="display:inline-block;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;background:#fdf2f8;color:#9d174d;">Rombel Gabungan</span>
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
                                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:12px;font-size:11px;font-weight:700;background:#dcfce7;color:#15803d;">
                                                <span style="width:6px;height:6px;border-radius:50%;background:#16a34a;"></span> Aktif
                                            </span>
                                        @else
                                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:12px;font-size:11px;font-weight:700;background:#f1f5f9;color:#64748b;">
                                                <span style="width:6px;height:6px;border-radius:50%;background:#94a3b8;"></span> Non-Aktif
                                            </span>
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
    <div id="modalKategori" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:var(--card,#fff);border-radius:18px;max-width:520px;width:100%;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.2);border:1px solid var(--border,#e2e8f0);max-height:90vh;overflow-y:auto;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 id="modalKategoriTitle" style="margin:0;font-size:17px;font-weight:800;color:var(--text);">Tambah Kategori SK</h3>
                <button type="button" onclick="tutupModal()" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);">&times;</button>
            </div>

            <form id="formKategori" method="POST" action="{{ route('admin.kategori-tutorial.store') }}">
                @csrf
                <div id="methodContainer"></div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12px;font-weight:700;margin-bottom:6px;color:var(--text);">Nama Kategori Pembelajaran:</label>
                    <input type="text" name="nama_kategori" id="inputNamaKategori" required placeholder="Contoh: Tutorial Komunitas 2 Jam" style="width:100%;padding:10px 12px;border:1px solid var(--border,#cbd5e1);border-radius:10px;font-size:13px;background:var(--card-alt,#f8fafc);color:var(--text);">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label style="display:block;font-size:12px;font-weight:700;margin-bottom:6px;color:var(--text);">Jenis Layanan:</label>
                        <select name="jenis_layanan" id="inputJenisLayanan" required style="width:100%;padding:10px 12px;border:1px solid var(--border,#cbd5e1);border-radius:10px;font-size:13px;background:var(--card-alt,#f8fafc);color:var(--text);">
                            <option value="komunitas">Tutorial Komunitas</option>
                            <option value="dl">Distance Learning (DL)</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:700;margin-bottom:6px;color:var(--text);">Durasi Sesi (Jam):</label>
                        <input type="number" step="0.25" min="0.5" max="12" name="durasi_jam" id="inputDurasiJam" required placeholder="2.0" style="width:100%;padding:10px 12px;border:1px solid var(--border,#cbd5e1);border-radius:10px;font-size:13px;background:var(--card-alt,#f8fafc);color:var(--text);">
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12px;font-weight:700;margin-bottom:6px;color:var(--text);">Nominal Honor per Pertemuan (Rp):</label>
                    <input type="number" step="1000" min="0" name="nominal_honor" id="inputNominalHonor" required placeholder="75000" style="width:100%;padding:10px 12px;border:1px solid var(--border,#cbd5e1);border-radius:10px;font-size:13px;background:var(--card-alt,#f8fafc);color:var(--text);">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                    <div style="background:var(--card-alt,#f8fafc);padding:10px 12px;border-radius:10px;border:1px solid var(--border,#e2e8f0);">
                        <label style="display:flex;align-items:center;gap:8px;font-size:12px;font-weight:700;cursor:pointer;color:var(--text);">
                            <input type="checkbox" name="is_abk" id="inputIsAbk" value="1" style="width:16px;height:16px;">
                            Khusus Siswa ABK
                        </label>
                    </div>
                    <div style="background:var(--card-alt,#f8fafc);padding:10px 12px;border-radius:10px;border:1px solid var(--border,#e2e8f0);">
                        <label style="display:flex;align-items:center;gap:8px;font-size:12px;font-weight:700;cursor:pointer;color:var(--text);">
                            <input type="checkbox" name="is_gabungan" id="inputIsGabungan" value="1" style="width:16px;height:16px;">
                            Rombel Gabungan
                        </label>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px;">
                    <div>
                        <label style="display:block;font-size:12px;font-weight:700;margin-bottom:6px;color:var(--text);">Urutan Tampilan:</label>
                        <input type="number" name="urutan" id="inputUrutan" min="0" value="0" style="width:100%;padding:10px 12px;border:1px solid var(--border,#cbd5e1);border-radius:10px;font-size:13px;background:var(--card-alt,#f8fafc);color:var(--text);">
                    </div>
                    <div style="display:flex;align-items:flex-end;">
                        <label style="display:flex;align-items:center;gap:8px;font-size:12px;font-weight:700;cursor:pointer;color:var(--text);padding-bottom:10px;">
                            <input type="checkbox" name="is_aktif" id="inputIsAktif" value="1" checked style="width:16px;height:16px;">
                            Status Aktif
                        </label>
                    </div>
                </div>

                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <button type="button" onclick="tutupModal()" class="btnOutline" style="padding:9px 16px;">Batal</button>
                    <button type="submit" id="btnSubmitModal" class="btnPrimary" style="padding:9px 20px;background:var(--blue-gradient);">Simpan Kategori</button>
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
