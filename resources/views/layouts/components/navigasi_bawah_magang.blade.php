<nav class="bottomNavAdmin">
    {{-- 1. Absen Magang --}}
    <a href="{{ route('magang.presensi') }}" class="navItem {{ request()->routeIs('magang.presensi*') ? 'active' : '' }}" aria-label="Absen Magang">
        <div class="navIconWrap">
            <ion-icon name="{{ request()->routeIs('magang.presensi*') ? 'camera' : 'camera-outline' }}"></ion-icon>
        </div>
        <span>Absen</span>
    </a>

    {{-- 2. Riwayat Presensi --}}
    <a href="{{ route('magang.riwayat') }}" class="navItem {{ request()->routeIs('magang.riwayat*') ? 'active' : '' }}" aria-label="Riwayat Magang">
        <div class="navIconWrap">
            <ion-icon name="{{ request()->routeIs('magang.riwayat*') ? 'time' : 'time-outline' }}"></ion-icon>
        </div>
        <span>Riwayat</span>
    </a>

    {{-- 3. DASHBOARD (CENTER PROMINENT HERO ANCHOR) --}}
    <a href="{{ route('magang.dashboard') }}" class="navItemCenter {{ request()->routeIs('magang.dashboard') ? 'active' : '' }}" aria-label="Dashboard Magang">
        <div class="navCenterCircle">
            <ion-icon name="{{ request()->routeIs('magang.dashboard') ? 'grid' : 'grid-outline' }}"></ion-icon>
        </div>
        <span class="navCenterLabel">Dashboard</span>
    </a>

    {{-- 4. Profil Saya --}}
    <a href="{{ route('magang.profil') }}" class="navItem {{ request()->routeIs('magang.profil*') ? 'active' : '' }}" aria-label="Profil Magang">
        <div class="navIconWrap">
            <ion-icon name="{{ request()->routeIs('magang.profil*') ? 'person' : 'person-outline' }}"></ion-icon>
        </div>
        <span>Profil</span>
    </a>

    {{-- 5. Menu Lainnya (Buka Bottom Drawer) --}}
    @php
        $isMagangDrawerActive = request()->routeIs('magang.profil*') && request()->has('tab');
    @endphp
    <button type="button" class="navItem {{ $isMagangDrawerActive ? 'active' : '' }}" onclick="toggleNavDrawer('navDrawerMagang')" aria-label="Menu Lainnya">
        <div class="navIconWrap">
            <ion-icon name="{{ $isMagangDrawerActive ? 'apps' : 'apps-outline' }}"></ion-icon>
            @if($isMagangDrawerActive)
                <span class="navDotBadge"></span>
            @endif
        </div>
        <span>Lainnya</span>
    </button>
</nav>

{{-- Drawer Sheet: Menu Tambahan Magang --}}
<div id="navDrawerMagang" class="navDrawerBackdrop" onclick="if(event.target === this) toggleNavDrawer('navDrawerMagang')">
    <div class="navDrawerSheet" onclick="event.stopPropagation()">
        <div class="navDrawerHandle"></div>
        
        <div class="navDrawerHeader">
            <div class="navDrawerTitleWrap">
                <div class="navDrawerSub">Aktivitas & Akun</div>
                <h3 class="navDrawerTitle">Menu Mahasiswa Magang</h3>
            </div>
            <button type="button" class="navDrawerCloseBtn" onclick="toggleNavDrawer('navDrawerMagang')" aria-label="Tutup Menu">
                <ion-icon name="close"></ion-icon>
            </button>
        </div>

        <div class="navDrawerBody">
            <div class="navDrawerSection">
                <div class="navDrawerSectionTitle">
                    <ion-icon name="person-circle-outline"></ion-icon>
                    <span>Akun & Informasi PKL</span>
                </div>
                <div class="navDrawerGrid">
                    <a href="{{ route('magang.profil') }}" class="navDrawerCard {{ request()->routeIs('magang.profil*') ? 'active' : '' }}">
                        <div class="navCardIcon blue">
                            <ion-icon name="person-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Profil & Instansi</div>
                            <div class="navCardDesc">Data Diri & Asal Kampus</div>
                        </div>
                    </a>

                    <a href="{{ route('magang.riwayat') }}" class="navDrawerCard {{ request()->routeIs('magang.riwayat*') ? 'active' : '' }}">
                        <div class="navCardIcon emerald">
                            <ion-icon name="calendar-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Rekap Kehadiran</div>
                            <div class="navCardDesc">Logbook & Jam Kerja</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof window.toggleNavDrawer === 'undefined') {
        window.toggleNavDrawer = function(id) {
            const drawer = document.getElementById(id);
            if (!drawer) return;
            const isActive = drawer.classList.contains('active');
            if (isActive) {
                drawer.classList.remove('active');
                document.body.style.overflow = '';
            } else {
                drawer.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        };

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.navDrawerBackdrop.active').forEach(d => {
                    d.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }
        });
    }
</script>
