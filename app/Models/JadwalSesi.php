<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalSesi extends Model
{
    use HasFactory;

    protected $table = 'jadwal_sesis';

    protected $fillable = [
        'jadwal_rutin_id',
        'tutor_id',
        'siswa_id',
        'kategori_tutorial_id',
        'jadwal_kerja_id',
        'tanggal_rencana',
        'jam_masuk_rencana',
        'jam_pulang_rencana',
        'durasi_jam',
        'jenis_sesi',
        'tanggal_asli',
        'alasan_penggantian',
        'status',
        'presensi_id',
        'presensi_siswa_id',
        'status_kehadiran_siswa',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_rencana' => 'date',
            'tanggal_asli' => 'date',
            'durasi_jam' => 'decimal:2',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function jadwalRutin(): BelongsTo
    {
        return $this->belongsTo(JadwalRutin::class);
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kategoriTutorial(): BelongsTo
    {
        return $this->belongsTo(KategoriTutorial::class);
    }

    public function jadwalKerja(): BelongsTo
    {
        return $this->belongsTo(JadwalKerja::class);
    }

    public function presensi(): BelongsTo
    {
        return $this->belongsTo(Presensi::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeHariIni(Builder $query, ?string $date = null): Builder
    {
        $targetDate = $date ?? Carbon::now('Asia/Jakarta')->toDateString();

        return $query->whereDate('tanggal_rencana', $targetDate);
    }

    public function scopeTerjadwal(Builder $query): Builder
    {
        return $query->where('status', 'terjadwal');
    }

    public function scopePengganti(Builder $query): Builder
    {
        return $query->where('jenis_sesi', 'pengganti');
    }

    // ── Accessors & Helpers ────────────────────────────────────────────────────

    public function getJamMasukFormattedAttribute(): string
    {
        return substr((string) $this->jam_masuk_rencana, 0, 5);
    }

    public function getJamPulangFormattedAttribute(): string
    {
        return substr((string) $this->jam_pulang_rencana, 0, 5);
    }

    public function getJenisLabelAttribute(): string
    {
        return match ($this->jenis_sesi) {
            'pengganti' => 'Sesi Pengganti (Reschedule)',
            'tambahan' => 'Sesi Tambahan',
            default => $this->jadwal_rutin_id ? 'Sesi Rutin Mingguan' : 'Sesi Reguler',
        };
    }
}
