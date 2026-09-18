<?php

namespace Tests\Feature;

use App\Models\JadwalRutin;
use App\Models\JadwalSesi;
use App\Models\JenjangPaket;
use App\Models\KategoriTutorial;
use App\Models\kelas as Kelas;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TutorJadwalMandiriTest extends TestCase
{
    use DatabaseTransactions;

    protected User $tutorUser;

    protected Tutor $tutor;

    protected User $siswaUser;

    protected Siswa $siswa;

    protected KategoriTutorial $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tutorUser = User::factory()->create(['role' => 'tutor', 'is_active' => 1]);
        $this->tutor = Tutor::create([
            'user_id' => $this->tutorUser->id,
            'nama_lengkap' => 'Tutor Mandiri Test',
            'email' => 'tutor_mandiri@pkbmpikat.com',
            'nik' => 'NIKMANDIRI01',
        ]);

        $this->siswaUser = User::factory()->create(['role' => 'siswa', 'is_active' => 1]);
        $jp = JenjangPaket::firstOrCreate(['kode' => 'paket_b_mandiri'], ['nama_jenjang' => 'Paket B Mandiri', 'status' => 'aktif']);
        $kls = Kelas::firstOrCreate(['nama_kelas' => 'Kelas 8 Paket B Mandiri'], ['jenjang_paket_id' => $jp->id, 'tingkat' => '8']);
        $this->siswa = Siswa::create([
            'user_id' => $this->siswaUser->id,
            'nama_siswa' => 'Siswa Mandiri Test',
            'no_absen' => 'MND-01',
            'no_hp' => '081234567890',
            'is_abk' => false,
            'nama_wali' => 'Wali Mandiri Test',
            'kelas_id' => $kls->id,
            'tutor_id' => $this->tutor->id,
            'status_siswa' => 'aktif',
        ]);

        $this->kategori = KategoriTutorial::firstOrCreate(
            ['nama_kategori' => 'Komunitas 2 Jam Test'],
            ['jenis_layanan' => 'komunitas', 'durasi_jam' => 2.0, 'nominal_honor' => 75000, 'is_aktif' => true]
        );
    }

    public function test_tutor_can_access_jadwal_sesi_calendar_and_routine_tabs(): void
    {
        $response = $this->actingAs($this->tutorUser)->get(route('tutor.jadwal-sesi.index'));
        $response->assertStatus(200);
        $response->assertSee('Kalender Sesi Belajar');
        $response->assertSee('Pola Rutin Saya');
        $response->assertSee('Agenda &amp; Pengumuman PKBM', false);

        $responseRutin = $this->actingAs($this->tutorUser)->get(route('tutor.jadwal-sesi.index', ['tab' => 'rutin']));
        $responseRutin->assertStatus(200);
        $responseRutin->assertSee('Master Pola Rutin Mingguan Anda');

        $responseAgenda = $this->actingAs($this->tutorUser)->get(route('tutor.jadwal-sesi.index', ['tab' => 'agenda']));
        $responseAgenda->assertStatus(200);
        $responseAgenda->assertSee('Agenda KBM &amp; Libur Sekolah', false);

        // Test route tutor.jadwal redirects to tutor.jadwal-sesi with tab=agenda
        $responseLegacy = $this->actingAs($this->tutorUser)->get(route('tutor.jadwal'));
        $responseLegacy->assertRedirect(route('tutor.jadwal-sesi.index', ['tab' => 'agenda']));
    }

    public function test_tutor_can_create_recurring_schedule_with_custom_end_date(): void
    {
        $startDate = Carbon::today('Asia/Jakarta')->toDateString();
        $endDate = Carbon::today('Asia/Jakarta')->addWeeks(6)->toDateString();

        $response = $this->actingAs($this->tutorUser)->post(route('tutor.jadwal-sesi.store'), [
            'is_recurring' => '1',
            'siswa_id' => $this->siswa->id,
            'kategori_tutorial_id' => $this->kategori->id,
            'hari' => 'kamis',
            'jam_masuk' => '13:30',
            'jam_pulang' => '15:30',
            'berlaku_mulai' => $startDate,
            'berlaku_sampai' => $endDate,
            'keterangan' => 'Jadwal rutin mingguan kesepakatan tutor dan siswa',
            'auto_generate' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_rutins', [
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'hari' => 'kamis',
            'jam_masuk' => '13:30:00',
            'jam_pulang' => '15:30:00',
            'is_active' => 1,
        ]);

        $rutin = JadwalRutin::where('tutor_id', $this->tutor->id)
            ->where('siswa_id', $this->siswa->id)
            ->first();

        $this->assertNotNull($rutin);
        $this->assertTrue(JadwalSesi::where('jadwal_rutin_id', $rutin->id)->count() >= 5);
    }

    public function test_tutor_can_create_single_session(): void
    {
        $targetDate = Carbon::tomorrow('Asia/Jakarta')->toDateString();

        $response = $this->actingAs($this->tutorUser)->post(route('tutor.jadwal-sesi.store'), [
            'is_recurring' => '0',
            'siswa_id' => $this->siswa->id,
            'kategori_tutorial_id' => $this->kategori->id,
            'tanggal_rencana' => $targetDate,
            'jam_masuk_rencana' => '10:00',
            'jam_pulang_rencana' => '12:00',
            'jenis_sesi' => 'reguler',
            'catatan' => 'Sesi KBM pengayaan',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_sesis', [
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'tanggal_rencana' => $targetDate,
            'jam_masuk_rencana' => '10:00:00',
            'jam_pulang_rencana' => '12:00:00',
            'jenis_sesi' => 'reguler',
            'status' => 'terjadwal',
        ]);
    }

    public function test_tutor_can_reschedule_session_using_option_two(): void
    {
        $origDate = Carbon::today('Asia/Jakarta')->toDateString();
        $sesi = JadwalSesi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'kategori_tutorial_id' => $this->kategori->id,
            'tanggal_rencana' => $origDate,
            'jam_masuk_rencana' => '09:00:00',
            'jam_pulang_rencana' => '11:00:00',
            'durasi_jam' => 2.0,
            'jenis_sesi' => 'reguler',
            'status' => 'terjadwal',
        ]);

        $newDate = Carbon::today('Asia/Jakarta')->addDays(3)->toDateString();

        $response = $this->actingAs($this->tutorUser)->post(
            route('tutor.jadwal-sesi.reschedule', $sesi),
            [
                'tanggal_baru' => $newDate,
                'jam_masuk_baru' => '14:00',
                'jam_pulang_baru' => '16:00',
                'alasan_penggantian' => 'Siswa ada acara keluarga mendadak',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify old session status is dibatalkan
        $sesi->refresh();
        $this->assertEquals('dibatalkan', $sesi->status);
        $this->assertEquals('Siswa ada acara keluarga mendadak', $sesi->alasan_penggantian);

        // Verify replacement session created
        $this->assertDatabaseHas('jadwal_sesis', [
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'tanggal_rencana' => $newDate,
            'jam_masuk_rencana' => '14:00:00',
            'jam_pulang_rencana' => '16:00:00',
            'jenis_sesi' => 'pengganti',
            'status' => 'terjadwal',
            'tanggal_asli' => $origDate,
            'alasan_penggantian' => 'Siswa ada acara keluarga mendadak',
        ]);
    }

    public function test_tutor_cannot_reschedule_completed_session(): void
    {
        $origDate = Carbon::today('Asia/Jakarta')->toDateString();
        $sesi = JadwalSesi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'kategori_tutorial_id' => $this->kategori->id,
            'tanggal_rencana' => $origDate,
            'jam_masuk_rencana' => '09:00:00',
            'jam_pulang_rencana' => '11:00:00',
            'durasi_jam' => 2.0,
            'jenis_sesi' => 'reguler',
            'status' => 'selesai',
        ]);

        $newDate = Carbon::today('Asia/Jakarta')->addDays(2)->toDateString();

        $response = $this->actingAs($this->tutorUser)->post(
            route('tutor.jadwal-sesi.reschedule', $sesi),
            [
                'tanggal_baru' => $newDate,
                'jam_masuk_baru' => '14:00',
                'jam_pulang_baru' => '16:00',
                'alasan_penggantian' => 'Coba ubah sesi selesai',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('warning');

        $sesi->refresh();
        $this->assertEquals('selesai', $sesi->status);
    }

    public function test_tutor_can_toggle_and_destroy_own_recurring_schedule(): void
    {
        $rutin = JadwalRutin::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'kategori_tutorial_id' => $this->kategori->id,
            'hari' => 'jumat',
            'jam_masuk' => '08:00:00',
            'jam_pulang' => '10:00:00',
            'durasi_jam' => 2.0,
            'is_active' => true,
        ]);

        // Toggle status
        $toggleResp = $this->actingAs($this->tutorUser)->patch(
            route('tutor.jadwal-rutin.toggleStatus', $rutin)
        );
        $toggleResp->assertRedirect();
        $rutin->refresh();
        $this->assertFalse((bool) $rutin->is_active);

        // Destroy
        $destroyResp = $this->actingAs($this->tutorUser)->delete(
            route('tutor.jadwal-rutin.destroy', $rutin)
        );
        $destroyResp->assertRedirect();
        $this->assertDatabaseMissing('jadwal_rutins', ['id' => $rutin->id]);
    }

    public function test_tutor_can_create_custom_duration_session_with_non_sk_tagging(): void
    {
        $date = Carbon::today('Asia/Jakarta')->addDays(3)->toDateString();

        // Jadwal dengan durasi 1.25 jam (09:00 - 10:15) yang tidak ada di SK
        $response = $this->actingAs($this->tutorUser)->post(route('tutor.jadwal-sesi.store'), [
            'is_recurring' => '0',
            'siswa_id' => $this->siswa->id,
            'kategori_tutorial_id' => '',
            'tanggal_rencana' => $date,
            'jam_masuk_rencana' => '09:00',
            'jam_pulang_rencana' => '10:15',
            'jenis_sesi' => 'reguler',
            'catatan' => 'Bimbingan intensif persiapan lomba',
        ]);

        $response->assertRedirect();

        $sesi = JadwalSesi::where('tutor_id', $this->tutor->id)
            ->whereDate('tanggal_rencana', $date)
            ->first();

        $this->assertNotNull($sesi);
        $this->assertEquals(1.25, (float) $sesi->durasi_jam);
        $this->assertNull($sesi->kategori_tutorial_id);
        $this->assertStringContainsString('[Jadwal Khusus: Durasi 1.25 Jam di luar SK]', $sesi->catatan);
    }
}
