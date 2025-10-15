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
        $user = DB::table('sis_user')->where('username', $request->username)->first();

        // --- Logika Cek Password Anda (sudah benar) ---
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

        // Mengambil semua data mentah yang diizinkan dengan filter yang benar
        $groupIds = DB::table('sis_usergroup')->where('user_id', $user->id)->pluck('group_id');
        $allowedPageIds = DB::table('sis_acl')->whereIn('group_id', $groupIds)->pluck('page_id');

        $allAllowedMenusData = DB::table('sis_page')
            ->whereIn('id', $allowedPageIds)
            ->where('enabled', 1)  // <-- Filter 'enabled' diterapkan di sini
            ->where('is_menu', 1)   // <-- Filter 'is_menu' diterapkan di sini
            ->select('id', 'parent_id', 'title', 'link', 'icon', 'application_id')
            ->orderBy('application_id')
            ->orderBy('ordering')
            ->get();
        
        // Menyerahkan data mentah ke "otak" (MenuService) untuk diproses
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