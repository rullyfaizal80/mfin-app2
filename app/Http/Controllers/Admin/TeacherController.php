<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // <-- Tambahkan ini
use Illuminate\Validation\Rule;    // <-- Tambahkan ini

class TeacherController extends Controller
{
    /**
     * Menampilkan halaman daftar guru/pendidik.
     */
    public function index(Request $request)
    {
        $searchTerm = $request->query('search');
        $perPage = $request->query('perPage', 10);

        // Query ini menggabungkan sis_user dan sis_teacher
        // (Sesuai ax_get_teacher() di kode lama)
        $query = DB::table('sis_user')
            ->join('sis_teacher', 'sis_user.id', '=', 'sis_teacher.id')
            ->where('sis_user.is_teacher', 'yes') // Filter utama
            ->select(
                'sis_user.id',
                'sis_teacher.nik', // Ambil NIK dari sis_teacher
                'sis_user.fullname',
                'sis_user.mobile_phone',
                'sis_user.home_phone',
                'sis_user.email'
            );

        // Logika pencarian: berdasarkan NIK atau Nama
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('sis_teacher.nik', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sis_user.fullname', 'like', '%' . $searchTerm . '%');
            });
        }

        // Urutkan berdasarkan Nama (Fullname)
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
     * [BARU] Menampilkan form untuk membuat guru baru.
     */
    public function create()
    {
        // Hanya tampilkan view
        return view('admin.teacher.create');
    }

    /**
     * [MODIFIKASI] Menyimpan data guru baru ke database (2 tabel).
     * Disesuaikan dengan field baru: att_id, skill, reference.
     */
    public function store(Request $request)
    {
        // 1. Validasi Data
        $request->validate([
            'fullname' => 'required|string|max:45',
            'nik' => [ // NIK/NIP
                'required', 'string', 'max:45', 'alpha_dash',
                Rule::unique('sis_teacher', 'nik'),
                Rule::unique('sis_user', 'username') // NIK akan jadi username
            ],
            'email'    => [
                'nullable', 'email', 'max:45',
                Rule::unique('sis_user', 'email')
            ],
        ], [
            'nik.alpha_dash' => 'NIK/NIP hanya boleh berisi huruf, angka, strip (-), dan underscore (_).',
            'nik.unique' => 'NIK/NIP ini sudah terdaftar.',
            'email.unique' => 'Email ini sudah terdaftar.',
        ]);

        // 2. Gunakan Transaksi Database
        DB::transaction(function () use ($request) {
            
            // 3a. Simpan ke 'sis_user' (Tidak ada perubahan di sini)
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

            // 3b. Simpan ke 'sis_teacher'
            // [PERBAIKAN] Menyimpan field-field baru dari form
            DB::table('sis_teacher')->insert([
                'id' => $newUserId,
                'nik' => $request->nik,
                
                // Data dari form "Data Kepegawaian"
                'emp_status' => $request->emp_status ?? 'permanent',
                'att_id' => $request->att_id ?? 0, // Absensi ID
                'grade' => $request->grade ?? '', // Golongan
                'license_no' => $request->license_no ?? '', // SK Pemerintah
                'foundation_license_no' => $request->foundation_license_no ?? '', // SK Yayasan
                'career_objective' => $request->career_objective ?? '',
                'skill' => $request->skill ?? '', // Keahlian
                'reference' => $request->reference ?? '', // Referensi
                'note' => $request->note ?? '',
                
                // Field NOT NULL lain yang diisi di 'edit CV' (sesuai db lama)
                'training' => '', 
                'organization' => '',
                'personal_identity' => '',
                
                // Field dengan default di DB, tapi kita set
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
     * [BARU] Menghapus data guru/pendidik dari database (6 tabel).
     */
    public function destroy($id)
    {
        // Pengecekan agar tidak bisa menghapus diri sendiri
        if ($id == session('user_id')) {
            return redirect()->route('teacher.index')
                             ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Kita gunakan Transaksi untuk memastikan SEMUA data terhapus
        // Sesuai dengan logika di Teacher.php -> delete()
        DB::transaction(function () use ($id) {

            // 1. Hapus data CV dari 4 tabel anak (child-child tables)
            // (Parameter 'teacher_id' sesuai dengan file SQL yang Anda berikan)
            DB::table('sis_education_level')->where('teacher_id', $id)->delete();
            DB::table('sis_work_experience')->where('teacher_id', $id)->delete();
            DB::table('sis_training')->where('teacher_id', $id)->delete();
            DB::table('sis_organization')->where('teacher_id', $id)->delete();

            // 2. Hapus data dari sis_teacher (child table)
            DB::table('sis_teacher')->where('id', $id)->delete();

            // 3. Hapus data dari sis_user (parent table)
            // (Ini juga akan otomatis menghapus data sis_student jika guru
            //  kebetulan juga terdaftar sebagai siswa, karena cascade)
            DB::table('sis_user')->where('id', $id)->delete();
        });

        return redirect()->route('teacher.index')
                         ->with('success', 'Data pendidik berhasil dihapus.');
    }
}