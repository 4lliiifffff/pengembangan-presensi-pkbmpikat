<?php

namespace Tests\Feature;

use App\Models\JadwalRutin;
use App\Models\JadwalSesi;
use App\Models\JenjangPaket;
use App\Models\kelas as Kelas;
use App\Models\LokasiPresensi;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use App\Services\JadwalRutinService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JadwalRutinAndPresensiGatingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected User $tutorUser;

    protected Tutor $tutor;

    protected User $siswaUser;

    protected Siswa $siswa;

    protected LokasiPresensi $lokasi;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        Config::set('lokasi.sekolah_lat', -7.8011945);
        Config::set('lokasi.sekolah_lng', 110.364917);
        Config::set('lokasi.radius_meter', 100);

        $this->adminUser = User::factory()->create(['role' => 'admin', 'is_active' => 1]);

        $this->tutorUser = User::factory()->create(['role' => 'tutor', 'is_active' => 1]);
        $this->tutor = Tutor::create([
            'user_id' => $this->tutorUser->id,
            'nama_lengkap' => 'Tutor Rutin Test',
            'email' => 'tutor_rutin@pkbmpikat.com',
            'nik' => 'NIKRUTIN01',
        ]);

        $this->siswaUser = User::factory()->create(['role' => 'siswa', 'is_active' => 1]);
        $jp = JenjangPaket::firstOrCreate(['kode' => 'paket_c_rutin'], ['nama_jenjang' => 'Paket C Rutin', 'status' => 'aktif']);
        $kls = Kelas::firstOrCreate(['nama_kelas' => 'Kelas 10 Paket C Rutin'], ['jenjang_paket_id' => $jp->id, 'tingkat' => '10']);
        $this->siswa = Siswa::create([
            'user_id' => $this->siswaUser->id,
            'nama_siswa' => 'Siswa Rutin Test',
            'nama_wali' => 'Wali Rutin Test',
            'kelas_id' => $kls->id,
            'tutor_id' => $this->tutor->id,
            'status_siswa' => 'aktif',
            'no_absen' => 'RUT-01',
            'no_hp' => '081234567890',
            'is_abk' => false,
        ]);

        $this->lokasi = LokasiPresensi::create([
            'nama_lokasi' => 'Gedung Utama PKBM Pikat',
            'latitude' => -7.8011945,
            'longitude' => 110.364917,
            'radius_meter' => 100,
            'is_aktif' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_admin_can_view_and_create_jadwal_rutin_for_specific_student(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.jadwal-rutin.index'));
        $response->assertStatus(200);
        $response->assertSee('Master Jadwal Rutin KBM Siswa');

        $storeResponse = $this->actingAs($this->adminUser)->post(route('admin.jadwal-rutin.store'), [
            'siswa_id' => $this->siswa->id,
            'tutor_id' => $this->tutor->id,
            'hari' => 'selasa',
            'jam_masuk' => '09:00',
            'jam_pulang' => '11:00',
            'durasi_jam' => '2.00',
            'is_active' => '1',
            'auto_generate' => '0',
            'keterangan' => 'KBM Rutin Selasa Siswa Rutin Test',
        ]);

        $storeResponse->assertRedirect(route('admin.jadwal-rutin.index'));
        $storeResponse->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_rutins', [
            'siswa_id' => $this->siswa->id,
            'tutor_id' => $this->tutor->id,
            'hari' => 'selasa',
            'jam_masuk' => '09:00:00',
            'jam_pulang' => '11:00:00',
            'is_active' => 1,
        ]);
    }

    public function test_jadwal_rutin_service_generates_sessions_correctly(): void
    {
        $rutin = JadwalRutin::create([
            'siswa_id' => $this->siswa->id,
            'tutor_id' => $this->tutor->id,
            'hari' => 'rabu',
            'jam_masuk' => '13:00',
            'jam_pulang' => '15:00',
            'durasi_jam' => 2.00,
            'is_active' => true,
        ]);

        $service = app(JadwalRutinService::class);
        // Periode 2 minggu dari sekarang
        $startDate = '2026-10-01'; // Kamis
        $endDate = '2026-10-14';   // Rabu

        $result = $service->generateSesiForPeriod($startDate, $endDate, $this->siswa->id);

        $this->assertGreaterThan(0, $result['created']);

        // Pastikan instance jadwal sesi terbuat dengan jadwal_rutin_id yang tepat
        $this->assertDatabaseHas('jadwal_sesis', [
            'jadwal_rutin_id' => $rutin->id,
            'siswa_id' => $this->siswa->id,
            'tutor_id' => $this->tutor->id,
            'jam_masuk_rencana' => '13:00:00',
            'jam_pulang_rencana' => '15:00:00',
        ]);
    }

    public function test_reschedule_single_session_does_not_mutate_master_rutin(): void
    {
        $rutin = JadwalRutin::create([
            'siswa_id' => $this->siswa->id,
            'tutor_id' => $this->tutor->id,
            'hari' => 'kamis',
            'jam_masuk' => '09:00',
            'jam_pulang' => '11:00',
            'durasi_jam' => 2.00,
            'is_active' => true,
        ]);

        $sesi = JadwalSesi::create([
            'jadwal_rutin_id' => $rutin->id,
            'siswa_id' => $this->siswa->id,
            'tutor_id' => $this->tutor->id,
            'tanggal_rencana' => '2026-10-08',
            'jam_masuk_rencana' => '09:00:00',
            'jam_pulang_rencana' => '11:00:00',
            'durasi_jam' => 2.00,
            'jenis_sesi' => 'reguler',
            'status' => 'terjadwal',
        ]);

        // Reschedule ke hari Jumat jam 14:00
        $sesi->update([
            'tanggal_rencana' => '2026-10-09',
            'jam_masuk_rencana' => '14:00:00',
            'jam_pulang_rencana' => '16:00:00',
            'jenis_sesi' => 'pengganti',
            'tanggal_asli' => '2026-10-08',
            'alasan_penggantian' => 'Kesepakatan tutor dan siswa ganti hari',
        ]);

        // Master rutin harus tetap Kamis jam 09:00!
        $rutinFresh = $rutin->fresh();
        $this->assertEquals('kamis', $rutinFresh->hari);
        $this->assertEquals('09:00:00', $rutinFresh->jam_masuk);

        // Sedangkan instance sesi sudah berubah menjadi hari Jumat
        $sesiFresh = $sesi->fresh();
        $this->assertEquals('2026-10-09', $sesiFresh->tanggal_rencana->toDateString());
        $this->assertEquals('14:00:00', $sesiFresh->jam_masuk_rencana);
        $this->assertEquals('pengganti', $sesiFresh->jenis_sesi);
    }

    public function test_siswa_cannot_checkin_on_unscheduled_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-05 09:00:00', 'Asia/Jakarta')); // Senin
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // Siswa memiliki jadwal rutin di hari Selasa, hari ini Senin
        JadwalRutin::create([
            'siswa_id' => $this->siswa->id,
            'tutor_id' => $this->tutor->id,
            'hari' => 'selasa',
            'jam_masuk' => '09:00',
            'jam_pulang' => '11:00',
            'durasi_jam' => 2.00,
            'is_active' => true,
        ]);

        // Pastikan tidak ada jadwal untuk siswa pada hari ini (Senin)
        JadwalSesi::where('siswa_id', $this->siswa->id)->whereDate('tanggal_rencana', $today)->delete();

        $foto = UploadedFile::fake()->image('absen.jpg', 640, 480);

        $response = $this->actingAs($this->siswaUser)->post(route('siswa.presensi.store'), [
            'mode' => 'masuk',
            'foto' => $foto,
            'lokasi' => '-7.8011945,110.364917',
            'lokasi_akurasi' => 15,
            'lokasi_presensi_id' => $this->lokasi->id,
        ]);

        $response->assertRedirect(route('siswa.dashboard'));
        $response->assertSessionHas('warning');

        $this->assertDatabaseMissing('presensi_mandiri_siswas', [
            'siswa_id' => $this->siswa->id,
            'tgl_presensi' => $today,
        ]);
    }

    public function test_siswa_cannot_checkin_too_early_before_session(): void
    {
        // Jadwal KBM jam 09:00 - 11:00 WIB
        // Jam tes sekarang: 08:00 WIB (lebih awal dari batas H-30m yang dibuka 08:30 WIB)
        Carbon::setTestNow(Carbon::parse('2026-10-06 08:00:00', 'Asia/Jakarta'));
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        JadwalSesi::create([
            'siswa_id' => $this->siswa->id,
            'tutor_id' => $this->tutor->id,
            'tanggal_rencana' => $today,
            'jam_masuk_rencana' => '09:00:00',
            'jam_pulang_rencana' => '11:00:00',
            'durasi_jam' => 2.00,
            'status' => 'terjadwal',
        ]);

        $foto = UploadedFile::fake()->image('absen_pagi.jpg', 640, 480);

        $response = $this->actingAs($this->siswaUser)->post(route('siswa.presensi.store'), [
            'mode' => 'masuk',
            'foto' => $foto,
            'lokasi' => '-7.8011945,110.364917',
            'lokasi_akurasi' => 15,
            'lokasi_presensi_id' => $this->lokasi->id,
        ]);

        $response->assertRedirect(route('siswa.presensi'));
        $response->assertSessionHas('warning');

        $this->assertDatabaseMissing('presensi_mandiri_siswas', [
            'siswa_id' => $this->siswa->id,
            'tgl_presensi' => $today,
        ]);
    }

    public function test_siswa_can_checkin_within_tolerance_window(): void
    {
        // Jadwal KBM jam 09:00 - 11:00 WIB
        // Jam tes sekarang: 08:40 WIB (masuk jendela buka absen 08:30 WIB)
        Carbon::setTestNow(Carbon::parse('2026-10-06 08:40:00', 'Asia/Jakarta'));
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $sesi = JadwalSesi::create([
            'siswa_id' => $this->siswa->id,
            'tutor_id' => $this->tutor->id,
            'tanggal_rencana' => $today,
            'jam_masuk_rencana' => '09:00:00',
            'jam_pulang_rencana' => '11:00:00',
            'durasi_jam' => 2.00,
            'status' => 'terjadwal',
            'status_kehadiran_siswa' => 'belum_presensi',
        ]);

        $foto = UploadedFile::fake()->image('absen_sah.jpg', 640, 480);

        $response = $this->actingAs($this->siswaUser)->post(route('siswa.presensi.store'), [
            'mode' => 'masuk',
            'foto' => $foto,
            'lokasi' => '-7.8011945,110.364917',
            'lokasi_akurasi' => 15,
            'lokasi_presensi_id' => $this->lokasi->id,
        ]);

        $response->assertRedirect(route('siswa.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('presensi_mandiri_siswas', [
            'siswa_id' => $this->siswa->id,
            'tgl_presensi' => $today,
            'status' => 'hadir',
        ]);

        // Status sesi juga ter-link
        $this->assertEquals('hadir', $sesi->fresh()->status_kehadiran_siswa);
    }

    public function test_artisan_command_generates_sessions(): void
    {
        JadwalRutin::create([
            'siswa_id' => $this->siswa->id,
            'tutor_id' => $this->tutor->id,
            'hari' => 'jumat',
            'jam_masuk' => '10:00',
            'jam_pulang' => '12:00',
            'durasi_jam' => 2.00,
            'is_active' => true,
        ]);

        $this->artisan('jadwal:generate-sesi', [
            '--weeks' => 2,
            '--siswa_id' => $this->siswa->id,
        ])->assertExitCode(0);
    }
}
