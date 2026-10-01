@extends('errors.layout')

@section('title', '403 - Akses Ditolak (Forbidden)')
@section('toast_type', 'warning')
@section('toast_title', 'Akses Ditolak (403)')
@section('toast_message', $exception->getMessage() ?: 'Anda tidak memiliki hak akses atau otoritas untuk membuka halaman ini.')

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

<div class="error-code-badge" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.25);">
    <ion-icon name="shield-outline"></ion-icon> ERROR 403 &bull; FORBIDDEN
</div>

<div class="error-icon-wrapper" style="background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.2);">
    <ion-icon name="hand-left-outline"></ion-icon>
</div>

<h1 class="error-title">Akses Ditolak / Dibatasi</h1>
<p class="error-description">
    {{ $exception->getMessage() ?: 'Mohon maaf, akun Anda tidak memiliki izin atau peran yang sesuai untuk mengakses modul/halaman ini. Silakan kembali ke dashboard utama Anda.' }}
</p>

<div class="error-actions-grid">
    <a href="{{ $targetRoute }}" class="btn-error-primary">
        <ion-icon name="home-outline"></ion-icon> Ke Dashboard
    </a>
    <button type="button" onclick="window.history.back()" class="btn-error-secondary">
        <ion-icon name="arrow-back-outline"></ion-icon> Kembali
    </button>
</div>
@endsection
