<?php

namespace Tests\Feature;

use App\Models\JenjangPaket;
use App\Models\kelas;
use App\Models\PresensiMandiriSiswa;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiswaRoleAndPresensiTest extends TestCase
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

    public function test_siswa_user_login_redirects_to_siswa_dashboard(): void
    {
        $siswaUser = User::factory()->create([
            'role' => 'siswa',
            'nik' => 'SWTEST01',
            'password' => bcrypt('password123'),
        ]);

        $jp = JenjangPaket::create(['kode' => 'paket_c', 'nama_jenjang' => 'Paket C', 'status' => 'aktif']);
        $kls = kelas::create(['nama_kelas' => 'Paket C - Kelas 10', 'jenjang_paket_id' => $jp->id, 'tingkat' => '10']);

        Siswa::create([
            'user_id' => $siswaUser->id,
            'no_absen' => 'SW01',
            'nama_siswa' => 'Ahmad Rizky Test',
            'nama_wali' => 'Bambang',
            'no_hp' => '081234567890',
            'kelas_id' => $kls->id,
            'status_siswa' => 'aktif',
            'is_abk' => false,
        ]);

        $response = $this->post(route('login.process'), [
            'username' => 'SWTEST01',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('siswa.dashboard'));
    }

    public function test_siswa_user_cannot_access_admin_or_tutor_routes(): void
    {
        $siswaUser = User::factory()->create([
            'role' => 'siswa',
            'nik' => 'SWTEST02',
        ]);

        $jp = JenjangPaket::create(['kode' => 'paket_b', 'nama_jenjang' => 'Paket B', 'status' => 'aktif']);
        $kls = kelas::create(['nama_kelas' => 'Paket B - Kelas 7', 'jenjang_paket_id' => $jp->id, 'tingkat' => '7']);

        Siswa::create([
            'user_id' => $siswaUser->id,
            'no_absen' => 'SW02',
            'nama_siswa' => 'Dewi Test',
            'nama_wali' => 'Hendro',
            'no_hp' => '081234567890',
            'kelas_id' => $kls->id,
            'status_siswa' => 'aktif',
            'is_abk' => false,
        ]);

        $this->actingAs($siswaUser);

        $responseAdmin = $this->get(route('admin.dashboard'));
        $responseAdmin->assertRedirect(route('siswa.dashboard'));

        $responseTutor = $this->get(route('tutor.dashboard'));
        $responseTutor->assertRedirect(route('siswa.dashboard'));
    }

    public function test_siswa_can_view_dashboard_and_presensi_camera_page(): void
    {
        $siswaUser = User::factory()->create([
            'role' => 'siswa',
            'nik' => 'SWTEST03',
        ]);

        $jp = JenjangPaket::create(['kode' => 'paket_a', 'nama_jenjang' => 'Paket A', 'status' => 'aktif']);
        $kls = kelas::create(['nama_kelas' => 'Paket A - Kelas 1', 'jenjang_paket_id' => $jp->id, 'tingkat' => '1']);

        Siswa::create([
            'user_id' => $siswaUser->id,
            'no_absen' => 'SW03',
            'nama_siswa' => 'Budi Siswa Test',
            'nama_wali' => 'Supri',
            'no_hp' => '081234567890',
            'kelas_id' => $kls->id,
            'status_siswa' => 'aktif',
            'is_abk' => false,
        ]);

        $this->actingAs($siswaUser);

        $responseDash = $this->get(route('siswa.dashboard'));
        $responseDash->assertStatus(200);
        $responseDash->assertSee('Budi Siswa Test');

        $responsePresensi = $this->get(route('siswa.presensi.foto'));
        $responsePresensi->assertStatus(200);
        $responsePresensi->assertSee('Presensi Mandiri');
    }

    public function test_siswa_can_clock_in_within_geofence_and_photo(): void
    {
        $siswaUser = User::factory()->create([
            'role' => 'siswa',
            'nik' => 'SWTEST04',
        ]);

        $jp = JenjangPaket::create(['kode' => 'paket_c', 'nama_jenjang' => 'Paket C', 'status' => 'aktif']);
        $kls = kelas::create(['nama_kelas' => 'Paket C - Kelas 10', 'jenjang_paket_id' => $jp->id, 'tingkat' => '10']);

        $siswa = Siswa::create([
            'user_id' => $siswaUser->id,
            'no_absen' => 'SW04',
            'nama_siswa' => 'Siti Siswa Test',
            'nama_wali' => 'Nur',
            'no_hp' => '081234567890',
            'kelas_id' => $kls->id,
            'status_siswa' => 'aktif',
            'is_abk' => false,
        ]);

        $this->actingAs($siswaUser);

        $foto = UploadedFile::fake()->image('selfie_masuk_siswa.jpg', 640, 480);

        // Koordinat tepat di PKBM Pikat (0m < 100m)
        $response = $this->post(route('siswa.presensi.store'), [
            'mode' => 'mulai',
            'lokasi' => '-7.8011945,110.364917',
            'foto' => $foto,
        ]);

        $response->assertRedirect(route('siswa.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('presensi_mandiri_siswas', [
            'siswa_id' => $siswa->id,
            'tgl_presensi' => Carbon::now('Asia/Jakarta')->toDateString(),
            'status' => 'hadir',
        ]);
    }

    public function test_siswa_clock_in_fails_outside_geofence(): void
    {
        $siswaUser = User::factory()->create([
            'role' => 'siswa',
            'nik' => 'SWTEST05',
        ]);

        $jp = JenjangPaket::create(['kode' => 'paket_c', 'nama_jenjang' => 'Paket C', 'status' => 'aktif']);
        $kls = kelas::create(['nama_kelas' => 'Paket C - Kelas 10', 'jenjang_paket_id' => $jp->id, 'tingkat' => '10']);

        $siswa = Siswa::create([
            'user_id' => $siswaUser->id,
            'no_absen' => 'SW05',
            'nama_siswa' => 'Eko Siswa Test',
            'nama_wali' => 'Bambang',
            'no_hp' => '081234567890',
            'kelas_id' => $kls->id,
            'status_siswa' => 'aktif',
            'is_abk' => false,
        ]);

        $this->actingAs($siswaUser);

        $foto = UploadedFile::fake()->image('selfie_masuk_siswa.jpg', 640, 480);

        // Koordinat Monas Jakarta (~450 km dari Yogyakarta)
        $response = $this->post(route('siswa.presensi.store'), [
            'mode' => 'mulai',
            'lokasi' => '-6.175392,106.827153',
            'foto' => $foto,
        ]);

        $response->assertSessionHas('warning');
        $this->assertDatabaseMissing('presensi_mandiri_siswas', [
            'siswa_id' => $siswa->id,
        ]);
    }

    public function test_siswa_clock_out_requires_minimum_15_minutes(): void
    {
        $siswaUser = User::factory()->create([
            'role' => 'siswa',
            'nik' => 'SWTEST06',
        ]);

        $jp = JenjangPaket::create(['kode' => 'paket_c', 'nama_jenjang' => 'Paket C', 'status' => 'aktif']);
        $kls = kelas::create(['nama_kelas' => 'Paket C - Kelas 10', 'jenjang_paket_id' => $jp->id, 'tingkat' => '10']);

        $siswa = Siswa::create([
            'user_id' => $siswaUser->id,
            'no_absen' => 'SW06',
            'nama_siswa' => 'Fajar Siswa Test',
            'nama_wali' => 'Wali',
            'no_hp' => '081234567890',
            'kelas_id' => $kls->id,
            'status_siswa' => 'aktif',
            'is_abk' => false,
        ]);

        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // Presensi masuk baru 5 menit lalu
        $presensi = PresensiMandiriSiswa::create([
            'siswa_id' => $siswa->id,
            'tgl_presensi' => $today,
            'jam_masuk' => Carbon::now('Asia/Jakarta')->subMinutes(5)->toTimeString(),
            'foto_masuk' => 'uploads/test.jpg',
            'lokasi_masuk' => '-7.8011945,110.364917',
            'status' => 'hadir',
        ]);

        $this->actingAs($siswaUser);

        $foto = UploadedFile::fake()->image('selfie_pulang_siswa.jpg', 640, 480);

        // Coba absen pulang (kurang dari 15 menit)
        $response = $this->post(route('siswa.presensi.store'), [
            'mode' => 'selesai',
            'lokasi' => '-7.8011945,110.364917',
            'foto' => $foto,
        ]);

        $response->assertSessionHas('warning');
        $this->assertNull($presensi->fresh()->jam_pulang);

        // Sekarang kita set jam masuk sudah 30 menit lalu
        $presensi->update([
            'jam_masuk' => Carbon::now('Asia/Jakarta')->subMinutes(30)->toTimeString(),
        ]);

        $responseSuccess = $this->post(route('siswa.presensi.store'), [
            'mode' => 'selesai',
            'lokasi' => '-7.8011945,110.364917',
            'foto' => $foto,
        ]);

        $responseSuccess->assertRedirect(route('siswa.dashboard'));
        $responseSuccess->assertSessionHas('success');
        $this->assertNotNull($presensi->fresh()->jam_pulang);
    }

    public function test_siswa_can_view_riwayat_and_profil(): void
    {
        $siswaUser = User::factory()->create([
            'role' => 'siswa',
            'nik' => 'SWTEST07',
        ]);

        $jp = JenjangPaket::create(['kode' => 'paket_c', 'nama_jenjang' => 'Paket C', 'status' => 'aktif']);
        $kls = kelas::create(['nama_kelas' => 'Paket C - Kelas 10', 'jenjang_paket_id' => $jp->id, 'tingkat' => '10']);

        Siswa::create([
            'user_id' => $siswaUser->id,
            'no_absen' => 'SW07',
            'nama_siswa' => 'Gita Siswa Test',
            'nama_wali' => 'Wali Gita',
            'no_hp' => '081234567890',
            'kelas_id' => $kls->id,
            'status_siswa' => 'aktif',
            'is_abk' => false,
        ]);

        $this->actingAs($siswaUser);

        $responseRiwayat = $this->get(route('siswa.riwayat'));
        $responseRiwayat->assertStatus(200);
        $responseRiwayat->assertSee('Riwayat Presensi Mandiri');

        $responseProfil = $this->get(route('siswa.profil'));
        $responseProfil->assertStatus(200);
        $responseProfil->assertSee('Gita Siswa Test');
    }
}
