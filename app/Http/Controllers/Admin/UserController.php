<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Menampilkan halaman daftar pengguna (User Manager).
     */
    public function index()
    {
        // 1. Mengambil data dari tabel 'sis_user' dengan paginasi (10 data per halaman)
        $users = DB::table('sis_user')->paginate(10);

        // 2. Mengirim data pengguna ke view
        return view('admin.user.index', ['users' => $users]);
    }
}