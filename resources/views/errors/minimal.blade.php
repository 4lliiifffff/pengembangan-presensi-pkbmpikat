@extends('errors.layout')

@php
    $rawMsg = isset($exception) ? $exception->getMessage() : ($message ?? '');
    $indoDesc = 'Permintaan tidak dapat diselesaikan saat ini. Silakan kembali ke beranda atau muat ulang halaman.';
    if ($rawMsg) {
        if (preg_match('/The route\s+(.*?)\s+could not be found/i', $rawMsg, $m)) {
            $indoDesc = 'Rute halaman \'' . $m[1] . '\' tidak ditemukan atau belum terdaftar di sistem.';
        } elseif (preg_match('/Unauthenticated/i', $rawMsg)) {
            $indoDesc = 'Sesi akun Anda belum terautentikasi. Silakan masuk terlebih dahulu.';
        } elseif (preg_match('/This action is unauthorized/i', $rawMsg) || preg_match('/Access denied/i', $rawMsg) || preg_match('/Forbidden/i', $rawMsg)) {
            $indoDesc = 'Anda tidak memiliki hak akses atau wewenang untuk membuka halaman ini.';
        } elseif (preg_match('/CSRF token mismatch/i', $rawMsg) || preg_match('/Page expired/i', $rawMsg)) {
            $indoDesc = 'Sesi keamanan form telah kedaluwarsa. Silakan muat ulang halaman.';
        } elseif (preg_match('/Too Many Attempts/i', $rawMsg) || preg_match('/Too Many Requests/i', $rawMsg)) {
            $indoDesc = 'Terlalu banyak permintaan dalam waktu singkat. Silakan tunggu beberapa saat.';
        } elseif (preg_match('/Server Error/i', $rawMsg)) {
            $indoDesc = 'Terjadi kendala teknis internal pada server aplikasi.';
        } else {
            $indoDesc = $rawMsg;
        }
    }
@endphp

@section('title', ($code ?? 'Kendala') . ' - Terjadi Kesalahan')
@section('toast_type', 'warning')
@section('toast_title', 'Status ' . ($code ?? 'Kendala'))
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

<div class="error-code-badge" style="background: rgba(100, 116, 139, 0.12); color: var(--muted); border: 1px solid rgba(100, 116, 139, 0.25);">
    <ion-icon name="alert-circle-outline"></ion-icon> KODE KESALAHAN {{ $code ?? 'HTTP' }}
</div>

<div class="error-icon-wrapper" style="background: rgba(100, 116, 139, 0.1); color: var(--muted); border: 1px solid rgba(100, 116, 139, 0.2);">
    <ion-icon name="information-circle-outline"></ion-icon>
</div>

<h1 class="error-title">{{ $title ?? 'Terjadi Kesalahan' }}</h1>
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
