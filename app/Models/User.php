<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Model User - Pengguna Sistem (Akun Login)
 *
 * Model ini merepresentasikan akun pengguna yang dapat masuk ke dalam sistem.
 * Satu User dapat memiliki satu profil detail berdasarkan perannya (admin/tutor/kepala_sekolah).
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Kolom yang boleh diisi secara massal (Mass Assignment Whitelist).
     * Melindungi dari serangan Mass Assignment di mana penyerang bisa menyisipkan
     * field berbahaya seperti 'role' atau 'is_admin' melalui form yang tidak aman.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nik',          // Nomor Induk Karyawan
        'nama_lengkap', // Nama lengkap pengguna
        'email',        // Alamat email pengguna
        'password',     // Password (akan di-hash otomatis oleh cast 'hashed')
        'role',         // Peran: 'admin', 'tutor', 'kepala_sekolah'
        'no_hp',        // Nomor handphone (opsional)
        'foto',         // Path foto profil (relatif dari storage/app/public/, misal: 'foto_karyawan/...')
        'is_active',    // Status akun: 1=aktif, 0=nonaktif
    ];

    /**
     * Atribut yang disembunyikan saat serialisasi ke JSON/array.
     * Mencegah data sensitif bocor ke response API atau view.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',      // Tidak boleh tampil di response apapun
        'remember_token', // Token "ingat saya" - sensitif untuk keamanan sesi
    ];

    /**
     * Relasi: User memiliki satu profil Admin (One-to-One / HasOne).
     * Hanya berlaku jika user memiliki role = 'admin'.
     *
     * @return HasOne
     */
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    /**
     * Relasi: User memiliki satu profil Tutor (One-to-One / HasOne).
     * Hanya berlaku jika user memiliki role = 'tutor'.
     * Digunakan untuk mengakses detail tutor: $user->tutor->jabatan
     *
     * @return HasOne
     */
    public function tutor()
    {
        return $this->hasOne(Tutor::class);
    }

    /**
     * Relasi: User memiliki satu profil Kepala Sekolah (One-to-One / HasOne).
     * Hanya berlaku jika user memiliki role = 'kepala_sekolah'.
     *
     * @return HasOne
     */
    public function kepala_sekolah()
    {
        return $this->hasOne(Kepala_Sekolah::class);
    }

    /**
     * Relasi: User memiliki satu profil Mahasiswa Magang (One-to-One / HasOne).
     * Hanya berlaku jika user memiliki role = 'magang'.
     *
     * @return HasOne
     */
    public function magang()
    {
        return $this->hasOne(Magang::class);
    }

    /**
     * Relasi: User memiliki satu profil Murid (One-to-One / HasOne).
     * Hanya berlaku jika user memiliki role = 'murid' (atau 'siswa' legacy).
     *
     * @return HasOne
     */
    public function murid()
    {
        return $this->hasOne(Murid::class, 'user_id');
    }

    /**
     * Alias relasi murid untuk backward compatibility.
     *
     * @return HasOne
     */
    public function siswa()
    {
        return $this->murid();
    }

    /**
     * Mendefinisikan casting (konversi tipe data) untuk atribut tertentu.
     * Cast ini otomatis diterapkan saat membaca/menulis nilai atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // Otomatis dikonversi ke Carbon instance
            'password' => 'hashed',            // Otomatis di-hash saat di-set (tanpa bcrypt manual)
        ];
    }

    /**
     * Accessor: Mengembalikan status aktif pengguna dengan dukungan backward compatibility.
     *
     * Accessor ini menangani kasus di mana database LAMA belum memiliki kolom 'is_active'.
     * Daripada error, sistem menganggap semua pengguna aktif jika kolom tidak ada.
     *
     * SIDANG FAQ:
     *   Q: Apa itu Accessor di Eloquent?
     *   A: Accessor adalah method yang otomatis dipanggil saat mengakses atribut model.
     *      Format nama: get{NamaAtribut}Attribute. Nilai yang di-return itulah yang
     *      akan dilihat oleh kode yang mengakses $user->is_active.
     *
     *   Q: Mengapa perlu cek Schema::hasColumn?
     *   A: Untuk kompatibilitas dengan database versi lama yang mungkin belum
     *      memiliki kolom tersebut. Ini menghindari error saat sistem di-upgrade.
     *
     * @param  mixed  $value  Nilai mentah dari database
     * @return bool
     */
    public function getIsActiveAttribute($value)
    {
        // Jika kolom is_active belum ada di tabel (database lama), anggap semua pengguna aktif
        if (! Schema::hasColumn('users', 'is_active')) {
            return true;
        }

        // Cast ke boolean: 1 = true (aktif), 0 = false (nonaktif)
        return (bool) $value;
    }

    /**
     * Accessor: Mengembalikan URL foto profil dengan backward compatibility.
     */
    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->foto) {
            return null;
        }

        if (str_starts_with($this->foto, 'http://') || str_starts_with($this->foto, 'https://')) {
            return $this->foto;
        }

        if (str_starts_with($this->foto, 'storage/')) {
            return asset($this->foto);
        }

        if (file_exists(public_path($this->foto))) {
            return asset($this->foto);
        }

        return asset('storage/'.ltrim($this->foto, '/'));
    }

    /**
     * Relasi ke Push Subscription perangkat pengguna.
     */
    public function pushSubscriptions()
    {
        return $this->hasMany(PushSubscription::class, 'user_id');
    }

    /**
     * Dapatkan nomor WhatsApp kontak admin resmi (Single Source of Truth).
     *
     * Alur Mitigasi Multi-Admin & Hotline:
     * 1. Menggunakan nomor hotline resmi institusi dari config('app.admin_wa') jika diset.
     * 2. Jika config kosong, mencari admin dengan akun aktif (is_active = 1) pertama yang memiliki nomor HP.
     * 3. Jika tidak ada, menggunakan fallback darurat resmi PKBM.
     *
     * @return string Nomor WhatsApp terformat internasional (diawali 62)
     */
    public static function getAdminWaContact(): string
    {
        $configWa = config('app.admin_wa');
        if (! empty($configWa)) {
            return self::formatWhatsappNumber((string) $configWa);
        }

        $activeAdmin = self::where('role', 'admin')
            ->where('is_active', 1)
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '')
            ->orderBy('id', 'asc')
            ->first();

        if ($activeAdmin && ! empty($activeAdmin->no_hp)) {
            return self::formatWhatsappNumber($activeAdmin->no_hp);
        }

        return '6285156452939';
    }

    /**
     * Dapatkan seluruh daftar kontak admin yang aktif (berguna untuk opsi multi-admin).
     *
     * @return Collection<int, array{id: int, nama: string, no_hp: string, wa_number: string}>
     */
    public static function getActiveAdminContacts(): Collection
    {
        return self::where('role', 'admin')
            ->where('is_active', 1)
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($admin) {
                return [
                    'id' => $admin->id,
                    'nama' => $admin->nama_lengkap ?? $admin->name,
                    'no_hp' => $admin->no_hp,
                    'wa_number' => self::formatWhatsappNumber($admin->no_hp),
                ];
            });
    }

    /**
     * Format nomor HP lokal Indonesia (08...) ke format WhatsApp internasional (628...).
     */
    public static function formatWhatsappNumber(?string $phone): string
    {
        if (empty($phone)) {
            return '6285156452939';
        }

        $cleaned = preg_replace('/[^0-9]/', '', (string) $phone);
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62'.substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '8')) {
            $cleaned = '62'.$cleaned;
        }

        return $cleaned;
    }
}
