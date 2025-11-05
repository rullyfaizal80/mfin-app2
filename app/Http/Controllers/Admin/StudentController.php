<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Menampilkan halaman daftar siswa.
     */
    public function index(Request $request)
    {
        $searchTerm = $request->query('search');
        $perPage = $request->query('perPage', 10);

        // Query ini menggabungkan sis_user dan sis_student
        $query = DB::table('sis_user')
            ->join('sis_student', 'sis_user.id', '=', 'sis_student.id')
            ->where('sis_user.is_student', 'yes') // Hanya ambil siswa
            ->select(
                'sis_user.id',
                'sis_student.nis',
                'sis_user.fullname',
                'sis_user.dateofbirth',
                'sis_user.gender',
                'sis_user.mobile_phone',
                'sis_user.home_phone'
            );

        // Logika pencarian: berdasarkan NIS atau Nama
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('sis_student.nis', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sis_user.fullname', 'like', '%' . $searchTerm . '%');
            });
        }

        // Urutkan berdasarkan Nama (Fullname)
        $query->orderBy('sis_user.fullname', 'asc');

        $students = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('admin.student._student_table', ['students' => $students]);
        }

        return view('admin.student.index', [
            'students' => $students,
            'searchTerm' => $searchTerm,
            'perPage' => $perPage
        ]);
    }

    /**
     * [BARU] Menampilkan form untuk membuat siswa baru.
     */
    public function create()
    {
        // Ambil daftar user yang merupakan orang tua (sesuai kode CI2 Anda)
        $parents = DB::table('sis_user')
                    ->where('is_parent', 'yes')
                    ->orderBy('fullname', 'asc')
                    ->get(['id', 'fullname']);

        return view('admin.student.create', [
            'parents' => $parents
        ]);
    }

    /**
     * [MODIFIKASI] Menyimpan data siswa baru ke database (2 tabel).
     * Username & Password sekarang di-generate otomatis.
     */
    public function store(Request $request)
    {
        // 1. Validasi Data
        // Validasi username & password dihapus dari sini
        $request->validate([
            // Data sis_user
            'fullname' => 'required|string|max:45',
            'email'    => 'nullable|email|max:45|unique:sis_user,email',
            
            // Data sis_student
            // NIS sekarang juga harus unik di tabel sis_user (karena jadi username)
            'nis' => [
                'required',
                'string',
                'max:45',
                'alpha_dash', // Pastikan tidak ada spasi
                Rule::unique('sis_student', 'nis'),
                Rule::unique('sis_user', 'username') // Pastikan unik sebagai username juga
            ],
            'father_id' => 'nullable|integer',
            'mother_id' => 'nullable|integer',
            'parent_id' => 'nullable|integer',
        ], [
            // Pesan error kustom
            'nis.alpha_dash' => 'NIS hanya boleh berisi huruf, angka, strip (-), dan underscore (_).',
            'nis.unique' => 'NIS ini sudah terdaftar.',
            'email.unique' => 'Email ini sudah terdaftar.',
        ]);

        // 2. Siapkan Data
        $distancetoschool = $request->distancetoschool1 === 'other' 
            ? $request->distancetoschool2 
            : $request->distancetoschool1;
            
        $gotoschool_with = $request->gotoschool_with1 === 'other' 
            ? $request->gotoschool_with2 
            : $request->gotoschool_with1;

        // 3. Gunakan Transaksi Database
        DB::transaction(function () use ($request, $distancetoschool, $gotoschool_with) {
            
            // 3a. Simpan ke 'sis_user'
            $newUserId = DB::table('sis_user')->insertGetId([
                'fullname' => $request->fullname,
                'nickname' => $request->nickname,
                
                // [PERUBAHAN] Username & Password di-generate otomatis
                'username' => $request->nis, 
                'password' => Hash::make($request->nis),

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
                'is_student' => 'yes', // <-- Ini PENTING
                'is_admin' => 'no',
                'is_parent' => 'no',
                'is_teacher' => 'no',
                'is_educator' => 'no',
                
                'created' => now(),
                'updated' => now(),
                'update_by' => auth()->id(), 
            ]);

            // 3b. Simpan ke 'sis_student' menggunakan ID baru
            DB::table('sis_student')->insert([
                'id' => $newUserId,
                'nis' => $request->nis,
                'nin' => $request->nin,
                'father_id' => $request->father_id,
                'mother_id' => $request->mother_id,
                'parent_id' => $request->parent_id,
                'parent_relation' => $request->parent_relation,
                'mothertongue' => $request->mothertongue,
                'birthorder' => $request->birthorder,
                'total_sibling' => $request->total_sibling,
                'total_stepbrother' => $request->total_stepbrother,
                'total_fosterbrother' => $request->total_fosterbrother,
                'live_with' => $request->live_with,
                'distancetoschool' => $distancetoschool,
                'gotoschool_with' => $gotoschool_with,
                'cronic_desease' => $request->cronic_desease,
                'severe_desease' => $request->severe_desease,
                'height' => $request->height,
                'weight' => $request->weight,
                
                'created' => now(),
                'updated' => now(),
                'update_by' => auth()->id(),
            ]);
        }); // Transaksi Selesai

        // 4. Redirect kembali
        return redirect()->route('student.index')
                         ->with('success', 'Data siswa baru berhasil ditambahkan.');
    }
}