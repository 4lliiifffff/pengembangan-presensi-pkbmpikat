<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\Concerns\ResolvesAdmin;
use App\Http\Controllers\Kepsek\Concerns\ResolvesKepsek;
use App\Http\Controllers\Tutor\Concerns\ResolvesTutor;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    use ResolvesAdmin, ResolvesKepsek, ResolvesTutor;

    public function index()
    {
        $user = Auth::user();

        $hadirCount = 0;
        $izinCount = 0;

        if ($user->role === 'tutor' && $user->tutor) {
            $tutor = $user->tutor;

            $hadirCount = Presensi::where('tutor_id', $tutor->id)->where('status', 'hadir')->count();
            $izinCount = Presensi::where('tutor_id', $tutor->id)->where('status', 'izin')->count();
        }

        if ($user->role === 'kepala_sekolah') {
            return view('kepsek.profil');
        }

        if ($user->role === 'admin') {
            return view('admin.profil');
        }

        return view('tutor.profil', compact('hadirCount', 'izinCount'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Menerapkan Standard Validation, tambahkan atau modifikasi sesuai kebutuhan jika kelak ingin lebih spesifik.
        $request->validate([
            'nik' => 'nullable|string|max:20|unique:users,nik,'.$user->id,
            'nama_lengkap' => 'required|string|max:50',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only(['nama_lengkap', 'email', 'no_hp']);
        if ($request->has('nik')) {
            $data['nik'] = $request->nik;
        }

        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($user->foto) {
                $cleanPath = ltrim(str_replace('storage/', '', $user->foto), '/');
                if (Storage::disk('public')->exists($cleanPath)) {
                    Storage::disk('public')->delete($cleanPath);
                }
                if (File::exists(public_path($user->foto))) {
                    File::delete(public_path($user->foto));
                }
            }
            // Simpan foto menggunakan Laravel Storage disk 'public'
            $file = $request->file('foto');
            $filename = 'foto_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('uploads/foto_karyawan', $file, $filename);
            $data['foto'] = $path;
        }

        $user->update($data);

        // Sinkronisasi data detail berdasarkan role
        if ($user->role === 'tutor') {
            $tutor = $this->resolveTutor();
            if ($tutor) {
                $tutorData = $data;
                if ($request->has('alamat')) {
                    $tutorData['alamat'] = $request->alamat;
                }
                $tutor->update($tutorData);
            }
        } elseif ($user->role === 'kepala_sekolah') {
            $kepsek = $this->resolveKepsek();
            if ($kepsek) {
                $kepsek->update($data);
            }
        } elseif ($user->role === 'admin') {
            $admin = $this->resolveAdmin();
            if ($admin) {
                $admin->update($data);
            }
        }

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function updatePassword(Request $request)
    {
        // Validasi password standar: min 8 karakter dan perlu konfirmasi.
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->with('warning', 'Kata sandi lama tidak sesuai!');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Kata sandi berhasil diperbarui!');
    }
}
