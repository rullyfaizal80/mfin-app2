<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\MenuService; // <-- [PERUBAHAN 1] Import MenuService

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    // [PERUBAHAN 2] Inject MenuService ke dalam method login
    public function login(Request $request, MenuService $menuService)
    {
        // --- BAGIAN INI SAMA SEKALI TIDAK BERUBAH ---
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = DB::table('sis_user')->where('username', $request->username)->first();

        if (!$user) {
            return back()->withErrors(['username' => 'Username tidak ditemukan.']);
        }

        $passwordMatch = false;
        $storedPass = $user->password;

        if (preg_match('/^\$2[aby]\$/', $storedPass)) {
            if (\Illuminate\Support\Facades\Hash::check($request->password, $storedPass)) {
                $passwordMatch = true;
            }
        } else {
            if (md5($request->password) === $storedPass) {
                $passwordMatch = true;
                DB::table('sis_user')->where('id', $user->id)->update([
                    'password' => bcrypt($request->password),
                ]);
            }
        }

        if (!$passwordMatch) {
            return back()->withErrors(['password' => 'Password salah.']);
        }

        if (strtolower($user->is_active) !== 'yes') {
            return back()->withErrors(['username' => 'Akun tidak aktif.']);
        }
        // --- AKHIR DARI BAGIAN YANG TIDAK BERUBAH ---


        // =====================================================================
        // [PERUBAHAN 3] LOGIKA PENGAMBILAN MENU LAMA DIHAPUS DAN DIGANTI
        // =====================================================================

        // 1. Ambil group dan level (jika masih perlu disimpan di session)
        $groupIds = DB::table('sis_usergroup')->where('user_id', $user->id)->pluck('group_id');
        $levelIds = DB::table('sis_userlevel')->where('user_id', $user->id)->pluck('level_id');
        
        // 2. Panggil MenuService untuk membuat menu dalam format pohon
        $menuTree = $menuService->getMenuForUser($user->id);

        // 3. Simpan semua data yang dibutuhkan ke session
        session([
            'user_id' => $user->id,
            'username' => $user->username,
            'fullname' => $user->fullname ?? '',
            'group_ids' => $groupIds,     // Tetap simpan jika Anda membutuhkannya di tempat lain
            'level_ids' => $levelIds,     // Tetap simpan jika Anda membutuhkannya di tempat lain
            'menuTree' => $menuTree       // Simpan menuTree yang baru dari Service
        ]);

        // =====================================================================
        // AKHIR DARI BAGIAN PERUBAHAN
        // =====================================================================

        // Redirect ke dashboard (tetap sama)
        return redirect()->route('dashboard');
    }

    public function logout(Request $request) // <-- Menambahkan Request $request
    {
        // Menggunakan cara yang lebih aman untuk logout
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

