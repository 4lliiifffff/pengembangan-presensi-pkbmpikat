<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TutorExport;
use App\Exports\TutorTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\TutorImport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search') ?? $request->input('q');
        $status = $request->query('status', 'aktif');
        $role = $request->query('role');

        $karyawanQuery = User::query();

        if (Schema::hasColumn('users', 'role')) {
            $karyawanQuery->whereIn('role', ['admin', 'tutor', 'kepala_sekolah']);
        }

        if ($status !== 'semua' && Schema::hasColumn('users', 'is_active')) {
            $karyawanQuery->where('is_active', $status === 'aktif' ? 1 : 0);
        }

        if ($role && Schema::hasColumn('users', 'role')) {
            $karyawanQuery->where('role', $role);
        }

        if ($search) {
            $karyawanQuery->where(function ($q) use ($search) {
                if (Schema::hasColumn('users', 'nama_lengkap')) {
                    $q->where('nama_lengkap', 'like', "%{$search}%");
                }
                if (Schema::hasColumn('users', 'name')) {
                    $q->orWhere('name', 'like', "%{$search}%");
                }
                if (Schema::hasColumn('users', 'nik')) {
                    $q->orWhere('nik', 'like', "%{$search}%");
                }
                if (Schema::hasColumn('users', 'no_hp')) {
                    $q->orWhere('no_hp', 'like', "%{$search}%");
                }
            });
        }

        $karyawan = $karyawanQuery->latest('id')->paginate(15)->withQueryString();

        $allUsers = User::whereIn('role', ['admin', 'tutor', 'kepala_sekolah'])->get();
        $total = $allUsers->count();
        $hasIsActive = Schema::hasColumn('users', 'is_active');
        $aktif = $hasIsActive ? $allUsers->where('is_active', 1)->count() : $total;
        $nonaktif = $hasIsActive ? $allUsers->where('is_active', 0)->count() : 0;
        $tutorCount = $allUsers->where('role', 'tutor')->count();

        return view('admin.karyawan.index', compact(
            'karyawan',
            'total',
            'aktif',
            'nonaktif',
            'tutorCount',
            'status',
            'role'
        ));
    }

    public function create()
    {
        return view('admin.karyawan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required',
            'nik' => 'required|unique:users',
            'password' => 'required',
            'role' => 'required',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $generatedEmail = null;
        if (Schema::hasColumn('users', 'email')) {
            $nik = (string) $request->nik;
            $generatedEmail = strtolower(preg_replace('/\s+/', '', $nik)).'@local.test';
        }

        $data = [
            'nama_lengkap' => $request->nama_lengkap,
            'name' => $request->nama_lengkap,
            'nik' => $request->nik,
            'email' => $generatedEmail,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'is_active' => 1,
            'no_hp' => $request->no_hp,
        ];

        // Upload foto jika ada
        if ($request->hasFile('foto') && Schema::hasColumn('users', 'foto')) {
            $file = $request->file('foto');
            $filename = 'foto_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('uploads/foto_karyawan', $file, $filename);
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
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $payload = $request->only(['nama_lengkap', 'nik', 'role', 'no_hp']);
        if ($request->filled('nama_lengkap')) {
            $payload['name'] = $request->nama_lengkap;
        }
        if (Schema::hasColumn('users', 'email') && $request->filled('nik')) {
            $nik = (string) $request->nik;
            $payload['email'] = strtolower(preg_replace('/\s+/', '', $nik)).'@local.test';
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
            $path = Storage::disk('public')->putFileAs('uploads/foto_karyawan', $file, $filename);
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
