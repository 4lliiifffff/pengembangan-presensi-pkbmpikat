<nav class="bottomNavAdmin">
    <a href="{{ route('tutor.dashboard') }}" class="{{ request()->routeIs('tutor.dashboard') ? 'active' : '' }}"
       aria-label="Dashboard">
        <ion-icon name="{{ request()->routeIs('tutor.dashboard') ? 'grid' : 'grid-outline' }}"></ion-icon>
        <span >Dashboard</span>
    </a>

    <a href="{{ route('tutor.presensi') }}" class="{{ request()->routeIs('tutor.presensi*') ? 'active' : '' }}"
       aria-label="Absen">
        <ion-icon name="{{ request()->routeIs('tutor.presensi*') ? 'camera' : 'camera-outline' }}"></ion-icon>
        <span >Absen</span>
    </a>

    <a href="{{ route('tutor.riwayat') }}" class="{{ request()->routeIs('tutor.riwayat*') ? 'active' : '' }}"
       aria-label="Riwayat">
        <ion-icon name="{{ request()->routeIs('tutor.riwayat*') ? 'time' : 'time-outline' }}"></ion-icon>
        <span >Riwayat</span>
    </a>

    <a href="{{ route('tutor.pengajuan-izin') }}" class="{{ request()->routeIs('tutor.pengajuan-izin*') ? 'active' : '' }}"
       aria-label="Izin Tutor">
        <ion-icon name="{{ request()->routeIs('tutor.pengajuan-izin*') ? 'medkit' : 'medkit-outline' }}"></ion-icon>
        <span >Izin</span>
    </a>

    <a href="{{ route('tutor.jadwal') }}" class="{{ request()->routeIs('tutor.jadwal*') ? 'active' : '' }}"
       aria-label="Agenda">
        <ion-icon name="{{ request()->routeIs('tutor.jadwal*') ? 'calendar' : 'calendar-outline' }}"></ion-icon>
        <span >Agenda</span>
    </a>

    <a href="{{ route('tutor.payroll.index') }}" class="{{ request()->routeIs('tutor.payroll*') ? 'active' : '' }}"
       aria-label="Payroll">
        <ion-icon name="{{ request()->routeIs('tutor.payroll*') ? 'wallet' : 'wallet-outline' }}"></ion-icon>
        <span >Payroll</span>
    </a>
</nav>
