<?php

namespace Tests\Feature;

use App\Models\JenjangPaket;
use App\Models\kelas as Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminJenjangPaketTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_jenjang_paket_index(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $jp = JenjangPaket::create([
            'kode' => 'paket_a',
            'nama_jenjang' => 'Paket A (Setara SD)',
            'tingkat_label' => 'Kelas 1 - 6',
            'urutan' => 1,
            'is_aktif' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.jenjang-paket.index'));
        $response->assertOk();
        $response->assertSee('Paket A (Setara SD)');
        $response->assertSee('paket_a');
    }

    public function test_admin_can_create_custom_jenjang_paket(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.jenjang-paket.store'), [
            'kode' => 'keaksaraan_fungsional',
            'nama_jenjang' => 'Keaksaraan Fungsional (KF)',
            'tingkat_label' => 'Dasar / Mandiri',
            'keterangan' => 'Pemberantasan buta aksara dan keterampilan hidup',
            'urutan' => 7,
            'is_aktif' => '1',
        ]);

        $response->assertRedirect(route('admin.jenjang-paket.index'));
        $this->assertDatabaseHas('jenjang_pakets', [
            'kode' => 'keaksaraan_fungsional',
            'nama_jenjang' => 'Keaksaraan Fungsional (KF)',
            'is_aktif' => true,
        ]);
    }

    public function test_admin_can_update_jenjang_paket(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $jp = JenjangPaket::create([
            'kode' => 'vokasi',
            'nama_jenjang' => 'Vokasi Kejuruan',
            'tingkat_label' => 'Dasar',
            'urutan' => 4,
            'is_aktif' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.jenjang-paket.update', $jp), [
            'kode' => 'vokasi',
            'nama_jenjang' => 'Vokasi & Keterampilan Terapan',
            'tingkat_label' => 'Dasar - Terampil - Mahir',
            'keterangan' => 'Program vokasi industri dan ekonomi kreatif',
            'urutan' => 4,
            'is_aktif' => '1',
        ]);

        $response->assertRedirect(route('admin.jenjang-paket.index'));
        $this->assertDatabaseHas('jenjang_pakets', [
            'id' => $jp->id,
            'nama_jenjang' => 'Vokasi & Keterampilan Terapan',
            'tingkat_label' => 'Dasar - Terampil - Mahir',
        ]);
    }

    public function test_admin_can_toggle_jenjang_paket_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $jp = JenjangPaket::create([
            'kode' => 'kursus',
            'nama_jenjang' => 'Kursus Singkat',
            'is_aktif' => true,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.jenjang-paket.toggleStatus', $jp));
        $response->assertRedirect(route('admin.jenjang-paket.index'));
        $this->assertDatabaseHas('jenjang_pakets', [
            'id' => $jp->id,
            'is_aktif' => false,
        ]);
    }

    public function test_admin_cannot_delete_jenjang_paket_in_use_without_confirm_unlink(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $jp = JenjangPaket::create([
            'kode' => 'paket_b',
            'nama_jenjang' => 'Paket B (Setara SMP)',
            'is_aktif' => true,
        ]);

        Kelas::create([
            'nama_kelas' => 'Paket B - Kelas 7',
            'jenjang_paket_id' => $jp->id,
            'tingkat' => '7',
        ]);

        // Delete without confirm_unlink should be prevented
        $response = $this->actingAs($admin)->delete(route('admin.jenjang-paket.destroy', $jp));
        $response->assertRedirect(route('admin.jenjang-paket.index'));
        $response->assertSessionHas('warning');
        $this->assertDatabaseHas('jenjang_pakets', [
            'id' => $jp->id,
        ]);
    }

    public function test_admin_can_delete_jenjang_paket_with_confirm_unlink_and_nullify_foreign_key(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $jp = JenjangPaket::create([
            'kode' => 'paket_khusus',
            'nama_jenjang' => 'Paket Khusus Difabel',
            'is_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => 'Kelas Khusus A',
            'jenjang_paket_id' => $jp->id,
            'tingkat' => '1',
        ]);

        // Delete with confirm_unlink=1
        $response = $this->actingAs($admin)->delete(route('admin.jenjang-paket.destroy', $jp), [
            'confirm_unlink' => '1',
        ]);

        $response->assertRedirect(route('admin.jenjang-paket.index'));
        $response->assertSessionHas('success');

        // Master jenjang should be deleted
        $this->assertDatabaseMissing('jenjang_pakets', [
            'id' => $jp->id,
        ]);

        // Kelas foreign key should be set to NULL
        $this->assertDatabaseHas('kelas', [
            'id' => $kelas->id,
            'jenjang_paket_id' => null,
        ]);
    }

    public function test_admin_can_delete_jenjang_paket_with_reassigning_classes_to_target_jenjang(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $jpOld = JenjangPaket::create([
            'kode' => 'kursus_lama',
            'nama_jenjang' => 'Kursus Komputer Lama',
            'is_aktif' => true,
        ]);

        $jpNew = JenjangPaket::create([
            'kode' => 'vokasi_ti',
            'nama_jenjang' => 'Vokasi Teknologi Informasi',
            'is_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => 'TI Komputer Dasar',
            'jenjang_paket_id' => $jpOld->id,
            'tingkat' => '1',
        ]);

        $siswa = Siswa::create([
            'no_absen' => 'TI001',
            'nama_siswa' => 'Siswa Vokasi TI',
            'kelas_id' => $kelas->id,
            'status_siswa' => 'aktif',
            'no_hp' => '081234567890',
            'nama_wali' => 'Wali TI',
        ]);

        // Delete jpOld with target_jenjang_paket_id = jpNew->id
        $response = $this->actingAs($admin)->delete(route('admin.jenjang-paket.destroy', $jpOld), [
            'target_jenjang_paket_id' => $jpNew->id,
        ]);

        $response->assertRedirect(route('admin.jenjang-paket.index'));
        $response->assertSessionHas('success');

        // Old jenjang should be gone
        $this->assertDatabaseMissing('jenjang_pakets', ['id' => $jpOld->id]);

        // Kelas should be reassigned to jpNew
        $this->assertDatabaseHas('kelas', [
            'id' => $kelas->id,
            'jenjang_paket_id' => $jpNew->id,
        ]);

        // Siswa hasOneThrough masterJenjang should resolve directly to jpNew
        $this->assertEquals($jpNew->id, $siswa->fresh()->masterJenjang->id);
        $this->assertEquals('Vokasi Teknologi Informasi', $siswa->fresh()->masterJenjang->nama_jenjang);
    }

    public function test_kelas_create_and_edit_page_loads_dynamic_jenjang_pakets(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        JenjangPaket::create([
            'kode' => 'homeschooling',
            'nama_jenjang' => 'Homeschooling Mandiri',
            'tingkat_label' => 'Level 1 - 12',
            'is_aktif' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.kelas.create'));
        $response->assertOk();
        $response->assertSee('Homeschooling Mandiri');
        $response->assertSee('homeschooling');
    }
}
