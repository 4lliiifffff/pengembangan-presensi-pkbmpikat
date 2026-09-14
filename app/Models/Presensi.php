<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Presensi — Representasi Data Kehadiran Tutor
 *
 * Model ini merepresentasikan satu sesi kehadiran (absensi) seorang tutor
 * ketika mengajar seorang siswa. Sistem presensi menggunakan pendekatan
 * "Clock In / Clock Out" dengan verifikasi foto pada setiap tahap.
 */
class Presensi extends Model
{
    /**
     * Daftar kolom yang boleh diisi secara massal (Mass Assignment Whitelist).
     * Kolom yang tidak ada di sini TIDAK BISA diisi melalui Presensi::create() atau update().
     *
     * @var array<string>
     */
    protected $fillable =
        [
            'tutor_id',       // ID tutor yang melakukan presensi
            'siswa_id',       // ID siswa yang diajar dalam sesi ini
            'tgl_presensi',   // Tanggal sesi mengajar berlangsung
            'jam_mulai',      // Jam tutor mulai mengajar (clock-in)
            'jam_selesai',    // Jam tutor selesai mengajar (clock-out) — null jika masih berjalan
            'foto_mulai',     // Path foto bukti absen masuk
            'foto_selesai',   // Path foto bukti absen pulang — null jika belum selesai
            'lokasi_mulai',   // Koordinat GPS / deskripsi lokasi saat masuk (opsional)
            'lokasi_selesai', // Koordinat GPS / deskripsi lokasi saat pulang (opsional)
            'status',          // Status: 'hadir', 'izin', atau 'alpha'
        ];

    /**
     * Relasi: Presensi dimiliki oleh satu Tutor (Many-to-One / BelongsTo).
     *
     * Dengan relasi ini, kita bisa mengakses data tutor dari presensi:
     *   $presensi->tutor->nama_lengkap
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    /**
     * Relasi: Presensi terkait dengan satu Siswa (Many-to-One / BelongsTo).
     *
     * Dengan relasi ini, kita bisa mengakses data siswa dari presensi:
     *   $presensi->siswa->nama_siswa
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * Local Query Scope: Filter presensi berdasarkan periode waktu.
     *
     * Scope ini memudahkan pembuatan query laporan tanpa harus menulis
     * logika filter berulang di setiap controller.
     *
     * PENGGUNAAN:
     *   Presensi::laporanNgajar('hari')       // Hari ini
     *   Presensi::laporanNgajar('minggu')     // Minggu ini
     *   Presensi::laporanNgajar('bulan')      // Bulan ini
     *   Presensi::laporanNgajar('tahun')      // Tahun ini
     *   Presensi::laporanNgajar('hari', '2024-01-15') // Tanggal tertentu
     *
     * SIDANG FAQ:
     *   Q: Apa itu Query Scope di Eloquent?
     *   A: Scope adalah method khusus di Model yang memperluas (extend) query builder
     *      dengan logika filter yang bisa digunakan kembali (reusable). Namanya diawali
     *      dengan 'scope', tapi dipanggil tanpa awalan tersebut.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $periode  Periode filter: 'hari', 'minggu', 'bulan', 'tahun'
     * @param  string|null  $tanggal  Tanggal spesifik untuk filter 'hari' (opsional)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLaporanNgajar($query, $periode = 'hari', $tanggal = null)
    {
        switch ($periode) {
            case 'hari':
                // Filter presensi untuk tanggal tertentu atau hari ini
                return $query->whereDate('tgl_presensi', $tanggal ?? now()->toDateString());
            case 'minggu':
                // Filter presensi untuk minggu berjalan (Senin s/d Minggu)
                return $query->whereBetween('tgl_presensi', [now()->startOfWeek(), now()->endOfWeek()]);
            case 'bulan':
                // Filter presensi untuk bulan dan tahun saat ini
                return $query->whereMonth('tgl_presensi', now()->month)->whereYear('tgl_presensi', now()->year);
            case 'tahun':
                // Filter presensi untuk tahun saat ini
                return $query->whereYear('tgl_presensi', now()->year);
            default:
                // Jika periode tidak dikenali, kembalikan query tanpa filter tambahan
                return $query;
        }
    }
}
