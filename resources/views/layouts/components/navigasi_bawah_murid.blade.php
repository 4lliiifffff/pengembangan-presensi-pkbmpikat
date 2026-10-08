<nav class="bottomNavAdmin">
    {{-- 1. Absen Murid --}}
    <a href="{{ route('murid.presensi') }}" class="navItem {{ request()->routeIs('murid.presensi*') ? 'active' : '' }}" aria-label="Absen Murid">
        <div class="navIconWrap">
            <ion-icon name="{{ request()->routeIs('murid.presensi*') ? 'camera' : 'camera-outline' }}"></ion-icon>
        </div>
        <span>Absen</span>
    </a>

    {{-- 2. Riwayat Presensi --}}
    <a href="{{ route('murid.riwayat') }}" class="navItem {{ request()->routeIs('murid.riwayat*') ? 'active' : '' }}" aria-label="Riwayat Murid">
        <div class="navIconWrap">
            <ion-icon name="{{ request()->routeIs('murid.riwayat*') ? 'time' : 'time-outline' }}"></ion-icon>
        </div>
        <span>Riwayat</span>
    </a>

    {{-- 3. DASHBOARD (CENTER PROMINENT HERO ANCHOR) --}}
    <a href="{{ route('murid.dashboard') }}" class="navItemCenter {{ request()->routeIs('murid.dashboard') ? 'active' : '' }}" aria-label="Dashboard Murid">
        <div class="navCenterCircle">
            <ion-icon name="{{ request()->routeIs('murid.dashboard') ? 'grid' : 'grid-outline' }}"></ion-icon>
        </div>
        <span class="navCenterLabel">Dashboard</span>
    </a>

    {{-- 4. Jadwal & Agenda Murid --}}
    <a href="{{ route('murid.jadwal') }}" class="navItem {{ request()->routeIs('murid.jadwal*') ? 'active' : '' }}" aria-label="Jadwal Murid">
        <div class="navIconWrap">
            <ion-icon name="{{ request()->routeIs('murid.jadwal*') ? 'calendar' : 'calendar-outline' }}"></ion-icon>
        </div>
        <span>Jadwal</span>
    </a>

    {{-- 5. Menu Lainnya (Buka Bottom Drawer) --}}
    @php
        $isMuridDrawerActive = request()->routeIs('murid.profil*');
    @endphp
    <button type="button" class="navItem {{ $isMuridDrawerActive ? 'active' : '' }}" onclick="toggleNavDrawer('navDrawerMurid')" aria-label="Menu Lainnya">
        <div class="navIconWrap">
            <ion-icon name="{{ $isMuridDrawerActive ? 'apps' : 'apps-outline' }}"></ion-icon>
            @if($isMuridDrawerActive)
                <span class="navDotBadge"></span>
            @endif
        </div>
        <span>Lainnya</span>
    </button>
</nav>

{{-- Drawer Sheet: Menu Tambahan Murid --}}
<div id="navDrawerMurid" class="navDrawerBackdrop" onclick="if(event.target === this) toggleNavDrawer('navDrawerMurid')">
    <div class="navDrawerSheet" onclick="event.stopPropagation()">
        <div class="navDrawerHandle"></div>
        
        <div class="navDrawerHeader">
            <div class="navDrawerTitleWrap">
                <div class="navDrawerSub">Aktivitas & Pembelajaran</div>
                <h3 class="navDrawerTitle">Menu Murid PKBM</h3>
            </div>
            <button type="button" class="navDrawerCloseBtn" onclick="toggleNavDrawer('navDrawerMurid')" aria-label="Tutup Menu">
                <ion-icon name="close"></ion-icon>
            </button>
        </div>

        <div class="navDrawerBody">
            <div class="navDrawerSection">
                <div class="navDrawerSectionTitle">
                    <ion-icon name="school-outline"></ion-icon>
                    <span>Informasi & Pembelajaran</span>
                </div>
                <div class="navDrawerGrid">
                    <a href="{{ route('murid.profil') }}" class="navDrawerCard {{ request()->routeIs('murid.profil*') ? 'active' : '' }}">
                        <div class="navCardIcon blue">
                            <ion-icon name="person-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Profil Murid</div>
                            <div class="navCardDesc">Biodata & Rombel Kelas</div>
                        </div>
                    </a>

                    <a href="{{ route('murid.jadwal') }}" class="navDrawerCard {{ request()->routeIs('murid.jadwal*') ? 'active' : '' }}">
                        <div class="navCardIcon indigo">
                            <ion-icon name="calendar-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Jadwal & Agenda</div>
                            <div class="navCardDesc">Sesi Belajar & Kegiatan</div>
                        </div>
                    </a>

                    <a href="{{ route('murid.riwayat') }}" class="navDrawerCard {{ request()->routeIs('murid.riwayat*') ? 'active' : '' }}">
                        <div class="navCardIcon emerald">
                            <ion-icon name="time-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Rekap Kehadiran</div>
                            <div class="navCardDesc">Riwayat Absensi Mandiri</div>
                        </div>
                    </a>

                    <a href="{{ route('murid.presensi') }}" class="navDrawerCard {{ request()->routeIs('murid.presensi*') ? 'active' : '' }}">
                        <div class="navCardIcon purple">
                            <ion-icon name="camera-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Presensi Mandiri</div>
                            <div class="navCardDesc">Check-In Selfie Masuk</div>
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
