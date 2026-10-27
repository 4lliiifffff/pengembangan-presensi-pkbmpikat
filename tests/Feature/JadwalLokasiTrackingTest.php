<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\JadwalRutin;
use App\Models\JadwalSesi;
use App\Models\JenjangPaket;
use App\Models\Kelas;
use App\Models\LokasiPresensi;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use App\Services\JadwalRutinService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JadwalLokasiTrackingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected User $tutorUser;

    protected Tutor $tutor;

    protected User $siswaUser;

    protected Siswa $siswa;

    protected LokasiPresensi $lokasiPusat;

    protected LokasiPresensi $lokasiMitra;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        Config::set('lokasi.sekolah_lat', -7.8011945);
        Config::set('lokasi.sekolah_lng', 110.364917);
        Config::set('lokasi.radius_meter', 100);

        $this->adminUser = User::factory()->create(['role' => 'admin', 'is_active' => 1]);

        $this->tutorUser = User::factory()->create(['role' => 'tutor', 'is_active' => 1]);
        $this->tutor = Tutor::create([
            'user_id' => $this->tutorUser->id,
            'nama_lengkap' => 'Tutor Lokasi Test',
            'email' => 'tutor_lokasi@pkbmpikat.com',
            'nik' => 'NIKLOK01',
        ]);

        $this->siswaUser = User::factory()->create(['role' => 'siswa', 'is_active' => 1]);
        $jp = JenjangPaket::firstOrCreate(['kode' => 'paket_b_lokasi'], ['nama_jenjang' => 'Paket B Lokasi', 'status' => 'aktif']);
        $kls = Kelas::firstOrCreate(['nama_kelas' => 'Kelas 8 Paket B Lokasi'], ['jenjang_paket_id' => $jp->id, 'tingkat' => '8']);
        $this->siswa = Siswa::create([
            'user_id' => $this->siswaUser->id,
            'nama_siswa' => 'Siswa Lokasi Test',
            'nama_wali' => 'Wali Lokasi Test',
            'kelas_id' => $kls->id,
            'tutor_id' => $this->tutor->id,
            'status_siswa' => 'aktif',
            'no_absen' => 'LOK-01',
            'no_hp' => '081298765432',
            'is_abk' => false,
        ]);

        $this->lokasiPusat = LokasiPresensi::create([
            'nama_lokasi' => 'Pusat PKBM Pikat',
            'latitude' => -7.8011945,
            'longitude' => 110.3649170,
            'radius_meter' => 100,
            'is_aktif' => true,
        ]);

        $this->lokasiMitra = LokasiPresensi::create([
            'nama_lokasi' => 'Lab Komputer Mitra Belajar',
            'latitude' => -7.7800000,
            'longitude' => 110.3700000,
            'radius_meter' => 75,
            'is_aktif' => true,
        ]);
    }

    public function test_admin_can_create_agenda_with_official_location_point(): void
    {
        $targetDate = now()->addDays(2)->format('Y-m-d');

        $response = $this->actingAs($this->adminUser)->post(route('admin.jadwal.store'), [
            'judul' => 'Workshop Robotika & Coding',
            'tanggal' => $targetDate,
            'lokasi_presensi_id' => $this->lokasiMitra->id,
            'lokasi' => 'Ruang Lab Cyber',
            'deskripsi' => 'Pelatihan teknologi robotika bersama mitra PKBM.',
        ]);

        $response->assertRedirect(route('admin.jadwal.index', ['tanggal' => $targetDate]));

        $this->assertDatabaseHas('jadwal', [
            'judul' => 'Workshop Robotika & Coding',
            'lokasi_presensi_id' => $this->lokasiMitra->id,
            'lokasi' => 'Ruang Lab Cyber',
        ]);

        $jadwal = Jadwal::where('judul', 'Workshop Robotika & Coding')->firstOrFail();
        $this->assertEquals('Lab Komputer Mitra Belajar (Ruang Lab Cyber)', $jadwal->lokasiRingkasan());
        $this->assertStringContainsString('-7.78,110.37', $jadwal->lokasiPetaUrl());
    }

    public function test_routine_schedule_generation_propagates_location_to_sessions(): void
    {
        $service = app(JadwalRutinService::class);
        $startDate = Carbon::parse('2026-10-05'); // Senin

        $rutin = JadwalRutin::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'hari' => 'senin',
            'jam_masuk' => '09:00:00',
            'jam_pulang' => '11:00:00',
            'durasi_jam' => 2.0,
            'lokasi_presensi_id' => $this->lokasiMitra->id,
            'berlaku_mulai' => $startDate->toDateString(),
            'is_active' => true,
        ]);

        $result = $service->generateSesiForPeriod(
            $startDate->toDateString(),
            $startDate->copy()->addWeeks(2)->toDateString(),
            $this->siswa->id,
            $this->tutor->id
        );
        $this->assertGreaterThan(0, $result['created']);

        $generated = JadwalSesi::where('jadwal_rutin_id', $rutin->id)->get();
        $this->assertNotEmpty($generated);

        foreach ($generated as $sesi) {
            $this->assertEquals($this->lokasiMitra->id, $sesi->lokasi_presensi_id);
            $this->assertNotNull($sesi->lokasiPresensi);
            $this->assertEquals('Lab Komputer Mitra Belajar', $sesi->lokasiPresensi->nama_lokasi);
        }
    }

    public function test_tutor_can_create_single_session_with_location(): void
    {
        $targetDate = Carbon::now('Asia/Jakarta')->addDays(1)->format('Y-m-d');

        $response = $this->actingAs($this->tutorUser)->post(route('tutor.jadwal-sesi.store'), [
            'siswa_id' => $this->siswa->id,
            'jenis_sesi' => 'reguler',
            'tanggal_rencana' => $targetDate,
            'jam_masuk_rencana' => '13:00',
            'jam_pulang_rencana' => '15:00',
            'durasi_jam' => 2.0,
            'lokasi_presensi_id' => $this->lokasiMitra->id,
            'is_recurring' => 0,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('jadwal_sesi', [
            'tutor_id' => $this->tutor->id,
            'murid_id' => $this->siswa->id,
            'tanggal_rencana' => $targetDate,
            'lokasi_presensi_id' => $this->lokasiMitra->id,
        ]);
    }

    public function test_tutor_can_reschedule_session_and_change_location(): void
    {
        $originalDate = Carbon::now('Asia/Jakarta')->addDays(1)->toDateString();
        $sesi = JadwalSesi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'tanggal_rencana' => $originalDate,
            'jam_masuk_rencana' => '09:00:00',
            'jam_pulang_rencana' => '11:00:00',
            'durasi_jam' => 2.0,
            'status' => 'terjadwal',
            'lokasi_presensi_id' => $this->lokasiPusat->id,
        ]);

        $newDate = Carbon::now('Asia/Jakarta')->addDays(3)->toDateString();

        $response = $this->actingAs($this->tutorUser)->post(route('tutor.jadwal-sesi.reschedule', $sesi), [
            'tanggal_baru' => $newDate,
            'jam_masuk_baru' => '10:00',
            'jam_pulang_baru' => '12:00',
            'alasan_penggantian' => 'Pindah ke Lab Mitra Belajar',
            'lokasi_presensi_id' => $this->lokasiMitra->id,
        ]);

        $response->assertRedirect();

        // Original marked dibatalkan
        $this->assertDatabaseHas('jadwal_sesi', [
            'id' => $sesi->id,
            'status' => 'dibatalkan',
        ]);

        // New replacement session has new location
        $this->assertDatabaseHas('jadwal_sesi', [
            'tutor_id' => $this->tutor->id,
            'murid_id' => $this->siswa->id,
            'tanggal_rencana' => $newDate,
            'jenis_sesi' => 'pengganti',
            'lokasi_presensi_id' => $this->lokasiMitra->id,
        ]);
    }

    public function test_siswa_presensi_foto_page_targets_scheduled_kbm_location(): void
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // Create an active session for today at the Mitra location
        JadwalSesi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'tanggal_rencana' => $today,
            'jam_masuk_rencana' => '08:00:00',
            'jam_pulang_rencana' => '18:00:00',
            'durasi_jam' => 10.0,
            'status' => 'terjadwal',
            'lokasi_presensi_id' => $this->lokasiMitra->id,
        ]);

        $response = $this->actingAs($this->siswaUser)->get(route('siswa.presensi.foto'));

        $response->assertStatus(200);
        $response->assertSee('Lab Komputer Mitra Belajar');
        $response->assertViewHas('targetLokasi', function ($targetLokasi) {
            return $targetLokasi && $targetLokasi->id === $this->lokasiMitra->id;
        });
    }

    public function test_tutor_presensi_foto_page_auto_selects_scheduled_session_location_and_student(): void
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $sesi = JadwalSesi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'tanggal_rencana' => $today,
            'jam_masuk_rencana' => '08:00:00',
            'jam_pulang_rencana' => '10:00:00',
            'durasi_jam' => 2.0,
            'status' => 'terjadwal',
            'lokasi_presensi_id' => $this->lokasiMitra->id,
        ]);

        $response = $this->actingAs($this->tutorUser)->get(route('tutor.presensi', [
            'jadwal_sesi_id' => $sesi->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('selectedJadwalSesi', function ($selected) use ($sesi) {
            return $selected && $selected->id === $sesi->id;
        });
        $response->assertViewHas('targetLokasi', function ($targetLokasi) {
            return $targetLokasi && $targetLokasi->id === $this->lokasiMitra->id;
        });

        $content = $response->getContent();
        $this->assertStringContainsString('id="inputJadwalSesiId" value="'.$sesi->id.'"', $content);
        $this->assertStringContainsString('value="'.$this->siswa->id.'"', $content);
        $this->assertStringContainsString('checked', $content);
        $this->assertStringContainsString('value="'.$this->lokasiMitra->id.'" data-lat="-7.78"', $content);
    }

    public function test_siswa_presensi_foto_page_auto_selects_requested_jadwal_sesi_id(): void
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        Carbon::setTestNow(Carbon::parse($today.' 08:15:00', 'Asia/Jakarta'));

        $sesi = JadwalSesi::create([
            'tutor_id' => $this->tutor->id,
            'siswa_id' => $this->siswa->id,
            'tanggal_rencana' => $today,
            'jam_masuk_rencana' => '08:00:00',
            'jam_pulang_rencana' => '18:00:00',
            'durasi_jam' => 10.0,
            'status' => 'terjadwal',
            'lokasi_presensi_id' => $this->lokasiMitra->id,
        ]);

        $response = $this->actingAs($this->siswaUser)->get(route('siswa.presensi.foto', [
            'jadwal_sesi_id' => $sesi->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('todaySesi', function ($todaySesi) use ($sesi) {
            return $todaySesi && $todaySesi->id === $sesi->id;
        });
        $response->assertViewHas('targetLokasi', function ($targetLokasi) {
            return $targetLokasi && $targetLokasi->id === $this->lokasiMitra->id;
        });

        $content = $response->getContent();
        $this->assertStringContainsString('id="inputJadwalSesiId" value="'.$sesi->id.'"', $content);
        $this->assertStringContainsString('value="'.$this->lokasiMitra->id.'"', $content);

        Carbon::setTestNow();
    }
}
