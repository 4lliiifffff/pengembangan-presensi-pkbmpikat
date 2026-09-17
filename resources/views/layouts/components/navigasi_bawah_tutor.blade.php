<nav class="bottomNavAdmin">
    {{-- 1. Absen Presensi --}}
    <a href="{{ route('tutor.presensi') }}" class="navItem {{ request()->routeIs('tutor.presensi*') ? 'active' : '' }}" aria-label="Absen Presensi">
        <div class="navIconWrap">
            <ion-icon name="{{ request()->routeIs('tutor.presensi*') ? 'camera' : 'camera-outline' }}"></ion-icon>
        </div>
        <span>Absen</span>
    </a>

    {{-- 2. Riwayat Absensi --}}
    <a href="{{ route('tutor.riwayat') }}" class="navItem {{ request()->routeIs('tutor.riwayat*') ? 'active' : '' }}" aria-label="Riwayat Presensi">
        <div class="navIconWrap">
            <ion-icon name="{{ request()->routeIs('tutor.riwayat*') ? 'time' : 'time-outline' }}"></ion-icon>
        </div>
        <span>Riwayat</span>
    </a>

    {{-- 3. DASHBOARD (CENTER PROMINENT HERO ANCHOR) --}}
    <a href="{{ route('tutor.dashboard') }}" class="navItemCenter {{ request()->routeIs('tutor.dashboard') ? 'active' : '' }}" aria-label="Dashboard Tutor">
        <div class="navCenterCircle">
            <ion-icon name="{{ request()->routeIs('tutor.dashboard') ? 'grid' : 'grid-outline' }}"></ion-icon>
        </div>
        <span class="navCenterLabel">Dashboard</span>
    </a>

    {{-- 4. Agenda KBM --}}
    <a href="{{ route('tutor.jadwal') }}" class="navItem {{ request()->routeIs('tutor.jadwal*') ? 'active' : '' }}" aria-label="Agenda Jadwal">
        <div class="navIconWrap">
            <ion-icon name="{{ request()->routeIs('tutor.jadwal*') ? 'calendar' : 'calendar-outline' }}"></ion-icon>
        </div>
        <span>Agenda</span>
    </a>

    {{-- 5. Menu Lainnya (Buka Bottom Drawer) --}}
    @php
        $isTutorDrawerActive = request()->routeIs('tutor.payroll*') ||
                               request()->routeIs('tutor.pengajuan-izin*') ||
                               request()->routeIs('tutor.lupa-lapor*') ||
                               request()->routeIs('profil.*');
    @endphp
    <button type="button" class="navItem {{ $isTutorDrawerActive ? 'active' : '' }}" onclick="toggleNavDrawer('navDrawerTutor')" aria-label="Menu Lainnya">
        <div class="navIconWrap">
            <ion-icon name="{{ $isTutorDrawerActive ? 'apps' : 'apps-outline' }}"></ion-icon>
            @if($isTutorDrawerActive)
                <span class="navDotBadge"></span>
            @endif
        </div>
        <span>Lainnya</span>
    </button>
</nav>

{{-- Drawer Sheet: Menu Tambahan Tutor --}}
<div id="navDrawerTutor" class="navDrawerBackdrop" onclick="if(event.target === this) toggleNavDrawer('navDrawerTutor')">
    <div class="navDrawerSheet" onclick="event.stopPropagation()">
        <div class="navDrawerHandle"></div>
        
        <div class="navDrawerHeader">
            <div class="navDrawerTitleWrap">
                <div class="navDrawerSub">Fitur & Administrasi</div>
                <h3 class="navDrawerTitle">Menu Tambahan Tutor</h3>
            </div>
            <button type="button" class="navDrawerCloseBtn" onclick="toggleNavDrawer('navDrawerTutor')" aria-label="Tutup Menu">
                <ion-icon name="close"></ion-icon>
            </button>
        </div>

        <div class="navDrawerBody">
            <div class="navDrawerSection">
                <div class="navDrawerSectionTitle">
                    <ion-icon name="wallet-outline"></ion-icon>
                    <span>Keuangan & Administrasi</span>
                </div>
                <div class="navDrawerGrid">
                    <a href="{{ route('tutor.payroll.index') }}" class="navDrawerCard {{ request()->routeIs('tutor.payroll*') ? 'active' : '' }}">
                        <div class="navCardIcon blue">
                            <ion-icon name="wallet-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Payroll & Slip</div>
                            <div class="navCardDesc">Honorarium Tutorial</div>
                        </div>
                    </a>

                    <a href="{{ route('tutor.pengajuan-izin') }}" class="navDrawerCard {{ request()->routeIs('tutor.pengajuan-izin*') ? 'active' : '' }}">
                        <div class="navCardIcon rose">
                            <ion-icon name="medkit-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Pengajuan Izin</div>
                            <div class="navCardDesc">Sakit / Keperluan</div>
                        </div>
                    </a>

                    <a href="{{ route('tutor.lupa-lapor') }}" class="navDrawerCard {{ request()->routeIs('tutor.lupa-lapor*') ? 'active' : '' }}">
                        <div class="navCardIcon amber">
                            <ion-icon name="document-text-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Lupa Lapor</div>
                            <div class="navCardDesc">Koreksi Absensi</div>
                        </div>
                    </a>

                    <a href="{{ route('profil.index') }}" class="navDrawerCard {{ request()->routeIs('profil.*') ? 'active' : '' }}">
                        <div class="navCardIcon purple">
                            <ion-icon name="person-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Profil Saya</div>
                            <div class="navCardDesc">Informasi & Password</div>
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
