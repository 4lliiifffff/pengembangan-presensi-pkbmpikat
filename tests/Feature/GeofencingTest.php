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

class GeofencingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_haversine_distance_calculation(): void
    {
        $service = new GeofencingService;

        // Titik PKBM Pikat: -7.8011945, 110.364917
        $lat1 = -7.8011945;
        $lng1 = 110.364917;

        // Titik sama (jarak 0m)
        $distanceSame = $service->calculateDistance($lat1, $lng1, $lat1, $lng1);
        $this->assertEquals(0.0, round($distanceSame, 2));

        // Titik yang berjarak ~50 meter
        $lat2 = -7.8015500;
        $lng2 = 110.364917;
        $distance50m = $service->calculateDistance($lat1, $lng1, $lat2, $lng2);

        $this->assertGreaterThan(30.0, $distance50m);
        $this->assertLessThan(70.0, $distance50m);
    }

    public function test_presensi_sekolah_valid_within_radius(): void
    {
        $user = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $user->id,
            'nik' => $user->nik,
            'nama_tutor' => 'Tutor Geofence',
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'no_hp' => '081234567890',
        ]);
        $kelas = kelas::create(['nama_kelas' => 'Kelas A', 'tingkat' => 'PAUD']);
        $siswa = Siswa::create([
            'nama_siswa' => 'Siswa Geofence',
            'tutor_id' => $tutor->id,
            'kelas_id' => $kelas->id,
            'no_absen' => '001',
            'no_hp' => '081234567890',
            'nama_wali' => 'Wali Geofence',
        ]);

        // Titik sekolah PKBM Pikat: -7.8011945, 110.364917
        // Koordinat tutor ~20m dari sekolah
        $lokasiValid = '-7.801300,110.364917';

        $response = $this->actingAs($user)->post(route('tutor.presensi.store'), [
            'siswa_id' => [$siswa->id],
            'mode' => 'mulai',
            'moda_pembelajaran' => 'sekolah',
            'lokasi' => $lokasiValid,
            'foto' => UploadedFile::fake()->image('masuk.jpg'),
        ]);

        $response->assertRedirect(route('tutor.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('presensis', [
            'siswa_id' => $siswa->id,
            'tutor_id' => $tutor->id,
            'moda_pembelajaran' => 'sekolah',
            'lokasi_mulai' => $lokasiValid,
        ]);
    }

    public function test_presensi_sekolah_rejected_outside_radius(): void
    {
        $user = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $user->id,
            'nik' => $user->nik,
            'nama_tutor' => 'Tutor Geofence',
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'no_hp' => '081234567890',
        ]);
        $kelas = kelas::create(['nama_kelas' => 'Kelas B', 'tingkat' => 'SD']);
        $siswa = Siswa::create([
            'nama_siswa' => 'Siswa Geofence',
            'tutor_id' => $tutor->id,
            'kelas_id' => $kelas->id,
            'no_absen' => '002',
            'no_hp' => '081234567890',
            'nama_wali' => 'Wali Geofence',
        ]);

        // Koordinat tutor ~500m dari sekolah (misal: Malioboro / lokasi lain)
        $lokasiJauh = '-7.792592,110.365844';

        $response = $this->actingAs($user)->post(route('tutor.presensi.store'), [
            'siswa_id' => [$siswa->id],
            'mode' => 'mulai',
            'moda_pembelajaran' => 'sekolah',
            'lokasi' => $lokasiJauh,
            'foto' => UploadedFile::fake()->image('masuk.jpg'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning');

        $warning = session('warning');
        $this->assertStringContainsString('di luar radius sekolah PKBM Pikat', $warning);

        $this->assertDatabaseMissing('presensis', [
            'siswa_id' => $siswa->id,
            'tutor_id' => $tutor->id,
        ]);
    }

    public function test_presensi_online_bypasses_geofence_radius(): void
    {
        $user = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $user->id,
            'nik' => $user->nik,
            'nama_tutor' => 'Tutor Geofence',
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'no_hp' => '081234567890',
        ]);
        $kelas = kelas::create(['nama_kelas' => 'Kelas C', 'tingkat' => 'SMP']);
        $siswa = Siswa::create([
            'nama_siswa' => 'Siswa Geofence',
            'tutor_id' => $tutor->id,
            'kelas_id' => $kelas->id,
            'no_absen' => '003',
            'no_hp' => '081234567890',
            'nama_wali' => 'Wali Geofence',
        ]);

        // Koordinat tutor di luar radius (misal: di rumah / kota lain)
        $lokasiJauh = '-6.2088,106.8456'; // Jakarta

        $response = $this->actingAs($user)->post(route('tutor.presensi.store'), [
            'siswa_id' => [$siswa->id],
            'mode' => 'mulai',
            'moda_pembelajaran' => 'online',
            'link_daring' => 'https://meet.google.com/abc-defg-hij',
            'lokasi' => $lokasiJauh,
            'foto' => UploadedFile::fake()->image('masuk.jpg'),
        ]);

        $response->assertRedirect(route('tutor.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('presensis', [
            'siswa_id' => $siswa->id,
            'tutor_id' => $tutor->id,
            'moda_pembelajaran' => 'online',
            'link_daring' => 'https://meet.google.com/abc-defg-hij',
        ]);
    }
}
