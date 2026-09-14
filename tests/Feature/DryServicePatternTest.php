<?php

namespace Tests\Feature;

use App\Models\kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use App\Services\LaporanPresensiService;
use App\Services\PresensiService;
use App\Services\TutorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DryServicePatternTest extends TestCase
{
    use RefreshDatabase;

    public function test_tutor_service_returns_assignable_tutors_and_syncs_kepsek()
    {
        /** @var User $userTutor */
        $userTutor = User::factory()->create([
            'role' => 'tutor',
            'nama_lengkap' => 'Tutor A',
        ]);
        Tutor::create([
            'user_id' => $userTutor->id,
            'nik' => $userTutor->nik,
            'nama_tutor' => 'Tutor A',
            'nama_lengkap' => $userTutor->nama_lengkap,
            'email' => $userTutor->email,
            'no_hp' => '081234567891',
        ]);

        /** @var User $userKepsek */
        $userKepsek = User::factory()->create([
            'role' => 'kepala_sekolah',
            'nama_lengkap' => 'Kepsek B',
        ]);

        $tutorService = new TutorService;

        // Test getAssignableTutors
        $tutors = $tutorService->getAssignableTutors();
        $this->assertTrue($tutors->contains('user_id', $userTutor->id));
        $this->assertTrue($tutors->contains('user_id', $userKepsek->id));

        // Test syncKepsekIntoTutors
        $tutorService->syncKepsekIntoTutors();
        $this->assertDatabaseHas('tutors', [
            'user_id' => $userKepsek->id,
        ]);
    }

    public function test_laporan_presensi_service_calculates_monthly_rekap()
    {
        /** @var User $userTutor */
        $userTutor = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $userTutor->id,
            'nik' => $userTutor->nik,
            'nama_tutor' => 'Tutor Rekap',
            'nama_lengkap' => $userTutor->nama_lengkap,
            'email' => $userTutor->email,
            'no_hp' => '081234567892',
        ]);

        $kelas = kelas::create(['nama_kelas' => 'Reguler', 'tingkat' => 'SD']);
        $siswa = Siswa::create([
            'nama_siswa' => 'Siswa Test',
            'kelas_id' => $kelas->id,
            'tutor_id' => $tutor->id,
            'no_absen' => '001',
            'no_hp' => '08123456789',
            'nama_wali' => 'Wali Test',
        ]);

        // Create presensi record for current month
        Presensi::create([
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswa->id,
            'tgl_presensi' => now()->format('Y-m-d'),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'status' => 'hadir',
            'materi' => 'Matematika Dasar',
        ]);

        $laporanService = new LaporanPresensiService;
        $rekap = $laporanService->getRekapTutorBulanan((int) now()->month, (int) now()->year);

        $this->assertIsArray($rekap);
        $this->assertEquals(1, $rekap['totalSesi']);
        $this->assertEquals(1, $rekap['totalHadir']);
        $this->assertEquals(1, $rekap['totalTutorAktif']);
    }

    public function test_presensi_service_syncs_approved_attendance()
    {
        /** @var User $userTutor */
        $userTutor = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $userTutor->id,
            'nik' => $userTutor->nik,
            'nama_tutor' => 'Tutor Sync',
            'nama_lengkap' => $userTutor->nama_lengkap,
            'email' => $userTutor->email,
            'no_hp' => '081234567893',
        ]);

        $kelas = kelas::create(['nama_kelas' => 'Reguler', 'tingkat' => 'SD']);
        $siswa = Siswa::create([
            'nama_siswa' => 'Siswa Test Izin',
            'kelas_id' => $kelas->id,
            'tutor_id' => $tutor->id,
            'no_absen' => '002',
            'no_hp' => '08123456789',
            'nama_wali' => 'Wali Test',
        ]);

        $presensiService = new PresensiService;
        $createdCount = $presensiService->syncApprovedAttendance(
            tutorId: $tutor->id,
            siswaIds: [$siswa->id],
            startDateStr: now()->format('Y-m-d'),
            endDateStr: now()->format('Y-m-d'),
            status: 'izin',
            jamMulai: '08:00:00',
            jamSelesai: '10:00:00',
            materi: 'Izin Sakit'
        );

        $this->assertEquals(1, $createdCount);
        $this->assertDatabaseHas('presensis', [
            'tutor_id' => $tutor->id,
            'siswa_id' => $siswa->id,
            'tgl_presensi' => now()->format('Y-m-d'),
            'status' => 'izin',
        ]);
    }
}
