<nav class="bottomNavAdmin">
    <a href="{{ route('kepsek.dashboard') }}"
       class="{{ request()->routeIs('kepsek.dashboard') ? 'active' : '' }}"
       aria-label="Dashboard">
        <ion-icon name="{{ request()->routeIs('kepsek.dashboard') ? 'grid' : 'grid-outline' }}"></ion-icon>
        <span>Dashboard</span>
    </a>

    <a href="{{ route('kepsek.laporan') }}"
       class="{{ request()->routeIs('kepsek.laporan*') ? 'active' : '' }}"
       aria-label="Laporan">
        <ion-icon name="{{ request()->routeIs('kepsek.laporan*') ? 'bar-chart' : 'bar-chart-outline' }}"></ion-icon>
        <span>Laporan</span>
    </a>

    <a href="{{ route('kepsek.lupa-lapor') }}"
       class="{{ request()->routeIs('kepsek.lupa-lapor*') ? 'active' : '' }}"
       aria-label="Lupa Lapor">
        <ion-icon name="{{ request()->routeIs('kepsek.lupa-lapor*') ? 'document-text' : 'document-text-outline' }}"></ion-icon>
        <span>Lupa Presensi</span>
    </a>

    <a href="{{ route('kepsek.pengajuan-izin') }}"
       class="{{ request()->routeIs('kepsek.pengajuan-izin*') ? 'active' : '' }}"
       aria-label="Izin Tutor">
        <ion-icon name="{{ request()->routeIs('kepsek.pengajuan-izin*') ? 'medkit' : 'medkit-outline' }}"></ion-icon>
        <span>Izin Tutor</span>
    </a>

    <a href="{{ route('kepsek.presensi-tutor') }}"
       class="{{ request()->routeIs('kepsek.presensi-tutor*') ? 'active' : '' }}"
       aria-label="Presensi Tutor">
        <ion-icon name="{{ request()->routeIs('kepsek.presensi-tutor*') ? 'newspaper' : 'newspaper-outline' }}"></ion-icon>
        <span>Data</span>
    </a>

    <a href="{{ route('kepsek.presensi') }}"
       class="{{ request()->routeIs('kepsek.presensi') || request()->routeIs('kepsek.presensi.store') ? 'active' : '' }}"
       aria-label="Absen">
        <ion-icon name="{{ request()->routeIs('kepsek.presensi') || request()->routeIs('kepsek.presensi.store') ? 'camera' : 'camera-outline' }}"></ion-icon>
        <span>Absen</span>
    </a>
</nav>
