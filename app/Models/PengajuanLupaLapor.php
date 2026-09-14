<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model PengajuanLupaLapor — Pengajuan Lupa Lapor (Retroactive Attendance Request)
 *
 * Model ini merepresentasikan pengajuan presensi susulan/retroaktif oleh Tutor
 * ketika mereka lupa melakukan presensi digital pada hari mengajar.
 *
 * ALUR WORKFLOW:
 *   1. Tutor mengajukan -> status 'pending'
 *   2. Kepala Sekolah meninjau (Setujui / Tolak)
 *   3. Jika 'disetujui' -> Otomatis merekam (upsert) data presensi di tabel 'presensis'
 */
class PengajuanLupaLapor extends Model
{
    /**
     * Nama tabel eksplisit dalam Bahasa Indonesia baku.
     *
     * @var string
     */
    protected $table = 'pengajuan_lupa_lapor';

    /**
     * Kolom yang boleh diisi secara massal (Mass Assignment Whitelist).
     *
     * @var array<string>
     */
    protected $fillable = [
        'tutor_id',       // ID tutor yang mengajukan
        'siswa_id',       // ID siswa yang diajar
        'tanggal',        // Tanggal mengajar retroaktif
        'jam_mulai',      // Jam mulai mengajar (format HH:MM)
        'jam_selesai',    // Jam selesai mengajar (format HH:MM)
        'alasan',         // Alasan lupa lapor
        'status',         // Status persetujuan: 'pending', 'disetujui', 'ditolak'
        'catatan_kepsek', // Catatan/alasan tanggapan dari Kepala Sekolah
    ];

    /**
     * Relasi: Pengajuan dimiliki oleh satu Tutor (BelongsTo).
     */
    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    /**
     * Relasi: Pengajuan terkait dengan satu Siswa (BelongsTo).
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
