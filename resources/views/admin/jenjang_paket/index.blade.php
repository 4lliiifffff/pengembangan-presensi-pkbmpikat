@extends('layouts.admin')

@section('content')

<div class="laporanPageWrapper">
    {{-- ── Header Card ── --}}
    <div class="laporanHeader">
        <div class="laporanHeaderCard">
            <div class="laporanHeaderInfo">
                <div class="laporanHeaderLabel">MASTER PENDIDIKAN &amp; KURSUS</div>
                <h1 class="laporanHeaderTitle">Master Jenjang &amp; Program Paket</h1>
                <div class="laporanHeaderSub">Program pendidikan kesetaraan (Paket A/B/C), vokasi keterampilan, dan kursus</div>
                <p class="laporanHeaderDesc">Kelola hierarki jenjang, kode sistem, dan format tingkatan kelas secara dinamis.</p>
            </div>
            <div class="laporanHeaderActions header-actions-group">
                <button type="button" onclick="bukaModalTambah()" class="profileBtnPrimary">
                    <ion-icon name="add-circle-outline" class="icon-sm"></ion-icon> Tambah Jenjang Baru
                </button>
            </div>
        </div>
    </div>

    {{-- Banner Edukasi --}}
    <div class="info-callout-box mt-0 mb-4 d-flex items-center gap-3">
        <div class="icon-xl text-primary flex-shrink-0">
            <ion-icon name="information-circle-outline"></ion-icon>
        </div>
        <div class="info-callout-desc text-sm">
            Daftar Jenjang &amp; Program Paket di bawah ini digunakan secara <b >dinamis</b> pada form pembuatan kelas, filter rombel, <b >Universal Package Guard</b>, dan penentuan <b >Sesi Gabungan Komunitas</b>. Anda dapat menambah jenjang baru (misal: <i >Keaksaraan Fungsional, Vokasi Otomotif</i>) kapan saja.
        </div>
    </div>

    {{-- Tabel Master Jenjang Paket --}}
    <div class="table-wrapper-card">
        <div class="overflow-x-auto">
            <table class="table-modern">
                <thead >
                    <tr class="table-head-row">
                        <th class="table-col-num">Urutan</th>
                        <th class="p-3">Kode Sistem</th>
                        <th class="p-3">Nama Jenjang &amp; Program</th>
                        <th class="p-3">Format Tingkat</th>
                        <th class="p-3 text-center">Rombel / Kelas</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody >
                    @forelse($jenjangPakets as $jp)
                        <tr class="table-body-row">
                            <td class="p-3 text-center font-bold text-muted">{{ $jp->urutan ?: $loop->iteration }}</td>
                            <td class="p-3">
                                <code  class="text-sm font-bold text-dark rounded-sm badge-code">{{ $jp->kode }}</code>
                            </td>
                            <td class="p-3">
                                <div class="font-extrabold text-dark text-md">{{ $jp->nama_jenjang }}</div>
                                @if($jp->keterangan)
                                    <div class="text-sm text-muted mt-1">{{ $jp->keterangan }}</div>
                                @endif
                            </td>
                            <td class="p-3">
                                @if($jp->tingkat_label)
                                    <span class="app-badge badge-jenjang-paket_c">
                                        {{ $jp->tingkat_label }}
                                    </span>
                                @else
                                    <span class="text-muted text-xs">-</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <a href="{{ route('admin.kelas.index', ['jenjang' => $jp->kode]) }}" class="font-extrabold text-primary text-no-decor d-inline-flex items-center gap-1">
                                    <span >{{ $jp->kelas_count }} Kelas</span>
                                    <ion-icon name="open-outline" class="text-md"></ion-icon>
                                </a>
                            </td>
                            <td class="p-3 text-center">
                                <form method="POST" action="{{ route('admin.jenjang-paket.toggleStatus', $jp) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-none border-none cursor-pointer p-0" title="Klik untuk ubah status">
                                        @if($jp->is_aktif)
                                            <span class="app-badge badge-status-aktif">Aktif</span>
                                        @else
                                            <span class="app-badge badge-status-nonaktif">Nonaktif</span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td class="p-3 text-center">
                                <div class="d-flex gap-1 flex-center">
                                    <button type="button" onclick="bukaModalEdit({{ json_encode($jp) }})" class="smallBtn edit cursor-pointer btn-table-action" title="Edit Master Jenjang">
                                        <ion-icon name="create-outline"></ion-icon> Edit
                                    </button>

                                    <button type="button" onclick="bukaModalHapus({{ json_encode($jp) }})" title="Hapus Master Jenjang" class="smallBtn delete cursor-pointer btn-table-action">
                                        <ion-icon name="trash-outline"></ion-icon> Hapus
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr >
                            <td colspan="7" class="table-empty-cell">
                                Belum ada data Master Jenjang Paket. Klik "Tambah Jenjang Baru" untuk mendaftarkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Modal Tambah / Edit Jenjang Paket ── --}}
    <div id="modalJenjang" class="app-modal-backdrop">
        <div class="app-modal-card">
            <div class="app-modal-header">
                <h3 id="modalTitle" class="app-modal-title">Tambah Jenjang &amp; Program Baru</h3>
                <button type="button" onclick="tutupModal()" class="app-modal-close">&times;</button>
            </div>

            <form id="formJenjang" method="POST" action="{{ route('admin.jenjang-paket.store') }}">
                @csrf
                <div id="methodSpoof"></div>

                <div class="form-field-wrapper">
                    <label class="form-field-label">Nama Jenjang / Program Paket <span class="text-danger">*</span></label>
                    <input class="profileInput" type="text" name="nama_jenjang" id="input_nama_jenjang" placeholder="Contoh: Paket A (Setara SD) atau Vokasi Tata Boga" required oninput="autoGenerateKode()" />
                </div>

                <div class="form-field-wrapper">
                    <div class="flex-between mb-1">
                        <label class="form-field-label mb-0 mt-0">Kode Unik Sistem <span class="text-danger">*</span></label>
                        <span class="text-xs text-muted">Format: huruf_kecil_dan_underscore</span>
                    </div>
                    <input class="profileInput" type="text" name="kode" id="input_kode" placeholder="Contoh: paket_a atau vokasi_tata_boga" required />
                </div>

                <div class="form-field-wrapper">
                    <label class="form-field-label">Format / Rentang Tingkat</label>
                    <input class="profileInput" type="text" name="tingkat_label" id="input_tingkat_label" placeholder="Contoh: Kelas 1 - 6, Tingkat 7 - 9, atau Dasar / Terampil" />
                    <div class="field-help-text">Panduan tingkatan nomor kelas bagi admin saat membuat rombel.</div>
                </div>

                <div class="form-field-wrapper">
                    <label class="form-field-label">Urutan Tampilan</label>
                    <input class="profileInput" type="number" name="urutan" id="input_urutan" placeholder="1, 2, 3..." value="1" />
                </div>

                <div class="form-field-wrapper">
                    <label class="form-field-label">Keterangan / Deskripsi Program</label>
                    <textarea name="keterangan" id="input_keterangan" rows="2" placeholder="Deskripsi singkat tentang kurikulum atau sasaran program kesetaraan/vokasi" class="profileInput h-auto p-2"></textarea>
                </div>

                <div class="form-field-wrapper">
                    <div class="checkbox-toggle-card">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_aktif" id="input_is_aktif" value="1" checked class="w-auto" />
                            Status Aktif (dapat dipilih di form kelas)
                        </label>
                    </div>
                </div>

                <div class="app-modal-footer">
                    <button type="button" onclick="tutupModal()" class="profileBtnDanger px-3 text-sm rounded-md w-auto">Batal</button>
                    <button type="submit" class="profileBtnPrimary px-4 text-sm rounded-md w-auto">Simpan Master</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Modal Hapus / Migrasi Kelas Jenjang Paket ── --}}
    <div id="modalHapusJenjang" class="app-modal-backdrop">
        <div class="app-modal-card">
            <div class="app-modal-header">
                <div class="d-flex items-center gap-2">
                    <ion-icon name="warning-outline" class="icon-xl text-danger"></ion-icon>
                    <h3 id="hapusModalTitle" class="app-modal-title">Hapus Master Jenjang</h3>
                </div>
                <button type="button" onclick="tutupModalHapus()" class="app-modal-close">&times;</button>
            </div>

            <form id="formHapusJenjang" method="POST" action="">
                @csrf
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="confirm_unlink" id="confirm_unlink_val" value="1">

                <div id="hapusInfoBox" class="rounded-lg p-3 mb-4 text-danger text-sm line-height-relaxed alert-danger-box">
                    <div id="hapusNamaJenjangLabel" class="font-extrabold mb-1">Perhatian: Jenjang ini memiliki kelas aktif!</div>
                    <div id="hapusDeskripsiWarning">
                        Jenjang ini sedang digunakan oleh <b id="hapusKelasCountText">0</b> kelas.
                    </div>
                </div>

                {{-- Opsi Pengalihan Kelas --}}
                <div id="reassignBox" class="mb-4 d-none">
                    <label class="form-field-label text-sm mb-1">Tindakan untuk Kelas yang Terdaftar:</label>
                    <div class="flex-col gap-2">
                        <label class="d-flex items-start gap-2 text-sm cursor-pointer rounded-md border-base bg-card-alt p-2">
                            <input type="radio" name="delete_action" value="reassign" checked onchange="toggleDeleteAction(this.value)" class="mt-1">
                            <div >
                                <div class="font-bold text-dark">Pindahkan Seluruh Kelas ke Jenjang Lain (Direkomendasikan)</div>
                                <div class="text-xs text-muted">Kelas dan data murid akan otomatis dialihkan ke jenjang pengganti.</div>
                            </div>
                        </label>
                        <div id="targetJenjangSelectWrapper" class="pl-4 mt-1">
                            <select name="target_jenjang_paket_id" id="target_jenjang_paket_id" class="profileInput text-sm avatar-icon-36">
                                <option value="">-- Pilih Jenjang Pengganti --</option>
                                @foreach($jenjangPakets as $otherJp)
                                    <option value="{{ $otherJp->id }}" data-kode="{{ $otherJp->kode }}">
                                        {{ $otherJp->nama_jenjang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <label class="d-flex items-start gap-2 text-sm cursor-pointer rounded-md border-base bg-card-alt p-2">
                            <input type="radio" name="delete_action" value="set_null" onchange="toggleDeleteAction(this.value)" class="mt-1">
                            <div >
                                <div class="font-bold text-dark">Lepaskan Relasi (Atur Foreign Key menjadi NULL / Umum)</div>
                                <div class="text-xs text-muted">Kelas dan murid tetap aman tersimpan, tetapi tidak terikat jenjang tertentu.</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="app-modal-footer">
                    <button type="button" onclick="tutupModalHapus()" class="btnOutline px-3 text-sm rounded-md">Batal</button>
                    <button type="submit" class="profileBtnDanger px-4 text-sm rounded-md w-auto">
                        <ion-icon name="trash-outline"></ion-icon> Ya, Hapus Jenjang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script >
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
</div>

@endsection
