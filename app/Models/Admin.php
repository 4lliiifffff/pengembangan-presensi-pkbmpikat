<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admin extends Model
{
    protected $table = 'admin';

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
