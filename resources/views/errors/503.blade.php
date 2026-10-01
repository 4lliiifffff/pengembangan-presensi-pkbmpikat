@extends('errors.layout')

@section('title', '503 - Layanan Sedang Pemeliharaan')
@section('toast_type', 'info')
@section('toast_title', 'Pemeliharaan Sistem (503)')
@section('toast_message', $exception->getMessage() ?: 'Sistem sedang dalam proses peningkatan dan pemeliharaan berkala.')

@section('content')
<div class="error-code-badge" style="background: rgba(11, 94, 215, 0.12); color: #0b5ed7; border: 1px solid rgba(11, 94, 215, 0.25);">
    <ion-icon name="construct-outline"></ion-icon> STATUS 503 &bull; MAINTENANCE
</div>

<div class="error-icon-wrapper" style="background: rgba(11, 94, 215, 0.1); color: #0b5ed7; border: 1px solid rgba(11, 94, 215, 0.2);">
    <ion-icon name="hammer-outline"></ion-icon>
</div>

<h1 class="error-title">Sedang Pemeliharaan Sistem</h1>
<p class="error-description">
    {{ $exception->getMessage() ?: 'Aplikasi presensi PKBM Pikat sedang ditingkatkan untuk memberikan performa dan fitur yang lebih baik. Layanan akan segera kembali aktif dalam beberapa saat.' }}
</p>

<div class="error-actions-grid">
    <button type="button" onclick="window.location.reload()" class="btn-error-primary" style="grid-column: 1 / -1;">
        <ion-icon name="refresh-outline"></ion-icon> Periksa Ketersediaan
    </button>
</div>
@endsection
