@extends('layouts.kepsek')

@section('title', 'Profil Kepala Sekolah')

@section('content')
@php
    $user = Auth::user();
    $initial = strtoupper(substr($user->nama_lengkap ?? $user->name ?? 'K', 0, 1));
    $fotoUrl = $user->foto
        ? (str_starts_with($user->foto, 'uploads/') ? asset($user->foto) : asset('storage/' . $user->foto))
        : null;
@endphp

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
    <div class="profileRoleBadge badgeKepsek">
        <ion-icon name="school-outline"></ion-icon>
        Kepala Sekolah
    </div>
</div>

{{-- ── TAB SWITCHER ── --}}
<div class="profileTabs">
    <button type="button" class="profileTabBtn active" id="tabBtnData" onclick="switchProfileTab('data')">
        <ion-icon name="person-outline"></ion-icon>
        <span>Informasi Pribadi</span>
    </button>
    <button type="button" class="profileTabBtn" id="tabBtnKeamanan" onclick="switchProfileTab('keamanan')">
        <ion-icon name="key-outline"></ion-icon>
        <span>Keamanan Sandi</span>
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
            Data Akun Kepala Sekolah
        </div>

        <div class="profileField">
            <label class="profileFieldLabel" for="nik">NIP / NIK</label>
            <input type="text" name="nik" id="nik" class="profileInput" value="{{ old('nik', $user->nik) }}" placeholder="Nomor Induk Pegawai">
        </div>

        <div class="profileField">
            <label class="profileFieldLabel" for="nama_lengkap">Nama Lengkap</label>
            <input type="text" name="nama_lengkap" id="nama_lengkap" class="profileInput" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" placeholder="Nama Lengkap beserta Gelar" required>
        </div>

        <div class="profileField">
            <label class="profileFieldLabel" for="email">Alamat Email</label>
            <input type="email" name="email" id="email" class="profileInput" value="{{ old('email', $user->email) }}" placeholder="email@contoh.com" required>
        </div>

        <div class="profileField">
            <label class="profileFieldLabel" for="no_hp">No. Telepon / WhatsApp</label>
            <input type="tel" name="no_hp" id="no_hp" class="profileInput" value="{{ old('no_hp', $user->no_hp) }}" placeholder="08xx-xxxx-xxxx">
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
        const formD = document.getElementById('formData');
        const formK = document.getElementById('formKeamanan');

        if (tabName === 'data') {
            btnData.classList.add('active');
            btnKeamanan.classList.remove('active');
            formD.style.display = 'block';
            formK.style.display = 'none';
        } else {
            btnKeamanan.classList.add('active');
            btnData.classList.remove('active');
            formD.style.display = 'none';
            formK.style.display = 'block';
        }
    }

    function submitActiveProfileForm() {
        if (activeProfileTab === 'data') {
            document.getElementById('formData').submit();
        } else {
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
</script>
@endsection
