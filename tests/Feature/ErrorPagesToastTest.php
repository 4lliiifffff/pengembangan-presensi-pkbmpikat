<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ErrorPagesToastTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_error_page_renders_with_toast_metadata(): void
    {
        $response = $this->get('/halaman-yang-pasti-tidak-ada-xyz-12345');

        $response->assertStatus(404);
        $response->assertSee('404');
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee('AppNotification.toast', false);
    }

    public function test_403_error_view_renders_properly(): void
    {
        $response = $this->view('errors.403', [
            'exception' => new HttpException(403, 'Akses khusus administrator'),
        ]);

        $response->assertSee('403');
        $response->assertSee('Akses Ditolak');
        $response->assertSee('Akses khusus administrator');
    }

    public function test_401_error_view_renders_properly(): void
    {
        $response = $this->view('errors.401', [
            'exception' => new HttpException(401, 'Silakan login terlebih dahulu'),
        ]);

        $response->assertSee('401');
        $response->assertSee('Akses Belum Terautentikasi');
    }

    public function test_419_error_view_renders_properly(): void
    {
        $response = $this->view('errors.419', [
            'exception' => new HttpException(419, 'CSRF token mismatch'),
        ]);

        $response->assertSee('419');
        $response->assertSee('Sesi Halaman Kedaluwarsa');
    }

    public function test_429_error_view_renders_properly(): void
    {
        $response = $this->view('errors.429', [
            'exception' => new HttpException(429, 'Terlalu banyak request'),
        ]);

        $response->assertSee('429');
        $response->assertSee('Terlalu Banyak Permintaan');
    }

    public function test_500_error_view_renders_properly(): void
    {
        $response = $this->view('errors.500', [
            'exception' => new HttpException(500, 'Server bermasalah'),
        ]);

        $response->assertSee('500');
        $response->assertSee('Terjadi Kendala Teknis');
    }

    public function test_503_error_view_renders_properly(): void
    {
        $response = $this->view('errors.503', [
            'exception' => new HttpException(503, 'Maintenance mode'),
        ]);

        $response->assertSee('503');
        $response->assertSee('Sedang Pemeliharaan Sistem');
    }
}
