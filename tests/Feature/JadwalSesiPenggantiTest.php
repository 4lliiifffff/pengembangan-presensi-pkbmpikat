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

        $this->assertDatabaseHas('jadwal_sesis', [
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
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

        $this->assertDatabaseMissing('jadwal_sesis', ['id' => $sesi->id]);
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

        $this->assertDatabaseHas('presensis', [
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'jadwal_sesi_id' => $sesi->id,
            'status' => 'hadir',
        ]);

        $this->assertDatabaseHas('jadwal_sesis', [
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

        $this->assertDatabaseHas('jadwal_sesis', [
            'id' => $sesi->id,
            'status' => 'selesai',
        ]);

        Carbon::setTestNow();
    }
}
