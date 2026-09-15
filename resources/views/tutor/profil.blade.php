@extends('layouts.presensi')

@section('title', 'Profil Tutor')

@section('content')
@php
    $user = Auth::user();
    $tutor = $user->tutor;
    $initial = strtoupper(substr($user->nama_lengkap, 0, 1));
    $fotoUrl = $user->foto
        ? (str_starts_with($user->foto, 'uploads/') ? asset($user->foto) : asset('storage/' . $user->foto))
        : null;
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
        <!-- The file input is part of the form below -->
    </div>

    <div class="user-name">{{ $user->nama_lengkap }}</div>
    <div class="user-role">{{ $tutor?->jabatan ?: 'Tutor' }}</div>
    
    <!-- Stats Badge -->
    <div class="profileStatsGrid">
        <div class="profileStatItem">
            <div class="profileStatLabel">HADIR</div>
            <div class="profileStatValue primary">{{ $hadirCount ?? 0 }} Hari</div>
        </div>
        <div class="profileStatItem">
            <div class="profileStatLabel">IZIN</div>
            <div class="profileStatValue">{{ $izinCount ?? 0 }} Hari</div>
        </div>
    </div>
</div>

<div class="tab-container">
    <div class="tab-btn active" onclick="switchTab('data')">Informasi Pribadi</div>
    <div class="tab-btn" onclick="switchTab('keamanan')">Keamanan</div>
    <div class="tab-btn" onclick="switchTab('notifikasi')">Notifikasi</div>
</div>

<!-- Forms -->
<form action="{{ route('profil.update') }}" method="POST" enctype="multipart/form-data" id="formData">
    @csrf
    @method('PATCH')
    
    <!-- Hidden file input -->
    <input type="file" name="foto" id="fotoUpload" accept=".png,.jpg,.jpeg" style="display:none;" onchange="previewImage(event)">

    <div class="contentPad">
        <!-- Input NIP -->
        <div class="formRow">
            <label class="fieldLabel">NIP</label>
            <input type="text" name="nik" class="input" value="{{ old('nik', $user->nik) }}" placeholder="Nomor Induk Tutor" required>
        </div>

        <div class="formRow">
            <label class="fieldLabel">NAMA LENGKAP</label>
            <input type="text" name="nama_lengkap" class="input" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required>
        </div>

        <div class="formRow">
            <label class="fieldLabel">EMAIL</label>
            <!-- Menggunakan validasi email standar bawaan HTML -->
            <input type="email" name="email" class="input" value="{{ old('email', $user->email) }}" required>
        </div>

        <div class="formRow">
            <label class="fieldLabel">NO. TELEPON</label>
            <input type="text" name="no_hp" class="input" value="{{ old('no_hp', $user->no_hp) }}">
        </div>

        <div class="formRow">
            <label class="fieldLabel">ALAMAT</label>
            <textarea name="alamat" class="input input--no-resize" rows="3">{{ old('alamat', $tutor?->alamat) }}</textarea>
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

{{-- ── TAB NOTIFIKASI WEB PUSH ── --}}
<div id="sectionNotifikasi" style="display:none;">
    <div class="contentPad">
        <div class="sectionLabel">NOTIFIKASI PERANGKAT (WEB PUSH)</div>
        
        <div class="card" style="padding: 16px; border-radius: 18px; margin-bottom: 16px;">
            <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px;">
                <div style="width: 44px; height: 44px; border-radius: 14px; background: rgba(11, 94, 215, 0.12); display: grid; place-items: center; color: var(--blue2); font-size: 22px; flex-shrink: 0;">
                    <ion-icon name="notifications-outline"></ion-icon>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 14px; font-weight: 800; color: var(--text);">Push Notification PWA</div>
                    <div style="font-size: 12px; color: var(--muted); margin-top: 2px;">
                        Terima pengingat jadwal mengajar, approval izin, dan info penting langsung di layar HP Anda saat aplikasi tidak dibuka.
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid var(--border);">
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase;">Status Perangkat</div>
                    <div id="pushStatusText" style="font-size: 13px; font-weight: 800; color: var(--muted); margin-top: 2px;">Memeriksa status...</div>
                </div>
                <button type="button" id="pushToggleBtn" onclick="togglePushSubscription()" class="btnPrimary" style="padding: 8px 14px; font-size: 12px;">
                    Aktifkan Notifikasi
                </button>
            </div>
        </div>

        <div style="display: flex; gap: 8px; flex-direction: column;">
            <button type="button" id="pushTestBtn" onclick="handleSendPushTest()" class="btnPrimary" style="display: none; justify-content: center; background: var(--card-alt); color: var(--text); border: 1px solid var(--border); box-shadow: none;">
                <ion-icon name="paper-plane-outline" style="font-size: 16px; color: var(--blue2);"></ion-icon> Kirim Notifikasi Uji Coba ke HP Ini
            </button>
            <div id="pushFeedbackMsg" style="display: none; font-size: 12px; font-weight: 700; text-align: center; padding: 8px 12px; border-radius: 12px;"></div>
        </div>
    </div>
</div>

<!-- Fixed Actions -->
<div class="fixed-bottom-actions" id="fixedActionsGroup">
    <button type="button" class="btn-save" id="btnSaveData" onclick="submitActiveForm()">Simpan Perubahan</button>
    
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
        
        document.getElementById('formData').style.display = 'none';
        document.getElementById('formKeamanan').style.display = 'none';
        document.getElementById('sectionNotifikasi').style.display = 'none';
        document.getElementById('btnSaveData').style.display = 'block';

        if (tabName === 'data') {
            btns[0].classList.add('active');
            document.getElementById('formData').style.display = 'block';
        } else if (tabName === 'keamanan') {
            btns[1].classList.add('active');
            document.getElementById('formKeamanan').style.display = 'block';
        } else if (tabName === 'notifikasi') {
            btns[2].classList.add('active');
            document.getElementById('sectionNotifikasi').style.display = 'block';
            document.getElementById('btnSaveData').style.display = 'none';
            if (window.pushManager) {
                window.pushManager.init();
            }
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

    function submitActiveForm() {
        if (activeTab === 'data') {
            document.getElementById('formData').submit();
        } else if (activeTab === 'keamanan') {
            document.getElementById('formKeamanan').submit();
        }
    }

    function togglePass(fieldId) {
        const input = document.getElementById(fieldId);
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const preview = document.getElementById('avatarPreview');
            const initial = document.getElementById('avatarInitial');
            preview.src = reader.result;
            preview.style.display = 'block';
            if (initial) initial.style.display = 'none';
        }
        if(event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>
@endsection
