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
        // 1. Validasi Data (hanya yang wajib diisi)
        $request->validate([
            'fullname' => 'required|string|max:45',
            'is_active' => 'required|string', // Sesuai instruksi Anda, 'Aktif' juga wajib
            'nis' => [
                'required', 'string', 'max:45', 'alpha_dash',
                Rule::unique('sis_student', 'nis'),
                Rule::unique('sis_user', 'username')
            ],
            'email'    => 'nullable|email|max:45|unique:sis_user,email',
        ], [
            'nis.alpha_dash' => 'NIS hanya boleh berisi huruf, angka, strip (-), dan underscore (_).',
            'nis.unique' => 'NIS ini sudah terdaftar.',
            'email.unique' => 'Email ini sudah terdaftar.',
        ]);

        // 2. Siapkan Data (Radio button)
        $distancetoschool = $request->distancetoschool1 === 'other' 
            ? ($request->distancetoschool2 ?? '') // Beri default jika 'other' dipilih tapi tidak diisi
            : $request->distancetoschool1;
            
        $gotoschool_with = $request->gotoschool_with1 === 'other' 
            ? ($request->gotoschool_with2 ?? '') // Beri default jika 'other' dipilih tapi tidak diisi
            : $request->gotoschool_with1;

        // 3. Gunakan Transaksi Database
        DB::transaction(function () use ($request, $distancetoschool, $gotoschool_with) {
            
            // 3a. Simpan ke 'sis_user'
            // Nilai default (??) disesuaikan dengan struktur sis_user (yang BANYAK BOLEH NULL)
            $newUserId = DB::table('sis_user')->insertGetId([
                'fullname' => $request->fullname,
                'nickname' => $request->nickname, // Boleh null
                'username' => $request->nis, 
                'password' => Hash::make($request->nis),
                'placeofbirth' => $request->placeofbirth, // Boleh null
                'dateofbirth' => $request->dateofbirth, // Boleh null
                'gender' => $request->gender, // Boleh null
                'street' => $request->street, // Boleh null
                'city' => $request->city, // Boleh null
                'province' => $request->province, // Boleh null
                'country' => $request->country, // Boleh null
                'postalcode' => $request->postalcode, // Boleh null
                'home_phone' => $request->home_phone, // Boleh null
                'mobile_phone' => $request->mobile_phone, // Boleh null
                'email' => $request->email, // Boleh null
                'religion' => $request->religion, // Boleh null
                
                'is_active' => $request->is_active, // Wajib
                'is_student' => 'yes', 
                'is_admin' => 'no',
                'is_parent' => 'no',
                'is_teacher' => 'no',
                'is_educator' => 'no',
                'is_company' => 'no', // Wajib
                
                'created' => now(), // Wajib
                'updated' => now(), // Wajib
                'update_by' => session('user_id'), // Wajib
            ]);

            // 3b. Simpan ke 'sis_student'
            // Nilai default (??) disesuaikan dengan struktur sis_student (yang BANYAK NOT NULL)
            DB::table('sis_student')->insert([
                'id' => $newUserId,
                'nis' => $request->nis, // Wajib
                
                'nin' => $request->nin ?? '', // Wajib (NOT NULL)
                
                'father_id' => $request->father_id ?? 0,
                'mother_id' => $request->mother_id ?? 0,
                'parent_id' => $request->parent_id ?? 0,
                'parent_relation' => $request->parent_relation ?? '', // Wajib (NOT NULL)
                'mothertongue' => $request->mothertongue ?? '', // Wajib (NOT NULL)
                'birthorder' => $request->birthorder ?? '', // Wajib (NOT NULL)
                'total_sibling' => $request->total_sibling ?? '', // Wajib (NOT NULL)
                'total_stepbrother' => $request->total_stepbrother ?? '', // Wajib (NOT NULL)
                'total_fosterbrother' => $request->total_fosterbrother ?? '', // Wajib (NOT NULL)
                'live_with' => $request->live_with ?? '', // Wajib (NOT NULL)
                'distancetoschool' => $distancetoschool ?? '', // Wajib (NOT NULL)
                'gotoschool_with' => $gotoschool_with ?? '', // Wajib (NOT NULL)
                'cronic_desease' => $request->cronic_desease ?? '', // Wajib (NOT NULL)
                'severe_desease' => $request->severe_desease ?? '', // Wajib (NOT NULL)
                'height' => $request->height ?? 0,
                'weight' => $request->weight ?? 0,
                
                'created' => now(),
                'updated' => now(),
                'update_by' => session('user_id'),
            ]);
        }); // Transaksi Selesai

        // 4. Redirect kembali
        return redirect()->route('student.index')
                         ->with('success', 'Data siswa baru berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        // Kita gunakan Transaksi untuk memastikan kedua tabel terhapus
        DB::transaction(function () use ($id) {
            // 1. Hapus dari sis_student (child table) terlebih dahulu
            DB::table('sis_student')->where('id', $id)->delete();

            // 2. Hapus dari sis_user (parent table)
            DB::table('sis_user')->where('id', $id)->delete();
        });

        return redirect()->route('student.index')
                         ->with('success', 'Data siswa berhasil dihapus.');
    }
}