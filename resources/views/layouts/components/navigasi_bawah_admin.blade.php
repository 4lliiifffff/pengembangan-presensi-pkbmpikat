<nav class="bottomNavAdmin">
    {{-- 1. Presensi / Absen Cepat --}}
    <a href="{{ route('admin.presensi') }}" class="navItem {{ request()->routeIs('admin.presensi*') ? 'active' : '' }}" aria-label="Presensi">
        <div class="navIconWrap">
            <ion-icon name="{{ request()->routeIs('admin.presensi*') ? 'camera' : 'camera-outline' }}"></ion-icon>
        </div>
        <span>Absen</span>
    </a>

    {{-- 2. Laporan Presensi --}}
    <a href="{{ route('admin.laporan.index') }}" class="navItem {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}" aria-label="Laporan Presensi">
        <div class="navIconWrap">
            <ion-icon name="{{ request()->routeIs('admin.laporan.*') ? 'bar-chart' : 'bar-chart-outline' }}"></ion-icon>
        </div>
        <span>Laporan</span>
    </a>

    {{-- 3. DASHBOARD (CENTER PROMINENT HERO ANCHOR) --}}
    <a href="{{ route('admin.dashboard') }}" class="navItemCenter {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" aria-label="Dashboard Admin">
        <div class="navCenterCircle">
            <ion-icon name="{{ request()->routeIs('admin.dashboard') ? 'grid' : 'grid-outline' }}"></ion-icon>
        </div>
        <span class="navCenterLabel">Dashboard</span>
    </a>

    {{-- 4. Modul Payroll & Honorarium --}}
    <a href="{{ route('admin.payroll.index') }}" class="navItem {{ request()->routeIs('admin.payroll.*') ? 'active' : '' }}" aria-label="Payroll">
        <div class="navIconWrap">
            <ion-icon name="{{ request()->routeIs('admin.payroll.*') ? 'wallet' : 'wallet-outline' }}"></ion-icon>
        </div>
        <span>Payroll</span>
    </a>

    {{-- 5. Menu Lainnya (Buka Bottom Drawer) --}}
    @php
        $isAdminDrawerActive = request()->routeIs('admin.karyawan.*') ||
                               request()->routeIs('admin.siswa.*') ||
                               request()->routeIs('admin.jadwal.*') ||
                               request()->routeIs('admin.jadwal-rutin.*') ||
                               request()->routeIs('admin.jadwal-kerja.*') ||
                               request()->routeIs('admin.izin.*') ||
                               request()->routeIs('admin.lokasi-presensi.*') ||
                               request()->routeIs('admin.jenjang-paket.*') ||
                               request()->routeIs('admin.kategori-tutorial.*') ||
                               request()->routeIs('admin.magang.*');
    @endphp
    <button type="button" class="navItem {{ $isAdminDrawerActive ? 'active' : '' }}" onclick="toggleNavDrawer('navDrawerAdmin')" aria-label="Menu Lainnya">
        <div class="navIconWrap">
            <ion-icon name="{{ $isAdminDrawerActive ? 'apps' : 'apps-outline' }}"></ion-icon>
            @if($isAdminDrawerActive)
                <span class="navDotBadge"></span>
            @endif
        </div>
        <span>Lainnya</span>
    </button>
</nav>

{{-- Drawer Sheet: Pusat Menu Lengkap Admin --}}
<div id="navDrawerAdmin" class="navDrawerBackdrop" onclick="if(event.target === this) toggleNavDrawer('navDrawerAdmin')">
    <div class="navDrawerSheet" onclick="event.stopPropagation()">
        <div class="navDrawerHandle"></div>
        
        <div class="navDrawerHeader">
            <div class="navDrawerTitleWrap">
                <div class="navDrawerSub">Menu Kelola & Operasional</div>
                <h3 class="navDrawerTitle">Pusat Fitur Admin</h3>
            </div>
            <button type="button" class="navDrawerCloseBtn" onclick="toggleNavDrawer('navDrawerAdmin')" aria-label="Tutup Menu">
                <ion-icon name="close"></ion-icon>
            </button>
        </div>

        <div class="navDrawerBody">
            {{-- Kelompok 1: Data Master & Konfigurasi --}}
            <div class="navDrawerSection">
                <div class="navDrawerSectionTitle">
                    <ion-icon name="folder-open-outline"></ion-icon>
                    <span>Data Master & Konfigurasi</span>
                </div>
                <div class="navDrawerGrid">
                    <a href="{{ route('admin.karyawan.index') }}" class="navDrawerCard {{ request()->routeIs('admin.karyawan.*') ? 'active' : '' }}">
                        <div class="navCardIcon emerald">
                            <ion-icon name="people-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Kelola Akun</div>
                            <div class="navCardDesc">Semua Pengguna &amp; Akses</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.siswa.index') }}" class="navDrawerCard {{ request()->routeIs('admin.siswa.*') ? 'active' : '' }}">
                        <div class="navCardIcon blue">
                            <ion-icon name="school-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Data Siswa</div>
                            <div class="navCardDesc">Peserta Didik & ABK</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.jadwal-kerja.index') }}" class="navDrawerCard {{ request()->routeIs('admin.jadwal-kerja.*') ? 'active' : '' }}">
                        <div class="navCardIcon orange">
                            <ion-icon name="time-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Jadwal Shift</div>
                            <div class="navCardDesc">Jam Masuk, Pulang & SK</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.lokasi-presensi.index') }}" class="navDrawerCard {{ request()->routeIs('admin.lokasi-presensi.*') ? 'active' : '' }}">
                        <div class="navCardIcon cyan">
                            <ion-icon name="location-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Titik Lokasi</div>
                            <div class="navCardDesc">Multi-Geofence Radius</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.jenjang-paket.index') }}" class="navDrawerCard {{ request()->routeIs('admin.jenjang-paket.*') ? 'active' : '' }}">
                        <div class="navCardIcon indigo">
                            <ion-icon name="file-tray-stacked-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Jenjang Paket</div>
                            <div class="navCardDesc">Paket A, B, C, Vokasi</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.kategori-tutorial.index') }}" class="navDrawerCard {{ request()->routeIs('admin.kategori-tutorial.*') ? 'active' : '' }}">
                        <div class="navCardIcon purple">
                            <ion-icon name="ribbon-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Kategori SK</div>
                            <div class="navCardDesc">Tarif & Honor Tutorial</div>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Kelompok 2: Operasional & KBM --}}
            <div class="navDrawerSection">
                <div class="navDrawerSectionTitle">
                    <ion-icon name="calendar-clear-outline"></ion-icon>
                    <span>Operasional & Pembelajaran</span>
                </div>
                <div class="navDrawerGrid">
                    <a href="{{ route('admin.jadwal.index') }}" class="navDrawerCard {{ request()->routeIs('admin.jadwal.*') ? 'active' : '' }}">
                        <div class="navCardIcon amber">
                            <ion-icon name="calendar-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Agenda KBM</div>
                            <div class="navCardDesc">Jadwal Tutorial Siswa</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.jadwal-rutin.index') }}" class="navDrawerCard {{ request()->routeIs('admin.jadwal-rutin.*') ? 'active' : '' }}">
                        <div class="navCardIcon blue">
                            <ion-icon name="repeat-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Jadwal Rutin Siswa</div>
                            <div class="navCardDesc">Pola Mingguan & Time-Gating</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.izin.index') }}" class="navDrawerCard {{ request()->routeIs('admin.izin.*') ? 'active' : '' }}">
                        <div class="navCardIcon rose">
                            <ion-icon name="shield-checkmark-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Izin Tutor</div>
                            <div class="navCardDesc">Persetujuan Ketidakhadiran</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.magang.index') }}" class="navDrawerCard {{ request()->routeIs('admin.magang.*') ? 'active' : '' }}">
                        <div class="navCardIcon teal">
                            <ion-icon name="briefcase-outline"></ion-icon>
                        </div>
                        <div class="navCardMeta">
                            <div class="navCardTitle">Mahasiswa Magang</div>
                            <div class="navCardDesc">Data PKL & Rekap Presensi</div>
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
