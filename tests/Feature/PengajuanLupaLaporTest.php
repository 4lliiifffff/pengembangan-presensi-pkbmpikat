<?php

namespace Tests\Feature;

use App\Models\kelas;
use App\Models\PengajuanLupaLapor;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengajuanLupaLaporTest extends TestCase
{
    use RefreshDatabase;

    public function test_tutor_can_submit_pengajuan_lupa_lapor(): void
    {
        $userTutor = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $userTutor->id,
            'nik' => $userTutor->nik,
            'nama_lengkap' => $userTutor->nama_lengkap,
            'email' => $userTutor->email,
        ]);

        $kelas = kelas::create(['nama_kelas' => 'Paket A (Setara SD)', 'tingkat' => 'SD']);

        $siswa = Siswa::create([
            'no_absen' => 'TEST901',
            'nama_siswa' => 'Budi Santoso Test',
            'no_hp' => '08123456789',
            'nama_wali' => 'Wali Budi',
            'kelas_id' => $kelas->id,
        ]);

        $response = $this->actingAs($userTutor)->post('/tutor/lupa-lapor', [
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'alasan' => 'Lupa membawa smartphone saat sesi mengajar',
        ]);

        $response->assertRedirect(route('tutor.lupa-lapor'));
        $this->assertDatabaseHas('pengajuan_lupa_lapor', [
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswa->id,
            'status' => 'pending',
        ]);
    }

    public function test_kepsek_can_approve_pengajuan_and_auto_upsert_presensi(): void
    {
        $userKepsek = User::factory()->create(['role' => 'kepala_sekolah']);

        $userTutor = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $userTutor->id,
            'nik' => $userTutor->nik,
            'nama_lengkap' => $userTutor->nama_lengkap,
            'email' => $userTutor->email,
        ]);

        $kelas = kelas::create(['nama_kelas' => 'Kelas Paket C', 'tingkat' => 'SMA']);

        $siswa = Siswa::create([
            'no_absen' => 'TEST902',
            'nama_siswa' => 'Siti Nurhaliza Test',
            'no_hp' => '08987654321',
            'nama_wali' => 'Wali Siti',
            'kelas_id' => $kelas->id,
        ]);

        $pengajuan = PengajuanLupaLapor::create([
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswa->id,
            'tanggal' => now()->subDay()->toDateString(),
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
            'alasan' => 'Kendala sinyal internet di lokasi mengajar',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($userKepsek)->patch('/kepsek/lupa-lapor/'.$pengajuan->id.'/setujui');

        $response->assertSessionHas('success');

        // Check pengajuan status updated to disetujui
        $this->assertDatabaseHas('pengajuan_lupa_lapor', [
            'id' => $pengajuan->id,
            'status' => 'disetujui',
        ]);

        // Check auto-upsert into presensis table
        $this->assertDatabaseHas('presensis', [
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswa->id,
            'tgl_presensi' => $pengajuan->tanggal,
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
            'status' => 'hadir',
        ]);
    }
}
