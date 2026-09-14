<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Lapor_Lapor — Pengajuan Lupa Lapor (Manual Attendance Report)
 *
 * Model ini merepresentasikan pengajuan laporan kehadiran manual yang dilakukan oleh
 * tutor ketika mereka lupa melakukan presensi digital (absen masuk/pulang) pada hari
 * yang sudah lewat.
 *
 * KONSEP FITUR "LUPA LAPOR":
 *   Sistem presensi utama mensyaratkan tutor melakukan absen foto pada hari mengajar.
 *   Namun, ada kalanya tutor lupa atau ada kendala teknis. Fitur "Lupa Lapor" memungkinkan
 *   tutor mengajukan laporan retroaktif (untuk tanggal yang sudah lewat) dengan menyertakan:
 *   - Data siswa yang diajar
 *   - Tanggal mengajar
 *   - Jam mulai dan jam selesai
 *   - Alasan mengapa tidak lapor tepat waktu
 *
 * ALUR PROSES:
 *   Tutor mengajukan → Data tersimpan → Kepala Sekolah dapat melihat & mengelola
 *
 * NAMA TABEL EKSPLISIT:
 *   Laravel mengkonversi 'Lapor_Lapor' menjadi 'lapor_lapors' secara default,
 *   yang tidak sesuai dengan nama tabel yang di-generate migration ('lapor__lapors').
 *   Maka $table perlu di-set secara eksplisit.
 *
 * STRUKTUR TABEL 'lapor__lapors' (double underscore dari migration):
 *   - id          : Primary key
 *   - tutor_id    : Foreign key ke tabel tutors
 *   - siswa_id    : Foreign key ke tabel siswas
 *   - tanggal     : Tanggal mengajar yang dilaporkan (tidak boleh masa depan)
 *   - jam_mulai   : Jam mulai mengajar
 *   - jam_selesai : Jam selesai mengajar (harus setelah jam_mulai)
 *   - alasan      : Alasan lupa lapor (minimal 10 karakter)
 *
 * SIDANG FAQ:
 *   Q: Apakah pengajuan lupa lapor langsung masuk ke rekap presensi?
 *   A: Tidak. Fitur lupa lapor hanya mencatat pengajuan secara terpisah di tabel
 *      lapor__lapors. Data ini dapat dilihat oleh Kepala Sekolah untuk diverifikasi
 *      secara manual. Keputusan untuk memasukkannya ke presensi resmi ada pada admin.
 *
 *   Q: Mengapa tanggal tidak boleh masa depan?
 *   A: Lupa lapor hanya untuk kehadiran yang sudah terjadi. Validasi 'before_or_equal:today'
 *      diterapkan di controller untuk memastikan hal ini.
 *
 *   Q: Apa perbedaan fitur ini dengan presensi biasa?
 *   A: Presensi biasa dilakukan real-time dengan foto pada hari mengajar.
 *      Lupa lapor adalah pengajuan retroaktif tanpa foto, sebagai mekanisme koreksi.
 */
class Lapor_Lapor extends Model
{
    /**
     * Nama tabel eksplisit (double underscore karena hasil migration memiliki nama unik).
     * Tanpa ini, Laravel akan mencari tabel 'lapor_lapors' (single underscore) yang tidak ada.
     *
     * @var string
     */
    protected $table = 'lapor__lapors';

    /**
     * Kolom yang boleh diisi secara massal (Mass Assignment Whitelist).
     *
     * @var array<string>
     */
    protected $fillable = [
        'tutor_id',    // ID tutor yang mengajukan laporan (foreign key ke tutors)
        'siswa_id',    // ID siswa yang diajar (foreign key ke siswas)
        'tanggal',     // Tanggal mengajar yang dilaporkan (tidak boleh masa depan)
        'jam_mulai',   // Jam mulai mengajar (format: HH:MM)
        'jam_selesai', // Jam selesai mengajar (harus setelah jam_mulai)
        'alasan',      // Alasan lupa tidak lapor (minimal 10 karakter)
    ];

    /**
     * Relasi: Pengajuan lupa lapor dimiliki oleh satu Tutor (BelongsTo).
     * Digunakan untuk mengakses data tutor dari pengajuan.
     * Contoh: $laporLapor->tutor->nama_lengkap
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    /**
     * Relasi: Pengajuan lupa lapor terkait dengan satu Siswa (BelongsTo).
     * Digunakan untuk menampilkan nama siswa yang diajar dalam pengajuan.
     * Contoh: $laporLapor->siswa->nama_siswa
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
