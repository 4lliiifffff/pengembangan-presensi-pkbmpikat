<?php

namespace Tests\Feature;

use App\Models\JadwalSesi;
use App\Models\JenjangPaket;
use App\Models\KategoriTutorial;
use App\Models\kelas as Kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use App\Services\ShiftPresensiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TutorCheckOutEligibilityTest extends TestCase
{
    use DatabaseTransactions;

    protected User $tutorUser;

    protected Tutor $tutor;

    protected User $siswaUser;

    protected Siswa $siswa;

    protected KategoriTutorial $kategori;

    protected ShiftPresensiService $shiftService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->shiftService = app(ShiftPresensiService::class);

        $this->tutorUser = User::factory()->create(['role' => 'tutor', 'is_active' => 1]);
        $this->tutor = Tutor::create([
            'user_id' => $this->tutorUser->id,
            'nama_lengkap' => 'Tutor Smart Checkout',
            'nama_tutor' => 'Tutor Smart Checkout',
            'email' => 'tutor_checkout@pkbmpikat.com',
            'nik' => 'NIKCKOUT01',
        ]);

        $this->siswaUser = User::factory()->create(['role' => 'siswa', 'is_active' => 1]);
        $jp = JenjangPaket::firstOrCreate(['kode' => 'paket_b_checkout'], ['nama_jenjang' => 'Paket B Checkout', 'status' => 'aktif']);
        $kls = Kelas::firstOrCreate(['nama_kelas' => 'Kelas 8 Paket B Checkout'], ['jenjang_paket_id' => $jp->id, 'tingkat' => '8']);
        $this->siswa = Siswa::create([
            'user_id' => $this->siswaUser->id,
            'nama_siswa' => 'Siswa Checkout Test',
            'no_absen' => 'CKO-01',
            'no_hp' => '081234567899',
            'is_abk' => false,
            'nama_wali' => 'Wali Checkout Test',
            'kelas_id' => $kls->id,
            'tutor_id' => $this->tutor->id,
            'status_siswa' => 'aktif',
        ]);

        $this->kategori = KategoriTutorial::firstOrCreate(
            ['nama_kategori' => 'Privat 2 Jam Checkout'],
            ['jenis_layanan' => 'privat', 'durasi_jam' => 2.0, 'nominal_honor' => 80000, 'is_aktif' => true]
        );
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_two_hour_session_check_out_unlocked_at_80_percent_duration(): void
    {
        // Sesi 10:00 - 12:00 (2 jam = 120 menit).
        // 80% durasi = 96 menit -> 11:36.
        // H-10 jadwal selesai = 11:50.
        // Earliest: 11:36 (alasan: durasi_terpenuhi).
        $jadwal = JadwalSesi::create([
            'siswa_id' => $this->siswa->id,
            'tutor_id' => $this->tutor->id,
            'kategori_tutorial_id' => $this->kategori->id,
            'tanggal_rencana' => '2026-10-10',
            'jam_masuk_rencana' => '10:00:00',
            'jam_pulang_rencana' => '12:00:00',
            'durasi_jam' => 2.0,
            'status' => 'terjadwal',
        ]);

        $presensi = Presensi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'jadwal_sesi_id' => $jadwal->id,
            'tgl_presensi' => '2026-10-10',
            'jam_mulai' => '10:00:00',
            'foto_mulai' => 'selfie_in.jpg',
            'status' => 'hadir',
            'durasi_jam' => 2.0,
            'durasi_pilihan' => 2.0,
        ]);

        // Cek pada jam 10:50 (50 menit berjalan -> belum boleh pulang)
        $now50m = Carbon::parse('2026-10-10 10:50:00', 'Asia/Jakarta');
        $result50m = $this->shiftService->calculateCheckOutEligibility($presensi, $now50m);
        $this->assertFalse($result50m['bisa_pulang']);
        $this->assertEquals(46, $result50m['sisa_menit']); // 11:36 - 10:50 = 46 menit
        $this->assertEquals('11:36', $result50m['target_waktu_buka']);

        // Cek pada jam 11:36 (96 menit berjalan -> sudah boleh pulang)
        $now96m = Carbon::parse('2026-10-10 11:36:00', 'Asia/Jakarta');
        $result96m = $this->shiftService->calculateCheckOutEligibility($presensi, $now96m);
        $this->assertTrue($result96m['bisa_pulang']);
        $this->assertEquals(0, $result96m['sisa_detik']);
        $this->assertEquals('11:36', $result96m['target_waktu_buka']);
    }

    public function test_late_check_in_unlocked_earlier_by_target_jadwal_selesai(): void
    {
        // Sesi 10:00 - 12:00 (2 jam).
        // Tutor telat absen masuk jam 10:20.
        // Target B (80% durasi = 96 menit dari 10:20): 11:56.
        // Target A (H-10 dari 12:00): 11:50.
        // Earliest: 11:50 (alasan: jadwal_selesai).
        $jadwal = JadwalSesi::create([
            'siswa_id' => $this->siswa->id,
            'tutor_id' => $this->tutor->id,
            'kategori_tutorial_id' => $this->kategori->id,
            'tanggal_rencana' => '2026-10-10',
            'jam_masuk_rencana' => '10:00:00',
            'jam_pulang_rencana' => '12:00:00',
            'durasi_jam' => 2.0,
            'status' => 'terjadwal',
        ]);

        $presensi = Presensi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'jadwal_sesi_id' => $jadwal->id,
            'tgl_presensi' => '2026-10-10',
            'jam_mulai' => '10:20:00',
            'foto_mulai' => 'selfie_in.jpg',
            'status' => 'hadir',
            'durasi_jam' => 2.0,
            'durasi_pilihan' => 2.0,
        ]);

        // Cek jam 11:45 -> masih kurang 5 menit dari 11:50
        $now1145 = Carbon::parse('2026-10-10 11:45:00', 'Asia/Jakarta');
        $res1145 = $this->shiftService->calculateCheckOutEligibility($presensi, $now1145);
        $this->assertFalse($res1145['bisa_pulang']);
        $this->assertEquals(5, $res1145['sisa_menit']);
        $this->assertEquals('jadwal_selesai', $res1145['alasan_buka']);
        $this->assertEquals('11:50', $res1145['target_waktu_buka']);

        // Cek jam 11:50 -> sudah memenuhi jadwal_selesai
        $now1150 = Carbon::parse('2026-10-10 11:50:00', 'Asia/Jakarta');
        $res1150 = $this->shiftService->calculateCheckOutEligibility($presensi, $now1150);
        $this->assertTrue($res1150['bisa_pulang']);
        $this->assertEquals('jadwal_selesai', $res1150['alasan_buka']);
    }

    public function test_one_hour_session_allows_clock_out_before_sixty_minutes(): void
    {
        // Sesi 1 jam: 10:00 - 11:00 (durasi 60 menit).
        // 80% durasi = 48 menit -> 10:48.
        // H-10 jadwal: 10:50.
        // Earliest: 10:48.
        // Pada aturan lama (3600 detik = 60 menit), tutor dipaksa menunggu hingga 11:00.
        // Dengan aturan baru, jam 10:48 sudah dibuka!
        $jadwal = JadwalSesi::create([
            'siswa_id' => $this->siswa->id,
            'tutor_id' => $this->tutor->id,
            'tanggal_rencana' => '2026-10-10',
            'jam_masuk_rencana' => '10:00:00',
            'jam_pulang_rencana' => '11:00:00',
            'durasi_jam' => 1.0,
            'status' => 'terjadwal',
        ]);

        $presensi = Presensi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'jadwal_sesi_id' => $jadwal->id,
            'tgl_presensi' => '2026-10-10',
            'jam_mulai' => '10:00:00',
            'foto_mulai' => 'selfie_in.jpg',
            'status' => 'hadir',
            'durasi_jam' => 1.0,
            'durasi_pilihan' => 1.0,
        ]);

        $now1048 = Carbon::parse('2026-10-10 10:48:00', 'Asia/Jakarta');
        $res1048 = $this->shiftService->calculateCheckOutEligibility($presensi, $now1048);
        $this->assertTrue($res1048['bisa_pulang']);
        $this->assertEquals('10:48', $res1048['target_waktu_buka']);
        $this->assertEquals(48, $res1048['menit_efektif_wajib']);
    }

    public function test_http_post_selesai_blocked_before_threshold_and_allowed_after(): void
    {
        $today = '2026-10-10';
        $jadwal = JadwalSesi::create([
            'siswa_id' => $this->siswa->id,
            'tutor_id' => $this->tutor->id,
            'tanggal_rencana' => $today,
            'jam_masuk_rencana' => '10:00:00',
            'jam_pulang_rencana' => '12:00:00',
            'durasi_jam' => 2.0,
            'status' => 'terjadwal',
        ]);

        $presensi = Presensi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'jadwal_sesi_id' => $jadwal->id,
            'tgl_presensi' => $today,
            'jam_mulai' => '10:00:00',
            'foto_mulai' => 'selfie_in.jpg',
            'moda_pembelajaran' => 'online',
            'status' => 'hadir',
            'durasi_jam' => 2.0,
            'durasi_pilihan' => 2.0,
        ]);

        $fotoOut = UploadedFile::fake()->image('selfie_out.jpg');

        // Jam 10:30 -> Baru 30 menit (ambang 96 menit / 11:36) -> DITOLAK
        Carbon::setTestNow(Carbon::parse("{$today} 10:30:00", 'Asia/Jakarta'));

        $resEarly = $this->actingAs($this->tutorUser)->post(route('tutor.presensi.store'), [
            'mode' => 'selesai',
            'siswa_id' => [$this->siswa->id],
            'presensi_id' => $presensi->id,
            'foto' => $fotoOut,
        ]);

        $resEarly->assertRedirect();
        $resEarly->assertSessionHas('warning');

        $presensi->refresh();
        $this->assertNull($presensi->foto_selesai);
        $this->assertNull($presensi->jam_selesai);

        // Jam 11:40 -> Sudah 100 menit (> 96 menit) -> DITERIMA
        Carbon::setTestNow(Carbon::parse("{$today} 11:40:00", 'Asia/Jakarta'));

        $resAllowed = $this->actingAs($this->tutorUser)->post(route('tutor.presensi.store'), [
            'mode' => 'selesai',
            'siswa_id' => [$this->siswa->id],
            'presensi_id' => $presensi->id,
            'foto' => $fotoOut,
        ]);

        $resAllowed->assertRedirect(route('tutor.dashboard'));
        $resAllowed->assertSessionHas('success');

        $presensi->refresh();
        $this->assertNotNull($presensi->foto_selesai);
        $this->assertNotNull($presensi->jam_selesai);
    }
}
