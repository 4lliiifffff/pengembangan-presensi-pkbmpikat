<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Admin;
use Illuminate\Support\Facades\Schema;

trait ResolvesAdmin
{
    protected function resolveAdmin(): ?Admin
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $admin = Admin::where('user_id', $user->id)->first();
        if ($admin) {
            return $admin;
        }

        $nik = (string) ($user->nik ?? '');
        $email = (string) ($user->email ?? '');

        $hasAdminNik = Schema::hasColumn('admins', 'nik');
        $hasAdminEmail = Schema::hasColumn('admins', 'email');

        if (($hasAdminNik && $nik !== '') || ($hasAdminEmail && $email !== '')) {
            $admin = Admin::query()
                ->where(function ($q) use ($nik, $email, $hasAdminNik, $hasAdminEmail) {
                    if ($hasAdminNik && $nik !== '') {
                        $q->where('nik', $nik);
                    }
                    if ($hasAdminEmail && $email !== '') {
                        $q->orWhere('email', $email);
                    }
                })
                ->first();
        } else {
            $admin = null;
        }

        if ($admin) {
            $admin->user_id = $user->id;
            $admin->save();

            return $admin;
        }

        if ($nik === '' && $email === '') {
            return null;
        }

        $name = (string) (($user->nama_lengkap ?? $user->name) ?? 'Admin');
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

        if ($hasAdminNik) {
            $payload['nik'] = $nik !== '' ? $nik : ('ADMIN-'.$user->id);
        }

        if (! $hasAdminEmail) {
            unset($payload['email']);
        }

        return Admin::create($payload);
    }
}
