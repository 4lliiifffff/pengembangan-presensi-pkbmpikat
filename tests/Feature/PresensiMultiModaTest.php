<?php

namespace Tests\Feature;

use App\Models\kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PresensiMultiModaTest extends TestCase
{
    use RefreshDatabase;

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

        $kelas = kelas::create(['nama_kelas' => 'Kelas A', 'tingkat' => 'PAUD']);
        $siswa = Siswa::create([
            'no_absen' => '001',
            'nama_siswa' => 'Budi Santoso',
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
}
