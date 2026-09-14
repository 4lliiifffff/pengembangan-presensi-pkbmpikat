<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Jadwal — Data Agenda/Jadwal Kegiatan
 *
 * Model ini merepresentasikan agenda atau jadwal kegiatan yang dibuat oleh admin.
 * Jadwal bersifat informatif dan dapat dilihat oleh semua role (tutor juga bisa melihat).
 *
 * STRUKTUR TABEL 'jadwals':
 *   - id        : Primary key
 *   - judul     : Judul/nama kegiatan atau agenda
 *   - deskripsi : Deskripsi detail kegiatan (opsional)
 *   - tanggal   : Tanggal pelaksanaan kegiatan
 *   - lokasi    : Tempat pelaksanaan (jika kosong, diasumsikan di lokasi sekolah)
 *
 * FITUR KHUSUS:
 *   - Auto-generate URL Google Maps dari field 'lokasi'
 *   - Fallback ke lokasi sekolah default dari konfigurasi jika lokasi tidak diisi
 *
 * SIDANG FAQ:
 *   Q: Apakah Jadwal terhubung dengan presensi tutor?
 *   A: Jadwal hanya bersifat informatif (pengumuman kegiatan). Presensi tutor
 *      dilakukan secara mandiri oleh tutor melalui fitur absensi foto, tidak
 *      terikat langsung ke jadwal. Ini desain yang fleksibel untuk lembaga bimbel.
 *
 *   Q: Mengapa 'tanggal' di-cast ke 'date:Y-m-d'?
 *   A: Cast ini memastikan Laravel membaca nilai dari DB sebagai Carbon date object
 *      dengan format 'Y-m-d'. Ini memudahkan manipulasi tanggal dan memastikan
 *      konsistensi format saat ditampilkan di view.
 */
class Jadwal extends Model
{
    /**
     * Kolom yang boleh diisi secara massal.
     *
     * @var array<string>
     */
    protected $fillable = [
        'judul',     // Nama/judul agenda kegiatan
        'deskripsi', // Deskripsi lebih lanjut tentang kegiatan (opsional)
        'tanggal',   // Tanggal pelaksanaan (format: YYYY-MM-DD)
        'lokasi',    // Lokasi kegiatan; kosong = di lokasi sekolah default
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
     * Memeriksa apakah lokasi jadwal adalah lokasi sekolah (default).
     * Method ini selalu mengembalikan true karena lokasi jadwal selalu
     * diasosiasikan dengan sekolah (bisa di sekolah atau mengikuti Google Maps).
     */
    public function isLokasiSekolah(): bool
    {
        return true;
    }

    /**
     * Mengembalikan ringkasan lokasi untuk ditampilkan di UI.
     * Jika lokasi diisi → tampilkan lokasi tersebut.
     * Jika tidak → tampilkan nama sekolah dari konfigurasi (config/lokasi.php).
     */
    public function lokasiRingkasan(): string
    {
        return $this->lokasi ?: (string) config('lokasi.sekolah_nama');
    }

    /**
     * Menghasilkan URL Google Maps untuk lokasi jadwal.
     *
     * Logika:
     *  - Jika ada lokasi custom → encode ke URL pencarian Google Maps
     *  - Jika tidak ada lokasi → gunakan URL tetap sekolah dari konfigurasi
     *  - Jika keduanya tidak ada → kembalikan null
     *
     * SIDANG FAQ:
     *   Q: Mengapa menggunakan Google Maps API dengan parameter 'query'?
     *   A: Parameter 'query' memungkinkan pencarian berdasarkan nama tempat atau
     *      alamat tanpa memerlukan API key khusus (menggunakan web search URL).
     *      rawurlencode() digunakan untuk encoding karakter spesial dalam nama lokasi.
     *
     * @return string|null URL Google Maps atau null jika tidak ada lokasi
     */
    public function lokasiPetaUrl(): ?string
    {
        if ($this->lokasi) {
            // Buat URL pencarian Google Maps dengan nama lokasi yang sudah di-encode
            return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($this->lokasi);
        }

        // Gunakan URL Maps sekolah yang sudah dikonfigurasi di config/lokasi.php
        $url = config('lokasi.sekolah_maps_url');

        return $url ? (string) $url : null;
    }
}
