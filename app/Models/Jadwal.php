<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Jadwal — Data Agenda/Jadwal Kegiatan
 *
 * Model ini merepresentasikan agenda atau jadwal kegiatan yang dibuat oleh admin.
 * Jadwal bersifat informatif dan dapat dilihat oleh semua role (tutor juga bisa melihat).
 *
 * STRUKTUR TABEL 'jadwals':
 *   - id                 : Primary key
 *   - judul              : Judul/nama kegiatan atau agenda
 *   - deskripsi          : Deskripsi detail kegiatan (opsional)
 *   - tanggal            : Tanggal pelaksanaan kegiatan
 *   - lokasi             : Tempat pelaksanaan (keterangan ruangan / custom)
 *   - lokasi_presensi_id : Foreign key ke master lokasi_presensis (opsional)
 */
class Jadwal extends Model
{
    protected $table = 'jadwal';

    /**
     * Kolom yang boleh diisi secara massal.
     *
     * @var array<string>
     */
    protected $fillable = [
        'judul',              // Nama/judul agenda kegiatan
        'deskripsi',          // Deskripsi lebih lanjut tentang kegiatan (opsional)
        'tanggal',            // Tanggal pelaksanaan (format: YYYY-MM-DD)
        'lokasi',             // Lokasi kegiatan; keterangan ruangan / lokasi custom
        'lokasi_presensi_id', // Foreign key ke master lokasi presensi resmi
    ];

    /**
     * Casting tipe data untuk atribut tertentu.
     * 'tanggal' di-cast ke date agar bisa langsung digunakan sebagai Carbon object.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal' => 'date:Y-m-d',
    ];

    /**
     * Relasi ke Master Titik Lokasi Presensi Resmi.
     */
    public function lokasiPresensi(): BelongsTo
    {
        return $this->belongsTo(LokasiPresensi::class, 'lokasi_presensi_id');
    }

    /**
     * Memeriksa apakah lokasi jadwal adalah lokasi sekolah (default).
     */
    public function isLokasiSekolah(): bool
    {
        if ($this->lokasiPresensi) {
            return $this->lokasiPresensi->tipe === 'pusat';
        }

        return true;
    }

    /**
     * Mengembalikan ringkasan lokasi untuk ditampilkan di UI.
     * Jika terhubung ke master lokasi resmi -> tampilkan nama titik resmi (+ detail ruangan jika ada).
     * Jika hanya lokasi teks -> tampilkan teks tersebut.
     * Jika kosong -> tampilkan nama sekolah default.
     */
    public function lokasiRingkasan(): string
    {
        if ($this->lokasiPresensi) {
            $nama = $this->lokasiPresensi->nama_lokasi;

            return $this->lokasi ? "{$nama} ({$this->lokasi})" : $nama;
        }

        return $this->lokasi ?: (string) config('lokasi.sekolah_nama');
    }

    /**
     * Menghasilkan URL Google Maps untuk lokasi jadwal.
     *
     * Logika:
     *  - Jika terhubung ke titik master lokasi resmi -> gunakan koordinat GPS akurat (latitude, longitude)
     *  - Jika ada lokasi custom teks -> encode ke URL pencarian Google Maps
     *  - Jika tidak ada lokasi -> gunakan URL tetap sekolah dari konfigurasi
     *
     * @return string|null URL Google Maps atau null jika tidak ada lokasi
     */
    public function lokasiPetaUrl(): ?string
    {
        if ($this->lokasiPresensi && $this->lokasiPresensi->latitude && $this->lokasiPresensi->longitude) {
            $lat = $this->lokasiPresensi->latitude;
            $lng = $this->lokasiPresensi->longitude;

            return "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}";
        }

        if ($this->lokasi) {
            return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($this->lokasi);
        }

        $url = config('lokasi.sekolah_maps_url');

        return $url ? (string) $url : null;
    }
}
