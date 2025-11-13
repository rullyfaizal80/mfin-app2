<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Menampilkan halaman daftar pengguna (User Manager).
     */
    public function index(Request $request)
    {
        // [MODIFIKASI] Query ini di-update untuk mengambil data group
        $searchTerm = $request->query('search');
        $perPage = $request->query('perPage', 10);

        $query = DB::table('sis_user')
            ->select(
                'sis_user.id', 
                'sis_user.fullname', 
                'sis_user.username', 
                'sis_user.is_active',
                'sis_user.email',
                'sis_user.mobile_phone',
                // Menggunakan GROUP_CONCAT untuk menggabungkan nama grup
                DB::raw('GROUP_CONCAT(DISTINCT sis_group.group_name ORDER BY sis_group.group_name SEPARATOR ", ") as groups') 
            )
            ->leftJoin('sis_usergroup', 'sis_user.id', '=', 'sis_usergroup.user_id')
            ->leftJoin('sis_group', 'sis_usergroup.group_id', '=', 'sis_group.id')
            ->groupBy(
                'sis_user.id', 
                'sis_user.fullname', 
                'sis_user.username', 
                'sis_user.is_active',
                'sis_user.email',
                'sis_user.mobile_phone'
            ); // Kelompokkan berdasarkan data user

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('sis_user.fullname', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sis_user.username', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sis_user.email', 'like', '%' . $searchTerm . '%');
            });
        }

        $query->orderBy('sis_user.fullname', 'asc');
        
        $users = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('admin.user._user_table', ['users' => $users]);
        }

        return view('admin.user.index', [
            'users' => $users,
            'searchTerm' => $searchTerm,
            'perPage' => $perPage
        ]);
    }

    /**
     * Menampilkan form untuk membuat user baru.
     */
    public function create()
    {
        $groups = DB::table('sis_group')->orderBy('ordering', 'asc')->get();
        $levels = DB::table('sis_level')->orderBy('grade', 'asc')->get();

        return view('admin.user.create', [
            'groups' => $groups,
            'levels' => $levels
        ]);
    }

    /**
     * [MODIFIKASI] Menyimpan user baru ke database.
     * Perbaikan: Menambahkan 'is_company' dan 'update_by'
     */
    public function store(Request $request)
    {
        // 1. Validasi Input Utama
        $request->validate([
            'fullname' => 'required|string|max:45',
            'username' => 'required|string|max:45|unique:sis_user,username|alpha_dash', 
            'password' => 'required|string|min:6|confirmed',
            'email'    => 'nullable|email|max:45|unique:sis_user,email',
            'group_ids' => 'required|array|min:1', 
            'level_ids' => 'required|array|min:1', 
        ], [
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, strip (-), dan underscore (_).'
        ]);

        // 2. Siapkan Data untuk tabel 'sis_user'
        $userData = $request->except(['_token', 'password_confirmation', 'group_ids', 'level_ids']);
        $userData['password'] = Hash::make($request->password);
        $userData['created'] = now();
        $userData['updated'] = now();

        // [PERBAIKAN] Tambahkan field wajib yang tidak ada di form
        $userData['is_company'] = 'no'; 
        $userData['update_by'] = session('user_id'); 

        // 3. Gunakan Transaksi Database
        DB::transaction(function () use ($userData, $request) {
            // 3a. Simpan ke sis_user dan ambil ID user baru
            $newUserId = DB::table('sis_user')->insertGetId($userData);

            // 3b. Siapkan data untuk sis_usergroup
            $userGroupData = [];
            foreach ($request->group_ids as $groupId) {
                $userGroupData[] = [
                    'user_id' => $newUserId,
                    'group_id' => $groupId
                ];
            }
            DB::table('sis_usergroup')->insert($userGroupData);

            // 3c. Siapkan data untuk sis_userlevel
            $userLevelData = [];
            foreach ($request->level_ids as $levelId) {
                $userLevelData[] = [
                    'user_id' => $newUserId,
                    'level_id' => $levelId
                ];
            }
            DB::table('sis_userlevel')->insert($userLevelData);
        });

        // 4. Redirect kembali
        return redirect()->route('admin.user.index')
                         ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit user.
     */
    public function edit($id)
    {
        $user = DB::table('sis_user')->find($id);
        if (!$user) {
            return redirect()->route('admin.user.index')->with('error', 'User tidak ditemukan!');
        }

        $groups = DB::table('sis_group')->orderBy('ordering', 'asc')->get();
        $levels = DB::table('sis_level')->orderBy('grade', 'asc')->get();

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
     * [MODIFIKASI] Memperbarui data user di database.
     * Perbaikan: Menambahkan 'update_by'
     */
    public function update(Request $request, $id)
    {
        // 1. Validasi Input
        $request->validate([
            'fullname' => 'required|string|max:45',
            'username' => [
                'required', 'string', 'max:45', 'alpha_dash',
                Rule::unique('sis_user')->ignore($id),
            ],
            'email'    => [
                'nullable', 'email', 'max:45',
                Rule::unique('sis_user')->ignore($id)
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'group_ids' => 'required|array|min:1',
            'level_ids' => 'required|array|min:1',
        ], [
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, strip (-), dan underscore (_).'
        ]);

        // 2. Siapkan data untuk tabel 'sis_user'
        $userData = $request->except(['_token', '_method', 'password_confirmation', 'group_ids', 'level_ids']);
        $userData['updated'] = now();

        // [PERBAIKAN] Tambahkan 'update_by'
        $userData['update_by'] = session('user_id');

        // 3. Hanya update password jika diisi
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        } else {
            // Hapus 'password' dari array jika tidak diisi
            unset($userData['password']); 
        }

        // 4. Mulai Transaksi Database
        DB::transaction(function () use ($userData, $request, $id) {
            // 4a. Update data di sis_user
            DB::table('sis_user')->where('id', $id)->update($userData);

            // 4b. Hapus data lama di tabel pivot
            DB::table('sis_usergroup')->where('user_id', $id)->delete();
            DB::table('sis_userlevel')->where('user_id', $id)->delete();

            // 4c. Siapkan data baru untuk sis_usergroup
            $userGroupData = [];
            foreach ($request->group_ids as $groupId) {
                $userGroupData[] = ['user_id' => $id, 'group_id' => $groupId];
            }
            DB::table('sis_usergroup')->insert($userGroupData);

            // 4d. Siapkan data baru untuk sis_userlevel
            $userLevelData = [];
            foreach ($request->level_ids as $levelId) {
                $userLevelData[] = ['user_id' => $id, 'level_id' => $levelId];
            }
            DB::table('sis_userlevel')->insert($userLevelData);
        });

        // 5. Redirect kembali
        return redirect()->route('admin.user.index')
                         ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Menghapus user dari database.
     */
    public function destroy($id)
    {
        // Pengecekan agar tidak bisa menghapus diri sendiri
        if ($id == session('user_id')) {
            return redirect()->route('admin.user.index')
                             ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        DB::transaction(function () use ($id) {
            // 1. Hapus dari tabel pivot
            DB::table('sis_usergroup')->where('user_id', $id)->delete();
            DB::table('sis_userlevel')->where('user_id', $id)->delete();
            
            // 2. Hapus dari tabel anak (jika ada) - DITANGANI OLEH onDelete('cascade')
            // DB::table('sis_student')->where('id', $id)->delete();
            // DB::table('sis_parents')->where('id', $id)->delete();
            
            // 3. Hapus dari tabel utama
            DB::table('sis_user')->where('id', $id)->delete();
        });

        return redirect()->route('admin.user.index')
                         ->with('success', 'User berhasil dihapus.');
    }
}