@extends('layouts.admin')

@section('title', 'Profil')

@section('content')


@php
    $user = Auth::user();
    $initial = strtoupper(substr($user->nama_lengkap, 0, 1));
    $fotoUrl = $user->foto
        ? (str_starts_with($user->foto, 'uploads/') ? asset($user->foto) : asset('storage/' . $user->foto))
        : null;
    $roleLabel = match ($user->role) {
        'admin' => 'Administrator',
        'kepala_sekolah' => 'Kepala Sekolah',
        default => 'Administrator',
    };
@endphp

<div class="profile-header">
    <div class="profile-nav">
        <a href="javascript:history.back()" class="back-btn">
            <ion-icon name="arrow-back-outline"></ion-icon>
        </a>
        <div class="title">Profil</div>
        <button class="theme-btn" type="button" aria-label="Tema" id="themeToggleBtn">
            <ion-icon name="moon-outline" id="themeToggleIcon"></ion-icon>
        </button>
    </div>

    <div class="avatar-wrapper">
        @if($fotoUrl)
            <img src="{{ $fotoUrl }}" alt="Avatar" class="avatar-img" id="avatarPreview">
        @else
            <div class="avatar-initial" id="avatarInitial">{{ $initial }}</div>
            <img src="" alt="Avatar" class="avatar-img" id="avatarPreview" style="display:none;">
        @endif

        <label for="fotoUpload" class="camera-btn">
            <ion-icon name="camera"></ion-icon>
        </label>
    </div>

    <div class="user-name">{{ $user->nama_lengkap }}</div>
    <div class="user-role">{{ $roleLabel }}</div>
</div>

<div class="tab-container">
    <div class="tab-btn active" onclick="switchTab('data')">Informasi Pribadi</div>
    <div class="tab-btn" onclick="switchTab('keamanan')">Keamanan</div>
</div>

<!-- Forms -->
<form action="{{ route('profil.update') }}" method="POST" enctype="multipart/form-data" id="formData">
    @csrf
    @method('PATCH')

    <!-- Hidden file input -->
    <input type="file" name="foto" id="fotoUpload" accept=".png,.jpg,.jpeg" style="display:none;" onchange="previewImage(event)">

    <div class="contentPad">
        <div class="formCard">
            <!-- Input NIP -->
            <div class="formRow">
                <label class="fieldLabel">NIP / NIK</label>
                <!-- Menggunakan validasi standar -->
                <input type="text" name="nik" class="input" value="{{ old('nik', $user->nik) }}" placeholder="Nomor Induk / Username" required>
            </div>

            <div class="formRow">
                <label class="fieldLabel">NAMA LENGKAP</label>
                <input type="text" name="nama_lengkap" class="input" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required>
            </div>

            <div class="formRow">
                <label class="fieldLabel">EMAIL</label>
                <input type="email" name="email" class="input" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="formRow">
                <label class="fieldLabel">NO. TELEPON</label>
                <input type="text" name="no_hp" class="input" value="{{ old('no_hp', $user->no_hp) }}">
            </div>
        </div>
    </div>
</form>

<form action="{{ route('profil.password') }}" method="POST" id="formKeamanan" style="display:none;">
    @csrf
    @method('PATCH')

    <div class="contentPad">
        <div class="sectionLabel">UBAH KATA SANDI</div>

        <div class="formRow">
            <label class="fieldLabel">PASSWORD LAMA</label>
            <div class="input-group">
                <input type="password" name="current_password" id="current_password" class="input" required>
                <ion-icon name="eye-outline" class="pass-toggle" onclick="togglePass('current_password')"></ion-icon>
            </div>
        </div>

        <div class="formRow">
            <label class="fieldLabel">PASSWORD BARU</label>
            <div class="input-group">
                <input type="password" name="password" id="new_password" class="input" required>
                <ion-icon name="eye-outline" class="pass-toggle" onclick="togglePass('new_password')"></ion-icon>
            </div>
        </div>

        <div class="formRow">
            <label class="fieldLabel">KONFIRMASI PASSWORD BARU</label>
            <div class="input-group">
                <input type="password" name="password_confirmation" id="confirm_password" class="input" required>
                <ion-icon name="eye-outline" class="pass-toggle" onclick="togglePass('confirm_password')"></ion-icon>
            </div>
        </div>
    </div>
</form>

<!-- Fixed Actions -->
<div class="fixed-bottom-actions">
    <button type="button" class="btn-save" onclick="submitActiveForm()">Simpan Perubahan</button>

    <form action="{{ route('logout') }}" method="POST" id="logoutForm" style="display:none;">
        @csrf
    </form>
    <button type="button" class="btn-logout" onclick="document.getElementById('logoutForm').submit()">
        <ion-icon name="log-out-outline" class="icon-md"></ion-icon> Keluar dari Akun
    </button>
</div>

<!-- Errors Display -->
@if ($errors->any())
    <div class="contentPad">
        <div class="errorList">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<script>
    let activeTab = 'data';

    function switchTab(tabName) {
        activeTab = tabName;
        const btns = document.querySelectorAll('.tab-btn');
        btns.forEach(btn => btn.classList.remove('active'));

        if (tabName === 'data') {
            btns[0].classList.add('active');
            document.getElementById('formData').style.display = 'block';
            document.getElementById('formKeamanan').style.display = 'none';
        } else {
            btns[1].classList.add('active');
            document.getElementById('formData').style.display = 'none';
            document.getElementById('formKeamanan').style.display = 'block';
        }
    }

    function submitActiveForm() {
        if (activeTab === 'data') {
            document.getElementById('formData').submit();
        } else {
            document.getElementById('formKeamanan').submit();
        }
    }

    function togglePass(id) {
        const input = document.getElementById(id);
        const icon = input.nextElementSibling;
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('name', 'eye-off-outline');
        } else {
            input.type = 'password';
            icon.setAttribute('name', 'eye-outline');
        }
    }

    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('avatarPreview');
            const initial = document.getElementById('avatarInitial');
            if (output) {
                output.src = reader.result;
                output.style.display = 'block';
            }
            if (initial) initial.style.display = 'none';
        };
        if(event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>
@endsection
