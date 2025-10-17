<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
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

    // [PERUBAHAN UTAMA] Cek apakah ini permintaan AJAX
    if ($request->ajax()) {
        // Jika ya, kirim partial view tabel saja
        return view('admin.user._user_table', ['users' => $users]);
    }

    // Jika tidak, kirim view lengkap seperti biasa
    return view('admin.user.index', [
        'users' => $users,
        'searchTerm' => $searchTerm,
        'perPage' => $perPage
    ]);
}
}