@extends('layouts.admin')



@section('content')

<div class="pageHeaderRow" style="flex-wrap:wrap;gap:10px;align-items:center;">
    <div>
        <h2 style="margin:0;">Data Karyawan &amp; Tutor</h2>
        <p style="margin:2px 0 0;font-size:12px;color:var(--muted);">Kelola tenaga pendidik, akun staf, dan hak akses sistem</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('admin.karyawan.exportExcel') }}" class="btnPrimary" style="padding:8px 12px;background:#16a34a;font-size:12px;">
            <ion-icon name="download-outline"></ion-icon> Export Excel
        </a>
        <button type="button" onclick="document.getElementById('importKaryawanModal').style.display='flex'" class="btnPrimary" style="padding:8px 12px;background:#0284c7;font-size:12px;">
            <ion-icon name="cloud-upload-outline"></ion-icon> Import Tutor
        </button>
        <a href="{{ route('admin.karyawan.create') }}" class="btnPrimary" style="padding:8px 12px;background:var(--blue-gradient);font-size:12px;">
            <ion-icon name="add-outline"></ion-icon> Tambah Staf
        </a>
    </div>
</div>

{{-- ── Modal Impor Tutor / Karyawan ── --}}
<div id="importKaryawanModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:16px;">
    <div style="background:var(--card,#fff);border-radius:18px;max-width:480px;width:100%;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.2);border:1px solid var(--border,#e2e8f0);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="margin:0;font-size:17px;font-weight:800;color:var(--text);">Impor Data Tutor &amp; Karyawan</h3>
            <button type="button" onclick="document.getElementById('importKaryawanModal').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);">&times;</button>
        </div>
        <p style="font-size:13px;color:var(--muted);margin-bottom:16px;line-height:1.4;">
            Unggah berkas spreadsheet Excel/CSV untuk mendaftarkan tutor dan staf baru secara massal. Akun login akan otomatis digenerate dengan password default berbasis NIK.
        </p>
        <div style="margin-bottom:18px;">
            <a href="{{ route('admin.karyawan.downloadTemplate') }}" class="btnOutline" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;font-size:12px;font-weight:700;">
                <ion-icon name="download-outline"></ion-icon> Download Template Tutor (.xlsx)
            </a>
        </div>
        <form method="POST" action="{{ route('admin.karyawan.importExcel') }}" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:12px;font-weight:700;margin-bottom:6px;color:var(--text);">Pilih Berkas Spreadsheet (.xlsx / .csv):</label>
                <input type="file" name="file_excel" accept=".xlsx,.xls,.csv" required style="width:100%;padding:10px;border:1px dashed var(--border,#cbd5e1);border-radius:10px;font-size:13px;background:var(--card-alt,#f8fafc);">
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('importKaryawanModal').style.display='none'" class="btnOutline" style="padding:9px 14px;">Batal</button>
                <button type="submit" class="btnPrimary" style="padding:9px 18px;background:var(--blue-gradient);">Unggah &amp; Impor Tutor</button>
            </div>
        </form>
    </div>
</div>

<!-- Statistik -->
<div class="statsRow" style="padding: 0 16px;">
    <div class="statBox dark">
        <ion-icon name="people-outline" style="font-size:24px;margin-bottom:4px;"></ion-icon>
        <h2>{{ $total }}</h2>
        <div>TOTAL STAFF</div>
    </div>
    <div class="statBox">
        <span class="dotIndicator green"></span>
        <div style="font-size:12px;color: var(--muted);">AKTIF</div>
        <h2>{{ $aktif }}</h2>
        <div style="font-size:12px;color: var(--muted);">Karyawan</div>
    </div>
    <div class="statBox">
        <span class="dotIndicator red"></span>
        <div style="font-size:12px;color: var(--muted);">NONAKTIF</div>
        <h2>{{ $nonaktif }}</h2>
        <div style="font-size:12px;color: var(--muted);">Karyawan</div>
    </div>
</div>

<!-- Search + Sort -->
<form method="GET" style="display:flex;align-items:center;gap:8px;margin:15px 0;padding:0 16px;">
    <div style="position:relative;flex:1;">
        <ion-icon name="search-outline" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color: var(--muted);font-size:16px;"></ion-icon>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama karyawan..." class="searchInput" style="padding-left:36px;">
    </div>
    <button type="submit" class="btnSortSmall">
        URUTKAN A-Z
    </button>
</form>

<!-- List Header -->
<div style="font-size:12px;font-weight:700;letter-spacing:1px;color: var(--muted);margin-bottom:10px;padding:0 16px;">DAFTAR STAFF</div>

<!-- List -->
<div class="listContainer" style="padding-left:16px;padding-right:16px;">
    @foreach($karyawan as $k)
    @php
        $displayName = (string) ($k->nama_lengkap ?? $k->name ?? '');
        $initials = strtoupper(substr($displayName, 0, 2));
        $avatarColors = ['#6366f1','#f59e0b','#10b981','#3b82f6','#ec4899','#8b5cf6','#14b8a6','#f97316'];
        $avatarIndex = count($avatarColors) ? (abs((int) crc32($displayName)) % count($avatarColors)) : 0;
        $avatarBg = $avatarColors[$avatarIndex] ?? '#64748b';
        $isActive = (bool) ($k->is_active ?? true);
        $fotoUrl = $k->foto
            ? (str_starts_with($k->foto, 'uploads/') ? asset($k->foto) : asset('storage/' . $k->foto))
            : null;
    @endphp
    <div class="cardItem" onclick="openDrawer({{ (int) $k->id }}, {{ Illuminate\Support\Js::from($displayName) }}, {{ Illuminate\Support\Js::from((string) ($k->role ?? '')) }}, {{ $isActive ? 'true' : 'false' }}, {{ Illuminate\Support\Js::from($fotoUrl) }})">
        @if($fotoUrl)
            <img src="{{ $fotoUrl }}" class="staffAvatar" style="object-fit:cover;" alt="Avatar" />
        @else
            <div class="staffAvatar" >
                {{ $initials }}
            </div>
        @endif
        <div class="info">
            <div class="name">{{ $displayName }}</div>
            <div class="role">{{ ucfirst(str_replace('_', ' ', (string) ($k->role ?? ''))) }}</div>
        </div>
        <div class="statusBadge {{ $isActive ? 'aktif' : 'nonaktif' }}">
            {{ $isActive ? 'AKTIF' : 'NONAKTIF' }}
        </div>
        <a href="{{ route('admin.karyawan.edit', $k->id) }}"
           onclick="event.stopPropagation()"
           aria-label="Edit Karyawan"
           style="display:grid;place-items:center;width:28px;height:28px;border-radius:10px;margin-left:6px;color:#94a3b8;text-decoration:none;">
            <ion-icon name="chevron-forward-outline" style="font-size:18px;"></ion-icon>
        </a>
    </div>
    @endforeach
</div>

<!-- Floating Button -->
<a href="{{ route('admin.karyawan.create') }}" class="fabAdd" aria-label="Tambah Karyawan">
    <ion-icon name="add-outline"></ion-icon>
</a>

<!-- Overlay -->
<div class="drawerOverlay" id="drawerOverlay" onclick="closeDrawer()"></div>

<!-- Bottom Drawer -->
<div class="bottomDrawer" id="bottomDrawer">
    <div class="drawerHandle"></div>

    <!-- Info Karyawan -->
    <div class="drawerHeader">
        <div class="drawerAvatar" id="drawerAvatar"></div>
        <div>
            <div class="drawerName" id="drawerName"></div>
            <div class="drawerRole" id="drawerRole"></div>
        </div>
    </div>

    <div class="drawerDivider"></div>

    <!-- Toggle Status -->
    <div class="drawerRow">
        <div>
            <div class="drawerRowTitle">Status Karyawan</div>
            <div class="drawerRowSub" id="drawerStatusLabel">Aktif</div>
        </div>
        <label class="toggleSwitch">
            <input type="checkbox" id="drawerToggle" onchange="updateStatus()">
            <span class="toggleSlider"></span>
        </label>
    </div>

    <div class="drawerDivider"></div>

    <!-- Tombol Edit -->
    <a id="drawerEditBtn" href="#" class="drawerActionBtn primary">
        <ion-icon name="create-outline"></ion-icon>
        Edit Data Karyawan
    </a>

    <!-- Tombol Hapus -->
    <form id="drawerDeleteForm" method="POST" onsubmit="return confirm('Yakin ingin menghapus karyawan ini?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="drawerActionBtn danger" style="width:100%;border:none;cursor:pointer;">
            <ion-icon name="trash-outline"></ion-icon>
            Hapus Karyawan
        </button>
    </form>

    <button onclick="closeDrawer()" class="drawerActionBtn ghost">
        Batal
    </button>
</div>

<!-- Hidden Form untuk Toggle Status -->
<form id="statusForm" method="POST" style="display:none;">
    @csrf
    @method('PATCH')
    <input type="hidden" name="is_active" id="statusValue">
</form>

@endsection

<script>
    let currentId = null;

    // Helper warna avatar konsisten berdasarkan nama
    const colors = ['#6366f1','#f59e0b','#10b981','#3b82f6','#ec4899','#8b5cf6','#14b8a6','#f97316'];
    function strColor(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) hash = str.charCodeAt(i) + ((hash << 5) - hash);
        return colors[Math.abs(hash) % colors.length];
    }

    function openDrawer(id, nama, role, isActive, fotoUrl = null) {
        currentId = id;
        const initials = nama.substring(0, 2).toUpperCase();
        const color = strColor(nama);

        const drawerAvatar = document.getElementById('drawerAvatar');
        if (fotoUrl) {
            drawerAvatar.innerHTML = `<img src="${fotoUrl}" style="width:100%;height:100%;border-radius:14px;object-fit:cover;" alt="Avatar">`;
            drawerAvatar.style.background = 'transparent';
        } else {
            drawerAvatar.innerHTML = initials;
            drawerAvatar.style.background = color;
        }

        document.getElementById('drawerName').textContent = nama;
        document.getElementById('drawerRole').textContent = role.replace('_', ' ');

        const toggle = document.getElementById('drawerToggle');
        toggle.checked = isActive;
        updateStatusLabel(isActive);

        document.getElementById('drawerEditBtn').href = `/admin/karyawan/${id}/edit`;
        document.getElementById('drawerDeleteForm').action = `/admin/karyawan/${id}`;

        document.getElementById('drawerOverlay').classList.add('show');
        document.getElementById('bottomDrawer').classList.add('show');
    }

    function closeDrawer() {
        document.getElementById('drawerOverlay').classList.remove('show');
        document.getElementById('bottomDrawer').classList.remove('show');
        currentId = null;
    }

    function updateStatusLabel(isActive) {
        const label = document.getElementById('drawerStatusLabel');
        label.textContent = isActive ? 'Karyawan saat ini Aktif' : 'Karyawan saat ini Nonaktif';
        label.style.color = isActive ? '#15803d' : '#dc2626';
    }

    function updateStatus() {
        const toggle = document.getElementById('drawerToggle');
        const isActive = toggle.checked;
        updateStatusLabel(isActive);

        if (!currentId) return;

        const form = document.getElementById('statusForm');
        form.action = `/admin/karyawan/${currentId}/status`;
        document.getElementById('statusValue').value = isActive ? '1' : '0';
        form.submit();
    }
</script>
