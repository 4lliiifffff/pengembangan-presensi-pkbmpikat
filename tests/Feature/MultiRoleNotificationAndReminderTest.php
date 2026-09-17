<?php

namespace Tests\Feature;

use App\Models\JadwalSesi;
use App\Models\JenjangPaket;
use App\Models\kelas as Kelas;
use App\Models\LokasiPresensi;
use App\Models\PengajuanIzinSakit;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use App\Services\WebPushService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MultiRoleNotificationAndReminderTest extends TestCase
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

    public function test_send_absen_reminder_morning_command_executes_successfully(): void
    {
        $today = Carbon::today('Asia/Jakarta')->toDateString();

        // 1. Create Tutor with User
        $tutorUser = User::factory()->create(['role' => 'tutor', 'is_active' => 1]);
        $tutor = Tutor::create([
            'user_id' => $tutorUser->id,
            'nama_lengkap' => 'Tutor Reminder Test',
            'email' => 'tutor_rem@pkbmpikat.com',
            'nik' => 'NIKREM01',
        ]);

        // 2. Create Siswa with User
        $siswaUser = User::factory()->create(['role' => 'siswa', 'is_active' => 1]);
        $jp = JenjangPaket::firstOrCreate(['kode' => 'paket_b_rem'], ['nama_jenjang' => 'Paket B Reminder', 'status' => 'aktif']);
        $kls = Kelas::firstOrCreate(['nama_kelas' => 'Kelas 7 Paket B Rem'], ['jenjang_paket_id' => $jp->id, 'tingkat' => '7']);
        $siswa = Siswa::create([
            'user_id' => $siswaUser->id,
            'no_absen' => 'SWREM01',
            'nama_siswa' => 'Siswa Reminder Test',
            'nama_wali' => 'Wali Test',
            'no_hp' => '081234567800',
            'kelas_id' => $kls->id,
            'status_siswa' => 'aktif',
            'is_abk' => false,
        ]);

        // 3. Create Session between Tutor and Siswa for today
        JadwalSesi::create([
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswa->id,
            'tanggal_rencana' => $today,
            'jam_masuk_rencana' => '08:30:00',
            'jam_pulang_rencana' => '10:30:00',
            'durasi_jam' => 2.0,
            'jenis_sesi' => 'reguler',
            'status' => 'terjadwal',
            'status_kehadiran_siswa' => 'belum_presensi',
        ]);

        // 4. Mock WebPushService
        $mockWebPush = $this->createMock(WebPushService::class);
        $mockWebPush->expects($this->atLeastOnce())
            ->method('sendToUser')
            ->willReturn(1);
        $this->app->instance(WebPushService::class, $mockWebPush);

        // Run Morning Reminder Command
        $this->artisan('presensi:send-reminder', ['--type' => 'morning', '--role' => 'all'])
            ->assertExitCode(0);
    }

    public function test_send_absen_reminder_clockout_command_executes_for_active_sessions(): void
    {
        $today = Carbon::today('Asia/Jakarta')->toDateString();

        // Kunci waktu saat ini ke jam 10:00 WIB
        Carbon::setTestNow(Carbon::parse($today.' 10:00:00', 'Asia/Jakarta'));

        $tutorUser = User::factory()->create(['role' => 'tutor', 'is_active' => 1]);
        $tutor = Tutor::create([
            'user_id' => $tutorUser->id,
            'nama_lengkap' => 'Tutor Clockout Test',
            'email' => 'tutor_co@pkbmpikat.com',
            'nik' => 'NIKCO01',
        ]);

        // Presensi mulai jam 08:00 (2 jam lalu dari jam 10:00)
        Presensi::create([
            'tutor_id' => $tutor->id,
            'tgl_presensi' => $today,
            'jam_mulai' => '08:00:00',
            'foto_mulai' => 'uploads/test_mulai.jpg',
            'lokasi_mulai' => '-7.8011945,110.364917',
            'status' => 'hadir',
        ]);

        $mockWebPush = $this->createMock(WebPushService::class);
        $mockWebPush->expects($this->atLeastOnce())
            ->method('sendToUser')
            ->willReturn(1);
        $this->app->instance(WebPushService::class, $mockWebPush);

        $this->artisan('presensi:send-reminder', ['--type' => 'clockout', '--role' => 'all'])
            ->assertExitCode(0);
    }

    public function test_send_absen_reminder_pending_approvals_sends_digest_to_kepsek(): void
    {
        $kepsekUser = User::factory()->create(['role' => 'kepala_sekolah', 'is_active' => 1]);
        $tutorUser = User::factory()->create(['role' => 'tutor', 'is_active' => 1]);
        $tutor = Tutor::create([
            'user_id' => $tutorUser->id,
            'nama_lengkap' => 'Tutor Izin Test',
            'email' => 'tutor_iz@pkbmpikat.com',
            'nik' => 'NIKIZ01',
        ]);

        $today = Carbon::today('Asia/Jakarta')->toDateString();

        // Buat pengajuan izin status pending
        PengajuanIzinSakit::create([
            'tutor_id' => $tutor->id,
            'tgl_mulai' => $today,
            'tgl_selesai' => $today,
            'status' => 'pending',
            'alasan' => 'Sakit demam',
        ]);

        $mockWebPush = $this->createMock(WebPushService::class);
        $mockWebPush->expects($this->once())
            ->method('sendToKepsek')
            ->willReturn(1);
        $this->app->instance(WebPushService::class, $mockWebPush);

        $this->artisan('presensi:send-reminder', ['--type' => 'pending-approvals'])
            ->assertExitCode(0);
    }

    public function test_tutor_scheduling_session_triggers_push_notification_to_student(): void
    {
        $tutorUser = User::factory()->create(['role' => 'tutor', 'is_active' => 1]);
        $tutor = Tutor::create([
            'user_id' => $tutorUser->id,
            'nama_lengkap' => 'Tutor Notif Test',
            'email' => 'tutor_notif@pkbmpikat.com',
            'nik' => 'NIKNOTIF01',
        ]);

        $siswaUser = User::factory()->create(['role' => 'siswa', 'is_active' => 1]);
        $jp = JenjangPaket::firstOrCreate(['kode' => 'paket_c_notif'], ['nama_jenjang' => 'Paket C Notif', 'status' => 'aktif']);
        $kls = Kelas::firstOrCreate(['nama_kelas' => 'Kelas 10 Paket C Notif'], ['jenjang_paket_id' => $jp->id, 'tingkat' => '10']);
        $siswa = Siswa::create([
            'user_id' => $siswaUser->id,
            'no_absen' => 'SWNOTIF01',
            'nama_siswa' => 'Siswa Notif Test',
            'nama_wali' => 'Wali Notif',
            'no_hp' => '081234567899',
            'kelas_id' => $kls->id,
            'status_siswa' => 'aktif',
            'is_abk' => false,
        ]);

        $mockWebPush = $this->createMock(WebPushService::class);
        $mockWebPush->expects($this->once())
            ->method('sendToUser')
            ->with($this->callback(function ($targetUser) use ($siswaUser) {
                return $targetUser->id === $siswaUser->id;
            }), $this->callback(function ($payload) {
                return str_contains($payload['title'], 'Jadwal Belajar Baru Terdaftar');
            }))
            ->willReturn(1);
        $this->app->instance(WebPushService::class, $mockWebPush);

        $this->actingAs($tutorUser);

        $response = $this->post(route('tutor.jadwal-sesi.store'), [
            'siswa_id' => $siswa->id,
            'tanggal_rencana' => Carbon::tomorrow('Asia/Jakarta')->toDateString(),
            'jam_masuk_rencana' => '10:00',
            'jam_pulang_rencana' => '12:00',
            'jenis_sesi' => 'reguler',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_student_checkin_triggers_push_notification_to_assigned_tutor(): void
    {
        $today = Carbon::today('Asia/Jakarta')->toDateString();

        $lokasi = LokasiPresensi::firstOrCreate(['nama_lokasi' => 'Sekolah Test Multi'], [
            'latitude' => -7.8011945,
            'longitude' => 110.364917,
            'radius_meter' => 200,
            'status' => 'aktif',
        ]);

        $tutorUser = User::factory()->create(['role' => 'tutor', 'is_active' => 1]);
        $tutor = Tutor::create([
            'user_id' => $tutorUser->id,
            'nama_lengkap' => 'Tutor Target Alert',
            'email' => 'tutor_target@pkbmpikat.com',
            'nik' => 'NIKTAR01',
        ]);

        $siswaUser = User::factory()->create(['role' => 'siswa', 'is_active' => 1]);
        $jp = JenjangPaket::firstOrCreate(['kode' => 'paket_a_tar'], ['nama_jenjang' => 'Paket A Target', 'status' => 'aktif']);
        $kls = Kelas::firstOrCreate(['nama_kelas' => 'Kelas 4 Paket A Tar'], ['jenjang_paket_id' => $jp->id, 'tingkat' => '4']);
        $siswa = Siswa::create([
            'user_id' => $siswaUser->id,
            'no_absen' => 'SWTAR01',
            'nama_siswa' => 'Siswa Target Alert',
            'nama_wali' => 'Wali Target',
            'no_hp' => '081234567888',
            'kelas_id' => $kls->id,
            'status_siswa' => 'aktif',
            'is_abk' => false,
        ]);

        // Ada sesi terjadwal hari ini
        JadwalSesi::create([
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswa->id,
            'tanggal_rencana' => $today,
            'jam_masuk_rencana' => '09:00:00',
            'jam_pulang_rencana' => '11:00:00',
            'durasi_jam' => 2.0,
            'jenis_sesi' => 'reguler',
            'status' => 'terjadwal',
            'status_kehadiran_siswa' => 'belum_presensi',
        ]);

        $mockWebPush = $this->createMock(WebPushService::class);
        // Expect minimal 2 sends: 1 konfirmasi ke Siswa, 1 alert ke Tutor bimbingan
        $mockWebPush->expects($this->atLeast(2))
            ->method('sendToUser')
            ->willReturn(1);
        $this->app->instance(WebPushService::class, $mockWebPush);

        $this->actingAs($siswaUser);

        $foto = UploadedFile::fake()->image('selfie_student.jpg', 600, 600);

        $response = $this->post(route('siswa.presensi.store'), [
            'foto' => $foto,
            'lokasi_presensi_id' => $lokasi->id,
            'lokasi' => '-7.8011945,110.364917',
            'lokasi_akurasi' => 15,
            'is_mocked' => '0',
        ]);

        $response->assertRedirect(route('siswa.dashboard'));
        $response->assertSessionHas('success');
    }
}
