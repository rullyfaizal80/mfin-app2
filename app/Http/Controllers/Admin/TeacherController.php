<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    /**
     * Menampilkan halaman daftar guru/pendidik.
     */
    public function index(Request $request)
    {
        $searchTerm = $request->query('search');
        $perPage = $request->query('perPage', 10);

        $query = DB::table('sis_user')
            ->join('sis_teacher', 'sis_user.id', '=', 'sis_teacher.id')
            ->where('sis_user.is_teacher', 'yes')
            ->select(
                'sis_user.id',
                'sis_teacher.nik',
                'sis_user.fullname',
                'sis_user.mobile_phone',
                'sis_user.home_phone',
                'sis_user.email'
            );

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('sis_teacher.nik', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sis_user.fullname', 'like', '%' . $searchTerm . '%');
            });
        }

        $query->orderBy('sis_user.fullname', 'asc');
        $teachers = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('admin.teacher._teacher_table', ['teachers' => $teachers]);
        }

        return view('admin.teacher.index', [
            'teachers' => $teachers,
            'searchTerm' => $searchTerm,
            'perPage' => $perPage
        ]);
    }

    /**
     * Menampilkan form untuk membuat guru baru.
     */
    public function create()
    {
        return view('admin.teacher.create');
    }

    /**
     * Menyimpan data guru baru ke database (2 tabel).
     */
    /**
     * [MODIFIKASI] Menyimpan data guru baru ke database (2 tabel).
     * PERBAIKAN: Menghapus validasi 'unique' pada NIK.
     */
    public function store(Request $request)
    {
        // 1. Validasi Data
        $request->validate([
            'fullname' => 'required|string|max:45',
            'nik' => [ // NIK/NIP
                'required', 'string', 'max:45', 'alpha_dash',
                // [DIHAPUS] Rule::unique('sis_teacher', 'nik'),
                // [DIHAPUS] Rule::unique('sis_user', 'username')
            ],
            'email'    => [
                'nullable', 'email', 'max:45',
                Rule::unique('sis_user', 'email') // Email tetap harus unik
            ],
        ], [
            'nik.alpha_dash' => 'NIK/NIP hanya boleh berisi huruf, angka, strip (-), dan underscore (_).',
            // 'nik.unique' dihapus karena aturannya dihapus
            'email.unique' => 'Email ini sudah terdaftar.',
        ]);

        // 2. Gunakan Transaksi Database (Tidak ada perubahan di sini)
        DB::transaction(function () use ($request) {
            
            // 3a. Simpan ke 'sis_user'
            $newUserId = DB::table('sis_user')->insertGetId([
                'fullname' => $request->fullname,
                'nickname' => $request->nickname,
                'username' => $request->nik, 
                'password' => Hash::make($request->nik),
                'placeofbirth' => $request->placeofbirth,
                'dateofbirth' => $request->dateofbirth,
                'gender' => $request->gender,
                'street' => $request->street,
                'city' => $request->city,
                'province' => $request->province,
                'country' => $request->country,
                'postalcode' => $request->postalcode,
                'home_phone' => $request->home_phone,
                'mobile_phone' => $request->mobile_phone,
                'email' => $request->email,
                'religion' => $request->religion,
                'is_active' => $request->is_active,
                'is_teacher' => 'yes',
                'is_parent' => 'no', 
                'is_student' => 'no', 
                'is_admin' => 'no',
                'is_educator' => 'no',
                'is_company' => 'no',
                'created' => now(),
                'updated' => now(),
                'update_by' => session('user_id'), 
            ]);

            // 3b. Simpan ke 'sis_teacher' (Tidak ada perubahan di sini)
            DB::table('sis_teacher')->insert([
                'id' => $newUserId,
                'nik' => $request->nik,
                'emp_status' => $request->emp_status ?? 'permanent',
                'att_id' => $request->att_id ?? 0,
                'grade' => $request->grade ?? '',
                'license_no' => $request->license_no ?? '',
                'foundation_license_no' => $request->foundation_license_no ?? '',
                'career_objective' => $request->career_objective ?? '',
                'skill' => $request->skill ?? '',
                'reference' => $request->reference ?? '',
                'note' => $request->note ?? '',
                'training' => '', 
                'organization' => '',
                'personal_identity' => '',
                'iduser' => 0, 
                'created' => now(),
                'updated' => now(),
                'update_by' => session('user_id'),
            ]);
        }); // Transaksi Selesai

        // 4. Redirect kembali
        return redirect()->route('teacher.index')
                         ->with('success', 'Data pendidik baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit data pendidik (Personal Detail).
     */
    public function edit(string $id)
    {
        $teacher = DB::table('sis_user')
                    ->leftJoin('sis_teacher', 'sis_user.id', '=', 'sis_teacher.id')
                    ->where('sis_user.id', $id)
                    ->where('sis_user.is_teacher', 'yes')
                    ->select('sis_user.*', 'sis_teacher.*', 'sis_user.id as user_id')
                    ->first();

        if (!$teacher) {
            return redirect()->route('teacher.index')->with('error', 'Data pendidik tidak ditemukan.');
        }

        return view('admin.teacher.edit', [
            'teacher' => $teacher
        ]);
    }

    /**
     * [VERSI FINAL] Memperbarui data pendidik di database (Personal Detail).
     */
    /**
     * [MODIFIKASI FINAL] Memperbarui data pendidik di database (Personal Detail).
     * PERBAIKAN: Menghapus validasi 'unique' pada NIK.
     */
    public function update(Request $request, string $id)
    {
        // 1. Validasi Data
        $request->validate([
            'fullname' => 'required|string|max:45',
            'nik' => [ // NIK/NIP
                'required', 'string', 'max:45', 'alpha_dash',
                // [DIHAPUS] Rule::unique('sis_teacher', 'nik')->ignore($id)
            ],
            'email'    => [
                'nullable', 'email', 'max:45',
                Rule::unique('sis_user', 'email')->ignore($id) // Email tetap harus unik
            ],
        ], [
            // Pesan Error
            'nik.alpha_dash' => 'NIK/NIP hanya boleh berisi huruf, angka, strip (-), dan underscore (_).',
            // 'nik.unique' dihapus karena aturannya dihapus
            'email.unique' => 'Email ini sudah terdaftar.',
        ]);

        // 2. Gunakan Transaksi Database (Tidak ada perubahan di sini)
        DB::transaction(function () use ($request, $id) {
            
            // 3a. Update tabel 'sis_user'
            DB::table('sis_user')->where('id', $id)->update([
                'fullname' => $request->fullname,
                'nickname' => $request->nickname,
                'username' => $request->nik,
                'password' => Hash::make($request->nik),
                'placeofbirth' => $request->placeofbirth,
                'dateofbirth' => $request->dateofbirth,
                'gender' => $request->gender,
                'street' => $request->street,
                'city' => $request->city,
                'province' => $request->province,
                'country' => $request->country,
                'postalcode' => $request->postalcode,
                'home_phone' => $request->home_phone,
                'mobile_phone' => $request->mobile_phone,
                'email' => $request->email,
                'religion' => $request->religion,
                'is_active' => $request->is_active,
                'updated' => now(),
                'update_by' => session('user_id'),
            ]);

            // 3b. Update tabel 'sis_teacher'
            DB::table('sis_teacher')->updateOrInsert(
                ['id' => $id], 
                [ 
                    'nik' => $request->nik,
                    'emp_status' => $request->emp_status ?? 'permanent',
                    'att_id' => $request->att_id ?? 0,
                    'grade' => $request->grade ?? '',
                    'license_no' => $request->license_no ?? '',
                    'foundation_license_no' => $request->foundation_license_no ?? '',
                    'career_objective' => $request->career_objective ?? '',
                    'skill' => $request->skill ?? '',
                    'reference' => $request->reference ?? '',
                    'note' => $request->note ?? '',
                    'updated' => now(),
                    'update_by' => session('user_id'),
                    'training' => DB::raw('training'),
                    'organization' => DB::raw('organization'),
                    'personal_identity' => DB::raw('personal_identity')
                ]
            );

        }); // Transaksi Selesai

        // 4. Redirect kembali
        return redirect()->route('teacher.index')
                         ->with('success', 'Data pendidik berhasil diperbarui.');
    }

    /**
     * Menghapus data guru/pendidik dari database (6 tabel).
     */
    public function destroy($id)
    {
        if ($id == session('user_id')) {
            return redirect()->route('teacher.index')
                             ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        DB::transaction(function () use ($id) {
            DB::table('sis_education_level')->where('teacher_id', $id)->delete();
            DB::table('sis_work_experience')->where('teacher_id', $id)->delete();
            DB::table('sis_training')->where('teacher_id', $id)->delete();
            DB::table('sis_organization')->where('teacher_id', $id)->delete();
            DB::table('sis_teacher')->where('id', $id)->delete();
            DB::table('sis_user')->where('id', $id)->delete();
        });

        return redirect()->route('teacher.index')
                         ->with('success', 'Data pendidik berhasil dihapus.');
    }
}