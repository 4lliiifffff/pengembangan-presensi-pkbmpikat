<div class="topBar">
    <div class="topBarRow">
        <div class="profileGroup">
            @if(isset($backRoute))
                <a href="{{ $backRoute }}" aria-label="Kembali" title="Kembali" class="iconBtn flex-shrink-0">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                </a>
            @endif
            <a href="{{ route('profil.index') }}" aria-label="Buka Profil" class="text-no-decor flex-shrink-0">
                @if(auth()->check() && auth()->user()->foto)
                    <img src="{{ auth()->user()->foto_url }}" class="avatar" alt="Avatar" class="object-cover" />
                @else
                    <div class="avatar" title="{{ $roleTitle ?? 'Profil' }}">
                        {{ strtoupper(substr(auth()->user()->nama_lengkap ?? auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                @endif
            </a>
            <div class="profileMeta">
                <div class="name">
                    {{ $titleName ?? (auth()->check() ? (auth()->user()->nama_lengkap ?? (auth()->user()->name ?? 'Pengguna')) : 'Pengguna') }}
                </div>
                <div class="sub">
                    {{ $subTitle ?? (auth()->check() ? (auth()->user()->role === 'admin' ? 'Admin PKBM' : (auth()->user()->role === 'kepala_sekolah' ? 'Kepala Sekolah' : 'Tutor PKBM')) : 'Sistem Presensi PKBM Pikat') }}
                </div>
            </div>
        </div>
        <div class="topIcons">
            @if(isset($dashRoute))
                <a href="{{ $dashRoute }}" class="iconBtn" aria-label="Dashboard" title="Kembali ke Dashboard">
                    <ion-icon name="grid-outline"></ion-icon>
                </a>
            @endif
            <button class="iconBtn" type="button" aria-label="Tema" id="themeToggleBtn" title="Ganti Tema">
                <ion-icon name="moon-outline" id="themeToggleIcon"></ion-icon>
            </button>
            <form action="{{ route('logout') }}" method="POST" class="d-none navLogoutForm">
                @csrf
            </form>
            <a href="{{ route('logout') }}" class="iconBtn" aria-label="Keluar" title="Keluar dari Akun" onclick="event.preventDefault(); (window.AppNotification ? window.AppNotification.confirm({ title: 'Konfirmasi Keluar', message: 'Apakah Anda yakin ingin keluar dari akun ini?', confirmText: 'Keluar Akun', cancelText: 'Batal', isDanger: true }) : Promise.resolve(confirm('Apakah Anda yakin ingin keluar dari akun?'))).then(ok => { if(ok) this.previousElementSibling.submit(); });">
                <ion-icon name="log-out-outline"></ion-icon>
            </a>
        </div>
    </div>
</div>