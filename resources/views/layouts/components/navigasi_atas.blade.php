<div class="topBar">
    <div class="topBarRow">
        <div class="profileGroup">
            <a href="{{ route('profil.index') }}" style="text-decoration: none;">
                @if(auth()->check() && auth()->user()->foto)
                    <img src="{{ auth()->user()->foto_url }}" class="avatar" alt="Avatar" style="object-fit:cover;" />
                @else
                    <div class="avatar" title="{{ $roleTitle ?? 'Profil' }}">
                        {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->nama_lengkap ?? 'U', 0, 1)) }}</div>
                @endif
            </a>
            <div class="profileMeta">
                <div class="name">
                    {{ $titleName ?? (auth()->user()->role === 'admin' ? 'Admin PKBM' : (auth()->user()->role === 'kepala_sekolah' ? 'Kepala Sekolah' : 'Tutor PKBM')) }}
                </div>
                <div class="sub">Sistem Presensi PKBM Pikat</div>
            </div>
        </div>
        <div class="topIcons">
            <button class="iconBtn" type="button" aria-label="Tema" id="themeToggleBtn">
                <ion-icon name="moon-outline" id="themeToggleIcon"></ion-icon>
            </button>
        </div>
    </div>
</div>