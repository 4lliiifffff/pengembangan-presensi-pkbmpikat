@extends('errors.layout')

@php
    $rawMsg = isset($exception) ? $exception->getMessage() : '';
    $indoDesc = 'Mohon maaf, akun Anda tidak memiliki izin atau peran yang sesuai untuk mengakses modul ini. Silakan kembali ke beranda utama Anda.';
    if ($rawMsg) {
        if (preg_match('/This action is unauthorized/i', $rawMsg) || preg_match('/Access denied/i', $rawMsg) || preg_match('/Forbidden/i', $rawMsg)) {
            $indoDesc = 'Anda tidak memiliki hak akses atau wewenang untuk membuka halaman ini.';
        } elseif (!preg_match('/[a-zA-Z]/', $rawMsg) || preg_match('/akses|izin|wewenang|peran/i', $rawMsg)) {
            $indoDesc = $rawMsg;
        }
    }
@endphp

@section('title', '403 - Akses Ditolak')
@section('toast_type', 'warning')
@section('toast_title', 'Akses Ditolak (403)')
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
            'murid', 'siswa' => route('murid.dashboard'),
            default => route('login'),
        };
    }
@endphp

<div class="error-code-badge" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.25);">
    <ion-icon name="shield-outline"></ion-icon> KODE 403 &bull; HAK AKSES DIBATASI
</div>

<div class="error-icon-wrapper" style="background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.2);">
    <ion-icon name="hand-left-outline"></ion-icon>
</div>

<h1 class="error-title">Hak Akses Dibatasi</h1>
<p class="error-description">
    {{ $indoDesc }}
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
