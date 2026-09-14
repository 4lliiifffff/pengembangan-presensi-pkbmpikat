<div class="topBar">
    <div class="topBarRow">
        <div class="profileGroup">
            <a href="{{ route('profil.index') }}" style="text-decoration: none;" aria-label="Buka Profil">
                @if(auth()->check() && auth()->user()->foto)
                    <img src="{{ auth()->user()->foto_url }}" class="avatar" alt="Avatar" style="object-fit:cover;" />
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
            <button class="iconBtn" type="button" aria-label="Tema" id="themeToggleBtn">
                <ion-icon name="moon-outline" id="themeToggleIcon"></ion-icon>
            </button>
        </div>
    </div>
</div>