<?php

namespace Tests\Feature;

use App\Models\kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KepsekAnalyticsKpiTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_service_calculates_monthly_trend_and_kpi_rankings(): void
    {
        /** @var User $userKepsek */
        $userKepsek = User::factory()->create(['role' => 'kepala_sekolah']);

        /** @var User $userTutor1 */
        $userTutor1 = User::factory()->create(['role' => 'tutor', 'nama_lengkap' => 'Tutor Prima']);
        $tutor1 = Tutor::create([
            'user_id' => $userTutor1->id,
            'nik' => $userTutor1->nik,
            'nama_tutor' => 'Tutor Prima',
            'nama_lengkap' => $userTutor1->nama_lengkap,
            'email' => $userTutor1->email,
            'no_hp' => '081234567891',
        ]);

        /** @var User $userTutor2 */
        $userTutor2 = User::factory()->create(['role' => 'tutor', 'nama_lengkap' => 'Tutor Sekunder']);
        $tutor2 = Tutor::create([
            'user_id' => $userTutor2->id,
            'nik' => $userTutor2->nik,
            'nama_tutor' => 'Tutor Sekunder',
            'nama_lengkap' => $userTutor2->nama_lengkap,
            'email' => $userTutor2->email,
            'no_hp' => '081234567892',
        ]);

        $kelas = kelas::create(['nama_kelas' => 'Bintang A', 'tingkat' => 'SMA']);
        $siswa = Siswa::create([
            'nama_siswa' => 'Siswa Analytics',
            'kelas_id' => $kelas->id,
            'tutor_id' => $tutor1->id,
            'no_absen' => '10',
            'no_hp' => '081234567890',
            'nama_wali' => 'Wali Analytics',
        ]);

        // Create presensi records for tutor 1 (2 hours session)
        Presensi::create([
            'tutor_id' => $tutor1->id,
            'siswa_id' => $siswa->id,
            'tgl_presensi' => now()->format('Y-m-d'),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'status' => 'hadir',
            'materi' => 'Fisika Kuantum',
        ]);

        $analyticsService = new AnalyticsService;

        // 1. Test monthly trend calculation
        $trend = $analyticsService->getMonthlyAttendanceTrend(6);
        $this->assertIsArray($trend);
        $this->assertCount(6, $trend['labels']);
        $this->assertGreaterThanOrEqual(1, array_sum($trend['total_hadir']));
        $this->assertGreaterThanOrEqual(2.0, array_sum($trend['total_jam']));

        // 2. Test KPI rankings calculation
        $rankings = $analyticsService->getTutorKpiRanking(now()->month, now()->year);
        $this->assertGreaterThanOrEqual(2, $rankings->count());

        $firstRank = $rankings->first();
        $this->assertEquals($tutor1->id, $firstRank['tutor']->id);
        $this->assertEquals(1, $firstRank['rank']);
        $this->assertStringContainsString('#1 Top Performer', $firstRank['rank_badge']);
        $this->assertEquals(100, $firstRank['pct_disiplin']);
    }

    public function test_kepsek_can_access_dashboard_and_analytics_view_with_data(): void
    {
        /** @var User $kepsek */
        $kepsek = User::factory()->create(['role' => 'kepala_sekolah']);

        $response = $this->actingAs($kepsek)->get(route('kepsek.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('kepsek.dashboard');
        $response->assertViewHasAll(['today', 'counts', 'weekly', 'latest', 'monthlyTrend', 'kpiRanking']);
        $response->assertSee('Analytics Tren Kehadiran');
        $response->assertSee('Pemeringkatan KPI Tutor');

        $analyticsResponse = $this->actingAs($kepsek)->get(route('kepsek.analytics'));
        $analyticsResponse->assertStatus(200);
    }
}
