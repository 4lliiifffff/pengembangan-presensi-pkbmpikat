<?php

namespace App\Http\Controllers\Tutor\Concerns;

use App\Models\Tutor;
use Illuminate\Support\Facades\Schema;

trait ResolvesTutor
{
    protected function resolveTutor(): ?Tutor
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $tutor = Tutor::where('user_id', $user->id)->first();
        if ($tutor) {
            return $tutor;
        }

        $nik = (string) ($user->nik ?? '');
        $email = (string) ($user->email ?? '');

        $hasTutorNik = Schema::hasColumn('tutors', 'nik');
        $hasTutorEmail = Schema::hasColumn('tutors', 'email');

        if (($hasTutorNik && $nik !== '') || ($hasTutorEmail && $email !== '')) {
            $tutor = Tutor::query()
                ->where(function ($q) use ($nik, $email, $hasTutorNik, $hasTutorEmail) {
                    if ($hasTutorNik && $nik !== '') {
                        $q->where('nik', $nik);
                    }
                    if ($hasTutorEmail && $email !== '') {
                        $q->orWhere('email', $email);
                    }
                })
                ->first();
        } else {
            $tutor = null;
        }

        if ($tutor) {
            $tutor->user_id = $user->id;
            $tutor->save();

            return $tutor;
        }

        if ($nik === '' && $email === '') {
            return null;
        }

        $name = (string) (($user->nama_lengkap ?? $user->name) ?? 'Tutor');
        if ($email === '') {
            $email = strtolower(preg_replace('/\s+/', '', $nik)).'@local.test';
        }

        $payload = [
            'user_id' => $user->id,
            'nama_lengkap' => $name,
            'email' => $email,
            'no_hp' => $user->no_hp ?? null,
            'foto' => $user->foto ?? null,
        ];

        if ($hasTutorNik) {
            $payload['nik'] = $nik !== '' ? $nik : ('TUTOR-'.$user->id);
        }

        if (! $hasTutorEmail) {
            unset($payload['email']);
        }

        return Tutor::create($payload);
    }
}
