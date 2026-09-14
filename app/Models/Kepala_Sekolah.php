<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kepala_Sekolah extends Model
{
    // buat mass assigment
    protected $fillable =
        [
            'user_id',
            'nik',
            'nama_lengkap',
            'email',
            'no_hp',
            'foto',
        ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
