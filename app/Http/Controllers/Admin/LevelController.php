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
    public function create()
    {
        return view('admin.level.create');
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'title' => 'required|string|max:45|unique:sis_level,title', // Pastikan title unik
            'grade' => 'required|integer',
        ]);

        // 2. Simpan ke Database
        DB::table('sis_level')->insert([
            'title' => $request->title,
            'grade' => $request->grade,
        ]);

        // 3. Redirect kembali ke halaman daftar dengan pesan sukses
        return redirect()->route('admin.level.index')
                         ->with('success', 'Level created successfully!');
    }

    public function destroy($id)
    {
        // Cari dan hapus data berdasarkan ID
        DB::table('sis_level')->where('id', $id)->delete();

        // Redirect kembali dengan pesan sukses
        return redirect()->route('admin.level.index')
                         ->with('success', 'Level deleted successfully!');
    }

    public function edit($id)
    {
        // Ambil data level tunggal berdasarkan ID
        $level = DB::table('sis_level')->where('id', $id)->first();

        // Jika data tidak ditemukan, kembali ke halaman index
        if (!$level) {
            return redirect()->route('admin.level.index')->with('error', 'Level not found!');
        }

        // Tampilkan view form edit dan kirim data level
        return view('admin.level.edit', ['level' => $level]);
    }

        public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            // Rule 'unique' diubah agar mengabaikan ID saat ini
            'title' => 'required|string|max:45|unique:sis_level,title,' . $id,
            'grade' => 'required|integer',
        ]);

        // Update data di database
        DB::table('sis_level')->where('id', $id)->update([
            'title' => $request->title,
            'grade' => $request->grade,
        ]);

        // Redirect kembali dengan pesan sukses
        return redirect()->route('admin.level.index')
                         ->with('success', 'Level updated successfully!');
    }
}