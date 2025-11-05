<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule; // <-- [TAMBAHAN] Untuk validasi 'unique' saat update

class UserController extends Controller
{
    /**
     * Menampilkan halaman daftar pengguna (User Manager).
     */
    public function index(Request $request)
    {
        $searchTerm = $request->query('search');
        $perPage = $request->query('perPage', 10);

        $query = DB::table('sis_user');

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('fullname', 'like', '%' . $searchTerm . '%')
                  ->orWhere('username', 'like', '%' . $searchTerm . '%');
            });
        }

        $users = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            // (Catatan: _user_table.blade.php nanti perlu di-update untuk menampilkan grup)
            return view('admin.user._user_table', ['users' => $users]);
        }

        return view('admin.user.index', [
            'users' => $users,
            'searchTerm' => $searchTerm,
            'perPage' => $perPage
        ]);
    }

    /**
     * [MODIFIKASI] Menampilkan form untuk membuat user baru.
     * Sekarang juga mengambil data group dan level.
     */
    public function create()
    {
        // Ambil data untuk pilihan di form
        $groups = DB::table('sis_group')->orderBy('ordering', 'asc')->get();
        $levels = DB::table('sis_level')->orderBy('grade', 'asc')->get();

        return view('admin.user.create', [
            'groups' => $groups,
            'levels' => $levels
        ]);
    }

    /**
     * [MODIFIKASI] Menyimpan user baru ke database.
     * Sekarang menyimpan ke 3 tabel sekaligus.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input Utama
        $request->validate([
            'fullname' => 'required|string|max:45',
            // [PERUBAHAN] Tambahkan 'alpha_dash' di akhir string
            'username' => 'required|string|max:45|unique:sis_user,username|alpha_dash', 
            'password' => 'required|string|min:6|confirmed',
            'email'    => 'nullable|email|max:45|unique:sis_user,email',
            'group_ids' => 'required|array|min:1', 
            'level_ids' => 'required|array|min:1', 
        ], [
            // [INI TAMBAHAN PESAN ERROR]
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, strip (-), dan underscore (_).'
        ]);

        // 2. Siapkan Data untuk tabel 'sis_user'
        $userData = $request->except(['_token', 'password_confirmation', 'group_ids', 'level_ids']);
        $userData['password'] = Hash::make($request->password);
        $userData['created'] = now();
        $userData['updated'] = now();

        // 3. Gunakan Transaksi Database (SANGAT PENTING)
        // Ini memastikan jika salah satu query gagal, semua data akan di-rollback.
        DB::transaction(function () use ($userData, $request) {
            // 3a. Simpan ke sis_user dan ambil ID user baru
            $newUserId = DB::table('sis_user')->insertGetId($userData);

            // 3b. Siapkan data untuk sis_usergroup (Bulk Insert)
            $userGroupData = [];
            foreach ($request->group_ids as $groupId) {
                $userGroupData[] = [
                    'user_id' => $newUserId,
                    'group_id' => $groupId
                ];
            }
            DB::table('sis_usergroup')->insert($userGroupData);

            // 3c. Siapkan data untuk sis_userlevel (Bulk Insert)
            $userLevelData = [];
            foreach ($request->level_ids as $levelId) {
                $userLevelData[] = [
                    'user_id' => $newUserId,
                    'level_id' => $levelId
                ];
            }
            DB::table('sis_userlevel')->insert($userLevelData);
        });

        // 4. Redirect kembali ke halaman daftar user
        return redirect()->route('admin.user.index')
                         ->with('success', 'User created successfully!');
    }

    /**
     * [BARU] Menampilkan form untuk mengedit user.
     */
    public function edit($id)
    {
        // 1. Ambil data user
        $user = DB::table('sis_user')->find($id);
        if (!$user) {
            return redirect()->route('admin.user.index')->with('error', 'User not found!');
        }

        // 2. Ambil semua pilihan group dan level
        $groups = DB::table('sis_group')->orderBy('ordering', 'asc')->get();
        $levels = DB::table('sis_level')->orderBy('grade', 'asc')->get();

        // 3. Ambil group dan level yang sudah dimiliki user
        $selectedGroups = DB::table('sis_usergroup')->where('user_id', $id)->pluck('group_id')->toArray();
        $selectedLevels = DB::table('sis_userlevel')->where('user_id', $id)->pluck('level_id')->toArray();

        return view('admin.user.edit', [
            'user' => $user,
            'groups' => $groups,
            'levels' => $levels,
            'selectedGroups' => $selectedGroups,
            'selectedLevels' => $selectedLevels
        ]);
    }

    /**
     * [BARU] Memperbarui data user di database.
     */
    public function update(Request $request, $id)
    {
        // 1. Validasi Input
        $request->validate([
            'fullname' => 'required|string|max:45',
            'username' => [
                'required', 'string', 'max:45',
                Rule::unique('sis_user')->ignore($id), // Abaikan ID saat ini saat cek unique
                'alpha_dash' // <-- [INI TAMBAHANNYA] Cukup tambahkan 'alpha_dash' di sini
            ],
            'email'    => [
                'nullable', 'email', 'max:45',
                Rule::unique('sis_user')->ignore($id)
            ],
            'password' => 'nullable|string|min:6|confirmed', // Password opsional saat update
            'group_ids' => 'required|array|min:1',
            'level_ids' => 'required|array|min:1',
        ], [
            // [INI TAMBAHAN PESAN ERROR]
            // Pesan ini diletakkan sebagai argumen kedua dari fungsi validate()
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, strip (-), dan underscore (_).'
        ]);

        // 2. Siapkan data untuk tabel 'sis_user'
        $userData = $request->except(['_token', '_method', 'password_confirmation', 'group_ids', 'level_ids']);
        $userData['updated'] = now();

        // 3. Hanya update password jika diisi
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        // 4. Mulai Transaksi Database
        DB::transaction(function () use ($userData, $request, $id) {
            // 4a. Update data di sis_user
            DB::table('sis_user')->where('id', $id)->update($userData);

            // 4b. Hapus data lama di tabel pivot
            DB::table('sis_usergroup')->where('user_id', $id)->delete();
            DB::table('sis_userlevel')->where('user_id', $id)->delete();

            // 4c. Siapkan data baru untuk sis_usergroup (Bulk Insert)
            $userGroupData = [];
            foreach ($request->group_ids as $groupId) {
                $userGroupData[] = ['user_id' => $id, 'group_id' => $groupId];
            }
            DB::table('sis_usergroup')->insert($userGroupData);

            // 4d. Siapkan data baru untuk sis_userlevel (Bulk Insert)
            $userLevelData = [];
            foreach ($request->level_ids as $levelId) {
                $userLevelData[] = ['user_id' => $id, 'level_id' => $levelId];
            }
            DB::table('sis_userlevel')->insert($userLevelData);
        });

        // 5. Redirect kembali
        return redirect()->route('admin.user.index')
                         ->with('success', 'User updated successfully!');
    }

    /**
     * [BARU] Menghapus user dari database.
     */
    public function destroy($id)
    {
        // Gunakan transaksi untuk memastikan semua data terkait terhapus
        DB::transaction(function () use ($id) {
            // 1. Hapus dari tabel pivot
            DB::table('sis_usergroup')->where('user_id', $id)->delete();
            DB::table('sis_userlevel')->where('user_id', $id)->delete();
            
            // 2. Hapus dari tabel utama
            DB::table('sis_user')->where('id', $id)->delete();
        });

        return redirect()->route('admin.user.index')
                         ->with('success', 'User deleted successfully!');
    }
}