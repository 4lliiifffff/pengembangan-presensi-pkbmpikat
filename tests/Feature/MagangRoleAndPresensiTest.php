<?php

namespace Tests\Feature;

use App\Models\Magang;
use App\Models\PresensiKaryawan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MagangRoleAndPresensiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        Config::set('lokasi.sekolah_lat', -7.8011945);
        Config::set('lokasi.sekolah_lng', 110.364917);
        Config::set('lokasi.radius_meter', 100);
    }

    public function test_magang_user_login_redirects_to_magang_dashboard(): void
    {
        $magangUser = User::factory()->create([
            'role' => 'magang',
            'nik' => 'MG202601',
            'password' => bcrypt('password123'),
        ]);

        Magang::create([
            'user_id' => $magangUser->id,
            'asal_instansi' => 'Universitas Negeri Yogyakarta',
            'nim_nisn' => '210101001',
            'jurusan' => 'Pendidikan Luar Sekolah',
            'tgl_mulai' => Carbon::now()->subMonth()->toDateString(),
            'tgl_selesai' => Carbon::now()->addMonths(2)->toDateString(),
            'status' => 'aktif',
        ]);

        $response = $this->post(route('login.process'), [
            'username' => 'MG202601',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('magang.dashboard'));
    }

    public function test_magang_user_cannot_access_admin_or_tutor_routes(): void
    {
        $magangUser = User::factory()->create([
            'role' => 'magang',
            'nik' => 'MG202601',
        ]);

        $this->actingAs($magangUser);

        $responseAdmin = $this->get(route('admin.dashboard'));
        $responseAdmin->assertRedirect(route('magang.dashboard'));

        $responseTutor = $this->get(route('tutor.dashboard'));
        $responseTutor->assertRedirect(route('magang.dashboard'));
    }

    public function test_magang_can_view_dashboard_and_presensi_camera_page(): void
    {
        $magangUser = User::factory()->create([
            'role' => 'magang',
            'nik' => 'MG202601',
        ]);

        Magang::create([
            'user_id' => $magangUser->id,
            'asal_instansi' => 'Universitas Gadjah Mada',
            'nim_nisn' => '210101002',
            'tgl_mulai' => Carbon::now()->subMonth()->toDateString(),
            'tgl_selesai' => Carbon::now()->addMonths(2)->toDateString(),
            'status' => 'aktif',
        ]);

        $this->actingAs($magangUser);

        $responseDash = $this->get(route('magang.dashboard'));
        $responseDash->assertStatus(200);
        $responseDash->assertSee('Universitas Gadjah Mada');

        $responsePresensi = $this->get(route('magang.presensi.foto'));
        $responsePresensi->assertStatus(200);
        $responsePresensi->assertSee('Presensi Magang');
    }

    public function test_magang_can_clock_in_within_geofence_and_photo(): void
    {
        $magangUser = User::factory()->create([
            'role' => 'magang',
            'nik' => 'MG202601',
        ]);

        Magang::create([
            'user_id' => $magangUser->id,
            'asal_instansi' => 'UNY',
            'tgl_mulai' => Carbon::now()->subMonth()->toDateString(),
            'tgl_selesai' => Carbon::now()->addMonths(2)->toDateString(),
            'status' => 'aktif',
        ]);

        $this->actingAs($magangUser);

        $foto = UploadedFile::fake()->image('selfie_masuk.jpg', 640, 480);

        // Koordinat tepat di PKBM Pikat (jarak 0m < 100m)
        $response = $this->post(route('magang.presensi.store'), [
            'mode' => 'mulai',
            'lokasi' => '-7.8011945,110.364917',
            'foto' => $foto,
        ]);

        $response->assertRedirect(route('magang.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('presensi_karyawans', [
            'user_id' => $magangUser->id,
            'tgl_presensi' => Carbon::now('Asia/Jakarta')->toDateString(),
            'status' => 'hadir',
        ]);
    }

    public function test_magang_clock_in_fails_outside_geofence(): void
    {
        $magangUser = User::factory()->create([
            'role' => 'magang',
            'nik' => 'MG202601',
        ]);

        Magang::create([
            'user_id' => $magangUser->id,
            'asal_instansi' => 'UNY',
            'tgl_mulai' => Carbon::now()->subMonth()->toDateString(),
            'tgl_selesai' => Carbon::now()->addMonths(2)->toDateString(),
            'status' => 'aktif',
        ]);

        $this->actingAs($magangUser);

        $foto = UploadedFile::fake()->image('selfie_masuk.jpg', 640, 480);

        // Koordinat Monas Jakarta (~450 km dari Yogyakarta)
        $response = $this->post(route('magang.presensi.store'), [
            'mode' => 'mulai',
            'lokasi' => '-6.175392,106.827153',
            'foto' => $foto,
        ]);

        $response->assertSessionHas('warning');
        $this->assertDatabaseMissing('presensi_karyawans', [
            'user_id' => $magangUser->id,
        ]);
    }

    public function test_magang_clock_out_requires_minimum_1_hour_duration(): void
    {
        $magangUser = User::factory()->create([
            'role' => 'magang',
            'nik' => 'MG202601',
        ]);

        Magang::create([
            'user_id' => $magangUser->id,
            'asal_instansi' => 'UNY',
            'tgl_mulai' => Carbon::now()->subMonth()->toDateString(),
            'tgl_selesai' => Carbon::now()->addMonths(2)->toDateString(),
            'status' => 'aktif',
        ]);

        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // Presensi masuk baru 10 menit lalu
        $presensi = PresensiKaryawan::create([
            'user_id' => $magangUser->id,
            'tgl_presensi' => $today,
            'jam_mulai' => Carbon::now('Asia/Jakarta')->subMinutes(10)->toTimeString(),
            'foto_mulai' => 'uploads/test.jpg',
            'lokasi_mulai' => '-7.8011945,110.364917',
            'status' => 'hadir',
        ]);

        $this->actingAs($magangUser);

        $foto = UploadedFile::fake()->image('selfie_pulang.jpg', 640, 480);

        // Coba absen pulang
        $response = $this->post(route('magang.presensi.store'), [
            'mode' => 'selesai',
            'lokasi' => '-7.8011945,110.364917',
            'foto' => $foto,
        ]);

        $response->assertSessionHas('warning');
        $this->assertNull($presensi->fresh()->jam_selesai);

        // Sekarang kita set jam masuk sudah 2 jam lalu
        $presensi->update([
            'jam_mulai' => Carbon::now('Asia/Jakarta')->subHours(2)->toTimeString(),
        ]);

        $responseSuccess = $this->post(route('magang.presensi.store'), [
            'mode' => 'selesai',
            'lokasi' => '-7.8011945,110.364917',
            'foto' => $foto,
        ]);

        $responseSuccess->assertRedirect(route('magang.dashboard'));
        $responseSuccess->assertSessionHas('success');
        $this->assertNotNull($presensi->fresh()->jam_selesai);
    }

    public function test_admin_can_crud_magang_and_export_pdf(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        // 1. Create Magang
        $responseCreate = $this->post(route('admin.magang.store'), [
            'nama_lengkap' => 'Budi Santoso',
            'nik' => 'MG9999',
            'email' => 'budi@kampus.ac.id',
            'password' => 'secret123',
            'asal_instansi' => 'Politeknik Negeri',
            'nim_nisn' => '99999',
            'jurusan' => 'Informatika',
            'tgl_mulai' => '2026-09-01',
            'tgl_selesai' => '2026-11-30',
        ]);

        $responseCreate->assertRedirect(route('admin.magang.index'));
        $this->assertDatabaseHas('users', ['email' => 'budi@kampus.ac.id', 'role' => 'magang']);
        $this->assertDatabaseHas('magangs', ['asal_instansi' => 'Politeknik Negeri']);

        $magangUser = User::where('email', 'budi@kampus.ac.id')->first();

        // 2. Monitoring Presensi view
        $responsePresensi = $this->get(route('admin.magang.presensi'));
        $responsePresensi->assertStatus(200);

        // 3. Export PDF
        $responsePdf = $this->get(route('admin.magang.exportPdf', ['user_id' => $magangUser->id]));
        $responsePdf->assertStatus(200);
        $this->assertTrue(str_contains($responsePdf->headers->get('content-type') ?? '', 'application/pdf'));
    }
}
