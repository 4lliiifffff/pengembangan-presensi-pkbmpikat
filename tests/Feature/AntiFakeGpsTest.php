<?php

namespace Tests\Feature;

use App\Models\kelas;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use App\Services\GeofencingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AntiFakeGpsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_service_validates_gps_integrity(): void
    {
        $service = new GeofencingService;

        // Valid GPS
        $valid = $service->validateGpsIntegrity('-7.8011945,110.364917', 15.0, false, 200);
        $this->assertTrue($valid['is_valid']);
        $this->assertNull($valid['message']);

        // Mocked GPS
        $mocked = $service->validateGpsIntegrity('-7.8011945,110.364917', 15.0, true, 200);
        $this->assertFalse($mocked['is_valid']);
        $this->assertStringContainsString('Fake GPS', $mocked['message']);

        // Poor Accuracy (e.g. 500m > 200m limit)
        $poorAcc = $service->validateGpsIntegrity('-7.8011945,110.364917', 500.0, false, 200);
        $this->assertFalse($poorAcc['is_valid']);
        $this->assertStringContainsString('terlalu rendah', $poorAcc['message']);

        // Zero Accuracy
        $zeroAcc = $service->validateGpsIntegrity('-7.8011945,110.364917', 0.0, false, 200);
        $this->assertFalse($zeroAcc['is_valid']);
        $this->assertStringContainsString('0 meter', $zeroAcc['message']);
    }

    public function test_presensi_with_valid_gps_accuracy_succeeds(): void
    {
        $user = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $user->id,
            'nik' => $user->nik,
            'nama_tutor' => 'Tutor AntiFake',
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'no_hp' => '081234567890',
        ]);
        $kelas = kelas::create(['nama_kelas' => 'Kelas AntiFake', 'tingkat' => 'SMA']);
        $siswa = Siswa::create([
            'nama_siswa' => 'Siswa AntiFake',
            'tutor_id' => $tutor->id,
            'kelas_id' => $kelas->id,
            'no_absen' => '010',
            'no_hp' => '081234567890',
            'nama_wali' => 'Wali AntiFake',
        ]);

        $lat = config('lokasi.sekolah_lat', -7.8011945);
        $lng = config('lokasi.sekolah_lng', 110.364917);

        $response = $this->actingAs($user)->post(route('tutor.presensi.store'), [
            'siswa_id' => [$siswa->id],
            'mode' => 'mulai',
            'moda_pembelajaran' => 'sekolah',
            'lokasi' => "{$lat},{$lng}",
            'lokasi_akurasi' => 12.5,
            'is_mock_location' => 0,
            'foto' => UploadedFile::fake()->image('masuk.jpg'),
        ]);

        $response->assertRedirect(route('tutor.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('presensis', [
            'siswa_id' => $siswa->id,
            'tutor_id' => $tutor->id,
            'lokasi_akurasi' => 12.50,
            'is_mocked' => false,
        ]);
    }

    public function test_presensi_with_mock_location_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $user->id,
            'nik' => $user->nik,
            'nama_tutor' => 'Tutor AntiFake',
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'no_hp' => '081234567890',
        ]);
        $kelas = kelas::create(['nama_kelas' => 'Kelas AntiFake', 'tingkat' => 'SMA']);
        $siswa = Siswa::create([
            'nama_siswa' => 'Siswa AntiFake',
            'tutor_id' => $tutor->id,
            'kelas_id' => $kelas->id,
            'no_absen' => '011',
            'no_hp' => '081234567890',
            'nama_wali' => 'Wali AntiFake',
        ]);

        $lat = config('lokasi.sekolah_lat', -7.8011945);
        $lng = config('lokasi.sekolah_lng', 110.364917);

        $response = $this->actingAs($user)->post(route('tutor.presensi.store'), [
            'siswa_id' => [$siswa->id],
            'mode' => 'mulai',
            'moda_pembelajaran' => 'sekolah',
            'lokasi' => "{$lat},{$lng}",
            'lokasi_akurasi' => 10.0,
            'is_mock_location' => 1, // Simulated Fake GPS detected
            'foto' => UploadedFile::fake()->image('masuk.jpg'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning');

        $warning = session('warning');
        $this->assertStringContainsString('Fake GPS', $warning);

        $this->assertDatabaseMissing('presensis', [
            'siswa_id' => $siswa->id,
            'tutor_id' => $tutor->id,
        ]);
    }

    public function test_presensi_with_poor_gps_accuracy_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $user->id,
            'nik' => $user->nik,
            'nama_tutor' => 'Tutor AntiFake',
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'no_hp' => '081234567890',
        ]);
        $kelas = kelas::create(['nama_kelas' => 'Kelas AntiFake', 'tingkat' => 'SMA']);
        $siswa = Siswa::create([
            'nama_siswa' => 'Siswa AntiFake',
            'tutor_id' => $tutor->id,
            'kelas_id' => $kelas->id,
            'no_absen' => '012',
            'no_hp' => '081234567890',
            'nama_wali' => 'Wali AntiFake',
        ]);

        $lat = config('lokasi.sekolah_lat', -7.8011945);
        $lng = config('lokasi.sekolah_lng', 110.364917);

        $response = $this->actingAs($user)->post(route('tutor.presensi.store'), [
            'siswa_id' => [$siswa->id],
            'mode' => 'mulai',
            'moda_pembelajaran' => 'sekolah',
            'lokasi' => "{$lat},{$lng}",
            'lokasi_akurasi' => 850.0, // 850m > 200m limit
            'is_mock_location' => 0,
            'foto' => UploadedFile::fake()->image('masuk.jpg'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning');

        $warning = session('warning');
        $this->assertStringContainsString('terlalu rendah', $warning);

        $this->assertDatabaseMissing('presensis', [
            'siswa_id' => $siswa->id,
            'tutor_id' => $tutor->id,
        ]);
    }
}
