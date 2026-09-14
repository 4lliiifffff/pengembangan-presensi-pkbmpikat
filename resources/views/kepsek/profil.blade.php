@extends('layouts.kepsek')

@section('title', 'Profil Kepala Sekolah')

@section('content')
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
