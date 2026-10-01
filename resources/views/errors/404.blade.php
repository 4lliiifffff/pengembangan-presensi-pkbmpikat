@extends('errors.layout')

@php
    $rawMsg = isset($exception) ? $exception->getMessage() : '';
    $indoDesc = 'Tautan yang Anda tuju mungkin salah ketik, sudah kedaluwarsa, atau telah dipindahkan ke rute lain oleh pengelola sistem.';
    if ($rawMsg) {
        if (preg_match('/The route\s+(.*?)\s+could not be found/i', $rawMsg, $m)) {
            $indoDesc = 'Rute halaman \'' . $m[1] . '\' tidak ditemukan atau belum terdaftar di sistem.';
        } elseif (preg_match('/not found/i', $rawMsg)) {
            $indoDesc = 'Halaman atau data yang Anda cari tidak dapat ditemukan.';
        }
    }
@endphp

@section('title', '404 - Halaman Tidak Ditemukan')
@section('toast_type', 'info')
@section('toast_title', 'Halaman Tidak Ditemukan (404)')
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

<div class="error-code-badge" style="background: rgba(11, 94, 215, 0.12); color: #0b5ed7; border: 1px solid rgba(11, 94, 215, 0.25);">
    <ion-icon name="compass-outline"></ion-icon> KODE 404 &bull; TIDAK DITEMUKAN
</div>

<div class="error-icon-wrapper" style="background: rgba(11, 94, 215, 0.1); color: #0b5ed7; border: 1px solid rgba(11, 94, 215, 0.2);">
    <ion-icon name="search-outline"></ion-icon>
</div>

<h1 class="error-title">Halaman Tidak Ditemukan</h1>
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
