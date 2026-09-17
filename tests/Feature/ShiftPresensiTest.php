<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\kelas;
use App\Models\Magang;
use App\Models\Presensi;
use App\Models\PresensiKaryawan;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use App\Services\ShiftPresensiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShiftPresensiTest extends TestCase
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

    public function test_shift_service_evaluates_exact_and_tolerance_times(): void
    {
        $service = new ShiftPresensiService;

        // 1. Lebih awal: 07:15 WIB (Batas awal 07:30 untuk shift 08:00)
        $timeEarly = Carbon::parse('2026-09-17 07:15:00', 'Asia/Jakarta');
        $evalEarly = $service->evaluateCheckIn($timeEarly, 'pagi');
        $this->assertEquals('lebih_awal', $evalEarly['status_kehadiran']);
        $this->assertEquals(0, $evalEarly['menit_keterlambatan']);
        $this->assertFalse($evalEarly['is_terlambat']);

        // 2. Tepat waktu (sebelum jam shift): 07:45 WIB
        $timeOnTimeBefore = Carbon::parse('2026-09-17 07:45:00', 'Asia/Jakarta');
        $evalOnTimeBefore = $service->evaluateCheckIn($timeOnTimeBefore, 'pagi');
        $this->assertEquals('tepat_waktu', $evalOnTimeBefore['status_kehadiran']);
        $this->assertEquals(0, $evalOnTimeBefore['menit_keterlambatan']);

        // 3. Tepat waktu dalam masa toleransi 30 menit: 08:20 WIB
        $timeTolerance = Carbon::parse('2026-09-17 08:20:00', 'Asia/Jakarta');
        $evalTolerance = $service->evaluateCheckIn($timeTolerance, 'pagi');
        $this->assertEquals('tepat_waktu', $evalTolerance['status_kehadiran']);
        $this->assertEquals(0, $evalTolerance['menit_keterlambatan']);

        // 4. Terlambat: 08:45 WIB (> 08:30 batas toleransi) -> Terlambat 45 menit dari 08:00
        $timeLate = Carbon::parse('2026-09-17 08:45:00', 'Asia/Jakarta');
        $evalLate = $service->evaluateCheckIn($timeLate, 'pagi');
        $this->assertEquals('terlambat', $evalLate['status_kehadiran']);
        $this->assertTrue($evalLate['is_terlambat']);
        $this->assertEquals(45, $evalLate['menit_keterlambatan']);
    }

    public function test_karyawan_presensi_records_shift_and_status(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Admin::create([
            'user_id' => $user->id,
            'nik' => $user->nik,
            'nama_admin' => 'Admin Shift Test',
            'nama_lengkap' => 'Admin Shift Test',
            'email' => $user->email,
        ]);

        $this->actingAs($user);

        $file = UploadedFile::fake()->image('selfie.jpg');

        $response = $this->post(route('admin.presensi.store'), [
            'mode' => 'mulai',
            'foto' => $file,
            'lokasi' => '-7.8011945,110.364917',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('presensi_karyawans', [
            'user_id' => $user->id,
            'status' => 'hadir',
        ]);

        $presensi = PresensiKaryawan::where('user_id', $user->id)->first();
        $this->assertNotNull($presensi->shift_nama);
        $this->assertNotNull($presensi->status_kehadiran);
    }

    public function test_magang_presensi_records_shift_and_status(): void
    {
        $user = User::factory()->create(['role' => 'magang']);
        Magang::create([
            'user_id' => $user->id,
            'asal_instansi' => 'Universitas Test',
            'tgl_mulai' => Carbon::now()->subMonth()->toDateString(),
            'tgl_selesai' => Carbon::now()->addMonths(2)->toDateString(),
            'status' => 'aktif',
        ]);

        $this->actingAs($user);

        $file = UploadedFile::fake()->image('selfie_magang.jpg');

        $response = $this->post(route('magang.presensi.store'), [
            'mode' => 'mulai',
            'foto' => $file,
            'lokasi' => '-7.8011945,110.364917',
        ]);

        $response->assertRedirect(route('magang.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('presensi_karyawans', [
            'user_id' => $user->id,
            'status' => 'hadir',
        ]);

        $presensi = PresensiKaryawan::where('user_id', $user->id)->first();
        $this->assertNotNull($presensi->shift_nama);
        $this->assertNotNull($presensi->status_kehadiran);
    }

    public function test_tutor_presensi_records_shift_and_status(): void
    {
        $user = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $user->id,
            'nik' => $user->nik,
            'nama_tutor' => 'Tutor Shift Test',
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'no_hp' => '081234567890',
        ]);

        $kelas = kelas::create(['nama_kelas' => 'Kelas A', 'tingkat' => 'SD']);
        $siswa = Siswa::create([
            'nama_siswa' => 'Siswa Shift Test',
            'no_absen' => 1,
            'nis' => '12345',
            'no_hp' => '081234567891',
            'nama_wali' => 'Wali Murid Test',
            'kelas_id' => $kelas->id,
            'tutor_id' => $tutor->id,
            'status_siswa' => 'aktif',
        ]);

        $this->actingAs($user);

        $file = UploadedFile::fake()->image('selfie_tutor.jpg');

        $response = $this->post(route('tutor.presensi.store'), [
            'siswa_id' => [$siswa->id],
            'mode' => 'mulai',
            'moda_pembelajaran' => 'online',
            'foto' => $file,
        ]);

        $response->assertRedirect(route('tutor.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('presensis', [
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswa->id,
            'status' => 'hadir',
        ]);

        $presensi = Presensi::where('tutor_id', $tutor->id)->where('siswa_id', $siswa->id)->first();
        $this->assertNotNull($presensi->shift_nama);
        $this->assertNotNull($presensi->status_kehadiran);
    }
}
