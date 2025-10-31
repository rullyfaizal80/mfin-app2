<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LevelController extends Controller
{
    public function index(Request $request)
{
    $searchTerm = $request->query('search');
    $perPage = $request->query('perPage', 10);

    $query = DB::table('sis_level');

    if ($searchTerm) {
        $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'like', '%' . $searchTerm . '%')
              ->orWhere('grade', 'like', '%' . $searchTerm . '%');
        });
    }

    $query->orderBy('grade', 'asc');
    $levels = $query->paginate($perPage)->withQueryString();

    // [PERUBAHAN UTAMA] Cek apakah ini permintaan AJAX
    if ($request->ajax()) {
        // Jika ya, kirim partial view tabel saja
        return view('admin.level._level_table', ['levels' => $levels]);
    }

    // Jika tidak, kirim view lengkap seperti biasa
    return view('admin.level.index', [
        'levels' => $levels,
        'searchTerm' => $searchTerm,
        'perPage' => $perPage
    ]);
}
}