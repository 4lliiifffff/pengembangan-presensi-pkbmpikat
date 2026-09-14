<?php

namespace App\Http\Controllers\Kepsek\Concerns;

use App\Models\Kepala_Sekolah;
use Illuminate\Support\Facades\Schema;

trait ResolvesKepsek
{
    protected function resolveKepsek(): ?Kepala_Sekolah
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $kepsek = Kepala_Sekolah::where('user_id', $user->id)->first();
        if ($kepsek) {
            return $kepsek;
        }

        $nik = (string) ($user->nik ?? '');
        $email = (string) ($user->email ?? '');
        $hasNik = Schema::hasColumn('kepala__sekolahs', 'nik');
        $hasEmail = Schema::hasColumn('kepala__sekolahs', 'email');

        if (($hasNik && $nik !== '') || ($hasEmail && $email !== '')) {
            $kepsek = Kepala_Sekolah::query()
                ->where(fn ($q) => $hasNik && $nik !== '' ? $q->where('nik', $nik) : $q)
                ->where(fn ($q) => $hasEmail && $email !== '' ? $q->orWhere('email', $email) : $q)
                ->first();
        }

        if ($kepsek) {
            $kepsek->user_id = $user->id;
            $kepsek->save();

            return $kepsek;
        }

        if ($nik === '' && $email === '') {
            return null;
        }

        $name = (string) (($user->nama_lengkap ?? $user->name) ?? 'Kepala Sekolah');
        $genEmail = $email ?: strtolower(preg_replace('/\s+/', '', $nik)).'@local.test';

        $payload = [
            'user_id' => $user->id,
            'nama_lengkap' => $name,
            'email' => $genEmail,
            'no_hp' => $user->no_hp ?? null,
            'foto' => $user->foto ?? null,
        ];

        if ($hasNik) {
            $payload['nik'] = $nik ?: ('KEPSEK-'.$user->id);
        }

        return Kepala_Sekolah::create($payload);
    }
}
