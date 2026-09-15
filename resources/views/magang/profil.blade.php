@extends('layouts.presensi')

@section('title', 'Profil Mahasiswa / Siswa Magang')

@php
    $user = Auth::user();
    $magang = $user->magang;
    $initial = strtoupper(substr($user->nama_lengkap ?? $user->name, 0, 1));
    $fotoUrl = $user->foto
        ? (str_starts_with($user->foto, 'uploads/') ? asset($user->foto) : asset('storage/' . $user->foto))
        : null;
@endphp

@push('topbar')
    <div class="profile-nav" style="padding: 14px 16px 0;">
        <a href="{{ route('magang.dashboard') }}" class="back-btn">
            <ion-icon name="arrow-back-outline"></ion-icon>
        </a>
        <div class="title">Profil Magang</div>
        <button class="theme-btn" type="button" aria-label="Tema" id="themeToggleBtn">
            <ion-icon name="moon-outline" id="themeToggleIcon"></ion-icon>
        </button>
    </div>
@endpush

@section('content')

<div class="profile-header">

    <div class="avatar-wrapper">
        @if($fotoUrl)
            <img src="{{ $fotoUrl }}" alt="Avatar" class="avatar-img" id="avatarPreview">
        @else
            <div class="avatar-initial" id="avatarInitial">{{ $initial }}</div>
            <img src="" alt="Avatar" class="avatar-img" id="avatarPreview" style="display:none;">
        @endif
    </div>

    <div class="user-name">{{ $user->nama_lengkap ?? $user->name }}</div>
    <div class="user-role">{{ $magang?->asal_instansi ?: 'Peserta Magang' }}</div>
    
    <!-- Stats Badge -->
    <div class="profileStatsGrid" style="display: flex; justify-content: center; gap: 16px; margin-top: 14px;">
        <div class="profileStatItem" style="background: rgba(255,255,255,0.15); padding: 8px 16px; border-radius: 12px; text-align: center;">
            <div class="profileStatLabel" style="font-size: 11px; opacity: 0.8;">TOTAL HADIR</div>
            <div class="profileStatValue" style="font-size: 16px; font-weight: 800;">{{ $hadirCount ?? 0 }} Hari</div>
        </div>
    </div>
</div>

<div class="tab-container">
    <div class="tab-btn active" onclick="switchTab('data')">Informasi Magang</div>
    <div class="tab-btn" onclick="switchTab('keamanan')">Keamanan Akun</div>
    <div class="tab-btn" onclick="switchTab('notifikasi')">Notifikasi</div>
</div>

<div class="profile-content">
    {{-- TAB 1: INFORMASI MAGANG --}}
    <div id="tab-data" class="tab-pane active">
        <div class="card" style="padding: 16px; border-radius: 16px; margin-bottom: 16px; background: var(--bg-card, #fff); border: 1px solid var(--border-color, #e2e8f0);">
            <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-size: 12px; color: var(--text-secondary, #64748b); display: block; margin-bottom: 4px;">NIM / NISN / NIK</label>
                <div style="font-size: 14px; font-weight: 700; color: var(--text-primary, #0f172a);">{{ $magang?->nim_nisn ?: $user->nik }}</div>
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-size: 12px; color: var(--text-secondary, #64748b); display: block; margin-bottom: 4px;">Asal Kampus / Sekolah</label>
                <div style="font-size: 14px; font-weight: 700; color: var(--text-primary, #0f172a);">{{ $magang?->asal_instansi ?: '—' }}</div>
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-size: 12px; color: var(--text-secondary, #64748b); display: block; margin-bottom: 4px;">Program Studi / Jurusan</label>
                <div style="font-size: 14px; font-weight: 700; color: var(--text-primary, #0f172a);">{{ $magang?->jurusan ?: '—' }}</div>
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-size: 12px; color: var(--text-secondary, #64748b); display: block; margin-bottom: 4px;">Periode Magang</label>
                <div style="font-size: 14px; font-weight: 700; color: var(--text-primary, #0f172a);">
                    {{ $magang?->tgl_mulai ? \Carbon\Carbon::parse($magang->tgl_mulai)->translatedFormat('d M Y') : '—' }} 
                    s/d 
                    {{ $magang?->tgl_selesai ? \Carbon\Carbon::parse($magang->tgl_selesai)->translatedFormat('d M Y') : '—' }}
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-size: 12px; color: var(--text-secondary, #64748b); display: block; margin-bottom: 4px;">No. WhatsApp / HP</label>
                <div style="font-size: 14px; font-weight: 700; color: var(--text-primary, #0f172a);">{{ $user->no_hp ?: '—' }}</div>
            </div>
            <div class="form-group" style="margin-bottom: 4px;">
                <label style="font-size: 12px; color: var(--text-secondary, #64748b); display: block; margin-bottom: 4px;">Email Akun</label>
                <div style="font-size: 14px; font-weight: 700; color: var(--text-primary, #0f172a);">{{ $user->email }}</div>
            </div>
        </div>
    </div>

    {{-- TAB 2: KEAMANAN (UBAH PASSWORD) --}}
    <div id="tab-keamanan" class="tab-pane" style="display:none;">
        <form action="{{ route('magang.profil.password') }}" method="POST">
            @csrf
            <div class="card" style="padding: 16px; border-radius: 16px; margin-bottom: 16px; background: var(--bg-card, #fff); border: 1px solid var(--border-color, #e2e8f0);">
                <div class="form-group" style="margin-bottom: 14px;">
                    <label style="font-size: 12px; font-weight: 600; color: var(--text-primary, #0f172a); display: block; margin-bottom: 6px;">Password Lama</label>
                    <input type="password" name="old_password" class="input" required style="width: 100%; border-radius: 10px; padding: 10px; border: 1px solid var(--border-color, #cbd5e1);">
                </div>
                <div class="form-group" style="margin-bottom: 14px;">
                    <label style="font-size: 12px; font-weight: 600; color: var(--text-primary, #0f172a); display: block; margin-bottom: 6px;">Password Baru</label>
                    <input type="password" name="password" class="input" required minlength="6" style="width: 100%; border-radius: 10px; padding: 10px; border: 1px solid var(--border-color, #cbd5e1);">
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 600; color: var(--text-primary, #0f172a); display: block; margin-bottom: 6px;">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" class="input" required minlength="6" style="width: 100%; border-radius: 10px; padding: 10px; border: 1px solid var(--border-color, #cbd5e1);">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; border-radius: 10px; background: #0b5ed7; color: white; font-weight: 700; border: none;">
                    Perbarui Password
                </button>
            </div>
        </form>
    </div>

    {{-- TAB 3: NOTIFIKASI --}}
    <div id="tab-notifikasi" class="tab-pane" style="display:none;">
        <div class="card" style="padding: 16px; border-radius: 16px; margin-bottom: 16px; background: var(--bg-card, #fff); border: 1px solid var(--border-color, #e2e8f0);">
            <div style="font-size: 14px; font-weight: 700; color: var(--text-primary, #0f172a); margin-bottom: 8px;">
                Pengaturan Push Notifikasi
            </div>
            <p style="font-size: 12px; color: var(--text-secondary, #64748b); margin-bottom: 14px;">
                Dapatkan notifikasi konfirmasi presensi masuk dan pengingat pulang langsung di perangkat ini.
            </p>
            <button type="button" id="btnEnablePush" onclick="requestPushNotification()" class="btn" style="width: 100%; padding: 12px; border-radius: 10px; background: rgba(11, 94, 215, 0.1); color: #0b5ed7; font-weight: 700; border: 1px solid rgba(11, 94, 215, 0.3); display: flex; align-items: center; justify-content: center; gap: 8px;">
                <ion-icon name="notifications-outline" style="font-size: 18px;"></ion-icon>
                <span id="pushBtnLabel">Aktifkan Notifikasi di Perangkat Ini</span>
            </button>
        </div>
    </div>

    {{-- LOGOUT BUTTON --}}
    <form action="{{ route('logout') }}" method="POST" style="margin-top: 20px; margin-bottom: 30px;">
        @csrf
        <button type="submit" style="width: 100%; padding: 12px; border-radius: 12px; background: rgba(220, 38, 38, 0.08); color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.2); font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer;">
            <ion-icon name="log-out-outline" style="font-size: 18px;"></ion-icon>
            Keluar dari Akun
        </button>
    </form>
</div>

<script>
    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.style.display = 'none';
            pane.classList.remove('active');
        });

        event.currentTarget.classList.add('active');
        const targetPane = document.getElementById('tab-' + tabName);
        if (targetPane) {
            targetPane.style.display = 'block';
            targetPane.classList.add('active');
        }
    }

    async function requestPushNotification() {
        if (!('Notification' in window) || !('serviceWorker' in navigator)) {
            alert('Browser ini tidak mendukung Push Notification.');
            return;
        }

        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            if (typeof initPushNotification === 'function') {
                initPushNotification();
            }
            document.getElementById('pushBtnLabel').textContent = 'Notifikasi Aktif';
            alert('Notifikasi berhasil diaktifkan!');
        } else {
            alert('Izin notifikasi ditolak.');
        }
    }
</script>
@endsection
