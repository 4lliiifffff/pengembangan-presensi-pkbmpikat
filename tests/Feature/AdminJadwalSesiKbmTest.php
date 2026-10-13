<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\JadwalRutin;
use App\Models\JadwalSesi;
use App\Models\KategoriTutorial;
use App\Models\kelas;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminJadwalSesiKbmTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected User $tutorUser;

    protected Tutor $tutor;

    protected Siswa $siswa1;

    protected Siswa $siswa2;

    protected KategoriTutorial $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->tutorUser = User::factory()->create([
            'role' => 'tutor',
            'is_active' => true,
        ]);

        $this->tutor = Tutor::create([
            'user_id' => $this->tutorUser->id,
            'nik' => $this->tutorUser->nik,
            'nama_tutor' => 'Tutor Admin Test',
            'nama_lengkap' => 'Tutor Admin Lengkap',
            'email' => $this->tutorUser->email,
            'no_hp' => '081234567890',
        ]);

        $kelas = kelas::create(['nama_kelas' => 'Paket B Rombel 1', 'tingkat' => 'SMP']);

        $this->siswa1 = Siswa::create([
            'nama_siswa' => 'Siswa Satu',
            'no_absen' => '01',
            'no_hp' => '081234567891',
            'nama_wali' => 'Wali Siswa Satu',
            'status_siswa' => 'aktif',
            'kelas_id' => $kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);

        $this->siswa2 = Siswa::create([
            'nama_siswa' => 'Siswa Dua',
            'no_absen' => '02',
            'no_hp' => '081234567892',
            'nama_wali' => 'Wali Siswa Dua',
            'status_siswa' => 'aktif',
            'kelas_id' => $kelas->id,
            'tutor_id' => $this->tutor->id,
        ]);

        $this->kategori = KategoriTutorial::create([
            'nama_kategori' => 'Tutorial Mandiri 2 Jam',
            'durasi_jam' => 2.0,
            'nominal_honor' => 50000,
            'is_active' => true,
            'urutan' => 1,
        ]);
    }

    public function test_admin_can_view_unified_hub_index_tab_sesi(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.jadwal.index', ['tab' => 'sesi']));

        $response->assertStatus(200);
        $response->assertSee('Jadwal Sesi KBM Seluruh Tutor');
        $response->assertSee('Pola Rutin KBM');
        $response->assertSee('Agenda Acara PKBM');
    }

    public function test_admin_can_view_unified_hub_index_tab_rutin(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.jadwal.index', ['tab' => 'rutin']));

        $response->assertStatus(200);
        $response->assertSee('Pola Rutin KBM (Mingguan)');
        $response->assertSee('Tambah Pola Rutin');
    }

    public function test_admin_can_view_unified_hub_index_tab_agenda(): void
    {
        Jadwal::create([
            'judul' => 'Ujian Tengah Semester PKBM',
            'tanggal' => Carbon::today()->toDateString(),
            'deskripsi' => 'Ujian seluruh program paket',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.jadwal.index', ['tab' => 'agenda']));

        $response->assertStatus(200);
        $response->assertSee('Agenda &amp; Kegiatan Resmi Sekolah', false);
        $response->assertSee('Ujian Tengah Semester PKBM');
    }

    public function test_admin_can_store_batch_rutin_schedule_for_multiple_students(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.jadwal.store'), [
                'is_recurring' => '1',
                'tutor_id' => $this->tutor->id,
                'siswa_ids' => [$this->siswa1->id, $this->siswa2->id],
                'hari' => 'senin',
                'jam_masuk' => '09:00',
                'jam_pulang' => '11:00',
                'kategori_tutorial_id' => $this->kategori->id,
                'auto_generate' => '1',
            ]);

        $response->assertRedirect(route('admin.jadwal.index', ['tab' => 'rutin']));
        $this->assertDatabaseHas('jadwal_rutin', [
            'tutor_id' => $this->tutor->id,
            'murid_id' => $this->siswa1->id,
            'hari' => 'senin',
            'jam_masuk' => '09:00:00',
        ]);
        $this->assertDatabaseHas('jadwal_rutin', [
            'tutor_id' => $this->tutor->id,
            'murid_id' => $this->siswa2->id,
            'hari' => 'senin',
            'jam_masuk' => '09:00:00',
        ]);
    }

    public function test_admin_can_store_single_session_for_multiple_students(): void
    {
        $tanggalRencana = Carbon::tomorrow()->toDateString();

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.jadwal.store'), [
                'is_recurring' => '0',
                'tutor_id' => $this->tutor->id,
                'siswa_ids' => [$this->siswa1->id, $this->siswa2->id],
                'tanggal_rencana' => $tanggalRencana,
                'jam_masuk_rencana' => '13:00',
                'jam_pulang_rencana' => '15:00',
                'kategori_tutorial_id' => $this->kategori->id,
                'jenis_sesi' => 'reguler',
            ]);

        $response->assertRedirect(route('admin.jadwal.index', [
            'tab' => 'sesi',
            'tanggal' => $tanggalRencana,
        ]));

        $this->assertDatabaseHas('jadwal_sesi', [
            'tutor_id' => $this->tutor->id,
            'murid_id' => $this->siswa1->id,
            'tanggal_rencana' => $tanggalRencana,
            'jam_masuk_rencana' => '13:00',
            'status' => 'terjadwal',
        ]);
        $this->assertDatabaseHas('jadwal_sesi', [
            'tutor_id' => $this->tutor->id,
            'murid_id' => $this->siswa2->id,
            'tanggal_rencana' => $tanggalRencana,
            'jam_masuk_rencana' => '13:00',
            'status' => 'terjadwal',
        ]);
    }

    public function test_admin_can_toggle_and_destroy_rutin_schedule(): void
    {
        $rutin = JadwalRutin::create([
            'tutor_id' => $this->tutor->id,
            'murid_id' => $this->siswa1->id,
            'hari' => 'rabu',
            'jam_masuk' => '10:00',
            'jam_pulang' => '12:00',
            'is_active' => true,
        ]);

        // Test toggle
        $responseToggle = $this->actingAs($this->adminUser)
            ->patch(route('admin.jadwal.toggleStatusRutin', $rutin->id));

        $responseToggle->assertStatus(302);
        $this->assertFalse((bool) $rutin->fresh()->is_active);

        // Test destroy
        $responseDelete = $this->actingAs($this->adminUser)
            ->delete(route('admin.jadwal.destroyRutin', $rutin->id));

        $responseDelete->assertStatus(302);
        $this->assertDatabaseMissing('jadwal_rutin', ['id' => $rutin->id]);
    }

    public function test_admin_can_destroy_scheduled_session_not_attended_yet(): void
    {
        $sesi = JadwalSesi::create([
            'tutor_id' => $this->tutor->id,
            'murid_id' => $this->siswa1->id,
            'tanggal_rencana' => Carbon::tomorrow()->toDateString(),
            'jam_masuk_rencana' => '08:00',
            'jam_pulang_rencana' => '10:00',
            'status' => 'terjadwal',
            'jenis_sesi' => 'reguler',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.jadwal.destroy', $sesi->id));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('jadwal_sesi', ['id' => $sesi->id]);
    }

    public function test_admin_can_store_agenda_via_modal(): void
    {
        $tanggalAgenda = Carbon::tomorrow()->toDateString();

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.jadwal.storeAgenda'), [
                'judul' => 'Rapat Pleno Tutor PKBM',
                'tanggal' => $tanggalAgenda,
                'lokasi' => 'Aula Gedung B',
                'deskripsi' => 'Pembahasan evaluasi KBM',
            ]);

        $response->assertRedirect(route('admin.jadwal.index', [
            'tab' => 'agenda',
            'tanggal' => $tanggalAgenda,
        ]));

        $this->assertDatabaseHas('jadwal', [
            'judul' => 'Rapat Pleno Tutor PKBM',
            'tanggal' => $tanggalAgenda,
            'lokasi' => 'Aula Gedung B',
        ]);
    }

    public function test_admin_can_destroy_agenda(): void
    {
        $agenda = Jadwal::create([
            'judul' => 'Agenda Dihapus Test',
            'tanggal' => Carbon::today()->toDateString(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.jadwal.destroyAgenda', $agenda->id));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('jadwal', ['id' => $agenda->id]);
    }

    public function test_admin_can_update_sesi_kbm_via_modal(): void
    {
        $sesi = JadwalSesi::create([
            'siswa_id' => $this->siswa1->id,
            'murid_id' => $this->siswa1->id,
            'tutor_id' => $this->tutor->id,
            'tanggal_rencana' => Carbon::today()->toDateString(),
            'jam_masuk_rencana' => '08:00',
            'jam_pulang_rencana' => '10:00',
            'status' => 'terjadwal',
            'jenis_sesi' => 'reguler',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.jadwal.update', $sesi->id), [
                'tutor_id' => $this->tutor->id,
                'murid_id' => $this->siswa1->id,
                'tanggal_rencana' => Carbon::today()->toDateString(),
                'jam_masuk_rencana' => '09:00',
                'jam_pulang_rencana' => '11:00',
                'jenis_sesi' => 'reguler',
                'catatan' => 'Catatan diupdate oleh admin via modal in-page',
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('jadwal_sesi', [
            'id' => $sesi->id,
            'jam_masuk_rencana' => '09:00',
            'jam_pulang_rencana' => '11:00',
            'catatan' => 'Catatan diupdate oleh admin via modal in-page',
        ]);
    }

    public function test_admin_can_update_rutin_schedule_via_modal(): void
    {
        $rutin = JadwalRutin::create([
            'siswa_id' => $this->siswa1->id,
            'murid_id' => $this->siswa1->id,
            'tutor_id' => $this->tutor->id,
            'hari' => 'senin',
            'jam_masuk' => '08:00',
            'jam_pulang' => '10:00',
            'durasi_jam' => 2.0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.jadwal.updateRutin', $rutin->id), [
                'murid_id' => $this->siswa1->id,
                'tutor_id' => $this->tutor->id,
                'hari' => 'rabu',
                'jam_masuk' => '13:00',
                'jam_pulang' => '15:00',
                'keterangan' => 'Pola rutin dipindah ke Rabu siang',
            ]);

        $response->assertRedirect(route('admin.jadwal.index', ['tab' => 'rutin']));
        $this->assertDatabaseHas('jadwal_rutin', [
            'id' => $rutin->id,
            'hari' => 'rabu',
            'jam_masuk' => '13:00',
            'jam_pulang' => '15:00',
            'keterangan' => 'Pola rutin dipindah ke Rabu siang',
        ]);
    }

    public function test_admin_can_update_agenda_via_modal(): void
    {
        $agenda = Jadwal::create([
            'judul' => 'Agenda Awal',
            'tanggal' => Carbon::today()->toDateString(),
            'deskripsi' => 'Deskripsi lama',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.jadwal.updateAgenda', $agenda->id), [
                'judul' => 'Agenda Diperbarui via Modal',
                'tanggal' => Carbon::today()->toDateString(),
                'deskripsi' => 'Deskripsi baru hasil edit modal',
                'lokasi' => 'Ruang Teater',
            ]);

        $response->assertRedirect(route('admin.jadwal.index', [
            'tab' => 'agenda',
            'tanggal' => Carbon::today()->toDateString(),
        ]));

        $this->assertDatabaseHas('jadwal', [
            'id' => $agenda->id,
            'judul' => 'Agenda Diperbarui via Modal',
            'lokasi' => 'Ruang Teater',
        ]);
    }

    public function test_legacy_jadwal_sesi_kbm_routes_still_work_for_backward_compatibility(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.jadwal-sesi-kbm.index'));

        $response->assertStatus(200);
        $response->assertSee('Jadwal Sesi KBM Seluruh Tutor');
    }
}
