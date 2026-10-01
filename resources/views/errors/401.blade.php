@extends('errors.layout')

@php
    $rawMsg = isset($exception) ? $exception->getMessage() : '';
    $indoDesc = 'Anda belum masuk ke akun atau sesi kredensial telah berakhir. Silakan masuk terlebih dahulu untuk melanjutkan akses ke halaman ini.';
    if ($rawMsg) {
        if (preg_match('/Unauthenticated/i', $rawMsg)) {
            $indoDesc = 'Sesi masuk Anda belum terautentikasi. Silakan masuk terlebih dahulu.';
        } elseif (preg_match('/masuk|login|sesi|kredensial/i', $rawMsg)) {
            $indoDesc = $rawMsg;
        }
    }
@endphp

@section('title', '401 - Akses Belum Terautentikasi')
@section('toast_type', 'warning')
@section('toast_title', 'Akses Belum Terautentikasi (401)')
@section('toast_message', $indoDesc)

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
    <ion-icon name="key-outline"></ion-icon> KODE 401 &bull; BELUM TERAUTENTIKASI
</div>

<div class="error-icon-wrapper" style="background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2);">
    <ion-icon name="lock-closed-outline"></ion-icon>
</div>

<h1 class="error-title">Akses Belum Terautentikasi</h1>
<p class="error-description">
    {{ $indoDesc }}
</p>

<div class="error-actions-grid">
    <a href="{{ route('login') }}" class="btn-error-primary">
        <ion-icon name="log-in-outline"></ion-icon> Masuk Sekarang
    </a>
    <button type="button" onclick="window.history.back()" class="btn-error-secondary">
        <ion-icon name="arrow-back-outline"></ion-icon> Kembali
    </button>
</div>
@endsection
