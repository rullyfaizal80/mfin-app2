<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ParentController extends Controller
{
    /**
     * Menampilkan halaman daftar orang tua.
     */
    public function index(Request $request)
    {
        $searchTerm = $request->query('search');
        $perPage = $request->query('perPage', 10);

        // Query ini menggabungkan sis_user dan sis_parents
        // (Mirip dengan ax_get_parents() di kode lama Anda)
        $query = DB::table('sis_user')
            ->join('sis_parents', 'sis_user.id', '=', 'sis_parents.id')
            ->where('sis_user.is_parent', 'yes') // Filter utama
            ->select(
                'sis_user.id',
                'sis_user.fullname',
                'sis_user.gender',
                'sis_user.mobile_phone',
                'sis_user.home_phone',
                'sis_parents.company_phone' // Ambil telp kantor dari sis_parents
            );

        // Logika pencarian: hanya berdasarkan Nama
        if ($searchTerm) {
            $query->where('sis_user.fullname', 'like', '%' . $searchTerm . '%');
        }

        // Urutkan berdasarkan Nama (Fullname)
        $query->orderBy('sis_user.fullname', 'asc');

        $parents = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('admin.parent._parent_table', ['parents' => $parents]);
        }

        return view('admin.parent.index', [
            'parents' => $parents,
            'searchTerm' => $searchTerm,
            'perPage' => $perPage
        ]);
    }

    /**
     * [BARU] Menampilkan form untuk membuat orang tua baru.
     */
    public function create()
    {
        // Method create untuk orang tua sangat sederhana
        // Kita tidak perlu mengambil data lain, hanya tampilkan view
        return view('admin.parent.create');
    }

    /**
     * [BARU] Menyimpan data orang tua baru ke database (2 tabel).
     */
    public function store(Request $request)
    {
        // 1. Validasi Data
        // Sesuai screenshot, hanya 'Nama Lengkap' yang wajib
        $request->validate([
            'fullname' => 'required|string|max:45',
            'email'    => [
                'nullable', 'email', 'max:45',
                Rule::unique('sis_user', 'email') // Pastikan email unik jika diisi
            ],
        ], [
            'email.unique' => 'Email ini sudah terdaftar.',
        ]);

        // 2. Gunakan Transaksi Database
        DB::transaction(function () use ($request) {
            
            // 3a. Simpan ke 'sis_user'
            $newUserId = DB::table('sis_user')->insertGetId([
                'fullname' => $request->fullname,
                'nickname' => $request->nickname,
                
                // Username & Password di-set NULL (sesuai database lama)
                'username' => null, 
                'password' => null,

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
                'is_parent' => 'yes', // <-- Ini PENTING
                
                // Set status lain ke 'no'
                'is_student' => 'no', 
                'is_admin' => 'no',
                'is_teacher' => $request->is_teacher ?? 'no',
                'is_educator' => 'no',
                'is_company' => 'no',
                
                'created' => now(),
                'updated' => now(),
                'update_by' => session('user_id'), 
            ]);

            // 3b. Simpan ke 'sis_parents'
            // [PERBAIKAN] Beri nilai default (string kosong) untuk semua
            // kolom NOT NULL agar sesuai database lama
            DB::table('sis_parents')->insert([
                'id' => $newUserId,
                'education' => $request->education ?? '',
                'profession' => $request->profession ?? '',
                'company' => $request->company ?? '',
                'company_phone' => $request->company_phone ?? '',
                'position' => $request->position ?? '',
                'salary' => $request->salary ?? '',
                
                'created' => now(),
                'updated' => now(),
                'update_by' => session('user_id'),
            ]);
        }); // Transaksi Selesai

        // 4. Redirect kembali
        return redirect()->route('parent.index')
                         ->with('success', 'Data orang tua baru berhasil ditambahkan.');
    }

    /**
     * [BARU] Menghapus data orang tua dari database (2 tabel).
     */
    public function destroy($id)
    {
        // Pengecekan agar tidak bisa menghapus diri sendiri
        if ($id == session('user_id')) {
            return redirect()->route('parent.index')
                             ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Kita gunakan Transaksi untuk memastikan kedua tabel terhapus
        DB::transaction(function () use ($id) {
            // 1. Hapus dari sis_parents (child table) terlebih dahulu
            DB::table('sis_parents')->where('id', $id)->delete();
            
            // 2. Hapus dari sis_user (parent table)
            DB::table('sis_user')->where('id', $id)->delete();
        });

        return redirect()->route('parent.index')
                         ->with('success', 'Data orang tua berhasil dihapus.');
    }

    /**
     * [BARU] Menampilkan form untuk mengedit data orang tua.
     */
    public function edit(string $id)
    {
        // 1. Ambil data gabungan dari sis_user dan sis_parents
        $parent = DB::table('sis_user')
                    ->leftJoin('sis_parents', 'sis_user.id', '=', 'sis_parents.id')
                    ->where('sis_user.id', $id)
                    ->select('sis_user.*', 'sis_parents.*', 'sis_user.id as user_id') // Ambil semua
                    ->first();

        // Jika orang tua tidak ditemukan
        if (!$parent) {
            return redirect()->route('parent.index')->with('error', 'Data orang tua tidak ditemukan.');
        }

        // 2. Tampilkan view edit dengan data
        return view('admin.parent.edit', [
            'parent' => $parent
        ]);
    }

    /**
     * [BARU] Memperbarui data orang tua di database.
     */
    public function update(Request $request, string $id)
    {
        // 1. Validasi Data
        $request->validate([
            'fullname' => 'required|string|max:45',
            'email'    => [
                'nullable', 'email', 'max:45',
                Rule::unique('sis_user', 'email')->ignore($id) // Abaikan ID ini
            ],
        ]);

        // 2. Gunakan Transaksi Database
        DB::transaction(function () use ($request, $id) {
            
            // 3a. Update tabel 'sis_user'
            DB::table('sis_user')->where('id', $id)->update([
                'fullname' => $request->fullname,
                'nickname' => $request->nickname, // Boleh null
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
                'is_teacher' => $request->is_teacher ?? 'no',
                
                'updated' => now(),
                'update_by' => session('user_id'),
            ]);

            // 3b. Update tabel 'sis_parents'
            // Gunakan updateOrInsert jika data di sis_parents belum ada
            DB::table('sis_parents')->updateOrInsert(
                ['id' => $id], // Kondisi pencarian
                [ // Data yang akan di-update atau di-insert
                    'education' => $request->education ?? '',
                    'profession' => $request->profession ?? '',
                    'company' => $request->company ?? '',
                    'company_phone' => $request->company_phone ?? '',
                    'position' => $request->position ?? '',
                    'salary' => $request->salary ?? '',
                    
                    'updated' => now(),
                    'update_by' => session('user_id'),
                    // 'created' tidak di-set agar tidak menimpa data lama saat update
                ]
            );

        }); // Transaksi Selesai

        // 4. Redirect kembali
        return redirect()->route('parent.index')
                         ->with('success', 'Data orang tua berhasil diperbarui.');
    }

}