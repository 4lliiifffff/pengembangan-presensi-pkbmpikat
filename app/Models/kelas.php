<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class kelas extends Model
{
    // buat mass assigment
    protected $fillable = [
        'nama_kelas',
    ];

    public function siswas()
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }
}
