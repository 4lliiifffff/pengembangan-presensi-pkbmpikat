@extends('errors.layout')

@php
    $rawMsg = isset($exception) ? $exception->getMessage() : '';
    $indoDesc = 'Aplikasi presensi PKBM Pikat sedang dalam proses pemeliharaan untuk meningkatkan performa layanan. Aplikasi akan segera kembali aktif dalam beberapa saat.';
    if ($rawMsg && preg_match('/pemeliharaan|perawatan|peningkatan/i', $rawMsg)) {
        $indoDesc = $rawMsg;
    }
@endphp

@section('title', '503 - Layanan Sedang Pemeliharaan')
@section('toast_type', 'info')
@section('toast_title', 'Pemeliharaan Sistem (503)')
@section('toast_message', $indoDesc)

@section('content')
<div class="error-code-badge" style="background: rgba(11, 94, 215, 0.12); color: #0b5ed7; border: 1px solid rgba(11, 94, 215, 0.25);">
    <ion-icon name="construct-outline"></ion-icon> KODE 503 &bull; PEMELIHARAAN SISTEM
</div>

<div class="error-icon-wrapper" style="background: rgba(11, 94, 215, 0.1); color: #0b5ed7; border: 1px solid rgba(11, 94, 215, 0.2);">
    <ion-icon name="hammer-outline"></ion-icon>
</div>

<h1 class="error-title">Sedang Pemeliharaan Sistem</h1>
<p class="error-description">
    {{ $indoDesc }}
</p>

<div class="error-actions-grid">
    <button type="button" onclick="window.location.reload()" class="btn-error-primary" style="grid-column: 1 / -1;">
        <ion-icon name="refresh-outline"></ion-icon> Periksa Status Ketersediaan
    </button>
</div>
@endsection
