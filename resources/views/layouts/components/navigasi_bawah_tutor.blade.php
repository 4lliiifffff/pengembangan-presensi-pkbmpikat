<div class="appBottomMenu">
    @php
        $leftHref = \Illuminate\Support\Facades\Route::has('tutor.riwayat')
            ? route('tutor.riwayat')
            : '#';
        $centerHref = \Illuminate\Support\Facades\Route::has('tutor.dashboard')
            ? route('tutor.dashboard')
            : '#';
        $rightHref = \Illuminate\Support\Facades\Route::has('tutor.jadwal')
            ? route('tutor.jadwal')
            : '#';
        $isCenter = request()->routeIs('tutor.dashboard');
        $isLeft = request()->routeIs('tutor.riwayat');
        $isRight = request()->routeIs('tutor.jadwal');
    @endphp

    <a href="{{ $leftHref }}" class="{{ $isLeft ? 'active' : '' }}" aria-label="Riwayat">
        <ion-icon name="{{ $isLeft ? 'time' : 'time-outline' }}">Riwayat</ion-icon>
    </a>

    <a href="{{ $centerHref }}" class="{{ $isCenter ? 'active' : '' }}" aria-label="Dashboard">
        <ion-icon name="{{ $isCenter ? 'grid' : 'grid-outline' }}">Dashboard</ion-icon>
    </a>

    <a href="{{ $rightHref }}" class="{{ $isRight ? 'active' : '' }}" aria-label="Jadwal">
        <ion-icon name="{{ $isRight ? 'calendar' : 'calendar-outline' }}">Agenda</ion-icon>
    </a>
</div>
