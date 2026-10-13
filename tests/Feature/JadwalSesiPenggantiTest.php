<?php

namespace Tests\Feature;

use App\Models\JadwalSesi;
use App\Models\KategoriTutorial;
use App\Models\kelas;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use App\Services\ShiftPresensiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JadwalSesiPenggantiTest extends TestCase
{
    use RefreshDatabase;

    protected User $tutorUser;

    protected Tutor $tutor;

    protected Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->tutorUser = User::factory()->create([
            'role' => 'tutor',
            'is_active' => true,
        ]);

        $this->tutor = Tutor::create([
            'user_id' => $this->tutorUser->id,
            'nik' => $this->tutorUser->nik,
            'nama_tutor' => 'Tutor Test',
            'nama_lengkap' => $this->tutorUser->nama_lengkap,
            'email' => $this->tutorUser->email,
            'no_hp' => '081234567890',
        ]);

        $kelas = kelas::create(['nama_kelas' => 'Paket C Rombel 1', 'tingkat' => 'SMA']);

        $this->siswa = Siswa::create([
            'nama_siswa' => 'Budi Siswa Test',
            'no_absen' => '01',
            'no_hp' => '081234567890',
            'nama_wali' => 'Ibu Budi',
            'status_siswa' => 'aktif',
            'kelas_id' => $kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);
    }

    public function test_tutor_can_view_jadwal_sesi_index(): void
    {
        $response = $this->actingAs($this->tutorUser)->get(route('tutor.jadwal-sesi.index'));
        $response->assertOk();
        $response->assertSee('Jadwal Sesi & Pengganti');
    }

    public function test_tutor_can_store_new_jadwal_sesi_pengganti(): void
    {
        $kategori = KategoriTutorial::create([
            'kode_kategori' => 'KAT_TEST',
            'nama_kategori' => 'Tutorial Komunitas 2 Jam',
            'honor_per_jam' => 50000,
            'durasi_jam' => 2.00,
        ]);

        $payload = [
            'siswa_id' => $this->siswa->id,
            'kategori_tutorial_id' => $kategori->id,
            'tanggal_rencana' => now()->addDay()->toDateString(),
            'jam_masuk_rencana' => '14:00',
            'jam_pulang_rencana' => '16:00',
            'durasi_jam' => 2.00,
            'jenis_sesi' => 'pengganti',
            'tanggal_asli' => now()->subDay()->toDateString(),
            'alasan_penggantian' => 'Permintaan wali murid karena acara keluarga',
            'catatan' => 'Materi Bab 3',
        ];

        $response = $this->actingAs($this->tutorUser)->post(route('tutor.jadwal-sesi.store'), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_sesi', [
            'tutor_id' => $this->tutor->id,
            'murid_id' => $this->siswa->id,
            'jenis_sesi' => 'pengganti',
            'jam_masuk_rencana' => '14:00:00',
            'status' => 'terjadwal',
        ]);
    }

    public function test_tutor_can_cancel_scheduled_sesi(): void
    {
        $sesi = JadwalSesi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'tanggal_rencana' => now()->toDateString(),
            'jam_masuk_rencana' => '15:00:00',
            'jam_pulang_rencana' => '17:00:00',
            'durasi_jam' => 2.00,
            'jenis_sesi' => 'pengganti',
            'status' => 'terjadwal',
        ]);

        $response = $this->actingAs($this->tutorUser)->delete(route('tutor.jadwal-sesi.destroy', $sesi));
        $response->assertRedirect();

        $this->assertDatabaseMissing('jadwal_sesi', ['id' => $sesi->id]);
    }

    public function test_shift_presensi_service_evaluates_against_jadwal_sesi_time(): void
    {
        $sesi = JadwalSesi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'tanggal_rencana' => now()->toDateString(),
            'jam_masuk_rencana' => '14:00:00',
            'jam_pulang_rencana' => '16:00:00',
            'durasi_jam' => 2.00,
            'jenis_sesi' => 'pengganti',
            'status' => 'terjadwal',
        ]);

        $service = new ShiftPresensiService;

        // Check in at 13:50 (10 minutes before 14:00 make-up class -> ON TIME)
        $eval = $service->evaluateCheckIn(
            time: Carbon::parse(now()->toDateString().' 13:50:00', 'Asia/Jakarta'),
            jadwalSesi: $sesi
        );

        $this->assertEquals('tepat_waktu', $eval['status_kehadiran']);
        $this->assertEquals(0, $eval['menit_keterlambatan']);
        $this->assertEquals('14:00', $eval['jam_masuk_target']);
        $this->assertEquals($sesi->id, $eval['jadwal_sesi_id']);
    }

    public function test_presensi_masuk_auto_links_and_completes_jadwal_sesi(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-18 14:00:00', 'Asia/Jakarta'));

        $sesi = JadwalSesi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'tanggal_rencana' => '2026-09-18',
            'jam_masuk_rencana' => '14:00:00',
            'jam_pulang_rencana' => '16:00:00',
            'durasi_jam' => 2.00,
            'jenis_sesi' => 'pengganti',
            'status' => 'terjadwal',
        ]);

        $photo = UploadedFile::fake()->image('selfie.jpg');

        $payload = [
            'mode' => 'mulai',
            'siswa_id' => [$this->siswa->id],
            'moda_pembelajaran' => 'kunjungan_rumah',
            'foto' => $photo,
            'lokasi' => '-7.8011945,110.364917',
        ];

        $response = $this->actingAs($this->tutorUser)->post(route('tutor.presensi.store'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('presensi', [
            'tutor_id' => $this->tutor->id,
            'murid_id' => $this->siswa->id,
            'jadwal_sesi_id' => $sesi->id,
            'status' => 'hadir',
        ]);

        $this->assertDatabaseHas('jadwal_sesi', [
            'id' => $sesi->id,
            'status' => 'berlangsung',
        ]);

        // Saat tutor absen pulang setelah minimal 1 jam (mode = selesai) pada hari yang sama
        Carbon::setTestNow(Carbon::parse('2026-09-18 16:05:00', 'Asia/Jakarta'));

        $pulangPayload = [
            'mode' => 'selesai',
            'siswa_id' => [$this->siswa->id],
            'foto' => UploadedFile::fake()->image('selfie_pulang.jpg'),
            'lokasi' => '-7.8011945,110.364917',
        ];

        $pulangResponse = $this->actingAs($this->tutorUser)->post(route('tutor.presensi.store'), $pulangPayload);
        $pulangResponse->assertRedirect();

        $this->assertDatabaseHas('jadwal_sesi', [
            'id' => $sesi->id,
            'status' => 'selesai',
        ]);

        Carbon::setTestNow();
    }

    public function test_tutor_can_reschedule_sesi_on_the_same_day_and_it_overwrites_session(): void
    {
        $todayStr = now()->toDateString();
        $sesi = JadwalSesi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'murid_id' => $this->siswa->id,
            'tanggal_rencana' => $todayStr,
            'jam_masuk_rencana' => '08:00:00',
            'jam_pulang_rencana' => '10:00:00',
            'durasi_jam' => 2.00,
            'jenis_sesi' => 'reguler',
            'status' => 'terjadwal',
        ]);

        $payload = [
            'tanggal_baru' => $todayStr,
            'jam_masuk_baru' => '13:00',
            'jam_pulang_baru' => '15:00',
            'alasan_penggantian' => 'Penyesuaian jam belajar murid',
        ];

        $response = $this->actingAs($this->tutorUser)->post(route('tutor.jadwal-sesi.reschedule', $sesi), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Jadwal sesi berhasil diperbarui ke jam baru pada hari yang sama.');

        // Harus tetap 1 sesi (jadwal lama tertimpa, tidak menumpuk 2 sesi)
        $this->assertEquals(1, JadwalSesi::where('murid_id', $this->siswa->id)->count());

        $sesi->refresh();
        $this->assertEquals('13:00', substr((string) $sesi->jam_masuk_rencana, 0, 5));
        $this->assertEquals('15:00', substr((string) $sesi->jam_pulang_rencana, 0, 5));
        $this->assertEquals('terjadwal', $sesi->status);
        $this->assertEquals('pengganti', $sesi->jenis_sesi);
        $this->assertEquals('Penyesuaian jam belajar murid', $sesi->alasan_penggantian);
    }

    public function test_tutor_reschedule_to_different_day_cancels_old_and_creates_new(): void
    {
        $todayStr = now()->toDateString();
        $nextDayStr = now()->addDays(2)->toDateString();

        $sesi = JadwalSesi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'murid_id' => $this->siswa->id,
            'tanggal_rencana' => $todayStr,
            'jam_masuk_rencana' => '08:00:00',
            'jam_pulang_rencana' => '10:00:00',
            'durasi_jam' => 2.00,
            'jenis_sesi' => 'reguler',
            'status' => 'terjadwal',
        ]);

        $payload = [
            'tanggal_baru' => $nextDayStr,
            'jam_masuk_baru' => '14:00',
            'jam_pulang_baru' => '16:00',
            'alasan_penggantian' => 'Murid ada acara keluarga',
        ];

        $response = $this->actingAs($this->tutorUser)->post(route('tutor.jadwal-sesi.reschedule', $sesi), $payload);
        $response->assertRedirect();

        $sesi->refresh();
        $this->assertEquals('dibatalkan', $sesi->status);

        $newSesi = JadwalSesi::where('tanggal_rencana', $nextDayStr)->first();
        $this->assertNotNull($newSesi);
        $this->assertEquals('terjadwal', $newSesi->status);
        $this->assertEquals('pengganti', $newSesi->jenis_sesi);
        $this->assertEquals('14:00', substr((string) $newSesi->jam_masuk_rencana, 0, 5));
    }

    public function test_tutor_can_store_jadwal_for_multiple_murid_single_session(): void
    {
        $kelas = kelas::first();
        $siswa2 = Siswa::create([
            'nama_siswa' => 'Siti Siswa Test',
            'no_absen' => '02',
            'no_hp' => '081234567891',
            'nama_wali' => 'Ibu Siti',
            'status_siswa' => 'aktif',
            'kelas_id' => $kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);

        $payload = [
            'is_recurring' => '0',
            'siswa_ids' => [$this->siswa->id, $siswa2->id],
            'jenis_sesi' => 'reguler',
            'tanggal_rencana' => now()->addDays(1)->toDateString(),
            'jam_masuk_rencana' => '09:00',
            'jam_pulang_rencana' => '11:00',
        ];

        $response = $this->actingAs($this->tutorUser)->post(route('tutor.jadwal-sesi.store'), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_sesi', [
            'tutor_id' => $this->tutor->id,
            'murid_id' => $this->siswa->id,
            'status' => 'terjadwal',
        ]);

        $this->assertDatabaseHas('jadwal_sesi', [
            'tutor_id' => $this->tutor->id,
            'murid_id' => $siswa2->id,
            'status' => 'terjadwal',
        ]);
    }

    public function test_tutor_can_store_jadwal_for_multiple_murid_recurring_weekly(): void
    {
        $kelas = kelas::first();
        $siswa2 = Siswa::create([
            'nama_siswa' => 'Dewi Siswa Test',
            'no_absen' => '03',
            'no_hp' => '081234567892',
            'nama_wali' => 'Ibu Dewi',
            'status_siswa' => 'aktif',
            'kelas_id' => $kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);

        $payload = [
            'is_recurring' => '1',
            'siswa_ids' => [$this->siswa->id, $siswa2->id],
            'hari' => 'selasa',
            'jam_masuk' => '13:00',
            'jam_pulang' => '15:00',
            'berlaku_mulai' => now()->toDateString(),
            'auto_generate' => '1',
        ];

        $response = $this->actingAs($this->tutorUser)->post(route('tutor.jadwal-sesi.store'), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_rutin', [
            'tutor_id' => $this->tutor->id,
            'murid_id' => $this->siswa->id,
            'hari' => 'selasa',
            'jam_masuk' => '13:00',
        ]);

        $this->assertDatabaseHas('jadwal_rutin', [
            'tutor_id' => $this->tutor->id,
            'murid_id' => $siswa2->id,
            'hari' => 'selasa',
            'jam_masuk' => '13:00',
        ]);
    }

    public function test_tutor_store_backward_compatible_with_single_siswa_id(): void
    {
        $payload = [
            'is_recurring' => '0',
            'siswa_id' => $this->siswa->id,
            'jenis_sesi' => 'reguler',
            'tanggal_rencana' => now()->addDays(3)->toDateString(),
            'jam_masuk_rencana' => '10:00',
            'jam_pulang_rencana' => '12:00',
        ];

        $response = $this->actingAs($this->tutorUser)->post(route('tutor.jadwal-sesi.store'), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_sesi', [
            'tutor_id' => $this->tutor->id,
            'murid_id' => $this->siswa->id,
            'tanggal_rencana' => now()->addDays(3)->toDateString(),
        ]);
    }

    public function test_admin_can_view_jadwal_sesi_kbm_monitoring_page(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($adminUser)->get(route('admin.jadwal-sesi-kbm.index'));
        $response->assertOk();
        $response->assertSee('Jadwal Sesi KBM Seluruh Tutor');
    }

    public function test_admin_can_reschedule_kbm_session_for_tutor_and_siswa(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $todayStr = now()->toDateString();
        $nextDayStr = now()->addDays(2)->toDateString();

        $sesi = JadwalSesi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'murid_id' => $this->siswa->id,
            'tanggal_rencana' => $todayStr,
            'jam_masuk_rencana' => '08:00:00',
            'jam_pulang_rencana' => '10:00:00',
            'durasi_jam' => 2.00,
            'jenis_sesi' => 'reguler',
            'status' => 'terjadwal',
        ]);

        $payload = [
            'tanggal_baru' => $nextDayStr,
            'jam_masuk_baru' => '15:00',
            'jam_pulang_baru' => '17:00',
            'alasan_penggantian' => 'Admin membantu penyesuaian jadwal atas permintaan orang tua',
        ];

        $response = $this->actingAs($adminUser)->post(route('admin.jadwal-sesi-kbm.reschedule', $sesi), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $sesi->refresh();
        $this->assertEquals('dibatalkan', $sesi->status);

        $newSesi = JadwalSesi::where('tanggal_rencana', $nextDayStr)->first();
        $this->assertNotNull($newSesi);
        $this->assertEquals('terjadwal', $newSesi->status);
        $this->assertEquals('pengganti', $newSesi->jenis_sesi);
        $this->assertEquals('15:00', substr((string) $newSesi->jam_masuk_rencana, 0, 5));
    }
}
