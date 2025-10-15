<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\MenuService;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request, MenuService $menuService)
    {
        $request->validate(['username' => 'required', 'password' => 'required']);
        $user = DB::table('sis_user')->where('username', 'like', $request->username)->first();

        // --- Logika Cek Password (sudah benar dan tidak perlu diubah) ---
        if (!$user) { return back()->withErrors(['username' => 'Username tidak ditemukan.']); }
        $passwordMatch = false; $storedPass = $user->password;
        if (preg_match('/^\$2[aby]\$/', $storedPass)) {
            if (\Illuminate\Support\Facades\Hash::check($request->password, $storedPass)) { $passwordMatch = true; }
        } else {
            if (md5($request->password) === $storedPass) {
                $passwordMatch = true;
                DB::table('sis_user')->where('id', $user->id)->update(['password' => bcrypt($request->password)]);
            }
        }
        if (!$passwordMatch) { return back()->withErrors(['password' => 'Password salah.']); }
        if (strtolower($user->is_active) !== 'yes') { return back()->withErrors(['username' => 'Akun tidak aktif.']); }
        // --- Akhir Logika Cek Password ---

        $groupIds = DB::table('sis_usergroup')->where('user_id', $user->id)->pluck('group_id');
        $allowedPageIds = DB::table('sis_acl')->whereIn('group_id', $groupIds)->pluck('page_id');

        // [KUNCI] Mengambil data dengan filter dan urutan yang benar
        $allAllowedMenusData = DB::table('sis_page')
            ->whereIn('id', $allowedPageIds)
            ->where('enabled', 1)
            ->where('is_menu', 1)
            ->select('id', 'parent_id', 'title', 'link', 'icon', 'application_id')
            ->orderBy('ordering', 'asc') // Urutkan SEMUA menu berdasarkan 'ordering'
            ->get();
        
        // Serahkan data yang sudah terurut ke "otak" (MenuService)
        $finalMenuData = $menuService->processAndBuildMenu($allAllowedMenusData);

        session([
            'user_id' => $user->id,
            'fullname' => $user->fullname ?? '',
            'finalMenuData' => $finalMenuData
        ]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}