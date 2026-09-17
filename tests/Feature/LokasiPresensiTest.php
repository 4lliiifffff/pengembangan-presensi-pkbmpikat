<?php

namespace Tests\Feature;

use App\Models\kelas;
use App\Models\LokasiPresensi;
use App\Models\Magang;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LokasiPresensiTest extends TestCase
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

    public function test_admin_can_access_lokasi_presensi_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        LokasiPresensi::create([
            'nama_lokasi' => 'Kampus Cabang Sleman',
            'tipe' => 'cabang',
            'latitude' => -7.712345,
            'longitude' => 110.364917,
            'radius_meter' => 150,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.lokasi-presensi.index'));

        $response->assertStatus(200);
        $response->assertSee('Kampus Cabang Sleman');
        $response->assertSee('150 Meter');
    }

    public function test_admin_can_create_new_lokasi_presensi(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.lokasi-presensi.store'), [
            'nama_lokasi' => 'Sentra Belajar Bantul',
            'tipe' => 'cabang',
            'latitude' => -7.890123,
            'longitude' => 110.334455,
            'radius_meter' => 120,
            'alamat' => 'Jl. Jenderal Sudirman Bantul',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.lokasi-presensi.index'));
        $this->assertDatabaseHas('lokasi_presensis', [
            'nama_lokasi' => 'Sentra Belajar Bantul',
            'tipe' => 'cabang',
            'radius_meter' => 120,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_lokasi_presensi(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lokasi = LokasiPresensi::create([
            'nama_lokasi' => 'Lokasi Lama',
            'tipe' => 'pusat',
            'latitude' => -7.777777,
            'longitude' => 110.444444,
            'radius_meter' => 80,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.lokasi-presensi.update', $lokasi->id), [
            'nama_lokasi' => 'Lokasi Diperbarui',
            'tipe' => 'cabang',
            'latitude' => -7.777777,
            'longitude' => 110.444444,
            'radius_meter' => 200,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.lokasi-presensi.index'));
        $this->assertDatabaseHas('lokasi_presensis', [
            'id' => $lokasi->id,
            'nama_lokasi' => 'Lokasi Diperbarui',
            'tipe' => 'cabang',
            'radius_meter' => 200,
        ]);
    }

    public function test_admin_can_toggle_lokasi_presensi_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lokasi = LokasiPresensi::create([
            'nama_lokasi' => 'Pos PKL 1',
            'tipe' => 'mitra',
            'latitude' => -7.800000,
            'longitude' => 110.360000,
            'radius_meter' => 100,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.lokasi-presensi.index'))
            ->patch(route('admin.lokasi-presensi.toggleStatus', $lokasi->id));

        $response->assertRedirect(route('admin.lokasi-presensi.index'));
        $this->assertDatabaseHas('lokasi_presensis', [
            'id' => $lokasi->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_lokasi_presensi(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lokasi = LokasiPresensi::create([
            'nama_lokasi' => 'Lokasi Sementara',
            'tipe' => 'lainnya',
            'latitude' => -7.800000,
            'longitude' => 110.360000,
            'radius_meter' => 100,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.lokasi-presensi.destroy', $lokasi->id));

        $response->assertRedirect(route('admin.lokasi-presensi.index'));
        $this->assertDatabaseMissing('lokasi_presensis', [
            'id' => $lokasi->id,
        ]);
    }

    public function test_non_admin_cannot_access_lokasi_presensi_management(): void
    {
        $tutorUser = User::factory()->create(['role' => 'tutor']);
        $response = $this->actingAs($tutorUser)->get(route('admin.lokasi-presensi.index'));

        $response->assertRedirect(route('tutor.dashboard'));
    }

    public function test_tutor_must_check_in_within_selected_location_radius(): void
    {
        $tutorUser = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $tutorUser->id,
            'nama_lengkap' => 'Tutor Test',
            'nik' => 'TUTOR01',
            'email' => 'tutor_test@pkbm.test',
        ]);
        $kelas = kelas::firstOrCreate(['nama_kelas' => 'Paket A Reguler']);
        $siswa = Siswa::create([
            'kelas_id' => $kelas->id,
            'tutor_id' => $tutor->id,
            'no_absen' => '001',
            'nama_siswa' => 'Siswa Test',
            'no_hp' => '08123456789',
            'nama_wali' => 'Wali Siswa',
            'status_siswa' => 'aktif',
        ]);

        // Buat 2 titik lokasi berbeda
        $lokasiPusat = LokasiPresensi::create([
            'nama_lokasi' => 'Gedung Pusat PKBM',
            'tipe' => 'pusat',
            'latitude' => -7.8011945,
            'longitude' => 110.364917,
            'radius_meter' => 100,
            'is_active' => true,
        ]);

        $lokasiCabang = LokasiPresensi::create([
            'nama_lokasi' => 'Cabang Kulon Progo',
            'tipe' => 'cabang',
            'latitude' => -7.850000,
            'longitude' => 110.150000,
            'radius_meter' => 150,
            'is_active' => true,
        ]);

        $file = UploadedFile::fake()->image('selfie.jpg', 600, 600);

        // Kasus 1: User memilih Cabang Kulon Progo, tapi posisi GPS berada di Gedung Pusat (jarak ~20km) -> Gagal
        $responseFail = $this->actingAs($tutorUser)->post(route('tutor.presensi.store'), [
            'mode' => 'mulai',
            'lokasi_presensi_id' => $lokasiCabang->id,
            'moda_pembelajaran' => 'sekolah',
            'siswa_id' => [$siswa->id],
            'foto' => $file,
            'lokasi' => '-7.8011945,110.364917', // Di Gedung Pusat
            'lokasi_akurasi' => 10,
        ]);

        $responseFail->assertSessionHas('warning');
        $this->assertDatabaseMissing('presensis', [
            'siswa_id' => $siswa->id,
            'lokasi_presensi_id' => $lokasiCabang->id,
        ]);

        // Kasus 2: User memilih Cabang Kulon Progo, dan posisi GPS berada di Cabang Kulon Progo (jarak ~10m) -> Sukses
        $responseSuccess = $this->actingAs($tutorUser)->post(route('tutor.presensi.store'), [
            'mode' => 'mulai',
            'lokasi_presensi_id' => $lokasiCabang->id,
            'moda_pembelajaran' => 'sekolah',
            'siswa_id' => [$siswa->id],
            'foto' => $file,
            'lokasi' => '-7.850050,110.150050', // Di Cabang Kulon Progo
            'lokasi_akurasi' => 10,
        ]);

        $responseSuccess->assertRedirect(route('tutor.dashboard'));
        $this->assertDatabaseHas('presensis', [
            'siswa_id' => $siswa->id,
            'lokasi_presensi_id' => $lokasiCabang->id,
            'status' => 'hadir',
        ]);
    }

    public function test_magang_must_check_in_within_selected_location_radius(): void
    {
        $magangUser = User::factory()->create(['role' => 'magang']);
        Magang::create([
            'user_id' => $magangUser->id,
            'asal_instansi' => 'UNY',
            'nim_nisn' => '210101001',
            'jurusan' => 'PLS',
            'tgl_mulai' => Carbon::now()->subMonth()->toDateString(),
            'tgl_selesai' => Carbon::now()->addMonths(2)->toDateString(),
            'status' => 'aktif',
        ]);

        $lokasiMitra = LokasiPresensi::create([
            'nama_lokasi' => 'Kantor Mitra Magang Bappeda',
            'tipe' => 'mitra',
            'latitude' => -7.795000,
            'longitude' => 110.368000,
            'radius_meter' => 100,
            'is_active' => true,
        ]);

        $file = UploadedFile::fake()->image('magang_selfie.jpg', 600, 600);

        // Absen di titik mitra dengan GPS sesuai
        $response = $this->actingAs($magangUser)->post(route('magang.presensi.store'), [
            'mode' => 'mulai',
            'lokasi_presensi_id' => $lokasiMitra->id,
            'foto' => $file,
            'lokasi' => '-7.795020,110.368010',
            'lokasi_akurasi' => 5,
        ]);

        $response->assertRedirect(route('magang.dashboard'));
        $this->assertDatabaseHas('presensi_karyawans', [
            'user_id' => $magangUser->id,
            'lokasi_presensi_id' => $lokasiMitra->id,
            'status' => 'hadir',
        ]);
    }
}
