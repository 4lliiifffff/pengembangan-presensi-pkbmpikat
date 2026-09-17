<?php

namespace Tests\Feature;

use App\Models\JadwalSesi;
use App\Models\JenjangPaket;
use App\Models\kelas;
use App\Models\PresensiMandiriSiswa;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiswaRoleAndPresensiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        Config::set('lokasi.sekolah_lat', -7.8011945);
        Config::set('lokasi.sekolah_lng', 110.364917);
        Config::set('lokasi.radius_meter', 100);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
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

    public function test_siswa_can_login_with_sw001_variation_or_no_absen(): void
    {
        $jp = JenjangPaket::create(['kode' => 'paket_c', 'nama_jenjang' => 'Paket C', 'status' => 'aktif']);
        $kls = kelas::create(['nama_kelas' => 'Paket C - Kelas 10', 'jenjang_paket_id' => $jp->id, 'tingkat' => '10']);

        $siswa = Siswa::create([
            'no_absen' => '001',
            'nama_siswa' => 'Ahmad Rizky Pratama',
            'nama_wali' => 'Bambang',
            'no_hp' => '081234567890',
            'kelas_id' => $kls->id,
            'status_siswa' => 'aktif',
            'is_abk' => false,
        ]);

        $testVariations = ['0001', 'sw0001', 'SW0001', '001', 'sw001', 'SW001', '1', 'SW1', 'siswa001@pkbmpikat.com'];

        foreach ($testVariations as $usernameInput) {
            $this->post(route('logout'));
            $res = $this->post(route('login.process'), [
                'username' => $usernameInput,
                'password' => 'password123',
            ]);
            $res->assertRedirect(route('siswa.dashboard'));
        }
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

    public function test_siswa_cannot_check_in_twice_on_same_day(): void
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

        // Presensi masuk sudah tercatat hari ini
        PresensiMandiriSiswa::create([
            'siswa_id' => $siswa->id,
            'tgl_presensi' => $today,
            'jam_masuk' => '08:00:00',
            'foto_masuk' => 'uploads/test.jpg',
            'lokasi_masuk' => '-7.8011945,110.364917',
            'status' => 'hadir',
        ]);

        $this->actingAs($siswaUser);

        $foto = UploadedFile::fake()->image('selfie_masuk_kedua.jpg', 640, 480);

        // Coba absen lagi di hari yang sama
        $response = $this->post(route('siswa.presensi.store'), [
            'mode' => 'mulai',
            'lokasi' => '-7.8011945,110.364917',
            'foto' => $foto,
        ]);

        $response->assertRedirect(route('siswa.dashboard'));
        $response->assertSessionHas('warning');

        // Pastikan jumlah presensi hari ini tetap 1
        $this->assertEquals(1, PresensiMandiriSiswa::where('siswa_id', $siswa->id)->whereDate('tgl_presensi', $today)->count());
    }

    public function test_siswa_check_in_links_to_scheduled_jadwal_sesi(): void
    {
        $siswaUser = User::factory()->create([
            'role' => 'siswa',
            'nik' => 'SWTEST06B',
        ]);

        $jp = JenjangPaket::create(['kode' => 'paket_c', 'nama_jenjang' => 'Paket C', 'status' => 'aktif']);
        $kls = kelas::create(['nama_kelas' => 'Paket C - Kelas 10', 'jenjang_paket_id' => $jp->id, 'tingkat' => '10']);

        $siswa = Siswa::create([
            'user_id' => $siswaUser->id,
            'no_absen' => 'SW06B',
            'nama_siswa' => 'Rian Siswa Test',
            'nama_wali' => 'Wali',
            'no_hp' => '081234567890',
            'kelas_id' => $kls->id,
            'status_siswa' => 'aktif',
            'is_abk' => false,
        ]);

        $tutorUser = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $tutorUser->id,
            'nik' => 'TTTEST06B',
            'email' => 'tutor06b@pkbmpikat.com',
            'nama_lengkap' => 'Tutor Pengajar',
            'no_hp' => '081234567890',
            'is_active' => true,
        ]);

        $today = Carbon::now('Asia/Jakarta')->toDateString();
        Carbon::setTestNow(Carbon::parse($today.' 08:15:00', 'Asia/Jakarta'));

        $jadwalSesi = JadwalSesi::create([
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswa->id,
            'tanggal_rencana' => $today,
            'jam_masuk_rencana' => '08:00:00',
            'jam_pulang_rencana' => '10:00:00',
            'durasi_jam' => 2.0,
            'jenis_sesi' => 'reguler',
            'status' => 'terjadwal',
            'status_kehadiran_siswa' => 'alpa',
        ]);

        $this->actingAs($siswaUser);

        $foto = UploadedFile::fake()->image('selfie_masuk_siswa.jpg', 640, 480);

        $response = $this->post(route('siswa.presensi.store'), [
            'mode' => 'mulai',
            'lokasi' => '-7.8011945,110.364917',
            'foto' => $foto,
        ]);

        $response->assertRedirect(route('siswa.dashboard'));
        $jadwalSesi->refresh();

        $this->assertEquals('hadir', $jadwalSesi->status_kehadiran_siswa);
        $this->assertNotNull($jadwalSesi->presensi_siswa_id);
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

    public function test_creating_siswa_automatically_creates_and_links_user_account(): void
    {
        $jp = JenjangPaket::create(['kode' => 'paket_c', 'nama_jenjang' => 'Paket C', 'status' => 'aktif']);
        $kls = kelas::create(['nama_kelas' => 'Paket C - Kelas 10', 'jenjang_paket_id' => $jp->id, 'tingkat' => '10']);

        $siswa = Siswa::create([
            'no_absen' => 'SW999',
            'nama_siswa' => 'Rendra Pratama',
            'nama_wali' => 'Pratama',
            'no_hp' => '089988776655',
            'kelas_id' => $kls->id,
            'status_siswa' => 'aktif',
            'is_abk' => false,
        ]);

        $siswa->refresh();
        $this->assertNotNull($siswa->user_id);
        $this->assertNotNull($siswa->user);
        $this->assertEquals('Rendra Pratama', $siswa->user->nama_lengkap);
        $this->assertEquals('siswa', $siswa->user->role);
        $this->assertEquals(1, $siswa->user->is_active);
        $this->assertEquals('089988776655', $siswa->user->no_hp);
    }

    public function test_updating_and_deleting_siswa_synchronizes_user_account(): void
    {
        $jp = JenjangPaket::create(['kode' => 'paket_b', 'nama_jenjang' => 'Paket B', 'status' => 'aktif']);
        $kls = kelas::create(['nama_kelas' => 'Paket B - Kelas 8', 'jenjang_paket_id' => $jp->id, 'tingkat' => '8']);

        $siswa = Siswa::create([
            'no_absen' => 'SW888',
            'nama_siswa' => 'Indah Permata',
            'nama_wali' => 'Wali Indah',
            'no_hp' => '081122334455',
            'kelas_id' => $kls->id,
            'status_siswa' => 'aktif',
            'is_abk' => false,
        ]);

        $siswa->refresh();
        $user = $siswa->user;
        $this->assertNotNull($user);

        // Update status menjadi nonaktif
        $siswa->update([
            'nama_siswa' => 'Indah Permata Putri',
            'status_siswa' => 'nonaktif',
        ]);

        $user->refresh();
        $this->assertEquals('Indah Permata Putri', $user->nama_lengkap);
        $this->assertEquals(0, $user->is_active);

        // Soft delete siswa
        $siswa->delete();
        $user->refresh();
        $this->assertEquals(0, $user->is_active);
    }

    public function test_admin_can_reset_siswa_account_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $jp = JenjangPaket::create(['kode' => 'paket_a', 'nama_jenjang' => 'Paket A', 'status' => 'aktif']);
        $kls = kelas::create(['nama_kelas' => 'Paket A - Kelas 5', 'jenjang_paket_id' => $jp->id, 'tingkat' => '5']);

        $siswa = Siswa::create([
            'no_absen' => 'SW777',
            'nama_siswa' => 'Agus Santoso',
            'nama_wali' => 'Santoso',
            'no_hp' => '087766554433',
            'kelas_id' => $kls->id,
            'status_siswa' => 'aktif',
            'is_abk' => false,
        ]);

        $siswa->refresh();
        $this->actingAs($admin);

        $response = $this->post(route('admin.siswa.resetPassword', $siswa));
        $response->assertRedirect(route('admin.siswa.show', $siswa));
        $response->assertSessionHas('success');

        // Pastikan siswa bisa login dengan password123 setelah direset
        $this->post(route('logout'));
        $loginRes = $this->post(route('login.process'), [
            'username' => $siswa->user->email,
            'password' => 'password123',
        ]);
        $loginRes->assertRedirect(route('siswa.dashboard'));
    }

    public function test_siswa_can_view_jadwal_page_with_calendar_and_sessions(): void
    {
        $jp = JenjangPaket::create(['kode' => 'paket_c', 'nama_jenjang' => 'Paket C', 'status' => 'aktif']);
        $kls = kelas::create(['nama_kelas' => 'Paket C - Kelas 12', 'jenjang_paket_id' => $jp->id, 'tingkat' => '12']);

        $siswa = Siswa::create([
            'no_absen' => 'SW999',
            'nama_siswa' => 'Budi Santoso',
            'nama_wali' => 'Wali Budi',
            'no_hp' => '089911223344',
            'kelas_id' => $kls->id,
            'status_siswa' => 'aktif',
            'is_abk' => false,
        ]);

        $tutorUser = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $tutorUser->id,
            'nama_lengkap' => 'Tutor Matematika Hebat',
            'email' => 'tutor_mtk@pkbmpikat.com',
            'nik' => 'NIKTUTOR99',
            'no_hp' => '081299887766',
            'status' => 'aktif',
        ]);

        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // Buat Sesi KBM
        JadwalSesi::create([
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswa->id,
            'tanggal_rencana' => $today,
            'jam_masuk_rencana' => '09:00:00',
            'jam_pulang_rencana' => '11:00:00',
            'durasi_jam' => 2.0,
            'jenis_sesi' => 'reguler',
            'status' => 'terjadwal',
            'status_kehadiran_siswa' => 'belum',
        ]);

        $siswa->refresh();
        $this->actingAs($siswa->user);

        $response = $this->get(route('siswa.jadwal'));
        $response->assertStatus(200);
        $response->assertSee('Jadwal Siswa PKBM');
        $response->assertSee('Tutor Matematika Hebat');
        $response->assertSee('09:00');
        $response->assertSee('11:00');
    }
}
