<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Ambil kata kunci pencarian dari URL
        $searchTerm = $request->query('search');
        
        // [PERUBAHAN 1] Ambil jumlah item per halaman dari URL, default-nya 10
        $perPage = $request->query('perPage', 10);

        // Mulai query ke tabel sis_user
        $query = DB::table('sis_user');

        // Jika ada kata kunci pencarian, filter datanya
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('fullname', 'like', '%' . $searchTerm . '%')
                  ->orWhere('username', 'like', '%' . $searchTerm . '%');
            });
        }

        // [PERUBAHAN 2] Gunakan variabel $perPage untuk paginasi
        $users = $query->paginate($perPage)->withQueryString();

        // Kirim semua data yang relevan ke view
        return view('admin.user.index', [
            'users' => $users,
            'searchTerm' => $searchTerm,
            'perPage' => $perPage // <-- Kirim nilai perPage agar dropdown mengingat pilihan
        ]);
    }
}