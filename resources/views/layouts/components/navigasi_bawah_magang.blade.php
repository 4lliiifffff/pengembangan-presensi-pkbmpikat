<nav class="bottomNavAdmin">
    <a href="{{ route('magang.dashboard') }}"
       class="{{ request()->routeIs('magang.dashboard') ? 'active' : '' }}"
       aria-label="Dashboard">
        <ion-icon name="{{ request()->routeIs('magang.dashboard') ? 'grid' : 'grid-outline' }}"></ion-icon>
        <span>Dashboard</span>
    </a>

    <a href="{{ route('magang.presensi') }}"
       class="{{ request()->routeIs('magang.presensi*') ? 'active' : '' }}"
       aria-label="Absen">
        <ion-icon name="{{ request()->routeIs('magang.presensi*') ? 'camera' : 'camera-outline' }}"></ion-icon>
        <span>Absen</span>
    </a>

    <a href="{{ route('magang.riwayat') }}"
       class="{{ request()->routeIs('magang.riwayat*') ? 'active' : '' }}"
       aria-label="Riwayat">
        <ion-icon name="{{ request()->routeIs('magang.riwayat*') ? 'time' : 'time-outline' }}"></ion-icon>
        <span>Riwayat</span>
    </a>

    <a href="{{ route('magang.profil') }}"
       class="{{ request()->routeIs('magang.profil*') ? 'active' : '' }}"
       aria-label="Profil">
        <ion-icon name="{{ request()->routeIs('magang.profil*') ? 'person' : 'person-outline' }}"></ion-icon>
        <span>Profil</span>
    </a>
</nav>
