<nav class="bottomNavAdmin">
    {{-- 1. Absen Siswa --}}
    <a href="{{ route('siswa.presensi') }}" class="navItem {{ request()->routeIs('siswa.presensi*') ? 'active' : '' }}" aria-label="Absen Siswa">
        <div class="navIconWrap">
            <ion-icon name="{{ request()->routeIs('siswa.presensi*') ? 'camera' : 'camera-outline' }}"></ion-icon>
        </div>
        <span>Absen</span>
    </a>

    {{-- 2. Riwayat Presensi --}}
    <a href="{{ route('siswa.riwayat') }}" class="navItem {{ request()->routeIs('siswa.riwayat*') ? 'active' : '' }}" aria-label="Riwayat Siswa">
        <div class="navIconWrap">
            <ion-icon name="{{ request()->routeIs('siswa.riwayat*') ? 'time' : 'time-outline' }}"></ion-icon>
        </div>
        <span>Riwayat</span>
    </a>

    {{-- 3. DASHBOARD (CENTER PROMINENT HERO ANCHOR) --}}
    <a href="{{ route('siswa.dashboard') }}" class="navItemCenter {{ request()->routeIs('siswa.dashboard') ? 'active' : '' }}" aria-label="Dashboard Siswa">
        <div class="navCenterCircle">
            <ion-icon name="{{ request()->routeIs('siswa.dashboard') ? 'grid' : 'grid-outline' }}"></ion-icon>
        </div>
        <span class="navCenterLabel">Dashboard</span>
    </a>

    {{-- 4. Profil Siswa --}}
    <a href="{{ route('siswa.profil') }}" class="navItem {{ request()->routeIs('siswa.profil*') ? 'active' : '' }}" aria-label="Profil Siswa">
        <div class="navIconWrap">
            <ion-icon name="{{ request()->routeIs('siswa.profil*') ? 'person' : 'person-outline' }}"></ion-icon>
        </div>
        <span>Profil</span>
    </a>

    {{-- 5. Menu Lainnya (Buka Bottom Drawer) --}}
    @php
        $isSiswaDrawerActive = request()->routeIs('siswa.profil*') && request()->has('tab');
    @endphp
    <button type="button" class="navItem {{ $isSiswaDrawerActive ? 'active' : '' }}" onclick="toggleNavDrawer('navDrawerSiswa')" aria-label="Menu Lainnya">
        <div class="navIconWrap">
            <ion-icon name="{{ $isSiswaDrawerActive ? 'apps' : 'apps-outline' }}"></ion-icon>
            @if($isSiswaDrawerActive)
                <span class="navDotBadge"></span>
            @endif
        </div>
        <span>Lainnya</span>
    </button>
</nav>

{{-- Drawer Sheet: Menu Tambahan Siswa --}}
<div id="navDrawerSiswa" class="navDrawerBackdrop" onclick="if(event.target === this) toggleNavDrawer('navDrawerSiswa')">
    <div class="navDrawerSheet" onclick="event.stopPropagation()">
        <div class="navDrawerHandle"></div>
        
        <div class="navDrawerHeader">
            <div class="navDrawerTitleWrap">
                <div class="navDrawerSub">Aktivitas & Pembelajaran</div>
                <h3 class="navDrawerTitle">Menu Siswa PKBM</h3>
            </div>
            <button type="button" class="navDrawerCloseBtn" onclick="toggleNavDrawer('navDrawerSiswa')" aria-label="Tutup Menu">
                <ion-icon name="close"></ion-icon>
            </button>
        </div>

        <div class="navDrawerBody">
            <div class="navDrawerSection">
                <div class="navDrawerSectionTitle">
                    <ion-icon name="school-outline"></ion-icon>
                    <span>Informasi & Kehadiran</span>
                </div>
                <div class="navDrawerGrid">
                    <a href="{{ route('siswa.profil') }}" class="navDrawerCard {{ request()->routeIs('siswa.profil*') ? 'active' : '' }}">
                        <div class="navCardIcon blue">
                            <ion-icon name="person-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Profil Siswa</div>
                            <div class="navCardDesc">Biodata & Rombel Kelas</div>
                        </div>
                    </a>

                    <a href="{{ route('siswa.riwayat') }}" class="navDrawerCard {{ request()->routeIs('siswa.riwayat*') ? 'active' : '' }}">
                        <div class="navCardIcon emerald">
                            <ion-icon name="calendar-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Rekap Kehadiran</div>
                            <div class="navCardDesc">Riwayat Absensi Mandiri</div>
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
