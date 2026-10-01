@extends('errors.layout')

@php
    $rawMsg = isset($exception) ? $exception->getMessage() : '';
    $indoDesc = 'Demi keamanan dan kestabilan server, sistem membatasi frekuensi percobaan yang dilakukan secara berulang dalam tempo singkat. Harap tunggu beberapa saat sebelum mencoba kembali.';
    if ($rawMsg) {
        if (preg_match('/Too Many Attempts|Too Many Requests/i', $rawMsg)) {
            $indoDesc = 'Terlalu banyak permintaan dalam waktu singkat. Silakan tunggu beberapa saat.';
        } elseif (preg_match('/permintaan|batas|tunggu/i', $rawMsg)) {
            $indoDesc = $rawMsg;
        }
    }
@endphp

@section('title', '429 - Terlalu Banyak Permintaan')
@section('toast_type', 'warning')
@section('toast_title', 'Batas Permintaan Terlampaui (429)')
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
    <ion-icon name="speedometer-outline"></ion-icon> KODE 429 &bull; BATAS PERMINTAAN TERLAMPAUI
</div>

<div class="error-icon-wrapper" style="background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2);">
    <ion-icon name="hand-left-outline"></ion-icon>
</div>

<h1 class="error-title">Terlalu Banyak Permintaan</h1>
<p class="error-description">
    {{ $indoDesc }}
</p>

<div class="error-actions-grid">
    <button type="button" onclick="window.location.reload()" class="btn-error-primary">
        <ion-icon name="refresh-outline"></ion-icon> Coba Lagi
    </button>
    <a href="{{ $targetRoute }}" class="btn-error-secondary">
        <ion-icon name="home-outline"></ion-icon> Ke Beranda Utama
    </a>
</div>
@endsection
