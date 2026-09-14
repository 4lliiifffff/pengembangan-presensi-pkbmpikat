@extends('layout.kepsek')

@section('title', 'Profil Kepala Sekolah')

@section('content')
<style>
    /* Sembunyikan bottom navigation kepsek standar di halaman profil */
    .bottomNavAdmin { display: none !important; }

    /* Ganti padding bottom agar tidak tertutupi fixed actions */
    #appCapsule {
        padding-bottom: calc(120px + env(safe-area-inset-bottom, 0px));
    }

    /* --- Profile Header --- */
    .profile-header {
        padding: 20px 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }

    .profile-nav {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .profile-nav .back-btn,
    .profile-nav .theme-btn {
        min-width: 44px;
        min-height: 44px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 24px;
        color: var(--text);
        text-decoration: none;
        background: transparent;
        border: none;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
        border-radius: 50%;
    }

    .profile-nav .title {
        font-size: 18px;
        font-weight: 800;
        color: var(--text);
    }

    /* Avatar */
    .avatar-wrapper {
        position: relative;
        width: 100px;
        height: 100px;
        margin-bottom: 15px;
        margin-top: 6px;
    }

    .avatar-img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        background: var(--avatar-bg);
        border: 4px solid var(--border);
    }

    .avatar-initial {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: var(--avatar-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        font-weight: 900;
        color: var(--avatar-text);
        border: 4px solid var(--border);
    }

    .camera-btn {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--blue2);
        color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 16px;
        border: 2px solid var(--card);
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }

    .user-name {
        font-size: 18px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 4px;
        text-align: center;
        word-break: break-word;
        max-width: 100%;
    }

    .user-role {
        font-size: 13px;
        color: var(--muted);
        font-weight: 600;
        text-align: center;
        margin-bottom: 20px;
    }

    /* Tab */
    .tab-container {
        display: flex;
        background: var(--card-alt);
        border-radius: 12px;
        padding: 4px;
        margin: 0 16px 20px;
    }

    .tab-btn {
        flex: 1;
        padding: 11px 0;
        text-align: center;
        font-size: 13px;
        font-weight: 800;
        color: var(--muted);
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
        -webkit-tap-highlight-color: transparent;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .tab-btn.active {
        background: var(--card);
        color: var(--blue2);
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    }

    /* Form area */
    .contentPad {
        padding: 0 16px 20px;
    }

    .formCard {
        margin: 0 0 14px;
        padding: 14px;
        border: 1px solid var(--border);
        border-radius: 18px;
        background: var(--card-alt);
    }

    .formRow {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 12px;
    }

    .formRow:last-child { margin-bottom: 0; }

    .fieldLabel {
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: var(--muted);
    }

    .input {
        width: 100%;
        padding: 13px 14px;
        border-radius: 14px;
        border: 1px solid var(--border);
        background: var(--input-bg);
        outline: none;
        font-size: 14px;
        color: var(--text);
        -webkit-appearance: none;
        appearance: none;
        min-height: 48px;
        box-sizing: border-box;
    }

    .input:focus {
        border-color: rgba(11,94,215,0.45);
        box-shadow: 0 0 0 3px rgba(11,94,215,0.12);
    }

    textarea.input {
        resize: none;
        min-height: 88px;
        line-height: 1.5;
    }

    /* Password toggle */
    .input-group { position: relative; }

    .pass-toggle {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--muted);
        cursor: pointer;
        font-size: 20px;
        padding: 4px;
        -webkit-tap-highlight-color: transparent;
    }

    /* Section heading inside form */
    .formSectionLabel {
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 14px;
        margin-top: 4px;
        padding-left: 2px;
    }

    /* Fixed bottom actions */
    .fixed-bottom-actions {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        margin: 0 auto;
        max-width: 430px;
        background: var(--nav-bg);
        border-top: 1px solid var(--border);
        padding: 14px 16px;
        padding-bottom: calc(14px + env(safe-area-inset-bottom, 0px));
        display: flex;
        gap: 12px;
        z-index: 1000;
    }

    .btn-save {
        flex: 1;
        background: var(--blue2);
        color: #fff;
        border: none;
        border-radius: 14px;
        padding: 14px 0;
        font-weight: 900;
        font-size: 14px;
        cursor: pointer;
        min-height: 50px;
        -webkit-tap-highlight-color: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-logout {
        flex: 1;
        background: rgba(239,68,68,0.07);
        color: var(--danger);
        border: 1px solid rgba(239,68,68,0.2);
        border-radius: 14px;
        padding: 14px 0;
        font-weight: 900;
        font-size: 14px;
        cursor: pointer;
        min-height: 50px;
        -webkit-tap-highlight-color: transparent;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
    }

    /* Error list */
    .errorList {
        margin: 0 16px 12px;
        padding: 10px 12px;
        border-radius: 16px;
        border: 1px solid rgba(239,68,68,0.25);
        background: rgba(239,68,68,0.08);
        color: #dc2626;
        font-weight: 900;
        font-size: 12px;
    }
</style>

@php
    $user    = Auth::user();
    $initial = strtoupper(substr($user->nama_lengkap ?? $user->name ?? 'K', 0, 1));
    $fotoUrl = $user->foto
        ? (str_starts_with($user->foto, 'uploads/') ? asset($user->foto) : asset('storage/' . $user->foto))
        : null;
@endphp

{{-- ── Profile Header ── --}}
<div class="profile-header">
    <div class="profile-nav">
        <a href="javascript:history.back()" class="back-btn" aria-label="Kembali">
            <ion-icon name="arrow-back-outline"></ion-icon>
        </a>
        <div class="title">Profil</div>
        <button class="theme-btn" type="button" aria-label="Tema" id="themeToggleBtnProfil">
            <ion-icon name="moon-outline" id="themeToggleIconProfil"></ion-icon>
        </button>
    </div>

    {{-- Avatar --}}
    <div class="avatar-wrapper">
        @if($fotoUrl)
            <img src="{{ $fotoUrl }}" alt="Avatar" class="avatar-img" id="avatarPreview">
        @else
            <div class="avatar-initial" id="avatarInitial">{{ $initial }}</div>
            <img src="" alt="Avatar" class="avatar-img" id="avatarPreview" style="display:none;">
        @endif

        <label for="fotoUpload" class="camera-btn" aria-label="Ganti foto">
            <ion-icon name="camera"></ion-icon>
        </label>
    </div>

    <div class="user-name">{{ $user->nama_lengkap ?? $user->name }}</div>
    <div class="user-role">Kepala Sekolah</div>
</div>

{{-- ── Tabs ── --}}
<div class="tab-container">
    <div class="tab-btn active" id="tabData" onclick="switchTab('data')">Informasi Pribadi</div>
    <div class="tab-btn" id="tabKeamanan" onclick="switchTab('keamanan')">Keamanan</div>
</div>

{{-- Error messages --}}
@if ($errors->any())
    <div class="errorList">
        <ul style="margin:0; padding-left:14px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ── Form Data Pribadi ── --}}
<form action="{{ route('profil.update') }}" method="POST" enctype="multipart/form-data" id="formData">
    @csrf
    @method('PATCH')

    {{-- Hidden file input --}}
    <input type="file" name="foto" id="fotoUpload" accept="image/*" style="display:none;" onchange="previewImage(event)">

    <div class="contentPad">
        <div class="formCard">
            <div class="formRow">
                <label class="fieldLabel" for="nik">NIP / NIK</label>
                <input type="text" name="nik" id="nik" class="input"
                       value="{{ old('nik', $user->nik) }}"
                       placeholder="Nomor Induk" autocomplete="off">
            </div>

            <div class="formRow">
                <label class="fieldLabel" for="nama_lengkap">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="nama_lengkap" class="input"
                       value="{{ old('nama_lengkap', $user->nama_lengkap) }}"
                       required autocomplete="name">
            </div>

            <div class="formRow">
                <label class="fieldLabel" for="email">Email</label>
                <input type="email" name="email" id="email" class="input"
                       value="{{ old('email', $user->email) }}"
                       required autocomplete="email">
            </div>

            <div class="formRow">
                <label class="fieldLabel" for="no_hp">No. Telepon</label>
                <input type="tel" name="no_hp" id="no_hp" class="input"
                       value="{{ old('no_hp', $user->no_hp) }}"
                       placeholder="08xx-xxxx-xxxx" autocomplete="tel">
            </div>
        </div>
    </div>
</form>

{{-- ── Form Keamanan ── --}}
<form action="{{ route('profil.password') }}" method="POST" id="formKeamanan" style="display:none;">
    @csrf
    @method('PATCH')

    <div class="contentPad">
        <div class="formCard">
            <div class="formSectionLabel">Ubah Kata Sandi</div>

            <div class="formRow">
                <label class="fieldLabel" for="current_password">Password Lama</label>
                <div class="input-group">
                    <input type="password" name="current_password" id="current_password"
                           class="input" required autocomplete="current-password"
                           style="padding-right: 46px;">
                    <ion-icon name="eye-outline" class="pass-toggle"
                              onclick="togglePass('current_password')"></ion-icon>
                </div>
            </div>

            <div class="formRow">
                <label class="fieldLabel" for="new_password">Password Baru</label>
                <div class="input-group">
                    <input type="password" name="password" id="new_password"
                           class="input" required autocomplete="new-password"
                           style="padding-right: 46px;">
                    <ion-icon name="eye-outline" class="pass-toggle"
                              onclick="togglePass('new_password')"></ion-icon>
                </div>
            </div>

            <div class="formRow">
                <label class="fieldLabel" for="confirm_password">Konfirmasi Password Baru</label>
                <div class="input-group">
                    <input type="password" name="password_confirmation" id="confirm_password"
                           class="input" required autocomplete="new-password"
                           style="padding-right: 46px;">
                    <ion-icon name="eye-outline" class="pass-toggle"
                              onclick="togglePass('confirm_password')"></ion-icon>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- ── Fixed Bottom Actions ── --}}
<div class="fixed-bottom-actions">
    <button type="button" class="btn-save" onclick="submitActiveForm()">
        <ion-icon name="checkmark-outline" style="font-size:18px;"></ion-icon>
        Simpan
    </button>

    <form action="{{ route('logout') }}" method="POST" id="logoutForm" style="display:none;">
        @csrf
    </form>
    <button type="button" class="btn-logout"
            onclick="document.getElementById('logoutForm').submit()">
        <ion-icon name="log-out-outline" style="font-size:18px;"></ion-icon>
        Keluar
    </button>
</div>

<div style="height:12px;"></div>

<script>
    /* ── Tab switching ── */
    let activeTab = 'data';

    function switchTab(tabName) {
        activeTab = tabName;
        document.getElementById('tabData').classList.toggle('active', tabName === 'data');
        document.getElementById('tabKeamanan').classList.toggle('active', tabName === 'keamanan');
        document.getElementById('formData').style.display      = tabName === 'data'      ? 'block' : 'none';
        document.getElementById('formKeamanan').style.display  = tabName === 'keamanan'  ? 'block' : 'none';
    }

    function submitActiveForm() {
        if (activeTab === 'data') {
            document.getElementById('formData').submit();
        } else {
            document.getElementById('formKeamanan').submit();
        }
    }

    /* ── Password visibility toggle ── */
    function togglePass(id) {
        const input = document.getElementById(id);
        const icon  = input.nextElementSibling;
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('name', 'eye-off-outline');
        } else {
            input.type = 'password';
            icon.setAttribute('name', 'eye-outline');
        }
    }

    /* ── Photo preview ── */
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function () {
            const output  = document.getElementById('avatarPreview');
            const initial = document.getElementById('avatarInitial');
            if (output) { output.src = reader.result; output.style.display = 'block'; }
            if (initial) initial.style.display = 'none';
        };
        if (event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }

    /* ── Theme toggle (independent of layout topbar button) ── */
    document.addEventListener('DOMContentLoaded', () => {
        const btn  = document.getElementById('themeToggleBtnProfil');
        const icon = document.getElementById('themeToggleIconProfil');
        const root = document.documentElement;

        function updateIcon() {
            if (!icon) return;
            icon.setAttribute('name', root.getAttribute('data-theme') === 'dark'
                ? 'sunny-outline' : 'moon-outline');
        }
        updateIcon();

        if (btn) {
            btn.addEventListener('click', () => {
                const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                root.setAttribute('data-theme', next);
                localStorage.setItem('theme', next);
                updateIcon();
                /* also sync layout topbar icon */
                const layoutIcon = document.getElementById('themeToggleIcon');
                if (layoutIcon) layoutIcon.setAttribute('name', next === 'dark' ? 'sunny-outline' : 'moon-outline');
            });
        }
    });
</script>
@endsection
