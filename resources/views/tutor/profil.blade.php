@extends('layouts.presensi')

@section('title', 'Profil Tutor')

@php
    $user = Auth::user();
    $tutor = $user->tutor;
    $initial = strtoupper(substr($user->nama_lengkap ?? $user->name ?? 'T', 0, 1));
    $fotoUrl = $user->foto
        ? (str_starts_with($user->foto, 'uploads/') ? asset($user->foto) : asset('storage/' . $user->foto))
        : null;
@endphp

@push('topbar')
    @include('layouts.components.navigasi_atas', [
        'titleName' => 'Profil Tutor',
        'subTitle' => $user->nama_lengkap ?? 'Tutor PKBM',
        'dashRoute' => route('tutor.dashboard'),
        'backRoute' => route('tutor.dashboard'),
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
            <img src="" alt="Avatar" class="profileAvatarImg" id="avatarPreview" style="display:none;">
        @endif

        <label for="fotoUpload" class="profileCameraBtn" title="Ganti Foto Profil">
            <ion-icon name="camera"></ion-icon>
        </label>
    </div>

    <div class="profileName">{{ $user->nama_lengkap ?? $user->name }}</div>
    <div class="profileRoleBadge badgeTutor">
        <ion-icon name="school-outline"></ion-icon>
        {{ $tutor?->jabatan ?: 'Tutor PKBM' }}
    </div>

    {{-- Stats Kehadiran --}}
    <div class="profileStatsGrid">
        <div class="profileStatItem">
            <div class="profileStatLabel">Hadir</div>
            <div class="profileStatValue primary">{{ $hadirCount ?? 0 }} Sesi</div>
        </div>
        <div class="profileStatItem">
            <div class="profileStatLabel">Izin</div>
            <div class="profileStatValue">{{ $izinCount ?? 0 }} Kali</div>
        </div>
    </div>
</div>

{{-- ── TAB SWITCHER ── --}}
<div class="profileTabs">
    <button type="button" class="profileTabBtn active" id="tabBtnData" onclick="switchProfileTab('data')">
        <ion-icon name="person-outline"></ion-icon>
        <span>Data Pribadi</span>
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
    <div style="margin: 0 16px 14px; padding: 12px 14px; border-radius: 14px; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.22); color: #dc2626; font-size: 12.5px; font-weight: 700;">
        <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px; font-weight: 800;">
            <ion-icon name="alert-circle-outline" style="font-size: 16px;"></ion-icon>
            Terdapat beberapa kesalahan:
        </div>
        <ul style="margin: 0; padding-left: 18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ── TAB 1: FORM DATA PRIBADI ── --}}
<form action="{{ route('profil.update') }}" method="POST" enctype="multipart/form-data" id="formData">
    @csrf
    @method('PATCH')

    {{-- Hidden file input --}}
    <input type="file" name="foto" id="fotoUpload" accept=".png,.jpg,.jpeg,image/*" style="display:none;" onchange="previewProfileImage(event)">

    <div class="profileCard">
        <div class="profileSectionTitle">
            <ion-icon name="id-card-outline" style="font-size: 15px; color: var(--blue2);"></ion-icon>
            Informasi Tutor
        </div>

        <div class="profileField">
            <label class="profileFieldLabel" for="nik">NIP / NIK Tutor</label>
            <input type="text" name="nik" id="nik" class="profileInput" value="{{ old('nik', $user->nik) }}" placeholder="Nomor Induk Tutor" required>
        </div>

        <div class="profileField">
            <label class="profileFieldLabel" for="nama_lengkap">Nama Lengkap</label>
            <input type="text" name="nama_lengkap" id="nama_lengkap" class="profileInput" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" placeholder="Nama Lengkap Tutor" required>
        </div>

        <div class="profileField">
            <label class="profileFieldLabel" for="email">Alamat Email</label>
            <input type="email" name="email" id="email" class="profileInput" value="{{ old('email', $user->email) }}" placeholder="email@contoh.com" required>
        </div>

        <div class="profileField">
            <label class="profileFieldLabel" for="no_hp">No. Telepon / WhatsApp</label>
            <input type="tel" name="no_hp" id="no_hp" class="profileInput" value="{{ old('no_hp', $user->no_hp) }}" placeholder="08xx-xxxx-xxxx">
        </div>

        <div class="profileField">
            <label class="profileFieldLabel" for="alamat">Alamat Domisili</label>
            <textarea name="alamat" id="alamat" class="profileTextarea" rows="3" placeholder="Alamat lengkap tempat tinggal">{{ old('alamat', $tutor?->alamat) }}</textarea>
        </div>
    </div>
</form>

{{-- ── TAB 2: FORM KEAMANAN SANDI ── --}}
<form action="{{ route('profil.password') }}" method="POST" id="formKeamanan" style="display:none;">
    @csrf
    @method('PATCH')

    <div class="profileCard">
        <div class="profileSectionTitle">
            <ion-icon name="lock-closed-outline" style="font-size: 15px; color: var(--blue2);"></ion-icon>
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
<div id="sectionNotifikasi" style="display:none;">
    <div class="profileCard">
        <div class="profileSectionTitle">
            <ion-icon name="notifications-outline" style="font-size: 15px; color: var(--blue2);"></ion-icon>
            Notifikasi Perangkat (Web Push)
        </div>

        <div style="display: flex; align-items: flex-start; gap: 14px; margin-bottom: 16px;">
            <div style="width: 46px; height: 46px; border-radius: 14px; background: rgba(11, 94, 215, 0.12); display: grid; place-items: center; color: var(--blue2); font-size: 24px; flex-shrink: 0;">
                <ion-icon name="phone-portrait-outline"></ion-icon>
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-size: 14px; font-weight: 800; color: var(--text);">Push Notification PWA</div>
                <div style="font-size: 12px; color: var(--muted); margin-top: 3px; line-height: 1.4;">
                    Terima pengingat jadwal mengajar, approval izin, dan info presensi langsung di layar HP saat aplikasi tidak dibuka.
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 14px; border-top: 1px solid var(--border);">
            <div>
                <div style="font-size: 10.5px; font-weight: 800; color: var(--muted); text-transform: uppercase; letter-spacing: 0.4px;">Status Perangkat</div>
                <div id="pushStatusText" style="font-size: 13px; font-weight: 800; color: var(--muted); margin-top: 2px;">Memeriksa status...</div>
            </div>
            <button type="button" id="pushToggleBtn" onclick="togglePushSubscription()" class="profileBtnPrimary" style="width: auto; padding: 8px 16px; font-size: 12px; border-radius: 10px;">
                Aktifkan Notifikasi
            </button>
        </div>
    </div>

    <div style="margin: 0 16px 16px; display: flex; flex-direction: column; gap: 8px;">
        <button type="button" id="pushTestBtn" onclick="handleSendPushTest()" class="profileBtnDanger" style="background: var(--card-alt); color: var(--text); border: 1px solid var(--border); box-shadow: none;">
            <ion-icon name="paper-plane-outline" style="font-size: 16px; color: var(--blue2);"></ion-icon>
            Kirim Notifikasi Uji Coba ke HP Ini
        </button>
        <div id="pushFeedbackMsg" style="display: none; font-size: 12px; font-weight: 700; text-align: center; padding: 10px 14px; border-radius: 12px;"></div>
    </div>
</div>

{{-- ── ACTION BUTTONS (SIMPAN & KELUAR) ── --}}
<div class="profileActionGroup">
    <button type="button" class="profileBtnPrimary" id="btnSubmitProfile" onclick="submitActiveProfileForm()">
        <ion-icon name="checkmark-circle-outline" style="font-size: 18px;"></ion-icon>
        Simpan Perubahan
    </button>

    <form action="{{ route('logout') }}" method="POST" id="logoutForm" style="display:none;">
        @csrf
    </form>
    <button type="button" class="profileBtnDanger" onclick="(window.AppNotification ? window.AppNotification.confirm({ title: 'Konfirmasi Keluar', message: 'Apakah Anda yakin ingin keluar dari akun ini?', confirmText: 'Keluar Akun', cancelText: 'Batal', isDanger: true }) : Promise.resolve(confirm('Apakah Anda yakin ingin keluar dari akun ini?'))).then(ok => { if(ok) document.getElementById('logoutForm').submit(); });">
        <ion-icon name="log-out-outline" style="font-size: 17px;"></ion-icon>
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
