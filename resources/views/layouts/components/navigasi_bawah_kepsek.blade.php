<nav class="bottomNavAdmin">
    {{-- 1. Presensi Pribadi --}}
    <a href="{{ route('kepsek.presensi') }}" class="navItem {{ request()->routeIs('kepsek.presensi') || request()->routeIs('kepsek.presensi.store') ? 'active' : '' }}" aria-label="Presensi">
        <div class="navIconWrap">
            <ion-icon name="{{ request()->routeIs('kepsek.presensi') || request()->routeIs('kepsek.presensi.store') ? 'camera' : 'camera-outline' }}"></ion-icon>
        </div>
        <span>Absen</span>
    </a>

    {{-- 2. Laporan KBM --}}
    <a href="{{ route('kepsek.laporan') }}" class="navItem {{ request()->routeIs('kepsek.laporan*') ? 'active' : '' }}" aria-label="Laporan Presensi KBM">
        <div class="navIconWrap">
            <ion-icon name="{{ request()->routeIs('kepsek.laporan*') ? 'bar-chart' : 'bar-chart-outline' }}"></ion-icon>
        </div>
        <span>Laporan</span>
    </a>

    {{-- 3. DASHBOARD (CENTER PROMINENT HERO ANCHOR) --}}
    <a href="{{ route('kepsek.dashboard') }}" class="navItemCenter {{ request()->routeIs('kepsek.dashboard') ? 'active' : '' }}" aria-label="Dashboard Kepala Sekolah">
        <div class="navCenterCircle">
            <ion-icon name="{{ request()->routeIs('kepsek.dashboard') ? 'grid' : 'grid-outline' }}"></ion-icon>
        </div>
        <span class="navCenterLabel">Dashboard</span>
    </a>

    {{-- 4. Modul Payroll & Honorarium --}}
    <a href="{{ route('kepsek.payroll.index') }}" class="navItem {{ request()->routeIs('kepsek.payroll*') ? 'active' : '' }}" aria-label="Payroll">
        <div class="navIconWrap">
            <ion-icon name="{{ request()->routeIs('kepsek.payroll*') ? 'wallet' : 'wallet-outline' }}"></ion-icon>
        </div>
        <span>Payroll</span>
    </a>

    {{-- 5. Menu Lainnya (Buka Bottom Drawer) --}}
    @php
        $isKepsekDrawerActive = request()->routeIs('kepsek.lupa-lapor*') ||
                                request()->routeIs('kepsek.pengajuan-izin*') ||
                                request()->routeIs('kepsek.presensi-tutor*');
    @endphp
    <button type="button" class="navItem {{ $isKepsekDrawerActive ? 'active' : '' }}" onclick="toggleNavDrawer('navDrawerKepsek')" aria-label="Menu Lainnya">
        <div class="navIconWrap">
            <ion-icon name="{{ $isKepsekDrawerActive ? 'apps' : 'apps-outline' }}"></ion-icon>
            @if($isKepsekDrawerActive)
                <span class="navDotBadge"></span>
            @endif
        </div>
        <span>Lainnya</span>
    </button>
</nav>

{{-- Drawer Sheet: Menu Tambahan Kepala Sekolah --}}
<div id="navDrawerKepsek" class="navDrawerBackdrop" onclick="if(event.target === this) toggleNavDrawer('navDrawerKepsek')">
    <div class="navDrawerSheet" onclick="event.stopPropagation()">
        <div class="navDrawerHandle"></div>
        
        <div class="navDrawerHeader">
            <div class="navDrawerTitleWrap">
                <div class="navDrawerSub">Menu Monitoring & Approval</div>
                <h3 class="navDrawerTitle">Fitur Kepala Sekolah</h3>
            </div>
            <button type="button" class="navDrawerCloseBtn" onclick="toggleNavDrawer('navDrawerKepsek')" aria-label="Tutup Menu">
                <ion-icon name="close"></ion-icon>
            </button>
        </div>

        <div class="navDrawerBody">
            <div class="navDrawerSection">
                <div class="navDrawerSectionTitle">
                    <ion-icon name="shield-checkmark-outline"></ion-icon>
                    <span>Monitoring & Persetujuan</span>
                </div>
                <div class="navDrawerGrid">
                    <a href="{{ route('kepsek.presensi-tutor') }}" class="navDrawerCard {{ request()->routeIs('kepsek.presensi-tutor*') ? 'active' : '' }}">
                        <div class="navCardIcon emerald">
                            <ion-icon name="newspaper-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Presensi Tutor</div>
                            <div class="navCardDesc">Monitoring Kehadiran</div>
                        </div>
                    </a>

                    <a href="{{ route('kepsek.pengajuan-izin') }}" class="navDrawerCard {{ request()->routeIs('kepsek.pengajuan-izin*') ? 'active' : '' }}">
                        <div class="navCardIcon rose">
                            <ion-icon name="medkit-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Persetujuan Izin</div>
                            <div class="navCardDesc">Verifikasi Cuti/Sakit</div>
                        </div>
                    </a>

                    <a href="{{ route('kepsek.lupa-lapor') }}" class="navDrawerCard {{ request()->routeIs('kepsek.lupa-lapor*') ? 'active' : '' }}">
                        <div class="navCardIcon amber">
                            <ion-icon name="document-text-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Lupa Lapor</div>
                            <div class="navCardDesc">Koreksi Absensi Tutor</div>
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
