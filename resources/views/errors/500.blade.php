@extends('errors.layout')

@section('title', '500 - Terjadi Kesalahan Server')
@section('toast_type', 'danger')
@section('toast_title', 'Kesalahan Server (500)')
@section('toast_message', $exception->getMessage() ?: 'Terjadi kendala teknis internal pada server aplikasi.')

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
    <ion-icon name="server-outline"></ion-icon> ERROR 500 &bull; SERVER ERROR
</div>

<div class="error-icon-wrapper" style="background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.2);">
    <ion-icon name="warning-outline"></ion-icon>
</div>

<h1 class="error-title">Terjadi Kendala Teknis</h1>
<p class="error-description">
    {{ $exception->getMessage() ?: 'Sistem mengalami gangguan saat memproses permintaan ini. Tim teknis PKBM Pikat sedang menindaklanjuti kendala tersebut. Silakan muat ulang atau coba kembali beberapa saat lagi.' }}
</p>

<div class="error-actions-grid">
    <button type="button" onclick="window.location.reload()" class="btn-error-primary">
        <ion-icon name="refresh-outline"></ion-icon> Muat Ulang Halaman
    </button>
    <a href="{{ $targetRoute }}" class="btn-error-secondary">
        <ion-icon name="home-outline"></ion-icon> Ke Dashboard
    </a>
</div>
@endsection
