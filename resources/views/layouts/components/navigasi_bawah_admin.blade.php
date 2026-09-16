<nav class="bottomNavAdmin">
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
       aria-label="Dashboard">
        <ion-icon name="{{ request()->routeIs('admin.dashboard') ? 'grid' : 'grid-outline' }}"></ion-icon>
        <span >Dashboard</span>
    </a>

    <a href="{{ route('admin.karyawan.index') }}" class="{{ request()->routeIs('admin.karyawan.*') ? 'active' : '' }}"
       aria-label="Data Karyawan">
        <ion-icon name="{{ request()->routeIs('admin.karyawan.*') ? 'id-card' : 'id-card-outline' }}"></ion-icon>
        <span >Karyawan</span>
    </a>

    <a href="{{ route('admin.siswa.index') }}" class="{{ request()->routeIs('admin.siswa.*') ? 'active' : '' }}"
       aria-label="Data Siswa">
        <ion-icon name="{{ request()->routeIs('admin.siswa.*') ? 'school' : 'school-outline' }}"></ion-icon>
        <span >Siswa</span>
    </a>

    <a href="{{ route('admin.jadwal.index') }}" class="{{ request()->routeIs('admin.jadwal.*') ? 'active' : '' }}"
       aria-label="Jadwal">
        <ion-icon name="{{ request()->routeIs('admin.jadwal.*') ? 'calendar' : 'calendar-outline' }}"></ion-icon>
        <span >Agenda</span>
    </a>

    <a href="{{ route('admin.presensi') }}" class="{{ request()->routeIs('admin.presensi*') ? 'active' : '' }}"
       aria-label="Presensi">
        <ion-icon name="{{ request()->routeIs('admin.presensi*') ? 'camera' : 'camera-outline' }}"></ion-icon>
        <span >Absen</span>
    </a>

    <a href="{{ route('admin.izin.index') }}" class="{{ request()->routeIs('admin.izin.*') ? 'active' : '' }}"
       aria-label="Izin Tutor">
        <ion-icon name="{{ request()->routeIs('admin.izin.*') ? 'shield-checkmark' : 'shield-checkmark-outline' }}"></ion-icon>
        <span >Izin</span>
    </a>

    <a href="{{ route('admin.laporan.index') }}" class="{{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}"
       aria-label="Laporan Presensi KBM"
       title="Laporan Presensi KBM">
        <ion-icon name="{{ request()->routeIs('admin.laporan.*') ? 'bar-chart' : 'bar-chart-outline' }}"></ion-icon>
        <span >Laporan</span>
    </a>

    <a href="{{ route('admin.payroll.index') }}" class="{{ request()->routeIs('admin.payroll.*') ? 'active' : '' }}"
       aria-label="Payroll & Honorarium"
       title="Payroll & Honorarium Tutor">
        <ion-icon name="{{ request()->routeIs('admin.payroll.*') ? 'wallet' : 'wallet-outline' }}"></ion-icon>
        <span >Payroll</span>
    </a>
</nav>
