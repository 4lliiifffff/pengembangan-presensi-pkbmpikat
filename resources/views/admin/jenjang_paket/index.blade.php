@extends('layouts.admin')

@section('content')

    <div class="pageHeaderRow" style="flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:18px;">
        <div>
            <h2 style="margin:0;font-size:20px;font-weight:800;color:var(--text);">Master Jenjang &amp; Program Paket</h2>
            <p style="margin:2px 0 0;font-size:12px;color:var(--muted);">Kelola program pendidikan kesetaraan (Paket A/B/C), vokasi keterampilan, kursus, dan program kustom lainnya</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button type="button" onclick="bukaModalTambah()" class="btnPrimary" style="padding:8px 14px;background:var(--blue-gradient);font-size:12px;display:inline-flex;align-items:center;gap:6px;">
                <ion-icon name="add-circle-outline" style="font-size:16px;"></ion-icon> Tambah Jenjang Baru
            </button>
        </div>
    </div>

    {{-- Banner Edukasi --}}
    <div style="background:rgba(31,59,138,0.04);border:1px solid rgba(31,59,138,0.15);border-radius:14px;padding:12px 16px;margin-bottom:18px;display:flex;align-items:center;gap:12px;">
        <div style="font-size:24px;color:#1f3b8a;">
            <ion-icon name="information-circle-outline"></ion-icon>
        </div>
        <div style="font-size:12px;color:var(--muted);line-height:1.4;">
            Daftar Jenjang &amp; Program Paket di bawah ini digunakan secara <b>dinamis</b> pada form pembuatan kelas, filter rombel, <b>Universal Package Guard</b>, dan penentuan <b>Sesi Gabungan Komunitas</b>. Anda dapat menambah jenjang baru (misal: <i>Keaksaraan Fungsional, Vokasi Otomotif</i>) kapan saja.
        </div>
    </div>

    {{-- Tabel Master Jenjang Paket --}}
    <div style="background:var(--card,#fff);border-radius:18px;border:1px solid var(--border,#e2e8f0);overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.03);">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;text-align:left;">
                <thead>
                    <tr style="background:var(--card-alt,#f8fafc);border-bottom:1px solid var(--border,#e2e8f0);color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">
                        <th style="padding:14px 16px;width:50px;text-align:center;">Urutan</th>
                        <th style="padding:14px 16px;">Kode Sistem</th>
                        <th style="padding:14px 16px;">Nama Jenjang &amp; Program</th>
                        <th style="padding:14px 16px;">Format Tingkat</th>
                        <th style="padding:14px 16px;text-align:center;">Rombel / Kelas</th>
                        <th style="padding:14px 16px;text-align:center;">Status</th>
                        <th style="padding:14px 16px;text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jenjangPakets as $jp)
                        <tr style="border-bottom:1px solid var(--border,#e2e8f0);transition:background 0.2s;" onmouseover="this.style.background='var(--card-alt,#f8fafc)'" onmouseout="this.style.background='transparent'">
                            <td style="padding:14px 16px;text-align:center;font-weight:700;color:var(--muted);">{{ $jp->urutan ?: $loop->iteration }}</td>
                            <td style="padding:14px 16px;">
                                <code style="font-size:12px;font-weight:700;background:#f1f5f9;color:#0f172a;padding:3px 8px;border-radius:6px;border:1px solid #e2e8f0;">{{ $jp->kode }}</code>
                            </td>
                            <td style="padding:14px 16px;">
                                <div style="font-weight:800;color:var(--text);font-size:14px;">{{ $jp->nama_jenjang }}</div>
                                @if($jp->keterangan)
                                    <div style="font-size:11.5px;color:var(--muted);margin-top:2px;">{{ $jp->keterangan }}</div>
                                @endif
                            </td>
                            <td style="padding:14px 16px;">
                                @if($jp->tingkat_label)
                                    <span style="display:inline-block;padding:3px 8px;border-radius:6px;font-size:11.5px;font-weight:700;background:#e0e7ff;color:#3730a3;">
                                        {{ $jp->tingkat_label }}
                                    </span>
                                @else
                                    <span style="color:var(--muted);font-size:11px;">-</span>
                                @endif
                            </td>
                            <td style="padding:14px 16px;text-align:center;">
                                <a href="{{ route('admin.kelas.index', ['jenjang' => $jp->kode]) }}" style="font-weight:800;color:#2563eb;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                    <span>{{ $jp->kelas_count }} Kelas</span>
                                    <ion-icon name="open-outline" style="font-size:13px;"></ion-icon>
                                </a>
                            </td>
                            <td style="padding:14px 16px;text-align:center;">
                                <form method="POST" action="{{ route('admin.jenjang-paket.toggleStatus', $jp) }}" style="display:inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" style="background:none;border:none;cursor:pointer;padding:0;" title="Klik untuk ubah status">
                                        @if($jp->is_aktif)
                                            <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:800;background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;">Aktif</span>
                                        @else
                                            <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:800;background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;">Nonaktif</span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td style="padding:14px 16px;text-align:center;">
                                <div style="display:flex;gap:6px;justify-content:center;align-items:center;">
                                    <button type="button" class="smallBtn edit" onclick="bukaModalEdit({{ json_encode($jp) }})" style="cursor:pointer;">
                                        <ion-icon name="create-outline"></ion-icon> Edit
                                    </button>

                                    <button type="button" class="smallBtn delete" onclick="bukaModalHapus({{ json_encode($jp) }})" style="cursor:pointer;" title="Hapus Master Jenjang">
                                        <ion-icon name="trash-outline"></ion-icon> Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:32px 16px;text-align:center;color:var(--muted);">
                                Belum ada data Master Jenjang Paket. Klik "Tambah Jenjang Baru" untuk mendaftarkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Modal Tambah / Edit Jenjang Paket ── --}}
    <div id="modalJenjang" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:var(--card,#fff);border-radius:18px;max-width:520px;width:100%;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.2);border:1px solid var(--border,#e2e8f0);max-height:90vh;overflow-y:auto;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 id="modalTitle" style="margin:0;font-size:17px;font-weight:800;color:var(--text);">Tambah Jenjang &amp; Program Baru</h3>
                <button type="button" onclick="tutupModal()" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);">&times;</button>
            </div>

            <form id="formJenjang" method="POST" action="{{ route('admin.jenjang-paket.store') }}">
                @csrf
                <div id="methodSpoof"></div>

                <div class="formRow" style="margin-bottom:14px;">
                    <label class="fieldLabel">Nama Jenjang / Program Paket <span style="color:#ef4444;">*</span></label>
                    <input class="input" type="text" name="nama_jenjang" id="input_nama_jenjang" placeholder="Contoh: Paket A (Setara SD) atau Vokasi Tata Boga" required oninput="autoGenerateKode()" />
                </div>

                <div class="formRow" style="margin-bottom:14px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                        <label class="fieldLabel" style="margin:0;">Kode Unik Sistem <span style="color:#ef4444;">*</span></label>
                        <span style="font-size:10.5px;color:var(--muted);">Format: huruf_kecil_dan_underscore</span>
                    </div>
                    <input class="input" type="text" name="kode" id="input_kode" placeholder="Contoh: paket_a atau vokasi_tata_boga" required />
                </div>

                <div class="formRow" style="margin-bottom:14px;">
                    <label class="fieldLabel">Format / Rentang Tingkat</label>
                    <input class="input" type="text" name="tingkat_label" id="input_tingkat_label" placeholder="Contoh: Kelas 1 - 6, Tingkat 7 - 9, atau Dasar / Terampil" />
                    <div style="font-size:11px;color:var(--muted);margin-top:4px;">Panduan tingkatan nomor kelas bagi admin saat membuat rombel.</div>
                </div>

                <div class="formRow" style="margin-bottom:14px;">
                    <label class="fieldLabel">Urutan Tampilan</label>
                    <input class="input" type="number" name="urutan" id="input_urutan" placeholder="1, 2, 3..." value="1" />
                </div>

                <div class="formRow" style="margin-bottom:14px;">
                    <label class="fieldLabel">Keterangan / Deskripsi Program</label>
                    <textarea class="input" name="keterangan" id="input_keterangan" rows="2" style="height:auto;padding:8px 12px;" placeholder="Deskripsi singkat tentang kurikulum atau sasaran program kesetaraan/vokasi"></textarea>
                </div>

                <div class="formRow" style="margin-bottom:18px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;font-weight:700;color:var(--text);">
                        <input type="checkbox" name="is_aktif" id="input_is_aktif" value="1" checked style="width:16px;height:16px;" />
                        Status Aktif (dapat dipilih di form kelas)
                    </label>
                </div>

                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <button type="button" onclick="tutupModal()" class="profileBtnDanger" style="height:38px;padding:0 14px;font-size:12px;border-radius:10px;width:auto;">Batal</button>
                    <button type="submit" class="profileBtnPrimary" style="height:38px;padding:0 16px;font-size:12px;border-radius:10px;width:auto;">Simpan Master</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Modal Hapus / Migrasi Kelas Jenjang Paket ── --}}
    <div id="modalHapusJenjang" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:var(--card,#fff);border-radius:18px;max-width:500px;width:100%;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.25);border:1px solid var(--border,#e2e8f0);max-height:90vh;overflow-y:auto;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <ion-icon name="warning-outline" style="font-size:22px;color:#dc2626;"></ion-icon>
                    <h3 id="hapusModalTitle" style="margin:0;font-size:16px;font-weight:800;color:var(--text);">Hapus Master Jenjang</h3>
                </div>
                <button type="button" onclick="tutupModalHapus()" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);">&times;</button>
            </div>

            <form id="formHapusJenjang" method="POST" action="">
                @csrf
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="confirm_unlink" id="confirm_unlink_val" value="1">

                <div id="hapusInfoBox" style="background:#fee2e2;border:1px solid #fecaca;border-radius:12px;padding:14px;margin-bottom:16px;color:#991b1b;font-size:12.5px;line-height:1.5;">
                    <div style="font-weight:800;margin-bottom:4px;" id="hapusNamaJenjangLabel">Perhatian: Jenjang ini memiliki kelas aktif!</div>
                    <div id="hapusDeskripsiWarning">
                        Jenjang ini sedang digunakan oleh <b id="hapusKelasCountText">0</b> kelas.
                    </div>
                </div>

                {{-- Opsi Pengalihan Kelas --}}
                <div id="reassignBox" style="margin-bottom:18px;display:none;">
                    <label class="fieldLabel" style="font-size:12px;margin-bottom:6px;">Tindakan untuk Kelas yang Terdaftar:</label>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <label style="display:flex;align-items:flex-start;gap:8px;font-size:12px;cursor:pointer;padding:8px 10px;border-radius:8px;border:1px solid var(--border,#e2e8f0);background:var(--card-alt,#f8fafc);">
                            <input type="radio" name="delete_action" value="reassign" checked onchange="toggleDeleteAction(this.value)" style="margin-top:2px;">
                            <div>
                                <div style="font-weight:700;color:var(--text);">Pindahkan Seluruh Kelas ke Jenjang Lain (Direkomendasikan)</div>
                                <div style="font-size:11px;color:var(--muted);">Kelas dan data murid akan otomatis dialihkan ke jenjang pengganti.</div>
                            </div>
                        </label>
                        <div id="targetJenjangSelectWrapper" style="padding-left:24px;margin-top:-2px;">
                            <select class="input" name="target_jenjang_paket_id" id="target_jenjang_paket_id" style="font-size:12px;height:36px;">
                                <option value="">-- Pilih Jenjang Pengganti --</option>
                                @foreach($jenjangPakets as $otherJp)
                                    <option value="{{ $otherJp->id }}" data-kode="{{ $otherJp->kode }}">
                                        {{ $otherJp->nama_jenjang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <label style="display:flex;align-items:flex-start;gap:8px;font-size:12px;cursor:pointer;padding:8px 10px;border-radius:8px;border:1px solid var(--border,#e2e8f0);background:var(--card-alt,#f8fafc);">
                            <input type="radio" name="delete_action" value="set_null" onchange="toggleDeleteAction(this.value)" style="margin-top:2px;">
                            <div>
                                <div style="font-weight:700;color:var(--text);">Lepaskan Relasi (Atur Foreign Key menjadi NULL / Umum)</div>
                                <div style="font-size:11px;color:var(--muted);">Kelas dan murid tetap aman tersimpan, tetapi tidak terikat jenjang tertentu.</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <button type="button" onclick="tutupModalHapus()" class="btnOutline" style="height:36px;padding:0 14px;font-size:12px;border-radius:8px;">Batal</button>
                    <button type="submit" class="profileBtnDanger" style="height:36px;padding:0 16px;font-size:12px;border-radius:8px;width:auto;">
                        <ion-icon name="trash-outline"></ion-icon> Ya, Hapus Jenjang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function bukaModalTambah() {
            document.getElementById('modalTitle').textContent = 'Tambah Jenjang & Program Baru';
            const form = document.getElementById('formJenjang');
            form.action = "{{ route('admin.jenjang-paket.store') }}";
            document.getElementById('methodSpoof').innerHTML = '';
            
            document.getElementById('input_nama_jenjang').value = '';
            document.getElementById('input_kode').value = '';
            document.getElementById('input_kode').readOnly = false;
            document.getElementById('input_tingkat_label').value = '';
            document.getElementById('input_urutan').value = '{{ ($jenjangPakets->max('urutan') ?? 0) + 1 }}';
            document.getElementById('input_keterangan').value = '';
            document.getElementById('input_is_aktif').checked = true;

            document.getElementById('modalJenjang').style.display = 'flex';
        }

        function bukaModalEdit(jp) {
            document.getElementById('modalTitle').textContent = 'Edit Jenjang: ' + jp.nama_jenjang;
            const form = document.getElementById('formJenjang');
            form.action = "/admin/jenjang-paket/" + jp.id;
            document.getElementById('methodSpoof').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            
            document.getElementById('input_nama_jenjang').value = jp.nama_jenjang || '';
            document.getElementById('input_kode').value = jp.kode || '';
            document.getElementById('input_kode').readOnly = false;
            document.getElementById('input_tingkat_label').value = jp.tingkat_label || '';
            document.getElementById('input_urutan').value = jp.urutan || 1;
            document.getElementById('input_keterangan').value = jp.keterangan || '';
            document.getElementById('input_is_aktif').checked = Boolean(jp.is_aktif);

            document.getElementById('modalJenjang').style.display = 'flex';
        }

        function tutupModal() {
            document.getElementById('modalJenjang').style.display = 'none';
        }

        function bukaModalHapus(jp) {
            const form = document.getElementById('formHapusJenjang');
            form.action = "/admin/jenjang-paket/" + jp.id;

            const kelasCount = jp.kelas_count || 0;
            const reassignBox = document.getElementById('reassignBox');
            const hapusInfoBox = document.getElementById('hapusInfoBox');
            const targetSelect = document.getElementById('target_jenjang_paket_id');

            // Filter options in targetSelect to exclude the current jp being deleted
            Array.from(targetSelect.options).forEach(opt => {
                if (opt.value == jp.id) {
                    opt.style.display = 'none';
                    opt.disabled = true;
                } else {
                    opt.style.display = 'block';
                    opt.disabled = false;
                }
            });
            targetSelect.value = '';

            if (kelasCount > 0) {
                document.getElementById('hapusModalTitle').textContent = 'Konfirmasi Penghapusan & Relasi Kelas';
                document.getElementById('hapusNamaJenjangLabel').textContent = 'PERINGATAN: Jenjang "' + jp.nama_jenjang + '" memiliki ' + kelasCount + ' kelas terdaftar!';
                document.getElementById('hapusDeskripsiWarning').textContent = 'Pilih tindakan untuk ' + kelasCount + ' kelas tersebut agar data murid dan rombongan belajar tetap aman tersimpan:';
                hapusInfoBox.style.display = 'block';
                reassignBox.style.display = 'block';
            } else {
                document.getElementById('hapusModalTitle').textContent = 'Hapus Master Jenjang: ' + jp.nama_jenjang;
                document.getElementById('hapusNamaJenjangLabel').textContent = 'Hapus Jenjang "' + jp.nama_jenjang + '"?';
                document.getElementById('hapusDeskripsiWarning').textContent = 'Jenjang ini belum memiliki kelas terdaftar. Tindakan ini aman dan tidak memengaruhi data siswa.';
                hapusInfoBox.style.background = '#fef3c7';
                hapusInfoBox.style.borderColor = '#fde68a';
                hapusInfoBox.style.color = '#92400e';
                reassignBox.style.display = 'none';
            }

            document.getElementById('modalHapusJenjang').style.display = 'flex';
        }

        function tutupModalHapus() {
            document.getElementById('modalHapusJenjang').style.display = 'none';
        }

        function toggleDeleteAction(val) {
            const selectWrapper = document.getElementById('targetJenjangSelectWrapper');
            const targetSelect = document.getElementById('target_jenjang_paket_id');
            if (val === 'reassign') {
                selectWrapper.style.display = 'block';
                targetSelect.required = true;
            } else {
                selectWrapper.style.display = 'none';
                targetSelect.required = false;
                targetSelect.value = '';
            }
        }

        function autoGenerateKode() {
            const nama = document.getElementById('input_nama_jenjang').value;
            const kodeInput = document.getElementById('input_kode');
            if (!kodeInput.dataset.manualEdit) {
                let slug = nama.toLowerCase()
                    .replace(/\s+/g, '_')
                    .replace(/[^\w]/g, '');
                kodeInput.value = slug;
            }
        }

        document.getElementById('input_kode').addEventListener('input', function() {
            this.dataset.manualEdit = 'true';
        });
    </script>

@endsection
