<?php

namespace Tests\Feature;

use App\Models\kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use Database\Seeders\KategoriTutorialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PresensiMultiModaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new KategoriTutorialSeeder)->run();
    }

    public function test_tutor_can_presensi_with_different_moda_pembelajaran(): void
    {
        Storage::fake('public');

        $userTutor = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $userTutor->id,
            'nik' => $userTutor->nik,
            'nama_lengkap' => $userTutor->nama_lengkap,
            'email' => $userTutor->email,
        ]);

        $kelas = kelas::create(['nama_kelas' => 'Paket A - Kelas 1', 'jenjang_paket' => 'paket_a', 'tingkat' => 1]);
        $siswa = Siswa::create([
            'no_absen' => 'TEST903',
            'nama_siswa' => 'Budi Santoso MultiModa',
            'no_hp' => '08123456789',
            'nama_wali' => 'Wali Budi',
            'kelas_id' => $kelas->id,
        ]);

        // 1. Test Clock In Online Moda
        $file = UploadedFile::fake()->image('bukti_online.jpg');
        $response = $this->actingAs($userTutor)->post('/tutor/presensi', [
            'siswa_id' => [$siswa->id],
            'mode' => 'mulai',
            'moda_pembelajaran' => 'online',
            'link_daring' => 'https://meet.google.com/abc-defg-hij',
            'foto' => $file,
        ]);

        $response->assertRedirect(route('tutor.dashboard'));
        $this->assertDatabaseHas('presensis', [
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswa->id,
            'moda_pembelajaran' => 'online',
            'link_daring' => 'https://meet.google.com/abc-defg-hij',
        ]);

        $presensi = Presensi::where('tutor_id', $tutor->id)->first();
        $this->assertEquals('Pembelajaran Online (Daring)', $presensi->moda_label);
    }

    public function test_gabungan_komunitas_fails_when_students_belong_to_different_packages(): void
    {
        Storage::fake('public');

        $userTutor = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $userTutor->id,
            'nik' => $userTutor->nik,
            'nama_lengkap' => $userTutor->nama_lengkap,
            'email' => $userTutor->email,
        ]);

        $kelasA = kelas::create(['nama_kelas' => 'Paket A (Setara SD)']);
        $kelasC = kelas::create(['nama_kelas' => 'Paket C (Setara SMA)']);

        $siswaA = Siswa::create([
            'no_absen' => 'TEST_A',
            'nama_siswa' => 'Siswa Paket A',
            'no_hp' => '08123456781',
            'nama_wali' => 'Wali A',
            'kelas_id' => $kelasA->id,
        ]);

        $siswaC = Siswa::create([
            'no_absen' => 'TEST_C',
            'nama_siswa' => 'Siswa Paket C',
            'no_hp' => '08123456782',
            'nama_wali' => 'Wali C',
            'kelas_id' => $kelasC->id,
        ]);

        $file = UploadedFile::fake()->image('bukti_gabungan.jpg');
        $response = $this->actingAs($userTutor)->from(route('tutor.presensi'))->post('/tutor/presensi', [
            'siswa_id' => [$siswaA->id, $siswaC->id],
            'mode' => 'mulai',
            'moda_pembelajaran' => 'kunjungan_rumah',
            'is_gabungan' => 1,
            'foto' => $file,
        ]);

        $response->assertRedirect(route('tutor.presensi'));
        $response->assertSessionHas('warning');
        $this->assertDatabaseMissing('presensis', [
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswaA->id,
        ]);
    }

    public function test_regular_multi_siswa_fails_when_students_belong_to_different_packages(): void
    {
        Storage::fake('public');

        $userTutor = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $userTutor->id,
            'nik' => $userTutor->nik,
            'nama_lengkap' => $userTutor->nama_lengkap,
            'email' => $userTutor->email,
        ]);

        $kelasA = kelas::create(['nama_kelas' => 'Paket A (Setara SD)']);
        $kelasB = kelas::create(['nama_kelas' => 'Paket B (Setara SMP)']);

        $siswaA = Siswa::create([
            'no_absen' => 'TEST_REG_A',
            'nama_siswa' => 'Siswa Paket A Reguler',
            'no_hp' => '08123456781',
            'nama_wali' => 'Wali A',
            'kelas_id' => $kelasA->id,
        ]);

        $siswaB = Siswa::create([
            'no_absen' => 'TEST_REG_B',
            'nama_siswa' => 'Siswa Paket B Reguler',
            'no_hp' => '08123456782',
            'nama_wali' => 'Wali B',
            'kelas_id' => $kelasB->id,
        ]);

        $file = UploadedFile::fake()->image('bukti_regular.jpg');
        // Sesi Reguler (is_gabungan = 0) tetapi memilih siswa Paket A dan Paket B
        $response = $this->actingAs($userTutor)->from(route('tutor.presensi'))->post('/tutor/presensi', [
            'siswa_id' => [$siswaA->id, $siswaB->id],
            'mode' => 'mulai',
            'moda_pembelajaran' => 'kunjungan_rumah',
            'is_gabungan' => 0,
            'foto' => $file,
        ]);

        $response->assertRedirect(route('tutor.presensi'));
        $response->assertSessionHas('warning');
        $this->assertDatabaseMissing('presensis', [
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswaA->id,
        ]);
        $this->assertDatabaseMissing('presensis', [
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswaB->id,
        ]);
    }

    public function test_gabungan_komunitas_succeeds_when_students_belong_to_same_package(): void
    {
        Storage::fake('public');

        $userTutor = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $userTutor->id,
            'nik' => $userTutor->nik,
            'nama_lengkap' => $userTutor->nama_lengkap,
            'email' => $userTutor->email,
        ]);

        $kelasB1 = kelas::create(['nama_kelas' => 'Paket B Kelas 7']);
        $kelasB2 = kelas::create(['nama_kelas' => 'Paket B Kelas 8']);

        $siswaB1 = Siswa::create([
            'no_absen' => 'TEST_B1',
            'nama_siswa' => 'Siswa Paket B1',
            'no_hp' => '08123456783',
            'nama_wali' => 'Wali B1',
            'kelas_id' => $kelasB1->id,
        ]);

        $siswaB2 = Siswa::create([
            'no_absen' => 'TEST_B2',
            'nama_siswa' => 'Siswa Paket B2',
            'no_hp' => '08123456784',
            'nama_wali' => 'Wali B2',
            'kelas_id' => $kelasB2->id,
        ]);

        $file = UploadedFile::fake()->image('bukti_gabungan.jpg');
        $response = $this->actingAs($userTutor)->post('/tutor/presensi', [
            'siswa_id' => [$siswaB1->id, $siswaB2->id],
            'mode' => 'mulai',
            'moda_pembelajaran' => 'kunjungan_rumah',
            'is_gabungan' => 1,
            'foto' => $file,
        ]);

        $response->assertRedirect(route('tutor.dashboard'));
        $this->assertDatabaseHas('presensis', [
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswaB1->id,
        ]);
        $this->assertDatabaseHas('presensis', [
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswaB2->id,
        ]);
    }

    public function test_auto_detect_gabungan_komunitas_when_students_from_different_classes_in_same_package(): void
    {
        Storage::fake('public');

        $userTutor = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $userTutor->id,
            'nik' => $userTutor->nik,
            'nama_lengkap' => $userTutor->nama_lengkap,
            'email' => $userTutor->email,
        ]);

        $kelasB7 = kelas::create(['nama_kelas' => 'Paket B - Kelas 7']);
        $kelasB8 = kelas::create(['nama_kelas' => 'Paket B - Kelas 8']);

        $siswa1 = Siswa::create([
            'no_absen' => 'AUTO_B7',
            'nama_siswa' => 'Siswa Auto B7',
            'no_hp' => '08123456788',
            'nama_wali' => 'Wali B7',
            'kelas_id' => $kelasB7->id,
        ]);

        $siswa2 = Siswa::create([
            'no_absen' => 'AUTO_B8',
            'nama_siswa' => 'Siswa Auto B8',
            'no_hp' => '08123456789',
            'nama_wali' => 'Wali B8',
            'kelas_id' => $kelasB8->id,
        ]);

        $file = UploadedFile::fake()->image('bukti_auto.jpg');
        // Tutor tidak mencentang is_gabungan (is_gabungan = 0)
        $response = $this->actingAs($userTutor)->post('/tutor/presensi', [
            'siswa_id' => [$siswa1->id, $siswa2->id],
            'mode' => 'mulai',
            'moda_pembelajaran' => 'kunjungan_rumah',
            'is_gabungan' => 0,
            'foto' => $file,
        ]);

        $response->assertRedirect(route('tutor.dashboard'));

        // Harus otomatis terdeteksi sebagai Gabungan Komunitas (SK Rp 50.000,- per rombel)
        $presensi1 = Presensi::where('tutor_id', $tutor->id)->where('siswa_id', $siswa1->id)->first();
        $presensi2 = Presensi::where('tutor_id', $tutor->id)->where('siswa_id', $siswa2->id)->first();

        $this->assertNotNull($presensi1);
        $this->assertNotNull($presensi2);
        $this->assertEquals(50000.0, (float) $presensi1->nominal_honor_snapshot);
        $this->assertEquals(50000.0, (float) $presensi2->nominal_honor_snapshot);
    }

    public function test_auto_detect_single_rombel_when_students_from_same_class(): void
    {
        Storage::fake('public');

        $userTutor = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $userTutor->id,
            'nik' => $userTutor->nik,
            'nama_lengkap' => $userTutor->nama_lengkap,
            'email' => $userTutor->email,
        ]);

        $kelasC10 = kelas::create(['nama_kelas' => 'Paket C - Kelas 10']);

        $siswa1 = Siswa::create([
            'no_absen' => 'SINGLE_C1',
            'nama_siswa' => 'Siswa Single C1',
            'no_hp' => '08123456791',
            'nama_wali' => 'Wali C1',
            'kelas_id' => $kelasC10->id,
        ]);

        $siswa2 = Siswa::create([
            'no_absen' => 'SINGLE_C2',
            'nama_siswa' => 'Siswa Single C2',
            'no_hp' => '08123456792',
            'nama_wali' => 'Wali C2',
            'kelas_id' => $kelasC10->id,
        ]);

        $file = UploadedFile::fake()->image('bukti_single.jpg');
        $response = $this->actingAs($userTutor)->post('/tutor/presensi', [
            'siswa_id' => [$siswa1->id, $siswa2->id],
            'mode' => 'mulai',
            'moda_pembelajaran' => 'kunjungan_rumah',
            'durasi_pilihan' => 2.0,
            'foto' => $file,
        ]);

        $response->assertRedirect(route('tutor.dashboard'));

        // Harus otomatis terdeteksi sebagai 1 Rombel Tutorial Komunitas Standar (Rp 75.000,-)
        $presensi1 = Presensi::where('tutor_id', $tutor->id)->where('siswa_id', $siswa1->id)->first();
        $this->assertNotNull($presensi1);
        $this->assertEquals(75000.0, (float) $presensi1->nominal_honor_snapshot);
    }
}
