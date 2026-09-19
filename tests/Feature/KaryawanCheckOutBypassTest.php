<?php

namespace Tests\Feature;

use App\Models\PresensiKaryawan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KaryawanCheckOutBypassTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected User $kepsekUser;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'nama_lengkap' => 'Admin Test Checkout',
            'is_active' => 1,
        ]);

        $this->kepsekUser = User::factory()->create([
            'role' => 'kepala_sekolah',
            'nama_lengkap' => 'Kepsek Test Checkout',
            'is_active' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_admin_can_checkout_immediately_without_waiting_one_hour(): void
    {
        $today = '2026-10-10';
        // Admin masuk 10 menit lalu (08:00 WIB, sekarang 08:10 WIB)
        Carbon::setTestNow(Carbon::parse("{$today} 08:10:00", 'Asia/Jakarta'));

        $presensi = PresensiKaryawan::create([
            'user_id' => $this->adminUser->id,
            'tgl_presensi' => $today,
            'jam_mulai' => '08:00:00',
            'foto_mulai' => 'admin_in.jpg',
            'status' => 'hadir',
        ]);

        $fotoOut = UploadedFile::fake()->image('admin_out.jpg');

        $response = $this->actingAs($this->adminUser)->post(route('admin.presensi.store'), [
            'mode' => 'selesai',
            'foto' => $fotoOut,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success');

        $presensi->refresh();
        $this->assertNotNull($presensi->foto_selesai);
        $this->assertNotNull($presensi->jam_selesai);
        $this->assertEquals('08:10:00', $presensi->jam_selesai);
    }

    public function test_kepsek_can_checkout_immediately_without_waiting_one_hour(): void
    {
        $today = '2026-10-10';
        // Kepsek masuk 5 menit lalu (09:00 WIB, sekarang 09:05 WIB)
        Carbon::setTestNow(Carbon::parse("{$today} 09:05:00", 'Asia/Jakarta'));

        $presensi = PresensiKaryawan::create([
            'user_id' => $this->kepsekUser->id,
            'tgl_presensi' => $today,
            'jam_mulai' => '09:00:00',
            'foto_mulai' => 'kepsek_in.jpg',
            'status' => 'hadir',
        ]);

        $fotoOut = UploadedFile::fake()->image('kepsek_out.jpg');

        $response = $this->actingAs($this->kepsekUser)->post(route('kepsek.presensi.store'), [
            'mode' => 'selesai',
            'foto' => $fotoOut,
        ]);

        $response->assertRedirect(route('kepsek.dashboard'));
        $response->assertSessionHas('success');

        $presensi->refresh();
        $this->assertNotNull($presensi->foto_selesai);
        $this->assertNotNull($presensi->jam_selesai);
        $this->assertEquals('09:05:00', $presensi->jam_selesai);
    }

    public function test_admin_and_kepsek_presensi_view_renders_ready_to_checkout_without_countdown(): void
    {
        $today = '2026-10-10';
        Carbon::setTestNow(Carbon::parse("{$today} 08:05:00", 'Asia/Jakarta'));

        // Admin view check
        PresensiKaryawan::create([
            'user_id' => $this->adminUser->id,
            'tgl_presensi' => $today,
            'jam_mulai' => '08:00:00',
            'foto_mulai' => 'admin_in.jpg',
            'status' => 'hadir',
        ]);

        $adminRes = $this->actingAs($this->adminUser)->get(route('admin.presensi'));
        $adminRes->assertStatus(200);
        $adminRes->assertSee('Akses Fleksibel Kepulangan Aktif');
        $adminRes->assertDontSee('id="countdown"', false);
        $adminRes->assertDontSee('minimal 1 jam');
        $adminRes->assertSee('Kirim Presensi Pulang');

        // Kepsek view check
        PresensiKaryawan::create([
            'user_id' => $this->kepsekUser->id,
            'tgl_presensi' => $today,
            'jam_mulai' => '08:00:00',
            'foto_mulai' => 'kepsek_in.jpg',
            'status' => 'hadir',
        ]);

        $kepsekRes = $this->actingAs($this->kepsekUser)->get(route('kepsek.presensi'));
        $kepsekRes->assertStatus(200);
        $kepsekRes->assertSee('Akses Fleksibel Kepulangan Aktif');
        $kepsekRes->assertDontSee('id="countdown"', false);
        $kepsekRes->assertDontSee('minimal 1 jam');
        $kepsekRes->assertSee('Kirim Presensi Pulang');
    }
}
