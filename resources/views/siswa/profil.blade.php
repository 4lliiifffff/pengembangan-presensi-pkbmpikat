@extends('layouts.presensi')

@section('title', 'Profil Siswa')

@php
    $user = Auth::user();
    $siswa = $user->siswa;
    $displayName = (string) ($siswa->nama_siswa ?? ($siswa->nama_lengkap ?? ($user->nama_lengkap ?? ($user->name ?? 'Siswa PKBM'))));
    $initial = strtoupper(substr($displayName, 0, 1));
    $fotoUrl = $user->foto_url;
@endphp

@push('topbar')
    @include('layouts.components.navigasi_atas', [
        'titleName' => 'Profil Siswa',
        'subTitle' => $displayName . ' • Siswa',
        'dashRoute' => route('siswa.dashboard'),
        'backRoute' => route('siswa.dashboard'),
    ])
@endpush

@section('content')

{{-- ── HERO PROFILE HEADER ── --}}
<div class="profileHero">
    <div class="profileAvatarBox">
        @if($fotoUrl)
            <img src="{{ $fotoUrl }}" alt="Avatar" class="profileAvatarImg" id="avatarPreview">
        @else
            <div class="profileAvatarInitial" id="avatarInitial">{{ $initial }}</div>
            <img src="" alt="Avatar" id="avatarPreview" class="profileAvatarImg d-none">
        @endif

        <label for="fotoUpload" class="profileCameraBtn" title="Ganti Foto Profil">
            <ion-icon name="camera"></ion-icon>
        </label>
    </div>

    <div class="profileName">{{ $displayName }}</div>
    <div class="profileRoleBadge badgeMagang">
        <ion-icon name="school-outline"></ion-icon>
        {{ $siswa?->kelas?->nama_kelas ?? 'Siswa PKBM' }} ({{ $siswa?->masterJenjang?->nama_jenjang ?? ($siswa?->jenjang_paket_label ?? 'Paket PKBM') }})
    </div>

    {{-- Stats Kehadiran --}}
    <div class="profileStatsGrid" style="max-width: 420px;">
        <div class="profileStatItem">
            <div class="profileStatLabel">Hari Hadir</div>
            <div class="profileStatValue primary">{{ $totalHariHadir ?? 0 }} Hari</div>
        </div>
        <div class="profileStatItem">
            <div class="profileStatLabel">Absen Mandiri</div>
            <div class="profileStatValue">{{ $totalHadirMandiri ?? 0 }} Kali</div>
        </div>
        <div class="profileStatItem">
            <div class="profileStatLabel">Sesi Kelas</div>
            <div class="profileStatValue">{{ $totalHadirKelas ?? 0 }} Sesi</div>
        </div>
    </div>
</div>

{{-- ── TAB SWITCHER ── --}}
<div class="profileTabs">
    <button type="button" class="profileTabBtn active" id="tabBtnData" onclick="switchProfileTab('data')">
        <ion-icon name="person-outline"></ion-icon>
        <span>Data Siswa</span>
    </button>
    <button type="button" class="profileTabBtn" id="tabBtnKeamanan" onclick="switchProfileTab('keamanan')">
        <ion-icon name="key-outline"></ion-icon>
        <span>Keamanan</span>
    </button>
    <button type="button" class="profileTabBtn" id="tabBtnNotifikasi" onclick="switchProfileTab('notifikasi')">
        <ion-icon name="notifications-outline"></ion-icon>
        <span>Notifikasi</span>
    </button>
</div>

{{-- ── ALERT ERROR DISPLAY ── --}}
@if ($errors->any())
    <div class="error-list-container">
        <div class="error-title">
            <ion-icon name="alert-circle-outline" class="align-middle icon-sm"></ion-icon>
            Terdapat beberapa kesalahan:
        </div>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ── TAB 1: FORM DATA SISWA ── --}}
<form action="{{ route('profil.update') }}" method="POST" enctype="multipart/form-data" id="formData">
    @csrf
    @method('PATCH')

    {{-- Hidden file input --}}
    <input type="file" name="foto" id="fotoUpload" accept=".png,.jpg,.jpeg,image/*" class="d-none" onchange="previewProfileImage(event)">

    {{-- Detail Akademik Siswa --}}
    <div class="profileCard">
        <div class="profileSectionTitle">
            <ion-icon name="information-circle-outline" class="text-lg text-primary"></ion-icon>
            Informasi Akademik & Rombel
        </div>

        <div class="profileInfoRow">
            <span class="profileInfoLabel">NISN / No. Absen / NIK</span>
            <span class="profileInfoValue">{{ $siswa?->nisn ?: ($siswa?->no_absen ?: ($user->nik ?: '—')) }}</span>
        </div>
        <div class="profileInfoRow">
            <span class="profileInfoLabel">Kelas (Rombel)</span>
            <span class="profileInfoValue">{{ $siswa?->kelas?->nama_kelas ?: '—' }}</span>
        </div>
        <div class="profileInfoRow">
            <span class="profileInfoLabel">Jenjang / Program Paket</span>
            <span class="profileInfoValue">{{ $siswa?->masterJenjang?->nama_jenjang ?: ($siswa?->jenjang_paket_label ?: '—') }}</span>
        </div>
        <div class="profileInfoRow">
            <span class="profileInfoLabel">Jenis Kelamin</span>
            <span class="profileInfoValue">
                {{ $siswa?->jenis_kelamin === 'L' ? 'Laki-laki' : ($siswa?->jenis_kelamin === 'P' ? 'Perempuan' : '—') }}
            </span>
        </div>
        <div class="profileInfoRow">
            <span class="profileInfoLabel">Status Siswa</span>
            <span class="profileInfoValue">
                <span class="badge {{ $siswa?->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}" style="font-size: 0.75rem; padding: 2px 8px; border-radius: 9999px;">
                    {{ ucfirst($siswa?->status ?? 'Aktif') }}
                </span>
            </span>
        </div>
    </div>

    {{-- Detail Kontak Pribadi --}}
    <div class="profileCard">
        <div class="profileSectionTitle">
            <ion-icon name="create-outline" class="text-lg text-primary"></ion-icon>
            Edit Kontak & Biodata
        </div>

        <div class="profileField">
            <label class="profileFieldLabel" for="nama_lengkap">Nama Lengkap</label>
            <input type="text" name="nama_lengkap" id="nama_lengkap" class="profileInput" value="{{ old('nama_lengkap', $user->nama_lengkap ?? ($user->name ?? $siswa?->nama_lengkap)) }}" placeholder="Nama Lengkap" required>
        </div>

        <div class="profileField">
            <label class="profileFieldLabel" for="email">Alamat Email</label>
            <input type="email" name="email" id="email" class="profileInput" value="{{ old('email', $user->email) }}" placeholder="email@contoh.com" required>
        </div>

        <div class="profileField">
            <label class="profileFieldLabel" for="no_hp">No. WhatsApp / HP</label>
            <input type="tel" name="no_hp" id="no_hp" class="profileInput" value="{{ old('no_hp', $user->no_hp ?? $siswa?->no_hp) }}" placeholder="08xx-xxxx-xxxx">
        </div>
    </div>
</form>

{{-- ── TAB 2: FORM KEAMANAN SANDI ── --}}
<form action="{{ route('profil.password') }}" method="POST" id="formKeamanan" class="d-none">
    @csrf
    @method('PATCH')

    <div class="profileCard">
        <div class="profileSectionTitle">
            <ion-icon name="lock-closed-outline" class="text-lg text-primary"></ion-icon>
            Ganti Kata Sandi
        </div>

        <div class="profileField">
            <label class="profileFieldLabel" for="current_password">Password Lama</label>
            <div class="profileInputGroup">
                <input type="password" name="current_password" id="current_password" class="profileInput" placeholder="Masukkan password saat ini" required autocomplete="current-password">
                <ion-icon name="eye-outline" class="profilePassToggle" onclick="togglePassVisibility('current_password')"></ion-icon>
            </div>
        </div>

        <div class="profileField">
            <label class="profileFieldLabel" for="new_password">Password Baru</label>
            <div class="profileInputGroup">
                <input type="password" name="password" id="new_password" class="profileInput" placeholder="Minimal 8 karakter" required autocomplete="new-password">
                <ion-icon name="eye-outline" class="profilePassToggle" onclick="togglePassVisibility('new_password')"></ion-icon>
            </div>
        </div>

        <div class="profileField">
            <label class="profileFieldLabel" for="confirm_password">Konfirmasi Password Baru</label>
            <div class="profileInputGroup">
                <input type="password" name="password_confirmation" id="confirm_password" class="profileInput" placeholder="Ketik ulang password baru" required autocomplete="new-password">
                <ion-icon name="eye-outline" class="profilePassToggle" onclick="togglePassVisibility('confirm_password')"></ion-icon>
            </div>
        </div>
    </div>
</form>

{{-- ── TAB 3: PUSH NOTIFICATION PWA ── --}}
<div id="sectionNotifikasi" class="d-none">
    <div class="profileCard">
        <div class="profileSectionTitle">
            <ion-icon name="notifications-outline" class="text-lg text-primary"></ion-icon>
            Notifikasi Perangkat (Web Push)
        </div>

        <div class="d-flex items-start gap-3 mb-4">
            <div class="rounded-lg icon-xl flex-shrink-0 avatar-icon-46 bg-success-light text-success">
                <ion-icon name="phone-portrait-outline"></ion-icon>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-md font-extrabold text-dark">Push Notification PWA</div>
                <div class="text-sm text-muted mt-1 line-height-base">
                    Terima konfirmasi presensi masuk dan pengingat checkout harian langsung di layar smartphone Anda.
                </div>
            </div>
        </div>

        <div class="d-flex justify-between items-center pt-3 border-t-base">
            <div>
                <div class="text-xs font-extrabold text-muted text-uppercase letter-spacing-sm">Status Perangkat</div>
                <div id="pushStatusText" class="text-md font-extrabold text-muted mt-1">Memeriksa status...</div>
            </div>
            <button type="button" id="pushToggleBtn" onclick="togglePushSubscription()" class="profileBtnPrimary w-auto text-sm rounded-md px-4 py-2">
                Aktifkan Notifikasi
            </button>
        </div>
    </div>

    <div class="d-flex flex-col gap-2 px-4 mb-4">
        <button type="button" id="pushTestBtn" onclick="handleSendPushTest()" class="profileBtnDanger text-dark border-base bg-card-alt shadow-none">
            <ion-icon name="paper-plane-outline" class="icon-sm text-primary"></ion-icon>
            Kirim Notifikasi Uji Coba ke HP Ini
        </button>
        <div id="pushFeedbackMsg" class="d-none text-sm font-bold text-center p-2 rounded-lg"></div>
    </div>
</div>

{{-- ── ACTION BUTTONS (SIMPAN & KELUAR) ── --}}
<div class="profileActionGroup">
    <button type="button" class="profileBtnPrimary" id="btnSubmitProfile" onclick="submitActiveProfileForm()">
        <ion-icon name="checkmark-circle-outline" class="icon-md"></ion-icon>
        Simpan Perubahan
    </button>

    <form action="{{ route('logout') }}" method="POST" id="logoutForm" class="d-none">
        @csrf
    </form>
    <button type="button" class="profileBtnDanger" onclick="(window.AppNotification ? window.AppNotification.confirm({ title: 'Konfirmasi Keluar', message: 'Apakah Anda yakin ingin keluar dari akun ini?', confirmText: 'Keluar Akun', cancelText: 'Batal', isDanger: true }) : Promise.resolve(confirm('Apakah Anda yakin ingin keluar dari akun ini?'))).then(ok => { if(ok) document.getElementById('logoutForm').submit(); });">
        <ion-icon name="log-out-outline" class="text-xl"></ion-icon>
        Keluar dari Akun
    </button>
</div>

<script>
    let activeProfileTab = 'data';

    function switchProfileTab(tabName) {
        activeProfileTab = tabName;
        const btnData = document.getElementById('tabBtnData');
        const btnKeamanan = document.getElementById('tabBtnKeamanan');
        const btnNotif = document.getElementById('tabBtnNotifikasi');
        const formD = document.getElementById('formData');
        const formK = document.getElementById('formKeamanan');
        const secN = document.getElementById('sectionNotifikasi');
        const btnSave = document.getElementById('btnSubmitProfile');

        btnData.classList.remove('active');
        btnKeamanan.classList.remove('active');
        btnNotif.classList.remove('active');
        formD.style.display = 'none';
        formK.style.display = 'none';
        secN.style.display = 'none';
        btnSave.style.display = 'flex';

        if (tabName === 'data') {
            btnData.classList.add('active');
            formD.style.display = 'block';
        } else if (tabName === 'keamanan') {
            btnKeamanan.classList.add('active');
            formK.style.display = 'block';
        } else if (tabName === 'notifikasi') {
            btnNotif.classList.add('active');
            secN.style.display = 'block';
            btnSave.style.display = 'none';
            if (window.pushManager) {
                window.pushManager.init();
            }
        }
    }

    function submitActiveProfileForm() {
        if (activeProfileTab === 'data') {
            document.getElementById('formData').submit();
        } else if (activeProfileTab === 'keamanan') {
            document.getElementById('formKeamanan').submit();
        }
    }

    function togglePassVisibility(inputId) {
        const input = document.getElementById(inputId);
        const icon = input.parentElement.querySelector('.profilePassToggle');
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('name', 'eye-off-outline');
        } else {
            input.type = 'password';
            icon.setAttribute('name', 'eye-outline');
        }
    }

    function previewProfileImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('avatarPreview');
            const initial = document.getElementById('avatarInitial');
            if (output) {
                output.src = reader.result;
                output.style.display = 'block';
            }
            if (initial) {
                initial.style.display = 'none';
            }
        };
        if(event.target.files && event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }

    async function togglePushSubscription() {
        if (!window.pushManager) return;
        const toggleBtn = document.getElementById('pushToggleBtn');
        toggleBtn.disabled = true;

        try {
            if (window.pushManager.isSubscribed) {
                await window.pushManager.unsubscribe();
                showPushFeedback('Notifikasi perangkat berhasil dimatikan.', 'success');
            } else {
                await window.pushManager.subscribe();
                showPushFeedback('Notifikasi perangkat berhasil diaktifkan!', 'success');
            }
        } catch (err) {
            showPushFeedback(err.message || 'Gagal mengubah status notifikasi.', 'error');
        } finally {
            toggleBtn.disabled = false;
        }
    }

    async function handleSendPushTest() {
        const testBtn = document.getElementById('pushTestBtn');
        testBtn.disabled = true;
        try {
            const res = await window.pushManager.sendTestNotification();
            showPushFeedback(res.message, res.status === 'success' ? 'success' : 'warning');
        } catch (err) {
            showPushFeedback('Gagal mengirim notifikasi tes.', 'error');
        } finally {
            testBtn.disabled = false;
        }
    }

    function showPushFeedback(msg, type) {
        if (window.showAppToast) {
            window.showAppToast({
                type: type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'error'),
                title: type === 'success' ? 'Berhasil' : 'Notifikasi',
                message: msg
            });
        }
        const fb = document.getElementById('pushFeedbackMsg');
        if (!fb) return;
        fb.textContent = msg;
        fb.style.display = 'block';
        if (type === 'success') {
            fb.style.background = 'rgba(22, 163, 74, 0.12)';
            fb.style.color = '#15803d';
            fb.style.border = '1px solid rgba(22, 163, 74, 0.25)';
        } else {
            fb.style.background = 'rgba(239, 68, 68, 0.12)';
            fb.style.color = '#dc2626';
            fb.style.border = '1px solid rgba(239, 68, 68, 0.25)';
        }
        setTimeout(() => { fb.style.display = 'none'; }, 4000);
    }
</script>
@endsection
