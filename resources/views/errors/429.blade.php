@extends('errors.layout')

@section('title', '429 - Terlalu Banyak Permintaan')
@section('toast_type', 'warning')
@section('toast_title', 'Terlalu Banyak Permintaan (429)')
@section('toast_message', $exception->getMessage() ?: 'Sistem mendeteksi terlalu banyak percobaan dalam waktu singkat. Silakan tunggu beberapa saat.')

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
    <ion-icon name="speedometer-outline"></ion-icon> ERROR 429 &bull; RATE LIMITED
</div>

<div class="error-icon-wrapper" style="background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2);">
    <ion-icon name="hand-left-outline"></ion-icon>
</div>

<h1 class="error-title">Terlalu Banyak Permintaan</h1>
<p class="error-description">
    {{ $exception->getMessage() ?: 'Demi keamanan dan kestabilan server, sistem membatasi frekuensi aksi yang dilakukan secara berulang-ulang dalam waktu singkat. Harap tunggu beberapa saat sebelum mencoba kembali.' }}
</p>

<div class="error-actions-grid">
    <button type="button" onclick="window.location.reload()" class="btn-error-primary">
        <ion-icon name="refresh-outline"></ion-icon> Coba Lagi
    </button>
    <a href="{{ $targetRoute }}" class="btn-error-secondary">
        <ion-icon name="home-outline"></ion-icon> Ke Dashboard
    </a>
</div>
@endsection
