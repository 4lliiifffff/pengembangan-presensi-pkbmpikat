@extends('errors.layout')

@section('title', ($code ?? 'Kendala') . ' - Terjadi Kesalahan')
@section('toast_type', 'warning')
@section('toast_title', 'Status ' . ($code ?? 'Kendala'))
@section('toast_message', $exception->getMessage() ?: ($message ?? 'Terjadi kendala saat memproses permintaan Anda.'))

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

<div class="error-code-badge" style="background: rgba(100, 116, 139, 0.12); color: var(--muted); border: 1px solid rgba(100, 116, 139, 0.25);">
    <ion-icon name="alert-circle-outline"></ion-icon> KODE KESALAHAN {{ $code ?? 'HTTP' }}
</div>

<div class="error-icon-wrapper" style="background: rgba(100, 116, 139, 0.1); color: var(--muted); border: 1px solid rgba(100, 116, 139, 0.2);">
    <ion-icon name="information-circle-outline"></ion-icon>
</div>

<h1 class="error-title">{{ $title ?? 'Terjadi Kesalahan' }}</h1>
<p class="error-description">
    {{ $exception->getMessage() ?: ($message ?? 'Permintaan tidak dapat diselesaikan saat ini. Silakan kembali ke beranda atau muat ulang halaman.') }}
</p>

<div class="error-actions-grid">
    <a href="{{ $targetRoute }}" class="btn-error-primary">
        <ion-icon name="home-outline"></ion-icon> Ke Beranda Utama
    </a>
    <button type="button" onclick="window.history.back()" class="btn-error-secondary">
        <ion-icon name="arrow-back-outline"></ion-icon> Kembali
    </button>
</div>
@endsection
