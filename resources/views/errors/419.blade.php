@extends('errors.layout')

@section('title', '419 - Sesi Halaman Kedaluwarsa')
@section('toast_type', 'warning')
@section('toast_title', 'Sesi Kedaluwarsa (419)')
@section('toast_message', $exception->getMessage() ?: 'Token keamanan form atau sesi peramban Anda telah kedaluwarsa. Silakan muat ulang halaman.')

@section('content')
@php
    $targetRoute = route('login');
    if (auth()->check()) {
        $userRole = auth()->user()->role;
        $targetRoute = match ($userRole) {
            'admin' => route('admin.dashboard'),
            'kepala_sekolah' => route('kepsek.dashboard'),
            'tutor' => route('tutor.dashboard'),
            'magang' => route('magang.dashboard'),
            'siswa' => route('siswa.dashboard'),
            default => route('login'),
        };
    }
@endphp

<div class="error-code-badge" style="background: rgba(245, 158, 11, 0.12); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.25);">
    <ion-icon name="time-outline"></ion-icon> KODE 419 &bull; SESI KEDALUWARSA
</div>

<div class="error-icon-wrapper" style="background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2);">
    <ion-icon name="hourglass-outline"></ion-icon>
</div>

<h1 class="error-title">Sesi Halaman Kedaluwarsa</h1>
<p class="error-description">
    {{ $exception->getMessage() ?: 'Halaman telah terbuka terlalu lama tanpa aktivitas sehingga token keamanan form kedaluwarsa. Silakan muat ulang halaman untuk melanjutkan kembali.' }}
</p>

<div class="error-actions-grid">
    <button type="button" onclick="window.location.reload()" class="btn-error-primary">
        <ion-icon name="refresh-outline"></ion-icon> Muat Ulang Halaman
    </button>
    <a href="{{ $targetRoute }}" class="btn-error-secondary">
        <ion-icon name="home-outline"></ion-icon> Ke Beranda Utama
    </a>
</div>
@endsection
