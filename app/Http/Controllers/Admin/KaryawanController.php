<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TutorExport;
use App\Exports\TutorTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\TutorImport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search') ?? $request->input('q');
        $status = $request->query('status', 'semua');
        $role = $request->query('role', 'semua');

        $karyawanQuery = User::with(['murid.relKelas.jenjangPaket']);

        if ($role && $role !== 'semua') {
            if ($role === 'murid' || $role === 'siswa') {
                $karyawanQuery->whereIn('role', ['murid', 'siswa']);
            } else {
                $karyawanQuery->where('role', $role);
            }
        }

        if ($status !== 'semua' && Schema::hasColumn('users', 'is_active')) {
            $karyawanQuery->where('is_active', $status === 'aktif' ? 1 : 0);
        }

        if ($search) {
            $karyawanQuery->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        $karyawan = $karyawanQuery->latest('id')->paginate(15)->withQueryString();

        // Hitung statistik untuk badge dan tab peran secara efisien di level database
        $total = User::count();
        $aktif = User::where('is_active', 1)->count();
        $nonaktif = $total - $aktif;

        $roleCounts = User::selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role');

        $tutorCount = (int) ($roleCounts['tutor'] ?? 0);
        $muridCount = (int) (($roleCounts['murid'] ?? 0) + ($roleCounts['siswa'] ?? 0));
        $siswaCount = $muridCount;
        $magangCount = (int) ($roleCounts['magang'] ?? 0);
        $adminCount = (int) ($roleCounts['admin'] ?? 0);
        $kepsekCount = (int) ($roleCounts['kepala_sekolah'] ?? 0);

        return view('admin.karyawan.index', compact(
            'karyawan',
            'total',
            'aktif',
            'nonaktif',
            'tutorCount',
            'muridCount',
            'siswaCount',
            'magangCount',
            'adminCount',
            'kepsekCount',
            'status',
            'role'
        ));
    }

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'password' => Hash::make('password123'),
        ]);

        return back()->with('success', "Password untuk akun {$user->nama_lengkap} ({$user->role}) berhasil direset menjadi 'password123'.");
    }

    public function create()
    {
        return view('admin.karyawan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|string|max:50|unique:users,nik',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,tutor,kepala_sekolah',
            'no_hp' => 'nullable|string|max:25',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $email = $request->email;
        if (empty($email)) {
            $nikClean = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $request->nik));
            $email = $nikClean.'@pkbmpikat.com';
        }

        $data = [
            'nama_lengkap' => $request->nama_lengkap,
            'nik' => $request->nik,
            'email' => $email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'is_active' => 1,
            'no_hp' => $request->no_hp,
        ];

        // Upload foto jika ada
        if ($request->hasFile('foto') && Schema::hasColumn('users', 'foto')) {
            $file = $request->file('foto');
            $filename = 'foto_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('foto_karyawan', $file, $filename);
            $data['foto'] = $path;
        }

        // Hapus field yang tidak ada di kolom tabel users
        foreach (array_keys($data) as $key) {
            if (! Schema::hasColumn('users', $key)) {
                unset($data[$key]);
            }
        }

        User::create($data);

        return redirect()->route('admin.karyawan.index')->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data = User::findOrFail($id);

        return view('admin.karyawan.edit', ['karyawan' => $data]);
    }

    public function update(Request $request, $id)
    {
        $karyawan = User::findOrFail($id);

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|string|max:50|unique:users,nik,'.$karyawan->id,
            'email' => 'nullable|email|max:255|unique:users,email,'.$karyawan->id,
            'role' => 'required|in:admin,tutor,kepala_sekolah',
            'no_hp' => 'nullable|string|max:25',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $payload = $request->only(['nama_lengkap', 'nik', 'role', 'no_hp']);

        if ($request->filled('email')) {
            $payload['email'] = $request->email;
        }

        if ($request->filled('password')) {
            $payload['password'] = bcrypt($request->password);
        }

        // Upload foto baru jika ada
        if ($request->hasFile('foto') && Schema::hasColumn('users', 'foto')) {
            // Hapus foto lama jika ada
            if ($karyawan->foto) {
                $cleanPath = ltrim(str_replace('storage/', '', $karyawan->foto), '/');
                if (Storage::disk('public')->exists($cleanPath)) {
                    Storage::disk('public')->delete($cleanPath);
                }
                if (File::exists(public_path($karyawan->foto))) {
                    File::delete(public_path($karyawan->foto));
                }
            }
            $file = $request->file('foto');
            $filename = 'foto_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('foto_karyawan', $file, $filename);
            $payload['foto'] = $path;
        }

        foreach (array_keys($payload) as $key) {
            if (! Schema::hasColumn('users', $key)) {
                unset($payload[$key]);
            }
        }

        $karyawan->update($payload);

        return redirect()->route('admin.karyawan.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return back();
    }

    public function status(Request $request, $id)
    {
        $data = User::findOrFail($id);
        if (! Schema::hasColumn('users', 'is_active')) {
            return redirect()
                ->route('admin.karyawan.index')
                ->with('warning', 'Kolom status (is_active) belum ada di tabel users. Status tidak bisa diubah.');
        }

        $newStatus = $request->has('is_active')
            ? (int) $request->is_active
            : ($data->is_active ? 0 : 1);

        $data->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.karyawan.index')
            ->with('success', "Status karyawan {$data->name} berhasil {$statusText}.");
    }

    public function exportExcel()
    {
        return Excel::download(
            new TutorExport,
            'Data_Karyawan_Tutor_PKBM_Pikat_'.date('Ymd').'.xlsx'
        );
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new TutorTemplateExport,
            'Template_Import_Tutor_Karyawan_PKBM_Pikat.xlsx'
        );
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file_excel.required' => 'Silakan pilih berkas spreadsheet Excel/CSV terlebih dahulu.',
            'file_excel.mimes' => 'Format berkas harus berekstensi .xlsx, .xls, atau .csv.',
            'file_excel.max' => 'Ukuran berkas maksimal adalah 5MB.',
        ]);

        $import = new TutorImport;
        Excel::import($import, $request->file('file_excel'));

        return redirect()->route('admin.karyawan.index')->with(
            'success',
            "Impor data tutor/karyawan berhasil: {$import->importedCount} akun baru dibuat, {$import->updatedCount} akun diperbarui, {$import->skippedCount} data dilewati."
        );
    }
}
